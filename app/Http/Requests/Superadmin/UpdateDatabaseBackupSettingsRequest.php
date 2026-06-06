<?php

namespace App\Http\Requests\Superadmin;

use App\Models\BackupSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDatabaseBackupSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return strtoupper(trim((string) session('user_role'))) === 'SUPERADMIN';
    }

    public function rules(): array
    {
        return [
            'automatic_backups_enabled' => ['nullable', 'boolean'],
            'frequency' => ['required', Rule::in([
                BackupSetting::FREQUENCY_DAILY,
                BackupSetting::FREQUENCY_WEEKLY,
                BackupSetting::FREQUENCY_MONTHLY,
            ])],
            'retention_count' => ['required', Rule::in(BackupSetting::RETENTION_OPTIONS)],
        ];
    }
}
