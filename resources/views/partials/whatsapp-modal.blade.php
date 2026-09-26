<!-- WhatsApp Lead Generation Modal (Tailwind v4 + Alpine.js) -->
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
             class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200/80 flex flex-col"
             @click.stop>

            <!-- Luxury Emerald Gradient Top Accent Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600"></div>

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 pb-4 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-700 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online Now
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">DET #1430583</span>
                        </div>
                        <h5 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" id="whatsappModalLabel">
                            Connect on WhatsApp
                        </h5>
                    </div>
                </div>
                <button type="button" 
                        @click="$store.modal.close()" 
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" 
                        aria-label="Close modal">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Inquired Tour Context Indicator -->
            <div id="waTourInterestBanner" class="mx-5 sm:mx-6 mt-4 p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100/80 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="bi bi-compass text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] uppercase font-extrabold tracking-wider text-emerald-700 block">Inquiry Interest</span>
                    <span class="text-xs font-extrabold text-slate-800 truncate block" id="waTourNameDisplay">Dubai Desert Safari Experience</span>
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
                        Your Full Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="waName" 
                               name="name" 
                               placeholder="e.g. John Smith" 
                               autocomplete="name" 
                               required 
                               data-form="whatsapp" 
                               data-field="name"
                               class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 shadow-2xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Phone Number Field -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-wider mb-1.5" for="waPhone">
                        WhatsApp Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <div class="welcome-phone-field rounded-2xl bg-white border border-slate-200 shadow-2xs focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                        <input type="tel" 
                               id="waPhone" 
                               name="phone" 
                               placeholder="50 123 4567" 
                               autocomplete="tel" 
                               required 
                               data-form="whatsapp" 
                               data-field="phone"
                               class="w-full py-3 px-4 bg-transparent text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Our certified safari captains will reply directly on this number.</p>
                </div>

                <!-- Terms & Privacy Agreement -->
                <div class="pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" 
                               id="waAgreement" 
                               name="agreement" 
                               value="1" 
                               required 
                               checked 
                               class="rounded-md border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 mt-0.5 shrink-0 cursor-pointer">
                        <span class="text-[11px] text-slate-600 leading-snug">
                            I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-emerald-600 font-bold hover:underline">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-600 font-bold hover:underline">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <!-- Error Container -->
                <div id="waError" class="d-none p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold"></div>

                <!-- Action Button -->
                <div class="pt-2">
                    <button type="submit" 
                            id="startChatBtn" 
                            disabled 
                            class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-extrabold text-sm shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-whatsapp text-lg"></i>
                        <span>Start WhatsApp Chat</span>
                        <i class="bi bi-arrow-right text-xs"></i>
                    </button>
                </div>

                <!-- Trust Badges -->
                <div class="flex items-center justify-center gap-4 text-[11px] font-semibold text-slate-400 pt-1">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="bi bi-shield-check text-emerald-500"></i> No Spam Guarantee
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <i class="bi bi-clock-history text-emerald-500"></i> &lt; 2 Min Response
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
