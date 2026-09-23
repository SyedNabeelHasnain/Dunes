<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_spend',
        'max_discount',
        'min_guests',
        'usage_limit',
        'usage_limit_per_user',
        'valid_from',
        'valid_until',
        'tour_date_from',
        'tour_date_to',
        'tour_id',
        'tier_id',
        'first_time_only',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_guests' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'tour_date_from' => 'date',
        'tour_date_to' => 'date',
        'first_time_only' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        $now = now();

        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
    }

    public function scopeFeatured($query)
    {
        return $query->active()->where('is_featured', true);
    }

    /**
     * Validate eligibility against booking parameters.
     */
    public function validateEligibility(float $subtotal, ?int $tourId = null, ?int $tierId = null, ?string $email = null, int $guests = 1, ?string $tourDate = null): array
    {
        if ($this->status !== 'active') {
            return ['valid' => false, 'message' => 'This promo code is currently inactive.'];
        }

        $now = now();

        // 1. Redemption window check
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return ['valid' => false, 'message' => 'This promo code will become active on '.$this->valid_from->format('M j, Y').'.'];
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return ['valid' => false, 'message' => 'This promo code expired on '.$this->valid_until->format('M j, Y').'.'];
        }

        // 2. Tour Date restriction check
        if (! empty($tourDate)) {
            $tDate = Carbon::parse($tourDate)->startOfDay();
            if ($this->tour_date_from && $tDate->lt(Carbon::parse($this->tour_date_from)->startOfDay())) {
                return ['valid' => false, 'message' => 'This promo is only valid for tours starting on or after '.Carbon::parse($this->tour_date_from)->format('M j, Y').'.'];
            }
            if ($this->tour_date_to && $tDate->gt(Carbon::parse($this->tour_date_to)->startOfDay())) {
                return ['valid' => false, 'message' => 'This promo is only valid for tours booked up to '.Carbon::parse($this->tour_date_to)->format('M j, Y').'.'];
            }
        }

        // 3. Tour specific restriction
        if ($this->tour_id && ((int) $this->tour_id !== (int) $tourId)) {
            $targetTour = Tour::find($this->tour_id);
            $tourName = $targetTour ? $targetTour->name : 'a specific tour';

            return ['valid' => false, 'message' => "This promo code is exclusive to the '{$tourName}'. Please select that tour to apply."];
        }

        // 4. Tier specific restriction
        if ($this->tier_id && ((int) $this->tier_id !== (int) $tierId)) {
            $targetTier = Tier::find($this->tier_id);
            $tierName = $targetTier ? $targetTier->display_name : 'a specific package';

            return ['valid' => false, 'message' => "This promo code is exclusive to the '{$tierName}' package."];
        }

        // 5. Minimum spend requirement
        if ($this->min_spend > 0 && $subtotal < $this->min_spend) {
            return ['valid' => false, 'message' => 'Minimum booking value of AED '.number_format($this->min_spend, 2).' required. (Current: AED '.number_format($subtotal, 2).')'];
        }

        // 6. Minimum guests count requirement
        if ($this->min_guests > 1 && $guests < $this->min_guests) {
            return ['valid' => false, 'message' => "This promo code requires a minimum of {$this->min_guests} guests."];
        }

        // 7. Global usage limit check
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'This promo code has reached its maximum redemption limit.'];
        }

        // 8. Per-user redemption limit & First-time check
        if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userEmail = strtolower(trim($email));

            if ($this->first_time_only) {
                $pastBookings = Booking::where('email', $userEmail)
                    ->where(function ($q) {
                        $q->whereIn('status', ['confirmed', 'completed'])
                            ->orWhereIn('payment_status', ['paid', 'partial', 'advance_paid']);
                    })
                    ->exists();
                if ($pastBookings) {
                    return ['valid' => false, 'message' => 'This promo code is exclusively for first-time customers.'];
                }
            }

            if ($this->usage_limit_per_user > 0) {
                $usedByUser = CouponUsage::where('coupon_id', $this->id)
                    ->where('customer_email', $userEmail)
                    ->count();
                if ($usedByUser >= $this->usage_limit_per_user) {
                    return ['valid' => false, 'message' => "You have already reached the maximum usage limit ({$this->usage_limit_per_user}) for this promo code."];
                }
            }
        }

        // Calculate discount
        $discountAmount = $this->calculateDiscount($subtotal, $guests);

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully!',
            'discount' => $discountAmount,
        ];
    }

    /**
     * Calculate exact discount in AED based on coupon configuration.
     */
    public function calculateDiscount(float $subtotal, int $guests = 1): float
    {
        $discount = 0.00;

        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * $this->discount_value) / 100;
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } elseif ($this->discount_type === 'fixed') {
            $discount = (float) $this->discount_value;
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, (float) $this->max_discount);
            }
            $discount = min($subtotal, $discount);
        } elseif ($this->discount_type === 'per_person') {
            $calculated = (float) $this->discount_value * max(1, $guests);
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $calculated = min($calculated, (float) $this->max_discount);
            }
            $discount = min($subtotal, $calculated);
        }

        return round($discount, 2);
    }

    /**
     * Resilient coupon lookup with self-healing fallback for core system promotional codes.
     */
    public static function findByCode(?string $code): ?self
    {
        if (empty($code)) {
            return null;
        }

        $normalized = strtoupper(trim($code));
        $conciergeActive = false;
        $exitIntentActive = false;
        try {
            $conciergeActive = DB::table('settings')
                ->where('setting_key', 'concierge_promo_active')
                ->value('setting_value') === '1';
            $exitIntentActive = DB::table('settings')
                ->where('setting_key', 'exit_intent_promo_active')
                ->value('setting_value') === '1';
        } catch (\Throwable $e) {
            $conciergeActive = false;
            $exitIntentActive = false;
        }

        $coupon = static::where('code', $normalized)->first();

        if ($coupon) {
            if ($normalized === 'MATCH5') {
                $coupon->status = $conciergeActive ? 'active' : 'inactive';
            }
            if ($normalized === 'SAVE5') {
                $coupon->status = $exitIntentActive ? 'active' : 'inactive';
            }

            return $coupon;
        }

        // Automatic fallback for system-defined gamified codes (MATCH5, SAVE5)
        if (in_array($normalized, ['MATCH5', 'SAVE5'])) {
            $status = ($normalized === 'MATCH5')
                ? ($conciergeActive ? 'active' : 'inactive')
                : ($exitIntentActive ? 'active' : 'inactive');
            try {
                return static::firstOrCreate(
                    ['code' => $normalized],
                    [
                        'name' => $normalized === 'MATCH5' ? 'Safari Match Concierge 5% Discount' : 'Exit-Intent Cart Saver 5% Discount',
                        'description' => 'System discount code (5% off)',
                        'discount_type' => 'percentage',
                        'discount_value' => 5.00,
                        'min_spend' => 0.00,
                        'min_guests' => 1,
                        'usage_limit_per_user' => 10,
                        'status' => $status,
                        'valid_from' => now()->subDay(),
                        'is_featured' => true,
                    ]
                );
            } catch (\Throwable $e) {
                $c = new static([
                    'code' => $normalized,
                    'name' => $normalized === 'MATCH5' ? 'Safari Match Concierge 5% Discount' : 'Exit-Intent Cart Saver 5% Discount',
                    'discount_type' => 'percentage',
                    'discount_value' => 5.00,
                    'status' => $status,
                ]);

                return $c;
            }
        }

        // Automatic fallback for official first-time visitor 25% promo codes (including dynamic FIRST25-* codes)
        if (in_array($normalized, ['DUNESWELCOME', 'FIRST25']) || str_starts_with($normalized, 'FIRST25-')) {
            try {
                return static::firstOrCreate(
                    ['code' => $normalized],
                    [
                        'name' => $normalized === 'DUNESWELCOME' ? 'First-Time Welcome 25% Promo (Top Banner)' : 'First-Time Guest 25% Promo',
                        'description' => 'Official 25% welcome promo code for first-time visitors',
                        'discount_type' => 'percentage',
                        'discount_value' => 25.00,
                        'min_spend' => 0.00,
                        'min_guests' => 1,
                        'usage_limit_per_user' => 10,
                        'status' => 'active',
                        'valid_from' => now()->subDay(),
                        'first_time_only' => true,
                        'is_featured' => true,
                    ]
                );
            } catch (\Throwable $e) {
                return new static([
                    'code' => $normalized,
                    'name' => $normalized === 'DUNESWELCOME' ? 'First-Time Welcome 25% Promo (Top Banner)' : 'First-Time Guest 25% Promo',
                    'discount_type' => 'percentage',
                    'discount_value' => 25.00,
                    'status' => 'active',
                    'first_time_only' => true,
                ]);
            }
        }

        return null;
    }
}
