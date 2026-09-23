@extends('layouts.admin')

@section('page_title', 'Campaign Triggers & Promotions')

@section('content')
<div class="space-y-6">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.coupons.index') }}" class="hover:text-primary transition">Coupons & Promos</a>
                <span>/</span>
                <span class="text-slate-700 font-bold">Campaign Triggers & Promotions</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Promotions & Campaign Triggers Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Centralized management for first-time visitor welcome offers (25%), top announcement banner, and Safari Match Concierge promo.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-ticket-perforated"></i> View All Promo Codes
            </a>
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-box-arrow-up-right"></i> Live Website
            </a>
        </div>
    </div>

    <!-- Unified Section Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition">
            <i class="bi bi-ticket-perforated text-primary"></i> All Promo Codes
        </a>
        <a href="{{ route('admin.coupons.popup-settings') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-primary text-white shadow-xs">
            <i class="bi bi-megaphone-fill text-amber-300"></i> Campaign Triggers & Banners (25% & Concierge)
        </a>
    </div>

    <form action="{{ route('admin.coupons.popup-settings.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Settings Configuration -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Card 1: First-Time Visitor 25% Offer Modal -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                <i class="bi bi-gift-fill text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900">First-Time Visitor Offer Modal (25% OFF)</h2>
                                <p class="text-xs text-slate-400">High-converting automated lead capture popup</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" id="welcome_popup_active" name="welcome_popup_active" value="1" {{ ($settings->get('welcome_popup_active', '1') == '1') ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-500">Active</span>
                        </label>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="welcome_popup_discount">Discount Rate (%)</label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="100" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-14 text-sm font-black text-primary focus:border-primary outline-hidden" id="welcome_popup_discount" name="welcome_popup_discount" value="{{ $settings->get('welcome_popup_discount', '25') }}" required>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">% OFF</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="welcome_popup_timer_mins">Urgency Timer</label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="120" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-14 text-sm font-bold text-slate-800 focus:border-primary outline-hidden" id="welcome_popup_timer_mins" name="welcome_popup_timer_mins" value="{{ $settings->get('welcome_popup_timer_mins', '15') }}" required>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">Mins</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="welcome_popup_delay_sec">Trigger Delay</label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="60" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-14 text-sm font-bold text-slate-800 focus:border-primary outline-hidden" id="welcome_popup_delay_sec" name="welcome_popup_delay_sec" value="{{ $settings->get('welcome_popup_delay_sec', '5') }}" required>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">Secs</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Smart Triggers</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer">
                                    <input class="rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" id="welcome_popup_exit_trigger" name="welcome_popup_exit_trigger" value="1" {{ ($settings->get('welcome_popup_exit_trigger', '1') == '1') ? 'checked' : '' }}>
                                    <span class="text-xs font-bold text-slate-800">Desktop Exit-Intent (When cursor moves to close tab)</span>
                                </label>
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer">
                                    <input class="rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" id="welcome_popup_scroll_trigger" name="welcome_popup_scroll_trigger" value="1" {{ ($settings->get('welcome_popup_scroll_trigger', '1') == '1') ? 'checked' : '' }}>
                                    <span class="text-xs font-bold text-slate-800">Scroll Depth (When visitor scrolls 35% of page)</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="welcome_popup_headline">Modal Headline</label>
                            <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-bold text-slate-900 focus:border-primary outline-hidden" id="welcome_popup_headline" name="welcome_popup_headline" value="{{ $settings->get('welcome_popup_headline', 'Unlock Exclusive 25% OFF') }}">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="welcome_popup_subheadline">Modal Subheadline</label>
                            <textarea class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" rows="2" id="welcome_popup_subheadline" name="welcome_popup_subheadline">{{ $settings->get('welcome_popup_subheadline', 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Top Announcement Promo Banner Bar -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="bi bi-megaphone-fill text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900">Top Announcement Promo Banner (25% OFF)</h2>
                                <p class="text-xs text-slate-400">Sticky top notification bar with instant copyable coupon code</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" id="top_promo_banner_active" name="top_promo_banner_active" value="1" {{ ($settings->get('top_promo_banner_active', '1') == '1') ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-500">Active</span>
                        </label>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="top_promo_banner_text">Banner Announcement Text</label>
                            <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:border-primary outline-hidden" id="top_promo_banner_text" name="top_promo_banner_text" value="{{ $settings->get('top_promo_banner_text', 'Special Online Exclusive: Get 25% OFF on all Desert Safari Tours! • 100% Free 24h Cancellation') }}">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="top_promo_banner_badge">Badge Pill Text</label>
                                <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-800 focus:border-primary outline-hidden" id="top_promo_banner_badge" name="top_promo_banner_badge" value="{{ $settings->get('top_promo_banner_badge', 'Limited Time Offer') }}">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="top_promo_banner_code">Featured Promo Code to Display</label>
                                <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-mono font-black uppercase text-primary focus:border-primary outline-hidden" id="top_promo_banner_code" name="top_promo_banner_code" value="{{ $settings->get('top_promo_banner_code', 'DUNESWELCOME') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Safari Match Concierge Promo Controls -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                <i class="bi bi-compass-fill text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900">Safari Match Concierge Promo Controls</h2>
                                <p class="text-xs text-slate-400">Interactive 3-step quiz recommendation reward code (MATCH5)</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" id="concierge_promo_active" name="concierge_promo_active" value="1" {{ ($settings->get('concierge_promo_active', '0') == '1') ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-500">Active</span>
                        </label>
                    </div>

                    <div class="p-6 space-y-4">
                        @if($settings->get('concierge_promo_active', '0') == '1')
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-600 mt-0.5"></i>
                            <div><strong>Concierge Promo is Live:</strong> The 5% reward badge is shown across navbars, quiz completion screens, and automatically preloads code MATCH5 into the booking checkout.</div>
                        </div>
                        @else
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                            <i class="bi bi-info-circle-fill text-amber-600 mt-0.5"></i>
                            <div><strong>Concierge Promo is Currently Deactivated:</strong> The Safari Match Concierge operates in <em>Pure Curation Mode</em>. Guests receive custom tour recommendations without discount badges or checkout promo auto-injection.</div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="concierge_promo_discount">Concierge Discount Rate (%)</label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="100" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-14 text-sm font-black text-primary focus:border-primary outline-hidden" id="concierge_promo_discount" name="concierge_promo_discount" value="{{ $settings->get('concierge_promo_discount', '5') }}" required>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">% OFF</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="concierge_promo_code">Concierge Promo Code</label>
                                <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono font-black uppercase text-slate-900 focus:border-primary outline-hidden" id="concierge_promo_code" name="concierge_promo_code" value="{{ $settings->get('concierge_promo_code', 'MATCH5') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Exit-Intent Cart Saver Modal Controls (SAVE5) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                                <i class="bi bi-door-closed-fill text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900">Exit-Intent Cart Saver Modal (5% OFF)</h2>
                                <p class="text-xs text-slate-400">Desktop exit-intent pop-up trigger with promo code SAVE5</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" id="exit_intent_promo_active" name="exit_intent_promo_active" value="1" {{ ($settings->get('exit_intent_promo_active', '0') == '1') ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-500">Active</span>
                        </label>
                    </div>

                    <div class="p-6 space-y-4">
                        @if($settings->get('exit_intent_promo_active', '0') == '1')
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-600 mt-0.5"></i>
                            <div><strong>Exit-Intent Cart Saver is Live:</strong> The 5% exit-intent popup is active and will fire when visitors move cursor toward browser tab/close area.</div>
                        </div>
                        @else
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                            <i class="bi bi-shield-lock-fill text-amber-600 mt-0.5"></i>
                            <div><strong>Exit-Intent Cart Saver is Currently Deactivated:</strong> Only the 25% Welcome Offer modal is active for first-time visitors. No competing 5% popup will display on exit.</div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="exit_intent_promo_discount">Exit-Intent Discount Rate (%)</label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" max="100" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-14 text-sm font-black text-primary focus:border-primary outline-hidden" id="exit_intent_promo_discount" name="exit_intent_promo_discount" value="{{ $settings->get('exit_intent_promo_discount', '5') }}" required>
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">% OFF</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" for="exit_intent_promo_code">Exit-Intent Promo Code</label>
                                <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono font-black uppercase text-slate-900 focus:border-primary outline-hidden" id="exit_intent_promo_code" name="exit_intent_promo_code" value="{{ $settings->get('exit_intent_promo_code', 'SAVE5') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all">
                        <i class="bi bi-save"></i> Save Promotion Settings
                    </button>
                </div>
            </div>

            <!-- Right Column: Live Visual Preview -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6 sticky top-6">
                    <div class="pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i class="bi bi-eye-fill text-sky-600"></i>
                        <h2 class="text-sm font-black text-slate-900">Live Visual Preview</h2>
                    </div>

                    <!-- Top Banner Preview -->
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Top Banner (25% OFF):</span>
                        <div class="p-2.5 rounded-xl text-white text-center text-xs font-bold flex items-center justify-center gap-2 shadow-xs bg-slate-950 border-b-2 border-primary">
                            <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-mono">{{ $settings->get('top_promo_banner_badge', 'Limited Time Offer') }}</span>
                            <span>Get 25% OFF</span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-400 text-slate-950 font-mono text-[10px]">{{ $settings->get('top_promo_banner_code', 'DUNESWELCOME') }}</span>
                        </div>
                    </div>

                    <!-- Modal Card Preview -->
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Welcome Modal (25% OFF):</span>
                        <div class="border border-primary/30 rounded-2xl p-5 shadow-xs bg-white relative overflow-hidden space-y-3">
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-amber-400"></div>
                            
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase">
                                <i class="bi bi-gift-fill"></i> First-Time Guest Special
                            </span>

                            <h3 class="text-base font-black text-slate-900 leading-snug">{{ $settings->get('welcome_popup_headline', 'Unlock Exclusive 25% OFF') }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $settings->get('welcome_popup_subheadline', 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.') }}</p>

                            <div class="p-2.5 rounded-xl bg-slate-50 text-center border border-slate-100">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Session Offer Expires In</span>
                                <span class="text-xl font-black text-primary font-mono">14:59</span>
                            </div>

                            <div class="space-y-2">
                                <input type="email" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-center font-bold text-slate-400 bg-slate-50" placeholder="name@example.com" disabled>
                                <button type="button" class="w-full py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-xs" disabled>
                                    Claim My 25% Discount &rarr;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Concierge Mode Preview -->
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Safari Match Concierge Mode:</span>
                        @if($settings->get('concierge_promo_active', '0') == '1')
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-slate-800 space-y-1">
                            <div class="flex items-center gap-2 font-bold text-xs text-emerald-700">
                                <i class="bi bi-check-circle-fill"></i> Promo Mode Active
                            </div>
                            <p class="text-xs text-slate-600">Navbars and quiz completion screens advertise and auto-inject <strong>{{ $settings->get('concierge_promo_discount', '5') }}% OFF ({{ $settings->get('concierge_promo_code', 'MATCH5') }})</strong>.</p>
                        </div>
                        @else
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 space-y-1">
                            <div class="flex items-center gap-2 font-bold text-xs text-slate-600">
                                <i class="bi bi-pause-circle-fill text-amber-500"></i> Pure Curation Mode (Promo Inactive)
                            </div>
                            <p class="text-xs text-slate-500">The Concierge recommends tailored safaris without discount certificates or checkout code auto-injection.</p>
                        </div>
                        @endif
                    </div>

                    <!-- Conversion Tips -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="bi bi-lightbulb-fill text-amber-500"></i> Conversion Tips
                        </div>
                        <ul class="list-disc pl-4 text-[11px] text-slate-500 space-y-1">
                            <li>The 25% First-Time Visitor offer provides maximum incentive for new travelers.</li>
                            <li>The Safari Match Concierge provides 1-on-1 advisor curation without diluting margins when promo is off.</li>
                            <li>All coupon redemptions and leads are tracked in real-time in the admin analytics.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
