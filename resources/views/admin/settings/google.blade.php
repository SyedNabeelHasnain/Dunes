@extends('layouts.admin')

@section('page_title', 'Google Integration settings')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-google"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">Google Integration Suite</h5>
                <div class="text-xs text-slate-500 mt-0.5">Manage Tag Manager, Analytics, Google Ads, and reCAPTCHA.</div>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- Master Switch -->
            <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 mb-6">
                <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3">Google Integration status</h6>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="google_active" id="google_active" value="1" {{ ($settings['google_active'] ?? '') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                    <label class="text-xs font-bold text-slate-900 cursor-pointer" for="google_active">Enable tracking code injection across all pages</label>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">When disabled, Google Tag Manager and Analytics scripts will not be loaded on the user-facing site.</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Analytics & Tag Manager -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-graph-up-arrow"></i> Tracking & Analytics
                        </h6>
                        
                        <div class="mb-4">
                            <label for="google_gtm_id" class="block text-xs font-bold text-slate-700 mb-1.5">Google Tag Manager ID</label>
                            <input type="text" name="google_gtm_id" id="google_gtm_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_gtm_id'] ?? '' }}" placeholder="e.g. GTM-XXXXXXX">
                            <div class="mt-1 text-[11px] text-slate-500">Used for tag container deployment.</div>
                        </div>

                        <div class="mb-4">
                            <label for="google_ga4_id" class="block text-xs font-bold text-slate-700 mb-1.5">Google Analytics 4 ID</label>
                            <input type="text" name="google_ga4_id" id="google_ga4_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_ga4_id'] ?? '' }}" placeholder="e.g. G-XXXXXXXXXX">
                            <div class="mt-1 text-[11px] text-slate-500">GA4 Measurement / Stream ID.</div>
                        </div>

                        <div>
                            <label for="google_site_verification" class="block text-xs font-bold text-slate-700 mb-1.5">Google Search Console Verification</label>
                            <input type="text" name="google_site_verification" id="google_site_verification" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Verification Code Hash">
                        </div>
                    </div>
                </div>

                <!-- Google Ads & Maps -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-megaphone"></i> Google Ads & Maps
                        </h6>
                        
                        <div class="mb-4">
                            <label for="google_ads_id" class="block text-xs font-bold text-slate-700 mb-1.5">Google Ads Conversion ID</label>
                            <input type="text" name="google_ads_id" id="google_ads_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_ads_id'] ?? '' }}" placeholder="e.g. AW-XXXXXXXXX">
                        </div>

                        <div class="mb-4">
                            <label for="google_conversion_label" class="block text-xs font-bold text-slate-700 mb-1.5">Google Ads Conversion Label</label>
                            <input type="text" name="google_conversion_label" id="google_conversion_label" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_conversion_label'] ?? '' }}" placeholder="e.g. abcdEFGH123456">
                        </div>

                        <div>
                            <label for="google_maps_api_key" class="block text-xs font-bold text-slate-700 mb-1.5">Google Maps API Key</label>
                            <input type="text" name="google_maps_api_key" id="google_maps_api_key" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_maps_api_key'] ?? '' }}" placeholder="Key for interactive location maps">
                        </div>
                    </div>
                </div>

                <!-- Google reCAPTCHA v3 -->
                <div class="col-span-1 md:col-span-2">
                    <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-2 flex items-center gap-2">
                            <i class="bi bi-shield-check"></i> Google reCAPTCHA v3 Protection
                        </h6>
                        <p class="text-xs text-slate-500 mb-4">Protects booking and inquiry contact forms from automated bots.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="recaptcha_site_key" class="block text-xs font-bold text-slate-700 mb-1.5">reCAPTCHA v3 Site Key</label>
                                <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['recaptcha_site_key'] ?? '' }}" placeholder="Site Key">
                            </div>
                            <div>
                                <label for="recaptcha_secret_key" class="block text-xs font-bold text-slate-700 mb-1.5">reCAPTCHA v3 Secret Key</label>
                                <input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['recaptcha_secret_key'] ?? '' }}" placeholder="Secret Key">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-check2-circle"></i> Save Google Configurations
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
