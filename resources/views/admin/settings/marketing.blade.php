@extends('layouts.admin')

@section('page_title', 'Marketing & Promotional Settings')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                <i class="bi bi-megaphone"></i>
            </div>
            <div>
                <h5 class="fw-800 mb-0 text-dark">Marketing & Promotional Announcements</h5>
                <div class="text-muted small">Manage sitewide top announcement banners, welcome offer modals, promo codes, and countdown timers dynamically.</div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <input type="hidden" name="promo_banner_form_submitted" value="1">
            <input type="hidden" name="promo_modal_form_submitted" value="1">

            <div class="row g-4">
                <!-- Top Announcement Promo Bar -->
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-layout-text-window-reverse me-2"></i>Top Sticky Announcement Bar</h6>
                            <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0" type="checkbox" name="promo_top_banner_enabled" id="promo_top_banner_enabled" value="1" {{ ($settings['promo_top_banner_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8rem; height: 1.4rem;">
                                <label class="form-check-label fw-bold text-dark small" for="promo_top_banner_enabled">Active</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="promo_top_banner_badge" class="form-label fw-bold text-dark">Announcement Badge</label>
                            <input type="text" name="promo_top_banner_badge" id="promo_top_banner_badge" class="form-control" value="{{ $settings['promo_top_banner_badge'] ?? 'Limited Time Offer' }}" placeholder="Limited Time Offer">
                            <div class="form-text">Highlighted badge text placed at the start of the announcement bar.</div>
                        </div>

                        <div class="mb-3">
                            <label for="promo_top_banner_text" class="form-label fw-bold text-dark">Announcement Message</label>
                            <textarea name="promo_top_banner_text" id="promo_top_banner_text" class="form-control" rows="3" placeholder="Special Offer: Get 25% OFF on all Desert Safari Tours!">{{ $settings['promo_top_banner_text'] ?? '' }}</textarea>
                            <div class="form-text">Full announcement copy visible on both desktop and mobile headers.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="promo_top_banner_code" class="form-label fw-bold text-dark">Promo Code</label>
                                <input type="text" name="promo_top_banner_code" id="promo_top_banner_code" class="form-control fw-bold text-uppercase" value="{{ $settings['promo_top_banner_code'] ?? 'DUNESWELCOME' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="promo_top_banner_discount" class="form-label fw-bold text-dark">Discount %</label>
                                <div class="input-group">
                                    <input type="number" min="1" max="100" name="promo_top_banner_discount" id="promo_top_banner_discount" class="form-control fw-bold" value="{{ $settings['promo_top_banner_discount'] ?? '25' }}">
                                    <span class="input-group-text fw-bold">% OFF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welcome Offer 25% Modal -->
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="text-primary fw-800 text-uppercase small mb-0"><i class="bi bi-gift me-2"></i>Welcome Offer Modal (Popup)</h6>
                            <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0" type="checkbox" name="promo_welcome_modal_enabled" id="promo_welcome_modal_enabled" value="1" {{ ($settings['promo_welcome_modal_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8rem; height: 1.4rem;">
                                <label class="form-check-label fw-bold text-dark small" for="promo_welcome_modal_enabled">Active</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="promo_welcome_modal_headline" class="form-label fw-bold text-dark">Modal Headline</label>
                            <input type="text" name="promo_welcome_modal_headline" id="promo_welcome_modal_headline" class="form-control" value="{{ $settings['promo_welcome_modal_headline'] ?? 'Unlock Exclusive 25% OFF' }}">
                        </div>

                        <div class="mb-3">
                            <label for="promo_welcome_modal_subheadline" class="form-label fw-bold text-dark">Subheadline / Description</label>
                            <textarea name="promo_welcome_modal_subheadline" id="promo_welcome_modal_subheadline" class="form-control" rows="3">{{ $settings['promo_welcome_modal_subheadline'] ?? 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.' }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="promo_welcome_modal_discount" class="form-label fw-bold text-dark">Discount %</label>
                                <div class="input-group">
                                    <input type="number" min="1" max="100" name="promo_welcome_modal_discount" id="promo_welcome_modal_discount" class="form-control fw-bold" value="{{ $settings['promo_welcome_modal_discount'] ?? '25' }}">
                                    <span class="input-group-text fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="promo_welcome_modal_timer_minutes" class="form-label fw-bold text-dark">Timer Duration</label>
                                <div class="input-group">
                                    <input type="number" min="1" max="120" name="promo_welcome_modal_timer_minutes" id="promo_welcome_modal_timer_minutes" class="form-control fw-bold" value="{{ $settings['promo_welcome_modal_timer_minutes'] ?? '15' }}">
                                    <span class="input-group-text">Min</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="promo_welcome_modal_delay_seconds" class="form-label fw-bold text-dark">Popup Delay</label>
                                <div class="input-group">
                                    <input type="number" min="0" max="60" name="promo_welcome_modal_delay_seconds" id="promo_welcome_modal_delay_seconds" class="form-control fw-bold" value="{{ $settings['promo_welcome_modal_delay_seconds'] ?? '5' }}">
                                    <span class="input-group-text">Sec</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save Promotional Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
