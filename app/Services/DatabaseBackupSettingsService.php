<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupSetting;
use Carbon\Carbon;

class DatabaseBackupSettingsService
{
    public function current(): BackupSetting
    {
        return BackupSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'automatic_backups_enabled' => false,
                'frequency' => BackupSetting::FREQUENCY_DAILY,
                'retention_count' => 7,
            ]
        );
    }

    public function update(array $attributes): BackupSetting
    {
        $settings = $this->current();
        $settings->fill($attributes);
        $settings->save();

        return $settings->fresh();
    }

    public function shouldRunNow(BackupSetting $settings): bool
    {
        if (! $settings->automatic_backups_enabled) {
            return false;
        }

        return ! Backup::query()
            ->whereNull('created_by')
            ->where('created_at', '>=', $this->periodStart((string) $settings->frequency))
            ->exists();
    }

    private function periodStart(string $frequency): Carbon
    {
        return match ($frequency) {
            BackupSetting::FREQUENCY_WEEKLY => now()->startOfWeek(),
            BackupSetting::FREQUENCY_MONTHLY => now()->startOfMonth(),
            default => now()->startOfDay(),
        };
    }
}
