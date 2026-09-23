<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Tier;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function __construct() {}

    /**
     * Real-time coupon validation endpoint.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
            'tour_id' => 'nullable|integer',
            'tier_id' => 'nullable|integer',
            'email' => 'nullable|email|max:255',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'date' => 'nullable|date',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal');
        $tourId = $request->filled('tour_id') ? (int) $request->input('tour_id') : null;
        $tierId = $request->filled('tier_id') ? (int) $request->input('tier_id') : null;
        $email = $request->input('email');
        $adults = (int) $request->input('adults', 1);
        $children = (int) $request->input('children', 0);
        $totalGuests = (int) $request->input('guests', max(1, $adults + $children));
        $tourDate = $request->input('date');

        // If client sent 0 subtotal but specified tour & tier, resolve price from database
        if ($subtotal <= 0 && $tourId && $tierId) {
            $pricing = DB::table('tour_tiers')
                ->where('tour_id', $tourId)
                ->where('tier_id', $tierId)
                ->first();
            if ($pricing && (float) $pricing->price > 0) {
                $priceType = strtolower($pricing->price_type ?? 'per person');
                if (in_array($priceType, ['per buggy', 'per vehicle', 'per group', 'private'])) {
                    $subtotal = (float) $pricing->price;
                } else {
                    $childPrice = round((float) $pricing->price * 0.70, 2);
                    $subtotal = ((float) $pricing->price * $adults) + ($childPrice * $children);
                }
            }
        }

        $coupon = Coupon::findByCode($code);

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code. Please check for typos and try again.',
            ], 422);
        }

        $check = $coupon->validateEligibility($subtotal, $tourId, $tierId, $email, $totalGuests, $tourDate);

        if (! $check['valid']) {
            return response()->json([
                'success' => false,
                'message' => $check['message'],
            ], 422);
        }

        $discountAmount = $check['discount'];
        $newTotal = max(0, round($subtotal - $discountAmount, 2));

        // Format savings text
        $savingsText = '';
        if ($coupon->discount_type === 'percentage') {
            if ($discountAmount > 0) {
                $savingsText = 'AED '.number_format($discountAmount, 2).' saved ('.(int) $coupon->discount_value.'% OFF)';
            } else {
                $savingsText = (int) $coupon->discount_value.'% OFF Discount Applied';
            }
        } elseif ($coupon->discount_type === 'per_person') {
            if ($discountAmount > 0) {
                $savingsText = 'AED '.number_format($discountAmount, 2).' saved (AED '.number_format($coupon->discount_value, 2).'/guest)';
            } else {
                $savingsText = 'AED '.number_format($coupon->discount_value, 2).'/guest OFF Applied';
            }
        } else {
            if ($discountAmount > 0) {
                $savingsText = 'AED '.number_format($discountAmount, 2).' saved (Flat Discount)';
            } else {
                $savingsText = 'AED '.number_format($coupon->discount_value, 2).' Flat Discount Applied';
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
                'discount_value' => (float) $coupon->discount_value,
                'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
                'discount_amount' => $discountAmount,
                'original_total' => $subtotal,
                'new_total' => $newTotal,
                'savings_text' => $savingsText,
                'formatted_discount' => 'AED '.number_format($discountAmount, 2),
                'formatted_new_total' => 'AED '.number_format($newTotal, 2),
                'formatted_original_total' => 'AED '.number_format($subtotal, 2),
            ],
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
