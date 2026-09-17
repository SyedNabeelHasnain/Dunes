<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriberGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    /**
     * Group Subscribers
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'subscriber_group_pivot', 'group_id', 'subscriber_id')
            ->withTimestamps();
    }

    /**
     * Group Campaigns
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'group_id');
    }

    /**
     * Active Subscribers Count
     */
    public function getActiveSubscribersCountAttribute(): int
    {
        return $this->subscribers()->where('status', 'subscribed')->count();
    }
}
