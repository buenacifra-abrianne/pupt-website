<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use App\Services\DatabaseBackupSettingsService;
use Illuminate\Console\Command;
use Throwable;

class RunScheduledDatabaseBackupsCommand extends Command
{
    protected $signature = 'database-backups:run-scheduled';

    protected $description = 'Run scheduled database backups and apply retention rules.';

    public function handle(
        DatabaseBackupSettingsService $settingsService,
        DatabaseBackupService $backupService
    ): int {
        $settings = $settingsService->current();

        if (! $settings->automatic_backups_enabled) {
            $this->info('Automatic database backups are disabled.');

            return self::SUCCESS;
        }

        try {
            if ($settingsService->shouldRunNow($settings)) {
                $backup = $backupService->createBackup();
                $this->info('Created scheduled backup: '.$backup->backup_name);
            } else {
                $this->info('No scheduled database backup is due in the current period.');
            }

            $deleted = $backupService->pruneOldBackups((int) $settings->retention_count);
            if ($deleted > 0) {
                $this->info('Pruned '.$deleted.' backup(s) to enforce retention.');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Scheduled database backup failed.');

            return self::FAILURE;
        }
    }
}
