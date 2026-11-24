<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyConsent extends Model
{
    protected $table = 'privacy_consents';

    protected $fillable = [
        'consent_id',
        'session_id',
        'ip_hash',
        'locale',
        'necessary',
        'analytics',
        'marketing',
        'functional',
        'consent_version',
        'action',
        'page_url',
        'user_agent',
        'consented_at',
    ];

    protected $casts = [
        'necessary' => 'boolean',
        'analytics' => 'boolean',
        'marketing' => 'boolean',
        'functional' => 'boolean',
        'consented_at' => 'datetime',
    ];
}
