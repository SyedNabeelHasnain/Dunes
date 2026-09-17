@extends('layouts.admin')

@section('page_title', 'SMTP & Mailer Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-800 text-dark mb-1">
                <i class="bi bi-envelope-gear-fill text-primary me-2"></i>SMTP & Mailer Configuration
            </h4>
            <div class="text-muted small">Configure dynamic email delivery, custom SMTP credentials, sender identity, and campaign throttle limits.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" onclick="document.getElementById('mailSettingsForm').submit();">
                <i class="bi bi-check-lg"></i> Save Mail Settings
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Main Settings Form Column -->
        <div class="col-12 col-xl-8">
            <form id="mailSettingsForm" action="{{ route('admin.settings.mail.update') }}" method="POST">
                @csrf

                <!-- SMTP Server Credentials -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="bi bi-server text-primary me-2"></i>Mail Server Connection
                        </h5>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 extra-small fw-bold">
                            Runtime Dynamic
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Mail Driver <span class="text-danger">*</span></label>
                            <select name="smtp_driver" class="form-select rounded-3 py-2 @error('smtp_driver') is-invalid @enderror" required>
                                <option value="smtp" {{ ($settings['smtp_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Recommended for Production)</option>
                                <option value="sendmail" {{ ($settings['smtp_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail (Local Server MTA)</option>
                                <option value="log" {{ ($settings['smtp_driver'] ?? '') === 'log' ? 'selected' : '' }}>Log Driver (Testing / Development)</option>
                            </select>
                            @error('smtp_driver')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control rounded-3 py-2 @error('smtp_host') is-invalid @enderror" placeholder="smtp.hostinger.com or smtp.gmail.com" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}">
                            @error('smtp_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label fw-bold small text-dark">Port</label>
                            <select name="smtp_port" class="form-select rounded-3 py-2 @error('smtp_port') is-invalid @enderror">
                                <option value="465" {{ ($settings['smtp_port'] ?? '465') == '465' ? 'selected' : '' }}>465 (SSL / SMTPS)</option>
                                <option value="587" {{ ($settings['smtp_port'] ?? '') == '587' ? 'selected' : '' }}>587 (TLS / STARTTLS)</option>
                                <option value="25" {{ ($settings['smtp_port'] ?? '') == '25' ? 'selected' : '' }}>25 (Standard Unencrypted)</option>
                                <option value="2525" {{ ($settings['smtp_port'] ?? '') == '2525' ? 'selected' : '' }}>2525 (Alternative TLS)</option>
                            </select>
                            @error('smtp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label fw-bold small text-dark">Encryption</label>
                            <select name="smtp_encryption" class="form-select rounded-3 py-2 @error('smtp_encryption') is-invalid @enderror">
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="none" {{ ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                            @error('smtp_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">SMTP Username / Email</label>
                            <input type="text" name="smtp_username" class="form-control rounded-3 py-2 @error('smtp_username') is-invalid @enderror" placeholder="info@domain.com" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}">
                            @error('smtp_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">SMTP Password</label>
                            <div class="input-group">
                                <input type="password" name="smtp_password" id="smtpPassword" class="form-control rounded-start-3 py-2" placeholder="•••••••••••••• (Leave blank to keep existing)">
                                <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePasswordVisibility('smtpPassword')">
                                    <i class="bi bi-eye" id="smtpPasswordEye"></i>
                                </button>
                            </div>
                            <div class="form-text extra-small text-muted">Passwords are securely stored. Leave blank if you don't wish to change the current password.</div>
                        </div>
                    </div>
                </div>

                <!-- Sender Branding & Header Info -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-800 text-dark mb-3">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>Sender Identity & Branding
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">From Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="smtp_from_address" class="form-control rounded-3 py-2 @error('smtp_from_address') is-invalid @enderror" placeholder="noreply@domain.com" value="{{ old('smtp_from_address', $settings['smtp_from_address'] ?? config('mail.from.address')) }}" required>
                            @error('smtp_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">From Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="smtp_from_name" class="form-control rounded-3 py-2 @error('smtp_from_name') is-invalid @enderror" placeholder="e.g. Dunes Discovery Tourism" value="{{ old('smtp_from_name', $settings['smtp_from_name'] ?? $settings['site_name'] ?? config('mail.from.name')) }}" required>
                            @error('smtp_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Reply-To Address</label>
                            <input type="email" name="smtp_reply_to" class="form-control rounded-3 py-2 @error('smtp_reply_to') is-invalid @enderror" placeholder="support@domain.com" value="{{ old('smtp_reply_to', $settings['smtp_reply_to'] ?? '') }}">
                            @error('smtp_reply_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text extra-small text-muted">Subscribers will see this address when hitting "Reply" in their email client.</div>
                        </div>
                    </div>
                </div>

                <!-- Throttle & Newsletter Engine Settings -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-800 text-dark mb-3">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>Broadcast Throttle & Deliverability
                    </h5>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between p-3 rounded-4 bg-light">
                                <div>
                                    <label class="form-check-label fw-800 text-dark mb-0" for="newsletterEnabled">
                                        Enable Public Newsletter Subscription Block
                                    </label>
                                    <div class="text-muted extra-small">Display newsletter subscription form above footer across the portal.</div>
                                </div>
                                <input class="form-check-input fs-4 ms-3" type="checkbox" name="newsletter_enabled" value="1" id="newsletterEnabled" {{ ($settings['newsletter_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Batch Size (Emails per Chunk)</label>
                            <input type="number" name="newsletter_batch_size" class="form-control rounded-3 py-2" min="5" max="250" value="{{ old('newsletter_batch_size', $settings['newsletter_batch_size'] ?? '50') }}" required>
                            <div class="form-text extra-small text-muted">Recommended: 25 - 50. Keeps batches within shared hosting burst limits.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Batch Throttle Delay (Seconds)</label>
                            <input type="number" name="newsletter_batch_delay" class="form-control rounded-3 py-2" min="0" max="10" value="{{ old('newsletter_batch_delay', $settings['newsletter_batch_delay'] ?? '1') }}" required>
                            <div class="form-text extra-small text-muted">Pause between chunk dispatches to prevent SMTP rate-limit bans.</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Live SMTP Connection Tester -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 85px; z-index: 10;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-2">
                        <i class="bi bi-broadcast fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 text-dark mb-0">Live SMTP Tester</h6>
                        <span class="text-muted extra-small">Verify host, port, credentials & socket connectivity</span>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Send a live diagnostic test email to verify that your mail server credentials authenticate and can transmit messages cleanly.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Diagnostic Recipient Email:</label>
                    <input type="email" id="testConnectionEmail" class="form-control rounded-3 py-2" placeholder="yourname@domain.com" value="{{ auth()->user()->email ?? '' }}">
                </div>

                <button type="button" class="btn btn-dark w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnTestConnection" onclick="runLiveSmtpTest()">
                    <i class="bi bi-send-check"></i> Test SMTP Connection
                </button>

                <div id="smtpTestResult" class="mt-3 d-none"></div>

                <hr class="my-4">

                <h6 class="fw-800 text-dark mb-2 extra-small text-uppercase">
                    <i class="bi bi-shield-check text-success me-1"></i> Recommended Best Practices
                </h6>
                <ul class="text-muted small ps-3 mb-0" style="font-size: 0.8rem; line-height: 1.6;">
                    <li>Ensure your sending domain has active <strong>SPF</strong> and <strong>DKIM</strong> DNS records.</li>
                    <li>Port <strong>465 (SSL)</strong> is typically preferred for Hostinger, cPanel, and Titan mail.</li>
                    <li>Always send a test email before initiating large promotional campaigns.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePasswordVisibility(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + 'Eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('bi-eye');
        eye.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('bi-eye-slash');
        eye.classList.add('bi-eye');
    }
}

async function runLiveSmtpTest() {
    const emailInput = document.getElementById('testConnectionEmail');
    const resultBox = document.getElementById('smtpTestResult');
    const btn = document.getElementById('btnTestConnection');

    const email = emailInput.value.trim();
    if (!email) {
        resultBox.className = 'alert alert-danger rounded-3 small mt-3';
        resultBox.textContent = 'Please enter an email address to receive the test.';
        resultBox.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing Connection...';
    resultBox.classList.add('d-none');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const response = await fetch('{{ route("admin.settings.mail.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ test_email: email })
        });

        const data = await response.json();
        if (data.success) {
            resultBox.className = 'alert alert-success border-0 rounded-4 small mt-3 shadow-sm';
            resultBox.innerHTML = `<strong><i class="bi bi-check-circle-fill me-1"></i> Success!</strong> ${data.message}`;
        } else {
            resultBox.className = 'alert alert-danger border-0 rounded-4 small mt-3 shadow-sm';
            resultBox.innerHTML = `<strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Connection Failed:</strong><br>${data.message}`;
        }
        resultBox.classList.remove('d-none');
    } catch (err) {
        resultBox.className = 'alert alert-danger border-0 rounded-4 small mt-3 shadow-sm';
        resultBox.innerHTML = `<strong><i class="bi bi-x-circle-fill me-1"></i> Error:</strong> Could not connect to testing endpoint.`;
        resultBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-check"></i> Test SMTP Connection';
    }
}
</script>
@endpush
@endsection
