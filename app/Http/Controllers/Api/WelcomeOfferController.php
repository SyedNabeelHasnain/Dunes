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
        $this->ensureSchema();
    }

    /**
     * Ensure database tables exist.
     */
    protected function ensureSchema(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('coupons') || !\Illuminate\Support\Facades\Schema::hasTable('coupon_usages')) {
                $migration = require database_path('migrations/2026_08_30_000001_create_coupons_table.php');
                $migration->up();
            }
        } catch (\Throwable $e) {
            Log::error("Welcome offer schema check error: " . $e->getMessage());
        }
    }

    /**
     * Claim First-Time Visitor 25% Discount Voucher.
     */
    public function claimOffer(Request $request): JsonResponse
    {
        $this->ensureSchema();

        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $email = strtolower(trim($request->input('email')));
        $name = trim($request->input('name', 'Valued Traveler')) ?: 'Valued Traveler';
        $phone = trim($request->input('phone', ''));

        // Check if there is already an active welcome coupon generated for this email today
        $existing = Coupon::where('code', 'like', 'FIRST25-%')
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if ($existing) {
            $coupon = $existing;
        } else {
            // Generate unique voucher code
            $code = 'FIRST25-' . strtoupper(Str::random(5));

            $coupon = Coupon::create([
                'code' => $code,
                'name' => 'First-Time 25% Welcome Offer (' . $email . ')',
                'description' => 'Claimed via welcome offer popup modal on ' . now()->format('Y-m-d H:i'),
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
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

        // Record Lead in Contacts Table
        try {
            $contact = Contact::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: 'N/A',
                'subject' => 'First-Time 25% Voucher Claimed (' . $coupon->code . ')',
                'message' => "Customer claimed 25% first-time visitor voucher {$coupon->code}. Voucher expires in 24 hours.",
                'status' => 'new',
                'ip_address' => $ctx['client_ip'],
                'is_verified' => false,
            ]);

            $logId = $this->tracker->logRequest('contact', $contact->id, 'welcome_popup', $ctx);
            if ($logId) {
                $contact->update(['request_log_id' => $logId]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to log welcome offer lead: " . $e->getMessage());
        }

        // Send Email Voucher to Customer
        try {
            $fromEmail = $this->settings->getFromEmail();
            Mail::to($email)->send(
                (new WelcomeOfferMail($coupon->code, 25.00, $name))->from($fromEmail, 'Dunes Discovery Tourism')
            );
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch welcome offer email to {$email}: " . $e->getMessage());
        }

        // Trigger Meta Conversions API Lead Event
        try {
            $this->metaCapi->dispatchEvent('Lead', [
                'event_id' => 'LEAD-WELCOME-' . strtoupper(substr(md5($email), 0, 10)),
                'email' => $email,
                'phone' => $phone,
                'custom_data' => [
                    'content_name' => 'First-Time 25% Welcome Voucher',
                    'content_category' => 'Welcome Offer',
                    'coupon' => $coupon->code,
                    'value' => 0.00,
                    'currency' => 'AED',
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("Meta CAPI Lead event error: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Congratulations! Your 25% discount voucher is ready.',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => 25,
                'discount_type' => 'percentage',
                'savings_text' => '25% OFF Today',
                'valid_until' => $coupon->valid_until ? $coupon->valid_until->toIso8601String() : null,
                'timer_seconds' => 900, // 15 minutes session urgency
            ]
        ]);
    }
}
