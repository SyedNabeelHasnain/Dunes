<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'payment_intent_id', 'amount', 'currency', 'status',
        'payment_url', 'notes', 'customer_name', 'customer_email',
        'customer_phone', 'description'
    ];

    protected $casts = [
        'amount' => 'float'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
