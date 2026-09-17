<!-- Smart Exit-Intent Cart Saver Modal -->
<div class="modal fade" id="exitIntentModal" tabindex="-1" aria-labelledby="exitIntentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg" style="background: #0B1120; border: 1.5px solid rgba(246, 144, 68, 0.4) !important; border-radius: 28px; color: #ffffff; overflow: hidden; box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.9);">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 position-relative z-2">
                <div class="w-100 d-flex align-items-center justify-content-between">
                    <span class="badge rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5" style="background: rgba(239, 68, 68, 0.18); border: 1px solid rgba(239, 68, 68, 0.4); color: #EF4444; font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="bi bi-gift-fill text-warning"></i> Exclusive Departure Offer
                    </span>
                    <button type="button" class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center shadow-none border-0" data-bs-dismiss="modal" aria-label="Close" id="exitIntentCloseBtn" style="background: rgba(255, 255, 255, 0.08); width: 38px; height: 38px;">
                        <i class="bi bi-x-lg fs-6 text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 p-md-5 text-center position-relative z-1">
                <div class="mb-3">
                    <span class="display-3 d-inline-block animate-bounce" style="line-height: 1;">🏜️</span>
                </div>

                <h3 class="fw-800 text-white mb-2 fs-3" id="exitIntentModalLabel">
                    Wait! Don't Leave Dubai Without Experiencing The Dunes
                </h3>
                <p class="text-white-50 small mb-4 mx-auto" style="max-width: 520px; font-size: 0.9rem; line-height: 1.6;">
                    Before you go, take an instant <strong class="text-warning">5% OFF</strong> on all certified Dubai desert safari packages with 4x4 hotel pickup and 5-star live BBQ dinner.
                </p>

                <!-- Gamified Coupon Certificate Card -->
                <div class="card border-0 rounded-4 p-3 mb-4 mx-auto" style="max-width: 500px; background: linear-gradient(135deg, rgba(246, 144, 68, 0.18), rgba(251, 191, 36, 0.08)); border: 1.5px dashed #F69044 !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="text-start">
                            <small class="text-white-50 d-block" style="font-size: 11px; text-transform: uppercase; font-weight: 700;">Instant Promo Code</small>
                            <span class="font-monospace fw-800 fs-4 text-warning" id="exitIntentCodeDisplay">SAVE5</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.8rem;">
                                5% Instant Savings
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold" id="exitIntentCopyBtn" title="Copy Code">
                                <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Trust Guarantees -->
                <div class="row g-2 mb-4 mx-auto text-start" style="max-width: 500px;">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-white-50 small" style="font-size: 12px;">
                            <i class="bi bi-patch-check-fill text-primary"></i>
                            <span>DTCM Licensed #1430583</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-white-50 small" style="font-size: 12px;">
                            <i class="bi bi-arrow-repeat text-success"></i>
                            <span>100% Free Cancel 24h</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-white-50 small" style="font-size: 12px;">
                            <i class="bi bi-award-fill text-warning"></i>
                            <span>5-Star Halal BBQ Buffet</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-white-50 small" style="font-size: 12px;">
                            <i class="bi bi-shield-lock-fill text-info"></i>
                            <span>Pay Online or on Pickup</span>
                        </div>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="row g-2 mx-auto" style="max-width: 500px;">
                    <div class="col-12 col-sm-7">
                        <button type="button" class="btn btn-desert-animated w-100 py-3 rounded-pill fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2" id="exitIntentClaimBtn">
                            <i class="bi bi-tag-fill"></i>
                            <span>Claim 5% & Book Now</span>
                        </button>
                    </div>
                    <div class="col-12 col-sm-5">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',(string)(\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056')) }}?text={{ urlencode('Hi Dunes Discovery, I am looking to book a desert safari with the 5% discount code SAVE5. Could you recommend the best package for my group?') }}" class="btn btn-whatsapp-animated w-100 py-3 rounded-pill fw-bold fs-6 d-flex align-items-center justify-content-center gap-2" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            <span>WhatsApp Us</span>
                        </a>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-link text-white-50 text-decoration-none small p-0" data-bs-dismiss="modal" id="exitIntentDismissLink" style="font-size: 12px;">
                        No thanks, I'll pay full price later
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exitModalEl = document.getElementById('exitIntentModal');
    if (!exitModalEl) return;

    let exitIntentFired = false;
    const STORAGE_KEY = 'dunes_exit_intent_dismissed_v1';

    // Check if dismissed in this session
    if (sessionStorage.getItem(STORAGE_KEY)) {
        exitIntentFired = true;
    }

    function triggerExitIntent() {
        if (exitIntentFired) return;

        // Check if any other modal (booking, search, etc.) is already open
        if (document.querySelector('.modal.show')) return;

        exitIntentFired = true;
        sessionStorage.setItem(STORAGE_KEY, '1');

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const exitModal = bootstrap.Modal.getOrCreateInstance(exitModalEl);
            exitModal.show();
        }
    }

    // Desktop: detect mouse moving up towards browser address bar/tab bar
    document.addEventListener('mouseleave', function(e) {
        if (e.clientY <= 10) {
            triggerExitIntent();
        }
    });

    // Mobile / Inactivity fallback: trigger after 45 seconds of browsing without booking
    let inactivityTimer = setTimeout(function() {
        // Trigger on mobile only if viewport is small and user hasn't opened booking
        if (window.innerWidth <= 768 && !exitIntentFired) {
            triggerExitIntent();
        }
    }, 45000);

    // Copy Code button
    const copyBtn = document.getElementById('exitIntentCopyBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText('SAVE5').then(() => {
                const orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="bi bi-check-lg text-success"></i> Copied';
                setTimeout(() => { copyBtn.innerHTML = orig; }, 2000);
            });
        });
    }

    // Claim 5% & Book Now button
    const claimBtn = document.getElementById('exitIntentClaimBtn');
    if (claimBtn) {
        claimBtn.addEventListener('click', function() {
            const exitModal = bootstrap.Modal.getInstance(exitModalEl);
            if (exitModal) exitModal.hide();

            setTimeout(() => {
                const bookingModalEl = document.getElementById('bookingModal');
                if (!bookingModalEl) return;

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                    bModal.show();
                }

                const promoInput = document.getElementById('bookingPromoCode');
                if (promoInput) {
                    promoInput.value = 'SAVE5';
                }

                if (typeof window.validateCurrentPromo === 'function') {
                    setTimeout(() => window.validateCurrentPromo(), 400);
                }
            }, 350);
        });
    }
});
</script>
