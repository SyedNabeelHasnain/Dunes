@extends('layouts.app')

@section('content')
@php
    $cat = $categorySlug ? $categories->firstWhere('slug', $categorySlug) : null;
    $total = $posts->total();
    $totalPages = $posts->lastPage();
    $page = $posts->currentPage();
@endphp

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type":"ListItem","position":1,"name":"Home","item":"{{ route('home') }}"},
    {"@type":"ListItem","position":2,"name":"Blog","item":"{{ route('blog.index') }}"}
    @if ($cat)
    ,{"@type":"ListItem","position":3,"name":"{{ $cat->name }}","item":"{{ route('blog.index', ['category' => $cat->slug]) }}"}
    @endif
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "{{ $cat ? $cat->name . ' Blog' : 'Dubai Travel Blog' }}",
  "description": "{{ $cat ? $cat->description : 'Expert guides and travel tips.' }}",
  "url": "{{ request()->fullUrl() }}"
}
</script>

<!-- Blog Hero -->
<section class="py-12 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden" style="margin-top: calc(-1 * var(--header-h, 72px));">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-16">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="{{ !$cat ? 'text-white font-semibold' : '' }}">
                    @if($cat)
                        <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a>
                    @else
                        Blog
                    @endif
                </li>
                @if ($cat)
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ $cat->name }}</li>
                @endif
            </ol>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight mb-3">
            @if ($cat)
                {{ $cat->name }}
            @elseif ($search)
                Search: <em class="text-amber-400 not-italic">"{{ $search }}"</em>
            @else
                Dubai Travel Blog
            @endif
        </h1>
        <p class="text-slate-300 text-sm sm:text-base max-w-2xl leading-relaxed">
            @if ($cat && $cat->description)
                {{ $cat->description }}
            @elseif (!$search)
                Expert guides, travel tips, and stories from Dubai's desert safari specialists.
            @else
                {{ $total }} {{ Str::plural('result', $total) }} found for "{{ $search }}"
            @endif
        </p>
    </div>
</section>

<!-- Category Filter + Search -->
<section class="bg-white border-b border-slate-200 py-3 sticky top-[var(--header-h,72px)] z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex gap-2 overflow-x-auto no-scrollbar w-full sm:w-auto pb-1">
                <a href="{{ route('blog.index') }}" class="rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors {{ !$categorySlug && !$search ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">All</a>
                @foreach ($categories as $c)
                <a href="{{ route('blog.index', ['category' => $c->slug]) }}" class="rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors {{ $categorySlug === $c->slug ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $c->name }}</a>
                @endforeach
            </div>
            <form action="{{ route('blog.index') }}" method="get" class="flex gap-2 w-full sm:w-auto">
                @if ($categorySlug)
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input type="search" name="search" class="w-full rounded-full pl-4 pr-10 py-1.5 bg-slate-100 border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" placeholder="Search articles..." value="{{ $search }}">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-primary transition-colors" aria-label="Search articles">
                        <i class="bi bi-search text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
    @if (!$categorySlug && !$search && $page === 1 && $featuredPost)
    <!-- Featured Posts Magazine Grid -->
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-6">
            <span class="bg-amber-400 text-slate-950 font-bold px-3.5 py-1 rounded-full text-xs inline-flex items-center gap-1.5">
                <i class="bi bi-star-fill"></i> Featured Guides
            </span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            @php
                $featuredImg = $featuredPost->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $featuredPost->featured_image)) : asset('images/desert-safari-poster.avif');
            @endphp
            <!-- Main Hero Featured Card -->
            <div class="lg:col-span-7">
                <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block h-full group text-inherit">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200 flex flex-col h-full">
                        <div class="relative overflow-hidden aspect-[16/9]">
                            <img src="{{ $featuredImg }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $featuredPost->featured_image_alt ?: $featuredPost->title }}">
                            @if ($featuredPost->category)
                            <span class="absolute top-4 left-4 bg-primary text-white rounded-full px-3 py-1 text-xs font-bold shadow-md">{{ $featuredPost->category?->name }}</span>
                            @endif
                        </div>
                        <div class="p-6 flex flex-col justify-between flex-grow">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors">{{ $featuredPost->title }}</h2>
                                @if ($featuredPost->excerpt)
                                    <p class="text-slate-600 text-xs sm:text-sm line-clamp-2 mb-4 leading-relaxed">{{ $featuredPost->excerpt }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-slate-500 text-xs pt-3 border-t border-slate-200">
                                <span><i class="bi bi-person text-primary mr-1"></i>{{ $featuredPost->author_name ?: 'Dunes Discovery' }}</span>
                                <span><i class="bi bi-clock text-primary mr-1"></i>{{ $featuredPost->read_time }} min read</span>
                                @if ($featuredPost->published_at)
                                    <span><i class="bi bi-calendar3 text-primary mr-1"></i>{{ $featuredPost->published_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Side Featured Stack (2 Cards) -->
            @if (isset($sideFeatured) && $sideFeatured->count() > 0)
            <div class="lg:col-span-5 flex flex-col justify-between gap-6">
                @foreach ($sideFeatured as $sidePost)
                @php
                    $sideImg = $sidePost->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $sidePost->featured_image)) : asset('images/desert-safari-poster.avif');
                @endphp
                <a href="{{ route('blog.show', $sidePost->slug) }}" class="block flex-1 group text-inherit">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 border border-slate-200 flex flex-col sm:flex-row h-full">
                        <div class="sm:w-5/12 relative aspect-[16/10] sm:aspect-auto">
                            <img src="{{ $sideImg }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $sidePost->featured_image_alt ?: $sidePost->title }}">
                            @if ($sidePost->category)
                            <span class="absolute top-2 left-2 bg-primary text-white rounded-full px-2 py-0.5 text-[10px] font-bold shadow-sm">{{ $sidePost->category?->name }}</span>
                            @endif
                        </div>
                        <div class="sm:w-7/12 p-4 flex flex-col justify-between flex-grow">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm mb-1 line-clamp-2 group-hover:text-primary transition-colors leading-snug">{{ $sidePost->title }}</h3>
                                @if ($sidePost->excerpt)
                                    <p class="text-slate-500 text-xs line-clamp-2 mb-2 leading-relaxed">{{ $sidePost->excerpt }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 text-[11px] pt-2 border-t border-slate-200">
                                <span>{{ Str::limit($sidePost->author_name ?: 'Dunes Discovery', 12) }}</span>
                                <span>â€¢ {{ $sidePost->read_time }}m</span>
                                @if ($sidePost->published_at)
                                    <span>â€¢ {{ $sidePost->published_at->format('M j') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    <hr class="my-10 border-slate-200">
    @endif

    <!-- Post Grid -->
    @if ($posts->count() === 0)
    <div class="text-center py-16">
        <i class="bi bi-search text-5xl text-slate-300 block mb-4"></i>
        <h3 class="text-xl font-bold text-slate-900 mb-1">No articles found</h3>
        <p class="text-slate-500 text-sm mb-6">Try a different search term or browse all categories.</p>
        <a href="{{ route('blog.index') }}" class="btn-desert-animated-dark text-white rounded-full px-6 py-2.5 text-xs font-bold inline-block shadow-md">Browse All Articles</a>
    </div>
    @else

    @if ($total > 0)
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h2 class="text-xs uppercase font-extrabold tracking-wider text-slate-500">
            @if ($cat)
                Articles in {{ $cat->name }}
            @elseif ($search)
                Search Results
            @else
                Latest Articles
            @endif
            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs font-bold ml-1.5">{{ $total }}</span>
        </h2>
        <div class="text-slate-500 text-xs">Page {{ $page }} of {{ $totalPages }}</div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($posts as $post)
        @php
            $postImg = $post->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $post->featured_image)) : asset('images/desert-safari-poster.avif');
        @endphp
        <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 border border-slate-200 flex flex-col h-full group">
            <a href="{{ route('blog.show', $post->slug) }}" class="block relative aspect-[16/10] overflow-hidden">
                <img src="{{ $postImg }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->featured_image_alt ?: $post->title }}" loading="lazy">
                @if ($post->category)
                <span class="absolute top-3 left-3 bg-primary text-white rounded-full px-2.5 py-0.5 text-xs font-bold shadow-sm">{{ $post->category?->name }}</span>
                @endif
            </a>
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-base font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors leading-snug">
                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                </h3>
                @if ($post->excerpt)
                <p class="text-slate-600 text-xs sm:text-sm line-clamp-3 mb-4 flex-grow leading-relaxed">{{ $post->excerpt }}</p>
                @endif
                <div class="flex justify-between items-center mt-auto pt-3 border-t border-slate-200 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1"><i class="bi bi-person-circle text-primary"></i>{{ $post->author_name ?: 'Dunes Discovery' }}</span>
                    <div class="flex gap-2.5">
                        @if ($post->published_at)
                            <span>{{ $post->published_at->format('M j') }}</span>
                        @endif
                        <span><i class="bi bi-clock mr-1 text-primary"></i>{{ $post->read_time }}m</span>
                    </div>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <!-- Pagination -->
    @if ($totalPages > 1)
    <nav aria-label="Blog pagination" class="mt-12 flex justify-center">
        <ul class="flex items-center gap-1.5">
            @if ($page > 1)
            <li>
                <a class="w-9 h-9 rounded-xl flex items-center justify-center font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" href="{{ $posts->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
            </li>
            @endif
            @for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++)
            <li>
                <a class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs transition-colors {{ $p === $page ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}" href="{{ $posts->url($p) }}">{{ $p }}</a>
            </li>
            @endfor
            @if ($page < $totalPages)
            <li>
                <a class="w-9 h-9 rounded-xl flex items-center justify-center font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" href="{{ $posts->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
            </li>
            @endif
        </ul>
    </nav>
    @endif

    @endif

    <!-- CTA Banner -->
    <div class="rounded-3xl p-8 sm:p-12 text-center mt-14 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-primary/30 shadow-xl text-white">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">Ready for Your Dubai Adventure?</h2>
        <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto mb-6">Book a desert safari tour and make memories that last a lifetime.</p>
        <button data-action="open-booking" class="btn-desert-animated rounded-full px-8 py-3.5 font-bold text-white text-sm shadow-lg cursor-pointer" @click="$store.modal.open('booking')">Book a Tour Now</button>
    </div>
</div>
@endsection
