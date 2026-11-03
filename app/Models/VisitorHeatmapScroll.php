<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorHeatmapScroll extends Model
{
    use HasFactory;

    protected $table = 'visitor_heatmap_scrolls';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'page_path',
        'device_type',
        'max_scroll_percent',
        'viewport_height',
        'page_height',
        'created_at',
    ];

    protected $casts = [
        'max_scroll_percent' => 'integer',
        'viewport_height' => 'integer',
        'page_height' => 'integer',
        'created_at' => 'datetime',
    ];
}
