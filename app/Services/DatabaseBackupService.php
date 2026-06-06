<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Support\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class DatabaseBackupService
{
    public function createBackup(?int $userId = null): Backup
    {
        $backupName = 'database_backup_'.now()->format('Y-m-d_H-i').'.zip';
        $disk = $this->resolveStorageDisk();
        $sqlTempPath = $this->makeTempPath('sql');
        $zipTempPath = $this->makeTempPath('zip');

        try {
            $this->runMysqlDump($sqlTempPath);
            $this->zipDump($sqlTempPath, $zipTempPath, $backupName);

            $storedPath = $this->storeArchive($disk, $backupName, $zipTempPath);
            $fileSize = max(0, (int) filesize($zipTempPath));

            $backup = Backup::query()->create([
                'backup_name' => $backupName,
                'file_path' => $storedPath,
                'file_size' => $fileSize,
                'storage_disk' => $disk,
                'created_by' => $userId,
            ]);

            $this->logAction(BackupLog::ACTION_CREATE, $backupName, BackupLog::STATUS_SUCCESS, $userId);
            AuditLog::record('CREATED', 'DATABASE_BACKUPS', 'Created database backup '.$backupName, $backup->id);

            return $backup;
        } catch (Throwable $e) {
            $this->logAction(BackupLog::ACTION_CREATE, $backupName, BackupLog::STATUS_FAILED, $userId);

            throw new RuntimeException(
                'Unable to create the database backup. Please verify MySQL dump access on the server.',
                previous: $e
            );
        } finally {
            $this->deleteTempFile($sqlTempPath);
            $this->deleteTempFile($zipTempPath);
        }
    }

    public function downloadBackup(Backup $backup, ?int $userId = null): StreamedResponse|RedirectResponse
    {
        $disk = Storage::disk($backup->storage_disk);

        if (! $disk->exists($backup->file_path)) {
            $this->logAction(BackupLog::ACTION_DOWNLOAD, $backup->backup_name, BackupLog::STATUS_FAILED, $userId);

            throw new RuntimeException('Backup file could not be found.');
        }

        $this->logAction(BackupLog::ACTION_DOWNLOAD, $backup->backup_name, BackupLog::STATUS_SUCCESS, $userId);
        AuditLog::record('DOWNLOAD', 'DATABASE_BACKUPS', 'Downloaded database backup '.$backup->backup_name, $backup->id);

        if ($backup->storage_disk === config('database_backups.s3_disk', 's3')) {
            $url = $disk->temporaryUrl(
                $backup->file_path,
                now()->addMinutes((int) config('database_backups.download_url_ttl_minutes', 5)),
                ['ResponseContentDisposition' => 'attachment; filename="'.$backup->backup_name.'"']
            );

            return redirect()->away($url);
        }

        return $disk->download($backup->file_path, $backup->backup_name);
    }

    public function deleteBackup(Backup $backup, ?int $userId = null): void
    {
        $disk = Storage::disk($backup->storage_disk);

        if ($disk->exists($backup->file_path)) {
            $disk->delete($backup->file_path);
        }

        $backupName = $backup->backup_name;
        $backupId = $backup->id;
        $backup->delete();

        $this->logAction(BackupLog::ACTION_DELETE, $backupName, BackupLog::STATUS_SUCCESS, $userId);
        AuditLog::record('DELETED', 'DATABASE_BACKUPS', 'Deleted database backup '.$backupName, $backupId);
    }

    public function pruneOldBackups(int $keepLast, ?int $userId = null): int
    {
        $keepLast = max(0, $keepLast);
        $deleted = 0;

        $backups = Backup::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip($keepLast)
            ->get();

        foreach ($backups as $backup) {
            $this->deleteBackup($backup, $userId);
            $deleted++;
        }

        return $deleted;
    }

    public function localStorageLabel(): string
    {
        return 'Local / storage/app/backups';
    }

    public function s3StorageLabel(): string
    {
        return 'S3 / '.trim((string) config('database_backups.s3_path', 'backups/database'), '/');
    }

    private function resolveStorageDisk(): string
    {
        $s3Disk = (string) config('database_backups.s3_disk', 's3');
        $s3Config = config('filesystems.disks.'.$s3Disk, []);

        if (($s3Config['driver'] ?? null) === 's3' && filled($s3Config['bucket'] ?? null)) {
            return $s3Disk;
        }

        return (string) config('database_backups.local_disk', 'database_backups_local');
    }

    private function runMysqlDump(string $sqlTempPath): void
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Database backups currently support MySQL connections only.');
        }

        $database = (string) ($connection['database'] ?? '');
        $username = (string) ($connection['username'] ?? '');
        $host = $connection['host'] ?? '127.0.0.1';
        $host = is_array($host) ? (string) ($host[0] ?? '127.0.0.1') : (string) $host;
        $port = (string) ($connection['port'] ?? '3306');

        if ($database === '' || $username === '') {
            throw new RuntimeException('Database backup connection details are incomplete.');
        }

        $command = [
            (string) config('database_backups.dump_binary', 'mysqldump'),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--result-file='.$sqlTempPath,
            $database,
        ];

        $env = [];
        $password = (string) ($connection['password'] ?? '');
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        $process = new Process(
            $command,
            base_path(),
            $env,
            null,
            (int) config('database_backups.dump_timeout', 300)
        );

        $process->run();

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException($message !== '' ? $message : 'mysqldump failed.');
        }
    }

    private function zipDump(string $sqlTempPath, string $zipTempPath, string $backupName): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The ZipArchive extension is required for database backups.');
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipTempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new RuntimeException('Unable to create the backup archive.');
        }

        $zip->addFile($sqlTempPath, pathinfo($backupName, PATHINFO_FILENAME).'.sql');
        $zip->close();
    }

    private function storeArchive(string $diskName, string $backupName, string $zipTempPath): string
    {
        $path = $diskName === config('database_backups.s3_disk', 's3')
            ? trim((string) config('database_backups.s3_path', 'backups/database'), '/').'/'.$backupName
            : $backupName;

        $stream = fopen($zipTempPath, 'r');
        Storage::disk($diskName)->put($path, $stream, ['visibility' => 'private']);
        fclose($stream);

        if (! Storage::disk($diskName)->exists($path)) {
            throw new RuntimeException('The backup archive could not be stored.');
        }

        return $path;
    }

    private function makeTempPath(string $extension): string
    {
        $directory = storage_path('app/backups/tmp');
        File::ensureDirectoryExists($directory);

        return $directory.DIRECTORY_SEPARATOR.'backup_'.str_replace('.', '', uniqid('', true)).'.'.$extension;
    }

    private function deleteTempFile(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function logAction(string $action, ?string $backupName, string $status, ?int $userId): void
    {
        BackupLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'backup_name' => $backupName,
            'status' => $status,
        ]);
    }
}
