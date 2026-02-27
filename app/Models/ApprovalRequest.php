<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'title',
        'details',
        'type',
        'status',
        'requester_name',
        'requester_email',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason'
    ];
}