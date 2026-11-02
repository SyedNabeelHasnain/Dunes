<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status',
        'request_log_id', 'ip_address', 'is_verified',
        'traffic_channel', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_term', 'utm_content', 'gclid', 'fbclid',
        'initial_referrer', 'landing_page',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];
}
