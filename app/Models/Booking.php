<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'tour_id', 'tier_id', 'tour_name', 'tier_name', 'tour_date',
        'adults', 'children', 'name', 'email', 'phone', 'pickup_location',
        'special_requests', 'subtotal', 'addons_total', 'total', 'currency',
        'status', 'payment_method', 'payment_status', 'payment_amount',
        'balance_due', 'ziina_payment_intent_id', 'ziina_status',
        'ziina_redirect_url', 'request_log_id', 'ip_address', 'ip_location',
        'gps_lat', 'gps_lng', 'gps_address', 'device_type', 'browser',
        'platform', 'user_agent', 'referrer', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content', 'is_verified'
    ];

    protected $casts = [
        'tour_date' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'subtotal' => 'float',
        'addons_total' => 'float',
        'total' => 'float',
        'payment_amount' => 'float',
        'balance_due' => 'float',
        'is_verified' => 'boolean'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    public function addons()
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }
}
