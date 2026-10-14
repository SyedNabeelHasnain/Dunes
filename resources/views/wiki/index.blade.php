@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type":"ListItem","position":1,"name":{!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
    {"@type":"ListItem","position":2,"name":{!! json_encode('Knowledge Portal', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('wiki.index'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}}
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": {!! json_encode($pageTitle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
  "description": {!! json_encode($pageDesc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
  "inLanguage": {!! json_encode(app()->getLocale(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
  "url": {!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
  "publisher": {
    "@type": "TravelAgency",
    "name": "Dunes Discovery Tourism LLC",
    "url": "https://dunesdiscoverytourism.com",
    "identifier": {
      "@type": "PropertyValue",
      "name": "DET Tourism License",
      "value": "1430583"
    }
  }
}
</script>

<!-- Knowledge Portal Hero -->
<section class="hero-subpage bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem); padding-bottom: 3.5rem;">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold">Dubai Desert Wiki</li>
            </ol>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="bi bi-book-half"></i> Verified Destination Knowledge Base
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white mb-4">
                Dubai Desert & UAE Tourism <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-primary">Encyclopedia</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                Authoritative geological breakdowns, 4x4 physics, safari health manuals, and Bedouin cultural traditions — documented and verified by licensed safari marshals.
            </p>

            <!-- Instant Live Search Bar -->
            <div class="relative max-w-xl" x-data="{ query: '', results: null, loading: false }" @click.outside="results = null">
                <div class="relative">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input 
                        type="text" 
                        x-model="query"
                        @input.debounce.300ms="if(query.length >= 2) { loading = true; fetch('{{ localized_route('wiki.search') }}?q=' + encodeURIComponent(query)).then(r => r.json()).then(d => { results = d; loading = false; }); } else { results = null; }"
                        placeholder="Search topics (e.g. Lahbab sand, tire pressure, pregnancy, gahwa, buggy)..." 
                        class="w-full pl-11 pr-12 py-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                    <div x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary" style="display: none;">
                        <span class="inline-block animate-spin"><i class="bi bi-arrow-repeat"></i></span>
                    </div>
                </div>

                <!-- Instant Search Dropdown -->
                <div x-show="results && (results.articles.length > 0 || results.terms.length > 0)" 
                     class="absolute top-full left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-3 z-50 max-h-96 overflow-y-auto text-left" 
                     style="display: none;"
                >
                    <template x-if="results.articles.length > 0">
                        <div class="mb-3">
                            <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider px-2 mb-1.5">Knowledge Guides</div>
                            <template x-for="a in results.articles" :key="a.url">
                                <a :href="a.url" class="block p-2 rounded-xl hover:bg-white/10 transition-colors">
                                    <div class="text-xs font-bold text-white" x-text="a.title"></div>
                                    <div class="text-[11px] text-slate-400 line-clamp-1" x-text="a.excerpt"></div>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="results.terms.length > 0">
                        <div>
                            <div class="text-[10px] font-bold text-sky-400 uppercase tracking-wider px-2 mb-1.5">Glossary Definitions</div>
                            <template x-for="t in results.terms" :key="t.term">
                                <a :href="t.url" class="block p-2 rounded-xl hover:bg-white/10 transition-colors">
                                    <div class="text-xs font-bold text-amber-300" x-text="t.term"></div>
                                    <div class="text-[11px] text-slate-400" x-text="t.definition"></div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Knowledge Pillars Grid -->
<section class="py-12 sm:py-16 bg-slate-50 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-primary">Explore by Domain</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Core Knowledge Pillars</h2>
            </div>
            <a href="{{ localized_route('wiki.glossary') }}" class="inline-flex items-center gap-2 text-xs font-bold text-primary hover:text-primary-dark transition-colors">
                <span>Browse A-Z Terminology Glossary</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pillars as $pillar)
            <a href="{{ localized_route('wiki.pillar', $pillar->slug) }}" class="group bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-xl hover:border-primary/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xl mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                        <i class="bi {{ $pillar->icon ?: 'bi-compass' }}"></i>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                        <span class="font-mono text-[11px] uppercase tracking-wider font-semibold">Pillar 0{{ $loop->iteration }}</span>
                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold text-[10px]">{{ $pillar->active_articles_count }} {{ Str::plural('Guide', $pillar->active_articles_count) }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">
                        {{ $pillar->name }}
                    </h3>
                    <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mb-4">
                        {{ $pillar->headline ?: $pillar->description }}
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-primary">
                    <span>Explore Guides</span>
                    <i class="bi bi-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Cornerstone Guides -->
<section class="py-12 sm:py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">Essential Reading</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-3">Featured Cornerstone Guides</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">In-depth technical and logistical briefings answering the most critical traveler questions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach($featuredArticles as $article)
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-xl transition-all flex flex-col group">
                <a href="{{ localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug]) }}" class="block aspect-video overflow-hidden relative bg-slate-100">
                    <img src="{{ resolve_tour_image($article->hero_image) }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 rounded-md bg-slate-900/80 backdrop-blur-md text-amber-300 text-[10px] font-bold uppercase tracking-wider">
                            {{ $article->pillar->name }}
                        </span>
                    </div>
                </a>
                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-2">
                            <span><i class="bi bi-clock"></i> {{ $article->reading_time_minutes }} min read</span>
                            <span>•</span>
                            <span class="text-emerald-600 font-semibold flex items-center gap-1"><i class="bi bi-shield-check"></i> Marshal Verified</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-primary transition-colors line-clamp-2 mb-2">
                            <a href="{{ localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug]) }}">
                                {{ $article->title }}
                            </a>
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mb-4">
                            {{ $article->excerpt }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">By {{ $article->reviewed_by_name }}</span>
                        <a href="{{ localized_route('wiki.article', ['pillarSlug' => $article->pillar->slug, 'articleSlug' => $article->slug]) }}" class="font-bold text-primary flex items-center gap-1 group-hover:underline">
                            Read Guide <i class="bi bi-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Fast Glossary Terminology Strip -->
<section class="py-12 bg-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Instant Definitions</span>
                <h3 class="text-xl sm:text-2xl font-bold text-white mt-1">Desert Safari Terminology Dictionary</h3>
            </div>
            <a href="{{ localized_route('wiki.glossary') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 transition-all">
                <span>View Full A-Z Glossary</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($glossaryHighlights as $term)
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-amber-400/40 transition-colors">
                <div class="font-bold text-sm text-amber-300 mb-1">{{ $term->term }}</div>
                <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed">{{ $term->definition }}</p>
                @if($term->article)
                <a href="{{ localized_route('wiki.article', ['pillarSlug' => $term->article->pillar->slug, 'articleSlug' => $term->article->slug]) }}" class="inline-block mt-2 text-[11px] text-primary hover:underline">
                    Read guide &rarr;
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- E-E-A-T Verified Authority Banner -->
<section class="py-12 bg-slate-50 border-t border-slate-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold mb-4">
            <i class="bi bi-award-fill text-emerald-600"></i> DET Licensed Tour Operator #1430583
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-slate-900 mb-3">
            Written by Real Desert Marshals, Not Content Scrapers
        </h3>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-3xl mx-auto mb-6">
            All factual claims, medical eligibility advisories, vehicle specifications, and cultural protocols on this knowledge portal are authored and vetted by licensed safari drivers and field operations leaders with over 15 years in the Lahbab desert.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-bold text-slate-700">
            <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> Dubai DET Tourism Certified</span>
            <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> 4.9⭐ Google & TripAdvisor Rated</span>
            <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> 90-Day Freshness Review Cycle</span>
        </div>
    </div>
</section>
@endsection
