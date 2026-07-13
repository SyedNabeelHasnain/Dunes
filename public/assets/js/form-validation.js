function shakeField(field){
    field.classList.add('shake-field');
    setTimeout(() => field.classList.remove('shake-field'), 500);
}

document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    const clearState = (field) => {
        field.classList.remove('field-valid', 'field-invalid', 'field-processing');
    };
    const setState = (field, state) => {
        clearState(field);
        if (state) field.classList.add(state);
    };
    const shake = (field) => {
        field.classList.add('shake-field');
        setTimeout(() => field.classList.remove('shake-field'), 500);
    };

    const initTelInput = (input) => {
        if (input.dataset.itiInitialized) return;
        input.dataset.itiInitialized = "true";
        clearState(input);
        const iti = window.intlTelInput(input, {
            loadUtils: () => import("/assets/vendor/intl-tel-input/26.0.6/build/utils.js"),
            separateDialCode: true,
            initialCountry: "auto",
            geoIpLookup: (success, failure) => {
                fetch("/ajax.php?action=geoip")
                    .then(res => res.text())
                    .then(t => { try { return JSON.parse(t.replace(/^\uFEFF+/, '').trim()); } catch(e){ throw e; } })
                    .then(data => success(data.country_code))
                    .catch(() => failure());
            },
            countryOrder: ["ae", "us", "gb", "in", "pk", "sa"],
            formatOnDisplay: true,
            formatAsYouType: true,
            autoPlaceholder: "aggressive",
            countrySearch: true,
            fixDropdownWidth: true,
            allowPhonewords: false
        });
        input.iti = iti;

        const validatePhone = (markTouched = false) => {
            if (markTouched) input.dataset.touched = "true";
            input.classList.remove('valid-phone', 'invalid-phone');
            clearState(input);

            if (input.value.trim()) {
                if (iti.isValidNumber()) {
                    input.classList.add('valid-phone');
                    setState(input, 'field-valid');
                    input.value = iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL);
                } else {
                    input.classList.add('invalid-phone');
                    setState(input, 'field-invalid');
                    shake(input);
                }
            } else {
                if (input.required && input.dataset.touched === "true") {
                    setState(input, 'field-invalid');
                    shake(input);
                }
            }
            validateForm(input.closest('form'), markTouched ? input : null);
        };

        input.addEventListener('blur', () => validatePhone(true));
        input.addEventListener('input', () => {
            const val = input.value;
            const clean = val.replace(/[a-zA-Z]/g, '');
            if (val !== clean) {
                input.value = clean;
            }

            input.classList.remove('invalid-phone', 'valid-phone');
            clearState(input);
        });

        input.addEventListener('countrychange', () => {
            updateLabelPosition();
        });

        const updateLabelPosition = () => {
            const wrapper = input.closest('.input-float') || input.closest('.form-floating');
            const label = wrapper ? wrapper.querySelector('label') : null;

            if (label) {
                const padding = window.getComputedStyle(input).paddingLeft;
                if (padding && parseFloat(padding) > 20) {
                    label.style.paddingLeft = padding;
                    label.style.zIndex = '3';
                }
            }
        };

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    updateLabelPosition();
                }
            });
        });

        observer.observe(input, { attributes: true, attributeFilter: ['style'] });

        updateLabelPosition();
        window.addEventListener('resize', updateLabelPosition);
        input.addEventListener('countrychange', updateLabelPosition);

        const updateFloatLabel = () => {
             const wrapper = input.closest('.input-float') || input.closest('.form-floating');
             if (wrapper) {
                 if (input.value.trim() !== '') {
                     wrapper.classList.add('has-value');
                 } else {
                     wrapper.classList.remove('has-value');
                 }
             }
        };
        input.addEventListener('blur', updateFloatLabel);
        input.addEventListener('input', updateFloatLabel);
        updateFloatLabel();
    };
    phoneInputs.forEach(input => {
        input.addEventListener('focus', () => initTelInput(input), { once: true });
        input.addEventListener('mousedown', () => initTelInput(input), { once: true });
        input.addEventListener('touchstart', () => initTelInput(input), { once: true });
    });

    const forms = document.querySelectorAll('form.needs-validation');
    forms.forEach(form => {
        form.setAttribute('novalidate', '');

        form.addEventListener('submit', event => {
            if (!validateForm(form, null, true)) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => validateForm(form, field));
        });
    });
});

function validateForm(form, touchedField = null, force = false) {
    if (!form) return true;
    let isValid = true;
    if (touchedField) touchedField.dataset.touched = "true";
    const shouldValidate = (field) => force || form.classList.contains('was-validated') || field.dataset.touched === "true";

    form.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.type === 'hidden' || field.disabled) return;
        if (field.classList.contains('field-processing')) return;
        const required = field.hasAttribute('required');
        const value = field.value.trim();
        const touched = shouldValidate(field);

        field.classList.remove('field-invalid');
        if (!field.classList.contains('is-verified')) {
            field.classList.remove('field-valid');
        }

        if (!value) {
            if (required && touched) {
                field.classList.add('field-invalid');
                isValid = false;
                if (force || touched) shakeField(field);
            }
            return;
        }

        if (field.type === 'email') {
            if (!isValidEmail(value)) {
                field.classList.add('field-invalid');
                isValid = false;
                if (force || touched) shakeField(field);
            } else if (field.classList.contains('is-verified')) {
                field.classList.add('field-valid');
            } else if (touched) {
                if (force) {
                    field.classList.add('field-invalid');
                    isValid = false;
                    shakeField(field);
                }
            }
            return;
        }

        if (field.type === 'tel') {
            if (field.iti && force) {
                if (field.iti.isValidNumber()) {
                    field.classList.add('valid-phone', 'field-valid');
                    return;
                } else {
                    field.classList.add('invalid-phone', 'field-invalid');
                    isValid = false;
                    shakeField(field);
                    return;
                }
            }
            if (field.classList.contains('valid-phone')) {
                field.classList.add('field-valid');
                return;
            }
            if (field.classList.contains('invalid-phone')) {
                field.classList.add('field-invalid');
                isValid = false;
                if (force) shakeField(field);
                return;
            }
            if (force) {
                field.classList.add('field-invalid');
                isValid = false;
                shakeField(field);
            }
            return;
        }

        if (field.name === 'name' && value.length < 3) {
            field.classList.add('field-invalid');
            isValid = false;
            if (force || touched) shakeField(field);
            return;
        }

        if (field.classList.contains('desert-checkbox')) return; // Skip success validation for agreement checkboxes

        field.classList.add('field-valid');
    });

    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
