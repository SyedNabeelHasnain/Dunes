@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebPage",
      "@@id": "{{ request()->url() }}#webpage",
      "url": "{{ request()->url() }}",
      "name": "{{ $pageTitle ?? $page->title }}",
      "description": "{{ $pageDesc ?? $page->description }}",
      "inLanguage": ["en", "ar"],
      "isPartOf": {
        "@@type": "WebSite",
        "@@id": "{{ route('home') }}#website",
        "url": "{{ route('home') }}",
        "name": "Dunes Discovery Tourism LLC Dubai"
      },
      "publisher": {
        "@@type": "TouristInformationCenter",
        "@@id": "{{ route('home') }}#organization",
        "name": "Dunes Discovery Tourism L.L.C",
        "url": "{{ route('home') }}",
        "telephone": "+971502456056",
        "email": "info@dunesdiscoverytourism.com",
        "address": {
          "@@type": "PostalAddress",
          "addressLocality": "Dubai",
          "addressCountry": "AE"
        }
      }
    },
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ request()->url() }}#breadcrumb",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('home') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Legal & Policies",
          "item": "{{ route('terms') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": "{{ $page->title }}",
          "item": "{{ request()->url() }}"
        }
      ]
    }
  ]
}
</script>

<style>
/* Legal Page Styling & Language Controls */
.legal-lang-switch .btn-check:checked + .btn {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.25);
}
.legal-toc-list {
    position: sticky;
    top: calc(var(--header-h, 80px) + 20px);
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}
.legal-toc-link {
    display: block;
    padding: 6px 12px;
    font-size: 0.85rem;
    color: #495057;
    border-left: 2px solid transparent;
    text-decoration: none;
    transition: all 0.2s ease;
}
.legal-toc-link:hover,
.legal-toc-link.active {
    color: var(--bs-primary);
    border-left-color: var(--bs-primary);
    background-color: rgba(255, 107, 0, 0.05);
    font-weight: 600;
}
.legal-rtl {
    direction: rtl;
    text-align: right;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.legal-rtl .border-start {
    border-start-width: 0 !important;
    border-right-width: 4px !important;
    border-right-style: solid !important;
    padding-right: 1rem !important;
    padding-left: 0 !important;
}
.legal-rtl .legal-toc-link {
    border-left-width: 0;
    border-right: 2px solid transparent;
}
.legal-rtl .legal-toc-link:hover,
.legal-rtl .legal-toc-link.active {
    border-right-color: var(--bs-primary);
}
@media print {
    #header, .footer, .btn-circle-whatsapp, .whatsapp-floating-btn, .rc-floating-bar,
    .legal-lang-switch, .legal-toc-col, .legal-action-bar, .legal-contact-card, .breadcrumb {
        display: none !important;
    }
    .page-header {
        background: none !important;
        color: #000 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .page-header h1, .page-header p {
        color: #000 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
}
</style>

<!-- Hero / Page Header -->
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h)); padding-top: calc(var(--header-h) + 1.5rem) !important;">
    <div class="container position-relative z-1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none small">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('terms') }}" class="text-white text-opacity-75 text-decoration-none small">Legal & Trust</a></li>
                <li class="breadcrumb-item active text-white small" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 badge bg-primary-subtle text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 mb-2 fw-semibold">
                    <i class="bi bi-shield-check"></i>
                    <span>Official UAE Tourism Document &bull; License 1430583</span>
                </div>
                <h1 class="display-5 fw-800 text-white mb-1" id="legalPageHeaderTitle">{{ $page->title }}</h1>
                <p class="lead text-white text-opacity-75 mb-0 fs-6" id="legalPageHeaderSubtitle">
                    {{ $page->subtitle ?: 'Dunes Discovery Tourism L.L.C &bull; Dubai, United Arab Emirates' }}
                </p>
            </div>

            <!-- Language Switcher Pills -->
            <div class="legal-lang-switch bg-black bg-opacity-50 p-1 rounded-pill border border-white border-opacity-25 d-inline-flex flex-shrink-0 align-self-start align-self-md-center">
                <input type="radio" class="btn-check" name="legalLang" id="langEn" autocomplete="off" checked onchange="switchLegalLanguage('en')">
                <label class="btn btn-sm text-white rounded-pill px-3 py-1 mb-0 d-flex align-items-center gap-2" for="langEn">
                    <span>🇬🇧</span>
                    <span>English</span>
                </label>

                <input type="radio" class="btn-check" name="legalLang" id="langAr" autocomplete="off" onchange="switchLegalLanguage('ar')">
                <label class="btn btn-sm text-white rounded-pill px-3 py-1 mb-0 d-flex align-items-center gap-2" for="langAr">
                    <span>🇦🇪</span>
                    <span>العربية</span>
                </label>
            </div>
        </div>
    </div>
</section>

<!-- Main Legal Body -->
<section class="section py-5 bg-light">
    <div class="container">
        
        <!-- Utility Action Bar -->
        <div class="legal-action-bar bg-white rounded-4 shadow-sm border p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 text-muted small">
                <span class="d-flex align-items-center gap-1"><i class="bi bi-calendar-check text-primary"></i> Last Verified: September 2026</span>
                <span class="d-none d-sm-inline text-opacity-25 text-dark">|</span>
                <span class="d-none d-sm-flex align-items-center gap-1"><i class="bi bi-building text-primary"></i> Dubai DET Registered</span>
                <span class="d-none d-md-inline text-opacity-25 text-dark">|</span>
                <span class="d-none d-md-flex align-items-center gap-1"><i class="bi bi-translate text-primary"></i> Bilingual (EN / AR)</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-printer"></i>
                    <span>Print / Save PDF</span>
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $waPhone ?? '971502456056') }}?text={{ urlencode('Hello Dunes Discovery Tourism, I have an inquiry regarding: ' . $page->title) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-pill px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp"></i>
                    <span>Inquire via WhatsApp</span>
                </a>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Table of Contents / Quick Jump (Desktop) -->
            <div class="col-lg-3 d-none d-lg-block legal-toc-col">
                <div class="legal-toc-list bg-white rounded-4 shadow-sm border p-3">
                    <div class="d-flex align-items-center gap-2 pb-2 mb-2 border-bottom">
                        <i class="bi bi-list-nested text-primary"></i>
                        <span class="fw-bold text-dark small text-uppercase tracking-wider">Document Sections</span>
                    </div>
                    <nav class="nav flex-column gap-1" id="legalTocNav">
                        @foreach($page->sections as $sec)
                        <a href="#sec-{{ $sec->id }}" class="legal-toc-link rounded-2" data-en="{{ $sec->heading }}" data-ar="{{ $sec->heading_ar ?: $sec->heading }}">
                            {{ $sec->heading }}
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Content Column -->
            <div class="col-12 col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white" style="border-top: 5px solid var(--bs-primary) !important;">
                    <div class="card-body p-4 p-lg-5">

                        <!-- English Container -->
                        <div id="contentEn" class="legal-content-container">
                            @if($page->description)
                            <div class="p-4 bg-light rounded-4 border-start border-4 border-primary mb-5 lead text-dark fs-6" style="line-height: 1.8;">
                                {!! nl2br(e($page->description)) !!}
                            </div>
                            @endif

                            @if($page->sections->count() > 0)
                                <div class="legal-sections-wrapper">
                                    @foreach($page->sections as $section)
                                        <div class="legal-section-block mb-5 pt-2" id="sec-{{ $section->id }}">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                                <h2 class="h4 fw-800 text-dark mb-0 border-start border-4 border-primary ps-3">
                                                    {{ $section->heading }}
                                                </h2>
                                                <a href="#sec-{{ $section->id }}" class="text-muted opacity-50 text-decoration-none small pt-1" title="Direct Link to Clause">
                                                    <i class="bi bi-link-45deg fs-5"></i>
                                                </a>
                                            </div>

                                            @if($section->subheading)
                                                <h3 class="h6 fw-bold text-secondary mb-3 ms-4">{{ $section->subheading }}</h3>
                                            @endif

                                            @if($section->items->count() > 0)
                                                <ul class="list-unstyled mb-0 d-grid gap-3 ms-2">
                                                    @foreach($section->items as $item)
                                                        <li class="d-flex align-items-start gap-3">
                                                            <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                                                            <span class="text-secondary" style="line-height: 1.7;">{{ $item->content }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <hr class="my-4 border-primary opacity-15">
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-hourglass-split fs-1 mb-3 d-block text-primary"></i>
                                    <p class="mb-0">Legal document updates in progress.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Arabic Container (RTL) -->
                        <div id="contentAr" class="legal-content-container legal-rtl d-none">
                            @if($page->description_ar || $page->description)
                            <div class="p-4 bg-light rounded-4 border-end border-4 border-primary mb-5 lead text-dark fs-6" style="line-height: 1.9;">
                                {!! nl2br(e($page->description_ar ?: $page->description)) !!}
                            </div>
                            @endif

                            @if($page->sections->count() > 0)
                                <div class="legal-sections-wrapper">
                                    @foreach($page->sections as $section)
                                        <div class="legal-section-block mb-5 pt-2" id="sec-ar-{{ $section->id }}">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                                <h2 class="h4 fw-800 text-dark mb-0 border-end border-4 border-primary pe-3">
                                                    {{ $section->heading_ar ?: $section->heading }}
                                                </h2>
                                                <a href="#sec-ar-{{ $section->id }}" class="text-muted opacity-50 text-decoration-none small pt-1" title="رابط مباشر للبند">
                                                    <i class="bi bi-link-45deg fs-5"></i>
                                                </a>
                                            </div>

                                            @if($section->subheading_ar || $section->subheading)
                                                <h3 class="h6 fw-bold text-secondary mb-3 me-4">{{ $section->subheading_ar ?: $section->subheading }}</h3>
                                            @endif

                                            @if($section->items->count() > 0)
                                                <ul class="list-unstyled mb-0 d-grid gap-3 me-2">
                                                    @foreach($section->items as $item)
                                                        <li class="d-flex align-items-start gap-3">
                                                            <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                                                            <span class="text-secondary" style="line-height: 1.8;">{{ $item->content_ar ?: $item->content }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <hr class="my-4 border-primary opacity-15">
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-hourglass-split fs-1 mb-3 d-block text-primary"></i>
                                    <p class="mb-0">جاري تحديث المحتوى القانوني.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Assistance / Contact Banner -->
                <div class="legal-contact-card mt-5 p-4 p-md-5 text-center bg-white rounded-4 shadow-sm border">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle rounded-circle shadow-sm mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-shield-lock-fill fs-2 text-primary"></i>
                    </div>
                    <h3 class="fw-800 text-dark mb-2" id="legalHelpTitle">Questions About Our Policies or Compliance?</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 540px;" id="legalHelpDesc">
                        Our legal compliance desk and customer concierge team in Dubai are available 24/7 to assist with any policy questions, corporate travel agreements, or booking amendments.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $waPhone ?? '971502456056') }}?text={{ urlencode('Hi Dunes Discovery Tourism, I have a question regarding: ' . $page->title) }}" class="btn btn-whatsapp-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            <span>Chat with Legal Desk</span>
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-desert-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-envelope"></i>
                            <span>Submit Formal Inquiry</span>
                        </a>
                    </div>
                </div>

                <!-- Trust Badges & Policy Sitemap Links -->
                <div class="mt-4 p-3 bg-white rounded-4 border text-center">
                    <div class="small text-muted mb-2 fw-semibold text-uppercase tracking-wider">Comprehensive Policy Suite</div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('terms') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'terms-condition' ? 'border-primary text-primary fw-bold' : '' }}">Terms & Conditions</a>
                        <a href="{{ route('privacy') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'privacy-policy' ? 'border-primary text-primary fw-bold' : '' }}">Privacy Policy</a>
                        <a href="{{ route('cookies') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'cookie-policy' ? 'border-primary text-primary fw-bold' : '' }}">Cookie Policy</a>
                        <a href="{{ route('cancellation') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'cancellation-refund-policy' ? 'border-primary text-primary fw-bold' : '' }}">Cancellation & Refund</a>
                        <a href="{{ route('payment.security') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'payment-security-policy' ? 'border-primary text-primary fw-bold' : '' }}">Payment Security</a>
                        <a href="{{ route('safety.waiver') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'safety-liability-waiver' ? 'border-primary text-primary fw-bold' : '' }}">Safety & Waiver</a>
                        <a href="{{ route('ai.editorial') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'ai-editorial-policy' ? 'border-primary text-primary fw-bold' : '' }}">AI & Editorial Policy</a>
                        <a href="{{ route('responsible.tourism') }}" class="badge bg-light text-secondary border rounded-pill px-3 py-2 text-decoration-none {{ $page->slug === 'responsible-tourism-policy' ? 'border-primary text-primary fw-bold' : '' }}">Responsible Tourism</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Interactive Language Switch JS -->
<script>
function switchLegalLanguage(lang) {
    const enBox = document.getElementById('contentEn');
    const arBox = document.getElementById('contentAr');
    const titleEl = document.getElementById('legalPageHeaderTitle');
    const subEl = document.getElementById('legalPageHeaderSubtitle');
    const helpTitle = document.getElementById('legalHelpTitle');
    const helpDesc = document.getElementById('legalHelpDesc');
    const tocLinks = document.querySelectorAll('.legal-toc-link');

    if (lang === 'ar') {
        enBox.classList.add('d-none');
        arBox.classList.remove('d-none');
        
        titleEl.textContent = "{{ $page->title_ar ?: $page->title }}";
        subEl.textContent = "{{ $page->subtitle_ar ?: ($page->subtitle ?: 'شركة ديونز ديسكفري للسياحة ذ.م.م &bull; دبي، الإمارات العربية المتحدة') }}";
        
        helpTitle.textContent = "هل لديك أي استفسار حول سياساتنا أو التراخيص الرسمية؟";
        helpDesc.textContent = "فريق الامتثال القانوني وخدمة العملاء في دبي متاح على مدار الساعة للرد على استفساراتكم أو تنسيق الحجوزات المؤسسية.";

        tocLinks.forEach(link => {
            const arText = link.getAttribute('data-ar');
            if (arText) link.textContent = arText;
            const targetId = link.getAttribute('href').replace('#sec-', '#sec-ar-');
            link.setAttribute('href', targetId);
        });
    } else {
        arBox.classList.add('d-none');
        enBox.classList.remove('d-none');
        
        titleEl.textContent = "{{ $page->title }}";
        subEl.textContent = "{{ $page->subtitle ?: 'Dunes Discovery Tourism L.L.C &bull; Dubai, United Arab Emirates' }}";

        helpTitle.textContent = "Questions About Our Policies or Compliance?";
        helpDesc.textContent = "Our legal compliance desk and customer concierge team in Dubai are available 24/7 to assist with any policy questions, corporate travel agreements, or booking amendments.";

        tocLinks.forEach(link => {
            const enText = link.getAttribute('data-en');
            if (enText) link.textContent = enText;
            const targetId = link.getAttribute('href').replace('#sec-ar-', '#sec-');
            link.setAttribute('href', targetId);
        });
    }
}
</script>
@endsection
