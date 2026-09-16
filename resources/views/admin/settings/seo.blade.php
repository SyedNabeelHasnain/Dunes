@extends('layouts.admin')

@section('page_title', 'SEO & Metadata Management')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                <i class="bi bi-search"></i>
            </div>
            <div>
                <h5 class="fw-800 mb-0 text-dark">SEO & Metadata Management</h5>
                <div class="text-muted small">Configure global default metadata and custom per-page SEO titles, descriptions, keywords, and OpenGraph social share cards.</div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- Nav Tabs -->
            <ul class="nav nav-pills gap-2 mb-4 p-2 bg-light rounded-4 border" id="seoTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold" id="default-tab" data-bs-toggle="pill" data-bs-target="#tab-default" type="button" role="tab">Global Defaults</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="home-tab" data-bs-toggle="pill" data-bs-target="#tab-home" type="button" role="tab">Homepage</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="tours-tab" data-bs-toggle="pill" data-bs-target="#tab-tours" type="button" role="tab">Tours Catalog</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="blog-tab" data-bs-toggle="pill" data-bs-target="#tab-blog" type="button" role="tab">Blog Catalog</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="about-tab" data-bs-toggle="pill" data-bs-target="#tab-about" type="button" role="tab">About Us</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="contact-tab" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab">Contact Us</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="faq-tab" data-bs-toggle="pill" data-bs-target="#tab-faq" type="button" role="tab">FAQ Page</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="rate-card-tab" data-bs-toggle="pill" data-bs-target="#tab-rate-card" type="button" role="tab">Rate Card</button>
                </li>
            </ul>

            <div class="tab-content" id="seoTabsContent">
                <!-- 1. Global Defaults -->
                <div class="tab-pane fade show active" id="tab-default" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-globe me-2"></i>Global Fallback SEO Settings</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">Sitewide Fallback</span>
                        </div>
                        <p class="text-muted small mb-4">Applied across all general pages whenever specific page meta is not provided.</p>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_default_title" class="form-label fw-bold text-dark">Default Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_default_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_default_title" id="seo_default_title" class="form-control seo-input" value="{{ $settings['seo_default_title'] ?? '' }}" placeholder="Dunes Discovery Tourism | Premium Dubai Desert Safari Tours">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_default_description" class="form-label fw-bold text-dark">Default Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_default_description">0 / 160</span>
                            </div>
                            <textarea name="seo_default_description" id="seo_default_description" class="form-control seo-input" rows="3" placeholder="Experience Dubai's premier desert safari adventures...">{{ $settings['seo_default_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_default_keywords" class="form-label fw-bold text-dark">Default Meta Keywords</label>
                                <input type="text" name="seo_default_keywords" id="seo_default_keywords" class="form-control" value="{{ $settings['seo_default_keywords'] ?? '' }}" placeholder="desert safari dubai, dunes discovery, evening safari">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_default_og_image" class="form-label fw-bold text-dark">Default OpenGraph Image URL</label>
                                <input type="text" name="seo_default_og_image" id="seo_default_og_image" class="form-control" value="{{ $settings['seo_default_og_image'] ?? '' }}" placeholder="/images/og-default.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Homepage SEO -->
                <div class="tab-pane fade" id="tab-home" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-house me-2"></i>Homepage SEO Settings (/)</h6>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">High Priority</span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_home_title" class="form-label fw-bold text-dark">Homepage Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_home_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_home_title" id="seo_home_title" class="form-control seo-input" value="{{ $settings['seo_home_title'] ?? '' }}" placeholder="Dunes Discovery Tourism | Dubai Desert Safari & Adventure Tours 2026">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_home_description" class="form-label fw-bold text-dark">Homepage Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_home_description">0 / 160</span>
                            </div>
                            <textarea name="seo_home_description" id="seo_home_description" class="form-control seo-input" rows="3">{{ $settings['seo_home_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_home_keywords" class="form-label fw-bold text-dark">Homepage Meta Keywords</label>
                                <input type="text" name="seo_home_keywords" id="seo_home_keywords" class="form-control" value="{{ $settings['seo_home_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_home_og_image" class="form-label fw-bold text-dark">Homepage OpenGraph Image</label>
                                <input type="text" name="seo_home_og_image" id="seo_home_og_image" class="form-control" value="{{ $settings['seo_home_og_image'] ?? '' }}" placeholder="/images/og-home.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Tours Catalog SEO -->
                <div class="tab-pane fade" id="tab-tours" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-compass me-2"></i>Tours Catalog SEO (/tours)</h6>
                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-bold">Commercial Catalog</span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_tours_title" class="form-label fw-bold text-dark">Tours Catalog Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_tours_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_tours_title" id="seo_tours_title" class="form-control seo-input" value="{{ $settings['seo_tours_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_tours_description" class="form-label fw-bold text-dark">Tours Catalog Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_tours_description">0 / 160</span>
                            </div>
                            <textarea name="seo_tours_description" id="seo_tours_description" class="form-control seo-input" rows="3">{{ $settings['seo_tours_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_tours_keywords" class="form-label fw-bold text-dark">Tours Catalog Keywords</label>
                                <input type="text" name="seo_tours_keywords" id="seo_tours_keywords" class="form-control" value="{{ $settings['seo_tours_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_tours_og_image" class="form-label fw-bold text-dark">Tours Catalog OG Image</label>
                                <input type="text" name="seo_tours_og_image" id="seo_tours_og_image" class="form-control" value="{{ $settings['seo_tours_og_image'] ?? '' }}" placeholder="/images/og-tours.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Blog Catalog SEO -->
                <div class="tab-pane fade" id="tab-blog" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-journal-text me-2"></i>Blog Catalog SEO (/blog)</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold">E-E-A-T Content</span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_blog_title" class="form-label fw-bold text-dark">Blog Catalog Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_blog_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_blog_title" id="seo_blog_title" class="form-control seo-input" value="{{ $settings['seo_blog_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_blog_description" class="form-label fw-bold text-dark">Blog Catalog Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_blog_description">0 / 160</span>
                            </div>
                            <textarea name="seo_blog_description" id="seo_blog_description" class="form-control seo-input" rows="3">{{ $settings['seo_blog_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_blog_keywords" class="form-label fw-bold text-dark">Blog Catalog Keywords</label>
                                <input type="text" name="seo_blog_keywords" id="seo_blog_keywords" class="form-control" value="{{ $settings['seo_blog_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_blog_og_image" class="form-label fw-bold text-dark">Blog Catalog OG Image</label>
                                <input type="text" name="seo_blog_og_image" id="seo_blog_og_image" class="form-control" value="{{ $settings['seo_blog_og_image'] ?? '' }}" placeholder="/images/og-blog.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. About Us SEO -->
                <div class="tab-pane fade" id="tab-about" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-info-circle me-2"></i>About Us SEO (/about)</h6>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_about_title" class="form-label fw-bold text-dark">About Us Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_about_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_about_title" id="seo_about_title" class="form-control seo-input" value="{{ $settings['seo_about_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_about_description" class="form-label fw-bold text-dark">About Us Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_about_description">0 / 160</span>
                            </div>
                            <textarea name="seo_about_description" id="seo_about_description" class="form-control seo-input" rows="3">{{ $settings['seo_about_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_about_keywords" class="form-label fw-bold text-dark">About Us Keywords</label>
                                <input type="text" name="seo_about_keywords" id="seo_about_keywords" class="form-control" value="{{ $settings['seo_about_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_about_og_image" class="form-label fw-bold text-dark">About Us OG Image</label>
                                <input type="text" name="seo_about_og_image" id="seo_about_og_image" class="form-control" value="{{ $settings['seo_about_og_image'] ?? '' }}" placeholder="/images/og-about.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Contact Us SEO -->
                <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-chat-dots me-2"></i>Contact Us SEO (/contact)</h6>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_contact_title" class="form-label fw-bold text-dark">Contact Us Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_contact_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_contact_title" id="seo_contact_title" class="form-control seo-input" value="{{ $settings['seo_contact_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_contact_description" class="form-label fw-bold text-dark">Contact Us Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_contact_description">0 / 160</span>
                            </div>
                            <textarea name="seo_contact_description" id="seo_contact_description" class="form-control seo-input" rows="3">{{ $settings['seo_contact_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_contact_keywords" class="form-label fw-bold text-dark">Contact Us Keywords</label>
                                <input type="text" name="seo_contact_keywords" id="seo_contact_keywords" class="form-control" value="{{ $settings['seo_contact_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_contact_og_image" class="form-label fw-bold text-dark">Contact Us OG Image</label>
                                <input type="text" name="seo_contact_og_image" id="seo_contact_og_image" class="form-control" value="{{ $settings['seo_contact_og_image'] ?? '' }}" placeholder="/images/og-contact.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. FAQ SEO -->
                <div class="tab-pane fade" id="tab-faq" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-question-circle me-2"></i>FAQ SEO (/faq)</h6>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_faq_title" class="form-label fw-bold text-dark">FAQ Page Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_faq_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_faq_title" id="seo_faq_title" class="form-control seo-input" value="{{ $settings['seo_faq_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_faq_description" class="form-label fw-bold text-dark">FAQ Page Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_faq_description">0 / 160</span>
                            </div>
                            <textarea name="seo_faq_description" id="seo_faq_description" class="form-control seo-input" rows="3">{{ $settings['seo_faq_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_faq_keywords" class="form-label fw-bold text-dark">FAQ Keywords</label>
                                <input type="text" name="seo_faq_keywords" id="seo_faq_keywords" class="form-control" value="{{ $settings['seo_faq_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_faq_og_image" class="form-label fw-bold text-dark">FAQ OG Image</label>
                                <input type="text" name="seo_faq_og_image" id="seo_faq_og_image" class="form-control" value="{{ $settings['seo_faq_og_image'] ?? '' }}" placeholder="/images/og-faq.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. Rate Card SEO -->
                <div class="tab-pane fade" id="tab-rate-card" role="tabpanel">
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-tag me-2"></i>Rate Card & Pricing Guide SEO (/rate-card)</h6>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_rate_card_title" class="form-label fw-bold text-dark">Rate Card Meta Title</label>
                                <span class="small text-muted char-count" data-target="seo_rate_card_title">0 / 60</span>
                            </div>
                            <input type="text" name="seo_rate_card_title" id="seo_rate_card_title" class="form-control seo-input" value="{{ $settings['seo_rate_card_title'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="seo_rate_card_description" class="form-label fw-bold text-dark">Rate Card Meta Description</label>
                                <span class="small text-muted char-count" data-target="seo_rate_card_description">0 / 160</span>
                            </div>
                            <textarea name="seo_rate_card_description" id="seo_rate_card_description" class="form-control seo-input" rows="3">{{ $settings['seo_rate_card_description'] ?? '' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_rate_card_keywords" class="form-label fw-bold text-dark">Rate Card Keywords</label>
                                <input type="text" name="seo_rate_card_keywords" id="seo_rate_card_keywords" class="form-control" value="{{ $settings['seo_rate_card_keywords'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="seo_rate_card_og_image" class="form-label fw-bold text-dark">Rate Card OG Image</label>
                                <input type="text" name="seo_rate_card_og_image" id="seo_rate_card_og_image" class="form-control" value="{{ $settings['seo_rate_card_og_image'] ?? '' }}" placeholder="/images/og-rate-card.jpg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save SEO Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCount(input) {
        const targetId = input.id;
        const countSpan = document.querySelector(`.char-count[data-target="${targetId}"]`);
        if (countSpan) {
            const max = targetId.includes('title') ? 60 : 160;
            const len = input.value.length;
            countSpan.textContent = `${len} / ${max}`;
            if (len > max) {
                countSpan.classList.add('text-danger', 'fw-bold');
                countSpan.classList.remove('text-muted');
            } else {
                countSpan.classList.remove('text-danger', 'fw-bold');
                countSpan.classList.add('text-muted');
            }
        }
    }

    document.querySelectorAll('.seo-input').forEach(function(input) {
        updateCount(input);
        input.addEventListener('input', function() {
            updateCount(this);
        });
    });
});
</script>
@endpush
@endsection
