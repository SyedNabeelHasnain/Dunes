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
<section class="bg-dark text-white py-5 position-relative overflow-hidden" style="background:linear-gradient(135deg,#1a0a00 0%,#3d1f00 50%,#1a0a00 100%) !important; margin-top: -var(--header-h);">
    <div class="container position-relative z-1">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-dark mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item {{ !$cat ? 'active text-white' : '' }}">
                    @if($cat)
                        <a href="{{ route('blog.index') }}" class="text-white-50 text-decoration-none">Blog</a>
                    @else
                        Blog
                    @endif
                </li>
                @if ($cat)
                <li class="breadcrumb-item active text-white">{{ $cat->name }}</li>
                @endif
            </ol>
        </nav>
        <h1 class="fw-800 display-6 mb-2">
            @if ($cat)
                {{ $cat->name }}
            @elseif ($search)
                Search: <em class="text-warning">{{ $search }}</em>
            @else
                Dubai Travel Blog
            @endif
        </h1>
        <p class="text-white-50 mb-0 lead" style="max-width:600px;">
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
<section class="bg-white border-bottom py-3 sticky-top" style="top:68px;z-index:100;">
    <div class="container">
        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-between">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="{{ route('blog.index') }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ !$categorySlug && !$search ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                @foreach ($categories as $c)
                <a href="{{ route('blog.index', ['category' => $c->slug]) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $categorySlug === $c->slug ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $c->name }}</a>
                @endforeach
            </div>
            <form action="{{ route('blog.index') }}" method="get" class="d-flex gap-2">
                @if ($categorySlug)
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                @endif
                <input type="search" name="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Search articles..." value="{{ $search }}" style="min-width:200px;">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
</section>

<div class="container py-5">
    @if (!$categorySlug && !$search && $page === 1 && $featuredPost)
    <!-- Featured Post -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill"><i class="bi bi-star-fill me-1"></i> Featured</span>
        </div>
        <div class="row g-4">
            @php
                $featuredImg = $featuredPost->featured_image ? asset('images/blog/' . $featuredPost->featured_image) : asset('images/desert-safari-poster.jpg');
            @endphp
            <div class="col-12 col-lg-8">
                <a href="{{ route('blog.show', $featuredPost->slug) }}" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover bg-white">
                        <div class="position-relative" style="padding-bottom: 50%;">
                            <img src="{{ $featuredImg }}" class="position-absolute w-100 h-100 object-fit-cover" alt="{{ $featuredPost->featured_image_alt ?: $featuredPost->title }}">
                            @if ($featuredPost->category)
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary rounded-pill px-3 py-2 fw-bold">{{ $featuredPost->category->name }}</span>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <h2 class="h4 fw-800 text-dark mb-2 line-clamp-2">{{ $featuredPost->title }}</h2>
                            @if ($featuredPost->excerpt)
                                <p class="text-muted small line-clamp-2 mb-3">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="bi bi-person me-1"></i>{{ $featuredPost->author_name ?: 'Dunes Discovery' }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ $featuredPost->read_time }} min read</span>
                                @if ($featuredPost->published_at)
                                    <span><i class="bi bi-calendar3 me-1"></i>{{ $featuredPost->published_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <hr class="my-5 opacity-10">
    @endif

    <!-- Post Grid -->
    @if ($posts->count() === 0)
    <div class="text-center py-5">
        <i class="bi bi-search display-3 text-muted opacity-25"></i>
        <h3 class="mt-4 fw-bold text-dark">No articles found</h3>
        <p class="text-muted">Try a different search term or browse all categories.</p>
        <a href="{{ route('blog.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Browse All Articles</a>
    </div>
    @else

    @if ($total > 0)
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="h6 fw-bold text-uppercase text-muted mb-0">
            @if ($cat)
                Articles in {{ $cat->name }}
            @elseif ($search)
                Search Results
            @else
                Latest Articles
            @endif
            <span class="badge bg-light text-muted border ms-2">{{ $total }}</span>
        </h2>
        <div class="text-muted small">Page {{ $page }} of {{ $totalPages }}</div>
    </div>
    @endif

    <div class="row g-4">
        @foreach ($posts as $post)
        @php
            $postImg = $post->featured_image ? asset('images/blog/' . $post->featured_image) : asset('images/desert-safari-poster.jpg');
        @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover bg-white">
                <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none d-block position-relative" style="padding-bottom:60%;">
                    <img src="{{ $postImg }}" class="position-absolute w-100 h-100 object-fit-cover" alt="{{ $post->featured_image_alt ?: $post->title }}" loading="lazy">
                    @if ($post->category)
                    <span class="position-absolute top-0 start-0 m-3 badge bg-primary rounded-pill px-2 py-1 small fw-bold">{{ $post->category->name }}</span>
                    @endif
                </a>
                <div class="card-body p-4 d-flex flex-column">
                    <h3 class="h6 fw-800 mb-2">
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none line-clamp-2">{{ $post->title }}</a>
                    </h3>
                    @if ($post->excerpt)
                    <p class="text-muted small line-clamp-3 flex-grow-1 mb-3">{{ $post->excerpt }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light small text-muted">
                        <span><i class="bi bi-person-circle me-1"></i>{{ $post->author_name ?: 'Dunes Discovery' }}</span>
                        <div class="d-flex gap-2">
                            @if ($post->published_at)
                                <span>{{ $post->published_at->format('M j') }}</span>
                            @endif
                            <span><i class="bi bi-clock me-1"></i>{{ $post->read_time }}m</span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if ($totalPages > 1)
    <nav aria-label="Blog pagination" class="mt-5">
        <ul class="pagination justify-content-center gap-1">
            @if ($page > 1)
            <li class="page-item">
                <a class="page-link rounded-3 border-0 fw-bold bg-light text-dark" href="{{ $posts->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
            </li>
            @endif
            @for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++)
            <li class="page-item {{ $p === $page ? 'active' : '' }}">
                <a class="page-link rounded-3 border-0 fw-bold {{ $p === $page ? 'btn-primary' : 'bg-light text-dark' }}" href="{{ $posts->url($p) }}">{{ $p }}</a>
            </li>
            @endfor
            @if ($page < $totalPages)
            <li class="page-item">
                <a class="page-link rounded-3 border-0 fw-bold bg-light text-dark" href="{{ $posts->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
            </li>
            @endif
        </ul>
    </nav>
    @endif

    @endif

    <!-- Newsletter / CTA Banner -->
    <div class="rounded-4 p-5 text-center mt-5" style="background:linear-gradient(135deg,#F58F43 0%,#e07020 100%);">
        <h2 class="fw-800 text-white mb-2">Ready for Your Dubai Adventure?</h2>
        <p class="text-white opacity-75 mb-4">Book a desert safari tour and make memories that last a lifetime.</p>
        <button data-action="open-booking" class="btn btn-white rounded-pill px-5 py-3 fw-bold shadow-lg">Book a Tour Now</button>
    </div>
</div>

<style>
.line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.line-clamp-3 { display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden; }
.card-hover { transition: transform .2s, box-shadow .2s; }
.card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.1) !important; }
.object-fit-cover { object-fit: cover; }
</style>
@endsection
