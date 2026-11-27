@php
    $bannerEnabled = ($settings['privacy_consent_banner_enabled'] ?? '1') !== '0';
    $bannerHeadline = $settings['privacy_banner_headline'] ?? 'Your Privacy & Cookie Choices';
    $bannerSummary = $settings['privacy_banner_summary'] ?? 'We use essential cookies to keep bookings working securely, and optional analytics & marketing cookies only with your consent.';
    $privacyEmail = $settings['privacy_contact_email'] ?? ($settings['site_email'] ?? 'info@dunesdiscoverytourism.com');
@endphp

@if($bannerEnabled)
<div x-data="{
        showBanner: false,
        showPrefs: false,
        analytics: false,
        marketing: false,
        functional: false,
        saving: false,
        init() {
            const current = window.DunesConsent ? window.DunesConsent.get() : null;
            const currentVersion = window.DunesConsent ? String(window.DunesConsent.version || '') : '';
            const versionMismatch = current && current.version && currentVersion && String(current.version) !== currentVersion;
            if (!current || !current.decided || versionMismatch) {
                this.showBanner = true;
            } else {
                this.analytics = !!current.analytics;
                this.marketing = !!current.marketing;
                this.functional = !!current.functional;
            }
            window.addEventListener('open-cookie-preferences', () => {
                const latest = window.DunesConsent ? window.DunesConsent.get() : null;
                if (latest) {
                    this.analytics = !!latest.analytics;
                    this.marketing = !!latest.marketing;
                    this.functional = !!latest.functional;
                }
                this.showPrefs = true;
            });
        },
        async submitConsent(action) {
            this.saving = true;
            if (action === 'accept_all') { this.analytics = true; this.marketing = true; this.functional = true; }
            if (action === 'reject_all' || action === 'withdraw') { this.analytics = false; this.marketing = false; this.functional = false; }

            const payload = {
                consent_id: (window.DunesConsent && window.DunesConsent.get()?.id) || '',
                action: action,
                analytics: this.analytics,
                marketing: this.marketing,
                functional: this.functional,
                page_url: window.location.href
            };

            if (window.DunesConsent && typeof window.DunesConsent.update === 'function') {
                window.DunesConsent.update(payload);
            }

            try {
                const res = await fetch('/api/v1/privacy/consent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.consent && data.consent.id && window.DunesConsent && typeof window.DunesConsent.update === 'function') {
                        window.DunesConsent.update(Object.assign({}, payload, { id: data.consent.id, version: data.consent.version || window.DunesConsent.version }));
                    }
                }
            } catch (e) {}

            this.saving = false;
            this.showBanner = false;
            this.showPrefs = false;
        }
    }"
    class="relative z-50">
    <!-- Cookie Consent Bar -->
    <div x-show="showBanner && !showPrefs"
         x-cloak
         role="region"
         aria-label="Cookie Consent Notice"
         class="fixed bottom-4 start-4 end-4 sm:start-6 sm:end-auto sm:max-w-lg bg-slate-950/95 backdrop-blur-md text-white rounded-2xl border border-slate-800 shadow-2xl p-5 z-50">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div class="space-y-2 flex-1">
                <p class="text-sm font-extrabold text-white">{{ $bannerHeadline }}</p>
                <p class="text-xs text-slate-300 leading-relaxed">
                    {{ $bannerSummary }}
                    <a href="{{ localized_route('cookies') }}" class="text-amber-400 underline hover:text-amber-300">Cookie Policy</a> ·
                    <a href="{{ localized_route('privacy') }}" class="text-amber-400 underline hover:text-amber-300">Privacy Policy</a>
                </p>
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <button type="button" @click="submitConsent('accept_all')" :disabled="saving" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition cursor-pointer">
                        Accept All
                    </button>
                    <button type="button" @click="submitConsent('reject_all')" :disabled="saving" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 text-xs font-bold transition cursor-pointer">
                        Essential Only
                    </button>
                    <button type="button" @click="showPrefs = true" class="px-3 py-2 rounded-xl border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition cursor-pointer">
                        Manage Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Granular Cookie Preference Modal (WCAG 2.2 Dialog Semantics) -->
    <div x-show="showPrefs"
         x-cloak
         @keydown.escape.window="showPrefs = false"
         role="dialog"
         aria-modal="true"
         aria-labelledby="cookiePrefsTitle"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4">
        <div @click.outside="showPrefs = false" class="bg-white text-slate-900 rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 id="cookiePrefsTitle" class="text-base font-extrabold text-slate-900">Cookie &amp; Privacy Preferences</h2>
                <button type="button" @click="showPrefs = false" aria-label="Close Cookie Preferences" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="font-bold text-slate-900">Strictly Necessary Cookies</div>
                        <div class="text-slate-500">Required for CSRF protection, session security, and booking cart continuity.</div>
                    </div>
                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 font-bold shrink-0">Always On</span>
                </div>

                <label class="rounded-xl border border-slate-200 p-3 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50">
                    <div>
                        <div class="font-bold text-slate-900">Analytics &amp; Experience Telemetry</div>
                        <div class="text-slate-500">Helps us measure Core Web Vitals and improve booking usability (GA4, Clarity, heatmaps).</div>
                    </div>
                    <input type="checkbox" x-model="analytics" class="w-4 h-4 rounded border-slate-300 text-primary">
                </label>

                <label class="rounded-xl border border-slate-200 p-3 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50">
                    <div>
                        <div class="font-bold text-slate-900">Advertising &amp; Attribution Cookies</div>
                        <div class="text-slate-500">Supports Google Ads &amp; Meta attribution only after explicit permission.</div>
                    </div>
                    <input type="checkbox" x-model="marketing" class="w-4 h-4 rounded border-slate-300 text-primary">
                </label>

                <label class="rounded-xl border border-slate-200 p-3 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50">
                    <div>
                        <div class="font-bold text-slate-900">Functional Personalization</div>
                        <div class="text-slate-500">Saves preferred currency and recently viewed desert safaris.</div>
                    </div>
                    <input type="checkbox" x-model="functional" class="w-4 h-4 rounded border-slate-300 text-primary">
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="submitConsent('withdraw')" :disabled="saving" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    Reject / Withdraw Non-Essential
                </button>
                <div class="flex gap-2">
                    <button type="button" @click="submitConsent('custom')" :disabled="saving" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:opacity-90">
                        Save Choices
                    </button>
                    <button type="button" @click="submitConsent('accept_all')" :disabled="saving" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90">
                        Accept All
                    </button>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 text-center">
                Data protection inquiries: <a href="mailto:{{ $privacyEmail }}" class="font-semibold text-primary hover:underline">{{ $privacyEmail }}</a>
            </p>
        </div>
    </div>
</div>
@endif
