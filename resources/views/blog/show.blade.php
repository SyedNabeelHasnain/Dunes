@extends('layouts.app')

@section('content')
@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $modifiedAt = $post->updated_at ?? $post->created_at;
    $authorName = $post->author_name ?: 'Dunes Discovery';
    $authorTitle = $post->author_title ?? 'Dubai Tourism Expert';
    $authorBio = $post->author_bio ?? '';
    
    $featImgPath = $post->featured_image ? asset('images/blog/' . $post->featured_image) : asset('images/desert-safari-poster.jpg');
    $canonical = $post->canonical_url ?: route('blog.show', $post->slug);
    $ogImageUrl = $post->og_image ?: $featImgPath;
    
    $pageTitle = $post->meta_title ?: $post->title . ' | Dunes Discovery';
    $pageDesc = $post->meta_desc ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 155));
    $pageKeys = $post->meta_keywords ?: 'dubai, desert safari, travel';
    
    // Breadcrumb Schema
    $breadcrumbItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')]
    ];
    if ($post->category) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $post->category->name, 'item' => route('blog.index', ['category' => $post->category->slug])];
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 4, 'name' => $post->title, 'item' => $canonical];
    } else {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $canonical];
    }
    
    // Article Schema
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => $post->schema_type ?? 'BlogPosting',
        'headline' => $post->title,
        'description' => $pageDesc,
        'image' => [$ogImageUrl],
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
            'jobTitle' => $authorTitle,
            'worksFor' => ['@type' => 'Organization', 'name' => 'Dunes Discovery Tourism', 'url' => route('home')]
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Dunes Discovery Tourism',
            'url' => route('home'),
            'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo.png'), 'width' => 160, 'height' => 46]
        ],
        'datePublished' => $publishedAt ? $publishedAt->toIso8601String() : now()->toIso8601String(),
        'dateModified' => $modifiedAt ? $modifiedAt->toIso8601String() : now()->toIso8601String(),
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        'url' => $canonical,
        'wordCount' => str_word_count(strip_tags($post->content ?? '')),
        'timeRequired' => 'PT' . (int)$post->read_time . 'M',
        'inLanguage' => 'en',
        'keywords' => $pageKeys,
        'articleSection' => $post->category ? $post->category->name : 'Travel'
    ];
@endphp

<!-- Article OG Overrides & Metadata -->
@push('scripts')
<script>
    document.querySelector('meta[property="og:type"]')?.setAttribute('content', 'article');
</script>
@endpush

<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$breadcrumbItems]) !!}</script>
@if ($post->faqs->count() > 0)
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @foreach($post->faqs as $fi => $faq)
    {
      "@type": "Question",
      "name": "{{ $faq->question }}",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "{{ $faq->answer }}"
      }
    }{{ $loop->last ? '' : ',' }}
    @endforeach
  ]
}
</script>
@endif

<article itemscope itemtype="https://schema.org/{{ $post->schema_type ?? 'BlogPosting' }}" class="blog-article">
    <header class="bg-dark text-white position-relative overflow-hidden" style="min-height:420px; margin-top: -var(--header-h);">
        @if ($post->featured_image)
        <img src="{{ $featImgPath }}" class="position-absolute w-100 h-100" style="object-fit:cover;opacity:.3;top:0;left:0;" alt="{{ $post->featured_image_alt ?: $post->title }}" fetchpriority="high" itemprop="image">
        @endif
        <div class="container position-relative z-1 py-5">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb breadcrumb-dark mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-white-50 text-decoration-none">Blog</a></li>
                    @if ($post->category)
                    <li class="breadcrumb-item"><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="text-white-50 text-decoration-none">{{ $post->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active text-white-75 d-none d-md-list-item">{{ Str::limit($post->title, 40) }}</li>
                </ol>
            </nav>

            @if ($post->category)
            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="badge bg-primary text-white rounded-pill px-3 py-2 fw-bold mb-3 text-decoration-none d-inline-block">{{ $post->category->name }}</a>
            @endif

            <h1 itemprop="headline" class="post-headline fw-800 display-6 text-white mb-3" style="max-width:780px;">{{ $post->title }}</h1>

            @if ($post->subtitle)
            <p class="lead text-white-50 mb-4" style="max-width:680px;">{{ $post->subtitle }}</p>
            @endif

            <!-- Author info -->
            <div class="d-flex align-items-center gap-4 flex-wrap text-white-50 small">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold text-white" style="width:36px;height:36px;flex-shrink:0;font-size:.85rem;" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        <span>{{ strtoupper(substr($authorName, 0, 1)) }}</span>
                        <meta itemprop="name" content="{{ $authorName }}">
                        <meta itemprop="jobTitle" content="{{ $authorTitle }}">
                    </div>
                    <div>
                        <div class="text-white fw-semibold" style="font-size:.85rem;">{{ $authorName }}</div>
                        <div class="text-white-50" style="font-size:.75rem;">{{ $authorTitle }}</div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    @if ($publishedAt)
                    <span itemprop="datePublished" content="{{ $publishedAt->toIso8601String() }}"><i class="bi bi-calendar3 me-1"></i>{{ $publishedAt->format('F j, Y') }}</span>
                    @endif
                    <span><i class="bi bi-clock me-1"></i>{{ $post->read_time }} min read</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-5">
        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                @if ($post->ai_summary)
                <div class="post-ai-summary alert border-start border-4 border-primary bg-primary bg-opacity-5 rounded-3 mb-4 py-3 px-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-robot text-primary"></i>
                        <span class="fw-bold small text-primary text-uppercase">Quick Summary</span>
                    </div>
                    <p class="mb-0 small text-muted">{{ $post->ai_summary }}</p>
                </div>
                @endif

                @if ($post->tags->count() > 0)
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.index', ['search' => $tag->name]) }}" class="badge bg-light text-muted border text-decoration-none fw-normal px-3 py-2 rounded-pill small">
                        <i class="bi bi-tag me-1"></i>{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Post HTML Content -->
                <div class="blog-content text-dark" itemprop="articleBody">
                    {!! $post->content !!}
                </div>

                @if ($post->featured_image_caption)
                <p class="text-muted small text-center mt-2 fst-italic">{{ $post->featured_image_caption }}</p>
                @endif

                <!-- FAQs Accordion -->
                @if ($post->faqs->count() > 0)
                <div class="mt-5 pt-4 border-top" id="faqs">
                    <h2 class="fw-800 h4 mb-4 text-dark"><i class="bi bi-question-circle text-primary me-2"></i>Frequently Asked Questions</h2>
                    <div class="accordion accordion-flush" id="faqAccordion">
                        @foreach ($post->faqs as $fi => $faq)
                        <div class="accordion-item border rounded-3 mb-2 overflow-hidden bg-white">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $fi > 0 ? 'collapsed' : '' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $fi }}">
                                    {{ $faq->question }}
                                </button>
                            </h3>
                            <div id="faq{{ $fi }}" class="accordion-collapse collapse {{ $fi === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">{!! nl2br(e($faq->answer)) !!}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if ($authorBio)
                <div class="mt-5 p-4 rounded-4 bg-light border d-flex gap-4 align-items-start flex-wrap flex-md-nowrap">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-800 flex-shrink-0" style="width:60px;height:60px;font-size:1.5rem;">
                        {{ strtoupper(substr($authorName, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $authorName }}</div>
                        <div class="text-muted small mb-2">{{ $authorTitle }}</div>
                        <p class="mb-0 text-muted small">{{ $authorBio }}</p>
                    </div>
                </div>
                @endif

                <!-- Social Share Widget -->
                <div class="mt-5 d-flex align-items-center gap-3 flex-wrap">
                    <span class="fw-semibold small text-muted">Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonical) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-facebook me-1"></i>Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonical) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-twitter-x me-1"></i>Twitter/X
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $canonical) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($canonical) }}&title={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        <i class="bi bi-linkedin me-1"></i>LinkedIn
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="navigator.clipboard.writeText('{{ $canonical }}').then(()=> { this.innerHTML = '<i class=\'bi bi-check2\'></i> Copied!'; setTimeout(()=> { this.innerHTML = '<i class=\'bi bi-link-45deg\'></i> Copy Link' }, 2000); })">
                        <i class="bi bi-link-45deg me-1"></i>Copy Link
                    </button>
                </div>
            </div>

            <!-- Sidebar Widgets -->
            <aside class="col-lg-4">
                <div class="sticky-top" style="top:90px;">
                    <!-- CTA Widget -->
                    <div class="rounded-4 p-4 mb-4 text-white" style="background:linear-gradient(135deg,#F58F43,#e07020);">
                        <h3 class="fw-800 h5 mb-2">Book a Desert Safari</h3>
                        <p class="small opacity-75 mb-3">From AED 99 per person. Instant confirmation.</p>
                        <button data-action="open-booking" class="btn btn-white rounded-pill fw-bold w-100">
                            <i class="bi bi-calendar-check me-1"></i>Book Now
                        </button>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056') }}?text={{ urlencode('Hi! I read your blog about ' . $post->title . ' and would like to know more.') }}" class="btn btn-outline-light rounded-pill fw-bold w-100 mt-2 small" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-1"></i>Ask on WhatsApp
                        </a>
                    </div>

                    <!-- Categories Sidebar -->
                    @php $cats = \App\Models\BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get(); @endphp
                    @if ($cats->count() > 0)
                    <div class="card card-modern mb-4 bg-white border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 ps-4">
                            <h3 class="fw-bold h6 mb-0 text-dark"><i class="bi bi-tags me-1 text-primary"></i>Categories</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($cats as $catItem)
                            @php $cc = \App\Models\BlogPost::where('category_id', $catItem->id)->where('status', 'published')->count(); @endphp
                            <a href="{{ route('blog.index', ['category' => $catItem->slug]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center small py-3 ps-4 pe-4 border-light text-dark">
                                {{ $catItem->name }}
                                <span class="badge bg-light text-muted border rounded-pill">{{ $cc }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Related posts widget -->
                    @if ($relatedPosts->count() > 0)
                    <div class="card card-modern bg-white border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 ps-4">
                            <h3 class="fw-bold h6 mb-0 text-dark"><i class="bi bi-newspaper me-1 text-primary"></i>Related Articles</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($relatedPosts as $rp)
                            @php $rpImg = $rp->featured_image ? asset('images/blog/' . $rp->featured_image) : asset('images/desert-safari-poster.jpg'); @endphp
                            <a href="{{ route('blog.show', $rp->slug) }}" class="list-group-item list-group-item-action py-3 ps-4 pe-4 border-light text-decoration-none text-dark">
                                <div class="d-flex gap-3 align-items-start">
                                    <img src="{{ $rpImg }}" style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;" loading="lazy" alt="">
                                    <div>
                                        <div class="fw-semibold text-dark line-clamp-2" style="font-size: 0.85rem;">{{ $rp->title }}</div>
                                        <div class="text-muted mt-1" style="font-size:.72rem;"><i class="bi bi-clock me-1 text-primary"></i>{{ $rp->read_time }} min</div>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</article>

<!-- Related Articles Bottom Section -->
@if ($relatedPosts->count() > 0)
<section class="bg-light py-5 border-top">
    <div class="container">
        <h2 class="fw-800 h4 mb-4 text-dark">You Might Also Like</h2>
        <div class="row g-4">
            @foreach ($relatedPosts as $rp)
            @php $rpImg = $rp->featured_image ? asset('images/blog/' . $rp->featured_image) : asset('images/desert-safari-poster.jpg'); @endphp
            <div class="col-md-4">
                <article class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover bg-white">
                    <a href="{{ route('blog.show', $rp->slug) }}" class="d-block position-relative text-decoration-none" style="padding-bottom:55%;">
                        <img src="{{ $rpImg }}" class="position-absolute w-100 h-100 object-fit-cover" alt="{{ $rp->featured_image_alt ?: $rp->title }}" loading="lazy">
                        @if ($rp->category)
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary rounded-pill small">{{ $rp->category->name }}</span>
                        @endif
                    </a>
                    <div class="card-body p-4">
                        <h3 class="h6 fw-800 mb-2"><a href="{{ route('blog.show', $rp->slug) }}" class="text-dark text-decoration-none line-clamp-2">{{ $rp->title }}</a></h3>
                        @if ($rp->excerpt)
                            <p class="text-muted small line-clamp-2 mb-0">{{ $rp->excerpt }}</p>
                        @endif
                        <div class="mt-3 small text-muted"><i class="bi bi-clock me-1 text-primary"></i>{{ $rp->read_time }} min read</div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Bottom CTA -->
<section class="py-5" style="background:linear-gradient(135deg,#1a0a00,#3d1f00);">
    <div class="container text-center text-white">
        <h2 class="fw-800 display-6 mb-3">Ready for Your Dubai Adventure?</h2>
        <p class="text-white-50 mb-4 lead">Join thousands of satisfied guests who have experienced Dubai with Dunes Discovery Tourism.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <button data-action="open-booking" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg">Book a Desert Safari</button>
            <a href="{{ route('tours.index') }}" class="btn btn-outline-light rounded-pill px-5 py-3 fw-bold">Browse All Tours</a>
        </div>
    </div>
</section>

<style>
.blog-content { font-size: 1.05rem; line-height: 1.85; color: #333; }
.blog-content h2 { font-weight: 800; margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem; }
.blog-content h3 { font-weight: 700; margin-top: 1.75rem; margin-bottom: .75rem; font-size: 1.2rem; }
.blog-content h4 { font-weight: 700; margin-top: 1.5rem; margin-bottom: .5rem; }
.blog-content p  { margin-bottom: 1.25rem; }
.blog-content ul, .blog-content ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
.blog-content li { margin-bottom: .4rem; }
.blog-content blockquote { border-left: 4px solid #F58F43; padding: .75rem 1.25rem; margin: 1.5rem 0; background: #fff8f0; border-radius: 0 8px 8px 0; color: #555; font-style: italic; }
.blog-content img { max-width: 100%; border-radius: 12px; height: auto; margin: 1rem 0; }
.blog-content a { color: #F58F43; text-decoration: none; font-weight: 500; }
.blog-content a:hover { text-decoration: underline; }
.blog-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; font-size: .9rem; }
.blog-content table th, .blog-content table td { padding: .6rem 1rem; border: 1px solid #dee2e6; text-align: left; }
.blog-content table th { background: #f8f9fa; font-weight: 700; }
.line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.object-fit-cover { object-fit: cover; }
.card-hover { transition: transform .2s, box-shadow .2s; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
</style>
@endsection
