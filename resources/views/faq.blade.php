@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "@id": "{{ $canonical }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "{{ __('ui.nav.home') ?? 'Home' }}",
          "item": "{{ localized_route('home') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{ __('ui.nav.faq') ?? 'FAQ' }}",
          "item": "{{ $canonical }}"
        }
      ]
    }
    @if(isset($faqs) && $faqs->count() > 0)
    ,
    {
      "@type": "FAQPage",
      "@id": "{{ $canonical }}#faqpage",
      "inLanguage": "{{ $currentLocale ?? app()->getLocale() ?? 'en' }}",
      "mainEntity": [
        @foreach($faqs as $index => $f)
        {
          "@type": "Question",
          "name": {!! json_encode($f->question) !!},
          "acceptedAnswer": {
            "@type": "Answer",
            "text": {!! json_encode($f->answer) !!}
          }
        }{{ $index < $faqs->count() - 1 ? ',' : '' }}
        @endforeach
      ]
    }
    @endif
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
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ __('ui.faq.title') }}</li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <span class="glass rounded-full px-3.5 py-1 text-xs inline-flex items-center gap-1.5 mb-2">
                    <i class="bi bi-patch-question-fill text-primary"></i>{{ __('ui.faq.help_center') }}
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-2">{{ __('ui.faq.title') }}</h1>
                <p class="text-sm sm:text-base text-white/80 max-w-2xl leading-relaxed">{{ __('ui.faq.subtitle') }}</p>
            </div>
            <div class="hidden lg:block shrink-0">
                <span class="bg-primary/20 text-primary border border-primary/40 px-4 py-2 rounded-full font-bold text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-clock-history"></i>{{ __('ui.faq.support_247') }}
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
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.dtcm_licensed') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.dtcm_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-arrow-repeat text-emerald-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.free_cancel') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.free_cancel_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.trust_strip.halal_food') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.halal_food_desc') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <i class="bi bi-shield-lock-fill text-cyan-600 text-xl shrink-0"></i>
                <div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ __('ui.why_choose_us.instant_confirm') }}</div>
                    <div class="text-slate-500 text-[11px]">{{ __('ui.trust_strip.secure_checkout_desc') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 sm:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Live Instant Search Bar for FAQs -->
        <div class="mb-10">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="relative">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" id="faqSearchInput" class="w-full rounded-full pl-11 pr-4 py-3 bg-white border border-slate-200 text-sm text-slate-800 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/40 shadow-xs" placeholder="{{ __('ui.faq.search_placeholder') }}" oninput="handleFaqSearch(this.value)">
                </div>
            </div>
        </div>

        <div x-data="{ openFaq: 0 }" class="space-y-3.5">
            @foreach($faqs as $i => $f)
            <div class="faq-item bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden transition-all duration-200" data-question="{{ strtolower($f->question) }}" data-answer="{{ strtolower($f->answer) }}">
                <button type="button" 
                        class="w-full text-left px-5 sm:px-6 py-4.5 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                        @click="openFaq = (openFaq === {{ $i }} ? null : {{ $i }})">
                    <span class="inline-flex items-center gap-3 text-sm sm:text-base">
                        <i class="bi bi-question-circle-fill text-primary shrink-0"></i>
                        <span>{{ $f->question }}</span>
                    </span>
                    <i class="bi bi-chevron-down transition-transform duration-300 text-slate-500 shrink-0"
                       :class="openFaq === {{ $i }} ? 'rotate-180 text-primary' : ''"></i>
                </button>
                <div x-show="openFaq === {{ $i }}" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-slate-600 text-xs sm:text-sm leading-relaxed border-t border-slate-200 pt-3.5">
                    {{ $f->answer }}
                </div>
            </div>
            @endforeach
        </div>

        <div id="noFaqResults" class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl text-slate-500">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">{{ __('ui.faq.no_results_title') }}</h3>
            <p class="text-slate-500 text-xs">{{ __('ui.faq.no_results_desc') }}</p>
        </div>

        <div class="mt-14 p-8 sm:p-12 text-center bg-slate-50 rounded-3xl border border-slate-200">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl text-primary shadow-xs">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('ui.faq.still_have_questions') }}</h2>
            <p class="text-slate-600 text-sm mb-6 max-w-md mx-auto">{{ __('ui.faq.team_ready') }}</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode('Hi! I have a question about your tours.') }}" class="btn-whatsapp-animated rounded-full px-6 py-3 font-bold text-white text-sm inline-flex items-center justify-center gap-2 shadow-sm cursor-pointer" target="_blank" rel="noopener noreferrer" data-action="whatsapp">
                    <i class="bi bi-whatsapp"></i>
                    <span>{{ __('ui.faq.whatsapp_247') }}</span>
                </a>
                <a href="{{ route('contact') }}" class="btn-desert-animated rounded-full px-6 py-3 font-bold text-white text-sm inline-flex items-center justify-center gap-2 shadow-sm">
                    <i class="bi bi-envelope"></i>
                    <span>{{ __('ui.nav.contact') }}</span>
                </a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
function handleFaqSearch(query) {
    const q = (query || '').trim().toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    let visibleCount = 0;

    items.forEach(item => {
        const question = item.dataset.question || '';
        const answer = item.dataset.answer || '';
        if (!q || question.includes(q) || answer.includes(q)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noFaqResults');
    if (noResults) {
        if (visibleCount > 0) {
            noResults.classList.add('hidden');
        } else {
            noResults.classList.remove('hidden');
        }
    }
}
</script>
@endpush
@endsection
