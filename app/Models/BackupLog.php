<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    public const ACTION_CREATE = 'create';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_DELETE = 'delete';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'action',
        'backup_name',
        'status',
    ];
}
