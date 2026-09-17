<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignClick extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'campaign_log_id',
        'url',
        'clicked_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    /**
     * Parent Campaign Log
     */
    public function log(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignLog::class, 'campaign_log_id');
    }
}
