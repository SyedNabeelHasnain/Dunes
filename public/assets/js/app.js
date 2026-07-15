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

                fetch('/ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=get_legal_content&type=${type}`
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
                loader.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
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
                    const res = await fetch('/ajax.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=check_email_status&email=${encodeURIComponent(email)}`
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
            const csrf = form?.querySelector('[name="csrf_token"]')?.value || '';
            const res = await fetch('/ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=send_otp&email=${encodeURIComponent(email)}&csrf_token=${encodeURIComponent(csrf)}`
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
            const csrf = form?.querySelector('[name="csrf_token"]')?.value || '';

            try {
                const res = await fetch('/ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=verify_otp&email=${encodeURIComponent(input.value)}&otp=${code}&csrf_token=${encodeURIComponent(csrf)}`
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

    initWhatsApp(){
        const modal=document.getElementById('whatsappModal');
        if(!modal)return;
        const form=document.getElementById('whatsappForm');
        const startBtn=document.getElementById('startChatBtn');
        const nameInp=document.getElementById('waName');
        const phoneInp=document.getElementById('waPhone');

        if(phoneInp && window.intlTelInput && !phoneInp.dataset.itiInitialized){

        }

        const tourNameInp=document.getElementById('waTourName');
        const pageUrlInp=document.getElementById('waPageUrl');

        const check=()=>{
            startBtn.disabled=!(nameInp.value.trim()&&phoneInp.value.trim());
        };

        nameInp.addEventListener('input',check);
        phoneInp.addEventListener('input',check);

        const getModal=()=>typeof bootstrap!=='undefined'&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(modal):null;

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

        document.addEventListener('click',e=>{
            const link=e.target.closest('a');
            if(!link)return;
            const href=link.getAttribute('href')||'';

            const isWa=href.includes('wa.me')||href.includes('api.whatsapp.com')||link.classList.contains('fab-whatsapp');

            if(!isWa)return;

            e.preventDefault();

            let tourName='';

            if(location.pathname.includes('/tours/')||document.querySelector('.tour-hero')){
                const h1=document.querySelector('h1');
                if(h1)tourName=h1.innerText.trim();
            }

            if(link.dataset.tourName)tourName=link.dataset.tourName;

            const formEnabled=(window.WHATSAPP_FORM_ENABLED==='1');
            if(!formEnabled){
                const fd=new FormData();
                fd.append('action','logWhatsApp');
                fd.append('csrf_token',window.CSRF_TOKEN||'');
                fd.append('name','N/A');
                fd.append('phone','N/A');
                fd.append('tour_name',tourName);
                fd.append('page_url',window.location.href);
                fetch('/ajax.php',{method:'POST',body:fd})
                .then(r=>r.text())
                .then(t=>{ try { return JSON.parse(t.replace(/^\uFEFF+/, '').trim()); } catch(e){ throw e; } })
                .then(d=>{
                    const url=d.redirect_url||href;
                    if(link.target==='_blank') window.open(url,'_blank'); else location.href=url;
                }).catch(()=>{
                    if(link.target==='_blank') window.open(href,'_blank'); else location.href=href;
                });
                return;
            }

            tourNameInp.value=tourName;
            pageUrlInp.value=window.location.href;

            syncGpsData();

            check();

            const m=getModal();
            if(m)m.show();
            else{
                modal.classList.add('active');
                document.body.style.overflow='hidden';
            }
            setTimeout(()=>window.dispatchEvent(new Event('resize')),0);
        });
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

                const tourName = tourSel.options[tourSel.selectedIndex].text;
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
            this.preselectedTierId=el.dataset.tier||null;
            this.preselectedTourId=el.dataset.tour||null;

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
                        if(titleEl) titleEl.textContent = tourSel.options[tourSel.selectedIndex].text;
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
            tooltipInstance = new bootstrap.Tooltip(btnWrapper, { trigger: 'hover click' });
        }

        const validateStep1 = () => {
            if(!next) return;
            const tour = tourSelect ? tourSelect.value : '';
            const tier = tierInput ? tierInput.value : '';
            const date = dateInput ? dateInput.value : '';
            const loc = locationInput ? locationInput.value.trim() : '';

            const missing = [];
            if(!tour) missing.push('Tour');
            if(!tier) missing.push('Package');
            if(!date) missing.push('Date');
            if(!loc) missing.push('Pickup Location');

            if(missing.length === 0) {
                next.disabled = false;
                next.classList.remove('opacity-50', 'pe-none');
                if(tooltipInstance) tooltipInstance.disable();
                if(btnWrapper) btnWrapper.removeAttribute('title');
            } else {
                next.disabled = true;
                next.classList.add('opacity-50', 'pe-none');
                if(tooltipInstance) {
                    tooltipInstance.enable();
                    const msg = 'Please select/fill: ' + missing.join(', ');
                    btnWrapper.setAttribute('data-bs-original-title', msg);

                    if(document.querySelector('.tooltip')) tooltipInstance.show();
                }
            }
        };

        validateStep1();

        tourSelect?.addEventListener('change', () => {
            this.loadTiers(tourSelect.value);
            validateStep1();
        });

        tierInput?.addEventListener('change', validateStep1);

        dateInput?.addEventListener('change', validateStep1);
        dateInput?.addEventListener('input', validateStep1);

        locationInput?.addEventListener('input', validateStep1);
        locationInput?.addEventListener('change', validateStep1);

        next?.addEventListener('click',()=>this.nextStep());
        prev?.addEventListener('click',()=>this.prevStep());
        edit?.addEventListener('click',(e)=>{
            e.preventDefault();
            this.currentStep = 1;
            this.updateStep();
        });

        document.getElementById('detectLocation')?.addEventListener('click',()=>this.detectLocation());

        if (locationInput) {
            const initAutocomplete = () => {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {

                    if (locationInput.dataset.mapsInitialized === 'true') return;

                    const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                        types: ['establishment', 'geocode'],
                        fields: ['formatted_address', 'geometry', 'name'],
                        componentRestrictions: { country: 'ae' },
                    });

                    let dropdownState = 'IDLE';
                    let lastSelectedValue = '';

                    const updateDropdownState = () => {
                        const pacs = document.querySelectorAll('.pac-container');
                        pacs.forEach(pac => {
                            if (dropdownState === 'SEARCHING') {
                                pac.classList.remove('pac-force-hidden');
                            } else {
                                pac.classList.add('pac-force-hidden');
                            }
                        });
                    };

                    locationInput.addEventListener('keydown', (e) => {
                        if(e.key !== 'Tab' && e.key !== 'Enter') {
                            dropdownState = 'SEARCHING';
                            updateDropdownState();
                        }
                    });

                    locationInput.addEventListener('input', () => {
                        if (locationInput.value === lastSelectedValue) {
                            return;
                        }

                        if (locationInput.value.trim().length === 0) {
                            dropdownState = 'IDLE';
                        } else {
                            if (dropdownState !== 'SELECTED') {
                                dropdownState = 'SEARCHING';
                            }
                        }
                        updateDropdownState();
                    });

                    autocomplete.addListener('place_changed', () => {
                        dropdownState = 'SELECTED';
                        updateDropdownState();

                        const place = autocomplete.getPlace();

                        if (place.formatted_address) {
                            locationInput.value = place.formatted_address;
                        } else if (place.name) {
                            locationInput.value = place.name;
                        }

                        lastSelectedValue = locationInput.value;

                        locationInput.dispatchEvent(new Event('input', {bubbles: true}));
                        locationInput.dispatchEvent(new Event('change', {bubbles: true}));

                        locationInput.blur();
                    });

                    locationInput.dataset.mapsInitialized = 'true';

                    const observer = new MutationObserver(() => {
                        const pac = document.querySelector('.pac-container');
                        if (pac) {
                            const wrapper = document.querySelector('.booking-location-wrapper');

                            if (wrapper && pac.parentElement !== wrapper) {
                                wrapper.appendChild(pac);

                                if (!pac.dataset.listenersAttached) {
                                    document.addEventListener('click', (e) => {
                                        if (!wrapper.contains(e.target) && !pac.contains(e.target)) {
                                            dropdownState = 'SELECTED'; // Treat outside click as "Done"
                                            updateDropdownState();
                                        }

                                        if (e.target === locationInput && locationInput.value.trim() !== '') {
                                            if (dropdownState === 'SELECTED' || dropdownState === 'IDLE') {
                                                updateDropdownState();
                                            }
                                        }
                                    }, true);

                                    document.addEventListener('mousedown', (e) => {
                                        if (e.target.closest('.pac-item') || e.target.closest('.pac-container')) {
                                            dropdownState = 'SELECTED'; // Lock immediately
                                            updateDropdownState();
                                        }
                                    }, true);

                                    const styleObserver = new MutationObserver(() => {
                                        updateDropdownState(); // Re-assert our force-hidden state
                                        updatePadding(pac);
                                    });
                                    styleObserver.observe(pac, { attributes: true, attributeFilter: ['style', 'display'] });

                                    const resizeObserver = new ResizeObserver(() => {
                                        updatePadding(pac);
                                    });
                                    resizeObserver.observe(pac);

                                    pac.dataset.listenersAttached = 'true';
                                }
                            }
                        }
                    });
                    observer.observe(document.body, { childList: true, subtree: false });

                    const updatePadding = (pac) => {
                        const scrollArea = document.querySelector('.booking-scroll-area');
                        const wrapper = document.querySelector('.booking-location-wrapper');
                        if (!scrollArea || !pac) return;

                        const shouldBeVisible = dropdownState === 'SEARCHING' && pac.offsetHeight > 0;

                        if (shouldBeVisible) {
                            requestAnimationFrame(() => {
                                const requiredSpace = pac.offsetHeight + 150;
                                scrollArea.style.paddingBottom = requiredSpace + 'px';
                            });

                            if (pac.dataset.wasHidden === 'true') {
                                setTimeout(() => {
                                    wrapper?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }, 100);
                                pac.dataset.wasHidden = 'false';
                            }
                        } else {
                            scrollArea.style.paddingBottom = '';
                            pac.dataset.wasHidden = 'true';
                        }
                    };
                }
            };

            const loadMaps = () => {
                const apiKey = (window.MAPS_API_KEY || '').trim();
                if (!apiKey) {
                    console.warn('Google Maps API key missing; autocomplete disabled.');
                    return;
                }
                if (locationInput.dataset.mapsInitialized === 'true') return;
                if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
                    if (document.querySelector('script[src*="maps.googleapis.com"]')) return;
                    var s = document.createElement('script');
                    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&loading=async&callback=Function.prototype';
                    s.async = true;
                    s.defer = true;
                    s.onload = initAutocomplete;
                    document.head.appendChild(s);
                } else {
                    initAutocomplete();
                }
            };

            locationInput.addEventListener('focus', loadMaps, { once: true });
            locationInput.addEventListener('click', loadMaps, { once: true });
            locationInput.addEventListener('keydown', loadMaps, { once: true });
            if (locationInput.value.trim().length) loadMaps();

            var poll = setInterval(function() {
                if (locationInput.dataset.mapsInitialized === 'true') {
                    clearInterval(poll);
                    return;
                }
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    initAutocomplete();
                    clearInterval(poll);
                }
            }, 300);

            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                initAutocomplete();
            } else {
                const checkGoogle = setInterval(() => {
                    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                        initAutocomplete();
                        clearInterval(checkGoogle);
                    }
                }, 500);

                setTimeout(() => clearInterval(checkGoogle), 10000);
            }
        }
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
            const res=await fetch('/ajax.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'action=getTiers&tour_id='+tourId
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
                                ${save?`<div class="old">AED ${t.old_price}</div>`:''}
                                <div class="current">AED ${t.price}</div>
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
                    ah+=`<div class="addon-card-horizontal" data-addon="${a.id}" data-price="${a.price}">
                        <input type="checkbox" name="addons[]" value="${a.id}">
                        <div class="addon-check-abs"><i class="bi bi-check-lg"></i></div>
                        <div class="addon-h-info">
                            <h5>${a.name}</h5>
                            <p>${a.description||''}</p>
                        </div>
                        <div class="addon-h-price">+AED ${a.price}</div>
                    </div>`;
                });
                addonList.innerHTML=ah;
                addons.style.display='block';

                addonList.querySelectorAll('.addon-card-horizontal').forEach(item=>{
                    item.addEventListener('click',e=>{
                        if(e.target.tagName!=='INPUT'){
                            const inp=item.querySelector('input');
                            inp.checked=!inp.checked;
                        }
                        item.classList.toggle('selected',item.querySelector('input').checked);
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

    updateTotal(){
        const adults=parseInt(document.getElementById('bookingAdults')?.value)||1;
        const children=parseInt(document.getElementById('bookingChildren')?.value)||0;

        let total=(this.selectedPrice*adults)+(this.selectedPrice*0.7*children);

        this.selectedAddons.forEach(a=>total+=a.price);

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

        const formatMoney = (v)=>'AED '+Number(v).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
        if(totalEl) {
            const formatted=formatMoney(total);
            totalEl.innerHTML = formatted.replace('AED ','<span class="currency">AED</span> ');
        }
        if(summaryTotalEl) summaryTotalEl.textContent = formatMoney(total);
        if(method==='cash'){
            payNow = total;
        }
        if(payInput) payInput.value = Number(payNow).toFixed(2);
        const submitBtn = document.getElementById('submitBooking');
        if(submitBtn){
            if(method==='cash'){
                submitBtn.innerHTML='Continue <i class="bi bi-arrow-right"></i>';
            }else{
                submitBtn.innerHTML=`Pay ${formatMoney(payNow)} <i class="bi bi-credit-card"></i>`;
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

            const submitBtn = document.getElementById('submitBooking');
            const method = document.getElementById('paymentMethod')?.value || 'cash';
            if(submitBtn){
                if(method==='cash'){
                    submitBtn.innerHTML='Confirm <i class="bi bi-check-lg"></i>';
                }else{
                    submitBtn.innerHTML='Pay Now <i class="bi bi-credit-card"></i>';
                }
            }
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
            if(!document.getElementById('bookingTour')?.value){
                this.toast('Please select a tour','error');
                return false;
            }
            if(!this.selectedTier){
                this.toast('Please select a package','error');
                return false;
            }
            if(!document.getElementById('bookingDate')?.value){
                this.toast('Please select a date','error');
                return false;
            }
            if(!document.getElementById('bookingLocation')?.value.trim()){
                this.toast('Please enter pickup location','error');
                return false;
            }
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
            const res=await fetch('http://ip-api.com/json/?fields=city,regionName,country');
            const t=await res.text();
            const d=JSON.parse(t.replace(/^\uFEFF+/, '').trim());
            if(d.city){
                inp.value=[d.city,d.regionName].filter(Boolean).join(', ');
                this.toast('Approximate location detected','success');
            }
        }catch(e){
            inp.value='Dubai, UAE';
        }
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

                    const res=await fetch('/ajax.php',{method:'POST',body:fd});
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

                        if(window.dataLayer){
                            if(action === 'contact'){
                                window.dataLayer.push({
                                    event: 'generate_lead',
                                    form_name: 'contact_form'
                                });
                            } else if(action === 'logWhatsApp'){
                                window.dataLayer.push({
                                    event: 'generate_lead',
                                    conversion_type: 'whatsapp'
                                });
                            }
                        }

                        if(data.redirect_url){
                            window.location.href=data.redirect_url;
                            return;
                        }
                        this.toast(data.message||'Success!','success');
                        if(data.reference)setTimeout(()=>this.toast('Reference: '+data.reference,'success'),1500);

                        if(data.reference && !data.redirect_url){
                            window.location.href = '/thankyou?ref='+encodeURIComponent(data.reference);
                            return;
                        }

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
                        if(errEl){
                            errEl.textContent=data.message||'An error occurred';
                            errEl.classList.remove('d-none');
                        }else{
                            this.toast(data.message||'An error occurred','error');
                        }

                    }
                }catch(e){
                    if(errEl){
                        errEl.textContent='Network error. Please try again.';
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
        const sticky=document.querySelector('.sidebar-sticky');
        if(!sticky)return;
        const col=sticky.closest('.col-lg-4')||sticky.parentElement;
        const update=()=>{
            if(window.innerWidth<992){
                sticky.style.position='';
                sticky.style.top='';
                sticky.style.left='';
                sticky.style.width='';
                sticky.style.bottom='';
                if(col)col.style.position='';
                return;
            }
            const gap=100;
            const colRect=col.getBoundingClientRect();
            const containerTop=colRect.top+window.scrollY;
            const containerBottom=containerTop+col.offsetHeight;
            const sbh=sticky.offsetHeight;
            const y=window.scrollY;
            const start=containerTop-gap;
            const stop=containerBottom-sbh-20;
            if(y<start){
                sticky.style.position='static';
                sticky.style.top='';
                sticky.style.left='';
                sticky.style.width='';
                sticky.style.bottom='';
                if(col)col.style.position='';
            }else if(y>=start&&y<stop){
                if(col)col.style.position='relative';
                sticky.style.position='fixed';
                sticky.style.top=gap+'px';
                sticky.style.left=colRect.left+'px';
                sticky.style.width=colRect.width+'px';
                sticky.style.bottom='';
            }else{
                if(col)col.style.position='relative';
                sticky.style.position='absolute';
                sticky.style.top=(col.offsetHeight-sbh-20)+'px';
                sticky.style.left='0px';
                sticky.style.width='100%';
                sticky.style.bottom='';
            }
        };
        window.addEventListener('scroll',update,{passive:true});
        window.addEventListener('resize',update);
        setTimeout(update,0);
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
document.addEventListener('DOMContentLoaded',()=>App.init());
const style=document.createElement('style');
style.textContent='@keyframes spin{to{transform:rotate(360deg)}}.spin{animation:spin 1s linear infinite}';
document.head.appendChild(style);
})();
