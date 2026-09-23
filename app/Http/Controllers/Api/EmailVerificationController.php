<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\EmailOtp;
use App\Models\VerifiedEmail;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationController extends Controller
{
    /**
     * Check if email is already verified.
     */
    public function status(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        $sessionVerified = session()->has('email_verified_'.md5($email));

        return response()->json(['success' => true, 'verified' => $sessionVerified]);
    }

    /**
     * Send OTP to visitor's email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        // Global rate limiting per email (max 5 OTPs per 10 minutes)
        $emailRateKey = 'send_otp:'.md5($email);
        if (RateLimiter::tooManyAttempts($emailRateKey, 5)) {
            $seconds = RateLimiter::availableIn($emailRateKey);

            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Please wait {$seconds}s before requesting another.",
            ], 429);
        }

        // Session rate limiting check (60 seconds cooldown)
        $rateKey = 'otp_sent_'.md5($email);
        if (session()->has($rateKey)) {
            $lastSent = (int) session($rateKey);
            $elapsed = time() - $lastSent;
            if ($elapsed < 60) {
                $wait = 60 - $elapsed;

                return response()->json([
                    'success' => false,
                    'message' => "Please wait {$wait}s before requesting another OTP",
                ]);
            }
        }

        RateLimiter::hit($emailRateKey, 600);

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            $rateKey => time(),
            'otp_email' => $email,
            'otp_code' => $otp,
            'otp_expiry' => time() + 300, // 5 minutes
        ]);

        // Hashed OTP audit log entry
        try {
            EmailOtp::create([
                'email' => $email,
                'otp' => hash('sha256', $otp),
                'expires_at' => now()->addMinutes(5),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to write email OTP audit log: '.$e->getMessage());
        }

        // Send the OTP email using Laravel Mail facade
        try {
            $fromEmail = app(SettingsService::class)->getFromEmail();
            Mail::to($email)->send((new OtpMail($otp))->from($fromEmail, 'Dunes Discovery Tourism'));

            return response()->json(['success' => true, 'message' => 'OTP sent successfully to '.$email]);

        } catch (\Throwable $e) {
            Log::error("Failed to dispatch OTP email to {$email}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please check your email address and try again.',
            ]);
        }
    }

    /**
     * Verify the user's OTP.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        $otp = preg_replace('/[^0-9]/', '', (string) $request->input('otp', ''));

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        if (strlen($otp) !== 6) {
            return response()->json(['success' => false, 'message' => 'OTP must be exactly 6 digits'], 400);
        }

        $attemptKey = 'verify_otp_attempts:'.md5($email);
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            $seconds = RateLimiter::availableIn($attemptKey);

            return response()->json([
                'success' => false,
                'message' => "Too many incorrect attempts. Please wait {$seconds}s before trying again.",
            ], 429);
        }

        $sessionEmail = session('otp_email');
        $sessionCode = session('otp_code');
        $sessionExpiry = session('otp_expiry');

        $isValid = false;

        // 1. Session check
        if ($sessionEmail === $email
            && hash_equals((string) $sessionCode, $otp)
            && time() < (int) $sessionExpiry
        ) {
            $isValid = true;
            session()->forget(['otp_email', 'otp_code', 'otp_expiry']);
        }

        // 2. Database audit log fallback check
        if (! $isValid) {
            $hashedOtp = hash('sha256', $otp);
            $dbOtp = EmailOtp::where('email', $email)
                ->where('otp', $hashedOtp)
                ->where('expires_at', '>', now())
                ->first();

            if ($dbOtp) {
                $isValid = true;
                try {
                    $dbOtp->delete();
                } catch (\Throwable $e) {
                }
            }
        }

        if ($isValid) {
            RateLimiter::clear($attemptKey);
            session(['email_verified_'.md5($email) => true]);

            // Persist email to verified_emails table
            try {
                VerifiedEmail::updateOrCreate(['email' => $email], ['verified_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Failed to save verified email to DB: '.$e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Email verified successfully']);
        }

        RateLimiter::hit($attemptKey, 300);

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP. Please request a new one.',
        ]);
    }
}
