@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "{{ request()->url() }}#webpage",
      "url": "{{ request()->url() }}",
      "name": "{{ $pageTitle ?? $page->title }}",
      "description": "{{ $pageDesc ?? $page->description }}",
      "inLanguage": ["en", "ar"],
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ route('home') }}#website",
        "url": "{{ route('home') }}",
        "name": "Dunes Discovery Tourism LLC Dubai"
      },
      "publisher": {
        "@type": "TravelAgency",
        "@id": "{{ route('home') }}#organization",
        "name": "Dunes Discovery Tourism L.L.C",
        "url": "{{ route('home') }}",
        "telephone": "+971502456056",
        "email": "info@dunesdiscoverytourism.com",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Dubai",
          "addressCountry": "AE"
        }
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ request()->url() }}#breadcrumb",
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
          "name": "Legal & Policies",
          "item": "{{ route('terms') }}"
        },
        {
          "@type": "ListItem",
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
    color: #475569;
    border-left: 2px solid transparent;
    text-decoration: none;
    transition: all 0.2s ease;
}
.legal-toc-link:hover,
.legal-toc-link.active {
    color: #F69044;
    border-left-color: #F69044;
    background-color: rgba(246, 144, 68, 0.08);
    font-weight: 600;
}
.legal-rtl {
    direction: rtl;
    text-align: right;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.legal-rtl .border-start-override {
    border-left-width: 0 !important;
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
    border-right-color: #F69044;
}
@media print {
    #header, .footer, .btn-circle-whatsapp, .whatsapp-floating-btn, .rc-floating-bar,
    .legal-lang-switch, .legal-toc-col, .legal-action-bar, .legal-contact-card, nav[aria-label="breadcrumb"], .legal-policy-suite {
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
    .legal-main-card {
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
<section class="page-header relative overflow-hidden bg-slate-950 text-white" style="margin-top: calc(-1 * var(--header-h, 80px)); padding-top: calc(var(--header-h, 80px) + 2rem) !important; padding-bottom: 2.5rem;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="flex items-center gap-2 text-xs text-white/75 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/40">/</li>
                <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Legal & Trust</a></li>
                <li class="text-white/40">/</li>
                <li class="text-white font-medium" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
        
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-amber-500/15 text-amber-400 border border-amber-500/30 rounded-full px-3.5 py-1 text-xs font-semibold mb-2.5">
                    <i class="bi bi-shield-check"></i>
                    <span>Official UAE Tourism Document &bull; License 1430583</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight mb-1.5" id="legalPageHeaderTitle">{{ $page->title }}</h1>
                <p class="text-white/75 text-sm sm:text-base" id="legalPageHeaderSubtitle">
                    {{ $page->subtitle ?: 'Dunes Discovery Tourism L.L.C • Dubai, United Arab Emirates' }}
                </p>
            </div>

            <!-- Language Switcher Pills -->
            <div class="legal-lang-switch bg-slate-900/80 p-1 rounded-full border border-white/20 inline-flex shrink-0 self-start md:self-center items-center shadow-lg">
                <input type="radio" class="sr-only peer/en" name="legalLang" id="langEn" autocomplete="off" checked onchange="switchLegalLanguage('en')">
                <label class="cursor-pointer text-white/80 hover:text-white rounded-full px-3.5 py-1.5 text-xs font-medium flex items-center gap-1.5 transition-all peer-checked/en:bg-primary peer-checked/en:text-white peer-checked/en:font-bold peer-checked/en:shadow-md" for="langEn">
                    <span class="bg-white/20 rounded-full px-1.5 py-0.5 text-[10px] font-bold">EN</span>
                    <span>English</span>
                </label>

                <input type="radio" class="sr-only peer/ar" name="legalLang" id="langAr" autocomplete="off" onchange="switchLegalLanguage('ar')">
                <label class="cursor-pointer text-white/80 hover:text-white rounded-full px-3.5 py-1.5 text-xs font-medium flex items-center gap-1.5 transition-all peer-checked/ar:bg-primary peer-checked/ar:text-white peer-checked/ar:font-bold peer-checked/ar:shadow-md" for="langAr">
                    <span class="bg-amber-400 text-slate-950 rounded-full px-1.5 py-0.5 text-[10px] font-bold">AR</span>
                    <span>العربية</span>
                </label>
            </div>
        </div>
    </div>
</section>

<!-- Main Legal Body -->
<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Utility Action Bar -->
        <div class="legal-action-bar bg-white rounded-2xl shadow-xs border border-slate-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 text-slate-500 text-xs sm:text-sm flex-wrap">
                <span class="flex items-center gap-1.5"><i class="bi bi-calendar-check text-primary"></i> Last Verified: September 2026</span>
                <span class="hidden sm:inline text-slate-300">|</span>
                <span class="hidden sm:flex items-center gap-1.5"><i class="bi bi-building text-primary"></i> Dubai DET Registered</span>
                <span class="hidden md:inline text-slate-300">|</span>
                <span class="hidden md:flex items-center gap-1.5"><i class="bi bi-translate text-primary"></i> Bilingual (EN / AR)</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="text-xs sm:text-sm border border-slate-300 hover:bg-slate-100 text-slate-700 font-medium rounded-full px-3.5 py-1.5 inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="bi bi-printer"></i>
                    <span>Print / Save PDF</span>
                </button>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $waPhone ?? '971502456056') }}?text={{ urlencode('Hello Dunes Discovery Tourism, I have an inquiry regarding: ' . $page->title) }}" target="_blank" rel="noopener" class="text-xs sm:text-sm border border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white font-medium rounded-full px-3.5 py-1.5 inline-flex items-center gap-1.5 transition-colors">
                    <i class="bi bi-whatsapp"></i>
                    <span>Inquire via WhatsApp</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Table of Contents / Quick Jump (Desktop) -->
            <div class="hidden lg:block lg:col-span-3 legal-toc-col">
                <div class="legal-toc-list bg-white rounded-2xl shadow-xs border border-slate-200 p-4">
                    <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                        <i class="bi bi-list-nested text-primary"></i>
                        <span class="font-bold text-slate-900 text-xs uppercase tracking-wider">Document Sections</span>
                    </div>
                    <nav class="flex flex-col gap-1" id="legalTocNav">
                        @foreach($page->sections as $sec)
                        <a href="#sec-{{ $sec->id }}" class="legal-toc-link rounded-lg" data-en="{{ $sec->heading }}" data-ar="{{ $sec->heading_ar ?: $sec->heading }}">
                            {{ $sec->heading }}
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Content Column -->
            <div class="lg:col-span-9">
                <div class="legal-main-card bg-white rounded-2xl shadow-xs border border-slate-200 border-t-4 border-t-primary overflow-hidden">
                    <div class="p-6 sm:p-8 lg:p-10">

                        <!-- English Container -->
                        <div id="contentEn" class="legal-content-container">
                            @if($page->description)
                            <div class="p-5 bg-slate-50 rounded-2xl border-l-4 border-primary mb-8 text-slate-700 text-sm sm:text-base leading-relaxed">
                                {!! nl2br(e($page->description)) !!}
                            </div>
                            @endif

                            @if($page->sections->count() > 0)
                                <div class="legal-sections-wrapper space-y-8">
                                    @foreach($page->sections as $section)
                                        <div class="legal-section-block pt-2" id="sec-{{ $section->id }}">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 border-l-4 border-primary pl-3">
                                                    {{ $section->heading }}
                                                </h2>
                                                <a href="#sec-{{ $section->id }}" class="text-slate-400 hover:text-primary transition-colors text-xs pt-1" title="Direct Link to Clause">
                                                    <i class="bi bi-link-45deg text-lg"></i>
                                                </a>
                                            </div>

                                            @if($section->subheading)
                                                <h3 class="text-sm font-bold text-slate-600 mb-3 ml-4">{{ $section->subheading }}</h3>
                                            @endif

                                            @if($section->items->count() > 0)
                                                <ul class="space-y-3 ml-2 list-none p-0">
                                                    @foreach($section->items as $item)
                                                        <li class="flex items-start gap-3">
                                                            <i class="bi bi-check-circle-fill text-primary mt-1 shrink-0 text-sm"></i>
                                                            <span class="text-slate-600 text-sm sm:text-base leading-relaxed">{{ $item->content }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <hr class="my-6 border-slate-100">
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12 text-slate-400">
                                    <i class="bi bi-hourglass-split text-4xl mb-3 block text-primary"></i>
                                    <p class="text-sm">Legal document updates in progress.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Arabic Container (RTL) -->
                        <div id="contentAr" class="legal-content-container legal-rtl hidden">
                            @if($page->description_ar || $page->description)
                            <div class="p-5 bg-slate-50 rounded-2xl border-r-4 border-primary mb-8 text-slate-700 text-sm sm:text-base leading-loose">
                                {!! nl2br(e($page->description_ar ?: $page->description)) !!}
                            </div>
                            @endif

                            @if($page->sections->count() > 0)
                                <div class="legal-sections-wrapper space-y-8">
                                    @foreach($page->sections as $section)
                                        <div class="legal-section-block pt-2" id="sec-ar-{{ $section->id }}">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 border-r-4 border-primary pr-3">
                                                    {{ $section->heading_ar ?: $section->heading }}
                                                </h2>
                                                <a href="#sec-ar-{{ $section->id }}" class="text-slate-400 hover:text-primary transition-colors text-xs pt-1" title="رابط مباشر للبند">
                                                    <i class="bi bi-link-45deg text-lg"></i>
                                                </a>
                                            </div>

                                            @if($section->subheading_ar || $section->subheading)
                                                <h3 class="text-sm font-bold text-slate-600 mb-3 mr-4">{{ $section->subheading_ar ?: $section->subheading }}</h3>
                                            @endif

                                            @if($section->items->count() > 0)
                                                <ul class="space-y-3 mr-2 list-none p-0">
                                                    @foreach($section->items as $item)
                                                        <li class="flex items-start gap-3">
                                                            <i class="bi bi-check-circle-fill text-primary mt-1 shrink-0 text-sm"></i>
                                                            <span class="text-slate-600 text-sm sm:text-base leading-loose">{{ $item->content_ar ?: $item->content }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <hr class="my-6 border-slate-100">
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12 text-slate-400">
                                    <i class="bi bi-hourglass-split text-4xl mb-3 block text-primary"></i>
                                    <p class="text-sm">جاري تحديث المحتوى القانوني.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Assistance / Contact Banner -->
                <div class="legal-contact-card mt-8 p-6 sm:p-8 text-center bg-white rounded-2xl shadow-xs border border-slate-200">
                    <div class="inline-flex items-center justify-center bg-amber-500/15 text-primary rounded-full shadow-xs mb-3 w-16 h-16">
                        <i class="bi bi-shield-lock-fill text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-2" id="legalHelpTitle">Questions About Our Policies or Compliance?</h3>
                    <p class="text-slate-500 text-sm sm:text-base mb-6 max-w-xl mx-auto leading-relaxed" id="legalHelpDesc">
                        Our legal compliance desk and customer concierge team in Dubai are available 24/7 to assist with any policy questions, corporate travel agreements, or booking amendments.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $waPhone ?? '971502456056') }}?text={{ urlencode('Hi Dunes Discovery Tourism, I have a question regarding: ' . $page->title) }}" class="btn-whatsapp-animated text-sm sm:text-base font-bold rounded-full px-6 py-3 text-white inline-flex items-center justify-center gap-2 shadow-sm" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            <span>Chat with Legal Desk</span>
                        </a>
                        <a href="{{ route('contact') }}" class="btn-desert-animated text-sm sm:text-base font-bold rounded-full px-6 py-3 text-white inline-flex items-center justify-center gap-2 shadow-sm">
                            <i class="bi bi-envelope"></i>
                            <span>Submit Formal Inquiry</span>
                        </a>
                    </div>
                </div>

                <!-- Trust Badges & Policy Sitemap Links -->
                <div class="legal-policy-suite mt-6 p-4 bg-white rounded-2xl border border-slate-200 text-center">
                    <div class="text-xs text-slate-400 mb-3 font-bold uppercase tracking-wider">Comprehensive Policy Suite</div>
                    <div class="flex flex-wrap justify-center gap-2">
                        <a href="{{ route('terms') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'terms-condition' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Terms & Conditions</a>
                        <a href="{{ route('privacy') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'privacy-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Privacy Policy</a>
                        <a href="{{ route('cookies') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'cookie-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Cookie Policy</a>
                        <a href="{{ route('cancellation') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'cancellation-refund-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Cancellation & Refund</a>
                        <a href="{{ route('payment.security') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'payment-security-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Payment Security</a>
                        <a href="{{ route('safety.waiver') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'safety-liability-waiver' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Safety & Waiver</a>
                        <a href="{{ route('ai.editorial') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'ai-editorial-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">AI & Editorial Policy</a>
                        <a href="{{ route('responsible.tourism') }}" class="text-xs rounded-full px-3.5 py-1.5 border transition-colors {{ $page->slug === 'responsible-tourism-policy' ? 'bg-primary text-white border-primary font-bold shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-primary/50' }}">Responsible Tourism</a>
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
        if (enBox) enBox.classList.add('hidden');
        if (arBox) arBox.classList.remove('hidden');
        
        if (titleEl) titleEl.textContent = "{{ $page->title_ar ?: $page->title }}";
        if (subEl) subEl.textContent = "{{ $page->subtitle_ar ?: ($page->subtitle ?: 'شركة ديونز ديسكفري للسياحة ذ.م.م • دبي، الإمارات العربية المتحدة') }}";
        
        if (helpTitle) helpTitle.textContent = "هل لديك أي استفسار حول سياساتنا أو التراخيص الرسمية؟";
        if (helpDesc) helpDesc.textContent = "فريق الامتثال القانوني وخدمة العملاء في دبي متاح على مدار الساعة للرد على استفساراتكم أو تنسيق الحجوزات المؤسسية.";

        tocLinks.forEach(link => {
            const arText = link.getAttribute('data-ar');
            if (arText) link.textContent = arText;
            const targetId = link.getAttribute('href').replace('#sec-', '#sec-ar-');
            link.setAttribute('href', targetId);
        });
    } else {
        if (arBox) arBox.classList.add('hidden');
        if (enBox) enBox.classList.remove('hidden');
        
        if (titleEl) titleEl.textContent = "{{ $page->title }}";
        if (subEl) subEl.textContent = "{{ $page->subtitle ?: 'Dunes Discovery Tourism L.L.C • Dubai, United Arab Emirates' }}";

        if (helpTitle) helpTitle.textContent = "Questions About Our Policies or Compliance?";
        if (helpDesc) helpDesc.textContent = "Our legal compliance desk and customer concierge team in Dubai are available 24/7 to assist with any policy questions, corporate travel agreements, or booking amendments.";

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
