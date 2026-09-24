@php
    $settingsService = app(\App\Services\SettingsService::class);
    $exitIntentPromoActive = ($settingsService->get('exit_intent_promo_active', '0')) === '1';
    $exitIntentDiscount = (int)$settingsService->get('exit_intent_promo_discount', '5');
    $exitIntentCode = $settingsService->get('exit_intent_promo_code', 'SAVE5');
@endphp

@if($exitIntentPromoActive)
<!-- Smart Exit-Intent Cart Saver Modal (Tailwind v4 + Alpine.js) -->
<div id="exitIntentModal"
     x-data="{}"
     x-show="$store.modal.active === 'exit-intent'"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="$store.modal.close()">

    <!-- Backdrop -->
    <div x-show="$store.modal.active === 'exit-intent'"
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
        <div x-show="$store.modal.active === 'exit-intent'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-3xl bg-slate-950 p-6 sm:p-8 text-center align-middle shadow-2xl transition-all border border-orange-500/40 text-white"
             @click.stop>
            
            <!-- Header Badge & Close Button -->
            <div class="flex items-center justify-between mb-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                    <i class="bi bi-gift-fill text-amber-400"></i> Exclusive Departure Offer
                </span>
                <button type="button" 
                        class="w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        aria-label="Close" 
                        id="exitIntentCloseBtn">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <!-- Compass Icon -->
            <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="bi bi-compass"></i>
            </div>

            <!-- Title & Subtitle -->
            <h3 class="text-xl sm:text-2xl font-black text-white mb-2 leading-snug" id="exitIntentModalLabel">
                Wait! Don't Leave Dubai Without Experiencing The Dunes
            </h3>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed mb-6 max-w-md mx-auto">
                Before you go, take an instant <strong class="text-amber-400">{{ $exitIntentDiscount }}% OFF</strong> on all certified Dubai desert safari packages with 4x4 hotel pickup and 5-star live BBQ dinner.
            </p>

            <!-- Gamified Coupon Certificate Card -->
            <div class="rounded-2xl p-4 mb-6 bg-gradient-to-r from-orange-500/15 to-amber-500/10 border-2 border-dashed border-orange-500/40 flex items-center justify-between flex-wrap gap-2 text-left">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-300 block tracking-wider">Instant Promo Code</span>
                    <span class="font-mono font-black text-2xl text-amber-400 tracking-wider" id="exitIntentCodeDisplay">{{ $exitIntentCode }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold">
                        {{ $exitIntentDiscount }}% Instant Savings
                    </span>
                    <button type="button" 
                            class="px-3 py-1.5 rounded-full border border-amber-400/50 hover:bg-amber-400 hover:text-slate-950 text-amber-400 text-xs font-bold transition-all cursor-pointer flex items-center gap-1" 
                            id="exitIntentCopyBtn" 
                            title="Copy Code">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- Trust Guarantees -->
            <div class="grid grid-cols-2 gap-2.5 text-left mb-6 text-xs text-slate-300">
                <div class="flex items-center gap-2">
                    <i class="bi bi-patch-check-fill text-primary"></i>
                    <span>DET Licensed #1430583</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="bi bi-arrow-repeat text-emerald-400"></i>
                    <span>100% Free Cancel 24h</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="bi bi-award-fill text-amber-400"></i>
                    <span>5-Star Halal BBQ Buffet</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-sky-400"></i>
                    <span>Pay Online or on Pickup</span>
                </div>
            </div>

            <!-- CTAs -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                <button type="button" 
                        class="sm:col-span-7 w-full py-3.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2" 
                        id="exitIntentClaimBtn">
                    <i class="bi bi-tag-fill"></i>
                    <span>Claim {{ $exitIntentDiscount }}% & Book Now</span>
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',(string)($settings['site_whatsapp'] ?? '971502456056')) }}?text={{ urlencode('Hi Dunes Discovery, I am looking to book a desert safari with the ' . $exitIntentDiscount . '% discount code ' . $exitIntentCode . '. Could you recommend the best package for my group?') }}" 
                   class="sm:col-span-5 w-full py-3.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2" 
                   target="_blank" 
                   rel="noopener noreferrer">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            </div>

            <div class="mt-4">
                <button type="button" 
                        class="text-xs text-slate-400 hover:text-slate-200 transition-colors cursor-pointer" 
                        @click="$store.modal.close()" 
                        id="exitIntentDismissLink">
                    No thanks, I'll pay full price later
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let exitIntentFired = false;
    const STORAGE_KEY = 'dunes_exit_intent_dismissed_v1';

    if (sessionStorage.getItem(STORAGE_KEY)) {
        exitIntentFired = true;
    }

    function triggerExitIntent() {
        if (exitIntentFired) return;

        if (window.Alpine && Alpine.store('modal') && Alpine.store('modal').active) {
            return;
        }
        if (sessionStorage.getItem('dunes_welcome_shown_session')) {
            return;
        }

        exitIntentFired = true;
        sessionStorage.setItem(STORAGE_KEY, '1');

        if (window.Alpine && Alpine.store('modal')) {
            Alpine.store('modal').open('exit-intent');
        }
    }

    document.addEventListener('mouseleave', function(e) {
        if (e.clientY <= 10) {
            triggerExitIntent();
        }
    });

    let inactivityTimer = setTimeout(function() {
        if (window.innerWidth <= 768 && !exitIntentFired) {
            triggerExitIntent();
        }
    }, 45000);

    const copyBtn = document.getElementById('exitIntentCopyBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText('{{ $exitIntentCode }}').then(() => {
                const orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="bi bi-check-lg text-emerald-400"></i> Copied';
                setTimeout(() => { copyBtn.innerHTML = orig; }, 2000);
            });
        });
    }

    const claimBtn = document.getElementById('exitIntentClaimBtn');
    if (claimBtn) {
        claimBtn.addEventListener('click', function() {
            if (window.Alpine && Alpine.store('modal')) {
                Alpine.store('modal').close();
                setTimeout(() => {
                    Alpine.store('modal').open('booking', { promo: '{{ $exitIntentCode }}' });
                    const promoInput = document.getElementById('bookingPromoCode');
                    if (promoInput) promoInput.value = '{{ $exitIntentCode }}';
                    if (typeof window.validateCurrentPromo === 'function') {
                        setTimeout(() => window.validateCurrentPromo(), 400);
                    }
                }, 300);
            }
        });
    }
});
</script>
@endif
