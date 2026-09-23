@extends('layouts.admin')

@section('page_title', 'Marketing & Promotional Settings')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-megaphone"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">Marketing & Promotional Announcements</h5>
                <div class="text-xs text-slate-500 mt-0.5">Manage sitewide top announcement banners, welcome offer modals, promo codes, and countdown timers dynamically.</div>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <input type="hidden" name="promo_banner_form_submitted" value="1">
            <input type="hidden" name="promo_modal_form_submitted" value="1">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Announcement Promo Bar -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-layout-text-window-reverse"></i> Top Sticky Announcement Bar
                            </h6>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="promo_top_banner_enabled" id="promo_top_banner_enabled" value="1" {{ ($settings['promo_top_banner_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-2 text-xs font-bold text-slate-700">Active</span>
                            </label>
                        </div>

                        <div class="mb-4">
                            <label for="promo_top_banner_badge" class="block text-xs font-bold text-slate-700 mb-1.5">Announcement Badge</label>
                            <input type="text" name="promo_top_banner_badge" id="promo_top_banner_badge" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_top_banner_badge'] ?? 'Limited Time Offer' }}" placeholder="Limited Time Offer">
                            <div class="mt-1 text-[11px] text-slate-500">Highlighted badge text placed at the start of the announcement bar.</div>
                        </div>

                        <div class="mb-4">
                            <label for="promo_top_banner_text" class="block text-xs font-bold text-slate-700 mb-1.5">Announcement Message</label>
                            <textarea name="promo_top_banner_text" id="promo_top_banner_text" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Special Offer: Get 25% OFF on all Desert Safari Tours!">{{ $settings['promo_top_banner_text'] ?? '' }}</textarea>
                            <div class="mt-1 text-[11px] text-slate-500">Full announcement copy visible on both desktop and mobile headers.</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="promo_top_banner_code" class="block text-xs font-bold text-slate-700 mb-1.5">Promo Code</label>
                                <input type="text" name="promo_top_banner_code" id="promo_top_banner_code" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold uppercase text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_top_banner_code'] ?? 'DUNESWELCOME' }}">
                            </div>
                            <div>
                                <label for="promo_top_banner_discount" class="block text-xs font-bold text-slate-700 mb-1.5">Discount %</label>
                                <div class="relative flex rounded-xl shadow-2xs">
                                    <input type="number" min="1" max="100" name="promo_top_banner_discount" id="promo_top_banner_discount" class="w-full rounded-l-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_top_banner_discount'] ?? '25' }}">
                                    <span class="inline-flex items-center px-3.5 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-xs font-bold text-slate-600">% OFF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welcome Offer 25% Modal -->
                <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-gift"></i> Welcome Offer Modal (Popup)
                            </h6>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="promo_welcome_modal_enabled" id="promo_welcome_modal_enabled" value="1" {{ ($settings['promo_welcome_modal_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-2 text-xs font-bold text-slate-700">Active</span>
                            </label>
                        </div>

                        <div class="mb-4">
                            <label for="promo_welcome_modal_headline" class="block text-xs font-bold text-slate-700 mb-1.5">Modal Headline</label>
                            <input type="text" name="promo_welcome_modal_headline" id="promo_welcome_modal_headline" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_welcome_modal_headline'] ?? 'Unlock Exclusive 25% OFF' }}">
                        </div>

                        <div class="mb-4">
                            <label for="promo_welcome_modal_subheadline" class="block text-xs font-bold text-slate-700 mb-1.5">Subheadline / Description</label>
                            <textarea name="promo_welcome_modal_subheadline" id="promo_welcome_modal_subheadline" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">{{ $settings['promo_welcome_modal_subheadline'] ?? 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label for="promo_welcome_modal_discount" class="block text-xs font-bold text-slate-700 mb-1.5">Discount %</label>
                                <div class="relative flex rounded-xl shadow-2xs">
                                    <input type="number" min="1" max="100" name="promo_welcome_modal_discount" id="promo_welcome_modal_discount" class="w-full rounded-l-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_welcome_modal_discount'] ?? '25' }}">
                                    <span class="inline-flex items-center px-2.5 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-xs font-bold text-slate-600">%</span>
                                </div>
                            </div>
                            <div>
                                <label for="promo_welcome_modal_timer_minutes" class="block text-xs font-bold text-slate-700 mb-1.5">Timer Duration</label>
                                <div class="relative flex rounded-xl shadow-2xs">
                                    <input type="number" min="1" max="120" name="promo_welcome_modal_timer_minutes" id="promo_welcome_modal_timer_minutes" class="w-full rounded-l-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_welcome_modal_timer_minutes'] ?? '15' }}">
                                    <span class="inline-flex items-center px-2 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-xs text-slate-600">Min</span>
                                </div>
                            </div>
                            <div>
                                <label for="promo_welcome_modal_delay_seconds" class="block text-xs font-bold text-slate-700 mb-1.5">Popup Delay</label>
                                <div class="relative flex rounded-xl shadow-2xs">
                                    <input type="number" min="0" max="60" name="promo_welcome_modal_delay_seconds" id="promo_welcome_modal_delay_seconds" class="w-full rounded-l-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['promo_welcome_modal_delay_seconds'] ?? '5' }}">
                                    <span class="inline-flex items-center px-2 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-xs text-slate-600">Sec</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-check2-circle"></i> Save Promotional Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
