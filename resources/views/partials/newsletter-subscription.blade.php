@php
    $settings = app(\App\Services\SettingsService::class);
    $newsletterEnabled = $settings->get('newsletter_enabled', '1') === '1';
    $siteName = $settings->get('site_name', 'Dunes Discovery Tourism');
@endphp

@if($newsletterEnabled)
<section class="newsletter-section py-5 position-relative overflow-hidden" id="newsletterBlock" style="background: linear-gradient(180deg, #0b1325 0%, #060a13 100%); border-top: 1px solid rgba(255, 255, 255, 0.08);">
    <div class="container position-relative z-2">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8 text-center">
                <!-- Eyebrow Badge -->
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-15 text-warning extra-small fw-bold text-uppercase mb-3 shadow-sm">
                    <i class="bi bi-envelope-paper-heart"></i> VIP Travel Club & Special Offers
                </div>

                <!-- Section Heading -->
                <h2 class="display-6 fw-800 text-white mb-3">
                    Receive Exclusive <span class="text-gradient-primary">Desert Safari Offers</span> & Guides
                </h2>
                <p class="text-white-50 lead fs-6 mb-4 px-md-4">
                    Subscribe to {{ $siteName }} for verified member discounts, seasonal adventure rates, and insider Dubai desert travel guides delivered directly to your inbox.
                </p>

                <!-- Subscription Card / Form -->
                <div class="card border-0 rounded-4 shadow-lg p-3 p-md-4 text-start" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1) !important; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                    <form id="publicNewsletterForm" class="row g-3 align-items-center" novalidate>
                        @csrf
                        <!-- Anti-Bot Honeypot -->
                        <div style="display: none !important;" aria-hidden="true">
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <!-- Name Input -->
                        <div class="col-12 col-md-4">
                            <div class="position-relative">
                                <i class="bi bi-person position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" name="name" id="newsletterName" class="form-control rounded-pill ps-5 py-2.5 bg-white border border-light shadow-sm fw-semibold" placeholder="Your Name (Optional)" maxlength="100">
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div class="col-12 col-md-5">
                            <div class="position-relative">
                                <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input type="email" name="email" id="newsletterEmail" class="form-control rounded-pill ps-5 py-2.5 bg-white border border-light shadow-sm fw-semibold" placeholder="Enter your email address *" required maxlength="255">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 col-md-3">
                            <button type="submit" id="btnNewsletterSubmit" class="btn btn-desert-animated w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <span>Join Club</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>

                        <!-- Consent & Privacy Disclaimer -->
                        <div class="col-12 mt-2">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input class="form-check-input mt-0 flex-shrink-0" type="checkbox" name="consent" id="newsletterConsent" required checked>
                                <label class="form-check-label text-white-50 extra-small" for="newsletterConsent">
                                    I agree to receive personalized newsletters and travel offers. Unsubscribe easily at any time.
                                </label>
                            </div>
                        </div>

                        <!-- Status Alert Box -->
                        <div class="col-12 d-none" id="newsletterStatusBox">
                            <div class="alert mb-0 rounded-3 py-2 px-3 small d-flex align-items-center gap-2" id="newsletterAlert"></div>
                        </div>
                    </form>
                </div>

                <!-- Trust Badges -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 mt-4 text-white-50 extra-small">
                    <div><i class="bi bi-shield-check text-success me-1"></i> Zero spam guarantee</div>
                    <div><i class="bi bi-lock-fill text-warning me-1"></i> 100% Privacy protected</div>
                    <div><i class="bi bi-check2-circle text-muted me-1"></i> Instant one-click unsubscribe</div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('publicNewsletterForm');
    if (!form) return;

    const emailInput = document.getElementById('newsletterEmail');
    const nameInput = document.getElementById('newsletterName');
    const consentInput = document.getElementById('newsletterConsent');
    const submitBtn = document.getElementById('btnNewsletterSubmit');
    const statusBox = document.getElementById('newsletterStatusBox');
    const alertBox = document.getElementById('newsletterAlert');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = emailInput.value.trim();
        const name = nameInput ? nameInput.value.trim() : '';
        const consent = consentInput ? consentInput.checked : true;
        const honeypot = form.querySelector('input[name="website_url"]')?.value || '';

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAlert('Please enter a valid email address.', 'danger');
            emailInput.focus();
            return;
        }

        if (!consent) {
            showAlert('Please check the consent box to receive newsletters.', 'warning');
            return;
        }

        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Subscribing...';
        statusBox.classList.add('d-none');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch('/api/v1/subscribers/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    name: name,
                    website_url: honeypot,
                    consent: consent ? 1 : 0
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message || 'Thank you for subscribing! Check your inbox for your welcome guide.', 'success');
                form.reset();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Welcome to VIP Club!',
                        text: data.message || 'You have successfully joined our exclusive traveler newsletter.',
                        icon: 'success',
                        confirmButtonColor: '#F58F43'
                    });
                }
            } else {
                showAlert(data.message || 'Could not complete subscription. Please try again.', 'danger');
            }
        } catch (error) {
            showAlert('Network error. Please check your connection and try again.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });

    function showAlert(msg, type) {
        statusBox.classList.remove('d-none');
        alertBox.className = `alert alert-${type} mb-0 rounded-3 py-2 px-3 small d-flex align-items-center gap-2`;
        alertBox.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} fs-6"></i> <span>${msg}</span>`;
    }
});
</script>
@endpush
@endif
