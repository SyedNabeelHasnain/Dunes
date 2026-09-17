<!-- Safari Matcher AI Recommendation Concierge & Gamified Discount Modal -->
<div class="modal fade" id="safariMatcherModal" tabindex="-1" aria-labelledby="safariMatcherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg" style="background: #0B1120; border: 1px solid rgba(246, 144, 68, 0.35) !important; border-radius: 26px; color: #ffffff; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85);">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 position-relative z-2">
                <div class="w-100 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge rounded-pill px-3 py-1.5 small mb-1 d-inline-flex align-items-center gap-1" style="background: rgba(246, 144, 68, 0.18); border: 1px solid rgba(246, 144, 68, 0.4); color: #F69044; font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-stars"></i> Smart Recommendation Concierge
                        </span>
                        <h4 class="modal-title fw-800 text-white mb-0" id="safariMatcherModalLabel">
                            Safari Matcher <span style="background: linear-gradient(135deg, #F69044, #FBBF24); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">AI</span>
                        </h4>
                    </div>
                    <button type="button" class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center shadow-none border-0" data-bs-dismiss="modal" aria-label="Close" style="background: rgba(255, 255, 255, 0.08); width: 38px; height: 38px;">
                        <i class="bi bi-x-lg fs-6 text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="px-4 pt-3 pb-2">
                <div class="d-flex align-items-center justify-content-between text-white-50 small mb-2 font-monospace" style="font-size: 0.78rem;">
                    <span id="matcherStepIndicator">Step 1 of 3: Travel Party</span>
                    <span id="matcherProgressPercent">33%</span>
                </div>
                <div class="progress" style="height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 999px;">
                    <div class="progress-bar transition-all" id="matcherProgressBar" role="progressbar" style="width: 33%; background: linear-gradient(90deg, #F69044, #FBBF24); border-radius: 999px;"></div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 position-relative z-1" style="min-height: 420px;">
                
                <!-- Step 1: Group Style -->
                <div class="matcher-step-view" id="matcherStep1">
                    <h5 class="fw-bold text-white mb-1">Who is embarking on this desert adventure with you?</h5>
                    <p class="text-white-50 small mb-4">Choose your party style so we can optimize dune pacing, vehicle safety, and camp seating.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="1" data-val="family" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">👨‍👩‍👧‍👦</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Family with Kids & Seniors</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Moderate dune bashing, gentle camel rides, child-friendly entertainment & mild live BBQ buffet.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="1" data-val="adventure" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">⚡</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Thrill & Adrenaline Seekers</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            High red dunes (Lahbab), high-powered 400cc Quad Bikes, 1000cc Buggies & sandboarding.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="1" data-val="luxury" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">👑</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">VIP Luxury & Couples / Romance</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Private 4x4 Land Cruiser pickup, reserved elevated VIP table with private waiter service & sunset views.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="1" data-val="budget" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">💰</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Best Value / Solo Explorer</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Dubai's #1 rated evening desert safari with full 5-star live BBQ and 3 cultural shows at lowest rate.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Time & Vibe -->
                <div class="matcher-step-view d-none" id="matcherStep2">
                    <h5 class="fw-bold text-white mb-1">What time of day & desert atmosphere do you prefer?</h5>
                    <p class="text-white-50 small mb-4">Select the timing that best fits your Dubai itinerary and climate preference.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="2" data-val="evening" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🌇</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Sunset Evening Safari (Classic)</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            2:30 PM - 9:30 PM. Red dunes sunset photography, Bedouin camp, 5-star live BBQ buffet & fire show.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="2" data-val="morning" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🌅</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Crisp Morning Safari (Cooler)</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            8:00 AM - 12:30 PM. Crisp morning air, stunning sunrise dunes, quad biking track, back before heat.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="2" data-val="overnight" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🌌</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Overnight Stargazing Glamping</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Full evening safari + private overnight Bedouin tent, bonfire stargazing & fresh sunrise breakfast.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="2" data-val="cruise" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">⛵</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Dubai Marina Luxury Dhow Cruise</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            7:30 PM - 10:30 PM. Gliding past illuminated skyscrapers with 5-star international buffet & live music.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Must-Haves -->
                <div class="matcher-step-view d-none" id="matcherStep3">
                    <h5 class="fw-bold text-white mb-1">What is your #1 must-have experience or perk?</h5>
                    <p class="text-white-50 small mb-4">Choose the feature you most look forward to experiencing.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="3" data-val="quad_buggy" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🏎️</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Quad Biking or Dune Buggy Drive</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Guaranteed 350cc/400cc ATV quad bike or 1000cc Can-Am Turbo buggy self-drive in open red dunes.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="3" data-val="vip_service" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🍷</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">VIP Raised Table & Private Waiter</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Skip all buffet lines with personalized table-side service, prime stage view & complimentary shisha.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="3" data-val="private_car" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🚙</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">Private 4x4 Vehicle (No Sharing)</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Exclusive 7-seater Land Cruiser for your party, flexible hotel pickup time & custom dune drive intensity.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="matcher-option-card p-3 rounded-4 cursor-pointer transition-all h-100" data-step="3" data-val="all_inclusive" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="fs-2 text-warning flex-shrink-0">🌟</div>
                                    <div>
                                        <div class="fw-bold text-white fs-6 mb-1">All-Inclusive Standard Package</div>
                                        <div class="text-white-50 small" style="font-size: 0.8rem; line-height: 1.4;">
                                            Dune bashing, camel ride, sandboarding, unlimited soft drinks, 5-star live BBQ & 3 cultural shows.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step Loading State (Simulated AI Engine) -->
                <div class="matcher-step-view d-none text-center py-5" id="matcherStepLoading">
                    <div class="spinner-border text-warning mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Calculating...</span>
                    </div>
                    <h5 class="fw-bold text-white mb-2">Analyzing 15+ Safari Options...</h5>
                    <p class="text-white-50 small mb-0">Cross-referencing your travel style, preferred timing, and inclusions with verified DTCM inventory.</p>
                </div>

                <!-- Step 4: Matched Result Screen -->
                <div class="matcher-step-view d-none" id="matcherStepResult">
                    <div class="text-center mb-3">
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold bg-success text-white mb-2 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-patch-check-fill"></i> 99% Best Match Found
                        </span>
                        <h4 class="fw-bold text-white mb-1">We Found Your Perfect Dubai Adventure!</h4>
                        <p class="text-white-50 small mb-0">Handpicked based on your party preferences and timing.</p>
                    </div>

                    <!-- Match Tour Card -->
                    <div class="card border-0 rounded-4 p-3 mb-3" style="background: rgba(30, 41, 59, 0.9); border: 1px solid rgba(246, 144, 68, 0.3) !important;">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-sm-4 col-md-3">
                                <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio: 4/3;">
                                    <img id="matcherTourThumb" src="{{ asset('images/desert-safari-poster.avif') }}" alt="Matched Tour" class="w-100 h-100 object-fit-cover">
                                    <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark fw-bold rounded-pill" style="font-size: 0.7rem;">Top Match</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-8 col-md-9">
                                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-1">
                                    <h5 class="fw-bold text-white mb-0" id="matcherTourTitle">Evening Desert Safari</h5>
                                    <div class="text-end">
                                        <div class="fw-800 text-warning fs-5" id="matcherTourPrice" data-aed="150">AED 150</div>
                                        <small class="text-white-50" style="font-size: 11px;">per person</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 text-white-50 small mb-2" style="font-size: 0.8rem;">
                                    <span><i class="bi bi-star-fill text-warning me-1"></i>4.9/5 (1,200+ Reviews)</span>
                                    <span>•</span>
                                    <span id="matcherTourDuration"><i class="bi bi-clock me-1"></i>6-7 Hours</span>
                                </div>

                                <!-- Personalized Reasons Bullets -->
                                <div class="p-2 rounded-3 mb-1" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05);">
                                    <div class="small text-white-50 fw-bold mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Why This Matches You:</div>
                                    <ul class="list-unstyled mb-0 small text-white" id="matcherReasonsList" style="font-size: 0.8rem; line-height: 1.5;">
                                        <li><i class="bi bi-check2 text-success me-1"></i> Matched your preferred party pace.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gamified Reward Box: MATCH5 Discount Certificate -->
                    <div class="p-3 rounded-4 mb-3 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(246, 144, 68, 0.22), rgba(251, 191, 36, 0.12)); border: 1.5px dashed #F69044;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-gift-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white small">Congratulations! 5% Matcher Promo Unlocked</div>
                                    <div class="text-white-50" style="font-size: 0.78rem;">Automatically applied when you proceed to booking.</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-monospace fw-bold fs-6 px-3 py-1 rounded-3 bg-dark text-warning border border-warning border-opacity-50">MATCH5</span>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 small" id="matcherCopyCodeBtn" title="Copy promo code">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Actions -->
                    <div class="row g-2">
                        <div class="col-12 col-md-7">
                            <button type="button" class="btn btn-desert-animated w-100 py-3 rounded-pill fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2" id="matcherBookNowBtn">
                                <span>Book This Tour with 5% OFF</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        <div class="col-12 col-md-5">
                            <a href="#" target="_blank" rel="noopener" class="btn btn-whatsapp-animated w-100 py-3 rounded-pill fw-bold fs-6 d-flex align-items-center justify-content-center gap-2" id="matcherWhatsAppBtn">
                                <i class="bi bi-whatsapp"></i>
                                <span>Ask Concierge</span>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 px-1">
                        <button type="button" class="btn btn-link text-white-50 text-decoration-none small p-0" id="matcherResetBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Retake Quiz
                        </button>
                        <a href="#" id="matcherCompareLink" class="btn btn-link text-warning text-decoration-none small p-0">
                            <i class="bi bi-shuffle me-1"></i> Compare with other tours
                        </a>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Controls (Back / Step Indicators) -->
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between" id="matcherFooterNav">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 text-white-50 border-secondary d-none" id="matcherBackBtn">
                    <i class="bi bi-chevron-left me-1"></i> Back
                </button>
                <div class="ms-auto small text-white-50 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-warning"></i>
                    <span>DTCM Licensed #1430583 • No Credit Card Required to Inquire</span>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.matcher-option-card {
    transition: all 0.25s ease-in-out;
}
.matcher-option-card:hover {
    transform: translateY(-2px);
    border-color: #F69044 !important;
    background: rgba(30, 41, 59, 0.95) !important;
    box-shadow: 0 10px 25px -5px rgba(246, 144, 68, 0.2);
}
.matcher-option-card.selected {
    border-color: #F69044 !important;
    background: rgba(246, 144, 68, 0.15) !important;
    box-shadow: 0 0 0 2px rgba(246, 144, 68, 0.4);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const matcherModalEl = document.getElementById('safariMatcherModal');
    if (!matcherModalEl) return;

    let currentStep = 1;
    const answers = {
        group: null,
        vibe: null,
        perk: null
    };

    let matchedTourData = null;

    const step1El = document.getElementById('matcherStep1');
    const step2El = document.getElementById('matcherStep2');
    const step3El = document.getElementById('matcherStep3');
    const stepLoadingEl = document.getElementById('matcherStepLoading');
    const stepResultEl = document.getElementById('matcherStepResult');

    const progressBar = document.getElementById('matcherProgressBar');
    const progressPercent = document.getElementById('matcherProgressPercent');
    const stepIndicator = document.getElementById('matcherStepIndicator');
    const backBtn = document.getElementById('matcherBackBtn');
    const footerNav = document.getElementById('matcherFooterNav');
    const resetBtn = document.getElementById('matcherResetBtn');
    const copyCodeBtn = document.getElementById('matcherCopyCodeBtn');
    const bookNowBtn = document.getElementById('matcherBookNowBtn');
    const whatsAppBtn = document.getElementById('matcherWhatsAppBtn');
    const compareLink = document.getElementById('matcherCompareLink');

    // Card selection handler
    document.querySelectorAll('.matcher-option-card').forEach(card => {
        card.addEventListener('click', function() {
            const step = parseInt(this.getAttribute('data-step'));
            const val = this.getAttribute('data-val');

            // Visual active highlight
            document.querySelectorAll(`.matcher-option-card[data-step="${step}"]`).forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            if (step === 1) {
                answers.group = val;
                setTimeout(() => goToStep(2), 200);
            } else if (step === 2) {
                answers.vibe = val;
                setTimeout(() => goToStep(3), 200);
            } else if (step === 3) {
                answers.perk = val;
                setTimeout(() => calculateAndShowResult(), 200);
            }
        });
    });

    function goToStep(step) {
        currentStep = step;
        step1El.classList.add('d-none');
        step2El.classList.add('d-none');
        step3El.classList.add('d-none');
        stepLoadingEl.classList.add('d-none');
        stepResultEl.classList.add('d-none');
        footerNav.classList.remove('d-none');

        if (step === 1) {
            step1El.classList.remove('d-none');
            progressBar.style.width = '33%';
            progressPercent.innerText = '33%';
            stepIndicator.innerText = 'Step 1 of 3: Travel Party';
            backBtn.classList.add('d-none');
        } else if (step === 2) {
            step2El.classList.remove('d-none');
            progressBar.style.width = '66%';
            progressPercent.innerText = '66%';
            stepIndicator.innerText = 'Step 2 of 3: Time & Vibe';
            backBtn.classList.remove('d-none');
        } else if (step === 3) {
            step3El.classList.remove('d-none');
            progressBar.style.width = '90%';
            progressPercent.innerText = '90%';
            stepIndicator.innerText = 'Step 3 of 3: Must-Have Perks';
            backBtn.classList.remove('d-none');
        }
    }

    if (backBtn) {
        backBtn.addEventListener('click', function() {
            if (currentStep === 2) goToStep(1);
            else if (currentStep === 3) goToStep(2);
        });
    }

    function calculateAndShowResult() {
        step3El.classList.add('d-none');
        stepLoadingEl.classList.remove('d-none');
        backBtn.classList.add('d-none');
        progressBar.style.width = '100%';
        progressPercent.innerText = '100%';
        stepIndicator.innerText = 'AI Analysis: Matching Safari';

        setTimeout(() => {
            stepLoadingEl.classList.add('d-none');
            stepResultEl.classList.remove('d-none');
            footerNav.classList.add('d-none');

            // Resolve tour catalog from compare data or fallback
            let catalog = [];
            const compareScript = document.getElementById('dunesCompareTourData');
            if (compareScript) {
                try {
                    catalog = JSON.parse(compareScript.textContent || '[]');
                } catch (e) {
                    catalog = [];
                }
            }

            // Fallback catalog if compare script was not populated
            if (!catalog || catalog.length === 0) {
                catalog = [
                    { id: '1', name: 'Standard Evening Desert Safari', slug: 'standard-evening-desert-safari', min_price: 120, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '2', name: 'VIP Luxury Desert Safari with Table Service', slug: 'vip-desert-safari', min_price: 250, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '3', name: 'Evening Desert Safari with Quad Biking', slug: 'quad-bike-desert-safari', min_price: 180, rating: 4.9, duration: '6-7 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '4', name: 'Morning Desert Safari with Camel Ride', slug: 'morning-desert-safari', min_price: 130, rating: 4.8, duration: '4 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '5', name: 'Overnight Desert Safari & Camping', slug: 'overnight-desert-safari', min_price: 350, rating: 4.9, duration: '18 Hours', thumb: '/images/desert-safari-poster.avif' },
                    { id: '6', name: 'Dubai Marina Luxury Dhow Cruise Dinner', slug: 'marina-dhow-cruise', min_price: 150, rating: 4.8, duration: '3 Hours', thumb: '/images/desert-safari-poster.avif' }
                ];
            }

            // Scoring system
            let bestTour = catalog[0];
            let highestScore = -999;

            catalog.forEach(t => {
                let score = 0;
                const nameLow = (t.name || '').toLowerCase();
                const slugLow = (t.slug || '').toLowerCase();

                // Timing matches
                if (answers.vibe === 'morning' && (slugLow.includes('morning') || nameLow.includes('morning'))) score += 50;
                if (answers.vibe === 'overnight' && (slugLow.includes('overnight') || nameLow.includes('overnight'))) score += 50;
                if (answers.vibe === 'cruise' && (slugLow.includes('cruise') || slugLow.includes('dhow') || nameLow.includes('cruise'))) score += 60;
                if (answers.vibe === 'evening' && !slugLow.includes('morning') && !slugLow.includes('overnight') && !slugLow.includes('cruise')) score += 30;

                // Perk matches
                if (answers.perk === 'quad_buggy' && (slugLow.includes('quad') || slugLow.includes('buggy') || nameLow.includes('quad') || nameLow.includes('buggy'))) score += 45;
                if (answers.perk === 'vip_service' && (slugLow.includes('vip') || nameLow.includes('vip') || slugLow.includes('luxury'))) score += 45;
                if (answers.perk === 'private_car' && (slugLow.includes('private') || slugLow.includes('vip'))) score += 35;
                if (answers.perk === 'all_inclusive' && (slugLow.includes('evening') || slugLow.includes('standard') || slugLow.includes('red-dunes'))) score += 30;

                // Group style matches
                if (answers.group === 'adventure' && (slugLow.includes('quad') || slugLow.includes('buggy') || slugLow.includes('red-dunes'))) score += 30;
                if (answers.group === 'luxury' && (slugLow.includes('vip') || slugLow.includes('private'))) score += 30;
                if (answers.group === 'family' && (slugLow.includes('standard') || slugLow.includes('evening') || slugLow.includes('morning'))) score += 25;
                if (answers.group === 'budget') {
                    if (t.min_price && t.min_price < 150) score += 25;
                    if (t.is_bestseller) score += 15;
                }

                if (score > highestScore) {
                    highestScore = score;
                    bestTour = t;
                }
            });

            matchedTourData = bestTour;

            // Render result card
            document.getElementById('matcherTourTitle').innerText = bestTour.name;
            document.getElementById('matcherTourDuration').innerHTML = `<i class="bi bi-clock me-1"></i>${bestTour.duration || '6-7 Hours'}`;
            
            const priceEl = document.getElementById('matcherTourPrice');
            const minAed = bestTour.min_price || 120;
            priceEl.setAttribute('data-aed', minAed);
            priceEl.innerText = `AED ${minAed}`;

            // Trigger currency recalculation if currency switcher is active
            if (window.DunesApp && typeof window.DunesApp.updatePrices === 'function') {
                window.DunesApp.updatePrices();
            }

            const thumbEl = document.getElementById('matcherTourThumb');
            if (thumbEl && bestTour.thumb) {
                thumbEl.src = bestTour.thumb;
            }

            // Generate personalized reasons
            const reasonsList = document.getElementById('matcherReasonsList');
            reasonsList.innerHTML = '';

            const bullets = [];
            if (answers.group === 'family') bullets.push('Optimized for families: gentle pacing, spacious camp & child-friendly activities.');
            else if (answers.group === 'adventure') bullets.push('Adrenaline-packed: extreme red dunes bashing & optional ATV self-drive.');
            else if (answers.group === 'luxury') bullets.push('VIP experience: premium comfort, luxury 4x4 & priority table hospitality.');
            else bullets.push('Best value: Dubai\'s highest-rated classic experience at guaranteed best rates.');

            if (answers.vibe === 'morning') bullets.push('Crisp morning timing: cooler desert temperatures and breathtaking sunrise dunes.');
            else if (answers.vibe === 'overnight') bullets.push('Magical overnight stay: authentic Bedouin tent, stargazing & sunrise breakfast.');
            else if (answers.vibe === 'cruise') bullets.push('Dubai Marina skyline: tranquil waters, live shows & 5-star international buffet.');
            else bullets.push('Golden hour sunset: prime dune photography & evening cultural live performances.');

            if (answers.perk === 'quad_buggy') bullets.push('Includes your desired high-power Quad / Buggy desert track session.');
            else if (answers.perk === 'vip_service') bullets.push('Includes exclusive VIP table service with private dedicated waiter.');
            else if (answers.perk === 'private_car') bullets.push('Available with private door-to-door Land Cruiser transfers.');
            else bullets.push('All-inclusive: dune bashing, camel riding, sandboarding & 5-star live BBQ dinner.');

            bullets.forEach(b => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="bi bi-check2 text-success me-1"></i> ${b}`;
                reasonsList.appendChild(li);
            });

            // Set WhatsApp link
            const waText = encodeURIComponent(`Hi Dunes Discovery! Your Safari Matcher AI recommended "${bestTour.name}" for my party with code MATCH5. Could you please share availability and details?`);
            whatsAppBtn.href = `https://wa.me/{{ preg_replace('/[^0-9]/','',$waPhone) }}?text=${waText}`;

        }, 600);
    }

    // Retake quiz
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            answers.group = null;
            answers.vibe = null;
            answers.perk = null;
            document.querySelectorAll('.matcher-option-card').forEach(c => c.classList.remove('selected'));
            goToStep(1);
        });
    }

    // Copy MATCH5 Code
    if (copyCodeBtn) {
        copyCodeBtn.addEventListener('click', function() {
            navigator.clipboard.writeText('MATCH5').then(() => {
                const orig = copyCodeBtn.innerHTML;
                copyCodeBtn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                setTimeout(() => { copyCodeBtn.innerHTML = orig; }, 2000);
            });
        });
    }

    // Compare link handler
    if (compareLink) {
        compareLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (matchedTourData && typeof window.addToCompare === 'function') {
                window.addToCompare(matchedTourData.id);
            }
            const modal = bootstrap.Modal.getInstance(matcherModalEl);
            if (modal) modal.hide();

            setTimeout(() => {
                const compareDrawerEl = document.getElementById('compareDrawer');
                if (compareDrawerEl && typeof bootstrap !== 'undefined') {
                    const bsDrawer = bootstrap.Offcanvas.getOrCreateInstance(compareDrawerEl);
                    bsDrawer.show();
                }
            }, 350);
        });
    }

    // Book Now button: closes matcher, opens booking modal with MATCH5 & matched tour pre-selected!
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(matcherModalEl);
            if (modal) modal.hide();

            setTimeout(() => {
                const bookingModalEl = document.getElementById('bookingModal');
                if (!bookingModalEl) return;

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bModal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                    bModal.show();
                }

                // Pre-select tour if matched
                if (matchedTourData && matchedTourData.id) {
                    const tourSelect = document.getElementById('bookingTour');
                    if (tourSelect) {
                        tourSelect.value = matchedTourData.id;
                        tourSelect.dispatchEvent(new Event('change'));
                    }
                }

                // Pre-load MATCH5 code
                const promoInput = document.getElementById('bookingPromoCode');
                if (promoInput) {
                    promoInput.value = 'MATCH5';
                }

                // Trigger promo validation
                if (typeof window.validateCurrentPromo === 'function') {
                    setTimeout(() => window.validateCurrentPromo(), 400);
                }
            }, 350);
        });
    }

    // Expose global launcher for easy triggering from buttons anywhere
    window.openSafariMatcher = function() {
        const modal = bootstrap.Modal.getOrCreateInstance(matcherModalEl);
        modal.show();
    };
});
</script>
