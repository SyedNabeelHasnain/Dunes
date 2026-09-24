@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "ContactPage",
      "@id": "{{ route('contact') }}#webpage",
      "url": "{{ route('contact') }}",
      "name": "Contact Dunes Discovery Tourism Dubai",
      "description": "Contact Dunes Discovery Tourism LLC for 24/7 Dubai desert safari bookings, dune buggy inquiries, and private group packages.",
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ rtrim(route('home'), '/') }}/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Contact Us",
            "item": "{{ route('contact') }}"
          }
        ]
      },
      "mainEntity": {
        "@type": "TravelAgency",
        "@id": "{{ route('home') }}#organization",
        "name": "{{ $settings['site_name'] ?? 'Dunes Discovery Tourism LLC' }}",
        "telephone": "{{ $settings['site_phone'] ?? '+971 50 245 6056' }}",
        "email": "{{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "{{ $settings['site_address'] ?? 'Al Fahidi, Bur Dubai' }}",
          "addressLocality": "Dubai",
          "addressRegion": "Dubai",
          "postalCode": "00000",
          "addressCountry": "AE"
        },
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": "25.2048",
          "longitude": "55.2708"
        },
        "openingHoursSpecification": {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
          "opens": "00:00",
          "closes": "23:59"
        }
      }
    }
  ]
}
</script>
@endpush

<!-- Page Header Section -->
<section class="py-10 bg-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.18)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-16">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">Contact Us</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass rounded-full px-3.5 py-1 text-xs inline-flex items-center gap-1.5">
                        <i class="bi bi-headset text-primary"></i>24/7 Dedicated Concierge
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>DTCM Licensed Operator
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">Get in Touch with Us</h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">We are ready 24/7 to assist with your Dubai safari reservations, custom itineraries & group inquiries.</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-lightning-charge"></i>Instant WhatsApp Response
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-slate-50 py-3.5 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-patch-check-fill text-primary text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">DTCM Licensed Operator</div>
                    <div class="text-slate-500 text-[11px]">Dubai Tourism Authority</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Free Cancellation</div>
                    <div class="text-slate-500 text-[11px]">Full refund 24h prior</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">100% Halal Food</div>
                    <div class="text-slate-500 text-[11px]">Veg, Non-Veg & Jain</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-shield-lock-fill text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">Direct Operator</div>
                    <div class="text-slate-500 text-[11px]">No Middleman Markup</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $phoneVal = $settings['site_phone'] ?? '+971 50 245 6056';
            $emailVal = $settings['site_email'] ?? 'info@dunesdiscoverytourism.com';
            $whatsappVal = $settings['site_whatsapp'] ?? '971502456056';
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <a href="tel:{{ preg_replace('/[^0-9+]/','',$phoneVal) }}" class="bg-slate-50 hover:bg-white rounded-2xl p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-telephone"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">Phone</div>
                <div class="text-slate-600 text-sm">{{ $phoneVal }}</div>
            </a>
            
            <a href="mailto:{{ $emailVal }}" class="bg-slate-50 hover:bg-white rounded-2xl p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">Email</div>
                <div class="text-slate-600 text-sm">{{ $emailVal }}</div>
            </a>
            
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$whatsappVal) }}" target="_blank" rel="noopener" class="bg-slate-50 hover:bg-white rounded-2xl p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">WhatsApp</div>
                <div class="text-slate-600 text-sm">Chat with us 24/7</div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl p-6 sm:p-10 border border-slate-200 shadow-md">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">Send Us a Message</h2>
                    <form id="contactForm" autocomplete="off" class="needs-validation space-y-5" novalidate>
                        @csrf
                        <!-- Honeypot field -->
                        <div style="position:absolute;left:-9999px">
                            <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="action" value="contact">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'name',
                                    'name' => 'name',
                                    'label' => 'Full Name',
                                    'placeholder' => 'Full Name',
                                    'autocomplete' => 'name',
                                    'required' => true,
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'name']
                                ]) !!}
                            </div>
                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'email',
                                    'id' => 'email',
                                    'name' => 'email',
                                    'label' => 'Email Address',
                                    'placeholder' => 'Email Address',
                                    'autocomplete' => 'email',
                                    'required' => true,
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'email']
                                ]) !!}
                            </div>
                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'tel',
                                    'id' => 'phone',
                                    'name' => 'phone',
                                    'label' => 'Phone Number',
                                    'placeholder' => '50 123 4567',
                                    'autocomplete' => 'tel',
                                    'wrapperClass' => 'relative rounded-xl phone-field',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'phone']
                                ]) !!}
                            </div>
                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'subject',
                                    'name' => 'subject',
                                    'label' => 'Subject',
                                    'placeholder' => 'Subject',
                                    'autocomplete' => 'off',
                                    'inputAttrs' => ['data-form' => 'contact', 'data-field' => 'subject']
                                ]) !!}
                            </div>
                        </div>

                        <div>
                            {!! renderFloatingTextarea([
                                'id' => 'message',
                                'name' => 'message',
                                'label' => 'Your Message',
                                'placeholder' => 'Your Message',
                                'autocomplete' => 'off',
                                'required' => true,
                                'inputAttrs' => ['style' => 'height: 140px', 'data-form' => 'contact', 'data-field' => 'message']
                            ]) !!}
                        </div>

                        <div>
                            <div class="legal-agreement-wrapper flex items-start gap-2.5">
                                <input class="rounded text-primary focus:ring-primary w-4 h-4 mt-0.5 cursor-pointer" type="checkbox" id="contactAgreement" required>
                                <label class="text-xs text-slate-600 leading-normal" for="contactAgreement">
                                    I agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-semibold">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-semibold">Privacy Policy</a>.
                                </label>
                            </div>
                        </div>

                        <!-- Error Message Banner -->
                        <div class="hidden" id="contactErrorAlert">
                            <div class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-xl text-xs sm:text-sm font-semibold" id="contactErrorMessage"></div>
                        </div>

                        <!-- Success Message Banner -->
                        <div class="hidden" id="contactSuccessAlert">
                            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-3 rounded-xl text-xs sm:text-sm font-semibold">Thank you! Your message has been sent successfully.</div>
                        </div>

                        <div class="text-center pt-2">
                            <button type="submit" class="btn-desert-animated text-base font-bold rounded-full px-8 py-3.5 text-white shadow-md inline-flex items-center gap-2 cursor-pointer w-full sm:w-auto justify-center" id="submitContactBtn" onclick="if(typeof gtagReportConversion==='function'){gtagReportConversion();}">
                                <span>Send Message</span>
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="lg:col-span-5 flex flex-col">
                <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-slate-200 flex-grow h-full min-h-[350px]">
                    <iframe src="{{ $settings['google_maps_embed_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14439.467468694034!2d55.2707828!3d25.2048493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f434910086b6d%3A0xc4db9db186e4e1e2!2sDunes%20Discovery%20Tourism%20LLC!5e0!3m2!1sen!2sae!4v1700000000000!5m2!1sen!2sae' }}" class="w-full h-full border-0 min-h-[400px]" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function() {
            if (typeof window.gtag === 'function') {
                window.gtag('event', 'conversion', {
                    'send_to': '{{ (!empty($settings['google_ads_id']) && !empty($settings['google_conversion_label'])) ? ($settings['google_ads_id'] . "/" . $settings['google_conversion_label']) : "AW-17859624049/eR3SCLimtvobEPH4kMRC" }}'
                });
            }
        });
    }
});
</script>
@endpush
@endsection
