@extends('layouts.admin')

@section('page_title', 'Meta Conversion Suite')

@section('content')
<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light text-primary rounded-circle border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                <i class="bi bi-facebook text-primary"></i>
            </div>
            <div>
                <h5 class="fw-800 mb-0 text-dark">Meta / Facebook Conversion suite</h5>
                <div class="text-muted small">Configure Pixel ID and Conversions API (CAPI) events.</div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- CAPI Status Switch -->
            <div class="p-4 bg-light rounded-4 border mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">Conversions API Tracking</h6>
                <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-3">
                    <input class="form-check-input m-0" type="checkbox" name="meta_active" id="meta_active" value="1" {{ ($settings['meta_active'] ?? '') == '1' ? 'checked' : '' }} style="width: 3.5rem; height: 1.75rem;">
                    <label class="form-check-label fw-bold text-dark" for="meta_active">Inject browser Pixel and trigger Server Conversions API events</label>
                </div>
                <div class="form-text mt-2">When enabled, server-side actions like Checkout initiated or WhatsApp clicks will submit secure telemetry directly to Meta via CAPI.</div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="p-4 bg-light rounded-4 border">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4"><i class="bi bi-gear-fill me-2"></i>Meta API Credentials</h6>
                        
                        <div class="mb-3">
                            <label for="meta_pixel_id" class="form-label fw-bold text-dark">Meta Pixel ID</label>
                            <input type="text" name="meta_pixel_id" id="meta_pixel_id" class="form-control" value="{{ $settings['meta_pixel_id'] ?? '' }}" placeholder="e.g. 123456789012345" required style="border-radius: 8px;">
                            <div class="form-text">Your unique target Pixel container identity.</div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_access_token" class="form-label fw-bold text-dark">System Access Token (CAPI)</label>
                            <textarea name="meta_access_token" id="meta_access_token" class="form-control" rows="4" placeholder="EAAG..." style="border-radius: 8px; font-family: Courier, monospace;">{{ $settings['meta_access_token'] ?? '' }}</textarea>
                            <div class="form-text">Long-lived access token generated in Meta Events Manager settings tab.</div>
                        </div>

                        <div class="mb-0">
                            <label for="meta_test_event_code" class="form-label fw-bold text-dark">Test Event Code (Debugging Only)</label>
                            <input type="text" name="meta_test_event_code" id="meta_test_event_code" class="form-control" value="{{ $settings['meta_test_event_code'] ?? '' }}" placeholder="e.g. TEST12345" style="border-radius: 8px;">
                            <div class="form-text text-warning"><strong>Warning:</strong> Leave empty in production. Only fill when tracking testing in sandbox is active.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Save Meta Configurations</button>
            </div>
        </form>
    </div>
</div>
@endsection
