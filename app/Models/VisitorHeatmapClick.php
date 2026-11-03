<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorHeatmapClick extends Model
{
    use HasFactory;

    protected $table = 'visitor_heatmap_clicks';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'page_path',
        'device_type',
        'x_ratio',
        'y_ratio',
        'click_x',
        'click_y',
        'viewport_width',
        'viewport_height',
        'target_tag',
        'target_text',
        'created_at',
    ];

    protected $casts = [
        'x_ratio' => 'float',
        'y_ratio' => 'float',
        'click_x' => 'integer',
        'click_y' => 'integer',
        'viewport_width' => 'integer',
        'viewport_height' => 'integer',
        'created_at' => 'datetime',
    ];
}
