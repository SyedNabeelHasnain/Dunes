<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Setting;
use App\Models\Tier;
use App\Models\Tour;
use App\Services\MetaCapiService;
use App\Services\VisitorTrackerService;
use App\Services\ZiinaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Mail\BookingNotification;
use App\Mail\BookingAdminNotification;
use App\Services\SettingsService;

class BookingController extends Controller
{
    protected $ziina;
    protected $tracker;
    protected $metaCapi;

    public function __construct(ZiinaPaymentService $ziina, VisitorTrackerService $tracker, MetaCapiService $metaCapi)
    {
        $this->ziina = $ziina;
        $this->tracker = $tracker;
        $this->metaCapi = $metaCapi;
    }

    /**
     * Submit Booking Checkout.
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'tour_id' => 'required|integer',
            'tier_id' => 'required|integer',
            'date' => 'required|date|after_or_equal:today',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'location' => 'required|string|max:500',
            'requests' => 'nullable|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'payment_method' => 'required|string|in:cash,advance,full',
            'addons' => 'nullable|array',
            'addons.*' => 'integer',
        ]);

        $tourId = (int)$request->input('tour_id');
        $tierId = (int)$request->input('tier_id');
        $date = $request->input('date');
        $adults = (int)$request->input('adults');
        $children = (int)$request->input('children', 0);
        $location = trim($request->input('location'));
        $requests = trim($request->input('requests', ''));
        $name = trim($request->input('name'));
        $email = trim(strtolower($request->input('email')));
        $phone = trim($request->input('phone'));
        $paymentMethod = $request->input('payment_method');

        $tour = Tour::find($tourId);
        $tier = Tier::find($tierId);

        if (!$tour || !$tier) {
            return response()->json(['success' => false, 'message' => 'Invalid tour or package selection'], 400);
        }

        // Fetch price for this specific tour tier
        $pricing = \Illuminate\Support\Facades\DB::table('tour_tiers')
            ->where('tour_id', $tourId)
            ->where('tier_id', $tierId)
            ->first();

        if (!$pricing) {
            return response()->json(['success' => false, 'message' => 'Pricing configuration not found for selected package'], 400);
        }

        $price = (float)$pricing->price;
        // Children get a 30% discount (they pay 70% of the adult price)
        $subtotal = ($price * $adults) + ($price * 0.7 * $children);

        // Addons total
        $addonsTotal = 0;
        $selectedAddons = [];
        $addonsInput = $request->input('addons', []);
        
        if (!empty($addonsInput)) {
            $addonsData = \Illuminate\Support\Facades\DB::table('addons')
                ->join('tour_addons', 'addons.id', '=', 'tour_addons.addon_id')
                ->where('tour_addons.tour_id', $tourId)
                ->whereIn('addons.id', $addonsInput)
                ->select('addons.id', 'addons.name', 'tour_addons.price')
                ->get();

            foreach ($addonsData as $addon) {
                $selectedAddons[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => (float)$addon->price
                ];
                $addonsTotal += (float)$addon->price;
            }
        }

        $rawSubtotal = $subtotal + $addonsTotal;
        $originalTotal = $rawSubtotal;
        $discountAmount = 0.00;
        $appliedCoupon = null;

        // Process promo code if provided
        $couponCodeInput = strtoupper(trim($request->input('coupon_code', '')));
        if (!empty($couponCodeInput)) {
            $coupon = Coupon::where('code', $couponCodeInput)->first();
            if ($coupon) {
                $check = $coupon->validateEligibility($rawSubtotal, $tourId, $tierId, $email, $adults, $date);
                if ($check['valid']) {
                    $appliedCoupon = $coupon;
                    $discountAmount = (float)$check['discount'];
                }
            }
        }

        $total = max(0, round($originalTotal - $discountAmount, 2));

        // Force cash if Ziina is not active
        if (!$this->ziina->isActive()) {
            $paymentMethod = 'cash';
        }

        $payNow = 0;
        $balanceDue = $total;

        if ($paymentMethod === 'advance') {
            $advancePercent = $this->ziina->getAdvancePercent();
            $payNow = round(($total * $advancePercent) / 100, 2);
            $balanceDue = round($total - $payNow, 2);
        } elseif ($paymentMethod === 'full') {
            $payNow = round($total, 2);
            $balanceDue = 0;
        }

        // Email Verification check
        $sessionVerified = session()->has('email_verified_' . md5($email));
        $isVerified = $sessionVerified || 
            \App\Models\VerifiedEmail::where('email', $email)->exists() ||
            Booking::where('email', $email)->where('is_verified', true)->exists() ||
            \App\Models\Contact::where('email', $email)->where('is_verified', true)->exists();

        if ($paymentMethod !== 'cash') {
            if ($payNow < 2.00) {
                return response()->json(['success' => false, 'message' => 'Minimum online payment amount is AED 2.00'], 400);
            }
        }

        // Generate Unique Booking Reference DDT + ymd + 5 random chars
        $ref = 'DDT' . date('ymd') . strtoupper(\Illuminate\Support\Str::random(5));

        // Request context logging
        $gpsPost = [
            'gps_consent' => $request->input('gps_consent'),
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'gps_accuracy' => $request->input('gps_accuracy'),
            'gps_timestamp' => $request->input('gps_timestamp'),
            'gps_source' => $request->input('gps_source'),
            'gps_altitude' => $request->input('gps_altitude'),
            'gps_heading' => $request->input('gps_heading'),
            'gps_speed' => $request->input('gps_speed'),
        ];
        $ctx = $this->tracker->collectRequestContext('booking', $gpsPost);

        try {
            // Save Booking
            $booking = Booking::create([
                'reference' => $ref,
                'tour_id' => $tourId,
                'tier_id' => $tierId,
                'tour_name' => $tour->name,
                'tier_name' => $tier->display_name,
                'tour_date' => $date,
                'adults' => $adults,
                'children' => $children,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'pickup_location' => $location,
                'special_requests' => $requests,
                'coupon_id' => $appliedCoupon ? $appliedCoupon->id : null,
                'coupon_code' => $appliedCoupon ? $appliedCoupon->code : null,
                'discount_type' => $appliedCoupon ? $appliedCoupon->discount_type : null,
                'discount_rate' => $appliedCoupon ? (float)$appliedCoupon->discount_value : 0.00,
                'discount_amount' => $discountAmount,
                'original_total' => $originalTotal,
                'subtotal' => $subtotal,
                'addons_total' => $addonsTotal,
                'total' => $total,
                'currency' => 'AED',
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'payment_amount' => $payNow,
                'balance_due' => $balanceDue,
                'ip_address' => $ctx['client_ip'],
                'ip_location' => implode(', ', array_filter([$ctx['city'] ?? '', $ctx['region'] ?? '', $ctx['country'] ?? ''])),
                'gps_lat' => $ctx['gps_latitude'],
                'gps_lng' => $ctx['gps_longitude'],
                'gps_address' => $ctx['gps_source'] ?? 'Not Available',
                'device_type' => $ctx['device_type'],
                'browser' => $ctx['browser_name'],
                'platform' => $ctx['os_name'],
                'user_agent' => $ctx['user_agent'],
                'referrer' => $ctx['referrer_url'],
                'utm_source' => $ctx['utm_source'],
                'utm_medium' => $ctx['utm_medium'],
                'utm_campaign' => $ctx['utm_campaign'],
                'utm_term' => $ctx['utm_term'],
                'utm_content' => $ctx['utm_content'],
                'is_verified' => $isVerified,
            ]);

            // Record Coupon Usage & increment counter
            if ($appliedCoupon && $discountAmount > 0) {
                try {
                    CouponUsage::create([
                        'coupon_id' => $appliedCoupon->id,
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->reference,
                        'customer_name' => $name,
                        'customer_email' => strtolower($email),
                        'customer_phone' => $phone,
                        'discount_amount' => $discountAmount,
                        'order_subtotal' => $originalTotal,
                        'order_final_total' => $total,
                        'used_at' => now(),
                    ]);
                    $appliedCoupon->increment('used_count');
                } catch (\Throwable $e) {
                    Log::error("Failed to record coupon usage: " . $e->getMessage());
                }
            }

            // Save Booking Addons
            foreach ($selectedAddons as $sa) {
                BookingAddon::create([
                    'booking_id' => $booking->id,
                    'addon_id' => $sa['id'],
                    'addon_name' => $sa['name'],
                    'quantity' => 1,
                    'price' => $sa['price'],
                ]);
            }

            // Log detailed context in request_logs table
            $logId = $this->tracker->logRequest('booking', $booking->id, 'booking', $ctx);
            if ($logId) {
                $booking->update(['request_log_id' => $logId]);
            }

            // Cash checkout is complete instantly
            if ($paymentMethod === 'cash') {
                $this->sendEmailNotification('booking_cash', $booking);
                
                // Trigger instant purchase conversion API (since cash completes without payment step)
                $custom = [
                    'value' => (float)$booking->total,
                    'currency' => 'AED',
                    'content_ids' => ['TOUR-' . $booking->tour_id],
                    'content_type' => 'product',
                    'contents' => [['id' => 'TOUR-' . $booking->tour_id, 'quantity' => 1]],
                    'coupon' => $booking->coupon_code,
                    'discount_amount' => (float)$booking->discount_amount,
                ];
                $this->metaCapi->dispatchEvent('Purchase', [
                    'event_id' => 'BOOK-' . $booking->reference,
                    'email' => $booking->email,
                    'phone' => $booking->phone,
                    'custom_data' => $custom
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your booking has been received.',
                    'reference' => $booking->reference,
                    'redirect_url' => route('booking.thankyou', ['ref' => $booking->reference])
                ]);
            }

            // Generate Ziina Payment Link
            $successUrl = route('booking.thankyou', ['pi' => '{PAYMENT_INTENT_ID}']);
            $cancelUrl = route('booking.cancel', ['pi' => '{PAYMENT_INTENT_ID}']);
            $messageStr = "Booking {$booking->reference} - {$booking->tour_name}";

            $intent = $this->ziina->createPaymentIntent($payNow, 'AED', $successUrl, $cancelUrl, $messageStr);

            if (isset($intent['error'])) {
                $booking->update(['payment_status' => 'failed']);
                return response()->json(['success' => false, 'message' => $intent['error']], 400);
            }

            if (empty($intent['redirect_url'])) {
                $booking->update(['payment_status' => 'failed']);
                return response()->json(['success' => false, 'message' => 'Payment link could not be generated.'], 400);
            }

            $booking->update([
                'ziina_payment_intent_id' => $intent['id'] ?? null,
                'ziina_status' => $intent['status'] ?? null,
                'ziina_redirect_url' => $intent['redirect_url'] ?? null
            ]);

            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $intent['id'],
                'amount' => $payNow,
                'currency' => 'AED',
                'status' => $intent['status'] ?? 'pending',
                'payment_url' => $intent['redirect_url']
            ]);

            // Dispatch InitiateCheckout event to Conversions API
            $custom = [
                'value' => (float)$booking->total,
                'currency' => 'AED',
                'content_ids' => ['TOUR-' . $booking->tour_id],
                'content_type' => 'product',
                'contents' => [['id' => 'TOUR-' . $booking->tour_id, 'quantity' => 1]]
            ];
            $this->metaCapi->dispatchEvent('InitiateCheckout', [
                'event_id' => 'INIT-' . $booking->reference,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'custom_data' => $custom
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => $intent['redirect_url']
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process checkout: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving your booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Thank You redirect landing page.
     */
    public function thankyou(Request $request)
    {
        $pi = $request->input('pi', '');
        $ref = $request->input('ref', '');

        $booking = null;
        $paymentStatus = '';
        $method = 'cash';

        if (!empty($pi)) {
            $booking = Booking::where('ziina_payment_intent_id', $pi)->first();
            $paymentRecord = null;

            if (!$booking) {
                $paymentRecord = BookingPayment::where('payment_intent_id', $pi)->first();
                if ($paymentRecord && $paymentRecord->booking) {
                    $booking = $paymentRecord->booking;
                }
            } else {
                $paymentRecord = BookingPayment::where('payment_intent_id', $pi)->first();
            }
            
            if ($booking) {
                $method = $booking->payment_method;
                $intent = $this->ziina->fetchPaymentIntent($pi);
                $intentStatus = $intent['status'] ?? '';

                if ($intentStatus === 'completed') {
                    $wasCompleted = ($paymentRecord && $paymentRecord->status === 'completed');
                    
                    if ($paymentRecord && !$wasCompleted) {
                        $paymentRecord->update(['status' => 'completed']);
                    }

                    $totalPaid = BookingPayment::where('booking_id', $booking->id)
                        ->where('status', 'completed')
                        ->sum('amount');
                    
                    if ($totalPaid <= 0 && isset($intent['amount'])) {
                        $totalPaid = (float)($intent['amount'] / 100);
                    }

                    $remBalance = max(0, (float)$booking->total - (float)$totalPaid);
                    $newStatus = ($remBalance <= 0) ? 'paid' : (($method === 'advance' || $totalPaid > 0) ? 'partial' : 'paid');

                    if ($booking->payment_status !== $newStatus || $booking->balance_due != $remBalance) {
                        $booking->update([
                            'payment_status' => $newStatus,
                            'ziina_status' => $intentStatus,
                            'status' => 'confirmed',
                            'balance_due' => $remBalance,
                            'payment_amount' => $totalPaid,
                        ]);

                        // Send booking confirmation email on first completion
                        if (!$wasCompleted) {
                            if ($newStatus === 'partial') {
                                $this->sendEmailNotification('booking_advance', $booking);
                            } else {
                                $this->sendEmailNotification('booking_full', $booking);
                            }
                        }

                        // Dispatch Purchase Event to Meta Conversions API
                        $custom = [
                            'value' => (float)$booking->total,
                            'currency' => 'AED',
                            'content_ids' => ['TOUR-' . $booking->tour_id],
                            'content_type' => 'product',
                            'contents' => [['id' => 'TOUR-' . $booking->tour_id, 'quantity' => 1]]
                        ];
                        $this->metaCapi->dispatchEvent('Purchase', [
                            'event_id' => 'BOOK-' . $booking->reference,
                            'email' => $booking->email,
                            'phone' => $booking->phone,
                            'custom_data' => $custom
                        ]);
                    }
                    $paymentStatus = 'completed';
                } elseif ($intentStatus === 'failed') {
                    $booking->update([
                        'payment_status' => 'failed',
                        'ziina_status' => $intentStatus
                    ]);
                    
                    BookingPayment::where('payment_intent_id', $pi)->update([
                        'status' => $intentStatus
                    ]);
                    
                    $paymentStatus = 'failed';
                } else {
                    $booking->update([
                        'ziina_status' => $intentStatus
                    ]);
                    
                    BookingPayment::where('payment_intent_id', $pi)->update([
                        'status' => $intentStatus
                    ]);
                    
                    $paymentStatus = 'pending';
                }
            }
        } elseif (!empty($ref)) {
            $booking = Booking::where('reference', $ref)->first();
            if ($booking) {
                $method = $booking->payment_method;
            }
        }

        return view('thankyou', compact('booking', 'paymentStatus', 'method'));
    }

    /**
     * Payment Cancel landing page.
     */
    public function paymentCancel(Request $request)
    {
        $pi = $request->input('pi', '');
        $booking = null;

        if (!empty($pi)) {
            $booking = Booking::where('ziina_payment_intent_id', $pi)->first();
            if ($booking) {
                $booking->update([
                    'payment_status' => 'cancelled',
                    'ziina_status' => 'cancelled'
                ]);

                BookingPayment::where('payment_intent_id', $pi)->update([
                    'status' => 'cancelled'
                ]);
            }
        }

        return view('payment-cancel', compact('booking'));
    }

    /**
     * Helper to render HTML and send notifications.
     */
    protected function sendEmailNotification(string $type, Booking $booking): void
    {
        try {
            $settings = app(SettingsService::class);
            $fromEmail = $settings->getFromEmail();
            $adminEmail = $settings->getAdminEmail();
            $ccEmails = $settings->getCcEmails();
            $bccEmails = $settings->getBccEmails();

            // Send to customer
            try {
                Mail::to($booking->email)
                    ->send((new BookingNotification($type, $booking))->from($fromEmail, 'Dunes Discovery Tourism'));
            } catch (\Throwable $e) {
                Log::error("Failed to send customer booking email for {$booking->reference}: " . $e->getMessage());
            }

            // Send to admin
            try {
                $adminMail = (new BookingAdminNotification($booking))->from($fromEmail, 'Dunes Discovery Tourism');
                if (!empty($ccEmails)) $adminMail->cc($ccEmails);
                if (!empty($bccEmails)) $adminMail->bcc($bccEmails);
                Mail::to($adminEmail)->send($adminMail);
            } catch (\Throwable $e) {
                Log::error("Failed to send admin booking email for {$booking->reference}: " . $e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to prepare booking email for {$booking->reference}: " . $e->getMessage());
        }
    }
}
