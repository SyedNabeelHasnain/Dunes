@extends('layouts.app')

@php
    $schemaPageType = 'ContactPage';
    $cmsSvc = app(\App\Services\CmsContentService::class);
    $contactPage = $cmsSvc->getPage('contact');
    $contactTitle = ($contactPage && $contactPage->title) ? $contactPage->title : __('ui.contact_page.title');
    $contactSubtitle = ($contactPage && $contactPage->subtitle) ? $contactPage->subtitle : __('ui.contact_page.subtitle');
    $channelsSec = $cmsSvc->getSection('contact', 'channels');
    $officeSec = $cmsSvc->getSection('contact', 'office_info');
@endphp

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "BreadcrumbList",
      "@@id": {!! json_encode($canonical . '#breadcrumb', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": {!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
          "item": {!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": {!! json_encode(__('ui.nav.contact') ?? 'Contact Us', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
          "item": {!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
        }
      ]
    }
  ]
}
</script>
@endpush

@section('content')

<!-- Page Header Section -->
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem);">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_15%_20%,rgba(246,144,68,0.18)_0%,transparent_60%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ __('ui.nav.contact') }}</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="glass-dark text-white rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-headset text-primary"></i>{{ __('ui.contact_page.concierge') }}
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i>{{ __('ui.trust.licensed_operator') }}
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">{{ $contactTitle }}</h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">{{ $contactSubtitle }}</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-lightning-charge"></i>{{ __('ui.contact_page.instant_whatsapp') }}
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
        
        @if($cmsSvc->isSectionVisible('contact', 'channels'))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-6 mb-8 sm:mb-12">
            <a href="tel:{{ preg_replace('/[^0-9+]/','',$phoneVal) }}" class="bg-slate-50 hover:bg-white rounded-2xl p-5 sm:p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-telephone"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">{{ __('ui.contact_page.phone_title') }}</div>
                <div class="text-slate-600 text-sm">{{ $phoneVal }}</div>
            </a>
            
            <a href="mailto:{{ $emailVal }}" class="bg-slate-50 hover:bg-white rounded-2xl p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">{{ __('ui.contact_page.email_title') }}</div>
                <div class="text-slate-600 text-sm">{{ $emailVal }}</div>
            </a>
            
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$whatsappVal) }}" target="_blank" rel="noopener" class="bg-slate-50 hover:bg-white rounded-2xl p-6 text-center border border-slate-200 hover:border-primary/40 shadow-xs hover:shadow-lg transition-all duration-300 group cursor-pointer btn-whatsapp" data-action="whatsapp">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div class="font-bold text-slate-900 text-base mb-1">{{ __('ui.contact_page.whatsapp_title') }}</div>
                <div class="text-slate-600 text-sm">{{ __('ui.contact_page.chat_247') }}</div>
            </a>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="{{ $cmsSvc->isSectionVisible('contact', 'office_info') ? 'lg:col-span-7' : 'lg:col-span-12 max-w-3xl mx-auto w-full' }}">
                <div class="bg-white rounded-2xl p-6 sm:p-10 border border-slate-200 shadow-md">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.contact_page.send_message') }}</h2>
                    <form id="contactForm" method="POST" action="{{ route('contact.submit') }}" autocomplete="off" class="needs-validation space-y-5" novalidate
                          data-pow-difficulty="{{ (int) ($contactSecurityChallenge['difficulty'] ?? 3) }}"
                          data-min-dwell="{{ (int) ($contactSecurityChallenge['min_dwell_seconds'] ?? 3) }}"
                          data-otp-required="{{ ($contactOtpRequired ?? true) ? '1' : '0' }}"
                          data-token-endpoint="{{ route('security.form-token') }}">
                        @csrf
                        <!-- Multi-Trap Invisible Honeypot Fields (never filled by real humans) -->
                        <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none;" aria-hidden="true">
                            <label for="hp_website_url">Website</label>
                            <input type="text" id="hp_website_url" name="website_url" value="" tabindex="-1" autocomplete="new-password">
                            <label for="hp_company_fax">Fax</label>
                            <input type="text" id="hp_company_fax" name="company_fax" value="" tabindex="-1" autocomplete="new-password">
                            <label for="hp_confirm_email_alt">Confirm Alternate Email</label>
                            <input type="email" id="hp_confirm_email_alt" name="confirm_email_alt" value="" tabindex="-1" autocomplete="new-password">
                        </div>
                        <input type="hidden" name="action" value="contact">
                        <input type="hidden" name="_dunes_token" id="contactSecurityToken" value="{{ $contactSecurityChallenge['token'] ?? '' }}">
                        <input type="hidden" name="_dunes_pow" id="contactSecurityPow" value="">
                        <input type="hidden" name="_dunes_telemetry" id="contactSecurityTelemetry" value="">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'text',
                                    'id' => 'name',
                                    'name' => 'name',
                                    'label' => __('ui.contact_page.full_name'),
                                    'placeholder' => __('ui.contact_page.full_name'),
                                    'autocomplete' => 'name',
                                    'required' => true,
                                    'inputAttrs' => ['maxlength' => '80', 'data-form' => 'contact', 'data-field' => 'name']
                                ]) !!}
                            </div>

                            <!-- Email Address + Interactive On-Spot OTP Verification (Mirroring Booking Flow) -->
                            <div id="contactEmailSection">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider" for="email">{{ __('ui.contact_page.email_address') }} <span class="text-rose-500">*</span></label>
                                    <span id="contactEmailStatusBadge" class="hidden items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold tracking-wide transition-all"></span>
                                </div>
                                <div class="relative flex items-center rounded-xl bg-white border border-slate-300 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all" id="contactEmailContainer">
                                    <input type="email"
                                           class="w-full pl-3.5 pr-24 py-3 rounded-xl bg-transparent text-sm font-semibold text-slate-900 border-0 focus:outline-none placeholder:text-slate-400"
                                           id="email"
                                           name="email"
                                           placeholder="{{ __('ui.contact_page.email_address') }}"
                                           autocomplete="email"
                                           required
                                           data-form="contact"
                                           data-field="email">
                                    <div class="absolute right-1.5 inset-y-1.5 flex items-center" id="contactEmailActionSlot">
                                        <span id="contactEmailCheckSpinner" class="hidden px-2.5 text-primary items-center">
                                            <span class="inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                                        </span>
                                        <button type="button" id="contactSendEmailOtpBtn" class="hidden h-full px-3 rounded-lg bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-[11px] uppercase tracking-wider shadow-xs transition-all cursor-pointer items-center gap-1 shrink-0">
                                            <i class="bi bi-shield-lock-fill text-xs"></i>
                                            <span>{{ __('ui.booking.verify') }}</span>
                                        </button>
                                        <span id="contactEmailVerifiedIcon" class="hidden px-2.5 text-emerald-500 text-lg items-center" title="Email Verified">
                                            <i class="bi bi-patch-check-fill"></i>
                                        </span>
                                    </div>
                                </div>
                                <p id="contactEmailHelperText" class="text-[11px] font-medium text-slate-500 mt-1 hidden"></p>
                            </div>

                            <!-- Interactive 6-Digit OTP Verification Card (Full Width Below Name/Email Row) -->
                            <div class="sm:col-span-2 hidden transition-all duration-300" id="contactOtpFieldsWrapper">
                                <div class="rounded-2xl bg-gradient-to-br from-orange-50/90 via-white to-amber-50/60 border-2 border-orange-200/90 p-4 shadow-md">
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                <i class="bi bi-envelope-check-fill text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-slate-900 uppercase tracking-wide leading-tight">{{ __('ui.booking.email_verification') }}</div>
                                                <div class="text-[11px] text-slate-600 truncate mt-0.5">
                                                    6-digit verification code sent to <span id="contactOtpTargetEmail" class="font-bold text-slate-900">your email</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200/80 text-[10px] font-extrabold shrink-0">Valid 5m</span>
                                    </div>

                                    <input type="hidden" id="contactOtpCode" value="">

                                    <div class="grid grid-cols-6 gap-1.5 sm:gap-2.5 my-2.5 max-w-md mx-auto" id="contactOtpDigitsContainer" dir="ltr">
                                        @for($d = 0; $d < 6; $d++)
                                        <input type="text"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               maxlength="1"
                                               data-contact-otp-index="{{ $d }}"
                                               autocomplete="{{ $d === 0 ? 'one-time-code' : 'off' }}"
                                               aria-label="Verification Digit {{ $d + 1 }}"
                                               class="contact-otp-digit-box w-full h-11 sm:h-12 text-center font-mono font-black text-base sm:text-lg text-slate-900 bg-white border-2 border-slate-200 rounded-xl shadow-2xs focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
                                        @endfor
                                    </div>

                                    <div id="contactOtpErrorMsg" class="hidden items-center gap-1.5 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl px-3 py-2 my-2">
                                        <i class="bi bi-exclamation-circle-fill shrink-0"></i>
                                        <span id="contactOtpErrorText">Invalid verification code.</span>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-2.5 border-t border-orange-200/60">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-slate-500 font-semibold text-[11px]" id="contactOtpTimer"></span>
                                            <button type="button" class="font-extrabold text-primary hover:text-primary-dark hover:underline text-xs cursor-pointer disabled:opacity-50 disabled:pointer-events-none py-1" id="contactResendOtpBtn">
                                                <i class="bi bi-arrow-repeat me-0.5"></i>{{ __('ui.booking.resend_code') }}
                                            </button>
                                        </div>
                                        <button type="button" id="contactVerifyOtpBtn" class="px-4 py-2 min-h-[42px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-all cursor-pointer inline-flex items-center gap-1.5 ms-auto">
                                            <i class="bi bi-check2-circle"></i>
                                            <span>{{ __('ui.booking.verify') }} Code</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                {!! renderFloatingInput([
                                    'type' => 'tel',
                                    'id' => 'phone',
                                    'name' => 'phone',
                                    'label' => __('ui.contact_page.phone_number'),
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
                                    'label' => __('ui.contact_page.subject'),
                                    'placeholder' => __('ui.contact_page.subject'),
                                    'autocomplete' => 'off',
                                    'inputAttrs' => ['maxlength' => '160', 'data-form' => 'contact', 'data-field' => 'subject']
                                ]) !!}
                            </div>
                        </div>

                        <div>
                            {!! renderFloatingTextarea([
                                'id' => 'message',
                                'name' => 'message',
                                'label' => __('ui.contact_page.your_message'),
                                'placeholder' => __('ui.contact_page.your_message'),
                                'autocomplete' => 'off',
                                'required' => true,
                                'inputAttrs' => ['maxlength' => '5000', 'style' => 'height: 140px', 'data-form' => 'contact', 'data-field' => 'message']
                            ]) !!}
                        </div>

                        @if(!empty($turnstileEnabled) && !empty($turnstileSiteKey))
                        <div class="pt-1">
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="light"></div>
                        </div>
                        @endif

                        <div>
                            <div class="legal-agreement-wrapper flex items-start gap-2.5 min-h-[44px]">
                                <input class="rounded text-primary focus:ring-primary w-5 h-5 mt-0.5 cursor-pointer shrink-0" type="checkbox" id="contactAgreement" required>
                                <label class="text-xs text-slate-600 leading-normal cursor-pointer py-1" for="contactAgreement">
                                    I agree to the <a href="{{ localized_route('terms') }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-semibold">{{ __('ui.footer.terms') }}</a> and <a href="{{ localized_route('privacy') }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-semibold">{{ __('ui.footer.privacy') }}</a>.
                                </label>
                            </div>
                        </div>

                        <!-- Error Message Banner -->
                        <div class="hidden" id="contactErrorAlert">
                            <div class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-xl text-xs sm:text-sm font-semibold" id="contactErrorMessage"></div>
                        </div>

                        <!-- Success Message Banner -->
                        <div class="hidden" id="contactSuccessAlert">
                            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-3 rounded-xl text-xs sm:text-sm font-semibold" id="contactSuccessMessage">Thank you! Your message has been sent successfully.</div>
                        </div>

                        <div class="text-center pt-2 space-y-2.5">
                            <button type="submit" class="btn-desert-animated text-base font-bold rounded-full px-8 py-3.5 text-white shadow-md inline-flex items-center gap-2 cursor-pointer w-full sm:w-auto justify-center" id="submitContactBtn">
                                <span>{{ __('ui.contact_page.send_btn') }}</span>
                                <i class="bi bi-send"></i>
                            </button>
                            <div class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
                                <i class="bi bi-shield-lock-fill text-emerald-600"></i>
                                <span>Protected by Multi-Layer Anti-Spam &amp; Instant Email Verification</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            @if($cmsSvc->isSectionVisible('contact', 'office_info'))
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Google Verified Entity & Review Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-md">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                                <i class="bi bi-google"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 leading-tight">Dunes Discovery Tourism LLC</h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <div class="flex text-amber-400 text-xs">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900">4.9</span>
                                    <span class="text-[11px] text-slate-500">(2,840+ {{ __('ui.contact_page.google_reviews') }})</span>
                                </div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200 shrink-0">
                            <i class="bi bi-patch-check-fill text-emerald-600"></i> Verified
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Official Dubai Department of Economy and Tourism (DET) Licensed Desert Safari & Adventure Destination Management Operator.
                    </p>

                    <div class="space-y-2 mb-5 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-geo-alt-fill text-primary shrink-0"></i>
                            <span>{{ $settings['site_address'] ?? 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock-fill text-slate-400 shrink-0"></i>
                            <span>{{ __('ui.contact_page.business_hours') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-shield-check text-emerald-600 shrink-0"></i>
                            <span>{{ __('ui.contact_page.license_verified') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-100">
                        <a href="{{ $settings['google_review_url'] ?? ('https://search.google.com/local/writereview?placeid=' . ($settings['google_place_id'] ?? 'ChIJbWsIEIVEdEER4uHEhb2dbcQ')) }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-800 transition">
                            <i class="bi bi-star-fill text-amber-500"></i>
                            <span>{{ __('ui.contact_page.write_review') }}</span>
                        </a>
                        <a href="https://maps.google.com/?q={{ $settings['site_latitude'] ?? '25.2048' }},{{ $settings['site_longitude'] ?? '55.2708' }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-primary hover:bg-[#e07b32] text-xs font-bold text-white shadow-xs transition">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ __('ui.contact_page.get_directions') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Google Maps Interactive Embed -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-slate-200 flex-grow aspect-video sm:aspect-auto sm:h-96 min-h-[260px]">
                    <iframe src="{{ $settings['google_maps_embed_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14439.467468694034!2d55.2707828!3d25.2048493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f434910086b6d%3A0xc4db9db186e4e1e2!2sDunes%20Discovery%20Tourism%20LLC!5e0!3m2!1sen!2sae!4v1700000000000!5m2!1sen!2sae' }}" class="w-full h-full border-0 min-h-[260px]" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@if(!empty($turnstileEnabled) && !empty($turnstileSiteKey))
@push('scripts')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endpush
@endif
@endsection
