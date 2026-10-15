<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\EmailVerificationController;
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
    // Tours Auto-complete Live Search
    Route::get('/tours/search', [TourController::class, 'liveSearch']);

    // GeoIP & IP utilities
    Route::get('/geoip', [ApiController::class, 'geoip']);

    // Email OTP Verification
    Route::post('/email/status', [EmailVerificationController::class, 'status']);
    Route::post('/email/otp/send', [EmailVerificationController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('/email/otp/verify', [EmailVerificationController::class, 'verifyOtp'])->middleware('throttle:10,1');

    // Coupon Validation & Marketing
    Route::post('/coupon/validate', [CouponController::class, 'validateCoupon'])->middleware('throttle:30,1');
    Route::get('/coupon/featured', [CouponController::class, 'featured']);
    Route::post('/welcome-offer/claim', [WelcomeOfferController::class, 'claimOffer'])->middleware('throttle:15,1');

    // Booking Checkout & Draft Lead Recovery
    Route::post('/booking/checkout', [BookingController::class, 'checkout'])->middleware('throttle:20,1');
    Route::post('/booking/draft', [BookingController::class, 'saveDraft'])->middleware('throttle:20,1');

    // Ziina Payment Webhook (throttled against DoS and aliased)
    Route::post('/ziina/webhook', [ZiinaWebhookController::class, 'handleWebhook'])->middleware('throttle:60,1');
    Route::post('/webhooks/ziina', [ZiinaWebhookController::class, 'handleWebhook'])->middleware('throttle:60,1');

    // Contact submission
    Route::post('/contact/submit', [PageController::class, 'submitContact'])->middleware('throttle:20,1');

    // WhatsApp lead logging
    Route::post('/whatsapp/log', [PageController::class, 'logWhatsapp'])->middleware('throttle:20,1');

    // Public Newsletter & Marketing Subscription
    Route::post('/subscribers/subscribe', [SubscriberController::class, 'subscribe'])->middleware('throttle:10,1');

    // Admin Real-time Active Visitors tracking
    Route::middleware('auth:sanctum')->get('/visitors/active', [AdminDashboardController::class, 'activeVisitors']);

    // Scheduler Crontab Health Check Endpoint
    Route::get('/cron/status', function () {
        $lastHeartbeat = \Illuminate\Support\Facades\Cache::get('scheduler_last_heartbeat');
        $isRecent = $lastHeartbeat && \Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now()) <= 3;

        return response()->json([
            'status' => $isRecent ? 'active' : ($lastHeartbeat ? 'stale' : 'pending'),
            'cron_active' => (bool) $isRecent,
            'last_heartbeat' => $lastHeartbeat,
            'server_time' => now()->toIso8601String(),
            'minutes_ago' => $lastHeartbeat ? \Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now()) : null,
            'message' => $isRecent
                ? 'Hostinger crontab is active and executing successfully.'
                : ($lastHeartbeat ? 'Hostinger crontab was seen earlier but heartbeat is stale.' : 'No scheduler heartbeat recorded yet. Ensure cron is running with proper directory and PHP path.')
        ]);
    });
});
