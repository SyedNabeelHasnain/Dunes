@extends('layouts.admin')

@section('page_title', 'Google Integration settings')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                <i class="bi bi-google"></i>
            </div>
            <div>
                <h5 class="fw-800 mb-0 text-dark">Google Integration Suite</h5>
                <div class="text-muted small">Manage Tag Manager, Analytics, Google Ads, and reCAPTCHA.</div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- Master Switch -->
            <div class="p-4 bg-light rounded-4 border mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">Google Integration status</h6>
                <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-3">
                    <input class="form-check-input m-0" type="checkbox" name="google_active" id="google_active" value="1" {{ ($settings['google_active'] ?? '') == '1' ? 'checked' : '' }} style="width: 3.5rem; height: 1.75rem;">
                    <label class="form-check-label fw-bold text-dark" for="google_active">Enable tracking code injection across all pages</label>
                </div>
                <div class="form-text mt-2">When disabled, Google Tag Manager and Analytics scripts will not be loaded on the user-facing site.</div>
            </div>

            <div class="row g-4">
                <!-- Analytics & Tag Manager -->
                <div class="col-md-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-graph-up-arrow me-2"></i>Tracking & Analytics</h6>
                        
                        <div class="mb-3">
                            <label for="google_gtm_id" class="form-label fw-bold text-dark">Google Tag Manager ID</label>
                            <input type="text" name="google_gtm_id" id="google_gtm_id" class="form-control" value="{{ $settings['google_gtm_id'] ?? '' }}" placeholder="e.g. GTM-XXXXXXX">
                            <div class="form-text">Used for tag container deployment.</div>
                        </div>

                        <div class="mb-3">
                            <label for="google_ga4_id" class="form-label fw-bold text-dark">Google Analytics 4 ID</label>
                            <input type="text" name="google_ga4_id" id="google_ga4_id" class="form-control" value="{{ $settings['google_ga4_id'] ?? '' }}" placeholder="e.g. G-XXXXXXXXXX">
                            <div class="form-text">GA4 Measurement / Stream ID.</div>
                        </div>

                        <div class="mb-0">
                            <label for="google_site_verification" class="form-label fw-bold text-dark">Google Search Console Verification</label>
                            <input type="text" name="google_site_verification" id="google_site_verification" class="form-control" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Verification Code Hash">
                        </div>
                    </div>
                </div>

                <!-- Google Ads & Maps -->
                <div class="col-md-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-megaphone me-2"></i>Google Ads & Maps</h6>
                        
                        <div class="mb-3">
                            <label for="google_ads_id" class="form-label fw-bold text-dark">Google Ads Conversion ID</label>
                            <input type="text" name="google_ads_id" id="google_ads_id" class="form-control" value="{{ $settings['google_ads_id'] ?? '' }}" placeholder="e.g. AW-XXXXXXXXX">
                        </div>

                        <div class="mb-3">
                            <label for="google_conversion_label" class="form-label fw-bold text-dark">Google Ads Conversion Label</label>
                            <input type="text" name="google_conversion_label" id="google_conversion_label" class="form-control" value="{{ $settings['google_conversion_label'] ?? '' }}" placeholder="e.g. abcdEFGH123456">
                        </div>

                        <div class="mb-0">
                            <label for="google_maps_api_key" class="form-label fw-bold text-dark">Google Maps API Key</label>
                            <input type="text" name="google_maps_api_key" id="google_maps_api_key" class="form-control" value="{{ $settings['google_maps_api_key'] ?? '' }}" placeholder="Key for interactive location maps">
                        </div>
                    </div>
                </div>

                <!-- Google reCAPTCHA v3 -->
                <div class="col-12">
                    <div class="p-4 bg-light rounded-4 border">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-shield-check me-2"></i>Google reCAPTCHA v3 Protection</h6>
                        <p class="text-muted small">Protects booking and inquiry contact forms from automated bots.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="recaptcha_site_key" class="form-label fw-bold text-dark">reCAPTCHA v3 Site Key</label>
                                <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control" value="{{ $settings['recaptcha_site_key'] ?? '' }}" placeholder="Site Key">
                            </div>
                            <div class="col-md-6">
                                <label for="recaptcha_secret_key" class="form-label fw-bold text-dark">reCAPTCHA v3 Secret Key</label>
                                <input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" class="form-control" value="{{ $settings['recaptcha_secret_key'] ?? '' }}" placeholder="Secret Key">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Save Google Configurations</button>
            </div>
        </form>
    </div>
</div>
@endsection
