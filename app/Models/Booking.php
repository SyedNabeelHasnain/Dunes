<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id', 'reference', 'tour_id', 'tier_id', 'tour_name', 'tier_name',
        'driver_id', 'vehicle_id', 'tour_date',
        'adults', 'children', 'infants', 'name', 'email', 'phone', 'pickup_location',
        'pickup_time_slot', 'pickup_status', 'driver_assigned_at', 'driver_notified_at',
        'guest_notified_at', 'driver_notes',
        'special_requests', 'coupon_id', 'coupon_code', 'discount_type', 'discount_rate',
        'discount_amount', 'original_total', 'subtotal', 'addons_total', 'total', 'currency',
        'status', 'payment_method', 'payment_status', 'payment_amount',
        'balance_due', 'ziina_payment_intent_id', 'ziina_status',
        'ziina_redirect_url', 'request_log_id', 'ip_address', 'ip_location',
        'gps_lat', 'gps_lng', 'gps_address', 'device_type', 'browser',
        'platform', 'user_agent', 'referrer', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content', 'is_verified',
    ];

    protected $casts = [
        'tour_date' => 'date',
        'driver_assigned_at' => 'datetime',
        'driver_notified_at' => 'datetime',
        'guest_notified_at' => 'datetime',
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'discount_rate' => 'float',
        'discount_amount' => 'float',
        'original_total' => 'float',
        'subtotal' => 'float',
        'addons_total' => 'float',
        'total' => 'float',
        'payment_amount' => 'float',
        'balance_due' => 'float',
        'is_verified' => 'boolean',
    ];

    /**
     * Backward-compatible accessor for notes -> special_requests.
     */
    public function getNotesAttribute(): ?string
    {
        return $this->special_requests;
    }

    /**
     * Backward-compatible accessor for discount -> discount_amount.
     */
    public function getDiscountAttribute(): float
    {
        return (float) ($this->discount_amount ?? 0);
    }

    /**
     * Accessor for pickup_time prioritizing assigned pickup_time_slot over tour default.
     */
    public function getPickupTimeAttribute(): ?string
    {
        return $this->pickup_time_slot ?: $this->tour?->pickup_time;
    }

    /**
     * Driver relationship.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Vehicle relationship.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Resolved driver name with backward compatibility for legacy DISPATCH notes.
     */
    public function getAssignedDriverNameAttribute(): ?string
    {
        if ($this->driver) {
            return $this->driver->name;
        }

        if ($this->special_requests && preg_match('/\[DISPATCH:\s*Driver:\s*([^\|]+)/i', $this->special_requests, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Resolved vehicle plate with backward compatibility for legacy DISPATCH notes.
     */
    public function getAssignedVehiclePlateAttribute(): ?string
    {
        if ($this->vehicle) {
            return $this->vehicle->short_plate;
        }

        if ($this->special_requests && preg_match('/Plate:\s*([^\]]+)\]/i', $this->special_requests, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function addons()
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }
}
