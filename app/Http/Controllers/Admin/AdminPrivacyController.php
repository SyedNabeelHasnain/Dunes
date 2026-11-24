<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivacyConsent;
use App\Models\Setting;
use App\Services\Governance\ApprovalWorkflowService;
use App\Services\Governance\ConfigAuditService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPrivacyController extends Controller
{
    public function __construct(
        protected ConfigAuditService $audit,
        protected ApprovalWorkflowService $approvals,
        protected SettingsService $settingsService,
    ) {}

    public function index(): View
    {
        $all = $this->settingsService->all();
        $settings = [
            'privacy_consent_banner_enabled' => $all['privacy_consent_banner_enabled'] ?? '1',
            'privacy_strict_consent_gating' => $all['privacy_strict_consent_gating'] ?? '1',
            'privacy_consent_version' => $all['privacy_consent_version'] ?? '1.0',
            'privacy_banner_headline' => $all['privacy_banner_headline'] ?? 'Your Privacy & Cookie Choices',
            'privacy_banner_summary' => $all['privacy_banner_summary'] ?? 'We use essential cookies to run our booking system cleanly, and optional analytics/marketing cookies (Google Consent Mode v2) only with your consent.',
            'privacy_contact_email' => $all['privacy_contact_email'] ?? ($all['site_email'] ?? 'privacy@dunesdiscoverytourism.com'),
            'privacy_log_retention_days' => $all['privacy_log_retention_days'] ?? '365',
        ];

        $recentConsents = PrivacyConsent::latest('consented_at')->limit(30)->get();
        $totalLogs = PrivacyConsent::count();
        $stats = [
            'total' => $totalLogs,
            'accept_all' => PrivacyConsent::where('action', 'accept_all')->count(),
            'reject_all' => PrivacyConsent::where('action', 'reject_all')->count(),
            'custom' => PrivacyConsent::where('action', 'custom')->count(),
            'withdrawals' => PrivacyConsent::where('action', 'withdraw')->count(),
            'analytics_opt_in_pct' => $totalLogs > 0 ? round((PrivacyConsent::where('analytics', true)->count() / $totalLogs) * 100, 1) : 0,
        ];

        return view('admin.settings.privacy', compact('settings', 'recentConsents', 'stats'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'privacy_consent_banner_enabled' => ['nullable', 'boolean'],
            'privacy_strict_consent_gating' => ['nullable', 'boolean'],
            'privacy_consent_version' => ['required', 'string', 'max:32'],
            'privacy_banner_headline' => ['required', 'string', 'max:200'],
            'privacy_banner_summary' => ['required', 'string', 'max:1000'],
            'privacy_contact_email' => ['required', 'email', 'max:160'],
            'privacy_log_retention_days' => ['required', 'integer', 'min:30', 'max:1825'],
        ]);

        $newPayload = [
            'privacy_consent_banner_enabled' => $request->boolean('privacy_consent_banner_enabled') ? '1' : '0',
            'privacy_strict_consent_gating' => $request->boolean('privacy_strict_consent_gating') ? '1' : '0',
            'privacy_consent_version' => trim((string) $validated['privacy_consent_version']),
            'privacy_banner_headline' => trim((string) $validated['privacy_banner_headline']),
            'privacy_banner_summary' => trim((string) $validated['privacy_banner_summary']),
            'privacy_contact_email' => trim((string) $validated['privacy_contact_email']),
            'privacy_log_retention_days' => (string) $validated['privacy_log_retention_days'],
        ];

        $existing = Setting::whereIn('setting_key', array_keys($newPayload))
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        $user = $request->user();
        if ($user && method_exists($user, 'hasAnyPermission') && ! $user->hasAnyPermission(['governance.approve', '*'])) {
            $approval = $this->approvals->propose(
                section: 'privacy',
                targetKey: 'privacy_consent_policy',
                summary: 'Proposed privacy & Consent Mode v2 policy update',
                payload: $newPayload,
                previousPayload: $existing,
                requester: $user,
            );

            return redirect()->route('admin.settings.privacy.index')
                ->with('success', "Privacy policy update submitted for approval (#{$approval->id}).");
        }

        foreach ($newPayload as $key => $val) {
            Setting::setVal($key, $val);
            if (($existing[$key] ?? null) !== $val) {
                $this->audit->log(
                    section: 'privacy',
                    action: 'updated',
                    settingKey: $key,
                    oldValue: $existing[$key] ?? null,
                    newValue: $val,
                    summary: "Updated privacy setting {$key}",
                    actor: $user,
                );
            }
        }

        return redirect()->route('admin.settings.privacy.index')->with('success', 'Privacy & Consent Mode v2 configuration updated.');
    }
}
