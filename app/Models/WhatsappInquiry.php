<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappInquiry extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'request_log_id',
        'name',
        'phone',
        'email',
        'tour_name',
        'page_url',
        'message_text',
        'client_ip',
        'country',
        'city',
        'region',
        'device_type',
        'browser_name',
        'os_name',
        'isp',
        'organization',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer_url',
        'screen_res',
        'client_timezone',
        'client_language',
        'prospect_source',
        'traffic_channel',
        'initial_referrer',
        'landing_page',
        'session_duration_seconds',
        'pages_viewed_count',
        'journey_timeline',
        'gclid',
        'fbclid',
        'status',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'session_duration_seconds' => 'integer',
        'pages_viewed_count' => 'integer',
        'journey_timeline' => 'array',
    ];

    /**
     * Mark inquiry lead as read.
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        if ($this->status === 'new' || empty($this->status)) {
            $this->status = 'read';
        }
        $this->read_at = now();
        $this->save();
    }
}
