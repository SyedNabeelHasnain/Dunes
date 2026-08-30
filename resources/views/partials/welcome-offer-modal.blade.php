@php
    $settingsService = app(\App\Services\SettingsService::class);
    $popupActive = $settingsService->get('welcome_popup_active', '1') === '1';
    $popupDiscount = (float)$settingsService->get('welcome_popup_discount', '25');
    $popupTimerMins = (int)$settingsService->get('welcome_popup_timer_mins', '15');
    $popupDelaySec = (int)$settingsService->get('welcome_popup_delay_sec', '5');
    $popupScrollTrigger = $settingsService->get('welcome_popup_scroll_trigger', '1') === '1';
    $popupExitTrigger = $settingsService->get('welcome_popup_exit_trigger', '1') === '1';
    $popupHeadline = $settingsService->get('welcome_popup_headline', 'Unlock 25% OFF Your Dubai Desert Adventure');
    $popupSubheadline = $settingsService->get('welcome_popup_subheadline', 'Valid for today\'s booking • Tour date can be selected for any future date!');
    
    $topBannerActive = $settingsService->get('top_promo_banner_active', '1') === '1';
    $topBannerText = $settingsService->get('top_promo_banner_text', '🎟️ First-Time Traveler? Claim 25% OFF Your Desert Safari Today with Code FIRST25! • 100% Free 24h Cancellation');
    $topBannerCode = $settingsService->get('top_promo_banner_code', 'FIRST25');
@endphp

@if($topBannerActive)
<!-- Top Sticky Announcement Bar -->
<div id="dunesTopPromoBanner" class="py-2 px-3 text-white text-center position-relative z-3 shadow-sm d-flex align-items-center justify-content-center flex-wrap gap-2" style="background: linear-gradient(90deg, #111827 0%, #1f2937 50%, #0f172a 100%); border-bottom: 2px solid #F58F43; font-size: 0.85rem;">
    <span class="fw-bold">{{ $topBannerText }}</span>
    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-0 fw-800 text-dark d-inline-flex align-items-center gap-1 shadow-sm top-banner-copy-btn" data-code="{{ $topBannerCode }}" style="font-size: 0.75rem; height: 26px;">
        <span>CODE: <strong class="font-monospace">{{ $topBannerCode }}</strong></span>
        <i class="bi bi-clipboard"></i>
    </button>
    <button type="button" class="btn-close btn-close-white ms-2 shadow-none position-absolute end-0 me-3" style="font-size: 0.65rem;" onclick="document.getElementById('dunesTopPromoBanner').style.display='none';"></button>
</div>
@endif

@if($popupActive)
<!-- First-Time Visitor 25% Discount Voucher Modal -->
<div class="modal fade" id="welcomeOfferModal" tabindex="-1" aria-labelledby="welcomeOfferLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden position-relative" style="background: #ffffff;">
            
            <!-- Luxury Orange & Gold Ambient Glow Bar -->
            <div style="height: 6px; background: linear-gradient(90deg, #F58F43 0%, #FFB067 50%, #d2a13b 100%);"></div>

            <!-- Close Button -->
            <button type="button" class="btn-close position-absolute top-0 end-0 m-4 shadow-none z-3 p-2 bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" id="closeWelcomeOfferBtn" style="font-size: 0.8rem;"></button>

            <div class="modal-body p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    
                    <!-- Left Visual & Highlights Column -->
                    <div class="col-lg-5 text-center text-lg-start">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary fw-800 small text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-gift-fill"></i>
                            <span>First-Time Guest Special</span>
                        </div>

                        <h2 class="display-6 fw-800 text-dark lh-1 mb-3" style="letter-spacing: -0.5px;">
                            {{ $popupHeadline }}
                        </h2>

                        <p class="text-muted small mb-4 lh-base">
                            {{ $popupSubheadline }}
                        </p>

                        <!-- Trust Pillars -->
                        <div class="d-flex flex-column gap-2 mb-4 text-start">
                            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span>100% Free 24h Cancellation</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                                <i class="bi bi-patch-check-fill text-primary fs-5" style="color: #F58F43 !important;"></i>
                                <span>DTCM Licensed Desert Marshals</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                                <i class="bi bi-stars text-warning fs-5"></i>
                                <span>5-Star Halal Gourmet Dining</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                                <i class="bi bi-cash-coin text-info fs-5"></i>
                                <span>Zero-Deposit Cash on Pickup</span>
                            </div>
                        </div>

                        <!-- Session Countdown Box -->
                        <div class="p-3 rounded-4 text-center border" style="background: #fff8f3; border-color: rgba(245, 143, 67, 0.25) !important;">
                            <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">Session Offer Expires In</span>
                            <div class="fw-800 fs-4 text-primary font-monospace" id="welcomeOfferCountdown" style="letter-spacing: 2px;">{{ sprintf('%02d', $popupTimerMins) }}:00</div>
                        </div>
                    </div>

                    <!-- Right Form / Success Column -->
                    <div class="col-lg-7">
                        <div class="p-4 p-md-4 rounded-4 shadow-sm bg-light border border-light">
                            
                            <!-- STATE 1: Lead Capture Form -->
                            <div id="welcomeOfferFormState">
                                <div class="text-center mb-4">
                                    <h4 class="fw-800 text-dark mb-1">Claim Your Voucher</h4>
                                    <p class="text-muted small mb-0">Enter your email to receive your exclusive {{ (int)$popupDiscount }}% promo voucher instantly.</p>
                                </div>

                                <form id="welcomeOfferForm">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcomeEmail">Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                            <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-envelope-fill text-primary"></i></span>
                                            <input type="email" class="form-control border-0 shadow-none py-3 fw-bold ps-2" id="welcomeEmail" name="email" placeholder="name@example.com" required autocomplete="email">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcomeName">Your Name (Optional)</label>
                                        <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                            <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-person-fill"></i></span>
                                            <input type="text" class="form-control border-0 shadow-none py-3 fw-bold ps-2" id="welcomeName" name="name" placeholder="e.g. Sarah Connor" autocomplete="name">
                                        </div>
                                    </div>

                                    <div class="alert alert-danger p-2 small mt-2 d-none mb-3 rounded-3" id="welcomeOfferError"></div>

                                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-800 fs-6 shadow-lg d-flex align-items-center justify-content-center gap-2 mb-3" id="claimOfferSubmitBtn" style="background: linear-gradient(135deg, #F58F43 0%, #e07425 100%); border: none;">
                                        <span>Claim My {{ (int)$popupDiscount }}% Discount</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </button>

                                    <div class="text-center text-muted small" style="font-size: 0.72rem;">
                                        <i class="bi bi-lock-fill me-1"></i> No spam ever. Single-use voucher valid for 24h.
                                    </div>
                                </form>
                            </div>

                            <!-- STATE 2: Success & Instant 1-Click Booking -->
                            <div id="welcomeOfferSuccessState" class="d-none text-center py-2">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-lg" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check-lg fs-2 fw-bold"></i>
                                </div>
                                <h3 class="fw-800 text-dark mb-1">{{ (int)$popupDiscount }}% Discount Unlocked!</h3>
                                <p class="text-muted small mb-4">Your personalized promo voucher is generated and ready to apply.</p>

                                <!-- Golden Ticket Display -->
                                <div class="p-4 rounded-4 text-white mb-4 position-relative shadow-lg" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 2px dashed #F58F43;">
                                    <span class="text-uppercase small fw-bold text-warning d-block mb-1" style="letter-spacing: 1.5px; font-size: 0.75rem;">Your Exclusive Promo Code</span>
                                    <div class="fs-2 fw-800 font-monospace text-white my-2" id="generatedVoucherCode" style="letter-spacing: 3px;">FIRST25-XXXXX</div>
                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-bold text-dark mt-1" id="copyVoucherBtn">
                                        <i class="bi bi-clipboard me-1"></i> Copy Code
                                    </button>
                                </div>

                                <button type="button" class="btn btn-primary rounded-pill w-100 py-3 fw-800 fs-6 shadow-lg d-flex align-items-center justify-content-center gap-2 mb-2" id="applyVoucherAndBookBtn" style="background: linear-gradient(135deg, #F58F43 0%, #e07425 100%); border: none;">
                                    <i class="bi bi-cart-check-fill me-1"></i>
                                    <span>Apply {{ (int)$popupDiscount }}% OFF & Book Safari Now</span>
                                </button>
                                
                                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
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
<div id="welcomeFloatingPill" class="d-none position-fixed bottom-0 start-0 m-3 z-3 shadow-lg rounded-pill p-2 ps-3 pe-3 bg-dark text-white border border-secondary d-flex align-items-center gap-2" style="cursor: pointer; transition: all 0.3s ease; animation: slideInPill 0.4s ease forwards;">
    <span class="badge bg-warning text-dark fw-800 rounded-pill px-2 py-1">{{ (int)$popupDiscount }}% OFF</span>
    <span class="small fw-bold font-monospace text-white" id="floatingPillCode">FIRST25-OFF</span>
    <span class="small text-muted font-monospace d-none d-sm-inline" id="floatingPillTimer">{{ sprintf('%02d', $popupTimerMins) }}:00</span>
    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-1 fw-bold ms-1" style="background: #F58F43; border: none; font-size: 0.75rem;">
        Apply
    </button>
</div>

<style>
@keyframes slideInPill {
    from { transform: translateY(100px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
#welcomeFloatingPill:hover {
    transform: scale(1.04) translateY(-2px);
    box-shadow: 0 10px 25px rgba(245, 143, 67, 0.4) !important;
}
</style>

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

        // Robust Modal Show Helper (works with Bootstrap or custom fallback)
        function showOfferModal() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const bModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    bModal.show();
                    return;
                } catch(e) {}
            }
            
            // Fallback if bootstrap is still loading
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }

        function hideOfferModal() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const bModal = bootstrap.Modal.getInstance(modalEl);
                    if (bModal) bModal.hide();
                } catch(e) {}
            }
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }

        const closeBtn = document.getElementById('closeWelcomeOfferBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', hideOfferModal);
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
            // Trigger 1: Delay Timer
            setTimeout(() => {
                triggerModal();
            }, popupDelaySec * 1000);

            // Trigger 2: Scroll Depth
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

            // Trigger 3: Desktop Exit Intent
            if (enableExit) {
                let exitTriggered = false;
                document.addEventListener('mouseleave', (e) => {
                    if (exitTriggered || e.clientY > 20) return;
                    exitTriggered = true;
                    triggerModal();
                });
            }

            // Dismissal Handler
            modalEl.addEventListener('hidden.bs.modal', function() {
                const claimed = localStorage.getItem('dunes_welcome_claimed');
                if (!claimed) {
                    localStorage.setItem('dunes_welcome_dismissed_until', Date.now() + (24 * 60 * 60 * 1000));
                }
            });
        }

        // Form Submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('welcomeEmail')?.value.trim();
                const name = document.getElementById('welcomeName')?.value.trim();

                if (!email) return;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating Voucher...';
                if (errorBox) errorBox.classList.add('d-none');

                fetch('/api/v1/welcome-offer/claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ email: email, name: name })
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
                        formState.classList.add('d-none');
                        successState.classList.remove('d-none');

                        showFloatingPill(code);
                    } else {
                        if (errorBox) {
                            errorBox.innerText = body.message || 'Unable to generate voucher. Please try again.';
                            errorBox.classList.remove('d-none');
                        }
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Claim My Discount</span> <i class="bi bi-arrow-right"></i>';
                    if (errorBox) {
                        errorBox.innerText = 'Network error. Please try again.';
                        errorBox.classList.remove('d-none');
                    }
                });
            });
        }

        // Copy Code
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const code = codeDisplay.innerText.trim();
                navigator.clipboard.writeText(code).then(() => {
                    copyBtn.innerHTML = '<i class="bi bi-check-lg text-success me-1"></i> Copied!';
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
                    const bookingModalEl = document.getElementById('bookingModal');
                    if (bookingModalEl) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                            bModal.show();
                        }
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
            floatingPill.classList.remove('d-none');
            floatingPill.classList.add('d-flex');
            startTimer();

            floatingPill.onclick = function() {
                const bookingModalEl = document.getElementById('bookingModal');
                if (bookingModalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                        bModal.show();
                    }
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

    // Initialize on DOM or Window Load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWelcomeOffer);
    } else {
        initWelcomeOffer();
    }

    // Top Banner Copy listener
    document.querySelectorAll('.top-banner-copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const code = this.dataset.code || 'FIRST25';
            navigator.clipboard.writeText(code).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="text-success fw-bold">COPIED!</span>';
                setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);

                const bookingModalEl = document.getElementById('bookingModal');
                if (bookingModalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                        bModal.show();
                    }
                    const promoInput = document.getElementById('bookingPromoCode');
                    if (promoInput) promoInput.value = code;
                    if (typeof window.validateCurrentPromo === 'function') {
                        setTimeout(() => window.validateCurrentPromo(), 400);
                    }
                }
            });
        });
    });
})();
</script>
