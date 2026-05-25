<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotpressKnowledgeLink extends Model
{
    protected $fillable = [
        'url',
        'content_hash',
        'sync_status',
        'last_synced_at',
        'last_discovered_at',
        'is_active',
        'last_error',
        'botpress_file_id',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'last_discovered_at' => 'datetime',
        'is_active' => 'bool',
    ];
}
