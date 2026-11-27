<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyntheticTestRun extends Model
{
    protected $fillable = [
        'trigger_type',
        'status',
        'flows_requested',
        'locale',
        'device_profile',
        'mail_mode',
        'auto_cleanup',
        'cleanup_completed',
        'total_flows',
        'passed_flows',
        'failed_flows',
        'total_steps',
        'passed_steps',
        'failed_steps',
        'duration_ms',
        'summary',
        'artifacts',
        'alert_sent',
        'triggered_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'flows_requested' => 'array',
        'artifacts' => 'array',
        'auto_cleanup' => 'boolean',
        'cleanup_completed' => 'boolean',
        'alert_sent' => 'boolean',
        'total_flows' => 'integer',
        'passed_flows' => 'integer',
        'failed_flows' => 'integer',
        'total_steps' => 'integer',
        'passed_steps' => 'integer',
        'failed_steps' => 'integer',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(SyntheticTestStep::class)->orderBy('id');
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function hasUncleanedArtifacts(): bool
    {
        if ($this->cleanup_completed) {
            return false;
        }

        $artifacts = is_array($this->artifacts) ? $this->artifacts : [];
        foreach ($artifacts as $items) {
            if (is_array($items) && ! empty($items)) {
                return true;
            }
        }

        return false;
    }

    public function getPassRateAttribute(): float
    {
        if ($this->total_steps <= 0) {
            return 0.0;
        }

        return round(($this->passed_steps / $this->total_steps) * 100, 1);
    }
}
