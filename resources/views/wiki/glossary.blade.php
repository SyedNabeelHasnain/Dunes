@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type":"ListItem","position":1,"name":{!! json_encode(__('ui.nav.home') ?? 'Home', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('home'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
    {"@type":"ListItem","position":2,"name":{!! json_encode('Knowledge Portal', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode(localized_route('wiki.index'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}},
    {"@type":"ListItem","position":3,"name":{!! json_encode('Glossary Index', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"item":{!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}}
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "DefinedTermSet",
  "name": "Dubai Desert Safari Terminology Glossary",
  "description": "Comprehensive A-Z dictionary of desert safari, dune mechanics, and cultural terminology.",
  "url": {!! json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
  "inLanguage": {!! json_encode(app()->getLocale(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
}
</script>

<!-- Glossary Hero -->
<section class="hero-subpage bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem); padding-bottom: 3.5rem;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <nav aria-label="{{ __('ui.common.breadcrumb') }}" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li><a href="{{ localized_route('wiki.index') }}" class="hover:text-white transition-colors">Knowledge Base</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold">Glossary</li>
            </ol>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="bi bi-alphabet-uppercase"></i> Terminology Index
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white mb-4">
                Dubai Desert Safari <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-primary">A-Z Glossary</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                Verified definitions of Emirati desert terms, off-roading slang, vehicle specifications, and cultural ceremonial names.
            </p>

            <!-- Alphabet Jump Bar -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                @foreach($groupedTerms->keys() as $letter)
                <a href="#letter-{{ $letter }}" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-primary text-white text-xs font-bold flex items-center justify-center transition-colors">
                    {{ $letter }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Terms Listing by Letter -->
<section class="py-12 sm:py-16 bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        @foreach($groupedTerms as $letter => $terms)
        <div id="letter-{{ $letter }}" class="scroll-mt-24">
            <div class="flex items-center gap-4 mb-6 pb-2 border-b-2 border-primary/20">
                <span class="w-10 h-10 rounded-xl bg-primary text-white font-black text-lg flex items-center justify-center shadow-xs">
                    {{ $letter }}
                </span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $terms->count() }} {{ Str::plural('Term', $terms->count()) }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($terms as $term)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:border-primary/40 transition-colors flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 mb-1.5 flex items-center justify-between">
                            <span>{{ $term->term }}</span>
                            <span class="text-[10px] font-mono text-slate-400 font-normal">{{ $term->term_key }}</span>
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            {{ $term->definition }}
                        </p>
                    </div>
                    @if($term->article)
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-primary">
                        <span>Read Full Guide ({{ $term->article->pillar->name }})</span>
                        <a href="{{ localized_route('wiki.article', ['pillarSlug' => $term->article->pillar->slug, 'articleSlug' => $term->article->slug]) }}" class="hover:underline flex items-center gap-1">
                            Explore <i class="bi bi-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection
