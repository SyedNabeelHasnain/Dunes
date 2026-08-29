@extends('layouts.admin')

@section('title', 'Welcome Offer Popup & Banner Settings')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Top Header & Breadcrumbs -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}" class="text-decoration-none text-muted">Coupons & Promos</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Welcome Popup & Banner</li>
                </ol>
            </nav>
            <h1 class="h3 fw-800 text-dark mb-0">Welcome Offer Popup & Top Banner Settings</h1>
            <p class="text-muted small mb-0">Manage first-time visitor lead capture, urgency countdown timers, and promotional banner bars.</p>
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ session('success') }}
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.coupons.popup-settings.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            
            <!-- Left Column: Settings Configuration -->
            <div class="col-lg-7">
                
                <!-- Card 1: Modal Trigger & Urgency Settings -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-gift-fill text-primary fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark">First-Time Visitor Offer Modal</h5>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="welcome_popup_active" name="welcome_popup_active" value="1" {{ ($settings->get('welcome_popup_active', '1') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="welcome_popup_active">Popup Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_discount">Discount Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="100" class="form-control fw-bold" id="welcome_popup_discount" name="welcome_popup_discount" value="{{ $settings->get('welcome_popup_discount', '25') }}" required>
                                    <span class="input-group-text bg-light fw-bold text-primary">% OFF</span>
                                </div>
                                <small class="text-muted d-block mt-1">Percentage discount applied to first booking.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_timer_mins">Session Urgency Timer</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="120" class="form-control fw-bold" id="welcome_popup_timer_mins" name="welcome_popup_timer_mins" value="{{ $settings->get('welcome_popup_timer_mins', '15') }}" required>
                                    <span class="input-group-text bg-light fw-bold">Minutes</span>
                                </div>
                                <small class="text-muted d-block mt-1">Urgency countdown ticker duration.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_delay_sec">Trigger Delay</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="60" class="form-control fw-bold" id="welcome_popup_delay_sec" name="welcome_popup_delay_sec" value="{{ $settings->get('welcome_popup_delay_sec', '5') }}" required>
                                    <span class="input-group-text bg-light fw-bold">Seconds</span>
                                </div>
                                <small class="text-muted d-block mt-1">Time on page before popup displays automatically.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Smart Trigger Triggers</label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="welcome_popup_exit_trigger" name="welcome_popup_exit_trigger" value="1" {{ ($settings->get('welcome_popup_exit_trigger', '1') == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold text-dark" for="welcome_popup_exit_trigger">
                                            Desktop Exit-Intent (When cursor moves to close tab)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="welcome_popup_scroll_trigger" name="welcome_popup_scroll_trigger" value="1" {{ ($settings->get('welcome_popup_scroll_trigger', '1') == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold text-dark" for="welcome_popup_scroll_trigger">
                                            Scroll Depth (When visitor scrolls 35% of page)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_headline">Modal Headline</label>
                                <input type="text" class="form-control fw-bold" id="welcome_popup_headline" name="welcome_popup_headline" value="{{ $settings->get('welcome_popup_headline', 'Unlock 25% OFF Your Dubai Desert Adventure') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="welcome_popup_subheadline">Modal Subheadline</label>
                                <textarea class="form-control" rows="2" id="welcome_popup_subheadline" name="welcome_popup_subheadline">{{ $settings->get('welcome_popup_subheadline', 'Valid for today\'s booking • Tour date can be selected for any future date!') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Top Announcement Promo Banner Bar -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-megaphone-fill text-warning fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark">Top Announcement Promo Banner</h5>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="top_promo_banner_active" name="top_promo_banner_active" value="1" {{ ($settings->get('top_promo_banner_active', '1') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="top_promo_banner_active">Banner Active</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="top_promo_banner_text">Banner Announcement Text</label>
                                <input type="text" class="form-control fw-bold" id="top_promo_banner_text" name="top_promo_banner_text" value="{{ $settings->get('top_promo_banner_text', '🎟️ First-Time Visitor? Claim 25% OFF Your Desert Safari Today with Code FIRST25! • 100% Free 24h Cancellation') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1" for="top_promo_banner_code">Featured Promo Code to Display</label>
                                <input type="text" class="form-control fw-bold font-monospace text-uppercase" id="top_promo_banner_code" name="top_promo_banner_code" value="{{ $settings->get('top_promo_banner_code', 'FIRST25') }}">
                                <small class="text-muted d-block mt-1">Visitors can 1-click copy this code from top banner.</small>
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
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2">Top Banner Preview:</span>
                            <div class="p-2 rounded-3 text-white text-center small fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); border-bottom: 2px solid #F58F43; font-size: 0.8rem;">
                                <span>🎟️ Claim 25% OFF Today with Code</span>
                                <span class="badge bg-warning text-dark font-monospace px-2 py-1">FIRST25</span>
                            </div>
                        </div>

                        <!-- Modal Card Preview -->
                        <span class="text-muted small fw-bold text-uppercase d-block mb-2">Modal Card Preview:</span>
                        <div class="border rounded-4 p-4 shadow-sm bg-white position-relative overflow-hidden" style="border-color: rgba(245, 143, 67, 0.3) !important;">
                            <div class="position-absolute top-0 start-0 end-0" style="height: 4px; background: linear-gradient(90deg, #F58F43 0%, #d2a13b 100%);"></div>
                            
                            <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill bg-primary-subtle text-primary fw-bold text-uppercase mb-2" style="font-size: 0.65rem;">
                                <i class="bi bi-gift-fill"></i> First-Time Guest Special
                            </div>

                            <h5 class="fw-800 text-dark lh-sm mb-2">Unlock 25% OFF Today</h5>
                            <p class="text-muted lh-sm mb-3" style="font-size: 0.8rem;">Book your authentic Dubai desert adventure today and save 25% instantly.</p>

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

                        <div class="mt-4 p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Conversion Tips:</h6>
                            <ul class="text-muted small mb-0 ps-3 lh-base">
                                <li>Urgency timers increase first-session checkout completion by up to 34%.</li>
                                <li>The 100% Free 24h Cancellation guarantee eliminates booking hesitation for international tourists.</li>
                                <li>Leads are logged automatically to Inquiries for follow-up.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
