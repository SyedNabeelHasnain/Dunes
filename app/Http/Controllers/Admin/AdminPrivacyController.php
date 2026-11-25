<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\PrivacyConsent;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\WhatsappInquiry;
use App\Services\Governance\ApprovalWorkflowService;
use App\Services\Governance\ConfigAuditService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

    /**
     * DSAR Data Subject Lookup across subscribers, inquiries, WhatsApp leads, and bookings.
     */
    public function dsarLookup(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:filter', 'max:255'],
        ]);
        $email = strtolower(trim($validated['email']));
        $dsarResults = $this->collectDsarRecords($email);

        if ($request->wantsJson()) {
            return response()->json(['email' => $email, 'records' => $dsarResults]);
        }

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
        $dsarEmail = $email;

        return view('admin.settings.privacy', compact('settings', 'recentConsents', 'stats', 'dsarEmail', 'dsarResults'));
    }

    /**
     * Export portable GDPR/DSAR JSON package for a verified subject email.
     */
    public function dsarExport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:filter', 'max:255'],
        ]);
        $email = strtolower(trim($validated['email']));
        $records = $this->collectDsarRecords($email);
        $hashRef = substr(hash('sha256', $email), 0, 12);

        $this->audit->log(
            section: 'privacy',
            action: 'dsar_exported',
            settingKey: "dsar:{$hashRef}",
            oldValue: null,
            newValue: [
                'subject_hash' => $hashRef,
                'counts' => $records['counts'],
            ],
            summary: "Exported GDPR DSAR data package for subject hash {$hashRef}",
            actor: $request->user(),
        );

        return response()->json([
            'exported_at' => now()->toIso8601String(),
            'subject_email' => $email,
            'legal_notice' => 'Portable Data Subject Access Request (DSAR) export generated by Dunes Discovery Tourism CMS.',
            'records' => $records,
        ], 200, [
            'Content-Disposition' => 'attachment; filename="dsar-export-'.$hashRef.'-'.now()->format('Ymd').'.json"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /**
     * Anonymize/erase marketing & inquiry PII for a subject email while preserving statutory booking ledgers.
     */
    public function dsarErase(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:filter', 'max:255'],
            'confirm_erase' => ['accepted'],
        ]);
        $email = strtolower(trim($validated['email']));
        $hashRef = substr(hash('sha256', $email), 0, 12);

        $affected = [
            'subscribers_anonymized' => 0,
            'contacts_anonymized' => 0,
            'whatsapp_leads_anonymized' => 0,
            'bookings_retained_statutory' => 0,
        ];

        DB::transaction(function () use ($email, &$affected) {
            $subscribers = Subscriber::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->get();
            foreach ($subscribers as $sub) {
                $sub->forceFill([
                    'email' => 'erased-'.substr(hash('sha256', $email.'|sub|'.$sub->id), 0, 16).'@erased.invalid',
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => null,
                    'ip_address' => null,
                    'status' => 'unsubscribed',
                    'unsubscribed_at' => now(),
                    'unsubscribe_reason' => 'GDPR DSAR Erasure',
                ])->save();
                $affected['subscribers_anonymized']++;
            }

            $contacts = Contact::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->get();
            foreach ($contacts as $contact) {
                $contact->forceFill([
                    'name' => 'Erased Subject',
                    'email' => 'erased-'.substr(hash('sha256', $email.'|cnt|'.$contact->id), 0, 16).'@erased.invalid',
                    'phone' => null,
                    'ip_address' => null,
                    'message' => '[Erased per GDPR DSAR request]',
                ])->save();
                $affected['contacts_anonymized']++;
            }

            $waLeads = WhatsappInquiry::whereRaw('LOWER(email) = ?', [$email])->get();
            foreach ($waLeads as $lead) {
                $lead->forceFill([
                    'name' => 'Erased Subject',
                    'email' => null,
                    'phone' => 'ERASED',
                    'client_ip' => null,
                    'message_text' => '[Erased per GDPR DSAR request]',
                ])->save();
                $affected['whatsapp_leads_anonymized']++;
            }

            $affected['bookings_retained_statutory'] = Booking::withTrashed()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->count();
        });

        $this->audit->log(
            section: 'privacy',
            action: 'dsar_erased',
            settingKey: "dsar:{$hashRef}",
            oldValue: null,
            newValue: $affected,
            summary: "Executed GDPR DSAR anonymization for subject hash {$hashRef}",
            actor: $request->user(),
        );

        $msg = "DSAR anonymization complete: {$affected['subscribers_anonymized']} subscriber(s), {$affected['contacts_anonymized']} contact inquiry(ies), and {$affected['whatsapp_leads_anonymized']} WhatsApp lead(s) anonymized."
            .($affected['bookings_retained_statutory'] > 0
                ? " {$affected['bookings_retained_statutory']} commercial booking record(s) were retained under UAE tourism/financial statutory record-keeping rules."
                : '');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'affected' => $affected]);
        }

        return redirect()->route('admin.settings.privacy.index')->with('success', $msg);
    }

    /**
     * Trigger immediate retention-policy purge of expired privacy consent & RUM telemetry records.
     */
    public function purgeExpired(Request $request): RedirectResponse
    {
        Artisan::call('privacy:purge-expired');
        $output = trim((string) Artisan::output());

        return redirect()->route('admin.settings.privacy.index')
            ->with('success', $output !== '' ? $output : 'Expired privacy consent and telemetry logs purged per retention policy.');
    }

    protected function collectDsarRecords(string $email): array
    {
        $subscribers = Subscriber::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get(['id', 'email', 'first_name', 'last_name', 'phone', 'status', 'source', 'subscribed_at', 'unsubscribed_at', 'created_at']);

        $contacts = Contact::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get(['id', 'name', 'email', 'phone', 'subject', 'status', 'created_at']);

        $whatsappLeads = WhatsappInquiry::whereRaw('LOWER(email) = ?', [$email])
            ->get(['id', 'name', 'email', 'phone', 'tour_name', 'status', 'created_at']);

        $bookings = Booking::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get(['id', 'reference', 'tour_name', 'tour_date', 'name', 'email', 'phone', 'total', 'currency', 'status', 'payment_status', 'created_at']);

        return [
            'counts' => [
                'subscribers' => $subscribers->count(),
                'contacts' => $contacts->count(),
                'whatsapp_leads' => $whatsappLeads->count(),
                'bookings' => $bookings->count(),
            ],
            'subscribers' => $subscribers->toArray(),
            'contacts' => $contacts->toArray(),
            'whatsapp_leads' => $whatsappLeads->toArray(),
            'bookings' => $bookings->toArray(),
        ];
    }
}
