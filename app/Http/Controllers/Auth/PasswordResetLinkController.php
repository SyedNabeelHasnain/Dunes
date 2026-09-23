<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $user = User::where('email', $email)->first();
        if (! $user) {
            return back()->with('status', __('passwords.sent'));
        }

        try {
            $token = Password::createToken($user);
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            Mail::to($user->email)->send(new AdminPasswordResetMail($user, $resetUrl));

            return back()->with('status', __('passwords.sent'));
        } catch (\Throwable $e) {
            Log::error('Password reset email dispatch error: '.$e->getMessage());

            return back()->with('status', 'We have received your password reset request. If the email address is registered, a reset link will be dispatched shortly.');
        }
    }
}
