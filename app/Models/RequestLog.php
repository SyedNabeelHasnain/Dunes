<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'form_name', 'request_timestamp',
        'request_method', 'request_uri', 'query_string', 'host',
        'server_protocol', 'https_flag', 'client_ip', 'ip_version',
        'ip_hash', 'country', 'region', 'city', 'latitude', 'longitude',
        'timezone', 'isp', 'organization', 'asn', 'hosting_flag',
        'proxy_flag', 'user_agent', 'device_type', 'os_name', 'os_version',
        'browser_name', 'browser_version', 'bot_indicator', 'accept_language',
        'accept_encoding', 'referrer_url', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content', 'landing_page',
        'session_id', 'session_start_time', 'session_end_time',
        'session_duration_seconds', 'pages_viewed_count', 'form_load_timestamp',
        'form_submit_timestamp', 'form_completion_seconds', 'repeat_visit_flag',
        'google_maps_link', 'gps_consent_flag', 'gps_latitude', 'gps_longitude',
        'gps_accuracy', 'gps_altitude', 'gps_heading', 'gps_speed',
        'gps_timestamp', 'gps_source'
    ];

    protected $casts = [
        'request_timestamp' => 'datetime',
        'session_start_time' => 'datetime',
        'session_end_time' => 'datetime',
        'form_load_timestamp' => 'datetime',
        'form_submit_timestamp' => 'datetime',
        'session_duration_seconds' => 'integer',
        'pages_viewed_count' => 'integer',
        'form_completion_seconds' => 'integer'
    ];
}
