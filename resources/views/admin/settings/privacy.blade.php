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

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90">Save Privacy Configuration</button>
            </div>
        </form>
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
