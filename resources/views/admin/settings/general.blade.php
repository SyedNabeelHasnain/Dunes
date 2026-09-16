@extends('layouts.admin')

@section('page_title', 'General Site Settings')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                <i class="bi bi-sliders"></i>
            </div>
            <div>
                <h5 class="fw-800 mb-0 text-dark">General Site Settings & Identity</h5>
                <div class="text-muted small">Manage official brand identity, contact numbers, licensing, Google Maps, and verified social channels.</div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="row g-4">
                <!-- Brand Identity & Licensing -->
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-building me-2"></i>Brand Identity & Legal Licensing</h6>
                        
                        <div class="mb-3">
                            <label for="site_name" class="form-label fw-bold text-dark">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="site_name" id="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }}" required>
                            <div class="form-text">Displayed in navbar, page headers, schema markup, and system notifications.</div>
                        </div>

                        <div class="mb-3">
                            <label for="company_license_number" class="form-label fw-bold text-dark">DET / DTCM License Number</label>
                            <input type="text" name="company_license_number" id="company_license_number" class="form-control" value="{{ $settings['company_license_number'] ?? '1430583' }}" placeholder="e.g. 1430583">
                            <div class="form-text">Official Dubai Department of Economy and Tourism commercial license for E-E-A-T trust and schemas.</div>
                        </div>

                        <div class="mb-3">
                            <label for="site_copyright" class="form-label fw-bold text-dark">Footer Copyright Notice</label>
                            <input type="text" name="site_copyright" id="site_copyright" class="form-control" value="{{ $settings['site_copyright'] ?? 'All rights reserved.' }}">
                        </div>

                        <div class="mb-0">
                            <label for="footer_about" class="form-label fw-bold text-dark">Footer About Bio</label>
                            <textarea name="footer_about" id="footer_about" class="form-control" rows="3">{{ $settings['footer_about'] ?? 'Your trusted partner for unforgettable Dubai desert safari and adventure tour experiences since 2018.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact & Communication -->
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-telephone-inbound me-2"></i>Direct Customer Contact</h6>
                        
                        <div class="mb-3">
                            <label for="site_phone" class="form-label fw-bold text-dark">Primary Telephone Hotline <span class="text-danger">*</span></label>
                            <input type="text" name="site_phone" id="site_phone" class="form-control" value="{{ $settings['site_phone'] ?? '+971 50 245 6056' }}" required>
                            <div class="form-text">Displayed on header, contact page, and schema. Format: +971 XX XXX XXXX</div>
                        </div>

                        <div class="mb-3">
                            <label for="site_whatsapp" class="form-label fw-bold text-dark">Official WhatsApp Number <span class="text-danger">*</span></label>
                            <input type="text" name="site_whatsapp" id="site_whatsapp" class="form-control" value="{{ $settings['site_whatsapp'] ?? '971502456056' }}" required>
                            <div class="form-text">Numeric digits with country code, no plus or spaces (e.g. 971502456056). Powers all instant WhatsApp chat buttons.</div>
                        </div>

                        <div class="mb-3">
                            <label for="site_email" class="form-label fw-bold text-dark">Primary Inquiries Email <span class="text-danger">*</span></label>
                            <input type="email" name="site_email" id="site_email" class="form-control" value="{{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}" required>
                        </div>

                        <div class="mb-0">
                            <label for="site_support_email" class="form-label fw-bold text-dark">Customer Support Email</label>
                            <input type="email" name="site_support_email" id="site_support_email" class="form-control" value="{{ $settings['site_support_email'] ?? 'support@dunesdiscoverytourism.com' }}">
                        </div>
                    </div>
                </div>

                <!-- Office Address & Google Maps Embed -->
                <div class="col-12">
                    <div class="p-4 bg-light rounded-4 border">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-geo-alt me-2"></i>Physical Office Location & Google Maps</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="site_address" class="form-label fw-bold text-dark">Official Physical Address</label>
                                <textarea name="site_address" id="site_address" class="form-control" rows="3">{{ $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates' }}</textarea>
                                <div class="form-text">Official registered office address for footer, contact page, and structured JSON-LD schemas.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="google_maps_embed_url" class="form-label fw-bold text-dark">Google Maps Embed Iframe URL</label>
                                <textarea name="google_maps_embed_url" id="google_maps_embed_url" class="form-control" rows="3" placeholder="https://www.google.com/maps/embed?pb=...">{{ $settings['google_maps_embed_url'] ?? '' }}</textarea>
                                <div class="form-text">Direct embed link for the interactive map shown on the Contact Us page.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Profiles & Reviews -->
                <div class="col-12">
                    <div class="p-4 bg-light rounded-4 border">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-share me-2"></i>Social Channels & Verified Review Platforms</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="social_tripadvisor" class="form-label fw-bold text-dark"><i class="bi bi-award text-success me-1"></i>TripAdvisor Review / Profile URL</label>
                                <input type="url" name="social_tripadvisor" id="social_tripadvisor" class="form-control" value="{{ $settings['social_tripadvisor'] ?? '' }}" placeholder="https://www.tripadvisor.com/...">
                            </div>
                            <div class="col-md-6">
                                <label for="social_google" class="form-label fw-bold text-dark"><i class="bi bi-google text-primary me-1"></i>Google Maps Review / Place URL</label>
                                <input type="url" name="social_google" id="social_google" class="form-control" value="{{ $settings['social_google'] ?? '' }}" placeholder="https://search.google.com/local/writereview?placeid=...">
                            </div>
                            <div class="col-md-6">
                                <label for="social_instagram" class="form-label fw-bold text-dark"><i class="bi bi-instagram text-danger me-1"></i>Instagram Profile URL</label>
                                <input type="url" name="social_instagram" id="social_instagram" class="form-control" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                            </div>
                            <div class="col-md-6">
                                <label for="social_facebook" class="form-label fw-bold text-dark"><i class="bi bi-facebook text-primary me-1"></i>Facebook Page URL</label>
                                <input type="url" name="social_facebook" id="social_facebook" class="form-control" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-6">
                                <label for="social_youtube" class="form-label fw-bold text-dark"><i class="bi bi-youtube text-danger me-1"></i>YouTube Channel URL</label>
                                <input type="url" name="social_youtube" id="social_youtube" class="form-control" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/@...">
                            </div>
                            <div class="col-md-6">
                                <label for="social_tiktok" class="form-label fw-bold text-dark"><i class="bi bi-tiktok text-dark me-1"></i>TikTok Profile URL</label>
                                <input type="url" name="social_tiktok" id="social_tiktok" class="form-control" value="{{ $settings['social_tiktok'] ?? '' }}" placeholder="https://tiktok.com/@...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
