<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'status',
        'source',
        'unsubscribe_token',
        'verification_token',
        'ip_address',
        'country',
        'city',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
        'metadata',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(40) . time();
            }
            if (empty($subscriber->subscribed_at) && $subscriber->status === 'subscribed') {
                $subscriber->subscribed_at = now();
            }
        });
    }

    /**
     * Subscriber Groups / Segments
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(SubscriberGroup::class, 'subscriber_group_pivot', 'subscriber_id', 'group_id');
    }

    /**
     * Campaign Logs
     */
    public function campaignLogs(): HasMany
    {
        return $this->hasMany(EmailCampaignLog::class);
    }

    /**
     * Full Name Accessor
     */
    public function getFullNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return !empty($name) ? $name : ($this->email ? explode('@', $this->email)[0] : 'Subscriber');
    }

    /**
     * Active Subscribed Scope
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    /**
     * Filter by Group Scope
     */
    public function scopeByGroup($query, $groupId)
    {
        return $query->whereHas('groups', function ($q) use ($groupId) {
            $q->where('subscriber_groups.id', $groupId);
        });
    }

    /**
     * Generate secure unsubscribe URL
     */
    public function getUnsubscribeUrlAttribute(): string
    {
        return url('/unsubscribe/' . $this->unsubscribe_token);
    }
}
