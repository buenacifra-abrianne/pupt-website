<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'backup_name',
        'file_path',
        'file_size',
        'storage_disk',
        'created_by',
    ];
}
