(function(){'use strict';
const App={
    currentStep:1,
    selectedTier:null,
    selectedPrice:0,
    selectedAddons:[],
    preselectedTierId:null,
    preselectedTourId:null,

    init(){
        this.initUTM();
        this.initHeader();
        this.initMobile();
        this.initModal();
        this.initBooking();
        this.initPaymentOptions();
        this.initForms();
        this.initQty();
        this.initFAQ();
        this.initTourSidebar();
        this.initStickySidebar();
        this.initHorizontalTabs();
        this.initTooltips();
        this.initWhatsApp();
        this.initTracking();
        this.initDateCards();
        this.initEmailVerification();
        this.initLegalModal();
        this.initSafariMatcher();
        this.initSunsetWidget();
        this.initCurrency();
        this.initPhoneInputs();
    },

    currency: {
        code: localStorage.getItem('dunes_currency') || 'AED',
        rates: (window.DunesRates && typeof window.DunesRates === 'object') ? Object.assign({
            'AED': 1.0,
            'USD': 0.2723,
            'EUR': 0.2510,
            'GBP': 0.2150,
            'SAR': 1.0210,
            'INR': 22.85
        }, window.DunesRates) : {
            'AED': 1.0,
            'USD': 0.2723,
            'EUR': 0.2510,
            'GBP': 0.2150,
            'SAR': 1.0210,
            'INR': 22.85
        },
        symbols: {
            'AED': 'AED',
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'SAR': 'SAR',
            'INR': '₹'
        },
        flags: {
            'AED': '\uD83C\uDDE6\uD83C\uDDEA',
            'USD': '\uD83C\uDDFA\uD83C\uDDF8',
            'EUR': '\uD83C\uDDEA\uD83C\uDDFA',
            'GBP': '\uD83C\uDDEC\uD83C\uDDE7',
            'SAR': '\uD83C\uDDF8\uD83C\uDDE6',
            'INR': '\uD83C\uDDEE\uD83C\uDDF3'
        }
    },

    initCurrency() {
        if (window.DunesRates && typeof window.DunesRates === 'object') {
            this.currency.rates = Object.assign({}, this.currency.rates, window.DunesRates);
        }
        const saved = localStorage.getItem('dunes_currency') || 'AED';
        this.setCurrency(saved, false);

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.currency-option');
            if (!btn) return;
            e.preventDefault();
            const cur = btn.dataset.currency;
            if (cur) {
                this.setCurrency(cur, true);
            }
        });
    },

    setCurrency(code, showToast = false) {
        if (!this.currency.rates[code]) return;
        this.currency.code = code;
        localStorage.setItem('dunes_currency', code);
        try {
            document.cookie = `dunes_currency=${encodeURIComponent(code)};path=/;max-age=31536000;SameSite=Lax`;
        } catch (e) {}

        const flag = this.currency.flags[code] || '';
        const symbol = this.currency.symbols[code] || code;

        document.querySelectorAll('.current-currency-flag').forEach(el => el.textContent = flag);
        document.querySelectorAll('.current-currency-code').forEach(el => el.textContent = code);

        document.querySelectorAll('.currency-option').forEach(btn => {
            const isMatch = btn.dataset.currency === code;
            btn.classList.toggle('active', isMatch);
            const check = btn.querySelector('.checkmark');
            if (check) {
                check.classList.toggle('d-none', !isMatch);
            }
        });

        // Convert and reflect all prices across the site
        this.convertAllPrices(code);

        if (typeof this.updateTotal === 'function') {
            this.updateTotal();
        }

        if (showToast) {
            this.toast(`Display currency set to ${code} (${symbol})`, 'success');
        }

        try {
            window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency: code, symbol: symbol } }));
        } catch (e) {}
    },

    formatPriceHtml(aedAmount, isOldPrice = false, showAedSuffix = true, isAddon = false) {
        const aed = parseFloat(aedAmount) || 0;
        const code = this.currency?.code || 'AED';
        const prefix = isAddon ? '+' : '';

        if (code === 'AED') {
            const formatted = Number(aed).toLocaleString('en-US', {
                minimumFractionDigits: (aed % 1 !== 0) ? 2 : 0,
                maximumFractionDigits: 2
            });
            return `${prefix}AED ${formatted}`;
        }

        const rate = this.currency?.rates?.[code] || 1;
        const symbol = this.currency?.symbols?.[code] || code;
        const converted = aed * rate;

        let formattedForeign;
        if (code === 'INR') {
            formattedForeign = `${prefix}${symbol}${Math.round(converted).toLocaleString('en-US')}`;
        } else if (code === 'SAR') {
            formattedForeign = `${prefix}${Math.round(converted).toLocaleString('en-US')} SAR`;
        } else {
            if (converted >= 10) {
                formattedForeign = `${prefix}${symbol}${Math.round(converted).toLocaleString('en-US')}`;
            } else {
                formattedForeign = `${prefix}${symbol}${Number(converted).toFixed(2)}`;
            }
        }

        if (isOldPrice) {
            return formattedForeign;
        }

        if (showAedSuffix) {
            const baseStr = Number(aed).toLocaleString('en-US', {
                minimumFractionDigits: (aed % 1 !== 0) ? 2 : 0,
                maximumFractionDigits: 2
            });
            return `${formattedForeign} <small class="text-muted fw-normal" style="font-size: 0.75em; letter-spacing: 0;">(${prefix}AED ${baseStr})</small>`;
        }

        return formattedForeign;
    },

    convertAllPrices(code) {
        if (!this.currency || !this.currency.rates) return;
        const targetCode = code || this.currency.code || 'AED';

        // 1. Update elements with explicit [data-aed]
        document.querySelectorAll('[data-aed]').forEach(el => {
            const aed = parseFloat(el.dataset.aed);
            if (isNaN(aed) || aed <= 0) return;

            const isOld = el.classList.contains('text-decoration-line-through') || 
                          el.classList.contains('old') || 
                          el.classList.contains('rc-old-price');
            const isAddon = el.dataset.isAddon === 'true' || el.textContent.trim().startsWith('+');
            const noSub = el.dataset.noSub === 'true' || isOld;

            el.innerHTML = this.formatPriceHtml(aed, isOld, !noSub, isAddon);
        });

        // 2. Auto-scan elements with price classes that might not yet have data-aed initialized
        const priceSelectors = [
            '.rc-cur-price', '.rc-old-price', '.tier-card-price .current', '.tier-card-price .old',
            '.package-option .text-primary', '.package-option .text-secondary'
        ];
        document.querySelectorAll(priceSelectors.join(',')).forEach(el => {
            if (!el.dataset.aed) {
                const txt = el.textContent.trim();
                const match = txt.match(/AED\s*([0-9,]+(?:\.[0-9]{1,2})?)/i) || txt.match(/([0-9,]+(?:\.[0-9]{1,2})?)/);
                if (match) {
                    const val = parseFloat(match[1].replace(/,/g, ''));
                    if (!isNaN(val) && val > 0) {
                        el.dataset.aed = val;
                        const isOld = el.classList.contains('text-decoration-line-through') || el.classList.contains('old') || el.classList.contains('rc-old-price');
                        el.innerHTML = this.formatPriceHtml(val, isOld, !isOld);
                    }
                }
            }
        });

        // 3. Update modal totals if available
        if (typeof this.updateTotal === 'function') {
            this.updateTotal();
        }
    },

    formatDisplayPrice(aedAmount) {
        const aed = parseFloat(aedAmount) || 0;
        const code = this.currency?.code || 'AED';
        const formattedAED = 'AED ' + Number(aed).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (code === 'AED') {
            return formattedAED;
        }

        const rate = this.currency?.rates?.[code] || 1;
        const converted = aed * rate;
        const symbol = this.currency?.symbols?.[code] || code;
        let formattedForeign;
        if (code === 'INR' || code === 'SAR') {
            formattedForeign = `${symbol} ${Number(converted).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            formattedForeign = `${symbol}${Number(converted).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }

        return `${formattedForeign} <span class="text-muted fw-normal small" style="font-size: 0.85em;">(~ ${formattedAED})</span>`;
    },

    initLegalModal() {
        const modal = document.getElementById('legalModal');
        if(!modal) return;

        const title = document.getElementById('legalModalTitle');
        const content = document.getElementById('legalModalContent');
        const loader = document.getElementById('legalModalLoader');
        const checkbox = document.getElementById('legalModalAgree');
        let currentTriggerCheckbox = null;

        document.addEventListener('click', (e) => {
            if(e.target.matches('.legal-link')) {
                e.preventDefault();
                const type = e.target.dataset.type;
                const pageTitle = type === 'terms-condition' ? 'Terms & Conditions' : 'Privacy Policy';

                const form = e.target.closest('form');
                if(form) {
                    currentTriggerCheckbox = form.querySelector('input[type="checkbox"][required]');
                }

                title.textContent = pageTitle;
                content.innerHTML = '';
                loader.classList.remove('d-none');
                checkbox.checked = currentTriggerCheckbox ? currentTriggerCheckbox.checked : false;

                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                fetch('/ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: `action=get_legal_content&type=${type}&_token=${encodeURIComponent(csrfToken)}`
                })
                .then(r => r.text())
                .then(t => { try { return JSON.parse(t.replace(/^\uFEFF+/, '').trim()); } catch(e){ throw e; } })
                .then(data => {
                    loader.classList.add('d-none');
                    if(data.success) {
                        content.innerHTML = data.html;
                    } else {
                        content.innerHTML = '<p class="text-danger text-center">Failed to load content.</p>';
                    }
                })
                .catch(() => {
                    loader.classList.add('d-none');
                    content.innerHTML = '<p class="text-danger text-center">Network error.</p>';
                });
            }
        });

        checkbox.addEventListener('change', () => {
            if(currentTriggerCheckbox) {
                currentTriggerCheckbox.checked = checkbox.checked;

                currentTriggerCheckbox.dispatchEvent(new Event('change'));

                if(checkbox.checked) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if(bsModal) {
                        setTimeout(() => bsModal.hide(), 300);
                    }
                }
            }
        });
    },

    initValidation(){},

    initEmailVerification(){
        const inputs = document.querySelectorAll('input[type="email"]');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        inputs.forEach(input => {
            if(input.id !== 'bookingEmail' && input.id !== 'email') return;

            const parent = input.parentElement;
            if(!parent.classList.contains('email-verify-wrapper')){
                parent.classList.add('email-verify-wrapper');
            }
            if(!parent.querySelector('.email-verify-loader')){
                const loader = document.createElement('span');
                loader.className = 'email-verify-loader';
                loader.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span>';
                parent.appendChild(loader);
            }

            input.addEventListener('blur', async () => {
                const email = input.value.trim();
                input.classList.remove('shake-field', 'field-processing');

                if(!email) {
                    this.resetFieldState(input);
                    return;
                }

                if(!emailRegex.test(email)) {
                    this.showError(input, true);
                    return;
                }

                input.classList.remove('field-valid', 'field-invalid');
                input.classList.add('field-processing');
                parent.classList.add('email-processing');
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                    const res = await fetch('/ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: `action=check_email_status&email=${encodeURIComponent(email)}&_token=${encodeURIComponent(csrfToken)}`
                    });
                    const raw = await res.text();
                    const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

                    if(data.success && data.verified){
                        this.markEmailVerified(input);
                    } else {
                        this.showVerifyButton(input);
                        this.showError(input, true);
                    }
                } catch(e) {
                    console.error('Email check failed', e);
                } finally {
                    input.classList.remove('field-processing');
                    parent.classList.remove('email-processing');
                }
            });

            input.addEventListener('input', () => {
                this.resetFieldState(input);
            });
        });
    },

    resetFieldState(input){
        input.classList.remove('is-verified', 'shake-field', 'field-valid', 'field-invalid', 'field-processing');
        const parent = input.parentElement;
        if(parent) parent.classList.remove('email-processing');

            if(parent.classList.contains('email-verify-wrapper')){
            const btn = parent.querySelector('.email-verify-btn');
            const otp = parent.nextElementSibling;

            if(btn) btn.remove();
            if(otp && otp.classList.contains('otp-field-wrapper')) otp.remove();
        }
    },

    showError(input, disableSubmit = false){
        input.classList.remove('field-processing');
        const parent = input.parentElement;
        if(parent) parent.classList.remove('email-processing');
        input.classList.add('field-invalid');
        input.classList.remove('is-verified', 'field-valid');

        void input.offsetWidth;
        input.classList.add('shake-field');

        setTimeout(() => {
            input.classList.remove('shake-field');
        }, 500);

        if(disableSubmit){
            this.toggleSubmit(input.form, false);
        }
    },

    markEmailVerified(input){
        const parent = input.parentElement;
        this.resetFieldState(input);

        input.classList.add('is-verified', 'field-valid');

        this.toggleSubmit(input.form, true);
    },

    showVerifyButton(input){
        const parent = input.parentElement;
        if(parent.querySelector('.email-verify-btn')) return;
        if(input.classList.contains('is-verified')) return;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-primary email-verify-btn';
        btn.innerHTML = 'Verify';

        btn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.startVerification(input);
        };

        parent.appendChild(btn);

        this.toggleSubmit(input.form, false);
    },

    async startVerification(input){
        const email = input.value.trim();
        const parent = input.parentElement;
        const btn = parent.querySelector('.email-verify-btn');

        if(btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;
        }

        input.classList.add('field-processing');
        parent.classList.add('email-processing');
        try {
            const form = input.closest('form');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('[name="_token"]')?.value || form?.querySelector('[name="csrf_token"]')?.value || window.CSRF_TOKEN || '';
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf
                },
                body: `action=send_otp&email=${encodeURIComponent(email)}&csrf_token=${encodeURIComponent(csrf)}&_token=${encodeURIComponent(csrf)}`
            });
            const raw = await res.text();
            const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            if(data.success){
                this.toast('OTP sent to ' + email, 'success');
                this.showOtpInput(input);
                if(btn) btn.remove();
            } else {
                const msg = data.message || 'Could not send OTP. Please try again or use WhatsApp to book.';
                this.toast(msg, 'error');
                if(btn) {
                    btn.innerHTML = 'Retry';
                    btn.disabled = false;
                }
            }
        } catch(e) {
            this.toast('Network error — please check your connection and try again.', 'error');
            if(btn) {
                btn.innerHTML = 'Retry';
                btn.disabled = false;
            }
        } finally {
            input.classList.remove('field-processing');
            parent.classList.remove('email-processing');
        }
    },

    showOtpInput(input){
        const parent = input.parentElement;
        if(parent.nextElementSibling?.classList.contains('otp-field-wrapper')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'otp-field-wrapper';
        wrapper.innerHTML = `
            <div class="small fw-bold text-muted mb-2">Enter the 6-digit code sent to your email <span class="text-dark">(valid 5 min)</span></div>
            <div class="d-flex gap-2">
                <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center fw-bold fs-5" maxlength="6" placeholder="000000" style="letter-spacing: 4px;">
                <button type="button" class="btn btn-success fw-bold px-3">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-danger d-none otp-error"></small>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-muted resend-otp-btn">Resend OTP</button>
            </div>
        `;

        parent.after(wrapper);

        const otpInput = wrapper.querySelector('input');
        const submitBtn = wrapper.querySelector('button.btn-success');
        const resendBtn = wrapper.querySelector('.resend-otp-btn');
        const errorEl = wrapper.querySelector('.otp-error');

        otpInput.focus();

        // Allow only numeric input
        otpInput.addEventListener('keydown', (e) => {
            const allowed = ['Backspace','Tab','Enter','Delete','ArrowLeft','ArrowRight','Home','End'];
            if(!allowed.includes(e.key) && !/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
        otpInput.addEventListener('input', () => {
            otpInput.value = otpInput.value.replace(/[^0-9]/g, '');
        });

        const verify = async () => {
            const code = otpInput.value.trim();
            if(code.length < 6) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            const form = input.closest('form');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('[name="_token"]')?.value || form?.querySelector('[name="csrf_token"]')?.value || window.CSRF_TOKEN || '';

            try {
                const res = await fetch('/ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: `action=verify_otp&email=${encodeURIComponent(input.value)}&otp=${code}&csrf_token=${encodeURIComponent(csrf)}&_token=${encodeURIComponent(csrf)}`
                });
                const raw = await res.text();
                const data = JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

                if(data.success){
                    this.toast('Email verified successfully', 'success');
                    this.markEmailVerified(input);
                } else {
                    errorEl.textContent = data.message || 'Invalid OTP';
                    errorEl.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-arrow-right"></i>';
                }
            } catch(e) {
                errorEl.textContent = 'Verification failed';
                errorEl.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-arrow-right"></i>';
            }
        };

        submitBtn.addEventListener('click', verify);
        otpInput.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') {
                e.preventDefault();
                verify();
            }
        });

        let cooldown = 180; // 3 minutes
        const updateTimer = () => {
            if(cooldown > 0){
                resendBtn.textContent = `Resend in ${Math.floor(cooldown/60)}:${String(cooldown%60).padStart(2,'0')}`;
                resendBtn.disabled = true;
                resendBtn.style.pointerEvents = 'none';
                resendBtn.classList.add('text-muted');
                cooldown--;
                setTimeout(updateTimer, 1000);
            } else {
                resendBtn.textContent = 'Resend OTP';
                resendBtn.disabled = false;
                resendBtn.style.pointerEvents = 'auto';
                resendBtn.classList.remove('text-muted');
                resendBtn.classList.add('text-primary');
            }
        };
        updateTimer();

        resendBtn.addEventListener('click', async () => {
            wrapper.remove();
            this.startVerification(input);
        });
    },

    toggleSubmit(form, enable){
        if(!form) return;
        const btn = form.querySelector('[type="submit"]');
        if(!btn) return;

        if(enable){
            btn.disabled = false;

            const wrapper = btn.closest('.submit-tooltip-wrapper');
            if(wrapper){
                const tooltip = bootstrap.Tooltip.getInstance(wrapper);
                if(tooltip) tooltip.dispose();

                wrapper.replaceWith(btn);
            }
        } else {

            if(!btn.closest('.submit-tooltip-wrapper')){
                const wrapper = document.createElement('div');
                wrapper.className = 'd-inline-block submit-tooltip-wrapper';
                wrapper.setAttribute('tabindex', '0'); // Make it focusable for tooltip
                wrapper.setAttribute('data-bs-toggle', 'tooltip');
                wrapper.setAttribute('data-bs-placement', 'top');
                wrapper.setAttribute('title', 'Due to spam and security, email verification is must. We apologize for the inconvenience and appreciate your time in verifying your email address.');

                btn.parentNode.insertBefore(wrapper, btn);
                wrapper.appendChild(btn);

                new bootstrap.Tooltip(wrapper);
            }
            btn.disabled = true;
        }
    },

    initTracking(){
        document.addEventListener('click', e => {
            const link = e.target.closest('a');
            if(!link) return;
            const href = link.getAttribute('href');
            if(!href) return;

            if(href.startsWith('tel:')){
                if(window.dataLayer) window.dataLayer.push({ event: 'contact', method: 'phone' });
            }
            else if(href.startsWith('mailto:')){
                if(window.dataLayer) window.dataLayer.push({ event: 'contact', method: 'email' });
            }
        });
    },

    initDateCards(){
        const wrapper = document.getElementById('dateCardsWrapper');
        const input = document.getElementById('bookingDate');
        if(!wrapper || !input) return;

        const today = new Date();


        let html = '';
        const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        for(let i=1; i<=30; i++){
            const d = new Date(today);
            d.setDate(today.getDate() + i);

            const offset = d.getTimezoneOffset();
            const localDate = new Date(d.getTime() - (offset*60*1000));
            const dateStr = localDate.toISOString().split('T')[0];

            const dayName = days[d.getDay()];
            const dayNum = d.getDate();
            const monthName = months[d.getMonth()];

            html += `<div class="date-card" data-date="${dateStr}">
                <div class="day">${dayName}</div>
                <div class="date">${dayNum}</div>
                <div class="month">${monthName}</div>
            </div>`;
        }

        wrapper.innerHTML = html;

        wrapper.querySelectorAll('.date-card[data-date]').forEach(card => {
            card.addEventListener('click', () => {
                wrapper.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                input.value = card.dataset.date;
                input.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });

        document.getElementById('calendarTrigger')?.addEventListener('click', () => {
            try {
                input.showPicker();
            } catch(e) {
                input.click();
            }
        });

        document.getElementById('datePrev')?.addEventListener('click', () => {
            wrapper.scrollBy({left: -220, behavior: 'smooth'});
        });
        document.getElementById('dateNext')?.addEventListener('click', () => {
            wrapper.scrollBy({left: 220, behavior: 'smooth'});
        });

        input.addEventListener('change', () => {
            const val = input.value;
            wrapper.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
            const matchingCard = wrapper.querySelector(`.date-card[data-date="${val}"]`);
            if(matchingCard) {
                matchingCard.classList.add('selected');
                matchingCard.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'center'});
            }
        });
    },

    initPhoneInputs(){
        if(typeof window.intlTelInput === 'undefined') return;

        const inputs = document.querySelectorAll('input[type="tel"], #bookingPhone, #waPhone, #welcomePhone');
        inputs.forEach(input => {
            if(!input || input.dataset.itiInitialized === 'true') return;
            input.dataset.itiInitialized = 'true';

            try {
                const iti = window.intlTelInput(input, {
                    utilsScript: '/assets/vendor/intl-tel-input/26.0.6/build/utils.js',
                    separateDialCode: true,
                    initialCountry: 'ae',
                    preferredCountries: ['ae', 'sa', 'om', 'kw', 'qa', 'bh', 'gb', 'us', 'in', 'de', 'ru'],
                    autoPlaceholder: 'aggressive',
                    customPlaceholder: function(selectedCountryPlaceholder) {
                        return selectedCountryPlaceholder ? selectedCountryPlaceholder : "50 123 4567";
                    }
                });

                input._iti = iti;

                input.addEventListener('countrychange', () => {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });

                input.addEventListener('blur', () => {
                    if (input.value.trim() !== '') {
                        if (typeof iti.isValidNumber === 'function' && !iti.isValidNumber()) {
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid') && typeof iti.isValidNumber === 'function' && iti.isValidNumber()) {
                        input.classList.remove('is-invalid');
                    }
                });
            } catch (err) {
                console.warn('intlTelInput init error:', err);
            }
        });
    },

    initWhatsApp(){
        const modal=document.getElementById('whatsappModal');
        if(!modal)return;
        const form=document.getElementById('whatsappForm');
        const startBtn=document.getElementById('startChatBtn');
        const nameInp=document.getElementById('waName');
        const phoneInp=document.getElementById('waPhone');

        this.initPhoneInputs();

        const tourNameInp=document.getElementById('waTourName');
        const pageUrlInp=document.getElementById('waPageUrl');

        const check=()=>{
            startBtn.disabled=!(nameInp.value.trim()&&phoneInp.value.trim());
        };

        nameInp.addEventListener('input',check);
        phoneInp.addEventListener('input',check);

        const getModal=()=>typeof bootstrap!=='undefined'&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(modal):null;

        modal.addEventListener('shown.bs.modal',()=>{
            this.initPhoneInputs();
        });

        modal.addEventListener('hidden.bs.modal',()=>{
            form.reset();
            check();
        });

        const syncGpsData = () => {
            const map = {
                'gpsLat': 'waGpsLat',
                'gpsLng': 'waGpsLng',
                'gpsAccuracy': 'waGpsAccuracy',
                'gpsTimestamp': 'waGpsTimestamp',
                'gpsConsent': 'waGpsConsent',
                'gpsSource': 'waGpsSource'
            };

            for(const [srcId, destId] of Object.entries(map)){
                const src = document.getElementById(srcId);
                const dest = document.getElementById(destId);
                if(src && dest && src.value) {
                    dest.value = src.value;
                }
            }
        };

        document.addEventListener('click', e => {
            const el = e.target.closest('a, button, .fab-whatsapp, .btn-circle-whatsapp, .btn-whatsapp-animated');
            if (!el) return;
            const href = el.getAttribute ? (el.getAttribute('href') || '') : '';
            const isWa = href.includes('wa.me') || href.includes('api.whatsapp.com') ||
                         el.classList.contains('fab-whatsapp') || el.classList.contains('btn-circle-whatsapp') ||
                         el.classList.contains('btn-whatsapp-animated') || el.closest('.fab-whatsapp, .btn-circle-whatsapp');
            if (!isWa) return;

            e.preventDefault();
            e.stopPropagation();

            let tourName = '';
            if (el.dataset?.tourName) {
                tourName = el.dataset.tourName;
            } else if (location.pathname.includes('/tours/') || document.querySelector('.tour-hero')) {
                const h1 = document.querySelector('h1');
                if (h1) tourName = h1.innerText.trim();
            }

            this.openWhatsApp(tourName, href);
        });
    },

    openWhatsApp(tourName = '', directHref = '') {
        const modal = document.getElementById('whatsappModal');
        const formEnabled = (window.WHATSAPP_FORM_ENABLED === '1');
        const defaultNum = (window.WHATSAPP_NUMBER || '971502456056').replace(/[^0-9]/g, '');
        const defaultMsg = encodeURIComponent(tourName ? `Hello, I would like to inquire about ${tourName}.` : 'Hello, I would like to inquire about Dubai desert safaris.');
        const fallbackUrl = directHref || `https://wa.me/${defaultNum}?text=${defaultMsg}`;

        if (!formEnabled || !modal) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
            const fd = new FormData();
            fd.append('action', 'logWhatsApp');
            fd.append('csrf_token', csrfToken);
            fd.append('_token', csrfToken);
            fd.append('name', 'N/A');
            fd.append('phone', 'N/A');
            fd.append('tour_name', tourName || 'General Inquiry');
            fd.append('page_url', window.location.href);

            if (typeof window.gtag === 'function') {
                window.gtag('event', 'conversion', { 'send_to': 'AW-17859624049/eR3SCLimtvobEPH4kMRC' });
            }
            if (window.dataLayer) {
                window.dataLayer.push({
                    event: 'generate_lead',
                    conversion_type: 'whatsapp',
                    conversion_label: 'eR3SCLimtvobEPH4kMRC'
                });
            }

            fetch('/ajax.php', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd
            })
            .then(r => r.text())
            .then(t => { try { return JSON.parse(t.replace(/^\uFEFF+/, '').trim()); } catch (e) { throw e; } })
            .then(d => {
                const url = d.redirect_url || fallbackUrl;
                window.open(url, '_blank');
            }).catch(() => {
                window.open(fallbackUrl, '_blank');
            });
            return;
        }

        const tourNameInp = document.getElementById('waTourName');
        const pageUrlInp = document.getElementById('waPageUrl');
        if (tourNameInp) tourNameInp.value = tourName;
        if (pageUrlInp) pageUrlInp.value = window.location.href;

        const map = {
            'gpsLat': 'waGpsLat',
            'gpsLng': 'waGpsLng',
            'gpsAccuracy': 'waGpsAccuracy',
            'gpsTimestamp': 'waGpsTimestamp',
            'gpsConsent': 'waGpsConsent',
            'gpsSource': 'waGpsSource'
        };
        for (const [srcId, destId] of Object.entries(map)) {
            const src = document.getElementById(srcId);
            const dest = document.getElementById(destId);
            if (src && dest && src.value) dest.value = src.value;
        }

        const startBtn = document.getElementById('startChatBtn');
        const nameInp = document.getElementById('waName');
        const phoneInp = document.getElementById('waPhone');
        if (startBtn && nameInp && phoneInp) {
            startBtn.disabled = !(nameInp.value.trim() && phoneInp.value.trim());
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        } else {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
    },

    initUTM(){
        const p=new URLSearchParams(location.search);
        ['utm_source','utm_medium','utm_campaign'].forEach(k=>{
            const v=p.get(k)||sessionStorage.getItem(k)||'';
            if(p.get(k))sessionStorage.setItem(k,p.get(k));
            const el=document.getElementById(k.replace('utm_','utm').replace(/_([a-z])/g,(_,l)=>l.toUpperCase()));
            if(el)el.value=v;
        });
    },

    initHeader(){
        const h=document.getElementById('header');
        if(!h)return;

        const updateHeight = () => {
            const banner = document.getElementById('dunesTopPromoBanner');
            const hHeight = h.offsetHeight || 72;
            document.documentElement.style.setProperty('--header-h', hHeight + 'px');
            if (banner && banner.offsetHeight) {
                document.documentElement.style.setProperty('--promo-banner-h', banner.offsetHeight + 'px');
            } else {
                document.documentElement.style.setProperty('--promo-banner-h', '0px');
            }
        };

        updateHeight();
        window.addEventListener('resize', updateHeight, {passive: true});
        window.addEventListener('orientationchange', updateHeight, {passive: true});

        if (typeof ResizeObserver !== 'undefined') {
            try {
                new ResizeObserver(updateHeight).observe(h);
            } catch (e) {}
        }

        let ly=0;
        window.addEventListener('scroll',()=>{
            const y=window.pageYOffset;
            if(y>100)h.style.transform=y>ly?'translateY(-100%)':'translateY(0)';
            else h.style.transform='translateY(0)';
            ly=y;
        },{passive:true});
    },

    initMobile(){
        const sheet=document.getElementById('mobileSheet');
        const toggle=document.getElementById('menuToggle');
        const close=document.getElementById('sheetClose');
        const overlay=document.getElementById('sheetOverlay');

        if(!sheet)return;

        const open=()=>{
            sheet.classList.add('active');
            document.body.style.overflow='hidden';
        };
        const cls=()=>{
            sheet.classList.remove('active');
            document.body.style.overflow='';
        };

        toggle?.addEventListener('click',open);
        close?.addEventListener('click',cls);
        overlay?.addEventListener('click',cls);
        sheet.querySelectorAll('a').forEach(a=>a.addEventListener('click',cls));
    },

    initModal(){
        const modal=document.getElementById('bookingModal');
        if(!modal)return;

        const getModal=()=>typeof bootstrap!=='undefined'&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(modal):null;

        modal.addEventListener('shown.bs.modal',()=>{
            this.currentStep=1;
            this.initPhoneInputs();

            const titleEl = document.getElementById('bookingModalTitle');
            const wrapper = document.getElementById('tourSelectWrapper');
            const tourSel = document.getElementById('bookingTour');

            if(this.preselectedTourId){
                if(tourSel && tourSel.value !== this.preselectedTourId){
                    tourSel.value = this.preselectedTourId;
                    this.loadTiers(this.preselectedTourId);
                } else if (tourSel && tourSel.value === this.preselectedTourId) {

                    const tierContainer = document.getElementById('tierCards');
                    if(tierContainer && (!tierContainer.children.length || tierContainer.querySelector('.tier-placeholder'))) {
                        this.loadTiers(this.preselectedTourId);
                    } else {

                         if(this.preselectedTierId) {
                             const card = tierContainer.querySelector(`.tier-card[data-tier="${this.preselectedTierId}"]`);
                             if(card) this.selectTier(card);
                         }
                    }
                }

                const tourName = (tourSel && tourSel.selectedIndex >= 0 && tourSel.options[tourSel.selectedIndex]) ? tourSel.options[tourSel.selectedIndex].text : 'Book Your Adventure';
                if(titleEl) titleEl.textContent = tourName;
                if(wrapper) wrapper.classList.add('d-none');
            } else {

                if(titleEl) titleEl.textContent = 'Book Your Adventure';
                if(wrapper) wrapper.classList.remove('d-none');
            }

            this.updateStep();
        });

        modal.addEventListener('hidden.bs.modal',()=>{
            this.resetForm();
            this.preselectedTierId=null;
            this.preselectedTourId=null;
        });

        document.querySelectorAll('[data-action="open-booking"]').forEach(el=>el.addEventListener('click',e=>{
            e.preventDefault();
            this.preselectedTierId=el.dataset.tier||el.dataset.tierId||null;
            this.preselectedTourId=el.dataset.tour||el.dataset.tourId||null;

            const m=getModal();
            if(m)m.show();
            else{
                modal.classList.add('active');
                document.body.style.overflow='hidden';
                this.currentStep=1;

                if(this.preselectedTourId) {
                    const tourSel = document.getElementById('bookingTour');
                    if(tourSel) {
                        tourSel.value = this.preselectedTourId;
                        this.loadTiers(this.preselectedTourId);
                        const wrapper = document.getElementById('tourSelectWrapper');
                        if(wrapper) wrapper.classList.add('d-none');
                        const titleEl = document.getElementById('bookingModalTitle');
                        if(titleEl) titleEl.textContent = (tourSel && tourSel.selectedIndex >= 0 && tourSel.options[tourSel.selectedIndex]) ? tourSel.options[tourSel.selectedIndex].text : 'Book Your Adventure';
                    }
                }
                this.updateStep();
            }
        }));
    },

    initBooking(){
        const tourSelect=document.getElementById('bookingTour');
        const next=document.getElementById('nextStep');
        const prev=document.getElementById('headerBackBtn');
        const edit=document.getElementById('editStep1');
        const dateInput = document.getElementById('bookingDate');
        const locationInput = document.getElementById('bookingLocation');
        const tierInput = document.getElementById('selectedTier');
        const btnWrapper = document.getElementById('continueBtnWrapper');
        let tooltipInstance = null;

        if(btnWrapper && typeof bootstrap !== 'undefined' && bootstrap.Tooltip){
            tooltipInstance = new bootstrap.Tooltip(btnWrapper, {
                trigger: 'hover',
                placement: 'top'
            });
        }

        const triggerShakeAndHighlight = (el) => {
            if (!el) return;
            el.classList.remove('shake-field', 'missing-field-highlight');
            void el.offsetWidth; // Force reflow
            el.classList.add('shake-field', 'missing-field-highlight');
            setTimeout(() => {
                el.classList.remove('shake-field');
            }, 650);
            setTimeout(() => {
                el.classList.remove('missing-field-highlight');
            }, 1800);
        };

        const validateStep1 = (shakeIfInvalid = false) => {
            if(!next) return false;
            const tourWrapper = document.getElementById('tourSelectWrapper');
            const tourIsRequired = !tourWrapper || !tourWrapper.classList.contains('d-none');
            const tour = tourSelect ? tourSelect.value : '';
            const tier = tierInput ? tierInput.value : '';
            const date = dateInput ? dateInput.value : '';
            const loc = locationInput ? locationInput.value.trim() : '';

            const missing = [];
            const missingElements = [];

            if(tourIsRequired && !tour) {
                missing.push('Tour');
                const tourBox = document.getElementById('tourSelectWrapper') || tourSelect;
                missingElements.push(tourBox);
            }
            if(!tier) {
                missing.push('Package');
                const tierBox = document.getElementById('tierCards')?.parentElement || document.getElementById('tierCards');
                missingElements.push(tierBox);
            }
            if(!date) {
                missing.push('Date');
                const dateBox = document.getElementById('dateCardsWrapper')?.parentElement || document.getElementById('dateCardsWrapper');
                missingElements.push(dateBox);
            }
            if(!loc) {
                missing.push('Pickup Location');
                const locBox = locationInput ? locationInput.closest('.booking-location-wrapper') || locationInput.closest('.booking-field-container') : null;
                missingElements.push(locBox || locationInput);
            }

            if(missing.length === 0) {
                next.classList.remove('btn-disabled-visual', 'opacity-50');
                if(btnWrapper) {
                    btnWrapper.removeAttribute('title');
                    btnWrapper.removeAttribute('data-bs-original-title');
                }
                if(tooltipInstance) {
                    tooltipInstance.hide();
                    tooltipInstance.disable();
                }
                return true;
            } else {
                next.classList.add('btn-disabled-visual');
                const msg = 'Please select: ' + missing.join(', ');

                if(btnWrapper) {
                    btnWrapper.setAttribute('data-bs-original-title', msg);
                    btnWrapper.setAttribute('title', msg);
                }

                if(tooltipInstance) {
                    tooltipInstance.enable();
                    if(typeof tooltipInstance.setContent === 'function') {
                        tooltipInstance.setContent({ '.tooltip-inner': msg });
                    }
                }

                if(shakeIfInvalid) {
                    missingElements.forEach(el => triggerShakeAndHighlight(el));

                    if(tooltipInstance) {
                        tooltipInstance.show();
                        setTimeout(() => {
                            if(tooltipInstance) tooltipInstance.hide();
                        }, 2500);
                    }

                    if(missingElements.length > 0 && missingElements[0]) {
                        missingElements[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }

                    this.toast(msg, 'error');
                }

                return false;
            }
        };

        this.validateStep1 = validateStep1;
        validateStep1(false);

        tourSelect?.addEventListener('change', () => {
            this.loadTiers(tourSelect.value);
            validateStep1(false);
        });

        tierInput?.addEventListener('change', () => validateStep1(false));

        dateInput?.addEventListener('change', () => validateStep1(false));
        dateInput?.addEventListener('input', () => validateStep1(false));

        locationInput?.addEventListener('input', () => validateStep1(false));
        locationInput?.addEventListener('change', () => validateStep1(false));

        const handleContinueClick = (e) => {
            if (this.currentStep === 1) {
                const isValid = validateStep1(false);
                if (!isValid) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    validateStep1(true);
                    return false;
                }
                this.nextStep();
            }
        };

        next?.addEventListener('click', handleContinueClick);
        btnWrapper?.addEventListener('click', (e) => {
            if (e.target !== next && !next.contains(e.target)) {
                handleContinueClick(e);
            }
        });
        prev?.addEventListener('click',()=>this.prevStep());
        edit?.addEventListener('click',(e)=>{
            e.preventDefault();
            this.currentStep = 1;
            this.updateStep();
        });

        document.getElementById('detectLocation')?.addEventListener('click',()=>this.detectLocation());
        
        if (locationInput) {
            const initOpenStreetMapAutocomplete = (locationInput) => {
                if (!locationInput || locationInput.dataset.osmInitialized === 'true') return;
                locationInput.dataset.osmInitialized = 'true';

                const popularLocations = [
                    { name: 'Dubai Marina', detail: 'Dubai Marina & JBR area, Dubai' },
                    { name: 'Downtown Dubai', detail: 'Burj Khalifa, Dubai Mall & Downtown area' },
                    { name: 'Palm Jumeirah', detail: 'Palm Jumeirah Island & Resorts, Dubai' },
                    { name: 'Business Bay', detail: 'Business Bay & Canal area, Dubai' },
                    { name: 'Deira Dubai', detail: 'Deira Old Town & Gold Souk area, Dubai' },
                    { name: 'Bur Dubai', detail: 'Bur Dubai & Al Fahidi Historic District' },
                    { name: 'Jumeirah Beach Residence (JBR)', detail: 'JBR Beach & Walk, Dubai' },
                    { name: 'Al Barsha', detail: 'Al Barsha & Mall of the Emirates area' },
                    { name: 'Jumeirah Lake Towers (JLT)', detail: 'JLT & Cluster towers area, Dubai' },
                    { name: 'Atlantis The Palm', detail: 'Crescent Rd, Palm Jumeirah, Dubai' },
                    { name: 'Dubai International Airport (DXB)', detail: 'Terminals 1, 2 & 3, Dubai' },
                    { name: 'Abu Dhabi City Center', detail: 'Corniche & Abu Dhabi Hotels' }
                ];

                let wrapper = locationInput.closest('.booking-location-wrapper') || locationInput.parentElement;
                if (getComputedStyle(wrapper).position === 'static') {
                    wrapper.style.position = 'relative';
                }

                const dropdown = document.createElement('div');
                dropdown.className = 'osm-autocomplete-dropdown shadow-lg rounded-3 border-0';
                dropdown.style.cssText = 'position:absolute; top:100%; left:0; right:0; z-index:1060; background:#ffffff; display:none; max-height:280px; overflow-y:auto; margin-top:6px; box-shadow:0 10px 30px rgba(0,0,0,0.15); border-radius:12px; border:1px solid rgba(246,144,68,0.25);';
                wrapper.appendChild(dropdown);

                let debounceTimer = null;

                const renderResults = (items) => {
                    dropdown.innerHTML = '';
                    if (!items || items.length === 0) {
                        dropdown.style.display = 'none';
                        return;
                    }

                    items.forEach((item) => {
                        const el = document.createElement('div');
                        el.className = 'osm-autocomplete-item p-3 text-start d-flex align-items-center gap-3 border-bottom border-light';
                        el.style.cssText = 'cursor:pointer; transition:all 0.15s ease; background:#ffffff;';
                        el.innerHTML = `
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px; height:34px; background:#fff4eb; color:#F69044;">
                                <i class="bi bi-geo-alt-fill" style="font-size:0.9rem;"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size:0.9rem;">${item.name}</div>
                                <div class="text-muted text-truncate" style="font-size:0.78rem;">${item.detail || 'Dubai, United Arab Emirates'}</div>
                            </div>
                        `;
                        el.addEventListener('mouseenter', () => { el.style.background = '#fff4eb'; });
                        el.addEventListener('mouseleave', () => { el.style.background = '#ffffff'; });
                        el.addEventListener('mousedown', (e) => {
                            e.preventDefault();
                            locationInput.value = item.name + (item.detail ? ', ' + item.detail : '');
                            locationInput.dispatchEvent(new Event('input', { bubbles: true }));
                            locationInput.dispatchEvent(new Event('change', { bubbles: true }));
                            dropdown.style.display = 'none';
                        });
                        dropdown.appendChild(el);
                    });

                    dropdown.style.display = 'block';
                };

                const showPopular = () => {
                    renderResults(popularLocations);
                };

                const searchPhoton = async (query) => {
                    try {
                        const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&lat=25.2048&lon=55.2708&limit=6`;
                        const res = await fetch(url);
                        if (!res.ok) return showPopular();
                        const data = await res.json();
                        if (data && data.features && data.features.length > 0) {
                            const results = data.features.map(f => {
                                const props = f.properties || {};
                                const name = props.name || props.street || query;
                                const parts = [props.district, props.city, props.country].filter(Boolean);
                                return {
                                    name: name,
                                    detail: parts.join(', ') || 'United Arab Emirates'
                                };
                            });
                            renderResults(results);
                        } else {
                            const filtered = popularLocations.filter(p => p.name.toLowerCase().includes(query.toLowerCase()) || p.detail.toLowerCase().includes(query.toLowerCase()));
                            renderResults(filtered.length ? filtered : [{ name: query, detail: 'Dubai, UAE' }]);
                        }
                    } catch (e) {
                        showPopular();
                    }
                };

                locationInput.addEventListener('focus', () => {
                    if (!locationInput.value.trim()) {
                        showPopular();
                    } else {
                        searchPhoton(locationInput.value.trim());
                    }
                });

                locationInput.addEventListener('input', () => {
                    const val = locationInput.value.trim();
                    clearTimeout(debounceTimer);
                    if (!val) {
                        showPopular();
                        return;
                    }
                    debounceTimer = setTimeout(() => {
                        searchPhoton(val);
                    }, 200);
                });

                document.addEventListener('click', (e) => {
                    if (!wrapper.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            };

            initOpenStreetMapAutocomplete(locationInput);
        }

        this.initDraftAutoSave();
    },

    initDraftAutoSave(){
        let draftTimer = null;
        let activeDraftId = null;

        const triggerDraftSave = () => {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(async () => {
                const name = document.getElementById('bookingName')?.value.trim();
                const email = document.getElementById('bookingEmail')?.value.trim();
                const phoneEl = document.getElementById('bookingPhone');
                const phone = (phoneEl && phoneEl._iti && typeof phoneEl._iti.getNumber === 'function' && phoneEl._iti.getNumber())
                    ? phoneEl._iti.getNumber()
                    : phoneEl?.value.trim();

                if ((name && name.length >= 2) || (email && email.includes('@')) || (phone && phone.length >= 7)) {
                    const tourId = document.getElementById('bookingTour')?.value;
                    const tierId = document.getElementById('selectedTier')?.value || this.selectedTier;
                    const date = document.getElementById('bookingDate')?.value;
                    const adults = document.getElementById('bookingAdults')?.value || 1;
                    const children = document.getElementById('bookingChildren')?.value || 0;
                    const location = document.getElementById('bookingLocation')?.value;
                    const baseTotal = this.calculateBaseTotal();

                    try {
                        const res = await fetch('/api/v1/booking/draft', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                draft_id: activeDraftId,
                                name: name,
                                email: email,
                                phone: phone,
                                tour_id: tourId,
                                tier_id: tierId,
                                date: date,
                                adults: adults,
                                children: children,
                                pickup_location: location,
                                subtotal: baseTotal,
                                total: baseTotal
                            })
                        });
                        const data = await res.json();
                        if (data.success && data.draft_id) {
                            activeDraftId = data.draft_id;
                        }
                    } catch (e) {}
                }
            }, 800);
        };

        ['bookingName', 'bookingEmail', 'bookingPhone'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', triggerDraftSave);
        });
    },

    initPaymentOptions(){
        const container=document.getElementById('paymentOptions');
        if(!container)return;
        const options=container.querySelectorAll('.payment-option');
        const methodInput=document.getElementById('paymentMethod');
        const select=(val)=>{
            options.forEach(o=>o.classList.toggle('selected',o.dataset.value===val));
            if(methodInput) methodInput.value=val;
            this.updateTotal();
        };
        options.forEach(o=>o.addEventListener('click',()=>select(o.dataset.value)));
        select(methodInput?.value||options[0]?.dataset.value||'cash');
    },

    async loadTiers(tourId){
        const container=document.getElementById('tierCards');
        const addons=document.getElementById('addonsSection');
        const addonList=document.getElementById('addonList');

        if(!container||!tourId){
            container.innerHTML='<div class="tier-placeholder">Select a tour to see packages</div>';
            if(addons) addons.style.display='none';
            this.selectedTier=null;
            const tInp = document.getElementById('selectedTier');
        if(tInp) { tInp.value=''; tInp.dispatchEvent(new Event('change', {bubbles: true})); }
            this.selectedPrice=0;
            this.selectedAddons=[];
            this.updateTotal();
            return;
        }

        container.innerHTML='<div class="tier-placeholder"><div class="spinner-border text-primary mb-2" role="status"></div><div>Loading packages...</div></div>';

        this.selectedTier=null;
        const tInp2 = document.getElementById('selectedTier');
        if(tInp2) { tInp2.value=''; tInp2.dispatchEvent(new Event('change', {bubbles: true})); }
        this.selectedPrice=0;
        this.selectedAddons=[];
        this.updateTotal();

        try{
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
            const res=await fetch('/ajax.php',{
                method:'POST',
                headers:{
                    'Content-Type':'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body:'action=getTiers&tour_id='+tourId+'&_token='+encodeURIComponent(csrfToken)
            });
            const raw=await res.text();
            const data=JSON.parse(raw.replace(/^\uFEFF+/, '').trim());

            if(data.tiers?.length){
                let h='';
                data.tiers.forEach(t=>{
                    const save=t.old_price>t.price?Math.round(((t.old_price-t.price)/t.old_price)*100):0;
                    h+=`<div class="tier-card${t.is_popular?' popular':''}" data-tier="${t.id}" data-price="${t.price}" data-name="${t.name}">
                        ${t.is_popular?'<div class="tier-popular-badge">Popular</div>':''}
                        <div class="tier-card-check"><i class="bi bi-check-lg"></i></div>
                        <div class="tier-card-inner">
                            <div class="tier-card-info">
                                <h4>${t.name}</h4>
                                <p>${t.description||''}</p>
                            </div>
                            <div class="tier-card-price">
                                ${save?`<div class="old" data-aed="${t.old_price}">AED ${t.old_price}</div>`:''}
                                <div class="current" data-aed="${t.price}">AED ${t.price}</div>
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML=h;

                container.querySelectorAll('.tier-card').forEach(c=>{
                    c.addEventListener('click',()=>this.selectTier(c));

                    if(this.preselectedTierId && c.dataset.tier === this.preselectedTierId){
                        this.selectTier(c);
                    }
                });
            } else {
                container.innerHTML='<div class="tier-placeholder">No packages available for this tour.</div>';
            }

            if(data.addons?.length){
                let ah='';
                data.addons.forEach(a=>{
                    const iconName = a.icon ? (a.icon.startsWith('bi-') ? a.icon : 'bi-' + a.icon) : 'bi-plus-circle';
                    ah+=`<div class="addon-card-horizontal" data-addon="${a.id}" data-price="${a.price}">
                        <input type="checkbox" name="addons[]" value="${a.id}">
                        <div class="addon-check-abs"><i class="bi bi-check-lg"></i></div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.95rem;">
                                <i class="${iconName}"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 fs-6 text-truncate" style="max-width: 140px;">${a.name}</h5>
                        </div>
                        <p class="small text-muted mb-2 lh-sm text-truncate-2" style="font-size: 0.78rem; min-height: 28px;">${a.description || 'Optional safari enhancement'}</p>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="badge bg-light text-dark border fw-bold rounded-pill" data-aed="${a.price}" data-is-addon="true">+AED ${parseFloat(a.price).toFixed(2)}</span>
                            <span class="small fw-bold text-primary addon-status-label" style="font-size: 0.75rem;">+ Add</span>
                        </div>
                    </div>`;
                });
                addonList.innerHTML=ah;
                addonList.style.display='flex';
                if(addons) addons.style.display='block';

                // Convert all freshly injected tiers and addons to active currency
                this.convertAllPrices(this.currency.code);

                addonList.querySelectorAll('.addon-card-horizontal').forEach(item=>{
                    item.addEventListener('click',e=>{
                        if(e.target.tagName!=='INPUT'){
                            const inp=item.querySelector('input');
                            inp.checked=!inp.checked;
                        }
                        const isChecked = item.querySelector('input').checked;
                        item.classList.toggle('selected', isChecked);
                        const statusLabel = item.querySelector('.addon-status-label');
                        if (statusLabel) {
                            statusLabel.textContent = isChecked ? '✓ Added' : '+ Add';
                            statusLabel.className = isChecked ? 'small fw-bold text-success addon-status-label' : 'small fw-bold text-primary addon-status-label';
                        }
                        this.updateAddons();
                    });
                });
            }else{
                if(addons) addons.style.display='none';
            }
        }catch(e){
            container.innerHTML='<div class="tier-placeholder text-danger"><i class="bi bi-exclamation-circle me-2"></i>Error loading packages</div>';
        }
    },

    selectTier(card){
        document.querySelectorAll('.tier-card').forEach(c=>c.classList.remove('selected'));
        card.classList.add('selected');
        const tierInput = document.getElementById('selectedTier');
        if(tierInput){
            tierInput.value=card.dataset.tier;
            tierInput.dispatchEvent(new Event('change', {bubbles: true}));
        }
        this.selectedTier=card.dataset.tier;
        this.selectedPrice=parseFloat(card.dataset.price);
        this.updateTotal();

        if(window.innerWidth < 992) {
            card.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'center'});
        }
    },

    updateAddons(){
        this.selectedAddons=[];
        document.querySelectorAll('.addon-card-horizontal input:checked').forEach(inp=>{
            const item=inp.closest('.addon-card-horizontal');
            this.selectedAddons.push({
                id:inp.value,
                price:parseFloat(item.dataset.price)
            });
        });
        this.updateTotal();
    },

    calculateBaseTotal(){
        const adults=parseInt(document.getElementById('bookingAdults')?.value)||1;
        const children=parseInt(document.getElementById('bookingChildren')?.value)||0;
        let price = this.selectedPrice || 0;
        if (price <= 0) {
            const selectedTierCard = document.querySelector('.tier-card.selected');
            if (selectedTierCard && selectedTierCard.dataset.price) {
                price = parseFloat(selectedTierCard.dataset.price) || 0;
                this.selectedPrice = price;
            }
        }
        let total=(price * adults)+(price * 0.7 * children);
        this.selectedAddons.forEach(a=>total+=a.price);
        return total;
    },

    updateTotal(){
        const adults=parseInt(document.getElementById('bookingAdults')?.value)||1;
        const children=parseInt(document.getElementById('bookingChildren')?.value)||0;

        let baseTotal=(this.selectedPrice*adults)+(this.selectedPrice*0.7*children);
        this.selectedAddons.forEach(a=>baseTotal+=a.price);

        // Apply discount if active coupon is present
        let discount = 0;
        if (window.appliedPromoCoupon) {
            const c = window.appliedPromoCoupon;
            if (c.discount_type === 'percentage') {
                discount = (baseTotal * parseFloat(c.discount_value)) / 100;
                if (c.max_discount && discount > parseFloat(c.max_discount)) {
                    discount = parseFloat(c.max_discount);
                }
            } else if (c.discount_type === 'fixed') {
                discount = Math.min(baseTotal, parseFloat(c.discount_value));
            } else if (c.discount_type === 'per_person') {
                discount = parseFloat(c.discount_value) * (adults + children);
            }
            discount = Math.min(baseTotal, Math.max(0, discount));

            const promoSavingsText = document.getElementById('promoSavingsText');
            if (promoSavingsText) {
                promoSavingsText.textContent = `AED ${discount.toFixed(2)} saved (${c.discount_type === 'percentage' ? parseInt(c.discount_value) + '% OFF' : 'Discount Applied'})`;
            }
        }

        let total = Math.max(0, baseTotal - discount);

        const totalEl = document.getElementById('bookingTotal');
        const summaryTotalEl = document.getElementById('summaryTotal');
        const payNowEl = document.getElementById('bookingPayNow');
        const method = document.getElementById('paymentMethod')?.value || 'cash';
        const payInput = document.getElementById('paymentAmount');
        const container=document.getElementById('paymentOptions');
        const advancePercent=parseFloat(container?.dataset.advancePercent)||0;
        let payNow=0;
        if(method==='advance') payNow=(total*advancePercent)/100;
        else if(method==='full') payNow=total;
        else if(method==='cash') payNow=total;

        const formatMoney = (v)=> this.formatDisplayPrice(v);
        if(totalEl) {
            totalEl.innerHTML = formatMoney(total);
        }
        if(summaryTotalEl) {
            if (discount > 0) {
                summaryTotalEl.innerHTML = `<span class="text-decoration-line-through text-muted small me-2">${formatMoney(baseTotal)}</span> <span class="text-success fw-bold">${formatMoney(total)}</span>`;
            } else {
                summaryTotalEl.innerHTML = formatMoney(total);
            }
        }
        if(payInput) payInput.value = Number(payNow).toFixed(2);
        const submitBtn = document.getElementById('submitBooking');
        if(submitBtn){
            if(method==='cash'){
                submitBtn.innerHTML='Confirm <i class="bi bi-check-lg"></i>';
            }else{
                const cur = this.currency.code;
                const converted = payNow * (this.currency.rates[cur] || 1);
                const approx = cur !== 'AED' ? ` (~ ${this.currency.symbols[cur]}${Math.round(converted)})` : '';
                submitBtn.innerHTML=`Pay AED ${Number(payNow).toFixed(2)}${approx} <i class="bi bi-credit-card"></i>`;
            }
        }
    },

    nextStep(){
        if(!this.validateStep(this.currentStep))return;
        if(this.currentStep<2){

            if(window.dataLayer){
                const tourSel = document.getElementById('bookingTour');
                const tourName = tourSel ? tourSel.options[tourSel.selectedIndex].text : '-';
                const tierCard = document.querySelector('.tier-card.selected');
                const tierName = tierCard ? tierCard.dataset.name : '-';
                const adults = parseInt(document.getElementById('bookingAdults')?.value)||1;
                const children = parseInt(document.getElementById('bookingChildren')?.value)||0;

                let total = (this.selectedPrice*adults) + (this.selectedPrice*0.7*children);
                this.selectedAddons.forEach(a=>total+=a.price);

                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: "begin_checkout",
                    ecommerce: {
                        currency: "AED",
                        value: total,
                        items: [{
                            item_id: this.selectedTier,
                            item_name: tourName + " - " + tierName,
                            price: this.selectedPrice,
                            quantity: adults + children,
                            item_category: "Tours",
                            item_variant: tierName
                        }]
                    }
                });
            }

            this.currentStep++;
            this.updateStep();
        }
    },

    prevStep(){
        if(this.currentStep>1){
            this.currentStep--;
            this.updateStep();
        }
    },

    updateStep(){

        document.querySelectorAll('.step-content').forEach((s)=>{
            const stepNum = parseInt(s.dataset.step);
            if(stepNum === this.currentStep) {
                s.classList.remove('d-none');
                s.classList.add('active');
            } else {
                s.classList.add('d-none');
                s.classList.remove('active');
            }
        });

        const subtitle = document.getElementById('bookingModalSubtitle');
        if(subtitle){
            subtitle.textContent = `Step ${this.currentStep} of 2`;
            subtitle.classList.remove('d-none');
        }

        const backBtn = document.getElementById('headerBackBtn');
        if(backBtn){
            if(this.currentStep > 1) backBtn.classList.remove('d-none');
            else backBtn.classList.add('d-none');
        }

        const nextBtn = document.getElementById('nextStep');
        const submitBtn = document.getElementById('submitBooking');

        if(nextBtn) {
            if(this.currentStep < 2) {
                nextBtn.classList.remove('d-none');
                nextBtn.classList.add('d-inline-flex');
            } else {
                nextBtn.classList.add('d-none');
                nextBtn.classList.remove('d-inline-flex');
            }
        }
        if(submitBtn) {
            if(this.currentStep === 2) {
                submitBtn.classList.remove('d-none');
                submitBtn.classList.add('d-inline-flex');
            } else {
                submitBtn.classList.add('d-none');
                submitBtn.classList.remove('d-inline-flex');
            }
        }

        if(this.currentStep === 2){
            this.updateSummary();
            this.updateTotal();
        }
    },

    updateSummary(){
        const tourSel = document.getElementById('bookingTour');
        const tierCard = document.querySelector('.tier-card.selected');

        const tourName = tourSel ? tourSel.options[tourSel.selectedIndex].text : '-';
        const tierName = tierCard ? tierCard.dataset.name : '-';

        const sTour = document.getElementById('summaryTourName');
        const sTier = document.getElementById('summaryTierName');

        if(sTour) sTour.textContent = tourName;
        if(sTier) sTier.textContent = tierName;

    },

    validateStep(step){
        if(step===1){
            return this.validateStep1 ? this.validateStep1(true) : true;
        }
        if(step===2){
            if(!document.getElementById('paymentMethod')?.value){
                this.toast('Please select a payment option','error');
                return false;
            }
        }
        return true;
    },

    resetForm(){
        document.getElementById('bookingForm')?.reset();
        document.getElementById('tierCards').innerHTML='<div class="tier-placeholder">Select a tour to see packages</div>';
        document.getElementById('selectedTier').value='';
        document.getElementById('bookingTotal').textContent='AED 0.00';
        document.getElementById('paymentMethod') && (document.getElementById('paymentMethod').value='cash');
        document.getElementById('paymentAmount') && (document.getElementById('paymentAmount').value='0');
        const paymentContainer=document.getElementById('paymentOptions');
        if(paymentContainer){
            paymentContainer.querySelectorAll('.payment-option').forEach(o=>o.classList.toggle('selected',o.dataset.value==='cash'));
        }
        const addons = document.getElementById('addonsSection');
        if(addons) addons.style.display='none';

        this.currentStep=1;
        this.selectedTier=null;
        this.selectedPrice=0;
        this.selectedAddons=[];

        const titleEl = document.getElementById('bookingModalTitle');
        const wrapper = document.getElementById('tourSelectWrapper');
        if(titleEl) titleEl.textContent = 'Book Your Adventure';
        if(wrapper) wrapper.classList.remove('d-none');

        this.updateStep();

        const dateWrapper = document.getElementById('dateCardsWrapper');
        if(dateWrapper) dateWrapper.querySelectorAll('.date-card').forEach(c=>c.classList.remove('selected'));
    },

    async detectLocation(){
        const inp=document.getElementById('bookingLocation');
        const btn=document.getElementById('detectLocation');
        const gpsLat=document.getElementById('gpsLat');
        const gpsLng=document.getElementById('gpsLng');
        const gpsAcc=document.getElementById('gpsAccuracy');
        const gpsTs=document.getElementById('gpsTimestamp');
        const gpsConsent=document.getElementById('gpsConsent');
        const gpsSource=document.getElementById('gpsSource');
        const gpsAddr=document.getElementById('gpsAddress');

        if(!inp||!btn)return;

        const orig=btn.innerHTML;
        gpsConsent.value='Requested';
        gpsSource.value='GPS (User Consented)';
        btn.innerHTML='<i class="bi bi-arrow-clockwise spin"></i>';
        btn.disabled=true;

        const resetUi=()=>{
            btn.innerHTML=orig;
            btn.disabled=false;
        };

        try{
            if('geolocation' in navigator){
                const pos=await new Promise((res,rej)=>navigator.geolocation.getCurrentPosition(res,rej,{timeout:10000}));

                gpsLat.value=pos.coords.latitude;
                gpsLng.value=pos.coords.longitude;
                gpsAcc.value=typeof pos.coords.accuracy==='number'?String(pos.coords.accuracy):'Not Available';
                gpsTs.value=pos.timestamp?String(pos.timestamp):String(Date.now());
                gpsConsent.value='Yes';
                gpsSource.value='GPS (User Consented)';

                const addr=await this.reverseGeocode(pos.coords.latitude,pos.coords.longitude);
                if(addr){
                    inp.value=addr;
                    inp.dispatchEvent(new Event('change', {bubbles: true}));
                    gpsAddr.value=addr;
                }
                this.toast('Location detected','success');
            }else{
                gpsConsent.value='Not Available';
                gpsSource.value='Not Available';
                await this.ipLocation(inp);
            }
        }catch(e){
            gpsConsent.value='Denied/Failed';
            gpsSource.value='Not Available';
            gpsLat.value='';
            gpsLng.value='';
            gpsAcc.value='Not Available';
            gpsTs.value='Not Available';
            await this.ipLocation(inp);
        }
        resetUi();
    },

    async reverseGeocode(lat,lng){
        try{
            const res=await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const t=await res.text();
            const d=JSON.parse(t.replace(/^\uFEFF+/, '').trim());
            if(d?.address){
                const a=d.address;
                return[a.road,a.suburb||a.neighbourhood,a.city||a.town].filter(Boolean).join(', ');
            }
        }catch(e){}
        return null;
    },

    async ipLocation(inp){
        try{
            const res=await fetch('/ajax.php?action=geoip');
            const t=await res.text();
            const d=JSON.parse(t.replace(/^\uFEFF+/, '').trim());
            const locCity = d.city || d.region;
            if(locCity){
                inp.value=[d.city, d.region].filter(Boolean).join(', ');
                this.toast('Approximate location detected','success');
                return;
            }
        }catch(e){}
        inp.value='Dubai, UAE';
    },

    initForms(){
        document.querySelectorAll('form').forEach(form=>{
            form.addEventListener('submit',async e=>{
                e.preventDefault();

                const emailInput = form.querySelector('#bookingEmail, #email');
                if(emailInput && !emailInput.classList.contains('is-verified')){

                    const emailVal = emailInput.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if(emailVal && emailRegex.test(emailVal)){
                         this.toast('Please verify your email address first.', 'error');

                         if(!emailInput.parentElement.querySelector('.email-verify-btn')){
                            this.showVerifyButton(emailInput);
                         }

                         this.toggleSubmit(form, false);

                         return;
                    }
                }

                const errEl=document.getElementById('bookingError');
                if(errEl) { errEl.classList.add('d-none'); errEl.textContent=''; }
                if(typeof validateForm === 'function'){
                    if(!validateForm(form, null, true)) {
                        if(errEl){
                            errEl.textContent='Please complete all required fields before continuing.';
                            errEl.classList.remove('d-none');
                        } else {
                            this.toast('Please complete all required fields before continuing.','error');
                        }
                        return;
                    }
                } else if(!form.checkValidity()){

                    const invalidFields = form.querySelectorAll(':invalid');
                    invalidFields.forEach(field => {
                         this.showError(field, false);
                    });

                    if(invalidFields.length > 0) invalidFields[0].focus();

                    if(errEl){
                        errEl.textContent='Please complete all required fields before continuing.';
                        errEl.classList.remove('d-none');
                    }
                    return;
                }

                // Sync and validate international telephone number if intl-tel-input is initialized
                let phoneError = null;
                const phoneInputs = form.querySelectorAll('input[type="tel"], #bookingPhone, #waPhone, #welcomePhone, #phone');
                phoneInputs.forEach(inp => {
                    if (inp._iti && typeof inp._iti.getNumber === 'function') {
                        const fullNum = inp._iti.getNumber();
                        if (fullNum) {
                            inp.value = fullNum;
                        }
                        const val = inp.value.trim();
                        if (inp.hasAttribute('required') || val !== '') {
                            if (typeof inp._iti.isValidNumber === 'function' && !inp._iti.isValidNumber()) {
                                phoneError = 'Please enter a valid phone number with country code.';
                                inp.classList.add('is-invalid');
                                inp.focus();
                            } else {
                                inp.classList.remove('is-invalid');
                            }
                        }
                    }
                });

                if (phoneError) {
                    if (errEl) {
                        errEl.textContent = phoneError;
                        errEl.classList.remove('d-none');
                    } else {
                        this.toast(phoneError, 'error');
                    }
                    return;
                }

                const btn=form.querySelector('[type="submit"]');
                const orig=btn?.innerHTML;
                if(btn){
                    btn.disabled=true;
                    btn.innerHTML='<i class="bi bi-arrow-clockwise spin"></i>Processing...';
                }

                try{

                    const fd=new FormData(form);
                    const action=String(fd.get('action')||'');

                    if((action==='booking'||action==='contact'||action==='logWhatsApp')&&typeof window!=='undefined'&&window.RECAPTCHA_SITE_KEY){
                        try{
                            if(typeof grecaptcha==='undefined' || !grecaptcha.enterprise || typeof grecaptcha.enterprise.execute!=='function'){
                                await new Promise(function(resolve){
                                    var s=document.createElement('script');
                                    s.src='https://www.google.com/recaptcha/enterprise.js?render='+window.RECAPTCHA_SITE_KEY;
                                    s.async=true;
                                    s.onload=resolve;
                                    document.head.appendChild(s);
                                });
                            }
                            await new Promise(r=>grecaptcha.enterprise.ready(r));
                            const token=await grecaptcha.enterprise.execute(window.RECAPTCHA_SITE_KEY,{action});
                            fd.set('g-recaptcha-response',token);
                        }catch(e){
                            console.error('ReCaptcha error:', e);
                            if(errEl){
                                errEl.textContent='Security verification failed. Please refresh and try again.';
                                errEl.classList.remove('d-none');
                            }else{
                                this.toast('Security verification failed. Please refresh and try again.','error');
                            }
                            return;
                        }
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.CSRF_TOKEN || '';
                    if (!fd.has('_token') && csrfToken) {
                        fd.append('_token', csrfToken);
                    }
                    const res=await fetch('/ajax.php',{
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body:fd
                    });
                    const raw=await res.text();
                    let data=null;
                    try{ data=JSON.parse(raw.replace(/^\uFEFF+/, '').trim()); }catch(e){ data=null; }
                    if(!data){
                        if(errEl){
                            errEl.textContent='Payment could not be initiated. Please try again.';
                            errEl.classList.remove('d-none');
                        }else{
                            this.toast('Payment could not be initiated. Please try again.','error');
                        }
                        return;
                    }

                    if(data.success){
                        const totalVal = parseFloat(document.getElementById('bookingTotal')?.textContent?.replace(/[^0-9.]/g, '') || 1.0) || 1.0;
                        const targetUrl = data.redirect_url || (data.reference ? ('/thankyou?ref=' + encodeURIComponent(data.reference)) : null);

                        const eventParams = {
                            'send_to': 'AW-17859624049/eR3SCLimtvobEPH4kMRC',
                            'value': totalVal,
                            'currency': 'AED',
                            'transaction_id': data.reference || ('REF-' + Date.now())
                        };

                        // Google Ads Conversion Tag & Event: Submit lead form (AW-17859624049/eR3SCLimtvobEPH4kMRC)
                        if (typeof window.gtag === 'function') {
                            window.gtag('event', 'conversion', eventParams);
                            window.gtag('event', 'conversion_event_submit_lead_form', eventParams);
                        }
                        if (window.dataLayer) {
                            window.dataLayer.push({
                                event: 'generate_lead',
                                form_name: action || 'lead_form',
                                conversion_label: 'eR3SCLimtvobEPH4kMRC',
                                transaction_id: data.reference || '',
                                value: totalVal,
                                currency: 'AED'
                            });
                        }

                        if(targetUrl){
                            if(typeof window.gtagSendEvent === 'function'){
                                window.gtagSendEvent(targetUrl, eventParams);
                            } else {
                                window.location.href = targetUrl;
                            }
                            return;
                        }

                        this.toast(data.message||'Success!','success');
                        if(data.reference)setTimeout(()=>this.toast('Reference: '+data.reference,'success'),1500);

                        const bookingEl=document.getElementById('bookingModal');
                        const waEl=document.getElementById('whatsappModal');
                        if(bookingEl&&typeof bootstrap!=='undefined'&&bootstrap.Modal){
                            bootstrap.Modal.getInstance(bookingEl)?.hide();
                        }else bookingEl?.classList.remove('active');
                        if(waEl&&typeof bootstrap!=='undefined'&&bootstrap.Modal){
                            bootstrap.Modal.getInstance(waEl)?.hide();
                        }else waEl?.classList.remove('active');
                        document.body.style.overflow='';
                        form.reset();
                    }else{
                        let msg = data.message || 'An error occurred';
                        if (data.errors && typeof data.errors === 'object') {
                            const errorList = Object.values(data.errors).flat();
                            if (errorList.length > 0) {
                                msg = errorList.join('<br>');
                            }
                        }
                        if(errEl){
                            errEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + msg;
                            errEl.classList.remove('d-none');
                        }else{
                            this.toast(msg,'error');
                        }

                    }
                }catch(e){
                    if(errEl){
                        errEl.innerHTML='<i class="bi bi-exclamation-triangle-fill me-2"></i>Network error. Please try again.';
                        errEl.classList.remove('d-none');
                    }else{
                        this.toast('Network error. Please try again.','error');
                    }
                }
                if(btn){
                    btn.disabled=false;
                    btn.innerHTML=orig;
                }
            });
        });
    },

    initQty(){
        document.querySelectorAll('button[data-action]').forEach(btn=>{
            btn.addEventListener('click',()=>{
                const target=btn.dataset.target;
                const action=btn.dataset.action;
                const inp=document.getElementById('booking'+target.charAt(0).toUpperCase()+target.slice(1));

                if(!inp)return;

                let v=parseInt(inp.value)||0;
                const min=parseInt(inp.min)||0;
                const max=parseInt(inp.max)||99;

                if(action==='minus')v=Math.max(min,v-1);
                else if(action==='plus')v=Math.min(max,v+1);

                inp.value=v;
                this.updateTotal();
            });
        });
    },

    initFAQ(){
        document.querySelectorAll('.faq-q').forEach(q=>{
            q.addEventListener('click',()=>{
                const item=q.closest('.faq-item');
                const answer=item.querySelector('.faq-a');
                const inner=answer.querySelector('.faq-a-inner');
                const isOpen=q.classList.contains('active');

                document.querySelectorAll('.faq-q.active').forEach(oq=>{
                    oq.classList.remove('active');
                    oq.closest('.faq-item').querySelector('.faq-a').style.maxHeight='0';
                });

                if(!isOpen){
                    q.classList.add('active');
                    answer.style.maxHeight=inner.scrollHeight+'px';
                }
            });
        });
    },

    initTourSidebar(){
        const list=document.getElementById('sidebarTierList');
        if(!list)return;

        const book=document.getElementById('sidebarBookOnline');
        const wa=document.getElementById('sidebarBookWhatsapp');
        const priceEl=document.querySelector('.book-card-price .amount');

        const buildWa=(tierName)=>{
            if(!wa)return;
            const base=wa.getAttribute('href');
            wa.dataset.baseHref=wa.dataset.baseHref||base;

            try{
                const u=new URL(wa.dataset.baseHref,location.origin);
                const t=(tierName?(' Package: '+tierName+'.'):'');
                u.searchParams.set('text',decodeURIComponent(u.searchParams.get('text')||'')+t);
                wa.setAttribute('href',u.pathname+u.search);
            }catch(e){}
        };

        const apply=(btn)=>{
            list.querySelectorAll('.sidebar-tier').forEach(b=>b.classList.remove('is-active'));
            btn.classList.add('is-active');

            const tierId=btn.dataset.tier;
            const tierName=btn.dataset.name||'';
            const price=btn.dataset.price?parseFloat(btn.dataset.price):0;

            if(book){
                book.dataset.tier=tierId;
            }
            if(priceEl&&price){
                priceEl.textContent='AED '+Math.round(price).toLocaleString();
            }
            buildWa(tierName);
        };

        list.querySelectorAll('.sidebar-tier').forEach(btn=>btn.addEventListener('click',()=>apply(btn)));

        const first=list.querySelector('.sidebar-tier.is-popular')||list.querySelector('.sidebar-tier');
        if(first)apply(first);
    },

    initStickySidebar(){
        // Native CSS position: sticky is used for zero layout shift and smooth scrolling
        return;
    },

    initHorizontalTabs(){
        const tabs=document.getElementById('tourTabs');
        if(!tabs)return;
        let isDown=false,startX=0,scrollLeft=0;
        const onDown=e=>{
            isDown=true;
            startX=(e.pageX||e.touches?.[0]?.pageX||0);
            scrollLeft=tabs.scrollLeft;
        };
        const onMove=e=>{
            if(!isDown)return;
            const x=(e.pageX||e.touches?.[0]?.pageX||0);
            const walk=(startX-x);
            tabs.scrollLeft=scrollLeft+walk;
        };
        const onUp=()=>{isDown=false;};
        tabs.addEventListener('mousedown',onDown);
        tabs.addEventListener('mousemove',onMove);
        window.addEventListener('mouseup',onUp);
        tabs.addEventListener('touchstart',onDown,{passive:true});
        tabs.addEventListener('touchmove',onMove,{passive:true});
        tabs.addEventListener('touchend',onUp);
        tabs.addEventListener('wheel',e=>{
            if(Math.abs(e.deltaY)>0){
                e.preventDefault();
                tabs.scrollLeft+=e.deltaY;
            }
        },{passive:false});
    },

    openBooking(tourId, tierId = null){
        this.preselectedTourId = String(tourId);
        this.preselectedTierId = tierId ? String(tierId) : null;

        const modal = document.getElementById('bookingModal');
        if (!modal) return;

        const tourSel = document.getElementById('bookingTour');
        if (tourSel) {
            tourSel.value = String(tourId);
            this.loadTiers(String(tourId));
            const wrapper = document.getElementById('tourSelectWrapper');
            if (wrapper) wrapper.classList.add('d-none');
            const titleEl = document.getElementById('bookingModalTitle');
            if (titleEl && tourSel.selectedIndex >= 0) {
                titleEl.textContent = tourSel.options[tourSel.selectedIndex].text;
            }
        }

        const getModal = () => (typeof bootstrap !== 'undefined' && bootstrap.Modal) ? (bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal)) : null;
        const m = getModal();
        if (m) m.show();
    },

    initSafariMatcher(){
        const section = document.getElementById('safariMatcherSection');
        if(!section) return;

        let activeTours = [];
        try {
            const raw = document.getElementById('activeToursDataset')?.textContent;
            if (raw) activeTours = JSON.parse(raw);
        } catch(e) {}

        // If dataset is empty, fall back to tours options from the select dropdown
        if (!activeTours.length) {
            const tourSelect = document.getElementById('bookingTour');
            if (tourSelect) {
                for (let i = 0; i < tourSelect.options.length; i++) {
                    const opt = tourSelect.options[i];
                    if (opt.value) {
                        activeTours.push({
                            id: String(opt.value),
                            name: opt.text,
                            slug: opt.text.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
                            category_slug: opt.text.toLowerCase().includes('city') ? 'city-tour' : (opt.text.toLowerCase().includes('cruise') ? 'water-activity' : 'desert-safari'),
                            category_name: opt.text.toLowerCase().includes('city') ? 'City Tour' : (opt.text.toLowerCase().includes('cruise') ? 'Water Cruise' : 'Desert Safari'),
                            duration: '4-6 Hours',
                            rating: 4.9,
                            min_price: 79,
                            short_desc: opt.text + ' - Top rated Dubai experience with 5-star service.'
                        });
                    }
                }
            }
        }

        let answers = { experience: null, time: null, style: null };

        const step1 = document.getElementById('quizStep1');
        const step2 = document.getElementById('quizStep2');
        const step3 = document.getElementById('quizStep3');
        const result = document.getElementById('quizResult');

        const stepNum = document.getElementById('quizStepNum');
        const stepTitle = document.getElementById('quizStepTitle');
        const progressText = document.getElementById('quizProgressText');

        const matchedTitle = document.getElementById('quizMatchedTitle');
        const matchedCategory = document.getElementById('quizMatchedCategory');
        const matchedMeta = document.getElementById('quizMatchedMeta');
        const matchedDesc = document.getElementById('quizMatchedDesc');
        const matchedPrice = document.getElementById('quizMatchedPrice');
        const bookBtn = document.getElementById('quizBookBtn');
        const resetBtn = document.getElementById('quizResetBtn');

        let matchedTour = activeTours[0] || null;

        const calculateBestMatch = () => {
            if (!activeTours.length) return;

            // 1. Primary Category Pool Filtering
            let pool = activeTours.filter(t => {
                const catLow = (t.category_slug || '').toLowerCase();
                const nameLow = (t.name || '').toLowerCase();
                const slugLow = (t.slug || '').toLowerCase();

                if (answers.experience === 'desert') {
                    return catLow.includes('desert') || slugLow.includes('desert') || slugLow.includes('safari');
                } else if (answers.experience === 'city') {
                    return catLow.includes('city') || slugLow.includes('city') || slugLow.includes('abu-dhabi') || slugLow.includes('sightseeing');
                } else if (answers.experience === 'water') {
                    return catLow.includes('water') || slugLow.includes('cruise') || slugLow.includes('dhow') || slugLow.includes('catamaran') || slugLow.includes('yacht');
                } else if (answers.experience === 'quad_buggy') {
                    return slugLow.includes('buggy') || slugLow.includes('quad') || nameLow.includes('quad') || nameLow.includes('buggy') || slugLow.includes('atv');
                }
                return true;
            });

            if (!pool.length) pool = activeTours;

            // 2. Score Candidates within the selected experience pool
            let candidates = pool.map(t => {
                let score = 0;
                const nameLow = (t.name || '').toLowerCase();
                const slugLow = (t.slug || '').toLowerCase();
                const descLow = (t.short_desc || '').toLowerCase();
                const haystack = `${nameLow} ${slugLow} ${descLow}`;

                // Time match
                if (answers.time === 'morning') {
                    if (haystack.includes('morning') || haystack.includes('sunrise') || haystack.includes('half day') || slugLow.includes('morning')) score += 50;
                } else if (answers.time === 'evening') {
                    if (haystack.includes('evening') || haystack.includes('sunset') || haystack.includes('dinner') || haystack.includes('bbq') || slugLow.includes('evening')) score += 50;
                } else if (answers.time === 'overnight') {
                    if (haystack.includes('overnight') || haystack.includes('camping') || slugLow.includes('overnight')) score += 60;
                }

                // Style match
                if (answers.style === 'thrill') {
                    if (haystack.includes('quad') || haystack.includes('buggy') || haystack.includes('bashing') || haystack.includes('extreme')) score += 40;
                } else if (answers.style === 'luxury') {
                    if (haystack.includes('vip') || haystack.includes('luxury') || haystack.includes('private') || haystack.includes('abu dhabi') || haystack.includes('catamaran')) score += 40;
                } else if (answers.style === 'family') {
                    if (haystack.includes('city') || haystack.includes('evening') || haystack.includes('cruise') || haystack.includes('family') || t.is_bestseller) score += 30;
                }

                if (t.is_bestseller) score += 15;
                if (t.is_featured) score += 10;
                score -= (t.priority || 10);

                return { tour: t, score: score };
            });

            candidates.sort((a, b) => b.score - a.score);
            matchedTour = candidates[0]?.tour || pool[0];

            if (matchedTitle) matchedTitle.textContent = matchedTour.name;
            if (matchedCategory) matchedCategory.textContent = matchedTour.category_name || 'Experience';
            if (matchedMeta) {
                matchedMeta.innerHTML = `<i class="bi bi-clock me-1"></i>${matchedTour.duration || '4-6 Hours'} • ⭐ ${matchedTour.rating || 4.9} (${matchedTour.review_count || '500+'} Reviews)`;
            }
            if (matchedPrice) {
                const pVal = Math.round(matchedTour.min_price || 79);
                matchedPrice.dataset.aed = pVal;
                matchedPrice.textContent = 'AED ' + pVal;
                if (typeof this.convertAllPrices === 'function' && this.currency?.code) {
                    this.convertAllPrices(this.currency.code);
                }
            }
        };

        section.querySelectorAll('.quiz-choice-card').forEach(card => {
            card.addEventListener('click', () => {
                const step = card.dataset.step;
                const val = card.dataset.val;

                if (step === '1') {
                    answers.experience = val;
                    step1.classList.add('d-none');
                    step2.classList.remove('d-none');
                    stepNum.textContent = '2';
                    stepTitle.textContent = 'Preferred Timing';
                    progressText.textContent = 'Step 2 of 3';
                } else if (step === '2') {
                    answers.time = val;
                    step2.classList.add('d-none');
                    step3.classList.remove('d-none');
                    stepNum.textContent = '3';
                    stepTitle.textContent = 'Group Style';
                    progressText.textContent = 'Step 3 of 3';
                } else if (step === '3') {
                    answers.style = val;
                    calculateBestMatch();
                    step3.classList.add('d-none');
                    result.classList.remove('d-none');
                    stepNum.textContent = '✓';
                    stepTitle.textContent = 'Your Matched Tour';
                    progressText.textContent = 'Matched!';
                }
            });
        });

        if (bookBtn) {
            bookBtn.addEventListener('click', () => {
                if (matchedTour && matchedTour.id) {
                    this.openBooking(matchedTour.id);
                    const isPromoActive = el.dataset.conciergePromoActive === '1';
                    const promoInput = document.getElementById('bookingPromoCode');
                    if (isPromoActive && promoInput) {
                        promoInput.value = el.dataset.conciergePromoCode || 'MATCH5';
                        if (typeof window.validateCurrentPromo === 'function') {
                            setTimeout(() => window.validateCurrentPromo(), 400);
                        }
                    } else if (!isPromoActive && promoInput && promoInput.value === 'MATCH5') {
                        promoInput.value = '';
                    }
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                answers = { experience: null, time: null, style: null };
                result.classList.add('d-none');
                step2.classList.add('d-none');
                step3.classList.add('d-none');
                step1.classList.remove('d-none');
                stepNum.textContent = '1';
                stepTitle.textContent = 'Choose Experience Type';
                progressText.textContent = 'Step 1 of 3';
            });
        }
    },

    initSunsetWidget(){
        const label = document.getElementById('sunsetCountdownLabel');
        if (!label) return;

        const getDubaiSunset = (date) => {
            const lat = 25.2048;
            const lng = 55.2708;
            const rad = Math.PI / 180;
            const deg = 180 / Math.PI;

            const start = new Date(date.getFullYear(), 0, 0);
            const diff = date - start;
            const oneDay = 1000 * 60 * 60 * 24;
            const dayOfYear = Math.floor(diff / oneDay);

            const lngHour = lng / 15;
            const t = dayOfYear + ((18 - lngHour) / 24);
            const M = (0.9856 * t) - 3.289;
            let L = M + (1.916 * Math.sin(M * rad)) + (0.020 * Math.sin(2 * M * rad)) + 282.634;
            L = ((L % 360) + 360) % 360;

            let RA = deg * Math.atan(0.91764 * Math.tan(L * rad));
            RA = ((RA % 360) + 360) % 360;
            const Lquadrant = Math.floor(L / 90) * 90;
            const RAquadrant = Math.floor(RA / 90) * 90;
            RA = (RA + (Lquadrant - RAquadrant)) / 15;

            const sinDec = 0.39782 * Math.sin(L * rad);
            const cosDec = Math.cos(Math.asin(sinDec));
            const zenith = 90.833;
            const cosH = (Math.cos(zenith * rad) - (sinDec * Math.sin(lat * rad))) / (cosDec * Math.cos(lat * rad));

            if (cosH > 1 || cosH < -1) return { hours: 18, minutes: 30 };
            const H = (deg * Math.acos(cosH)) / 15;
            const T = H + RA - (0.06571 * t) - 6.622;
            let UT = T - lngHour;
            UT = ((UT % 24) + 24) % 24;
            const localHour = UT + 4;
            const hours = Math.floor(localHour);
            const minutes = Math.round((localHour - hours) * 60);
            return { hours, minutes };
        };

        const updateCountdown = () => {
            const now = new Date();
            const utcTime = now.getTime() + (now.getTimezoneOffset() * 60000);
            const dubaiTime = new Date(utcTime + (3600000 * 4));

            const sunsetCalc = getDubaiSunset(dubaiTime);
            const sunsetToday = new Date(dubaiTime);
            sunsetToday.setHours(sunsetCalc.hours, sunsetCalc.minutes, 0, 0);

            const displayHour = sunsetCalc.hours > 12 ? sunsetCalc.hours - 12 : sunsetCalc.hours;
            const displayMin = sunsetCalc.minutes.toString().padStart(2, '0');
            const sunsetFormatted = `${displayHour}:${displayMin} PM`;

            const diffMs = sunsetToday - dubaiTime;
            if (diffMs > 0) {
                const diffHrs = Math.floor(diffMs / 3600000);
                const diffMins = Math.floor((diffMs % 3600000) / 60000);
                label.innerHTML = `Sunset: ${sunsetFormatted} • Golden Hour in ${diffHrs > 0 ? diffHrs + 'h ' : ''}${diffMins}m`;
            } else {
                label.innerHTML = `Stargazing Safari Live Tonight • Clear Skies`;
            }
        };

        updateCountdown();
        setInterval(updateCountdown, 60000);
    },

    initTooltips(){},

    toast(msg,type='success'){
        const container=document.getElementById('toastContainer');
        if(!container)return;

        const icons={
            success:'<i class="bi bi-check-circle-fill"></i>',
            error:'<i class="bi bi-x-circle-fill"></i>',
            warning:'<i class="bi bi-exclamation-circle-fill"></i>'
        };

        const t=document.createElement('div');
        t.className='toast '+type;
        t.innerHTML=`<div class="toast-icon">${icons[type]}</div><div class="toast-content"><strong>${type==='success'?'Success':type==='error'?'Error':'Notice'}</strong><p>${msg}</p></div><div class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></div>`;

        container.appendChild(t);
        setTimeout(()=>t.remove(),5000);
    }
};

window.App=App;
window.DunesApp=App;
document.addEventListener('DOMContentLoaded',()=>App.init());
const style=document.createElement('style');
style.textContent='@keyframes spin{to{transform:rotate(360deg)}}.spin{animation:spin 1s linear infinite}';
document.head.appendChild(style);
})();
