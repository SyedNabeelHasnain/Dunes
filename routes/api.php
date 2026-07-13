<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // GeoIP & IP utilities
    Route::get('/geoip', [ApiController::class, 'geoip']);
    
    // Email OTP Verification
    Route::post('/email/status', [EmailVerificationController::class, 'status']);
    Route::post('/email/otp/send', [EmailVerificationController::class, 'sendOtp']);
    Route::post('/email/otp/verify', [EmailVerificationController::class, 'verifyOtp']);
    
    // Booking Checkout
    Route::post('/booking/checkout', [BookingController::class, 'checkout']);
    
    // Contact submission
    Route::post('/contact/submit', [PageController::class, 'submitContact']);
    
    // WhatsApp lead logging
    Route::post('/whatsapp/log', [PageController::class, 'logWhatsapp']);

    // Admin Real-time Active Visitors tracking
    Route::middleware('auth:sanctum')->get('/visitors/active', [AdminDashboardController::class, 'activeVisitors']);
});
