<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'subject',
        'preview_text',
        'from_name',
        'from_email',
        'reply_to',
        'template_id',
        'target_type',
        'group_id',
        'content_html',
        'content_plain',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'unique_opens',
        'clicked_count',
        'unique_clicks',
        'bounced_count',
        'unsubscribed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'opened_count' => 'integer',
        'unique_opens' => 'integer',
        'clicked_count' => 'integer',
        'unique_clicks' => 'integer',
        'bounced_count' => 'integer',
        'unsubscribed_count' => 'integer',
    ];

    /**
     * Associated Template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Targeted Subscriber Group (if target_type === 'group')
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SubscriberGroup::class, 'group_id');
    }

    /**
     * Detailed Recipient Logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EmailCampaignLog::class, 'campaign_id');
    }

    /**
     * Calculated Metric Rates
     */
    public function getOpenRateAttribute(): float
    {
        if ($this->sent_count <= 0) return 0.0;
        return round(($this->unique_opens / $this->sent_count) * 100, 1);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->sent_count <= 0) return 0.0;
        return round(($this->unique_clicks / $this->sent_count) * 100, 1);
    }

    public function getBounceRateAttribute(): float
    {
        if ($this->sent_count <= 0) return 0.0;
        return round(($this->bounced_count / $this->sent_count) * 100, 1);
    }

    public function getUnsubscribeRateAttribute(): float
    {
        if ($this->sent_count <= 0) return 0.0;
        return round(($this->unsubscribed_count / $this->sent_count) * 100, 1);
    }
}
