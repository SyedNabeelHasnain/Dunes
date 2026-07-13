<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\VerifiedEmail;
use App\Models\EmailOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Check if email is already verified.
     */
    public function status(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        $sessionVerified = session()->has('email_verified_' . md5($email));
        
        $dbVerified = $sessionVerified || 
            VerifiedEmail::where('email', $email)->exists() ||
            Booking::where('email', $email)->where('is_verified', true)->exists() ||
            Contact::where('email', $email)->where('is_verified', true)->exists();

        if ($dbVerified) {
            session(['email_verified_' . md5($email) => true]);
            return response()->json(['success' => true, 'verified' => true]);
        }

        return response()->json(['success' => true, 'verified' => false]);
    }

    /**
     * Send OTP to visitor's email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        // Rate limiting check (60 seconds)
        $rateKey = 'otp_sent_' . md5($email);
        if (session()->has($rateKey)) {
            $lastSent = (int)session($rateKey);
            $elapsed = time() - $lastSent;
            if ($elapsed < 60) {
                $wait = 60 - $elapsed;
                return response()->json([
                    'success' => false,
                    'message' => "Please wait {$wait}s before requesting another OTP"
                ]);
            }
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        session([
            $rateKey => time(),
            'otp_email' => $email,
            'otp_code' => $otp,
            'otp_expiry' => time() + 300 // 5 minutes
        ]);

        // Hashed OTP audit log entry
        try {
            EmailOtp::create([
                'email' => $email,
                'otp' => hash('sha256', $otp),
                'expires_at' => now()->addMinutes(5),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to write email OTP audit log: " . $e->getMessage());
        }

        // Send the OTP email using Laravel Mail facade
        try {
            // Check if setting for site email and settings is loaded
            $settingEmail = \App\Models\Setting::where('setting_key', 'site_email')->first();
            $fromEmail = $settingEmail ? $settingEmail->setting_value : 'info@dunesdiscoverytourism.com';

            Mail::send([], [], function ($message) use ($email, $otp, $fromEmail) {
                $message->to($email)
                    ->from($fromEmail, 'Dunes Discovery Tourism')
                    ->subject('Your Email Verification Code - Dunes Discovery Tourism')
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;'>
                            <div style='text-align: center; margin-bottom: 20px;'>
                                <img src='https://dunesdiscoverytourism.com/images/logo.png' alt='Dunes Discovery Tourism' style='max-width: 150px;'>
                            </div>
                            <h2 style='color: #d2a13b; text-align: center;'>Verify Your Email Address</h2>
                            <p>Thank you for booking with Dunes Discovery Tourism. Please use the verification code below to complete your booking:</p>
                            <div style='background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333; border: 1px dashed #d2a13b; margin: 20px 0;'>
                                {$otp}
                            </div>
                            <p style='color: #666; font-size: 12px; text-align: center;'>This verification code is valid for 5 minutes. If you did not request this code, please ignore this email.</p>
                            <hr style='border: 0; border-top: 1px solid #eee; margin-top: 30px;'>
                            <p style='color: #999; font-size: 11px; text-align: center;'>&copy; " . date('Y') . " Dunes Discovery Tourism. All rights reserved.</p>
                        </div>
                    ");
            });

            return response()->json(['success' => true, 'message' => 'OTP sent successfully to ' . $email]);

        } catch (\Exception $e) {
            Log::error("Failed to dispatch OTP email to {$email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please check your email address and try again.'
            ]);
        }
    }

    /**
     * Verify the user's OTP.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $email = trim(strtolower($request->input('email', '')));
        $otp = preg_replace('/[^0-9]/', '', (string)$request->input('otp', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        if (strlen($otp) !== 6) {
            return response()->json(['success' => false, 'message' => 'OTP must be exactly 6 digits'], 400);
        }

        $sessionEmail = session('otp_email');
        $sessionCode = session('otp_code');
        $sessionExpiry = session('otp_expiry');

        if ($sessionEmail === $email 
            && hash_equals((string)$sessionCode, $otp) 
            && time() < (int)$sessionExpiry
        ) {
            session(['email_verified_' . md5($email) => true]);
            
            // Cleanup OTP session variables
            session()->forget(['otp_email', 'otp_code', 'otp_expiry']);

            // Persist email to verified_emails table
            try {
                VerifiedEmail::updateOrCreate(['email' => $email], ['verified_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Failed to save verified email to DB: " . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Email verified successfully']);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP. Please request a new one.'
        ]);
    }
}
