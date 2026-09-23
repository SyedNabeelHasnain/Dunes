@php
    $settings = app(\App\Services\SettingsService::class);
    $newsletterEnabled = $settings->get('newsletter_enabled', '1') === '1';
    $siteName = $settings->get('site_name', 'Dunes Discovery Tourism');
@endphp

@if($newsletterEnabled)
<section class="py-12 sm:py-16 relative overflow-hidden bg-slate-950 border-t border-white/10" id="newsletterBlock">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center">
        <!-- Eyebrow Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4 shadow-sm">
            <i class="bi bi-envelope-paper-heart"></i>
            <span>VIP Travel Club & Special Offers</span>
        </div>

        <!-- Section Heading -->
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">
            Receive Exclusive <span class="bg-gradient-to-r from-orange-400 to-amber-300 bg-clip-text text-transparent">Desert Safari Offers</span> & Guides
        </h2>
        <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto mb-8 leading-relaxed">
            Subscribe to {{ $siteName }} for verified member discounts, seasonal adventure rates, and insider Dubai desert travel guides delivered directly to your inbox.
        </p>

        <!-- Subscription Card / Form -->
        <div class="rounded-3xl p-4 sm:p-6 text-left bg-white/5 border border-white/10 backdrop-blur-xl shadow-2xl">
            <form id="publicNewsletterForm" class="space-y-3" novalidate>
                @csrf
                <!-- Anti-Bot Honeypot -->
                <div style="display: none !important;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Name Input -->
                    <div class="md:col-span-4 relative">
                        <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="name" id="newsletterName" class="w-full rounded-full pl-11 pr-4 py-3 bg-white text-slate-800 text-sm font-semibold border-0 shadow-sm focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-slate-400" placeholder="Your Name (Optional)" maxlength="100">
                    </div>

                    <!-- Email Input -->
                    <div class="md:col-span-5 relative">
                        <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" name="email" id="newsletterEmail" class="w-full rounded-full pl-11 pr-4 py-3 bg-white text-slate-800 text-sm font-semibold border-0 shadow-sm focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-slate-400" placeholder="Enter your email address *" required maxlength="255">
                    </div>

                    <!-- Submit Button -->
                    <div class="md:col-span-3">
                        <button type="submit" id="btnNewsletterSubmit" class="w-full rounded-full py-3 px-4 font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-md flex items-center justify-center gap-2 text-sm transition-all cursor-pointer">
                            <span>Join Club</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Consent & Privacy Disclaimer -->
                <div class="flex items-center gap-2 pt-2">
                    <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer" type="checkbox" name="consent" id="newsletterConsent" required checked>
                    <label class="text-xs text-slate-400 cursor-pointer" for="newsletterConsent">
                        I agree to receive personalized newsletters and travel offers. Unsubscribe easily at any time.
                    </label>
                </div>

                <!-- Status Alert Box -->
                <div class="hidden" id="newsletterStatusBox">
                    <div class="rounded-xl py-2 px-3 text-xs flex items-center gap-2" id="newsletterAlert"></div>
                </div>
            </form>
        </div>

        <!-- Trust Badges -->
        <div class="flex flex-wrap items-center justify-center gap-6 mt-6 text-xs text-slate-400">
            <div class="flex items-center gap-1.5"><i class="bi bi-shield-check text-emerald-400"></i><span>Zero spam guarantee</span></div>
            <div class="flex items-center gap-1.5"><i class="bi bi-lock-fill text-amber-400"></i><span>100% Privacy protected</span></div>
            <div class="flex items-center gap-1.5"><i class="bi bi-check2-circle text-slate-400"></i><span>Instant one-click unsubscribe</span></div>
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
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Subscribing...';
        statusBox.classList.add('hidden');

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
        statusBox.classList.remove('hidden');
        if (type === 'success') {
            alertBox.className = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-xl py-2.5 px-3.5 text-xs flex items-center gap-2';
            alertBox.innerHTML = `<i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i> <span>${msg}</span>`;
        } else {
            alertBox.className = 'bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-xl py-2.5 px-3.5 text-xs flex items-center gap-2';
            alertBox.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-rose-400 text-sm"></i> <span>${msg}</span>`;
        }
    }
});
</script>
@endpush
@endif
