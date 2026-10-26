<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeOfferMail;
use App\Models\Contact;
use App\Models\Coupon;
use App\Services\MetaCapiService;
use App\Services\SettingsService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WelcomeOfferController extends Controller
{
    protected VisitorTrackerService $tracker;

    protected MetaCapiService $metaCapi;

    protected SettingsService $settings;

    public function __construct(VisitorTrackerService $tracker, MetaCapiService $metaCapi, SettingsService $settings)
    {
        $this->tracker = $tracker;
        $this->metaCapi = $metaCapi;
        $this->settings = $settings;
    }

    /**
     * Claim First-Time Visitor 25% Discount Voucher.
     */
    public function claimOffer(Request $request): JsonResponse
    {
        $modalEnabled = $this->settings->get('promo_welcome_modal_enabled');
        $modalActive = $this->settings->get('welcome_popup_active');
        if ($modalEnabled === '0' || $modalActive === '0') {
            return response()->json([
                'success' => false,
                'message' => 'This promotional offer is currently unavailable.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:7|max:50',
        ]);

        $email = strtolower(trim($request->input('email')));
        $name = trim($request->input('name'));
        $phone = trim($request->input('phone'));

        $isReturningCustomer = \App\Models\Booking::where('email', $email)
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'completed'])
                    ->orWhereIn('payment_status', ['paid', 'partial', 'advance_paid']);
            })
            ->exists();

        if ($isReturningCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'This welcome voucher is exclusively available for first-time guests.',
            ], 422);
        }

        $rawDiscount = (float) $this->settings->get('promo_welcome_modal_discount', $this->settings->get('welcome_popup_discount', '25'));
        $discountPercent = $rawDiscount > 0 ? max(1.0, min(50.0, round($rawDiscount, 2))) : 25.00;
        $discountInt = (int) round($discountPercent);

        // Check if there is already an active welcome coupon generated for this specific email today
        $existing = Coupon::where('code', 'like', 'FIRST25-%')
            ->where('name', 'like', "%({$email})%")
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if ($existing) {
            $coupon = $existing;
        } else {
            // Generate unique voucher code
            $code = 'FIRST25-'.strtoupper(Str::random(5));

            $coupon = Coupon::create([
                'code' => $code,
                'name' => "First-Time {$discountInt}% Welcome Offer ({$email})",
                'description' => "Claimed via welcome offer popup modal by {$name} ({$phone}) on ".now()->format('Y-m-d H:i'),
                'discount_type' => 'percentage',
                'discount_value' => $discountPercent,
                'min_spend' => 0.00,
                'max_discount' => null,
                'min_guests' => 1,
                'usage_limit' => 1,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addHours(24),
                'first_time_only' => true,
                'is_featured' => false,
                'status' => 'active',
            ]);
        }

        // Collect request visitor context
        $gpsPost = [
            'gps_consent' => $request->input('gps_consent'),
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'gps_accuracy' => $request->input('gps_accuracy'),
            'gps_timestamp' => $request->input('gps_timestamp'),
            'gps_source' => $request->input('gps_source'),
        ];
        $ctx = $this->tracker->collectRequestContext('popup_offer', $gpsPost);

        $logId = null;

        // Record Lead in Contacts Table
        try {
            $contact = Contact::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => 'First-Time 25% Voucher Claimed ('.$coupon->code.')',
                'message' => "Customer {$name} claimed 25% first-time visitor voucher {$coupon->code}.\nPhone/WhatsApp: {$phone}\nEmail: {$email}\nExpires in 24 hours.",
                'status' => 'new',
                'ip_address' => $ctx['client_ip'],
                'is_verified' => false,
            ]);

            $logId = $this->tracker->logRequest('contact', $contact->id, 'welcome_popup', $ctx);
            if ($logId) {
                $contact->update(['request_log_id' => $logId]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to log welcome offer lead to contacts: '.$e->getMessage());
        }

        // Record Lead in WhatsApp Inquiries Hub for 1-Click Retargeting
        try {
            DB::table('whatsapp_inquiries')->insert([
                'name' => $name,
                'phone' => $phone,
                'tour_name' => '25% Welcome Offer ('.$coupon->code.')',
                'page_url' => $request->header('referer') ?: url('/'),
                'message_text' => "Customer claimed 25% discount voucher {$coupon->code}. Phone: {$phone}, Email: {$email}.",
                'request_log_id' => $logId,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log welcome offer lead to whatsapp_inquiries: '.$e->getMessage());
        }

        // Send Email Voucher to Customer
        try {
            if (! app()->environment('testing')) {
                app(\App\Services\EmailMarketingService::class)->applySmtpConfig();
            }
            $fromEmail = $this->settings->getFromEmail();
            Mail::to($email)->send(
                (new WelcomeOfferMail($coupon->code, (float) $coupon->discount_value, $name))->from($fromEmail, 'Dunes Discovery Tourism')
            );
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch welcome offer email to {$email}: ".$e->getMessage());
        }

        // Trigger Meta Conversions API Lead Event
        try {
            $this->metaCapi->dispatchEvent('Lead', [
                'event_id' => 'LEAD-WELCOME-'.strtoupper(substr(md5($email), 0, 10)),
                'email' => $email,
                'phone' => $phone,
                'custom_data' => [
                    'content_name' => "First-Time {$discountInt}% Welcome Voucher",
                    'content_category' => 'Welcome Offer',
                    'coupon' => $coupon->code,
                    'value' => 0.00,
                    'currency' => 'AED',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Meta CAPI Lead event error: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Congratulations! Your {$discountInt}% discount voucher is ready.",
            'coupon' => [
                'code' => $coupon->code,
                'discount' => (float) $coupon->discount_value,
                'discount_type' => 'percentage',
                'savings_text' => "{$discountInt}% OFF Today",
                'valid_until' => $coupon->valid_until ? $coupon->valid_until->toIso8601String() : null,
                'timer_seconds' => 900, // 15 minutes session urgency
            ],
        ]);
    }
}
