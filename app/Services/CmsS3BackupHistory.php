<?php

namespace App\Services;

use App\Exceptions\CmsHistoryException;
use App\Models\Backup;
use App\Support\CmsBackupSqlReader;
use App\Support\CmsVersionHistory;
use App\Support\PublicationBackupContent;
use Aws\Exception\CredentialsException;
use Aws\S3\Exception\S3Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use PharData;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

class CmsS3BackupHistory
{
    public function backups(): Builder
    {
        $disk = $this->diskName();

        return Backup::query()->where('storage_disk', $disk);
    }

    public function read(Backup $backup, string $tabKey): object
    {
        $disk = $this->diskName();
        abort_unless($backup->storage_disk === $disk && config('filesystems.disks.'.$disk.'.driver') === 's3', 404, 'Only S3 database backups are available in CMS version history.');
        $prefix = trim((string) config('database_backups.s3_path', 'backups/database'), '/').'/';
        abort_unless(str_starts_with($backup->file_path, $prefix) && ! str_contains($backup->file_path, '..'), 422, 'The S3 backup path is invalid.');

        $remote = null;
        $sql = null;
        $temporary = null;
        $path = null;
        try {
            // The source is always S3. The ZIP decoder uses a disposable spool, never a local backup or cache.
            // Keep the underlying failure: the adapter's readStream may otherwise swallow it.
            $remote = Storage::disk($disk)->getDriver()->readStream($backup->file_path);
            if (! is_resource($remote)) {
                throw new RuntimeException('The selected S3 database backup could not be read.');
            }
            $temporary = tmpfile();
            if (! is_resource($temporary)) {
                throw new RuntimeException('Unable to allocate a temporary backup decoder.');
            }
            $max = (int) config('database_backups.history_max_archive_bytes', 268435456);
            $copied = stream_copy_to_stream($remote, $temporary, $max + 1);
            if ($copied === false || $copied > $max) {
                throw new RuntimeException('The S3 backup exceeds the CMS history read limit or could not be downloaded completely.');
            }
            $spool = stream_get_meta_data($temporary)['uri'];
            $path = $spool.'.zip';
            if (! copy($spool, $path)) {
                throw new RuntimeException('Unable to open the S3 backup decoder.');
            }
            $archiveChecksum = hash_file('sha256', $path);
            $archive = new PharData($path);
            $sqlEntry = null;
            foreach (new RecursiveIteratorIterator($archive) as $entry) {
                if (strtolower($entry->getExtension()) === 'sql') {
                    if ($sqlEntry !== null) {
                        throw new RuntimeException('The backup contains multiple SQL dumps and cannot be restored automatically.');
                    }
                    $sqlEntry = $entry;
                }
            }
            if ($sqlEntry === null || $sqlEntry->getSize() > (int) config('database_backups.history_max_sql_bytes', 536870912)) {
                throw new RuntimeException('The backup SQL dump is missing or exceeds the CMS history read limit.');
            }
            if (PublicationBackupContent::supports($tabKey)) {
                $definition = PublicationBackupContent::definition($tabKey);
                $tables = [];
                foreach ($definition['tables'] as $table => $key) {
                    $sql = fopen($sqlEntry->getPathname(), 'rb');
                    $tables[$table] = app(CmsBackupSqlReader::class)->read($sql, $table, $key, [$key, 'title']);
                    fclose($sql);
                    $sql = null;
                }
                $row = (object) ['title' => $definition['label'], 'content' => PublicationBackupContent::encode($tables)];
            } else {
                $sql = fopen($sqlEntry->getPathname(), 'rb');
                $rows = app(CmsBackupSqlReader::class)->read($sql);
                $row = $rows[$tabKey] ?? (object) ['title' => null, 'content' => null];
            }

            return (object) [
                'id' => $backup->id, 'tab_key' => $tabKey, 'title' => $row->title, 'content' => $row->content,
                'checksum' => CmsVersionHistory::checksum($row), 'backup_checksum' => $archiveChecksum,
                'backup_name' => $backup->backup_name, 'source' => 's3_backup',
                'actor_name' => 'S3 database backup', 'actor_role' => null,
                'created_at' => $backup->created_at->toIso8601String(),
            ];
        } catch (Throwable $exception) {
            report($exception);
            throw $this->readFailure($exception);
        } finally {
            if (is_resource($sql)) {
                fclose($sql);
            }
            unset($sqlEntry, $entry, $archive);
            if ($path !== null && is_file($path)) {
                unlink($path);
            }
            if (is_resource($temporary)) {
                fclose($temporary);
            }
            if (is_resource($remote)) {
                fclose($remote);
            }
        }
    }

    private function diskName(): string
    {
        $disk = (string) config('database_backups.s3_disk', 's3');
        if (config('filesystems.disks.'.$disk.'.driver') !== 's3') {
            throw new CmsHistoryException(503, 'CMS version history requires the configured S3 backup disk.', 's3_backup_disk_not_configured');
        }
        if (! filled(config('filesystems.disks.'.$disk.'.bucket'))) {
            throw new CmsHistoryException(503, 'Configure the S3 backup bucket before using CMS version history.', 's3_backup_bucket_not_configured');
        }

        return $disk;
    }

    private function readFailure(Throwable $exception): CmsHistoryException
    {
        for ($cause = $exception; $cause !== null; $cause = $cause->getPrevious()) {
            if ($cause instanceof CredentialsException) {
                return new CmsHistoryException(503, 'This server cannot authenticate with AWS to read S3 database backups. Configure its AWS credentials or IAM role, then try again. No content was restored.', 's3_authentication_unavailable', $exception);
            }
            if ($cause instanceof S3Exception) {
                $code = $cause->getAwsErrorCode();
                if (in_array($code, ['NoSuchKey', 'NoSuchBucket', 'NotFound'], true) || $cause->getStatusCode() === 404) {
                    return new CmsHistoryException(404, 'The selected database backup was not found in S3. Choose another backup or check the configured bucket and backup path. No content was restored.', 's3_backup_not_found', $exception);
                }
                if (in_array($code, ['AccessDenied', 'InvalidAccessKeyId', 'SignatureDoesNotMatch', 'ExpiredToken', 'InvalidToken'], true) || in_array($cause->getStatusCode(), [401, 403], true)) {
                    return new CmsHistoryException(503, 'This server is not authorized to read the selected S3 database backup. Check its AWS credentials and access to database backups. No content was restored.', 's3_backup_access_denied', $exception);
                }

                return new CmsHistoryException(503, 'S3 database backups are temporarily unavailable. Please try again later. No content was restored.', 's3_backup_unavailable', $exception);
            }
        }

        return new CmsHistoryException(422, 'The selected S3 database backup could not be verified. It may be missing, damaged, or incompatible. No content was restored.', 's3_backup_invalid', $exception);
    }
}
