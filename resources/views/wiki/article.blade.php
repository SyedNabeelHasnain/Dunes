@extends('layouts.app')

@section('content')
@php
    $toc = $article->parsed_toc;
    $contentHtml = Str::markdown((string) $article->content);
@endphp

<!-- Schema.org JSON-LD for TechArticle / Informational Guide -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {"@type":"ListItem","position":1,"name":{!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
        {"@type":"ListItem","position":2,"name":{!! json_encode('Knowledge Portal', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('wiki.index'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
        {"@type":"ListItem","position":3,"name":{!! json_encode($pillar->name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('wiki.pillar', $pillar->slug), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
        {"@type":"ListItem","position":4,"name":{!! json_encode($article->title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}}
      ]
    },
    {
      "@type": "TechArticle",
      "@id": {!! json_encode($canonical . '#article', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "headline": {!! json_encode($article->title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "description": {!! json_encode($article->excerpt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "image": [{!! json_encode(resolve_tour_image($article->hero_image ?: $pillar->hero_image), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}],
      "inLanguage": {!! json_encode(app()->getLocale(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "datePublished": {!! json_encode($article->created_at->toIso8601String(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "dateModified": {!! json_encode($article->updated_at->toIso8601String(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
      "author": {
        "@type": "Organization",
        "name": "Dunes Discovery Tourism LLC",
        "url": "https://dunesdiscoverytourism.com",
        "identifier": {
          "@type": "PropertyValue",
          "name": "DET Tourism License",
          "value": "1430583"
        }
      },
      @if(!empty($article->reviewed_by_name))
      "reviewedBy": {
        "@type": "Person",
        "name": {!! json_encode($article->reviewed_by_name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
        "jobTitle": {!! json_encode($article->reviewed_by_title ?: 'Operations Lead (DET #1430583)', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
        "worksFor": {
          "@type": "TravelAgency",
          "name": "Dunes Discovery Tourism LLC"
        }
      },
      @endif
      "publisher": {
        "@type": "TravelAgency",
        "name": "Dunes Discovery Tourism LLC",
        "url": "https://dunesdiscoverytourism.com",
        "logo": {
          "@type": "ImageObject",
          "url": "https://dunesdiscoverytourism.com/images/logo.png"
        }
      }
    }
  ]
}
</script>

<!-- Article Hero -->
<section class="hero-subpage bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem); padding-bottom: 3.5rem;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ localized_route('wiki.index') }}" class="hover:text-white transition-colors">Knowledge Base</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ localized_route('wiki.pillar', $pillar->slug) }}" class="hover:text-white transition-colors">{{ $pillar->name }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold truncate max-w-xs">{{ $article->title }}</li>
            </ol>
        </nav>

        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold uppercase tracking-wider mb-4">
                <i class="bi {{ $pillar->icon ?: 'bi-compass' }}"></i> {{ $pillar->name }}
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-5xl font-black tracking-tight text-white mb-4 leading-tight">
                {{ $article->title }}
            </h1>
            @if($article->subtitle)
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                {{ $article->subtitle }}
            </p>
            @endif

            <!-- Metadata & Verification Bar -->
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 pt-4 border-t border-white/10">
                <span class="flex items-center gap-1.5"><i class="bi bi-clock text-amber-400"></i> {{ $article->reading_time_minutes }} min read</span>
                <span>•</span>
                <span class="flex items-center gap-1.5"><i class="bi bi-calendar3 text-slate-400"></i> Published: {{ $article->created_at->format('M d, Y') }}</span>
                <span>•</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 font-semibold">
                    <i class="bi bi-shield-check text-emerald-400"></i> Vetted by {{ $article->reviewed_by_name }} ({{ $article->last_reviewed_at ? $article->last_reviewed_at->format('M Y') : 'Recent' }})
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Article Body & Sidebar -->
<section class="py-12 sm:py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <!-- Left Sidebar: Table of Contents & Author Credentials (4 cols on lg) -->
            <aside class="lg:col-span-4 order-2 lg:order-1">
                <div class="space-y-6 sticky top-24">
                    <!-- Table of Contents Card -->
                    @if(!empty($toc) && count($toc) > 0)
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                            <i class="bi bi-list-nested text-primary"></i> On This Page
                        </div>
                        <nav class="space-y-1.5 text-xs">
                            @foreach($toc as $item)
                            <a href="#{{ $item['anchor'] }}" class="block text-slate-600 hover:text-primary transition-colors py-1 pl-{{ ($item['level'] - 2) * 3 }} leading-snug">
                                {{ $item['title'] }}
                            </a>
                            @endforeach
                        </nav>
                    </div>
                    @endif

                    <!-- Marshal Verification Card -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">{{ $article->reviewed_by_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $article->reviewed_by_title }}</div>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                            Licensed under Dubai Department of Economy and Tourism (DET License #1430583) with over 15 years leading desert expeditions across Lahbab.
                        </p>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-emerald-600 font-semibold">
                            <span><i class="bi bi-patch-check-fill"></i> DET Certified Fact-Check</span>
                            <span>{{ $article->last_reviewed_at ? $article->last_reviewed_at->format('M Y') : 'Active' }}</span>
                        </div>
                    </div>

                    <!-- Return to Pillar -->
                    <a href="{{ localized_route('wiki.pillar', $pillar->slug) }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                        <i class="bi bi-arrow-left"></i>
                        <span>More in {{ $pillar->name }}</span>
                    </a>
                </div>
            </aside>

            <!-- Main Guide Content (8 cols on lg) -->
            <main class="lg:col-span-8 order-1 lg:order-2">
                <article class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-10 shadow-xs">
                    <!-- Featured Image -->
                    @if($article->hero_image)
                    <div class="rounded-xl overflow-hidden mb-8 border border-slate-200 aspect-video bg-slate-100">
                        <img src="{{ resolve_tour_image($article->hero_image) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
                    </div>
                    @endif

                    <!-- Prose Markdown Content -->
                    <div class="prose prose-slate max-w-none text-slate-700 text-sm sm:text-base leading-relaxed
                                prose-headings:font-bold prose-headings:text-slate-900 prose-headings:tracking-tight
                                prose-h2:text-xl sm:prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:pt-4 prose-h2:border-t prose-h2:border-slate-100
                                prose-h3:text-base sm:prose-h3:text-lg prose-h3:mt-6 prose-h3:mb-3
                                prose-p:my-3.5 prose-p:leading-relaxed
                                prose-ul:my-3 prose-ul:list-disc prose-ul:pl-5
                                prose-li:my-1
                                prose-table:my-6 prose-table:w-full prose-table:text-xs sm:prose-table:text-sm prose-th:bg-slate-100 prose-th:p-2.5 prose-td:p-2.5 prose-td:border-b
                                prose-strong:text-slate-900 prose-strong:font-bold">
                        {!! $contentHtml !!}
                    </div>

                    <!-- Embedded Contextual Tour Booking Card -->
                    @if($contextualTour)
                        @include('partials.wiki-tour-card', ['tour' => $contextualTour])
                    @endif

                    <!-- Article Share & Action Strip -->
                    <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                        <div>
                            <span>Have a technical or medical question?</span>
                            <a href="{{ localized_route('contact') }}" class="font-bold text-primary hover:underline ml-1">Ask our Safari Marshals</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-700">Share this guide:</span>
                            <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . $canonical) }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors" title="Share via WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode($canonical) }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center hover:bg-sky-600 hover:text-white transition-colors" title="Share on Twitter / X">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Related Cluster Guides in this Pillar -->
                @if($relatedArticles->count() > 0)
                <div class="mt-10">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Related Guides in {{ $pillar->name }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($relatedArticles as $ra)
                        <a href="{{ localized_route('wiki.article', ['pillarSlug' => $pillar->slug, 'articleSlug' => $ra->slug]) }}" class="bg-white p-4 rounded-xl border border-slate-200/80 hover:border-primary/40 hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] text-slate-400 mb-1"><i class="bi bi-clock"></i> {{ $ra->reading_time_minutes }} min read</div>
                                <div class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-2 mb-2">{{ $ra->title }}</div>
                            </div>
                            <div class="text-[11px] font-bold text-primary flex items-center gap-1">
                                Read Guide <i class="bi bi-chevron-right text-[9px]"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </main>
        </div>
    </div>
</section>
@endsection
