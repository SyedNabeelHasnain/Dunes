<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnblockRequest extends Model
{
    use HasFactory;

    protected $table = 'unblock_requests';

    protected $fillable = [
        'ip_rule_id',
        'ip_address',
        'name',
        'email',
        'phone',
        'reason',
        'user_agent',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function ipRule(): BelongsTo
    {
        return $this->belongsTo(IpRule::class, 'ip_rule_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
