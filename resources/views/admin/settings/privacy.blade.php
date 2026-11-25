@extends('layouts.admin')

@section('page_title', 'Privacy, GDPR Consent Mode v2 & Preferences')

@section('content')
<div class="space-y-6 mb-8">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase">Consent Records</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-emerald-600 uppercase">Analytics Opt-In</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['analytics_opt_in_pct'] }}%</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase">Accept All</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['accept_all'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-400 uppercase">Essential Only</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['reject_all'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-amber-600 uppercase">Withdrawals</div>
            <div class="text-2xl font-black text-amber-800 mt-1">{{ $stats['withdrawals'] }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
        <h5 class="text-base font-extrabold text-slate-900 mb-1">Consent Mode v2 &amp; Script Gating Policy</h5>
        <p class="text-xs text-slate-500 mb-5">Controls banner visibility, strict pre-consent blocking of Clarity / Meta Pixel / session recording, and retention policy.</p>

        <form method="POST" action="{{ route('admin.settings.privacy.update') }}" class="space-y-4">
            @csrf
            <div class="flex flex-wrap gap-6 bg-slate-50 border border-slate-200 rounded-xl p-4">
                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-800">
                    <input type="hidden" name="privacy_consent_banner_enabled" value="0">
                    <input type="checkbox" name="privacy_consent_banner_enabled" value="1" @checked($settings['privacy_consent_banner_enabled'] === '1') class="rounded border-slate-300 text-primary">
                    Enable Public Cookie Banner &amp; Preference Modal
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-800">
                    <input type="hidden" name="privacy_strict_consent_gating" value="0">
                    <input type="checkbox" name="privacy_strict_consent_gating" value="1" @checked($settings['privacy_strict_consent_gating'] === '1') class="rounded border-slate-300 text-primary">
                    Enforce Strict Script &amp; Telemetry Gating Prior to Consent
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Consent Banner Headline</label>
                    <input type="text" name="privacy_banner_headline" value="{{ $settings['privacy_banner_headline'] }}" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Policy Version</label>
                    <input type="text" name="privacy_consent_version" value="{{ $settings['privacy_consent_version'] }}" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Privacy Contact Email (DPO)</label>
                    <input type="email" name="privacy_contact_email" value="{{ $settings['privacy_contact_email'] }}" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Banner Summary Text</label>
                    <textarea name="privacy_banner_summary" rows="2" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">{{ $settings['privacy_banner_summary'] }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Consent Log Retention (days)</label>
                    <input type="number" name="privacy_log_retention_days" value="{{ $settings['privacy_log_retention_days'] }}" min="30" max="1825" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-[11px] text-slate-500">Scheduled purge runs daily via <code>privacy:purge-expired</code> honoring the retention window above.</span>
                <div class="flex items-center gap-2">
                    <button type="submit" formaction="{{ route('admin.settings.privacy.purge-expired') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 cursor-pointer">
                        Run Retention Purge Now
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90 cursor-pointer">Save Privacy Configuration</button>
                </div>
            </div>
        </form>
    </div>

    <!-- GDPR / UAE PDPL Data Subject Rights (DSAR) Operations -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h5 class="text-base font-extrabold text-slate-900">Data Subject Rights (DSAR Lookup, Export &amp; Anonymization)</h5>
                <p class="text-xs text-slate-500 mt-0.5">Search, export portable JSON records, or anonymize marketing/inquiry PII by subject email. Legal counsel review is required for jurisdiction-specific GDPR/PDPL sign-off.</p>
            </div>
            <span class="rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-[11px] font-bold text-amber-800">Audit-Logged Action</span>
        </div>

        <form method="GET" action="{{ route('admin.settings.privacy.dsar') }}" class="flex flex-wrap items-center gap-3">
            <input type="email" name="email" value="{{ $dsarEmail ?? '' }}" required placeholder="Enter data subject email address..." class="flex-1 min-w-64 rounded-xl border border-slate-200 px-3.5 py-2 text-xs">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 cursor-pointer">Lookup Subject Records</button>
        </form>

        @if(!empty($dsarEmail) && isset($dsarResults))
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3 text-xs">
                <div class="font-bold text-slate-900">Records matched for <code>{{ $dsarEmail }}</code>:</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-lg bg-white border border-slate-200 p-3"><div class="text-slate-400">Subscribers</div><div class="text-lg font-black">{{ $dsarResults['counts']['subscribers'] }}</div></div>
                    <div class="rounded-lg bg-white border border-slate-200 p-3"><div class="text-slate-400">Contact Inquiries</div><div class="text-lg font-black">{{ $dsarResults['counts']['contacts'] }}</div></div>
                    <div class="rounded-lg bg-white border border-slate-200 p-3"><div class="text-slate-400">WhatsApp Leads</div><div class="text-lg font-black">{{ $dsarResults['counts']['whatsapp_leads'] }}</div></div>
                    <div class="rounded-lg bg-white border border-slate-200 p-3"><div class="text-slate-400">Bookings (Statutory)</div><div class="text-lg font-black">{{ $dsarResults['counts']['bookings'] }}</div></div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200">
                    <form method="POST" action="{{ route('admin.settings.privacy.dsar-export') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $dsarEmail }}">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 cursor-pointer">
                            <i class="bi bi-download me-1"></i> Export Portable JSON
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.settings.privacy.dsar-erase') }}" class="flex flex-wrap items-center gap-3" onsubmit="return confirm('Permanently anonymize subscriber, contact, and WhatsApp lead PII for this email? Statutory booking ledgers are preserved.');">
                        @csrf
                        <input type="hidden" name="email" value="{{ $dsarEmail }}">
                        <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-rose-700">
                            <input type="checkbox" name="confirm_erase" value="1" required class="rounded border-rose-300 text-rose-600">
                            Confirm verified identity &amp; irreversible anonymization
                        </label>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer">
                            Anonymize Marketing &amp; Inquiry PII
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h6 class="text-sm font-extrabold text-slate-900">Recent Consent Ledger Entries</h6>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-4 py-3 text-left">Timestamp</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Analytics / Marketing / Functional</th>
                        <th class="px-4 py-3 text-left">Locale / Version</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentConsents as $rec)
                        <tr>
                            <td class="px-4 py-2.5 text-slate-500">{{ optional($rec->consented_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-2.5 font-bold text-slate-800">{{ $rec->action }}</td>
                            <td class="px-4 py-2.5 font-mono text-[11px]">A:{{ $rec->analytics ? 'Y' : 'N' }} M:{{ $rec->marketing ? 'Y' : 'N' }} F:{{ $rec->functional ? 'Y' : 'N' }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $rec->locale }} · v{{ $rec->consent_version }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-slate-500">No consent submissions logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
