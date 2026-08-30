@php
    $modalTours = isset($allTours) && count($allTours) ? $allTours : try_get_modal_tours();
    $minDate = date('Y-m-d', strtotime('+1 day'));
    $ziinaActive = isset($settings['ziina_active']) ? ($settings['ziina_active'] === '1') : false;
    $advancePercent = (int)($settings['ziina_advance_percent'] ?? 10);

    function try_get_modal_tours() {
        try {
            return \App\Models\Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
@endphp
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 24px; display: flex; flex-direction: column; height: auto; max-height: none;">

            <div class="modal-header border-bottom bg-white py-3 px-4 sticky-top z-3">
                <div class="d-flex align-items-center gap-3 w-100">
                    <button type="button" class="btn btn-light rounded-circle shadow-sm p-0 d-none" id="headerBackBtn" style="width: 40px; height: 40px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="flex-grow-1">
                        <h5 class="modal-title fw-800 h5 mb-0" id="bookingModalTitle">Book Your Adventure</h5>
                        <div class="text-primary small fw-bold d-none" id="bookingModalSubtitle">Step 1 of 2</div>
                    </div>
                    <button type="button" class="btn-close shadow-none bg-light rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0 bg-light" style="flex: 1; overflow: hidden;">
                <form id="bookingForm" autocomplete="off" class="h-100 d-flex flex-column needs-validation" style="min-height: 0;">
                    @csrf
                    <!-- Honeypot anti-spam field -->
                    <div style="position:absolute;left:-9999px">
                        <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="action" value="booking">
                    <input type="hidden" name="utm_source" id="utmSource">
                    <input type="hidden" name="utm_medium" id="utmMedium">
                    <input type="hidden" name="utm_campaign" id="utmCampaign">
                    <input type="hidden" name="gps_lat" id="gpsLat">
                    <input type="hidden" name="gps_lng" id="gpsLng">
                    <input type="hidden" name="gps_address" id="gpsAddress">
                    <input type="hidden" name="gps_accuracy" id="gpsAccuracy">
                    <input type="hidden" name="gps_timestamp" id="gpsTimestamp">
                    <input type="hidden" name="gps_consent" id="gpsConsent" value="No">
                    <input type="hidden" name="gps_source" id="gpsSource" value="Not Available">
                    <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                    <input type="hidden" name="payment_amount" id="paymentAmount" value="0">

                    <div class="booking-scroll-area p-4 flex-grow-1 overflow-y-auto">

                        <!-- Step 1: Select Tour and Date -->
                        <div class="step-content active" data-step="1">
                            <div class="mb-4" id="tourSelectWrapper">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Choose Tour</div>
                                <div class="form-floating">
                                    <select class="form-select border-0 shadow-sm rounded-4 fw-bold" id="bookingTour" name="tour_id" required style="height: 60px;" autocomplete="off">
                                        <option value="">Select a tour...</option>
                                        @foreach($modalTours as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="bookingTour">Select Tour</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Select Package</div>
                                <div class="tier-cards" id="tierCards">
                                    <div class="text-center text-muted opacity-50">
                                        <i class="bi bi-cursor-fill fs-1 mb-2 d-block"></i>
                                        <small class="fw-bold">Select a tour above to view packages</small>
                                    </div>
                                </div>
                                <input type="hidden" name="tier_id" id="selectedTier" required>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                    <div class="fw-800 small text-muted text-uppercase mb-0">When</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-light btn-sm rounded-pill px-1 d-inline-flex align-items-center gap-2" id="calendarTrigger">
                                            <i class="bi bi-calendar3"></i>
                                            <span class="small fw-bold">Select from Calendar</span>
                                        </button>
                                        <div class="date-nav d-flex align-items-center">
                                            <button type="button" class="btn btn-light btn-sm rounded-circle" id="datePrev"><i class="bi bi-chevron-left"></i></button>
                                            <button type="button" class="btn btn-light btn-sm rounded-circle ms-1" id="dateNext"><i class="bi bi-chevron-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="date-cards-wrapper d-flex gap-2 overflow-x-auto pb-2" id="dateCardsWrapper"></div>
                                <input type="date" class="form-control visually-hidden" name="date" id="bookingDate" required min="{{ $minDate }}" autocomplete="off">
                            </div>

                            <div class="row g-3 mb-3 booking-guest-pickup-row align-items-stretch">
                                <div class="col-12 col-sm-4 col-lg-3">
                                    <label class="form-label fw-800 small text-muted text-uppercase mb-2" for="bookingAdults">Guests</label>
                                    <div class="booking-field-container d-flex align-items-center justify-content-between bg-white shadow-sm rounded-4 p-0">
                                        <button type="button" class="btn btn-link text-primary shadow-none p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 100%;" data-action="minus" data-target="adults">
                                            <i class="bi bi-dash-circle-fill fs-5"></i>
                                        </button>
                                        <div class="text-center lh-1 flex-grow-1 d-flex justify-content-center">
                                            <input type="number" class="form-control border-0 bg-transparent text-center fw-800 shadow-none p-0 fs-5" name="adults" id="bookingAdults" value="1" min="1" max="50" readonly style="width: 3ch;" autocomplete="off">
                                        </div>
                                        <button type="button" class="btn btn-link text-primary shadow-none p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 100%;" data-action="plus" data-target="adults">
                                            <i class="bi bi-plus-circle-fill fs-5"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="children" id="bookingChildren" value="0">
                                </div>
                                <div class="col-12 col-sm-8 col-lg-9">
                                    <label class="form-label fw-800 small text-muted text-uppercase mb-2" for="bookingLocation">Pickup</label>
                                    <div class="position-relative booking-location-wrapper">
                                        <div class="booking-field-container input-group shadow-sm rounded-4 overflow-hidden">
                                            <span class="input-group-text bg-white border-0 ps-3 pe-2"><i class="bi bi-geo-alt-fill text-primary"></i></span>
                                            <input type="text" class="form-control border-0 shadow-none fw-bold px-0" name="location" id="bookingLocation" required placeholder="Hotel/Area" style="height: 60px;" autocomplete="street-address">
                                            <button class="btn btn-white border-start px-3" type="button" id="detectLocation">
                                                <i class="bi bi-crosshair"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Customer details, Addons, and Payment options -->
                        <div class="step-content d-none" data-step="2">
                            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-check-lg fw-bold"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 10px;">Selected Package</small>
                                            <div class="fw-800 text-dark lh-1" id="summaryTourName">Loading...</div>
                                            <div class="small text-muted mt-1" id="summaryTierName"></div>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <div class="fw-800 text-primary" id="summaryTotal">AED 0</div>
                                            <a href="#" class="small text-decoration-none fw-bold" id="editStep1">Edit</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Tour-Specific Addons Section -->
                            <div class="mb-4" id="addonsSection" style="display:none">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-800 small text-muted text-uppercase d-flex align-items-center gap-1">
                                        <i class="bi bi-stars text-warning"></i>
                                        <span>Enhance Your Safari (Optional Add-ons)</span>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2 py-1" style="font-size: 0.7rem;">1-Click Add</span>
                                </div>
                                <div class="addon-horizontal-wrapper" id="addonList"></div>
                            </div>

                            <!-- OTP verification state banners -->
                            <div class="alert alert-info d-none mb-3" id="otpNotice"></div>

                            <div class="mb-3">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Contact Info</div>
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'bookingName',
                                    'name' => 'name',
                                    'label' => 'Full Name',
                                    'placeholder' => 'John Doe',
                                    'autocomplete' => 'name',
                                    'required' => true,
                                    'wrapperClass' => 'form-floating mb-3',
                                    'inputClass' => 'form-control border-0 shadow-sm rounded-4 fw-bold',
                                    'inputAttrs' => ['data-form' => 'booking', 'data-field' => 'name']
                                ]) !!}
                                {!! renderFloatingInput([
                                    'type' => 'email',
                                    'id' => 'bookingEmail',
                                    'name' => 'email',
                                    'label' => 'Email Address',
                                    'placeholder' => 'name@example.com',
                                    'autocomplete' => 'email',
                                    'required' => true,
                                    'wrapperClass' => 'form-floating mb-3',
                                    'inputClass' => 'form-control border-0 shadow-sm rounded-4 fw-bold',
                                    'inputAttrs' => ['data-form' => 'booking', 'data-field' => 'email']
                                ]) !!}
                                {!! renderFloatingInput([
                                    'type' => 'tel',
                                    'id' => 'bookingPhone',
                                    'name' => 'phone',
                                    'label' => 'Phone Number',
                                    'placeholder' => '+971',
                                    'autocomplete' => 'tel',
                                    'required' => true,
                                    'wrapperClass' => 'form-floating phone-field',
                                    'inputClass' => 'form-control border-0 shadow-sm rounded-4 fw-bold',
                                    'inputAttrs' => ['data-form' => 'booking', 'data-field' => 'phone']
                                ]) !!}
                            </div>

                            <!-- OTP Verification Fields (Loaded dynamically via JS if needed) -->
                            <div class="mb-3 d-none" id="otpFieldsWrapper">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Email Verification Code</div>
                                <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                    <input type="text" class="form-control border-0 shadow-none fw-bold text-center" id="bookingOtpCode" placeholder="Enter 6-digit OTP" style="height: 60px; letter-spacing: 5px; font-size: 1.25rem;">
                                    <button class="btn btn-primary px-4 fw-bold" type="button" id="verifyOtpBtn">Verify</button>
                                </div>
                                <div class="d-flex justify-content-between mt-2 px-1">
                                    <span class="small text-muted" id="otpTimer"></span>
                                    <a href="#" class="small text-decoration-none fw-bold" id="resendOtpBtn">Resend Code</a>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Notes</div>
                                {!! renderFloatingTextarea([
                                    'id' => 'bookingRequests',
                                    'name' => 'requests',
                                    'label' => 'Any special requirements?',
                                    'placeholder' => 'Special requests',
                                    'autocomplete' => 'off',
                                    'inputClass' => 'form-control border-0 shadow-sm rounded-4 fw-bold',
                                    'inputAttrs' => ['style' => 'height: 100px', 'data-form' => 'booking', 'data-field' => 'requests']
                                ]) !!}
                            </div>

                            <!-- Promo / Coupon Code Section -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3" id="promoCodeCard">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="fw-800 small text-muted text-uppercase mb-0" for="bookingPromoCode">
                                        <i class="bi bi-tag-fill text-primary me-1"></i> Have a Promo Code?
                                    </label>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small fw-bold d-none" id="promoAppliedBadge">
                                        <i class="bi bi-check-circle-fill me-1"></i>Applied
                                    </span>
                                </div>
                                <div class="input-group rounded-4 overflow-hidden border" id="promoInputGroup">
                                    <input type="text" class="form-control border-0 shadow-none fw-bold text-uppercase px-3 font-monospace" id="bookingPromoCode" name="coupon_code" placeholder="ENTER CODE (e.g. SUMMER2026)" style="height: 48px; letter-spacing: 1px;" autocomplete="off">
                                    <button class="btn btn-dark px-4 fw-800" type="button" id="applyPromoBtn">Apply</button>
                                </div>
                                
                                <div class="d-none mt-2 align-items-center justify-content-between p-3 rounded-4 bg-success-subtle text-success border border-success-subtle" id="promoSuccessBox">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                                            <i class="bi bi-check-lg fw-bold"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block font-monospace fs-6" id="promoCodeLabel">CODE</strong>
                                            <small class="fw-bold" id="promoSavingsText">Savings applied</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold p-0" id="removePromoBtn">
                                        <i class="bi bi-x-circle-fill me-1"></i>Remove
                                    </button>
                                </div>
                                <div class="alert alert-danger p-2 small mt-2 d-none mb-0 rounded-3" id="promoErrorBox"></div>
                            </div>

                            <div class="mb-4" id="paymentOptions" data-ziina-active="{{ $ziinaActive ? '1' : '0' }}" data-advance-percent="{{ $advancePercent }}">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Payment Options</div>
                                <div class="payment-options">
                                    <div class="payment-option selected" data-value="cash">
                                        <div class="payment-option-title">Cash</div>
                                        <div class="payment-option-sub">Pay on pickup</div>
                                    </div>
                                    @if($ziinaActive)
                                    <div class="payment-option" data-value="advance">
                                        <div class="payment-option-title">Advance</div>
                                        <div class="payment-option-sub">Hold slot ({{ $advancePercent }}%)</div>
                                    </div>
                                    <div class="payment-option" data-value="full">
                                        <div class="payment-option-title">Full</div>
                                        <div class="payment-option-sub">Instant confirmation</div>
                                    </div>
                                    @endif
                                </div>
                                <div class="alert alert-danger mt-3 d-none" id="bookingError"></div>
                            </div>

                            <div class="mb-3">
                                <div class="legal-agreement-wrapper">
                                    <input class="form-check-input desert-checkbox border-primary" type="checkbox" id="bookingAgreement" required>
                                    <label class="legal-agreement-text" for="bookingAgreement">
                                        I agree to the <a href="{{ route('terms') }}" target="_blank" class="legal-link">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" class="legal-link">Privacy Policy</a>.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-white p-3 z-3" style="position: sticky; bottom: 0; padding-bottom: max(1rem, env(safe-area-inset-bottom)) !important;">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="d-flex align-items-center">
                                <div class="fw-800 text-primary mb-0 booking-total-value" id="bookingTotal">AED 0.00</div>
                            </div>
                            <div class="d-flex align-items-center ms-auto" id="continueBtnWrapper">
                                <button type="button" class="btn btn-desert-animated rounded-pill w-100 w-sm-auto px-5 py-3 fw-800 shadow-lg d-inline-flex align-items-center justify-content-center gap-2" id="nextStep"> Continue <i class="bi bi-arrow-right"></i> </button>
                                <button type="submit" class="btn btn-whatsapp-animated rounded-pill w-100 w-sm-auto px-5 py-3 fw-800 shadow-lg d-none align-items-center justify-content-center gap-2" id="submitBooking" onclick="if(typeof gtagReportConversion==='function'){gtagReportConversion();}"> Confirm <i class="bi bi-check-lg"></i> </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.appliedPromoCoupon = null;

    const promoInput = document.getElementById('bookingPromoCode');
    const applyBtn = document.getElementById('applyPromoBtn');
    const removeBtn = document.getElementById('removePromoBtn');
    const successBox = document.getElementById('promoSuccessBox');
    const inputGroup = document.getElementById('promoInputGroup');
    const errorBox = document.getElementById('promoErrorBox');
    const codeLabel = document.getElementById('promoCodeLabel');
    const savingsLabel = document.getElementById('promoSavingsText');
    const appliedBadge = document.getElementById('promoAppliedBadge');

    // Auto-apply promo from URL (?promo=CODE or ?coupon=CODE)
    const urlParams = new URLSearchParams(window.location.search);
    const urlPromo = urlParams.get('promo') || urlParams.get('coupon');
    if (urlPromo && promoInput) {
        promoInput.value = urlPromo.toUpperCase().trim();
    }

    window.validateCurrentPromo = function() {
        if (!promoInput) return;
        const code = promoInput.value.trim().toUpperCase();
        if (!code) {
            if (errorBox) {
                errorBox.innerText = 'Please enter a promo code.';
                errorBox.classList.remove('d-none');
            }
            return;
        }

        // Fetch current subtotal
        let subtotal = 0;
        if (window.App && typeof window.App.calculateBaseTotal === 'function') {
            subtotal = window.App.calculateBaseTotal();
        } else if (window.DunesApp && typeof window.DunesApp.calculateBaseTotal === 'function') {
            subtotal = window.DunesApp.calculateBaseTotal();
        }

        const adults = parseInt(document.getElementById('bookingAdults')?.value || '1', 10);

        if (subtotal <= 0) {
            const selectedTierCard = document.querySelector('.tier-card.selected');
            if (selectedTierCard && selectedTierCard.dataset.price) {
                subtotal = parseFloat(selectedTierCard.dataset.price) * adults;
            }
        }

        if (subtotal <= 0) {
            const rawTxt = document.getElementById('bookingTotal')?.innerText?.replace(/[^0-9.]/g, '') || document.getElementById('summaryTotal')?.innerText?.replace(/[^0-9.]/g, '') || '0';
            subtotal = parseFloat(rawTxt) || 0;
        }

        const tourId = document.getElementById('bookingTour')?.value || null;
        const tierId = document.getElementById('selectedTier')?.value || null;
        const email = document.getElementById('bookingEmail')?.value || null;
        const tourDate = document.getElementById('bookingDate')?.value || null;

        if (applyBtn) {
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }
        if (errorBox) errorBox.classList.add('d-none');

        fetch('/api/v1/coupon/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                code: code,
                subtotal: subtotal,
                tour_id: tourId,
                tier_id: tierId,
                email: email,
                adults: adults,
                date: tourDate
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (applyBtn) {
                applyBtn.disabled = false;
                applyBtn.innerText = 'Apply';
            }

            if (status === 200 && body.success && body.coupon) {
                window.appliedPromoCoupon = body.coupon;
                codeLabel.innerText = body.coupon.code;
                if (body.coupon.savings_text) {
                    savingsLabel.innerText = body.coupon.savings_text;
                }
                
                inputGroup.classList.add('d-none');
                successBox.classList.remove('d-none');
                successBox.classList.add('d-flex');
                if (appliedBadge) appliedBadge.classList.remove('d-none');

                if (window.App && typeof window.App.updateTotal === 'function') {
                    window.App.updateTotal();
                } else if (window.DunesApp && typeof window.DunesApp.updateTotal === 'function') {
                    window.DunesApp.updateTotal();
                } else {
                    const originalTotal = parseFloat(body.coupon.original_total);
                    const newTotal = parseFloat(body.coupon.new_total);
                    const totalEl = document.getElementById('bookingTotal');
                    const summaryTotalEl = document.getElementById('summaryTotal');

                    if (totalEl) totalEl.innerText = `AED ${newTotal.toFixed(2)}`;
                    if (summaryTotalEl) {
                        summaryTotalEl.innerHTML = `<span class="text-decoration-line-through text-muted small me-2">AED ${originalTotal.toFixed(2)}</span> <span class="text-success">AED ${newTotal.toFixed(2)}</span>`;
                    }
                }
            } else {
                window.appliedPromoCoupon = null;
                if (errorBox) {
                    errorBox.innerText = body.message || 'Invalid promo code.';
                    errorBox.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            if (applyBtn) {
                applyBtn.disabled = false;
                applyBtn.innerText = 'Apply';
            }
            if (errorBox) {
                errorBox.innerText = 'Unable to validate promo code. Please try again.';
                errorBox.classList.remove('d-none');
            }
        });
    };

    window.removeCurrentPromo = function() {
        window.appliedPromoCoupon = null;
        if (promoInput) promoInput.value = '';
        if (inputGroup) inputGroup.classList.remove('d-none');
        if (successBox) {
            successBox.classList.add('d-none');
            successBox.classList.remove('d-flex');
        }
        if (appliedBadge) appliedBadge.classList.add('d-none');
        if (errorBox) errorBox.classList.add('d-none');

        if (window.App && typeof window.App.updateTotal === 'function') {
            window.App.updateTotal();
        } else if (window.DunesApp && typeof window.DunesApp.updateTotal === 'function') {
            window.DunesApp.updateTotal();
        }
    };

    if (applyBtn) applyBtn.addEventListener('click', window.validateCurrentPromo);
    if (removeBtn) removeBtn.addEventListener('click', window.removeCurrentPromo);
    if (promoInput) {
        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.validateCurrentPromo();
            }
        });
    }
});
</script>

