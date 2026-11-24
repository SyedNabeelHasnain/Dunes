<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebVitalsMetric extends Model
{
    protected $table = 'web_vitals_metrics';

    protected $fillable = [
        'page_path',
        'route_name',
        'locale',
        'device_type',
        'metric_name',
        'metric_value',
        'rating',
        'navigation_type',
        'connection_type',
        'session_id',
        'recorded_at',
    ];

    protected $casts = [
        'metric_value' => 'float',
        'recorded_at' => 'datetime',
    ];
}
