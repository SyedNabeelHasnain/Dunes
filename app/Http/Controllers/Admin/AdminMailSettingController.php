<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\EmailMarketingService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminMailSettingController extends Controller
{
    protected SettingsService $settingsService;
    protected EmailMarketingService $emailService;

    public function __construct(SettingsService $settingsService, EmailMarketingService $emailService)
    {
        $this->settingsService = $settingsService;
        $this->emailService = $emailService;
    }

    /**
     * Show SMTP & Mail Settings screen.
     */
    public function index()
    {
        $settings = $this->settingsService->all()->all();
        return view('admin.settings.mail', compact('settings'));
    }

    /**
     * Update SMTP & Newsletter settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'smtp_driver' => 'required|string|in:smtp,sendmail,log',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|in:25,465,587,2525',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_from_address' => 'required|email:filter|max:255',
            'smtp_from_name' => 'required|string|max:255',
            'smtp_reply_to' => 'nullable|email:filter|max:255',
            'newsletter_enabled' => 'nullable|in:0,1',
            'newsletter_batch_size' => 'required|integer|min:5|max:250',
            'newsletter_batch_delay' => 'required|integer|min:0|max:10',
        ]);

        $keys = [
            'smtp_driver', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_from_address', 'smtp_from_name',
            'smtp_reply_to', 'newsletter_batch_size', 'newsletter_batch_delay'
        ];

        foreach ($keys as $k) {
            Setting::updateOrCreate(
                ['setting_key' => $k],
                ['setting_value' => (string)($validated[$k] ?? '')]
            );
        }

        // Only update password if provided
        if (!empty($validated['smtp_password'])) {
            Setting::updateOrCreate(
                ['setting_key' => 'smtp_password'],
                ['setting_value' => (string)$validated['smtp_password']]
            );
        }

        Setting::updateOrCreate(
            ['setting_key' => 'newsletter_enabled'],
            ['setting_value' => $request->has('newsletter_enabled') ? '1' : '0']
        );

        Cache::forget('site_settings_cache');

        return redirect()->route('admin.settings.mail')
            ->with('success', 'SMTP and Mailer settings saved successfully.');
    }

    /**
     * Test SMTP Connection & Send Diagnostic Email.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email:filter|max:255',
        ]);

        $recipient = trim($request->input('test_email'));
        $siteName = $this->settingsService->get('site_name', 'Dunes Discovery Tourism');

        $htmlContent = "
        <div style='font-family: sans-serif; padding: 20px; line-height: 1.6; color: #1e293b;'>
            <h2 style='color: #F58F43;'>SMTP Connection Test Successful!</h2>
            <p>Hello,</p>
            <p>This is a live diagnostic confirmation that your mail configuration on <strong>{$siteName}</strong> is properly configured and successfully communicating with your mail server.</p>
            <p style='font-size: 13px; color: #64748b;'>Timestamp: " . now()->toRfc2822String() . "</p>
        </div>";

        $res = $this->emailService->sendTestEmail($recipient, "SMTP Diagnostic Test - {$siteName}", $htmlContent);

        return response()->json($res);
    }
}
