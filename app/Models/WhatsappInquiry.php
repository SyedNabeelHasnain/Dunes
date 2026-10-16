<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappInquiry extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'request_log_id',
        'name',
        'phone',
        'tour_name',
        'page_url',
        'message_text',
        'status',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mark inquiry lead as read.
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        if ($this->status === 'new' || empty($this->status)) {
            $this->status = 'read';
        }
        $this->read_at = now();
        $this->save();
    }
}
