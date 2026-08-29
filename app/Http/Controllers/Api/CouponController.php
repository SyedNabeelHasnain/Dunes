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
            'date' => 'nullable|date',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float)$request->input('subtotal');
        $tourId = $request->filled('tour_id') ? (int)$request->input('tour_id') : null;
        $tierId = $request->filled('tier_id') ? (int)$request->input('tier_id') : null;
        $email = $request->input('email');
        $adults = (int)$request->input('adults', 1);
        $tourDate = $request->input('date');

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
            $savingsText = "AED " . number_format($discountAmount, 2) . " saved (" . (float)$coupon->discount_value . "% OFF)";
        } elseif ($coupon->discount_type === 'per_person') {
            $savingsText = "AED " . number_format($discountAmount, 2) . " saved (AED " . number_format($coupon->discount_value, 2) . "/guest)";
        } else {
            $savingsText = "AED " . number_format($discountAmount, 2) . " saved (Flat Discount)";
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
