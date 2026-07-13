<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappInquiry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'request_log_id', 'name', 'phone', 'tour_name', 'page_url', 'message_text'
    ];
}
