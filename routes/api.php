<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\SessionTelemetryController;
use App\Http\Controllers\Api\SyndicationController;
use App\Http\Controllers\Api\WelcomeOfferController;
use App\Http\Controllers\Api\ZiinaWebhookController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public Tour Syndication API (for partner portals, travel aggregators, and widgets)
    Route::get('/syndication/tours', [SyndicationController::class, 'tours'])->middleware('throttle:60,1');

    // Tours Auto-complete Live Search
    Route::get('/tours/search', [TourController::class, 'liveSearch'])->middleware('throttle:60,1');

    // GeoIP & IP utilities
    Route::get('/geoip', [ApiController::class, 'geoip'])->middleware('throttle:30,1');

    // Email OTP Verification
    Route::post('/email/status', [EmailVerificationController::class, 'status'])->middleware('throttle:20,1');
    Route::post('/email/otp/send', [EmailVerificationController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/email/otp/verify', [EmailVerificationController::class, 'verifyOtp'])->middleware('throttle:10,1');

    // Coupon Validation & Marketing
    Route::post('/coupon/validate', [CouponController::class, 'validateCoupon'])->middleware('throttle:30,1');
    Route::get('/coupon/featured', [CouponController::class, 'featured'])->middleware('throttle:60,1');
    Route::post('/welcome-offer/claim', [WelcomeOfferController::class, 'claimOffer'])->middleware('throttle:6,1');

    // Booking Checkout & Draft Lead Recovery
    Route::post('/booking/checkout', [BookingController::class, 'checkout'])->middleware('throttle:20,1');
    Route::post('/booking/draft', [BookingController::class, 'saveDraft'])->middleware('throttle:20,1');

    // Ziina Payment Webhook (throttled against DoS and aliased)
    Route::post('/ziina/webhook', [ZiinaWebhookController::class, 'handleWebhook'])->middleware('throttle:60,1');
    Route::post('/webhooks/ziina', [ZiinaWebhookController::class, 'handleWebhook'])->middleware('throttle:60,1');

    // Contact submission
    Route::post('/contact/submit', [PageController::class, 'submitContact'])->middleware('throttle:6,1');

    // WhatsApp lead logging
    Route::post('/whatsapp/log', [PageController::class, 'logWhatsapp'])->middleware('throttle:20,1');

    // Public Newsletter & Marketing Subscription
    Route::post('/subscribers/subscribe', [SubscriberController::class, 'subscribe'])->middleware('throttle:10,1');

    // Privacy Consent & Real-User Core Web Vitals (RUM) Telemetry
    Route::post('/privacy/consent', [\App\Http\Controllers\PrivacyConsentController::class, 'store'])->middleware('throttle:30,1');
    Route::post('/rum/vitals', [\App\Http\Controllers\RumVitalsController::class, 'store'])->middleware('throttle:120,1');

    // Self-Hosted Session Recording & Heatmap Ingestion (rrweb & click/scroll telemetry)
    Route::post('/telemetry/recordings/batch', [SessionTelemetryController::class, 'ingestRecordingBatch'])->middleware('throttle:180,1');
    Route::post('/telemetry/heatmaps/click', [SessionTelemetryController::class, 'recordHeatmapClick'])->middleware('throttle:300,1');
    Route::post('/telemetry/heatmaps/scroll', [SessionTelemetryController::class, 'recordHeatmapScroll'])->middleware('throttle:120,1');

    // Scheduler Crontab Health Check Endpoint
    Route::get('/cron/status', function () {
        $lastHeartbeat = \Illuminate\Support\Facades\Cache::get('scheduler_last_heartbeat');
        $isRecent = $lastHeartbeat && \Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now()) <= 3;

        return response()->json([
            'status' => $isRecent ? 'active' : ($lastHeartbeat ? 'stale' : 'pending'),
            'cron_active' => (bool) $isRecent,
            'last_heartbeat' => $lastHeartbeat,
            'server_time' => now()->toIso8601String(),
            'minutes_ago' => $lastHeartbeat ? (int) floor(\Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now())) : null,
            'message' => $isRecent
                ? 'Scheduler crontab is active and executing successfully.'
                : ($lastHeartbeat ? 'Scheduler crontab was seen earlier but heartbeat is stale.' : 'No scheduler heartbeat recorded yet. Ensure cron is running with proper directory and PHP path.'),
        ]);
    })->middleware('throttle:30,1');

    // Trigger scheduler runner via Web / Cron HTTP request
    Route::match(['get', 'post'], '/cron/run', function (\Illuminate\Http\Request $request) {
        $token = $request->bearerToken() ?: $request->post('token', $request->query('token'));
        $validToken = (string) config('services.cron.secret_token', '');
        if ($validToken === '' && app()->environment('testing')) {
            $validToken = 'dunes_cron_2026';
        }

        if (app()->isProduction() && ($validToken === '' || $validToken === 'dunes_cron_2026')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cron secret token must be configured in production.',
            ], 503, ['Cache-Control' => 'no-store, private', 'X-Robots-Tag' => 'noindex, nofollow']);
        }

        if (empty($token) || empty($validToken) || ! hash_equals($validToken, (string) $token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized cron token.',
            ], 403, ['Cache-Control' => 'no-store, private', 'X-Robots-Tag' => 'noindex, nofollow']);
        }

        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        \Illuminate\Support\Facades\Cache::put('scheduler_last_heartbeat', now()->toIso8601String(), 86400);

        return response()->json([
            'status' => 'success',
            'cron_active' => true,
            'server_time' => now()->toIso8601String(),
            'heartbeat' => now()->toIso8601String(),
            'message' => 'Scheduler executed successfully.',
        ], 200, ['Cache-Control' => 'no-store, private', 'X-Robots-Tag' => 'noindex, nofollow']);
    })->middleware('throttle:10,1');
});

// Top-level aliases for direct /api/telemetry calls
Route::post('/telemetry/recordings/batch', [SessionTelemetryController::class, 'ingestRecordingBatch'])->middleware('throttle:180,1');
Route::post('/telemetry/heatmaps/click', [SessionTelemetryController::class, 'recordHeatmapClick'])->middleware('throttle:300,1');
Route::post('/telemetry/heatmaps/scroll', [SessionTelemetryController::class, 'recordHeatmapScroll'])->middleware('throttle:120,1');

