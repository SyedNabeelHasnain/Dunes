<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class IpRule extends Model
{
    use HasFactory;

    protected $table = 'ip_rules';

    protected $fillable = [
        'ip_address',
        'ip_mask',
        'mode',
        'label',
        'reason',
        'status',
        'blocked_attempts_count',
        'last_activity_at',
        'created_by',
    ];

    protected $casts = [
        'ip_mask' => 'integer',
        'blocked_attempts_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Cache key for cached security rules.
     */
    public const CACHE_KEY = 'ip_security_rules_cache';

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unblockRequests(): HasMany
    {
        return $this->hasMany(UnblockRequest::class, 'ip_rule_id');
    }

    public function isBlocked(): bool
    {
        return $this->status === 'active' && $this->mode === 'block';
    }

    public function isIgnoredFromAnalytics(): bool
    {
        return $this->status === 'active' && $this->mode === 'ignore_analytics';
    }
}
