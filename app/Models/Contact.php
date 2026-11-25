<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status',
        'is_spam', 'spam_score', 'spam_reasons', 'quarantined_at', 'security_meta',
        'request_log_id', 'ip_address', 'is_verified',
        'traffic_channel', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_term', 'utm_content', 'gclid', 'fbclid',
        'initial_referrer', 'landing_page',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_spam' => 'boolean',
        'spam_score' => 'integer',
        'spam_reasons' => 'array',
        'security_meta' => 'array',
        'quarantined_at' => 'datetime',
    ];

    public function scopeClean(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_spam', false)->orWhereNull('is_spam');
        })->where('status', '!=', 'spam');
    }

    public function scopeQuarantined(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_spam', true)->orWhere('status', 'spam');
        });
    }
}
