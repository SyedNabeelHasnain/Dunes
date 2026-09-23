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
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-check2-circle"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
