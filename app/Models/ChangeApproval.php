<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeApproval extends Model
{
    protected $table = 'change_approvals';

    protected $fillable = [
        'section',
        'target_key',
        'summary',
        'payload',
        'previous_payload',
        'status',
        'requested_by',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'previous_payload' => 'array',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
