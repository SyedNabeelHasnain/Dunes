<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== STANDALONE SMTP & MAIL DISPATCH DIAGNOSTIC ===\n\n";

$email = 'dunesdiscovery85@gmail.com';

echo "1. Checking User record for {$email}...\n";
$user = \App\Models\User::where('email', $email)->first();
if (!$user) {
    echo "   User NOT found by email. Finding primary user...\n";
    $user = \App\Models\User::first();
    if ($user) {
        $user->email = $email;
        $user->save();
        echo "   Updated primary user (ID {$user->id}) email to {$email}.\n";
    } else {
        echo "   CRITICAL: No user records exist in the database!\n";
        exit(1);
    }
}
echo "   User confirmed: ID={$user->id}, Name={$user->name}, Email={$user->email}\n\n";

echo "2. Generating password reset token...\n";
try {
    $token = \Illuminate\Support\Facades\Password::createToken($user);
    $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ], false));
    echo "   Token generated successfully.\n";
    echo "   Reset URL: {$resetUrl}\n\n";
} catch (\Throwable $e) {
    echo "   ERROR generating token: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "3. Attempting Mail::send to {$email} via info@dunesdiscoverytourism.com...\n";
try {
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
                </div>
            ");
    });
    echo "   SUCCESS: Mail::send executed without exception!\n";
} catch (\Throwable $e) {
    echo "   CRITICAL MAIL EXCEPTION: " . $e->getMessage() . "\n";
    echo "   Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
