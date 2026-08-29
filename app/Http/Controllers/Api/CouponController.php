<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Tour;
use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->ensureSchema();
    }

    /**
     * Ensure coupons table exists.
     */
    protected function ensureSchema(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('coupons')) {
                $migration = require database_path('migrations/2026_08_30_000001_create_coupons_table.php');
                $migration->up();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Coupon schema auto-creation error: " . $e->getMessage());
        }
    }

    /**
     * Real-time coupon validation endpoint.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $this->ensureSchema();
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
            'tour_id' => 'nullable|integer',
            'tier_id' => 'nullable|integer',
            'email' => 'nullable|email|max:255',
            'adults' => 'nullable|integer|min:1',
            'date' => 'nullable|date',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float)$request->input('subtotal');
        $tourId = $request->filled('tour_id') ? (int)$request->input('tour_id') : null;
        $tierId = $request->filled('tier_id') ? (int)$request->input('tier_id') : null;
        $email = $request->input('email');
        $adults = (int)$request->input('adults', 1);
        $tourDate = $request->input('date');

        // If client sent 0 subtotal but specified tour & tier, resolve price from database
        if ($subtotal <= 0 && $tourId && $tierId) {
            $pricing = \Illuminate\Support\Facades\DB::table('tour_tiers')
                ->where('tour_id', $tourId)
                ->where('tier_id', $tierId)
                ->first();
            if ($pricing && (float)$pricing->price > 0) {
                $subtotal = (float)$pricing->price * max(1, $adults);
            }
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code. Please check and try again.'
            ], 422);
        }

        $check = $coupon->validateEligibility($subtotal, $tourId, $tierId, $email, $adults, $tourDate);

        if (!$check['valid']) {
            return response()->json([
                'success' => false,
                'message' => $check['message']
            ], 422);
        }

        $discountAmount = $check['discount'];
        $newTotal = max(0, round($subtotal - $discountAmount, 2));

        // Format savings text
        $savingsText = '';
        if ($coupon->discount_type === 'percentage') {
            if ($discountAmount > 0) {
                $savingsText = "AED " . number_format($discountAmount, 2) . " saved (" . (int)$coupon->discount_value . "% OFF)";
            } else {
                $savingsText = (int)$coupon->discount_value . "% OFF Discount Applied";
            }
        } elseif ($coupon->discount_type === 'per_person') {
            if ($discountAmount > 0) {
                $savingsText = "AED " . number_format($discountAmount, 2) . " saved (AED " . number_format($coupon->discount_value, 2) . "/guest)";
            } else {
                $savingsText = "AED " . number_format($coupon->discount_value, 2) . "/guest OFF Applied";
            }
        } else {
            if ($discountAmount > 0) {
                $savingsText = "AED " . number_format($discountAmount, 2) . " saved (Flat Discount)";
            } else {
                $savingsText = "AED " . number_format($coupon->discount_value, 2) . " Flat Discount Applied";
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Promo code applied successfully!',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float)$coupon->discount_value,
                'max_discount' => $coupon->max_discount ? (float)$coupon->max_discount : null,
                'discount_amount' => $discountAmount,
                'original_total' => $subtotal,
                'new_total' => $newTotal,
                'savings_text' => $savingsText,
            ]
        ]);
    }

    /**
     * Get featured public active promo codes (for banners / marketing pills).
     */
    public function featured(): JsonResponse
    {
        $featured = Coupon::featured()
            ->valid()
            ->select('id', 'code', 'name', 'description', 'discount_type', 'discount_value', 'min_spend')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'featured' => $featured,
            'coupons' => $featured,
        ]);
    }
}
