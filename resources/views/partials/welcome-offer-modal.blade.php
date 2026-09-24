@php
    $settingsService = app(\App\Services\SettingsService::class);
    $popupActive = ($settingsService->get('promo_welcome_modal_enabled', $settingsService->get('welcome_popup_active', '1'))) === '1';
    $popupDiscount = (float)$settingsService->get('promo_welcome_modal_discount', $settingsService->get('welcome_popup_discount', '25'));
    $popupTimerMins = (int)$settingsService->get('promo_welcome_modal_timer_minutes', $settingsService->get('welcome_popup_timer_mins', '15'));
    $popupDelaySec = (int)$settingsService->get('promo_welcome_modal_delay_seconds', $settingsService->get('welcome_popup_delay_sec', '5'));
    $popupScrollTrigger = $settingsService->get('welcome_popup_scroll_trigger', '1') === '1';
    $popupExitTrigger = $settingsService->get('welcome_popup_exit_trigger', '1') === '1';
    $popupHeadline = $settingsService->get('promo_welcome_modal_headline', $settingsService->get('welcome_popup_headline', 'Unlock Exclusive 25% OFF'));
    $popupSubheadline = $settingsService->get('promo_welcome_modal_subheadline', $settingsService->get('welcome_popup_subheadline', 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.'));
@endphp

@if($popupActive)
<!-- First-Time Visitor 25% Discount Voucher Modal (Tailwind v4 + Alpine.js) -->
<div id="welcomeOfferModal" 
     x-data="{}" 
     x-show="$store.modal.active === 'welcome-offer'" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     role="dialog" 
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'welcome-offer'"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         @click="$store.modal.close()"></div>

    <!-- Modal Dialog Panel -->
    <div class="min-h-full flex items-center justify-center p-3 sm:p-4 text-center">
        <div x-show="$store.modal.active === 'welcome-offer'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200 relative"
             @click.stop>
            
            <!-- Luxury Orange & Gold Ambient Glow Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-orange-500 via-amber-400 to-yellow-500"></div>

            <!-- Close Button -->
            <button type="button" 
                    @click="$store.modal.close()" 
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer absolute top-4 right-4 z-10" 
                    id="closeWelcomeOfferBtn" 
                    aria-label="Close">
                <i class="bi bi-x-lg text-xs"></i>
            </button>

            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-center">
                    
                    <!-- Left Visual & Highlights Column -->
                    <div class="lg:col-span-5 text-center lg:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-50 text-primary font-black text-[11px] uppercase tracking-wider mb-3">
                            <i class="bi bi-gift-fill text-amber-500"></i>
                            <span>First-Time Guest Special</span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight mb-2 tracking-tight">
                            {{ $popupHeadline }}
                        </h2>

                        <p class="text-slate-500 text-xs sm:text-sm leading-relaxed mb-4">
                            {{ $popupSubheadline }}
                        </p>

                        <!-- Trust Pillars -->
                        <div class="space-y-2 mb-6 text-left">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                <i class="bi bi-shield-check text-emerald-500 text-base"></i>
                                <span>100% Free 24h Cancellation</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                <i class="bi bi-patch-check-fill text-primary text-base"></i>
                                <span>DTCM Licensed Desert Marshals</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                <i class="bi bi-stars text-amber-500 text-base"></i>
                                <span>5-Star Halal Gourmet Dining</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                <i class="bi bi-cash-coin text-sky-500 text-base"></i>
                                <span>Zero-Deposit Cash on Pickup</span>
                            </div>
                        </div>

                        <!-- Session Countdown Box -->
                        <div class="p-3.5 rounded-2xl text-center bg-orange-50/70 border border-orange-200/80">
                            <span class="text-[10px] uppercase font-black text-slate-500 tracking-wider block mb-0.5">Session Offer Expires In</span>
                            <div class="text-2xl font-black text-primary font-mono tracking-widest" id="welcomeOfferCountdown">{{ sprintf('%02d', $popupTimerMins) }}:00</div>
                        </div>
                    </div>

                    <!-- Right Form / Success Column -->
                    <div class="lg:col-span-7">
                        <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs">
                            
                            <!-- STATE 1: Lead Capture Form -->
                            <div id="welcomeOfferFormState">
                                <div class="text-center mb-5">
                                    <h4 class="font-extrabold text-slate-900 text-lg mb-1">Claim Your Voucher</h4>
                                    <p class="text-slate-500 text-xs">Enter your details to receive your exclusive {{ (int)$popupDiscount }}% promo voucher instantly.</p>
                                </div>

                                <form id="welcomeOfferForm" class="space-y-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1" for="welcomeName">Full Name <span class="text-red-500">*</span></label>
                                        <div class="relative rounded-xl shadow-2xs overflow-hidden border border-slate-200 bg-white focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-primary"><i class="bi bi-person-fill"></i></span>
                                            <input type="text" class="w-full pl-10 pr-3 py-2.5 bg-transparent text-xs sm:text-sm font-semibold text-slate-800 border-0 focus:outline-none placeholder:text-slate-400" id="welcomeName" name="name" placeholder="e.g. Sarah Connor" required autocomplete="name">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1" for="welcomeEmail">Email Address <span class="text-red-500">*</span></label>
                                        <div class="relative rounded-xl shadow-2xs overflow-hidden border border-slate-200 bg-white focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-primary"><i class="bi bi-envelope-fill"></i></span>
                                            <input type="email" class="w-full pl-10 pr-3 py-2.5 bg-transparent text-xs sm:text-sm font-semibold text-slate-800 border-0 focus:outline-none placeholder:text-slate-400" id="welcomeEmail" name="email" placeholder="name@example.com" required autocomplete="email">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1" for="welcomePhone"><i class="bi bi-whatsapp text-emerald-500 me-1"></i>Phone / WhatsApp Number <span class="text-red-500">*</span></label>
                                        <div class="welcome-phone-field rounded-xl shadow-2xs bg-white border border-slate-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all relative">
                                            <input type="tel" class="w-full py-2.5 px-3 bg-transparent text-xs sm:text-sm font-semibold text-slate-800 border-0 focus:outline-none placeholder:text-slate-400" id="welcomePhone" name="phone" placeholder="50 123 4567" required autocomplete="tel">
                                        </div>
                                        <small class="text-slate-500 block mt-1 text-[11px]"><i class="bi bi-shield-check text-emerald-500 me-1"></i>Voucher sent via Email & WhatsApp.</small>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-red-50 text-red-700 text-xs border border-red-200 hidden" id="welcomeOfferError"></div>

                                    <button type="submit" class="w-full py-3.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2" id="claimOfferSubmitBtn">
                                        <span>Claim My {{ (int)$popupDiscount }}% Discount</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </button>

                                    <div class="text-center text-slate-500 text-[11px]">
                                        <i class="bi bi-lock-fill me-1"></i> 100% Privacy. Single-use voucher valid for 24h.
                                    </div>
                                </form>
                            </div>

                            <!-- STATE 2: Success & Instant 1-Click Booking -->
                            <div id="welcomeOfferSuccessState" class="hidden text-center py-2">
                                <div class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/20 text-2xl font-bold">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <h3 class="font-black text-slate-900 text-xl mb-1">{{ (int)$popupDiscount }}% Discount Unlocked!</h3>
                                <p class="text-slate-500 text-xs mb-4">Your personalized promo voucher is generated and ready to apply.</p>

                                <!-- Golden Ticket Display -->
                                <div class="p-4 rounded-2xl text-white mb-4 relative shadow-lg bg-gradient-to-br from-slate-900 to-slate-950 border-2 border-dashed border-orange-500">
                                    <span class="text-[10px] uppercase font-bold text-amber-400 tracking-widest block mb-1">Your Exclusive Promo Code</span>
                                    <div class="text-2xl sm:text-3xl font-black font-mono text-white my-2 tracking-widest" id="generatedVoucherCode">FIRST25-XXXXX</div>
                                    <button type="button" class="px-3 py-1 rounded-full bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold transition-colors cursor-pointer inline-flex items-center gap-1.5" id="copyVoucherBtn">
                                        <i class="bi bi-clipboard"></i> <span>Copy Code</span>
                                    </button>
                                </div>

                                <button type="button" class="w-full py-3.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2 mb-2" id="applyVoucherAndBookBtn">
                                    <i class="bi bi-cart-check-fill"></i>
                                    <span>Apply {{ (int)$popupDiscount }}% OFF & Book Safari Now</span>
                                </button>
                                
                                <small class="text-slate-500 block mt-2 text-[11px]">
                                    A copy has also been sent to your email address.
                                </small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Floating Persistent Voucher Reminder Pill (Appears when claimed) -->
<div id="welcomeFloatingPill" 
     class="hidden fixed bottom-6 left-6 z-40 mb-safe items-center gap-2 p-2 px-3.5 rounded-full bg-slate-950/95 text-white border border-orange-500/50 shadow-2xl backdrop-blur-md cursor-pointer hover:scale-105 transition-all">
    <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-xs">{{ (int)$popupDiscount }}% OFF</span>
    <span class="text-xs font-bold font-mono text-white" id="floatingPillCode">FIRST25-OFF</span>
    <span class="text-xs text-slate-300 font-mono hidden sm:inline" id="floatingPillTimer">{{ sprintf('%02d', $popupTimerMins) }}:00</span>
    <button type="button" class="px-2.5 py-1 rounded-full bg-primary hover:bg-primary-dark text-white font-bold text-xs transition-colors ms-1 cursor-pointer">
        Apply
    </button>
</div>

<script>
(function() {
    const popupDelaySec = {{ (int)$popupDelaySec }};
    const popupTimerMins = {{ (int)$popupTimerMins }};
    const enableScroll = {{ $popupScrollTrigger ? 'true' : 'false' }};
    const enableExit = {{ $popupExitTrigger ? 'true' : 'false' }};

    function initWelcomeOffer() {
        const modalEl = document.getElementById('welcomeOfferModal');
        const form = document.getElementById('welcomeOfferForm');
        const formState = document.getElementById('welcomeOfferFormState');
        const successState = document.getElementById('welcomeOfferSuccessState');
        const errorBox = document.getElementById('welcomeOfferError');
        const submitBtn = document.getElementById('claimOfferSubmitBtn');
        const countdownEl = document.getElementById('welcomeOfferCountdown');
        const codeDisplay = document.getElementById('generatedVoucherCode');
        const copyBtn = document.getElementById('copyVoucherBtn');
        const applyBookBtn = document.getElementById('applyVoucherAndBookBtn');
        const floatingPill = document.getElementById('welcomeFloatingPill');
        const floatingCode = document.getElementById('floatingPillCode');
        const floatingTimer = document.getElementById('floatingPillTimer');

        function showOfferModal() {
            if (window.Alpine && Alpine.store('modal')) {
                Alpine.store('modal').open('welcome-offer');
                if (window.App && typeof window.App.initPhoneInputs === 'function') {
                    setTimeout(() => window.App.initPhoneInputs(), 100);
                }
            }
        }

        function hideOfferModal() {
            if (window.Alpine && Alpine.store('modal')) {
                Alpine.store('modal').close();
            }
        }

        // Urgency Timer
        let durationSeconds = popupTimerMins * 60;
        let timerInterval = null;

        function startTimer() {
            const savedEndTime = sessionStorage.getItem('dunes_welcome_offer_end');
            let endTime = savedEndTime ? parseInt(savedEndTime, 10) : (Date.now() + (durationSeconds * 1000));
            sessionStorage.setItem('dunes_welcome_offer_end', endTime);

            if (timerInterval) clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                const now = Date.now();
                const diff = Math.max(0, Math.floor((endTime - now) / 1000));
                const mins = String(Math.floor(diff / 60)).padStart(2, '0');
                const secs = String(diff % 60).padStart(2, '0');
                const timeStr = `${mins}:${secs}`;

                if (countdownEl) countdownEl.innerText = timeStr;
                if (floatingTimer) floatingTimer.innerText = timeStr;

                if (diff <= 0) {
                    clearInterval(timerInterval);
                    if (countdownEl) countdownEl.innerText = '00:00';
                }
            }, 1000);
        }

        function shouldShowWelcomePopup() {
            const claimed = localStorage.getItem('dunes_welcome_claimed');
            if (claimed) {
                showFloatingPill(claimed);
                return false;
            }

            const dismissedUntil = localStorage.getItem('dunes_welcome_dismissed_until');
            if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
                return false;
            }

            return true;
        }

        function triggerModal() {
            if (!modalEl || !shouldShowWelcomePopup()) return;
            if (sessionStorage.getItem('dunes_welcome_shown_session')) return;

            sessionStorage.setItem('dunes_welcome_shown_session', 'true');
            startTimer();
            showOfferModal();
        }

        if (modalEl) {
            setTimeout(() => {
                triggerModal();
            }, popupDelaySec * 1000);

            if (enableScroll) {
                let scrollTriggered = false;
                window.addEventListener('scroll', () => {
                    if (scrollTriggered) return;
                    const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                    if (scrollPercent >= 35) {
                        scrollTriggered = true;
                        triggerModal();
                    }
                }, { passive: true });
            }

            if (enableExit) {
                let exitTriggered = false;
                document.addEventListener('mouseleave', (e) => {
                    if (exitTriggered || e.clientY > 20) return;
                    exitTriggered = true;
                    triggerModal();
                });
            }
        }

        // Form Submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const name = document.getElementById('welcomeName')?.value.trim();
                const email = document.getElementById('welcomeEmail')?.value.trim();
                const phoneInput = document.getElementById('welcomePhone');
                const phone = (phoneInput && phoneInput._iti && typeof phoneInput._iti.getNumber === 'function' && phoneInput._iti.getNumber())
                    ? phoneInput._iti.getNumber()
                    : phoneInput?.value.trim();

                if (!name || !email || !phone) {
                    if (errorBox) {
                        errorBox.innerText = 'Please provide your full name, email, and phone/WhatsApp number.';
                        errorBox.classList.remove('hidden');
                    }
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin me-2"></span> Generating Voucher...';
                if (errorBox) errorBox.classList.add('hidden');

                fetch('/api/v1/welcome-offer/claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ name: name, email: email, phone: phone })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Claim My Discount</span> <i class="bi bi-arrow-right"></i>';

                    if (status === 200 && body.success && body.coupon) {
                        const code = body.coupon.code;
                        localStorage.setItem('dunes_welcome_claimed', code);
                        localStorage.removeItem('dunes_welcome_dismissed_until');

                        if (typeof gtag === 'function') {
                            gtag('event', 'conversion_event_submit_lead_form', {
                                'event_category': 'Welcome Offer',
                                'event_label': code
                            });
                        }

                        if (codeDisplay) codeDisplay.innerText = code;
                        formState.classList.add('hidden');
                        successState.classList.remove('hidden');

                        showFloatingPill(code);
                    } else {
                        if (errorBox) {
                            errorBox.innerText = body.message || 'Unable to generate voucher. Please check your details and try again.';
                            errorBox.classList.remove('hidden');
                        }
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Claim My Discount</span> <i class="bi bi-arrow-right"></i>';
                    if (errorBox) {
                        errorBox.innerText = 'Network error. Please try again.';
                        errorBox.classList.remove('hidden');
                    }
                });
            });
        }

        // Top Banner Claim / Copy & Book Button
        document.querySelectorAll('.top-banner-copy-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const code = this.dataset.code || 'FIRST25';
                navigator.clipboard.writeText(code).then(() => {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<span class="text-emerald-400 font-bold">COPIED!</span> <i class="bi bi-check-lg text-emerald-400"></i>';
                    setTimeout(() => { btn.innerHTML = orig; }, 2000);

                    if (window.Alpine && Alpine.store('modal')) {
                        Alpine.store('modal').open('booking', { promo: code });
                    }
                }).catch(() => {
                    showOfferModal();
                });
            });
        });

        // Copy Code
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const code = codeDisplay.innerText.trim();
                navigator.clipboard.writeText(code).then(() => {
                    copyBtn.innerHTML = '<i class="bi bi-check-lg text-emerald-500 me-1"></i> Copied!';
                    setTimeout(() => { copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Code'; }, 2000);
                });
            });
        }

        // Apply & Book
        if (applyBookBtn) {
            applyBookBtn.addEventListener('click', function() {
                const code = codeDisplay.innerText.trim();
                navigator.clipboard.writeText(code);
                hideOfferModal();

                setTimeout(() => {
                    if (window.Alpine && Alpine.store('modal')) {
                        Alpine.store('modal').open('booking', { promo: code });
                        const promoInput = document.getElementById('bookingPromoCode');
                        if (promoInput) promoInput.value = code;
                        if (typeof window.validateCurrentPromo === 'function') {
                            setTimeout(() => window.validateCurrentPromo(), 400);
                        }
                    }
                }, 300);
            });
        }

        function showFloatingPill(code) {
            if (!floatingPill) return;
            if (floatingCode) floatingCode.innerText = code;
            floatingPill.classList.remove('hidden');
            floatingPill.classList.add('flex');
            startTimer();

            floatingPill.onclick = function() {
                if (window.Alpine && Alpine.store('modal')) {
                    Alpine.store('modal').open('booking', { promo: code });
                    const promoInput = document.getElementById('bookingPromoCode');
                    if (promoInput) promoInput.value = code;
                    if (typeof window.validateCurrentPromo === 'function') {
                        setTimeout(() => window.validateCurrentPromo(), 400);
                    }
                }
            };
        }

        const savedClaimedCode = localStorage.getItem('dunes_welcome_claimed');
        if (savedClaimedCode) {
            showFloatingPill(savedClaimedCode);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWelcomeOffer);
    } else {
        initWelcomeOffer();
    }
})();
</script>
