@extends('layouts.admin')

@section('page_title', 'WhatsApp Integration Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
                <h5 class="fw-800 mb-0 text-dark">WhatsApp Integration Settings</h5>
                <div class="text-muted small">Configure global settings for WhatsApp clicks and redirection.</div>
            </div>
            <div class="card-body p-4 ps-4 pe-4">
                <form action="{{ route('admin.whatsapp.settings.update') }}" method="POST">
                    @csrf

                    <!-- WhatsApp Phone Number -->
                    <div class="mb-4">
                        <label for="site_whatsapp" class="form-label fw-bold text-dark">WhatsApp Target Number</label>
                        <input type="text" name="site_whatsapp" id="site_whatsapp" class="form-control" value="{{ $settings['site_whatsapp'] ?? '971502456056' }}" placeholder="971502456056" required style="height: 50px; border-radius: 8px;">
                        <div class="form-text">The phone number where customer WhatsApp queries are sent. Must include country code and exclude leading '+' or zeros.</div>
                    </div>

                    <!-- Default Country Code -->
                    <div class="mb-4">
                        <label for="whatsapp_default_country" class="form-label fw-bold text-dark">Default Country Code (e.g. 971)</label>
                        <input type="text" name="whatsapp_default_country" id="whatsapp_default_country" class="form-control" value="{{ $settings['whatsapp_default_country'] ?? '971' }}" placeholder="971" required style="height: 50px; border-radius: 8px;">
                        <div class="form-text">Used to format phone numbers when users do not provide a country prefix.</div>
                    </div>

                    <!-- WhatsApp Lead Form Field Toggle -->
                    <div class="card bg-light border rounded-4 mb-4">
                        <div class="card-header bg-transparent py-3 border-bottom ps-4 pe-4">
                            <h6 class="fw-800 mb-0 text-dark">WhatsApp Lead Generation Form</h6>
                            <div class="text-muted small">Control if visitors must provide their details before contacting you on WhatsApp.</div>
                        </div>
                        <div class="card-body p-4 ps-4 pe-4">
                            <div class="d-flex align-items-center gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="whatsapp_form_enabled" id="waFormYes" value="1" {{ ($settings['whatsapp_form_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="waFormYes">Yes (Enabled)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="whatsapp_form_enabled" id="waFormNo" value="0" {{ ($settings['whatsapp_form_enabled'] ?? '1') === '0' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="waFormNo">No (Disabled)</label>
                                </div>
                            </div>
                            <div class="form-text mt-3">If set to "No", visitors will be redirected to WhatsApp instantly. Clicks are logged with default name "WhatsApp Visitor" and no phone detail.</div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 p-3 d-flex gap-3 mb-4">
                        <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                        <div class="small">
                            <strong>Redirection mechanism:</strong><br>
                            Redirection uses the standard WhatsApp API links (`wa.me`). This opens the WhatsApp application natively on mobile devices or WhatsApp Web on desktop browsers with a pre-filled, context-aware message.
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-800">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
