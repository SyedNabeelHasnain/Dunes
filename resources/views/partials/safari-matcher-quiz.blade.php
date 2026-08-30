<section class="py-5 bg-light position-relative overflow-hidden" id="safariMatcherSection">
    <div class="container py-lg-4">
        <div class="row justify-content-center text-center mb-4">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">
                    <i class="bi bi-compass me-1"></i> Tour Recommender
                </span>
                <h2 class="h2 fw-800 text-dark">Find Your Perfect Dubai Experience in 3 Clicks</h2>
                <p class="text-muted small">Not sure which tour to pick? Answer 3 quick questions and our recommendation engine will find the exact experience tailored to your trip.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="card border-0 shadow-lg rounded-5 overflow-hidden bg-white p-4 p-md-5">
                    
                    <!-- Step Indicator Progress Bar -->
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" id="quizStepNum" style="width: 28px; height: 28px;">1</span>
                            <span class="fw-bold text-dark small" id="quizStepTitle">Choose Experience Type</span>
                        </div>
                        <div class="text-muted small fw-bold" id="quizProgressText">Step 1 of 3</div>
                    </div>

                    <!-- Step 1: Adventure & Tour Category -->
                    <div class="quiz-step-panel" id="quizStep1">
                        <h4 class="fw-800 text-dark mb-3 text-center">What type of Dubai experience are you looking for?</h4>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="desert">
                                    <div class="fs-1 mb-2">🏜️</div>
                                    <h6 class="fw-bold text-dark mb-1">Desert Safari & Red Dunes</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">4x4 Dune Bashing, Camel Rides, Live Camp Shows & BBQ Dinner</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="city">
                                    <div class="fs-1 mb-2">🏙️</div>
                                    <h6 class="fw-bold text-dark mb-1">City Sightseeing & Landmarks</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Dubai & Abu Dhabi iconic tours, Burj Khalifa & Grand Mosque</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="water">
                                    <div class="fs-1 mb-2">🚢</div>
                                    <h6 class="fw-bold text-dark mb-1">Marina Dhow & Luxury Cruise</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Dubai Marina dinner cruise with skyline views & entertainment</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="quad_buggy">
                                    <div class="fs-1 mb-2">🏎️</div>
                                    <h6 class="fw-bold text-dark mb-1">Quad Bike & Dune Buggy Rentals</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Self-drive 1000cc Buggy & 400cc ATV adrenaline in open red dunes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Time Preference & Timing -->
                    <div class="quiz-step-panel d-none" id="quizStep2">
                        <h4 class="fw-800 text-dark mb-3 text-center">What time of day do you prefer?</h4>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="morning">
                                    <div class="fs-1 mb-2">🌅</div>
                                    <h6 class="fw-bold text-dark mb-1">Morning Experience</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">8:00 AM – 12:00 PM • Crisp breeze, cool weather & sightseeing</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="evening">
                                    <div class="fs-1 mb-2">🌇</div>
                                    <h6 class="fw-bold text-dark mb-1">Evening & Sunset</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">3:00 PM – 9:30 PM • Sunset, 5-Star Buffet & Live Shows</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="overnight">
                                    <div class="fs-1 mb-2">🌌</div>
                                    <h6 class="fw-bold text-dark mb-1">Overnight Stay</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Camp under desert stars with campfire & morning breakfast</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Adventure vs Luxury Style -->
                    <div class="quiz-step-panel d-none" id="quizStep3">
                        <h4 class="fw-800 text-dark mb-3 text-center">What is your group style & pace?</h4>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="3" data-val="family">
                                    <div class="fs-1 mb-2">👨‍👩‍👧‍👦</div>
                                    <h6 class="fw-bold text-dark mb-1">Family & Friends</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Relaxed sightseeing, great photo stops & family-friendly fun</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="3" data-val="thrill">
                                    <div class="fs-1 mb-2">🏎️</div>
                                    <h6 class="fw-bold text-dark mb-1">Thrill & Adrenaline</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Dune bashing, quad biking, sandboarding & high excitement</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="3" data-val="luxury">
                                    <div class="fs-1 mb-2">👑</div>
                                    <h6 class="fw-bold text-dark mb-1">VIP Luxury & Romance</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Private 4x4, reserved VIP dining & premium comfort</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Quiz Recommendation Result Card -->
                    <div class="quiz-step-panel d-none" id="quizResult">
                        <div class="text-center mb-3">
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold">🎯 99% Match From Live Catalog</span>
                            <h3 class="h4 fw-800 text-dark mt-2 mb-1" id="quizMatchedTitle">Evening Desert Safari Dubai</h3>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                <span class="badge bg-light text-primary border rounded-pill" id="quizMatchedCategory">Desert Safari</span>
                                <span class="small text-muted" id="quizMatchedMeta"><i class="bi bi-clock me-1"></i>6 Hours • ⭐ 4.9 (1,200+ Reviews)</span>
                            </div>
                            <p class="text-muted small mx-auto" style="max-width: 600px;" id="quizMatchedDesc">Top-rated Dubai tour experience.</p>
                        </div>

                        <div class="card bg-light border-0 rounded-4 p-3 p-md-4 mb-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-shield-check text-success fs-5"></i>
                                        <span class="fw-bold text-dark small">Official DTCM-Licensed Operator & 100% Guaranteed Spot</span>
                                    </div>
                                    <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-1" id="quizMatchedHighlights">
                                        <li><i class="bi bi-check2 text-success me-2 fw-bold"></i>Door-to-door hotel pick & drop included</li>
                                        <li><i class="bi bi-check2 text-success me-2 fw-bold"></i>24-Hour free cancellation available</li>
                                        <li><i class="bi bi-check2 text-success me-2 fw-bold"></i>No hidden charges • Instant WhatsApp support</li>
                                    </ul>
                                </div>
                                <div class="col-md-5 text-md-end text-center border-start-md">
                                    <div class="small text-muted text-uppercase fw-bold">Starting From</div>
                                    <div class="h2 fw-800 text-primary mb-2" id="quizMatchedPrice">AED 79</div>
                                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-800 text-white shadow-sm w-100" id="quizBookBtn">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Book This Tour
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-link text-muted small text-decoration-none" id="quizResetBtn">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Retake Matcher Quiz
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Dataset from Active Database Catalog -->
<script id="activeToursDataset" type="application/json">
{!! json_encode($allActiveTours ?? []) !!}
</script>

<style>
.quiz-choice-card {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    border-color: #E2E8F0 !important;
}
.quiz-choice-card:hover {
    border-color: #F58F43 !important;
    background: #FFF7ED;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(245, 143, 67, 0.12);
}
@media (min-width: 768px) {
    .border-start-md {
        border-left: 1px solid #E2E8F0;
    }
}
</style>