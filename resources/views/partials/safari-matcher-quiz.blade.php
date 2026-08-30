<section class="py-5 bg-light position-relative overflow-hidden" id="safariMatcherSection">
    <div class="container py-lg-4">
        <div class="row justify-content-center text-center mb-4">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">
                    <i class="bi bi-magic me-1"></i> Interactive Safari Matcher
                </span>
                <h2 class="h2 fw-800 text-dark">Find Your Perfect Dubai Safari in 3 Clicks</h2>
                <p class="text-muted small">Not sure which tour to pick? Answer 3 quick questions and our AI matching engine will recommend the exact experience tailored to your trip.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="card border-0 shadow-lg rounded-5 overflow-hidden bg-white p-4 p-md-5">
                    
                    <!-- Step Indicator Progress Bar -->
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" id="quizStepNum" style="width: 28px; height: 28px;">1</span>
                            <span class="fw-bold text-dark small" id="quizStepTitle">Who is traveling?</span>
                        </div>
                        <div class="text-muted small fw-bold" id="quizProgressText">Step 1 of 3</div>
                    </div>

                    <!-- Step 1: Traveling Group -->
                    <div class="quiz-step-panel" id="quizStep1">
                        <h4 class="fw-800 text-dark mb-3 text-center">Who are you traveling with?</h4>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="couples">
                                    <div class="fs-1 mb-2">👫</div>
                                    <h6 class="fw-bold text-dark mb-1">Couples & Romantic</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Sunset dunes, private tables, and stargazing</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="family">
                                    <div class="fs-1 mb-2">👨‍👩‍👧‍👦</div>
                                    <h6 class="fw-bold text-dark mb-1">Family with Kids</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Gentle dunes, camel rides, and family BBQ show</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="friends">
                                    <div class="fs-1 mb-2">👥</div>
                                    <h6 class="fw-bold text-dark mb-1">Friends & Squad</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Red dune bashing, quad bikes, and sandboarding</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="1" data-val="solo">
                                    <div class="fs-1 mb-2">👤</div>
                                    <h6 class="fw-bold text-dark mb-1">Solo Explorer</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Shared group safari, photography, and culture</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Time Preference -->
                    <div class="quiz-step-panel d-none" id="quizStep2">
                        <h4 class="fw-800 text-dark mb-3 text-center">What time of day do you prefer?</h4>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="morning">
                                    <div class="fs-1 mb-2">🌅</div>
                                    <h6 class="fw-bold text-dark mb-1">Morning Safari</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">8:00 AM – 12:00 PM • Crisp breeze & sunrise</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="evening">
                                    <div class="fs-1 mb-2">🌇</div>
                                    <h6 class="fw-bold text-dark mb-1">Evening & Sunset</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">3:00 PM – 9:30 PM • Sunset, 5-Star BBQ & Fire Shows</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card quiz-choice-card h-100 p-3 rounded-4 border text-center cursor-pointer" data-step="2" data-val="overnight">
                                    <div class="fs-1 mb-2">🌌</div>
                                    <h6 class="fw-bold text-dark mb-1">Overnight Safari</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Camp under desert stars + fresh morning breakfast</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Adventure vs Luxury Style -->
                    <div class="quiz-step-panel d-none" id="quizStep3">
                        <h4 class="fw-800 text-dark mb-3 text-center">Choose your adventure intensity:</h4>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-4 rounded-4 border text-center cursor-pointer" data-step="3" data-val="thrill">
                                    <div class="fs-1 mb-2">🏎️</div>
                                    <h6 class="fw-bold text-dark mb-1">High Adrenaline Thrill</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Extreme Red Dune Bashing + 400cc Quad Biking + Sandboarding</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card quiz-choice-card h-100 p-4 rounded-4 border text-center cursor-pointer" data-step="3" data-val="luxury">
                                    <div class="fs-1 mb-2">👑</div>
                                    <h6 class="fw-bold text-dark mb-1">VIP Luxury & Comfort</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Private 4x4, VIP reserved dining table & silver service buffet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Quiz Recommendation Result Card -->
                    <div class="quiz-step-panel d-none" id="quizResult">
                        <div class="text-center mb-3">
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold">🎯 99% Match For You</span>
                            <h3 class="h4 fw-800 text-dark mt-2 mb-1" id="quizMatchedTitle">Evening Red Dune Desert Safari</h3>
                            <p class="text-muted small" id="quizMatchedTagline">Dubai's #1 Rated Desert Adventure with Live 5-Star Camp Shows & BBQ</p>
                        </div>

                        <div class="card bg-light border-0 rounded-4 p-3 p-md-4 mb-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-7">
                                    <ul class="list-unstyled mb-0 small text-dark d-flex flex-column gap-2" id="quizMatchedFeatures">
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Includes 45-min Red Dune Bashing in Lahbab Desert</li>
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Live Fire Show, Belly Dance & Tanoura Spectacle</li>
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Lavish 5-Star BBQ Dinner (Veg & Non-Veg)</li>
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Free Sandboarding, Camel Ride & Arabic Costume Photos</li>
                                    </ul>
                                </div>
                                <div class="col-md-5 text-md-end text-center border-start-md">
                                    <div class="small text-muted text-uppercase fw-bold">From Only</div>
                                    <div class="h2 fw-800 text-primary mb-2" id="quizMatchedPrice">AED 130</div>
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