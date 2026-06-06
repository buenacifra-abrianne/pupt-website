<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    public const RETENTION_OPTIONS = [7, 15, 30];

    protected $fillable = [
        'automatic_backups_enabled',
        'frequency',
        'retention_count',
    ];

    protected $casts = [
        'automatic_backups_enabled' => 'boolean',
        'retention_count' => 'integer',
    ];
}
