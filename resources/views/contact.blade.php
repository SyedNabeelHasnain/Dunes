@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [{
    "@type": "ListItem",
    "position": 1,
    "name": "Home",
    "item": "{{ route('home') }}"
  },{
    "@type": "ListItem",
    "position": 2,
    "name": "Contact Us",
    "item": "{{ route('contact') }}"
  }]
}
</script>

<section class="page-header-modern">
    <div class="container position-relative" style="z-index: 1;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                <p class="lead mb-0 text-black">We are here to help with your Dubai adventure</p>
            </div>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container py-lg-4">
        <div class="row g-4 mb-5">
            @php
                $phoneVal = \App\Models\Setting::where('setting_key', 'site_phone')->value('setting_value') ?? '+971 50 245 6056';
                $emailVal = \App\Models\Setting::where('setting_key', 'site_email')->value('setting_value') ?? 'info@dunesdiscoverytourism.com';
                $whatsappVal = \App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056';
            @endphp
            <div class="col-12 col-md-4">
                <a href="tel:{{ preg_replace('/[^0-9+]/','',$phoneVal) }}" class="text-decoration-none">
                    <div class="card card-modern h-100 p-4 text-center border-0 shadow-sm bg-white">
                        <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mx-auto" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark">Phone</h4>
                        <p class="text-secondary mb-0">{{ $phoneVal }}</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="mailto:{{ $emailVal }}" class="text-decoration-none">
                    <div class="card card-modern h-100 p-4 text-center border-0 shadow-sm bg-white">
                        <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mx-auto" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark">Email</h4>
                        <p class="text-secondary mb-0">{{ $emailVal }}</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$whatsappVal) }}" class="text-decoration-none" target="_blank" rel="noopener">
                    <div class="card card-modern h-100 p-4 text-center border-0 shadow-sm bg-white">
                        <div class="icon-box mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mx-auto" style="width: 50px; height: 50px; font-size: 20px;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark">WhatsApp</h4>
                        <p class="text-secondary mb-0">Chat with us</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-7">
                <div class="card card-modern h-100 p-4 p-lg-5 border-0 shadow-sm bg-white">
                    <h2 class="display-6 fw-bold mb-4 text-dark">Send Us a Message</h2>
                    <form id="contactForm" autocomplete="off" class="needs-validation" novalidate>
                        @csrf
                        <!-- Honeypot field -->
                        <div style="position:absolute;left:-9999px">
                            <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="action" value="contact">
                        
                        <!-- OTP verification notification message -->
                        <div class="alert alert-info d-none mb-4" id="contactOtpNotice"></div>

                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'name',
                                    'name' => 'name',
                                    'label' => 'Full Name',
                                    'placeholder' => 'Full Name',
                                    'autocomplete' => 'name',
                                    'required' => true,
                                    'inputClass' => 'form-control form-control-modern fw-bold',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'name']
                                ]) !!}
                            </div>
                            <div class="col-12 col-md-6">
                                {!! renderFloatingInput([
                                    'type' => 'email',
                                    'id' => 'email',
                                    'name' => 'email',
                                    'label' => 'Email Address',
                                    'placeholder' => 'Email Address',
                                    'autocomplete' => 'email',
                                    'required' => true,
                                    'inputClass' => 'form-control form-control-modern fw-bold',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'email']
                                ]) !!}
                            </div>
                            <div class="col-12 col-md-6">
                                {!! renderFloatingInput([
                                    'type' => 'tel',
                                    'id' => 'phone',
                                    'name' => 'phone',
                                    'placeholder' => 'Phone Number',
                                    'autocomplete' => 'tel',
                                    'inputClass' => 'form-control form-control-modern fw-bold',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'phone']
                                ]) !!}
                            </div>
                            <div class="col-12 col-md-6">
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'subject',
                                    'name' => 'subject',
                                    'label' => 'Subject',
                                    'placeholder' => 'Subject',
                                    'autocomplete' => 'off',
                                    'inputClass' => 'form-control form-control-modern fw-bold',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'subject']
                                ]) !!}
                            </div>
                            <div class="col-12">
                                {!! renderFloatingTextarea([
                                    'id' => 'message',
                                    'name' => 'message',
                                    'label' => 'Your Message',
                                    'placeholder' => 'Your Message',
                                    'autocomplete' => 'off',
                                    'required' => true,
                                    'inputClass' => 'form-control form-control-modern fw-bold',
                                    'inputAttrs' => ['style' => 'height: 150px', 'data-form' => 'contact', 'data-field' => 'message']
                                ]) !!}
                            </div>

                            <!-- Dynamic OTP code fields if verification is needed -->
                            <div class="col-12 d-none" id="contactOtpWrapper">
                                <div class="fw-800 small text-muted text-uppercase mb-2">Email Verification Code</div>
                                <div class="input-group shadow-sm rounded-4 overflow-hidden">
                                    <input type="text" class="form-control border-0 shadow-none fw-bold text-center" id="contactOtpCode" placeholder="Enter 6-digit OTP" style="height: 60px; letter-spacing: 5px; font-size: 1.25rem;">
                                    <button class="btn btn-primary px-4 fw-bold" type="button" id="contactVerifyOtpBtn">Verify</button>
                                </div>
                                <div class="d-flex justify-content-between mt-2 px-1">
                                    <span class="small text-muted" id="contactOtpTimer"></span>
                                    <a href="#" class="small text-decoration-none fw-bold" id="contactResendOtpBtn">Resend Code</a>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="legal-agreement-wrapper">
                                    <input class="form-check-input desert-checkbox border-primary" type="checkbox" id="contactAgreement" required>
                                    <label class="legal-agreement-text" for="contactAgreement">
                                        I agree to the <a href="{{ route('terms') }}" target="_blank" class="legal-link">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" class="legal-link">Privacy Policy</a>.
                                    </label>
                                </div>
                            </div>

                            <!-- Error Message Banner -->
                            <div class="col-12 d-none" id="contactErrorAlert">
                                <div class="alert alert-danger mb-0" id="contactErrorMessage"></div>
                            </div>

                            <!-- Success Message Banner -->
                            <div class="col-12 d-none" id="contactSuccessAlert">
                                <div class="alert alert-success mb-0">Thank you! Your message has been sent successfully.</div>
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-desert-animated btn-lg rounded-pill px-5 fw-bold shadow-primary d-inline-flex align-items-center gap-2" id="submitContactBtn">
                                    <span>Send Message</span>
                                    <i class="bi bi-send fs-5"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card card-modern h-100 border-0 overflow-hidden shadow-lg rounded-4">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d462560.68279754556!2d54.89782249453am!3d25.076280448324027!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f43496ad9c645%3A0xbde66e5084295162!2sDubai%20-%20United%20Arab%20Emirates!5e0!3m2!1sen!2s!4v1704067200000" style="border:0;width:100%;height:100%;min-height: 400px;" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
