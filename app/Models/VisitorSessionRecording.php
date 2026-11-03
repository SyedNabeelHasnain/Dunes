<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class VisitorSessionRecording extends Model
{
    use HasFactory;

    protected $table = 'visitor_session_recordings';

    protected $fillable = [
        'session_id',
        'client_ip',
        'country',
        'city',
        'device_type',
        'browser_name',
        'os_name',
        'landing_page',
        'exit_page',
        'duration_seconds',
        'events_count',
        'storage_path',
        'file_size_bytes',
        'is_starred',
        'has_inquiry',
        'status',
        'started_at',
        'last_event_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'events_count' => 'integer',
        'file_size_bytes' => 'integer',
        'is_starred' => 'boolean',
        'has_inquiry' => 'boolean',
        'started_at' => 'datetime',
        'last_event_at' => 'datetime',
    ];

    /**
     * Get human formatted duration (e.g. 2m 14s or 45s).
     */
    public function getFormattedDurationAttribute(): string
    {
        $sec = (int) $this->duration_seconds;
        if ($sec < 60) {
            return "{$sec}s";
        }
        $m = floor($sec / 60);
        $s = $sec % 60;

        return "{$m}m {$s}s";
    }

    /**
     * Get human formatted file size (KB / MB).
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes <= 0) {
            return '0 KB';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * Get device icon class for UI.
     */
    public function getDeviceIconAttribute(): string
    {
        return match (strtolower($this->device_type ?? '')) {
            'mobile' => 'bi-phone',
            'tablet' => 'bi-tablet',
            default => 'bi-laptop',
        };
    }
}
