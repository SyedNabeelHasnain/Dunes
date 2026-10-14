@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type":"ListItem","position":1,"name":{!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
    {"@type":"ListItem","position":2,"name":{!! json_encode('Knowledge Portal', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('wiki.index'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
    {"@type":"ListItem","position":3,"name":{!! json_encode($pillar->name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}}
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
  "url": {!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
}
</script>

<!-- Pillar Hero -->
<section class="hero-subpage bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem); padding-bottom: 3.5rem;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ localized_route('wiki.index') }}" class="hover:text-white transition-colors">Knowledge Base</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold">{{ $pillar->name }}</li>
            </ol>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/20 border border-primary/40 text-primary-light text-xs font-bold uppercase tracking-wider mb-3">
                <i class="bi {{ $pillar->icon ?: 'bi-compass' }}"></i> Knowledge Pillar Hub
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white mb-4">
                {{ $pillar->name }}
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                {{ $pillar->headline ?: $pillar->description }}
            </p>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><i class="bi bi-file-earmark-text text-amber-400"></i> {{ $articles->count() }} Verified Guides</span>
                <span>•</span>
                <span class="flex items-center gap-1.5"><i class="bi bi-shield-check text-emerald-400"></i> DET Licensed Standards</span>
            </div>
        </div>
    </div>
</section>

<!-- Pillar Cluster Articles -->
<section class="py-12 sm:py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Articles List (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Guides in this Domain</div>

                @forelse($articles as $article)
                <article class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs hover:shadow-lg hover:border-primary/40 transition-all flex flex-col sm:flex-row gap-5 group">
                    <a href="{{ localized_route('wiki.article', ['pillarSlug' => $pillar->slug, 'articleSlug' => $article->slug]) }}" class="w-full sm:w-48 h-36 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                        <img src="{{ resolve_tour_image($article->hero_image) }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </a>
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-1.5">
                                <span><i class="bi bi-clock"></i> {{ $article->reading_time_minutes }} min read</span>
                                <span>•</span>
                                <span class="text-emerald-600 font-semibold flex items-center gap-1"><i class="bi bi-shield-check"></i> Verified by {{ $article->reviewed_by_name }}</span>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-primary transition-colors line-clamp-2 mb-2">
                                <a href="{{ localized_route('wiki.article', ['pillarSlug' => $pillar->slug, 'articleSlug' => $article->slug]) }}">
                                    {{ $article->title }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-3">
                                {{ $article->excerpt }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-bold text-primary">
                            <span>Read Full Guide</span>
                            <i class="bi bi-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </article>
                @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                    No articles published in this pillar yet. Please check back soon.
                </div>
                @endforelse
            </div>

            <!-- Sidebar: Other Pillars & Fast Links -->
            <div class="space-y-6">
                <!-- Other Pillars Box -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="bi bi-compass text-primary"></i> Other Knowledge Pillars
                    </h3>
                    <div class="space-y-2">
                        @foreach($otherPillars as $op)
                        <a href="{{ localized_route('wiki.pillar', $op->slug) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 hover:text-primary transition-colors">
                            <span class="flex items-center gap-2">
                                <i class="bi {{ $op->icon ?: 'bi-compass' }} text-slate-400"></i>
                                {{ $op->name }}
                            </span>
                            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-bold">{{ $op->active_articles_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Glossary Shortcut Banner -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl border border-slate-800 p-5 text-white">
                    <div class="text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">Need a quick definition?</div>
                    <h4 class="text-base font-bold mb-2">Safari Terminology Dictionary</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">
                        Instant definitions of terms like Lahbab, Dune Bashing, Gahwa, Tanoura, and Camel Trekking.
                    </p>
                    <a href="{{ localized_route('wiki.glossary') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition-all shadow-sm">
                        <span>Open A-Z Glossary</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
