<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamEvent extends Model
{
    public $timestamps = false;

    protected $table = 'spam_events';

    protected $fillable = [
        'channel',
        'ip_address',
        'email',
        'name_snippet',
        'subject_snippet',
        'spam_score',
        'action_taken',
        'reasons',
        'user_agent',
        'request_path',
        'created_at',
    ];

    protected $casts = [
        'spam_score' => 'integer',
        'reasons' => 'array',
        'created_at' => 'datetime',
    ];
}
