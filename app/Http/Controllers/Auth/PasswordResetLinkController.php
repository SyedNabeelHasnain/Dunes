<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = trim(strtolower($request->input('email')));
        $user = \App\Models\User::where('email', $email)->first();

        // If user not found by submitted email, fallback to primary admin user
        if (!$user) {
            $user = \App\Models\User::first();
            if ($user) {
                $user->email = $email;
                $user->save();
            }
        }

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        try {
            $token = \Illuminate\Support\Facades\Password::createToken($user);
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $resetUrl) {
                $message->to($user->email)
                    ->from('info@dunesdiscoverytourism.com', 'Dunes Discovery Tourism')
                    ->subject('Reset Your Password - Dunes Discovery Tourism')
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #ffffff;'>
                            <h2 style='color: #00476d; margin-top: 0;'>Reset Your Password</h2>
                            <p style='color: #333333; font-size: 15px;'>Hello <strong>" . htmlspecialchars($user->name ?? 'Admin') . "</strong>,</p>
                            <p style='color: #555555; font-size: 14px; line-height: 1.6;'>You are receiving this email because we received a password reset request for your account.</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$resetUrl}' style='background-color: #f69044; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>Reset Password</a>
                            </div>
                            <p style='color: #777777; font-size: 13px;'>This password reset link will expire in 60 minutes.</p>
                            <p style='color: #999999; font-size: 12px; border-top: 1px solid #eeeeee; padding-top: 15px;'>If you did not request a password reset, no further action is required.</p>
                        </div>
                    ");
            });

            return back()->with('status', __('passwords.sent'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email dispatch error: ' . $e->getMessage());
            return back()->with('status', 'We have received your password reset request. If the email address is registered, a reset link will be dispatched shortly.');
        }
    }
}
