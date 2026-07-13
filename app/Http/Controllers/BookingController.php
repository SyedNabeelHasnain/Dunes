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

        $total = $subtotal + $addonsTotal;

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
        $ref = 'DDT' . date('ymd') . strtoupper(substr(uniqid(), -5));

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
                    'contents' => [['id' => 'TOUR-' . $booking->tour_id, 'quantity' => 1]]
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
            
            if ($booking) {
                $method = $booking->payment_method;
                $intent = $this->ziina->fetchPaymentIntent($pi);
                $intentStatus = $intent['status'] ?? '';

                if ($intentStatus === 'completed') {
                    $newStatus = ($method === 'advance') ? 'partial' : 'paid';
                    
                    if ($booking->payment_status !== $newStatus) {
                        $balanceDue = ($method === 'advance') ? $booking->balance_due : 0;
                        
                        $booking->update([
                            'payment_status' => $newStatus,
                            'ziina_status' => $intentStatus,
                            'status' => 'confirmed',
                            'balance_due' => $balanceDue
                        ]);

                        BookingPayment::where('payment_intent_id', $pi)->update([
                            'status' => $intentStatus
                        ]);

                        // Send booking confirmation email
                        if ($method === 'advance') {
                            $this->sendEmailNotification('booking_advance', $booking);
                        } else {
                            $this->sendEmailNotification('booking_full', $booking);
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
            $brandColor = '#F58F43';
            $subject = '';
            $content = '';
            $pickupLine = "<p><strong>Dune Discovery will contact to confirm the pickup time</strong></p>";

            $name = $booking->name ?: 'Guest';
            $reference = $booking->reference;
            $tourName = $booking->tour_name;
            $dateStr = $booking->tour_date ? $booking->tour_date->format('Y-m-d') : '';
            $totalStr = $booking->total;
            $paidStr = $booking->payment_amount;
            $balStr = $booking->balance_due;
            $pickup = $booking->pickup_location;
            $phone = $booking->phone;

            switch ($type) {
                case 'booking_cash':
                    $subject = "Booking Received - Ref: {$reference}";
                    $content = "<h1 style='color:{$brandColor};'>Booking Received (Pay on Pickup)</h1>
                                <p>Dear {$name},</p>
                                <p>Your booking for <strong>{$tourName}</strong> is reserved.</p>
                                <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;'>
                                    <p><strong>Reference:</strong> {$reference}</p>
                                    <p><strong>Date:</strong> {$dateStr}</p>
                                    <p><strong>Total:</strong> AED {$totalStr}</p>
                                    <p><strong>Payment:</strong> Cash on pickup</p>
                                </div>
                                {$pickupLine}";
                    break;
                case 'booking_advance':
                    $subject = "Advance Payment Received - Ref: {$reference}";
                    $content = "<h1 style='color:{$brandColor};'>Advance Received</h1>
                                <p>Dear {$name},</p>
                                <p>Your booking slot for <strong>{$tourName}</strong> is held with an advance payment.</p>
                                <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;'>
                                    <p><strong>Reference:</strong> {$reference}</p>
                                    <p><strong>Date:</strong> {$dateStr}</p>
                                    <p><strong>Advance Paid:</strong> AED {$paidStr}</p>
                                    <p><strong>Balance Due:</strong> AED {$balStr}</p>
                                </div>
                                {$pickupLine}";
                    break;
                case 'booking_full':
                    $subject = "Payment Successful - Ref: {$reference}";
                    $content = "<h1 style='color:{$brandColor};'>Payment Successful</h1>
                                <p>Dear {$name},</p>
                                <p>Your booking for <strong>{$tourName}</strong> is confirmed.</p>
                                <div style='background-color:#f9f9f9;padding:15px;border-radius:5px;margin:20px 0;'>
                                    <p><strong>Reference:</strong> {$reference}</p>
                                    <p><strong>Date:</strong> {$dateStr}</p>
                                    <p><strong>Paid:</strong> AED {$paidStr}</p>
                                    <p><strong>Status:</strong> Confirmed</p>
                                </div>
                                {$pickupLine}";
                    break;
            }

            $siteEmailSetting = Setting::where('setting_key', 'site_email')->first();
            $fromEmail = $siteEmailSetting ? $siteEmailSetting->setting_value : 'info@dunesdiscoverytourism.com';

            $userEmailBody = "
                <div style='text-align:center;margin:0 auto;padding:20px;'>
                    <img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Discovery Tourism Logo' style='max-width:180px;width:100%;height:auto;display:block;margin:0 auto;'/>
                </div>
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                    <div style='background-color:{$brandColor};padding:20px;text-align:center;'>
                        <h2 style='color:white;margin:0;'>Dunes Discovery Tourism</h2>
                    </div>
                    <div style='padding:20px;background-color:#ffffff;color:#333;line-height:1.6;'>
                        {$content}
                        <p>Thank you for choosing Dunes Discovery Tourism.</p>
                    </div>
                    <div style='padding:15px;text-align:center;color:#888;font-size:12px;background-color:#f9f9f9;border-top:1px solid #eee;'>
                        &copy; " . date('Y') . " Dunes Discovery Tourism. All rights reserved.<br>
                        <a href='https://dunesdiscoverytourism.com' style='color:#888;text-decoration:none;'>https://dunesdiscoverytourism.com</a>
                    </div>
                </div>";

            // Send to user
            Mail::send([], [], function ($message) use ($booking, $subject, $userEmailBody, $fromEmail) {
                $message->to($booking->email)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject($subject)
                    ->html($userEmailBody);
            });

            // Send notification to Admin
            $adminSubject = "New Booking - Ref: {$reference}";
            $adminContent = "
                <h1 style='color:{$brandColor};'>New Booking Received</h1>
                <p><strong>Reference:</strong> {$reference}</p>
                <p><strong>Customer:</strong> {$name} ({$phone})</p>
                <p><strong>Email:</strong> {$booking->email}</p>
                <p><strong>Tour:</strong> {$tourName}</p>
                <p><strong>Total:</strong> AED {$totalStr}</p>
                <p><strong>Payment Method:</strong> " . ucfirst($booking->payment_method) . "</p>
                <p><strong>Payment Status:</strong> " . ucfirst($booking->payment_status) . "</p>";

            $adminEmailBody = "
                <div style='text-align:center;margin:0 auto;padding:20px;'>
                    <img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Discovery Tourism Logo' style='max-width:180px;width:100%;height:auto;display:block;margin:0 auto;'/>
                </div>
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                    <div style='background-color:{$brandColor};padding:20px;text-align:center;'>
                        <h2 style='color:white;margin:0;'>Dunes Discovery Tourism</h2>
                    </div>
                    <div style='padding:20px;background-color:#ffffff;color:#333;line-height:1.6;'>
                        {$adminContent}
                    </div>
                    <div style='padding:15px;text-align:center;color:#888;font-size:12px;background-color:#f9f9f9;border-top:1px solid #eee;'>
                        &copy; " . date('Y') . " Dunes Discovery Tourism. All rights reserved.
                    </div>
                </div>";

            $adminEmailSetting = Setting::where('setting_key', 'admin_email')->first();
            $adminEmail = $adminEmailSetting ? $adminEmailSetting->setting_value : 'admin@dunesdiscoverytourism.com';

            $ccSetting = Setting::where('setting_key', 'admin_email_cc')->first();
            $ccEmails = $ccSetting && !empty($ccSetting->setting_value) ? array_filter(array_map('trim', explode(',', $ccSetting->setting_value))) : [];

            $bccSetting = Setting::where('setting_key', 'admin_email_bcc')->first();
            $bccEmails = $bccSetting && !empty($bccSetting->setting_value) ? array_filter(array_map('trim', explode(',', $bccSetting->setting_value))) : [];

            Mail::send([], [], function ($message) use ($fromEmail, $adminEmail, $ccEmails, $bccEmails, $adminSubject, $adminEmailBody) {
                $message->to($adminEmail)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject($adminSubject)
                    ->html($adminEmailBody);
                
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
                if (!empty($bccEmails)) {
                    $message->bcc($bccEmails);
                }
            });

        } catch (\Exception $e) {
            Log::error("Failed to send booking checkout email notifications for booking {$booking->reference}: " . $e->getMessage());
        }
    }
}
