@extends('layouts.admin')

@section('page_title', 'Google Integrations & Local SEO')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-google"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">Google Integration Suite & Local SEO</h5>
                <div class="text-xs text-slate-500 mt-0.5">Manage Google Business Profile (GBP), Local SEO entities, Tag Manager, Analytics, Ads, and reCAPTCHA.</div>
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
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                    <label class="text-xs font-bold text-slate-900 cursor-pointer" for="google_active">Enable tracking code injection across all pages</label>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">When disabled, Google Tag Manager and Analytics scripts will not be loaded on the user-facing site.</div>
            </div>

            <!-- Google Business Profile (GBP) & Local SEO Section -->
            <div class="p-6 bg-gradient-to-br from-blue-50/40 via-white to-slate-50/70 rounded-2xl border border-blue-200/60 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-blue-100/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shadow-sm">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-sm font-black text-slate-900 leading-tight">Google Business Profile & Local SEO Entity</h6>
                            <div class="text-xs text-slate-500 mt-0.5">Canonical entity data feeding Schema.org microdata, Local SEO rankings, and Google Things To Do (GTTD).</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            <i class="bi bi-patch-check-fill text-emerald-600"></i> DET #1430583
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-[11px] font-bold">
                            <i class="bi bi-diagram-3-fill text-blue-600"></i> Schema Graph Linked
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Form Controls (8 cols) -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="google_place_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Google Place ID <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="google_place_id" id="google_place_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" placeholder="ChIJbWsIEIVEdEER4uHEhb2dbcQ" required>
                                <div class="mt-1 text-[11px] text-slate-500">Google Place ID for Dunes Discovery Tourism. Powers reviews & GTTD matching.</div>
                            </div>
                            <div>
                                <label for="google_cid" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Google Customer ID (CID)
                                </label>
                                <input type="text" name="google_cid" id="google_cid" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_cid'] ?? '14185012580795441634' }}" placeholder="14185012580795441634">
                                <div class="mt-1 text-[11px] text-slate-500">Decimal CID (14185012580795441634) or Hex (0xc4db9db186e4e1e2).</div>
                            </div>
                        </div>

                        <div>
                            <label for="google_review_url" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Direct Google Reviews URL
                            </label>
                            <input type="url" name="google_review_url" id="google_review_url" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_review_url'] ?? 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ' }}" placeholder="https://search.google.com/local/writereview?placeid=...">
                            <div class="mt-1 text-[11px] text-slate-500">Direct write-review intent URL shown on Contact page and customer communications.</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="site_latitude" class="block text-xs font-bold text-slate-700 mb-1.5">Latitude (GPS)</label>
                                <input type="text" name="site_latitude" id="site_latitude" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_latitude'] ?? '25.2048' }}" placeholder="25.2048">
                            </div>
                            <div>
                                <label for="site_longitude" class="block text-xs font-bold text-slate-700 mb-1.5">Longitude (GPS)</label>
                                <input type="text" name="site_longitude" id="site_longitude" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_longitude'] ?? '55.2708' }}" placeholder="55.2708">
                            </div>
                            <div>
                                <label for="site_postal_code" class="block text-xs font-bold text-slate-700 mb-1.5">Postal Code / PO Box</label>
                                <input type="text" name="site_postal_code" id="site_postal_code" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_postal_code'] ?? '00000' }}" placeholder="00000">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="google_primary_category" class="block text-xs font-bold text-slate-700 mb-1.5">Primary Business Category</label>
                                <input type="text" name="google_primary_category" id="google_primary_category" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_primary_category'] ?? 'Tour Operator' }}" placeholder="Tour Operator">
                            </div>
                            <div>
                                <label for="google_business_hours" class="block text-xs font-bold text-slate-700 mb-1.5">Operating Hours Specification</label>
                                <input type="text" name="google_business_hours" id="google_business_hours" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_business_hours'] ?? 'Monday - Sunday: 24 Hours (00:00 - 23:59)' }}" placeholder="Mon-Sun: 24 Hours">
                            </div>
                        </div>

                        <div>
                            <label for="google_service_area" class="block text-xs font-bold text-slate-700 mb-1.5">Service Areas Covered</label>
                            <input type="text" name="google_service_area" id="google_service_area" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['google_service_area'] ?? 'Dubai, Sharjah, Ajman, Lahbab Desert, Al Marmoom, United Arab Emirates' }}" placeholder="Dubai, Sharjah, Lahbab...">
                        </div>
                    </div>

                    <!-- Right Entity Preview & Test Actions (4 cols) -->
                    <div class="lg:col-span-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Live Entity Preview</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">Active</span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 mb-4">
                                <div class="text-xs font-black text-slate-900 leading-snug">{{ $settings['site_name'] ?? 'Dunes Discovery Tourism L.L.C.' }}</div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                    <i class="bi bi-patch-check-fill text-blue-600"></i> DET Commercial License #{{ $settings['company_license_number'] ?? '1430583' }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                    <i class="bi bi-geo-alt-fill text-primary"></i> {{ $settings['site_latitude'] ?? '25.2048' }}° N, {{ $settings['site_longitude'] ?? '55.2708' }}° E
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                    <i class="bi bi-star-fill text-amber-500"></i> Rating 4.9 ★ (2,840+ Reviews)
                                </div>
                            </div>

                            <div class="space-y-1.5 text-[11px] text-slate-600 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Geo Meta Tags:</span>
                                    <span class="font-bold text-slate-800">AE-DU / Dubai</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Schema Microdata:</span>
                                    <span class="font-bold text-slate-800">TravelAgency Graph</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">GTTD Operator:</span>
                                    <span class="font-bold text-emerald-600">Verification Ready</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-slate-100">
                            <a href="{{ $settings['google_review_url'] ?? ('https://search.google.com/local/writereview?placeid=' . ($settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ')) }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition">
                                <i class="bi bi-star-fill text-amber-500"></i> Test Review Modal
                            </a>
                            <a href="https://maps.google.com/?cid={{ $settings['google_cid'] ?? '14185012580795441634' }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition">
                                <i class="bi bi-map-fill text-blue-600"></i> Open Listing on Google Maps
                            </a>
                            <a href="https://maps.google.com/?q={{ $settings['site_latitude'] ?? '25.2048' }},{{ $settings['site_longitude'] ?? '55.2708' }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white transition">
                                <i class="bi bi-compass"></i> Test Directions Routing
                            </a>
                        </div>
                    </div>
                </div>
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
