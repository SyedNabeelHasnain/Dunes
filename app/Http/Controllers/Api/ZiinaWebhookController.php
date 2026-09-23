<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingAdminNotification;
use App\Mail\BookingNotification;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\MetaCapiService;
use App\Services\SettingsService;
use App\Services\ZiinaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $secret = $this->settings->get('ziina_webhook_secret', '') ?: config('services.ziina.webhook_secret', '') ?: env('ZIINA_WEBHOOK_SECRET', '');
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
            $totalPaid = 0.00;
            $newPaymentStatus = 'paid';
            $shouldNotify = false;

            DB::transaction(function () use (
                $booking, $paymentRecord, $intent, &$totalPaid, &$newPaymentStatus, &$shouldNotify
            ) {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
                $lockedPayment = $paymentRecord ? BookingPayment::where('id', $paymentRecord->id)->lockForUpdate()->first() : null;

                $wasCompleted = ($lockedPayment && $lockedPayment->status === 'completed')
                    && ($lockedBooking && $lockedBooking->status === 'confirmed' && in_array($lockedBooking->payment_status, ['paid', 'partial']));

                if ($lockedPayment && $lockedPayment->status !== 'completed') {
                    $lockedPayment->update(['status' => 'completed']);
                }

                $totalPaid = BookingPayment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                if ($totalPaid <= 0 && isset($intent['amount'])) {
                    $totalPaid = (float) ($intent['amount'] / 100);
                }

                $remBalance = max(0, (float) $lockedBooking->total - (float) $totalPaid);
                $method = $lockedBooking->payment_method;
                $newPaymentStatus = ($remBalance <= 0) ? 'paid' : (($method === 'advance' || $totalPaid > 0) ? 'partial' : 'paid');

                $lockedBooking->update([
                    'status' => 'confirmed',
                    'payment_status' => $newPaymentStatus,
                    'ziina_status' => 'completed',
                    'balance_due' => $remBalance,
                    'payment_amount' => $totalPaid,
                ]);

                // Record coupon usage and trigger notification only if this is the first completion event
                if (! $wasCompleted) {
                    $shouldNotify = true;
                    $this->recordCouponUsage($lockedBooking);
                }
            });

            // Dispatch emails & Meta CAPI outside the transaction
            if ($shouldNotify) {
                $emailType = ($newPaymentStatus === 'partial') ? 'booking_advance' : 'booking_full';
                $this->sendEmailNotification($emailType, $booking->fresh());

                try {
                    $custom = [
                        'value' => (float) $booking->total,
                        'currency' => 'AED',
                        'content_ids' => ['TOUR-'.$booking->tour_id],
                        'content_type' => 'product',
                        'contents' => [['id' => 'TOUR-'.$booking->tour_id, 'quantity' => 1]],
                        'coupon' => $booking->coupon_code,
                        'discount_amount' => (float) $booking->discount_amount,
                    ];
                    $this->metaCapi->dispatchEvent('Purchase', [
                        'event_id' => 'BOOK-'.$booking->reference,
                        'email' => $booking->email,
                        'phone' => $booking->phone,
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
            if (! in_array($booking->status, ['confirmed', 'completed']) && ! in_array($booking->payment_status, ['paid', 'partial'])) {
                if ($booking->coupon_id) {
                    try {
                        $usage = CouponUsage::where('booking_id', $booking->id)->first();
                        if ($usage) {
                            $coupon = Coupon::find($booking->coupon_id);
                            if ($coupon && $coupon->used_count > 0) {
                                $coupon->decrement('used_count');
                            }
                            $usage->delete();
                        }
                    } catch (\Throwable $e) {
                        Log::error('Ziina Webhook: Failed to release coupon on cancellation: '.$e->getMessage());
                    }
                }

                $booking->update([
                    'payment_status' => $status,
                    'ziina_status' => $status,
                ]);

                if ($paymentRecord) {
                    $paymentRecord->update(['status' => $status]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Booking payment marked as {$status}",
                'reference' => $booking->reference,
            ]);
        }

        // Other statuses (pending, processing, etc.)
        if ($paymentRecord && $paymentRecord->status !== 'completed') {
            $paymentRecord->update(['status' => $status]);
        }
        $booking->update(['ziina_status' => $status]);

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
            $alreadyRecorded = CouponUsage::where('booking_id', $booking->id)->exists();
            if (! $alreadyRecorded) {
                CouponUsage::create([
                    'coupon_id' => $booking->coupon_id,
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->reference,
                    'customer_name' => $booking->name,
                    'customer_email' => strtolower($booking->email),
                    'customer_phone' => $booking->phone,
                    'discount_amount' => (float) $booking->discount_amount,
                    'order_subtotal' => (float) $booking->original_total,
                    'order_final_total' => (float) $booking->total,
                    'used_at' => now(),
                ]);

                $coupon = Coupon::find($booking->coupon_id);
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }
        } catch (\Throwable $e) {
            Log::error("Ziina Webhook: Failed to record coupon usage for {$booking->reference}: ".$e->getMessage());
        }
    }

    /**
     * Helper to send customer and admin notification emails.
     */
    protected function sendEmailNotification(string $type, Booking $booking): void
    {
        try {
            $fromEmail = $this->settings->getFromEmail();
            $adminEmail = $this->settings->getAdminEmail();
            $ccEmails = $this->settings->getCcEmails();
            $bccEmails = $this->settings->getBccEmails();

            // Send to customer
            try {
                Mail::to($booking->email)
                    ->send((new BookingNotification($type, $booking))->from($fromEmail, 'Dunes Discovery Tourism'));
            } catch (\Throwable $e) {
                Log::error("Ziina Webhook: Failed to send customer booking email for {$booking->reference}: ".$e->getMessage());
            }

            // Send to admin
            try {
                $adminMail = (new BookingAdminNotification($booking))->from($fromEmail, 'Dunes Discovery Tourism');
                if (! empty($ccEmails)) {
                    $adminMail->cc($ccEmails);
                }
                if (! empty($bccEmails)) {
                    $adminMail->bcc($bccEmails);
                }
                Mail::to($adminEmail)->send($adminMail);
            } catch (\Throwable $e) {
                Log::error("Ziina Webhook: Failed to send admin booking email for {$booking->reference}: ".$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Ziina Webhook: Failed to prepare booking email for {$booking->reference}: ".$e->getMessage());
        }
    }
}
