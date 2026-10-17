@extends('layouts.admin')

@section('page_title', 'General Site Settings')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-sliders"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">General Site Settings & Identity</h5>
                <div class="text-xs text-slate-500 mt-0.5">Manage official brand identity, contact numbers, licensing, Google Maps, and verified social channels.</div>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Brand Visuals, Hero Poster & Fallback Media -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-image"></i> Brand Visuals, Homepage Hero Poster & Default Social Share Media
                            </h6>
                            <span class="rounded-full border border-primary/20 bg-amber-500/10 px-3 py-1 text-[11px] font-bold text-primary">Media Controls</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 1. Homepage Hero Video Poster -->
                            <div class="p-4 bg-white rounded-xl border border-slate-200 flex flex-col justify-between">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Homepage Hero Video Poster Image</label>
                                    <p class="text-[11px] text-slate-500 mb-3">The poster image displayed on the homepage while the background video streams, on mobile devices, and in Google VideoObject rich snippet schemas.</p>
                                    
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="w-24 h-16 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-2xs">
                                            @php
                                                $heroPosterPreview = !empty($settings['site_hero_poster']) 
                                                    ? (str_starts_with($settings['site_hero_poster'], 'http') ? $settings['site_hero_poster'] : asset(ltrim($settings['site_hero_poster'], '/'))) 
                                                    : asset('images/desert-safari-poster.avif');
                                            @endphp
                                            <img src="{{ $heroPosterPreview }}" alt="Hero Poster Preview" class="w-full h-full object-cover">
                                        </div>
                                        <div class="text-[11px] text-slate-500 overflow-hidden">
                                            <div class="font-bold text-slate-700">Active Poster</div>
                                            <div class="truncate text-slate-400 font-mono text-[10px]">{{ $settings['site_hero_poster'] ?? '/images/desert-safari-poster.avif' }}</div>
                                            <span class="inline-block mt-1 text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Native HD AVIF</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_hero_poster_file" class="block text-xs font-semibold text-slate-600 mb-1">Upload New Poster Image</label>
                                        <input type="file" name="site_hero_poster_file" id="site_hero_poster_file" accept="image/jpeg,image/png,image/webp,image/avif" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                        <div class="mt-1 text-[10px] text-slate-400">Max 8MB. Recommended: 900x600 or 1920x1080 (HD AVIF, WebP, JPG).</div>
                                    </div>

                                    <div>
                                        <label for="site_hero_poster" class="block text-xs font-semibold text-slate-600 mb-1">Or Specify Image Path / URL</label>
                                        <input type="text" name="site_hero_poster" id="site_hero_poster" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_hero_poster'] ?? '/images/desert-safari-poster.avif' }}" placeholder="/images/desert-safari-dubai-dune-discovery-tourism-HD.avif">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Sitewide Default OpenGraph / Social Share Image -->
                            <div class="p-4 bg-white rounded-xl border border-slate-200 flex flex-col justify-between">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Sitewide Default OpenGraph / Social Share Image</label>
                                    <p class="text-[11px] text-slate-500 mb-3">The default link preview thumbnail shown when pages without specific social cards are shared across WhatsApp, iMessage, Facebook, and Twitter/X.</p>
                                    
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="w-24 h-16 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-2xs">
                                            @php
                                                $ogDefaultPreview = !empty($settings['seo_default_og_image']) 
                                                    ? (str_starts_with($settings['seo_default_og_image'], 'http') ? $settings['seo_default_og_image'] : asset(ltrim($settings['seo_default_og_image'], '/'))) 
                                                    : asset('images/desert-safari-poster.avif');
                                            @endphp
                                            <img src="{{ $ogDefaultPreview }}" alt="Social Share Preview" class="w-full h-full object-cover">
                                        </div>
                                        <div class="text-[11px] text-slate-500 overflow-hidden">
                                            <div class="font-bold text-slate-700">Active Share Card</div>
                                            <div class="truncate text-slate-400 font-mono text-[10px]">{{ $settings['seo_default_og_image'] ?? '/images/desert-safari-poster.avif' }}</div>
                                            <span class="inline-block mt-1 text-[10px] text-sky-600 font-semibold bg-sky-50 px-2 py-0.5 rounded">Default OG Card</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="seo_default_og_image_file" class="block text-xs font-semibold text-slate-600 mb-1">Upload New Share Card</label>
                                        <input type="file" name="seo_default_og_image_file" id="seo_default_og_image_file" accept="image/jpeg,image/png,image/webp,image/avif" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                        <div class="mt-1 text-[10px] text-slate-400">Max 8MB. Recommended: 1200x630 (HD Landscape, 1.91:1 ratio).</div>
                                    </div>

                                    <div>
                                        <label for="seo_default_og_image" class="block text-xs font-semibold text-slate-600 mb-1">Or Specify Image Path / URL</label>
                                        <input type="text" name="seo_default_og_image" id="seo_default_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_default_og_image'] ?? '/images/desert-safari-poster.avif' }}" placeholder="/images/desert-safari-poster.avif">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                            <span><i class="bi bi-info-circle mr-1"></i>You can also customize individual per-page OpenGraph cards and search meta under <a href="{{ route('admin.settings.seo') }}" class="text-primary font-semibold hover:underline">SEO & Metadata Management</a>.</span>
                        </div>
                    </div>
                </div>

                <!-- Brand Identity & Licensing -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-building"></i> Brand Identity & Legal Licensing
                        </h6>
                        
                        <div class="mb-4">
                            <label for="site_name" class="block text-xs font-bold text-slate-700 mb-1.5">Brand Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="site_name" id="site_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }}" required>
                            <div class="mt-1 text-[11px] text-slate-500">Displayed in navbar, page headers, schema markup, and system notifications.</div>
                        </div>

                        <div class="mb-4">
                            <label for="company_license_number" class="block text-xs font-bold text-slate-700 mb-1.5">DET / DTCM License Number</label>
                            <input type="text" name="company_license_number" id="company_license_number" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['company_license_number'] ?? '1430583' }}" placeholder="e.g. 1430583">
                            <div class="mt-1 text-[11px] text-slate-500">Official Dubai Department of Economy and Tourism commercial license for E-E-A-T trust and schemas.</div>
                        </div>

                        <div class="mb-4">
                            <label for="site_copyright" class="block text-xs font-bold text-slate-700 mb-1.5">Footer Copyright Notice</label>
                            <input type="text" name="site_copyright" id="site_copyright" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_copyright'] ?? 'All rights reserved.' }}">
                        </div>

                        <div>
                            <label for="footer_about" class="block text-xs font-bold text-slate-700 mb-1.5">Footer About Bio</label>
                            <textarea name="footer_about" id="footer_about" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">{{ $settings['footer_about'] ?? 'Your trusted partner for unforgettable Dubai desert safari and adventure tour experiences since 2018.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact & Communication -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-telephone-inbound"></i> Direct Customer Contact
                        </h6>
                        
                        <div class="mb-4">
                            <label for="site_phone" class="block text-xs font-bold text-slate-700 mb-1.5">Primary Telephone Hotline <span class="text-rose-500">*</span></label>
                            <input type="text" name="site_phone" id="site_phone" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_phone'] ?? '+971 50 245 6056' }}" required>
                            <div class="mt-1 text-[11px] text-slate-500">Displayed on header, contact page, and schema. Format: +971 XX XXX XXXX</div>
                        </div>

                        <div class="mb-4">
                            <label for="site_whatsapp" class="block text-xs font-bold text-slate-700 mb-1.5">Official WhatsApp Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="site_whatsapp" id="site_whatsapp" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_whatsapp'] ?? '971502456056' }}" required>
                            <div class="mt-1 text-[11px] text-slate-500">Numeric digits with country code, no plus or spaces (e.g. 971502456056). Powers all instant WhatsApp chat buttons.</div>
                        </div>

                        <div class="mb-4">
                            <label for="site_email" class="block text-xs font-bold text-slate-700 mb-1.5">Primary Inquiries Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="site_email" id="site_email" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}" required>
                        </div>

                        <div>
                            <label for="site_support_email" class="block text-xs font-bold text-slate-700 mb-1.5">Customer Support Email</label>
                            <input type="email" name="site_support_email" id="site_support_email" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['site_support_email'] ?? 'support@dunesdiscoverytourism.com' }}">
                        </div>
                    </div>
                </div>

                <!-- Office Address & Google Maps Embed -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-geo-alt"></i> Physical Office Location & Google Maps
                        </h6>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="site_address" class="block text-xs font-bold text-slate-700 mb-1.5">Official Physical Address</label>
                                <textarea name="site_address" id="site_address" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">{{ $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates' }}</textarea>
                                <div class="mt-1 text-[11px] text-slate-500">Official registered office address for footer, contact page, and structured JSON-LD schemas.</div>
                            </div>
                            <div>
                                <label for="google_maps_embed_url" class="block text-xs font-bold text-slate-700 mb-1.5">Google Maps Embed Iframe URL</label>
                                <textarea name="google_maps_embed_url" id="google_maps_embed_url" rows="3" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">{{ $settings['google_maps_embed_url'] ?? '' }}</textarea>
                                <div class="mt-1 text-[11px] text-slate-500">Direct embed link for the interactive map shown on the Contact Us page.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Profiles & Reviews -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                        <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                            <i class="bi bi-share"></i> Social Channels & Verified Review Platforms
                        </h6>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="social_tripadvisor" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-award text-emerald-600 mr-1"></i>TripAdvisor Review / Profile URL</label>
                                <input type="url" name="social_tripadvisor" id="social_tripadvisor" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_tripadvisor'] ?? '' }}" placeholder="https://www.tripadvisor.com/...">
                            </div>
                            <div>
                                <label for="social_google" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-google text-blue-600 mr-1"></i>Google Maps Review / Place URL</label>
                                <input type="url" name="social_google" id="social_google" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_google'] ?? '' }}" placeholder="https://search.google.com/local/writereview?placeid=...">
                            </div>
                            <div>
                                <label for="social_instagram" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-instagram text-rose-600 mr-1"></i>Instagram Profile URL</label>
                                <input type="url" name="social_instagram" id="social_instagram" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                            </div>
                            <div>
                                <label for="social_facebook" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-facebook text-blue-700 mr-1"></i>Facebook Page URL</label>
                                <input type="url" name="social_facebook" id="social_facebook" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/...">
                            </div>
                            <div>
                                <label for="social_youtube" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-youtube text-red-600 mr-1"></i>YouTube Channel URL</label>
                                <input type="url" name="social_youtube" id="social_youtube" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/@...">
                            </div>
                            <div>
                                <label for="social_tiktok" class="block text-xs font-bold text-slate-700 mb-1.5"><i class="bi bi-tiktok text-slate-900 mr-1"></i>TikTok Profile URL</label>
                                <input type="url" name="social_tiktok" id="social_tiktok" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['social_tiktok'] ?? '' }}" placeholder="https://tiktok.com/@...">
                            </div>
                        </div>

                        <!-- TripAdvisor Content API Sync Settings -->
                        <div class="mt-4 pt-4 border-t border-slate-200/60">
                            <h6 class="text-[11px] font-black uppercase tracking-wider text-emerald-700 mb-2.5 flex items-center gap-1.5">
                                <i class="bi bi-award-fill"></i> TripAdvisor Content API & Auto-Sync
                            </h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tripadvisor_location_id" class="block text-xs font-bold text-slate-700 mb-1.5">TripAdvisor Location ID</label>
                                    <input type="text" name="tripadvisor_location_id" id="tripadvisor_location_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['tripadvisor_location_id'] ?? '29026644' }}" placeholder="29026644">
                                    <div class="mt-1 text-[11px] text-slate-500">Numerical Location ID for Dunes Discovery (e.g. 29026644 from d29026644).</div>
                                </div>
                                <div>
                                    <label for="tripadvisor_api_key" class="block text-xs font-bold text-slate-700 mb-1.5">TripAdvisor Content API Key</label>
                                    <input type="password" name="tripadvisor_api_key" id="tripadvisor_api_key" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['tripadvisor_api_key'] ?? '' }}" placeholder="Paste your TripAdvisor API Key here">
                                    <div class="mt-1 text-[11px] text-slate-500">TripAdvisor Content API v1 key from TripAdvisor Developer Portal.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Security, Admin Alert Routing & Ziina Payment Gateway -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-credit-card-2-front-fill"></i> Checkout Security, Admin Booking Alerts & Payment Gateway (Ziina UAE)
                            </h6>
                            <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-700">Checkout & Payments</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="admin_email" class="block text-xs font-bold text-slate-700 mb-1.5">Primary Admin Booking Alert Email</label>
                                <input type="email" name="admin_email" id="admin_email" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['admin_email'] ?? ($settings['site_email'] ?? 'info@dunesdiscoverytourism.com') }}" placeholder="info@dunesdiscoverytourism.com">
                                <div class="mt-1 text-[11px] text-slate-500">Receives instant new booking, inquiry, and WhatsApp lead notifications.</div>
                            </div>
                            <div>
                                <label for="admin_email_cc" class="block text-xs font-bold text-slate-700 mb-1.5">Admin Alert CC Email (Optional)</label>
                                <input type="email" name="admin_email_cc" id="admin_email_cc" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['admin_email_cc'] ?? '' }}" placeholder="operations@dunesdiscoverytourism.com">
                                <div class="mt-1 text-[11px] text-slate-500">Secondary recipient copied on all admin booking notifications.</div>
                            </div>
                            <div>
                                <label for="email_verification_required" class="block text-xs font-bold text-slate-700 mb-1.5">Checkout Email OTP Verification</label>
                                <input type="hidden" name="email_verification_required" value="0">
                                <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white cursor-pointer">
                                    <span class="text-xs font-bold text-slate-700">Require OTP Before Checkout</span>
                                    <input type="checkbox" name="email_verification_required" id="email_verification_required" value="1" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" {{ ($settings['email_verification_required'] ?? '0') === '1' ? 'checked' : '' }}>
                                </label>
                                <div class="mt-1 text-[11px] text-slate-500">When off, guests can complete bookings immediately without waiting for an email OTP code.</div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200/60">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ziina Online Payment Gateway</label>
                                    <input type="hidden" name="ziina_active" value="0">
                                    <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white cursor-pointer">
                                        <span class="text-xs font-bold text-slate-700">Enable Online Card / Apple Pay</span>
                                        <input type="checkbox" name="ziina_active" id="ziina_active" value="1" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" {{ ($settings['ziina_active'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ziina Sandbox / Test Mode</label>
                                    <input type="hidden" name="ziina_test_mode" value="0">
                                    <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-white cursor-pointer">
                                        <span class="text-xs font-bold text-slate-700">Use Test Mode (Sandbox)</span>
                                        <input type="checkbox" name="ziina_test_mode" id="ziina_test_mode" value="1" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" {{ ($settings['ziina_test_mode'] ?? '0') === '1' ? 'checked' : '' }}>
                                    </label>
                                </div>
                                <div>
                                    <label for="ziina_advance_percent" class="block text-xs font-bold text-slate-700 mb-1.5">Online Advance Deposit (%)</label>
                                    <input type="number" min="5" max="100" name="ziina_advance_percent" id="ziina_advance_percent" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-hidden transition focus:border-primary" value="{{ $settings['deposit_percentage'] ?? ($settings['ziina_advance_percent'] ?? '25') }}">
                                    <p class="text-[11px] text-slate-400 mt-1">Configurable online deposit percentage (default 25%). Customers can choose to pay this deposit, pay in full online, or pay cash on pickup.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="ziina_access_token" class="block text-xs font-bold text-slate-700 mb-1.5">Ziina API Access Token</label>
                                    <input type="password" name="ziina_access_token" id="ziina_access_token" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary" value="{{ $settings['ziina_access_token'] ?? '' }}" placeholder="Bearer token from Ziina Business Portal">
                                </div>
                                <div>
                                    <label for="ziina_webhook_secret" class="block text-xs font-bold text-slate-700 mb-1.5">Ziina Webhook Secret</label>
                                    <input type="password" name="ziina_webhook_secret" id="ziina_webhook_secret" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary" value="{{ $settings['ziina_webhook_secret'] ?? '' }}" placeholder="HMAC secret for verifying payment webhooks">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition cursor-pointer">
                    <i class="bi bi-check2-circle"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
