<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'booking_id',
        'booking_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'discount_amount',
        'order_subtotal',
        'order_final_total',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'float',
        'order_subtotal' => 'float',
        'order_final_total' => 'float',
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
