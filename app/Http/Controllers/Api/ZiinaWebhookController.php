<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backlink;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\MetaCapiService;
use App\Services\SettingsService;
use App\Services\ZiinaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZiinaWebhookController extends Controller
{
    protected ZiinaPaymentService $ziina;

    protected SettingsService $settings;

    protected MetaCapiService $metaCapi;

    public function __construct(ZiinaPaymentService $ziina, SettingsService $settings, MetaCapiService $metaCapi)
    {
        $this->ziina = $ziina;
        $this->settings = $settings;
        $this->metaCapi = $metaCapi;
    }

    /**
     * Handle incoming webhook event from Ziina.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();

        // 1. Signature Verification (strict requirement)
        $secret = $this->settings->get('ziina_webhook_secret', '') ?: config('services.ziina.webhook_secret', '');
        $signature = $request->header('X-Ziina-Signature') ?: $request->header('Ziina-Signature');

        if (empty($secret)) {
            Log::error('Ziina Webhook: Webhook secret is not configured on server.');

            return response()->json(['error' => 'Webhook secret not configured'], 401);
        }

        if (empty($signature)) {
            Log::warning('Ziina Webhook: Missing signature header', ['ip' => $request->ip()]);

            return response()->json(['error' => 'Missing signature header'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $rawPayload, $secret);
        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Ziina Webhook: Signature mismatch', [
                'ip' => $request->ip(),
                'received_sig' => $signature,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Deduplicate rapid replays of the exact same signed payload
        $replayKey = 'ziina_webhook_evt_'.hash('sha256', $rawPayload);
        if (! Cache::add($replayKey, 1, 300)) {
            return response()->json(['success' => true, 'message' => 'Duplicate webhook event ignored'], 200);
        }

        // 2. Parse Payload
        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            $payload = $request->all();
        }

        Log::info('Ziina Webhook Received', ['event' => $payload['event'] ?? 'unknown', 'ip' => $request->ip()]);

        $data = $payload['data'] ?? $payload;
        $intentId = $data['id'] ?? $data['payment_intent_id'] ?? $payload['id'] ?? null;

        if (empty($intentId)) {
            Log::warning('Ziina Webhook: Missing payment intent ID in payload', ['payload' => $payload]);

            return response()->json(['error' => 'Missing payment intent ID'], 400);
        }

        // 3. Authoritative Direct API Verification (Double-Check with Ziina Server)
        $intent = $this->ziina->fetchPaymentIntent($intentId);
        if (isset($intent['error'])) {
            Log::error('Ziina Webhook: Failed to verify intent with Ziina API', [
                'intent_id' => $intentId,
                'error' => $intent['error'],
            ]);

            return response()->json(['error' => 'Could not verify intent status with Ziina'], 502);
        }

        $status = $intent['status'] ?? '';

        // 4. Locate Booking and Payment Record
        $booking = Booking::where('ziina_payment_intent_id', $intentId)->first();
        $paymentRecord = BookingPayment::where('payment_intent_id', $intentId)->first();

        if (! $booking && $paymentRecord && $paymentRecord->booking) {
            $booking = $paymentRecord->booking;
        }

        if (! $booking) {
            if ($paymentRecord && $status === 'completed') {
                $paymentRecord->update(['status' => 'completed']);
                Log::info('Ziina Webhook: Standalone payment marked completed', ['intent_id' => $intentId, 'payment_id' => $paymentRecord->id]);

                return response()->json(['message' => 'Standalone payment processed successfully'], 200);
            }
            Log::warning('Ziina Webhook: No booking found for payment intent', ['intent_id' => $intentId]);

            return response()->json(['message' => 'Booking not found, event acknowledged'], 200);
        }

        // 5. Process Payment Completion atomically
        if ($status === 'completed') {
            $intentCurrency = strtoupper((string) ($intent['currency_code'] ?? $intent['currency'] ?? 'AED'));
            $intentTestMismatch = isset($intent['test']) && ((bool) $intent['test'] !== $this->ziina->isTestMode());
            if ($intentCurrency !== 'AED' || $intentTestMismatch) {
                Log::warning('Ziina Webhook: Currency or test-mode mismatch rejected', [
                    'intent_id' => $intentId,
                    'currency' => $intentCurrency,
                    'test_flag' => $intent['test'] ?? null,
                ]);

                return response()->json(['error' => 'Payment currency or mode mismatch'], 422);
            }

            $totalPaid = 0.00;
            $newPaymentStatus = 'paid';
            $shouldNotify = false;

            DB::transaction(function () use (
                $booking, $paymentRecord, $intent, &$totalPaid, &$newPaymentStatus, &$shouldNotify
            ) {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                $lockedPayment = $paymentRecord ? BookingPayment::where('id', $paymentRecord->id)->lockForUpdate()->first() : null;

                $wasCompleted = ($lockedPayment && $lockedPayment->status === 'completed')
                    || (! $lockedPayment && $lockedBooking && $lockedBooking->status === 'confirmed' && in_array($lockedBooking->payment_status, ['paid', 'partial'], true));

                if ($lockedPayment && $lockedPayment->status !== 'completed') {
                    $lockedPayment->update(['status' => 'completed']);
                }

                $totalPaid = (float) BookingPayment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                if ($totalPaid <= 0 && isset($intent['amount']) && is_numeric($intent['amount'])) {
                    $totalPaid = round((float) $intent['amount'] / 100, 2);
                } elseif (isset($intent['amount']) && is_numeric($intent['amount']) && $lockedPayment) {
                    $intentPaidAed = round((float) $intent['amount'] / 100, 2);
                    if ($intentPaidAed > 0 && $intentPaidAed + 0.01 < (float) $lockedPayment->amount) {
                        $totalPaid = min($totalPaid, $intentPaidAed);
                    }
                }

                $remBalance = max(0, round((float) $lockedBooking->total - $totalPaid, 2));
                $method = $lockedBooking->payment_method;
                $newPaymentStatus = ($remBalance <= 0.01) ? 'paid' : (($method === 'advance' || $totalPaid > 0) ? 'partial' : 'paid');
                $bookingStatus = ($method === 'full' && $remBalance > 0.01) ? 'pending' : 'confirmed';

                $lockedBooking->update([
                    'status' => $bookingStatus,
                    'payment_status' => $newPaymentStatus,
                    'ziina_status' => 'completed',
                    'balance_due' => $remBalance,
                    'payment_amount' => $totalPaid,
                ]);

                // Record coupon usage, backlink attribution, and trigger notification only if this is the first completion event
                if (! $wasCompleted && $bookingStatus === 'confirmed') {
                    $shouldNotify = true;
                    $this->recordCouponUsage($lockedBooking);
                    $this->recordBacklinkAttribution($lockedBooking);
                }
            });

            // Dispatch emails & Meta CAPI outside the transaction
            if ($shouldNotify) {
                $freshBooking = $booking->fresh();
                $emailType = ($newPaymentStatus === 'partial') ? 'booking_advance' : 'booking_full';
                $this->sendEmailNotification($emailType, $freshBooking);

                try {
                    $custom = [
                        'value' => (float) $freshBooking->total,
                        'currency' => 'AED',
                        'content_ids' => ['TOUR-'.$freshBooking->tour_id],
                        'content_type' => 'product',
                        'contents' => [['id' => 'TOUR-'.$freshBooking->tour_id, 'quantity' => 1]],
                        'coupon' => $freshBooking->coupon_code,
                        'discount_amount' => (float) $freshBooking->discount_amount,
                    ];
                    $this->metaCapi->dispatchEvent('Purchase', [
                        'event_id' => 'BOOK-'.$freshBooking->reference,
                        'email' => $freshBooking->email,
                        'phone' => $freshBooking->phone,
                        'client_ip' => $freshBooking->ip_address,
                        'user_agent' => $freshBooking->user_agent,
                        'event_source_url' => $freshBooking->landing_page ?: url('/'),
                        'fbc' => $freshBooking->fbclid ? ('fb.1.'.time().'.'.$freshBooking->fbclid) : null,
                        'custom_data' => $custom,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Ziina Webhook: Meta CAPI dispatch error: '.$e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment completed and booking confirmed',
                'reference' => $booking->reference,
            ]);
        }

        // 6. Process Failed or Cancelled Status
        if (in_array($status, ['failed', 'cancelled'])) {
            DB::transaction(function () use ($booking, $paymentRecord, $status) {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                $lockedPayment = $paymentRecord ? BookingPayment::where('id', $paymentRecord->id)->lockForUpdate()->first() : null;

                if ($lockedPayment && $lockedPayment->status !== 'completed') {
                    $lockedPayment->update(['status' => $status]);
                }

                $hasAnyCompletedPayment = BookingPayment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->exists();

                if (! $hasAnyCompletedPayment && ! in_array($lockedBooking->status, ['confirmed', 'completed']) && ! in_array($lockedBooking->payment_status, ['paid', 'partial'])) {
                    if ($lockedBooking->coupon_id) {
                        try {
                            $usage = CouponUsage::where('booking_id', $lockedBooking->id)->lockForUpdate()->first();
                            if ($usage) {
                                $coupon = Coupon::where('id', $lockedBooking->coupon_id)->lockForUpdate()->first();
                                if ($coupon && $coupon->used_count > 0) {
                                    $coupon->decrement('used_count');
                                }
                                $usage->delete();
                            }
                        } catch (\Throwable $e) {
                            Log::error('Ziina Webhook: Failed to release coupon on cancellation: '.$e->getMessage());
                        }
                    }

                    $lockedBooking->update([
                        'status' => in_array($status, ['cancelled', 'failed'], true) ? 'cancelled' : $lockedBooking->status,
                        'payment_status' => $status,
                        'ziina_status' => $status,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Booking payment marked as {$status}",
                'reference' => $booking->reference,
            ]);
        }

        // Other statuses (pending, processing, etc.) — never downgrade already-paid bookings
        if ($paymentRecord && $paymentRecord->status !== 'completed') {
            $paymentRecord->update(['status' => $status]);
        }
        if (! in_array($booking->payment_status, ['paid', 'partial'], true) && $booking->ziina_status !== 'completed') {
            $booking->update(['ziina_status' => $status]);
        }

        return response()->json([
            'success' => true,
            'message' => "Status {$status} recorded",
            'reference' => $booking->reference,
        ]);
    }

    /**
     * Record coupon usage upon confirmed payment.
     */
    protected function recordCouponUsage(Booking $booking): void
    {
        if (! $booking->coupon_id || (float) $booking->discount_amount <= 0) {
            return;
        }

        try {
            $usage = CouponUsage::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'coupon_id' => $booking->coupon_id,
                    'booking_reference' => $booking->reference,
                    'customer_name' => $booking->name,
                    'customer_email' => strtolower($booking->email),
                    'customer_phone' => $booking->phone,
                    'discount_amount' => (float) $booking->discount_amount,
                    'order_subtotal' => (float) $booking->original_total,
                    'order_final_total' => (float) $booking->total,
                    'used_at' => now(),
                ]
            );

            if ($usage->wasRecentlyCreated) {
                $coupon = Coupon::where('id', $booking->coupon_id)->lockForUpdate()->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }
        } catch (\Throwable $e) {
            Log::error("Ziina Webhook: Failed to record coupon usage for {$booking->reference}: ".$e->getMessage());
        }
    }

    /**
     * Record backlink conversion attribution upon confirmed webhook payment.
     */
    protected function recordBacklinkAttribution(Booking $booking): void
    {
        if (! $booking->backlink_id) {
            return;
        }

        try {
            $backlink = Backlink::find($booking->backlink_id);
            if ($backlink) {
                $backlink->increment('bookings_count');
                $bookingTotal = max(0, (float) $booking->total);
                if ($bookingTotal > 0) {
                    $backlink->increment('revenue_generated_aed', $bookingTotal);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Ziina Webhook: Failed to attribute backlink for {$booking->reference}: ".$e->getMessage());
        }
    }

    /**
     * Helper to send customer and admin notification emails.
     */
    protected function sendEmailNotification(string $type, Booking $booking): void
    {
        try {
            app(\App\Services\NotificationManagementService::class)->notifyBookingConfirmed($booking, $type);
        } catch (\Throwable $e) {
            Log::error("Ziina Webhook: Failed to process booking notifications for {$booking->reference}: ".$e->getMessage());
        }
    }
}
