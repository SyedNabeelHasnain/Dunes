@php
    if (!isset($settings) || (!is_array($settings) && !($settings instanceof \ArrayAccess))) {
        try {
            $settings = app(\App\Services\SettingsService::class)->all();
        } catch (\Throwable $e) {
            $settings = [];
        }
    }
    $waFormEnabled = ((string) ($settings['whatsapp_form_enabled'] ?? '1')) === '1';
    $waGreeting = $settings['whatsapp_greeting'] ?? __('ui.whatsapp_modal.subtitle');
    $detLicenseNum = $settings['company_license_number'] ?? ($settings['site_det_license'] ?? '1430583');
@endphp

@if($waFormEnabled)
<!-- WhatsApp Concierge Inquiry Modal (Dunes Discovery Luxury Branding) -->
<div id="whatsappModal"
     x-data="{}"
     x-show="$store.modal.active === 'whatsapp'"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="whatsappModalLabel"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'whatsapp'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity z-0"
         @click="$store.modal.close()"></div>

    <!-- Modal Dialog Panel -->
    <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'whatsapp'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-3xl bg-white text-start align-middle shadow-2xl transition-all border border-slate-200/80 flex flex-col"
             @click.stop>

            <!-- Signature Dunes Luxury Amber-Gold Top Accent Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-orange-500 via-amber-400 to-yellow-500 shrink-0"></div>

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 pb-4 border-b border-slate-100 bg-white">
                <div class="flex items-center gap-3.5">
                    <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500/15 to-amber-500/10 text-primary border border-orange-500/25 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                        <i class="bi bi-whatsapp"></i>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white absolute -top-0.5 -end-0.5"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-50 text-primary border border-orange-200/70 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('ui.whatsapp_modal.online') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">DET #{{ $detLicenseNum }}</span>
                        </div>
                        <h5 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-display" id="whatsappModalLabel">
                            {{ __('ui.whatsapp_modal.title') }}
                        </h5>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            {{ $waGreeting }}
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="$store.modal.close()" 
                        class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer shrink-0" 
                        aria-label="{{ __('ui.common.close') }}">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Inquired Tour Context Indicator -->
            <div id="waTourInterestBanner" class="mx-5 sm:mx-6 mt-4 p-3.5 rounded-2xl bg-orange-50/60 border border-orange-200/80 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-500/15 text-primary flex items-center justify-center shrink-0">
                    <i class="bi bi-compass-fill text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] uppercase font-extrabold tracking-wider text-primary block">{{ __('ui.whatsapp_modal.inquiring_about') }}</span>
                    <span class="text-xs sm:text-sm font-extrabold text-slate-900 truncate block" id="waTourNameDisplay">Dubai Desert Safari Experience</span>
                </div>
            </div>

            <!-- WhatsApp Form -->
            <form id="whatsappForm" action="/ajax.php" method="POST" autocomplete="off" class="p-5 sm:p-6 space-y-4">
                @csrf
                <div style="display:none;">
                    <input type="text" name="website_url" value="" autocomplete="off" tabindex="-1">
                </div>

                <input type="hidden" name="action" value="logWhatsApp">
                <input type="hidden" name="tour_name" id="waTourName" value="">
                <input type="hidden" name="page_url" id="waPageUrl" value="">
                <input type="hidden" name="message_text" id="waMessageText" value="">
                <input type="hidden" name="final_url" id="waFinalUrl" value="">

                <input type="hidden" name="gps_lat" id="waGpsLat" value="">
                <input type="hidden" name="gps_lng" id="waGpsLng" value="">
                <input type="hidden" name="gps_accuracy" id="waGpsAccuracy" value="">
                <input type="hidden" name="gps_timestamp" id="waGpsTimestamp" value="">
                <input type="hidden" name="gps_consent" id="waGpsConsent" value="">
                <input type="hidden" name="gps_source" id="waGpsSource" value="">

                <!-- Full Name Field -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1.5" for="waName">
                        {{ __('ui.booking.name') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="waName" 
                               name="name" 
                               placeholder="{{ __('ui.booking.name_placeholder') }}" 
                               autocomplete="name" 
                               required 
                               data-form="whatsapp" 
                               data-field="name"
                               class="w-full px-4 py-3 rounded-xl bg-slate-50/70 focus:bg-white border border-slate-200 text-base sm:text-sm font-semibold text-slate-900 shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Phone Number Field -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1.5" for="waPhone">
                        {{ __('ui.booking.phone') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="welcome-phone-field rounded-xl bg-slate-50/70 focus-within:bg-white border border-slate-200 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <input type="tel" 
                               id="waPhone" 
                               name="phone" 
                               placeholder="{{ __('ui.booking.phone_placeholder') }}" 
                               autocomplete="tel" 
                               required 
                               data-form="whatsapp" 
                               data-field="phone"
                               class="w-full py-3 px-4 bg-transparent text-base sm:text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">{{ __('ui.whatsapp_modal.privacy_note') }}</p>
                </div>

                <!-- Terms & Privacy Agreement -->
                <div class="pt-0.5">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none py-1 min-h-[40px]">
                        <input type="checkbox" 
                               id="waAgreement" 
                               name="agreement" 
                               value="1" 
                               required 
                               checked 
                               class="rounded border-slate-300 text-primary focus:ring-primary/20 w-4 h-4 mt-0.5 shrink-0 cursor-pointer">
                        <span class="text-xs text-slate-600 leading-snug">
                            {!! __('ui.booking.agree_terms', [
                                'terms' => '<a href="' . localized_route('terms') . '" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">' . e(__('ui.nav.terms')) . '</a>',
                                'privacy' => '<a href="' . localized_route('privacy') . '" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">' . e(__('ui.nav.privacy')) . '</a>',
                            ]) !!}
                        </span>
                    </label>
                </div>

                <!-- Error Container -->
                <div id="waError" class="d-none hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold"></div>

                <!-- Action Button -->
                <div class="pt-1">
                    <button type="submit" 
                            id="startChatBtn" 
                            disabled 
                            class="w-full py-3.5 min-h-[48px] px-6 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-sm shadow-md shadow-orange-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:bg-none disabled:bg-slate-100 disabled:text-slate-400 disabled:border disabled:border-slate-200/90 disabled:shadow-none disabled:cursor-not-allowed">
                        <i class="bi bi-whatsapp text-base"></i>
                        <span>{{ __('ui.common.start_whatsapp_chat') }}</span>
                        <i class="bi bi-arrow-right rtl:rotate-180 text-xs"></i>
                    </button>
                </div>

                <!-- Trust Badges -->
                <div class="flex items-center justify-center gap-4 text-[11px] font-semibold text-slate-500 pt-1">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="bi bi-shield-check text-primary"></i> {{ __('ui.whatsapp_modal.no_spam') }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <i class="bi bi-clock-history text-primary"></i> {{ __('ui.whatsapp_modal.fast_response') }}
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
window.openWhatsApp = function(tourName, directHref) {
    if (window.App && typeof window.App.openWhatsApp === 'function') {
        window.App.openWhatsApp(tourName, directHref);
        return;
    }
    var formEnabled = (
        typeof window.WHATSAPP_FORM_ENABLED === 'undefined' ||
        window.WHATSAPP_FORM_ENABLED === '1' ||
        window.WHATSAPP_FORM_ENABLED === 1 ||
        window.WHATSAPP_FORM_ENABLED === true ||
        window.WHATSAPP_FORM_ENABLED === 'true'
    );
    var defaultNum = String(window.WHATSAPP_NUMBER || '971502456056').replace(/[^0-9]/g, '');
    var msg = tourName ? ('Hello, I would like to inquire about ' + tourName + '.') : 'Hello, I would like to inquire about Dubai desert safaris.';
    var targetUrl = directHref || ('https://wa.me/' + defaultNum + '?text=' + encodeURIComponent(msg));

    if (!formEnabled) {
        window.open(targetUrl, '_blank', 'noopener,noreferrer');
        return;
    }
    if (window.Alpine && Alpine.store('modal')) {
        Alpine.store('modal').open('whatsapp', { tourName: tourName, href: directHref });
    }
};
</script>
