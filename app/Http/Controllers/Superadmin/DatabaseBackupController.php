<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\UpdateDatabaseBackupSettingsRequest;
use App\Models\Backup;
use App\Services\DatabaseBackupService;
use App\Services\DatabaseBackupSettingsService;
use App\Support\AuditLog;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        return redirect()->route('superadmin.dashboard', ['tab' => 'database-backups']);
    }

    public function store(DatabaseBackupService $backupService)
    {
        try {
            $backupService->createBackup($this->sessionUserId());

            return $this->dashboardBackupRedirect()
                ->with('success', 'Database backup created successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->dashboardBackupRedirect()
                ->with('error', 'Database backup creation failed.');
        }
    }

    public function download(Backup $backup, DatabaseBackupService $backupService)
    {
        try {
            return $backupService->downloadBackup($backup, $this->sessionUserId());
        } catch (Throwable $e) {
            report($e);

            return $this->dashboardBackupRedirect()
                ->with('error', 'Backup download failed.');
        }
    }

    public function destroy(Backup $backup, DatabaseBackupService $backupService)
    {
        try {
            $backupService->deleteBackup($backup, $this->sessionUserId());

            return $this->dashboardBackupRedirect()
                ->with('success', 'Database backup deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->dashboardBackupRedirect()
                ->with('error', 'Backup deletion failed.');
        }
    }

    public function updateSettings(
        UpdateDatabaseBackupSettingsRequest $request,
        DatabaseBackupSettingsService $settingsService,
        DatabaseBackupService $backupService
    ) {
        $validated = $request->validated();

        $settings = $settingsService->update([
            'automatic_backups_enabled' => $request->boolean('automatic_backups_enabled'),
            'frequency' => $validated['frequency'],
            'retention_count' => (int) $validated['retention_count'],
        ]);

        if ($settings->automatic_backups_enabled) {
            $backupService->pruneOldBackups((int) $settings->retention_count, $this->sessionUserId());
        }

        AuditLog::record('UPDATED', 'DATABASE_BACKUPS', 'Updated database backup settings.');

        return $this->dashboardBackupRedirect()
            ->with('success', 'Backup settings updated successfully.');
    }

    private function dashboardBackupRedirect()
    {
        return redirect()->route('superadmin.dashboard', ['tab' => 'database-backups']);
    }

    private function sessionUserId(): ?int
    {
        $userId = (int) session('user_id');

        return $userId > 0 ? $userId : null;
    }
}
