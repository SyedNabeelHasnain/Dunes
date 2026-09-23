@extends('layouts.admin')

@section('title', 'Promotions & Campaign Triggers Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Top Header & Breadcrumbs -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}" class="text-decoration-none text-muted">Coupons & Promos</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Campaign Triggers & Promotions</li>
                </ol>
            </nav>
            <h1 class="h3 fw-800 text-dark mb-0">Promotions & Campaign Triggers Hub</h1>
            <p class="text-muted small mb-0">Centralized management for first-time visitor welcome offers (25%), top announcement banner, and Safari Match Concierge promo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-bold">
                <i class="bi bi-ticket-perforated me-1"></i> View All Promo Codes
            </a>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-light rounded-pill px-3 fw-bold border">
                <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live Website
            </a>
        </div>
    </div>

    <!-- Unified Section Navigation Tabs -->
    <div class="d-flex gap-2 mb-4 border-bottom pb-3">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-light rounded-pill px-3 py-1.5 fw-bold small border text-muted">
            <i class="bi bi-ticket-perforated me-1 text-primary"></i> All Promo Codes
        </a>
        <a href="{{ route('admin.coupons.popup-settings') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-bold small shadow-sm">
            <i class="bi bi-megaphone-fill me-1 text-warning"></i> Campaign Triggers & Banners (25% & Concierge)
        </a>
    </div>

    <form action="{{ route('admin.coupons.popup-settings.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            
            <!-- Left Column: Settings Configuration -->
            <div class="col-lg-7">
                
                <!-- Card 1: First-Time Visitor 25% Offer Modal -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-gift-fill text-primary fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">First-Time Visitor Offer Modal (25% OFF)</h5>
                                <small class="text-muted">High-converting automated lead capture popup</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="welcome_popup_active" name="welcome_popup_active" value="1" {{ (($settings['welcome_popup_active'] ?? '1') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="welcome_popup_active">Popup Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_discount">Discount Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="100" class="form-control fw-bold" id="welcome_popup_discount" name="welcome_popup_discount" value="{{ ($settings['welcome_popup_discount'] ?? '25') }}" required>
                                    <span class="input-group-text bg-light fw-bold text-primary">% OFF</span>
                                </div>
                                <small class="text-muted d-block mt-1">Percentage discount applied to first booking.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_timer_mins">Session Urgency Timer</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="120" class="form-control fw-bold" id="welcome_popup_timer_mins" name="welcome_popup_timer_mins" value="{{ ($settings['welcome_popup_timer_mins'] ?? '15') }}" required>
                                    <span class="input-group-text bg-light fw-bold">Minutes</span>
                                </div>
                                <small class="text-muted d-block mt-1">Urgency countdown ticker duration.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_delay_sec">Trigger Delay</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="60" class="form-control fw-bold" id="welcome_popup_delay_sec" name="welcome_popup_delay_sec" value="{{ ($settings['welcome_popup_delay_sec'] ?? '5') }}" required>
                                    <span class="input-group-text bg-light fw-bold">Seconds</span>
                                </div>
                                <small class="text-muted d-block mt-1">Time on page before popup displays automatically.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Smart Triggers</label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="welcome_popup_exit_trigger" name="welcome_popup_exit_trigger" value="1" {{ (($settings['welcome_popup_exit_trigger'] ?? '1') == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold text-dark" for="welcome_popup_exit_trigger">
                                            Desktop Exit-Intent (When cursor moves to close tab)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="welcome_popup_scroll_trigger" name="welcome_popup_scroll_trigger" value="1" {{ (($settings['welcome_popup_scroll_trigger'] ?? '1') == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold text-dark" for="welcome_popup_scroll_trigger">
                                            Scroll Depth (When visitor scrolls 35% of page)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_headline">Modal Headline</label>
                                <input type="text" class="form-control fw-bold" id="welcome_popup_headline" name="welcome_popup_headline" value="{{ ($settings['welcome_popup_headline'] ?? 'Unlock Exclusive 25% OFF') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_subheadline">Modal Subheadline</label>
                                <textarea class="form-control" rows="2" id="welcome_popup_subheadline" name="welcome_popup_subheadline">{{ ($settings['welcome_popup_subheadline'] ?? 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Top Announcement Promo Banner Bar -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-megaphone-fill text-warning fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Top Announcement Promo Banner (25% OFF)</h5>
                                <small class="text-muted">Sticky top notification bar with instant copyable coupon code</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="top_promo_banner_active" name="top_promo_banner_active" value="1" {{ (($settings['top_promo_banner_active'] ?? '1') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="top_promo_banner_active">Banner Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="top_promo_banner_text">Banner Announcement Text</label>
                                <input type="text" class="form-control fw-bold" id="top_promo_banner_text" name="top_promo_banner_text" value="{{ ($settings['top_promo_banner_text'] ?? 'Special Online Exclusive: Get 25% OFF on all Desert Safari Tours! • 100% Free 24h Cancellation') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="top_promo_banner_badge">Badge Pill Text</label>
                                <input type="text" class="form-control fw-bold" id="top_promo_banner_badge" name="top_promo_banner_badge" value="{{ ($settings['top_promo_banner_badge'] ?? 'Limited Time Offer') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="top_promo_banner_code">Featured Promo Code to Display</label>
                                <input type="text" class="form-control fw-bold font-monospace text-uppercase" id="top_promo_banner_code" name="top_promo_banner_code" value="{{ ($settings['top_promo_banner_code'] ?? 'DUNESWELCOME') }}">
                                <small class="text-muted d-block mt-1">Visitors can 1-click copy this code from top banner.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Safari Match Concierge Promo Controls -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-compass-fill text-warning fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Safari Match Concierge Promo Controls</h5>
                                <small class="text-muted">Interactive 3-step quiz recommendation reward code (MATCH5)</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="concierge_promo_active" name="concierge_promo_active" value="1" {{ (($settings['concierge_promo_active'] ?? '0') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="concierge_promo_active">Promo Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if(($settings['concierge_promo_active'] ?? '0') == '1')
                        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-3 small">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div><strong>Concierge Promo is Live:</strong> The 5% reward badge is shown across navbars, quiz completion screens, and automatically preloads code MATCH5 into the booking checkout.</div>
                        </div>
                        @else
                        <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-3 small">
                            <i class="bi bi-info-circle-fill fs-5 text-warning"></i>
                            <div><strong>Concierge Promo is Currently Deactivated:</strong> The Safari Match Concierge operates in <em>Pure Curation Mode</em>. Guests receive custom tour recommendations without discount badges or checkout promo auto-injection. Toggle the switch above whenever you wish to reactivate.</div>
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="concierge_promo_discount">Concierge Discount Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="100" class="form-control fw-bold" id="concierge_promo_discount" name="concierge_promo_discount" value="{{ ($settings['concierge_promo_discount'] ?? '5') }}" required>
                                    <span class="input-group-text bg-light fw-bold text-primary">% OFF</span>
                                </div>
                                <small class="text-muted d-block mt-1">Percentage discount granted upon quiz completion.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="concierge_promo_code">Concierge Promo Code</label>
                                <input type="text" class="form-control fw-bold font-monospace text-uppercase" id="concierge_promo_code" name="concierge_promo_code" value="{{ ($settings['concierge_promo_code'] ?? 'MATCH5') }}" required>
                                <small class="text-muted d-block mt-1">Database coupon code linked to this concierge incentive.</small>
                            </div>
                        </div>
                    </div>
                <!-- Card 4: Exit-Intent Cart Saver Modal Controls (SAVE5) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-door-closed-fill text-danger fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Exit-Intent Cart Saver Modal (5% OFF)</h5>
                                <small class="text-muted">Desktop exit-intent pop-up trigger with promo code SAVE5</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="exit_intent_promo_active" name="exit_intent_promo_active" value="1" {{ (($settings['exit_intent_promo_active'] ?? '0') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="exit_intent_promo_active">Popup Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if(($settings['exit_intent_promo_active'] ?? '0') == '1')
                        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-3 small">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div><strong>Exit-Intent Cart Saver is Live:</strong> The 5% exit-intent popup is active and will fire when visitors move cursor toward the browser tab/close area.</div>
                        </div>
                        @else
                        <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-3 small">
                            <i class="bi bi-shield-lock-fill fs-5 text-warning"></i>
                            <div><strong>Exit-Intent Cart Saver is Currently Deactivated:</strong> Only the 25% Welcome Offer modal is active for first-time visitors. No competing 5% popup will display on exit. Toggle the switch above whenever you wish to reactivate.</div>
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="exit_intent_promo_discount">Exit-Intent Discount Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="100" class="form-control fw-bold" id="exit_intent_promo_discount" name="exit_intent_promo_discount" value="{{ ($settings['exit_intent_promo_discount'] ?? '5') }}" required>
                                    <span class="input-group-text bg-light fw-bold text-primary">% OFF</span>
                                </div>
                                <small class="text-muted d-block mt-1">Percentage discount offered in exit popup.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="exit_intent_promo_code">Exit-Intent Promo Code</label>
                                <input type="text" class="form-control fw-bold font-monospace text-uppercase" id="exit_intent_promo_code" name="exit_intent_promo_code" value="{{ ($settings['exit_intent_promo_code'] ?? 'SAVE5') }}" required>
                                <small class="text-muted d-block mt-1">Database coupon code linked to exit-intent offer.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold fs-6 shadow">
                        <i class="bi bi-save me-2"></i> Save Promotion Settings
                    </button>
                </div>
            </div>

            <!-- Right Column: Live Mockup & Preview -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-eye-fill text-info me-2"></i> Live Visual Preview</h6>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Top Banner Preview -->
                        <div class="mb-4">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2">Top Banner (25% OFF):</span>
                            <div class="p-2 rounded-3 text-white text-center small fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: linear-gradient(90deg, #111827 0%, #1f2937 50%, #0f172a 100%); border-bottom: 2px solid #F58F43; font-size: 0.8rem;">
                                <span class="badge bg-warning text-dark font-monospace px-2 py-0.5" style="font-size: 0.68rem;">{{ ($settings['top_promo_banner_badge'] ?? 'Limited Time Offer') }}</span>
                                <span><i class="bi bi-tag-fill text-warning me-1"></i> Get 25% OFF</span>
                                <span class="badge bg-warning text-dark font-monospace px-2 py-1">{{ ($settings['top_promo_banner_code'] ?? 'DUNESWELCOME') }}</span>
                            </div>
                        </div>

                        <!-- Modal Card Preview -->
                        <div class="mb-4">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2">Welcome Modal (25% OFF):</span>
                            <div class="border rounded-4 p-4 shadow-sm bg-white position-relative overflow-hidden" style="border-color: rgba(245, 143, 67, 0.3) !important;">
                                <div class="position-absolute top-0 start-0 end-0" style="height: 4px; background: linear-gradient(90deg, #F58F43 0%, #d2a13b 100%);"></div>
                                
                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill bg-primary-subtle text-primary fw-bold text-uppercase mb-2" style="font-size: 0.65rem;">
                                    <i class="bi bi-gift-fill"></i> First-Time Guest Special
                                </div>

                                <h5 class="fw-800 text-dark lh-sm mb-2">{{ ($settings['welcome_popup_headline'] ?? 'Unlock Exclusive 25% OFF') }}</h5>
                                <p class="text-muted lh-sm mb-3" style="font-size: 0.8rem;">{{ ($settings['welcome_popup_subheadline'] ?? 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.') }}</p>

                                <div class="p-2 rounded-3 bg-light text-center border mb-3">
                                    <span class="text-muted fw-bold d-block" style="font-size: 0.65rem;">SESSION OFFER EXPIRES IN</span>
                                    <span class="fw-bold text-primary font-monospace fs-5">14:59</span>
                                </div>

                                <div class="d-grid gap-2">
                                    <input type="email" class="form-control form-control-sm text-center fw-bold" placeholder="name@example.com" disabled>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold" style="background: #F58F43; border: none;" disabled>
                                        Claim My 25% Discount &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Concierge Mode Preview -->
                        <div class="mb-3">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2">Safari Match Concierge Mode:</span>
                            @if(($settings['concierge_promo_active'] ?? '0') == '1')
                            <div class="p-3 rounded-4 bg-success-subtle border border-success border-opacity-25 text-dark">
                                <div class="d-flex align-items-center gap-2 fw-bold text-success mb-1">
                                    <i class="bi bi-check-circle-fill"></i> Promo Mode Active
                                </div>
                                <p class="small text-muted mb-0">Navbars and quiz completion screens advertise and auto-inject <strong>{{ ($settings['concierge_promo_discount'] ?? '5') }}% OFF ({{ ($settings['concierge_promo_code'] ?? 'MATCH5') }})</strong>.</p>
                            </div>
                            @else
                            <div class="p-3 rounded-4 bg-light border text-dark">
                                <div class="d-flex align-items-center gap-2 fw-bold text-secondary mb-1">
                                    <i class="bi bi-pause-circle-fill text-warning"></i> Pure Curation Mode (Promo Inactive)
                                </div>
                                <p class="small text-muted mb-0">The Concierge recommends tailored safaris without discount certificates or checkout code auto-injection.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Exit-Intent Cart Saver Preview -->
                        <div class="mb-3">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2">Exit-Intent Cart Saver Mode:</span>
                            @if(($settings['exit_intent_promo_active'] ?? '0') == '1')
                            <div class="p-3 rounded-4 bg-success-subtle border border-success border-opacity-25 text-dark">
                                <div class="d-flex align-items-center gap-2 fw-bold text-success mb-1">
                                    <i class="bi bi-check-circle-fill"></i> Popup Mode Active
                                </div>
                                <p class="small text-muted mb-0">Fires on cursor exit with <strong>{{ ($settings['exit_intent_promo_discount'] ?? '5') }}% OFF ({{ ($settings['exit_intent_promo_code'] ?? 'SAVE5') }})</strong>.</p>
                            </div>
                            @else
                            <div class="p-3 rounded-4 bg-light border text-dark">
                                <div class="d-flex align-items-center gap-2 fw-bold text-secondary mb-1">
                                    <i class="bi bi-slash-circle-fill text-warning"></i> Deactivated (Protected)
                                </div>
                                <p class="small text-muted mb-0">Exit-intent popup is suppressed. Only the premier 25% Welcome Offer modal is presented.</p>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Conversion Tips:</h6>
                            <ul class="text-muted small mb-0 ps-3 lh-base">
                                <li>The 25% First-Time Visitor offer provides maximum incentive for new travelers.</li>
                                <li>The Safari Match Concierge provides 1-on-1 advisor curation without diluting margins when promo is off.</li>
                                <li>All coupon redemptions and leads are tracked in real-time in the admin analytics.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
