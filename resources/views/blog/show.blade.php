@extends('layouts.app')

@section('content')
@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $modifiedAt = $post->updated_at ?? $post->created_at;
    $authorName = $post->author_name ?: 'Dunes Discovery';
    $authorTitle = $post->author_title ?? 'Dubai Tourism Expert';
    $authorBio = $post->author_bio ?? '';
    
    $featImgPath = $post->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $post->featured_image)) : asset('images/desert-safari-poster.avif');
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
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $post->category?->name, 'item' => route('blog.index', ['category' => $post->category?->slug])];
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
            '@type' => 'TravelAgency',
            '@id' => route('home') . '#organization',
            'name' => 'Dunes Discovery Tourism',
            'url' => route('home'),
            'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo.png'), 'width' => 160, 'height' => 46]
        ],
        'datePublished' => $publishedAt ? $publishedAt->toIso8601String() : now()->toIso8601String(),
        'dateModified' => $modifiedAt ? $modifiedAt->toIso8601String() : now()->toIso8601String(),
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        'url' => $canonical,
        'wordCount' => str_word_count(strip_tags($post->content ?? '')),
        'timeRequired' => 'PT' . (int)($post->read_time ?: 5) . 'M',
        'inLanguage' => 'en',
        'keywords' => $pageKeys,
        'articleSection' => $post->category ? $post->category->name : 'Travel'
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems
    ];
@endphp

@push('preloads')
    <link rel="preload" as="image" href="{{ $featImgPath }}" type="image/avif">
@endpush

<!-- Article OG Overrides & Metadata -->
@push('scripts')
<script>
    document.querySelector('meta[property="og:type"]')?.setAttribute('content', 'article');
</script>
@endpush

<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
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
    <header class="bg-slate-950 text-white relative overflow-hidden min-h-[420px]" style="margin-top: calc(-1 * var(--header-h, 72px));">
        @if ($post->featured_image)
        <img src="{{ $featImgPath }}" class="absolute inset-0 w-full h-full object-cover opacity-25" alt="{{ $post->featured_image_alt ?: $post->title }}" fetchpriority="high" itemprop="image">
        @endif
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-24 pb-14">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 flex-wrap">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                    <li><span class="text-white/40">/</span></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                    @if ($post->category)
                    <li><span class="text-white/40">/</span></li>
                    <li><a href="{{ route('blog.index', ['category' => $post->category?->slug]) }}" class="hover:text-white transition-colors">{{ $post->category?->name }}</a></li>
                    @endif
                    <li><span class="text-white/40">/</span></li>
                    <li class="text-white font-semibold truncate max-w-[200px] sm:max-w-none">{{ Str::limit($post->title, 40) }}</li>
                </ol>
            </nav>

            @if ($post->category)
            <a href="{{ route('blog.index', ['category' => $post->category?->slug]) }}" class="bg-primary text-white rounded-full px-3.5 py-1 text-xs font-bold mb-4 inline-block shadow-sm">{{ $post->category?->name }}</a>
            @endif

            <h1 itemprop="headline" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4 max-w-4xl">{{ $post->title }}</h1>

            @if ($post->subtitle)
            <p class="text-slate-300 text-base sm:text-lg mb-6 max-w-3xl leading-relaxed">{{ $post->subtitle }}</p>
            @endif

            <!-- Author info -->
            <div class="flex items-center gap-6 flex-wrap text-white/80 text-xs sm:text-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center font-black text-white shrink-0" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        <span>{{ strtoupper(substr($authorName, 0, 1)) }}</span>
                        <meta itemprop="name" content="{{ $authorName }}">
                        <meta itemprop="jobTitle" content="{{ $authorTitle }}">
                    </div>
                    <div>
                        <div class="text-white font-bold leading-tight">{{ $authorName }}</div>
                        <div class="text-white/60 text-xs">{{ $authorTitle }}</div>
                    </div>
                </div>
                <div class="flex gap-4 items-center">
                    @if ($publishedAt)
                    <span itemprop="datePublished" content="{{ $publishedAt->toIso8601String() }}"><i class="bi bi-calendar3 mr-1 text-primary"></i>{{ $publishedAt->format('F j, Y') }}</span>
                    @endif
                    <span><i class="bi bi-clock mr-1 text-primary"></i>{{ $post->read_time }} min read</span>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Main Content (8 cols) -->
            <div class="lg:col-span-8">
                @if ($post->ai_summary)
                <div class="border-l-4 border-primary bg-primary/5 rounded-2xl p-5 mb-8">
                    <div class="flex items-center gap-2 mb-1.5">
                        <i class="bi bi-robot text-primary"></i>
                        <span class="font-bold text-xs uppercase text-primary tracking-wider">Quick Summary</span>
                    </div>
                    <p class="text-slate-700 text-sm leading-relaxed mb-0">{{ $post->ai_summary }}</p>
                </div>
                @endif

                @if ($post->tags->count() > 0)
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.index', ['search' => $tag->name]) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full transition-colors inline-flex items-center gap-1">
                        <i class="bi bi-tag text-slate-400"></i>{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Post HTML Content -->
                <div class="blog-content prose max-w-none text-slate-800 leading-relaxed" itemprop="articleBody">
                    {!! $post->content !!}
                </div>

                @if ($post->featured_image_caption)
                <p class="text-slate-400 text-xs text-center mt-3 italic">{{ $post->featured_image_caption }}</p>
                @endif

                <!-- FAQs Accordion with Alpine.js -->
                @if ($post->faqs->count() > 0)
                <div class="mt-12 pt-8 border-t border-slate-200" id="faqs" x-data="{ openFaq: 0 }">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="bi bi-question-circle text-primary"></i>
                        <span>Frequently Asked Questions</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach ($post->faqs as $fi => $faq)
                        <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-xs">
                            <button type="button" 
                                    class="w-full text-left px-5 py-4 font-bold text-slate-900 flex items-center justify-between gap-4 cursor-pointer"
                                    @click="openFaq = (openFaq === {{ $fi }} ? null : {{ $fi }})">
                                <span class="text-sm sm:text-base">{{ $faq->question }}</span>
                                <i class="bi bi-chevron-down transition-transform duration-300 text-slate-400 shrink-0"
                                   :class="openFaq === {{ $fi }} ? 'rotate-180 text-primary' : ''"></i>
                            </button>
                            <div x-show="openFaq === {{ $fi }}" x-collapse x-cloak class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if ($authorBio)
                <div class="mt-10 p-6 rounded-2xl bg-slate-50 border border-slate-200 flex gap-4 items-start">
                    <div class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center font-black text-xl shrink-0">
                        {{ strtoupper(substr($authorName, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-base">{{ $authorName }}</div>
                        <div class="text-slate-500 text-xs mb-2">{{ $authorTitle }}</div>
                        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-0">{{ $authorBio }}</p>
                    </div>
                </div>
                @endif

                <!-- Social Share Widget -->
                <div class="mt-10 pt-6 border-t border-slate-200 flex items-center gap-2 sm:gap-3 flex-wrap text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[11px] mr-2">Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonical) }}" target="_blank" rel="noopener noreferrer" class="border border-slate-200 hover:border-primary text-slate-700 hover:text-primary rounded-full px-3.5 py-1.5 transition-colors inline-flex items-center gap-1 font-semibold">
                        <i class="bi bi-facebook text-blue-600"></i>Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonical) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="border border-slate-200 hover:border-primary text-slate-700 hover:text-primary rounded-full px-3.5 py-1.5 transition-colors inline-flex items-center gap-1 font-semibold">
                        <i class="bi bi-twitter-x"></i>Twitter/X
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $canonical) }}" target="_blank" rel="noopener noreferrer" class="border border-slate-200 hover:border-primary text-slate-700 hover:text-primary rounded-full px-3.5 py-1.5 transition-colors inline-flex items-center gap-1 font-semibold">
                        <i class="bi bi-whatsapp text-emerald-600"></i>WhatsApp
                    </a>
                    <button type="button" class="border border-slate-200 hover:border-primary text-slate-700 hover:text-primary rounded-full px-3.5 py-1.5 transition-colors inline-flex items-center gap-1 font-semibold cursor-pointer" onclick="navigator.clipboard.writeText('{{ $canonical }}').then(()=> { this.innerHTML = '<i class=\'bi bi-check2\'></i> Copied!'; setTimeout(()=> { this.innerHTML = '<i class=\'bi bi-link-45deg\'></i> Copy Link' }, 2000); })">
                        <i class="bi bi-link-45deg"></i>Copy Link
                    </button>
                </div>
            </div>

            <!-- Sidebar Widgets (4 cols) -->
            <aside class="lg:col-span-4">
                <div class="sticky top-24 space-y-6">
                    <!-- CTA Widget -->
                    <div class="rounded-2xl p-6 bg-gradient-to-br from-primary to-orange-600 text-white shadow-lg">
                        <h3 class="font-extrabold text-lg text-white mb-1.5">Book a Desert Safari</h3>
                        <p class="text-xs text-white/90 mb-4 leading-relaxed">From <span data-aed="99" class="font-bold">AED 99</span> per person. Instant confirmation.</p>
                        <button data-action="open-booking" class="w-full bg-white hover:bg-slate-50 text-slate-950 font-bold rounded-full py-2.5 text-xs transition-colors cursor-pointer shadow-xs mb-2" @click="$store.modal.open('booking')">
                            <i class="bi bi-calendar-check mr-1.5"></i>Book Now
                        </button>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode('Hi! I read your blog about ' . $post->title . ' and would like to know more.') }}" class="w-full border border-white/40 hover:border-white text-white font-bold rounded-full py-2 text-xs transition-colors flex items-center justify-center gap-1.5" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp text-emerald-300"></i>Ask on WhatsApp
                        </a>
                    </div>

                    <!-- Categories Sidebar -->
                    @php $cats = \App\Models\BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get(); @endphp
                    @if ($cats->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-4 border-b border-slate-100">
                            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-tags text-primary"></i>Categories
                            </h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach ($cats as $catItem)
                            @php $cc = \App\Models\BlogPost::where('category_id', $catItem->id)->where('status', 'published')->count(); @endphp
                            <a href="{{ route('blog.index', ['category' => $catItem->slug]) }}" class="flex justify-between items-center px-4 py-3 text-xs text-slate-700 hover:text-primary hover:bg-slate-50 transition-colors">
                                <span>{{ $catItem->name }}</span>
                                <span class="bg-slate-100 text-slate-600 rounded-full px-2 py-0.5 font-semibold text-[10px]">{{ $cc }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Related posts widget -->
                    @if ($relatedPosts->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-4 border-b border-slate-100">
                            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-newspaper text-primary"></i>Related Articles
                            </h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach ($relatedPosts as $rp)
                            @php $rpImg = $rp->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $rp->featured_image)) : asset('images/desert-safari-poster.avif'); @endphp
                            <a href="{{ route('blog.show', $rp->slug) }}" class="flex gap-3 p-4 hover:bg-slate-50 transition-colors group">
                                <img src="{{ $rpImg }}" class="w-13 h-13 object-cover rounded-xl shrink-0" loading="lazy" alt="{{ $rp->title }}">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs line-clamp-2 group-hover:text-primary transition-colors leading-snug">{{ $rp->title }}</div>
                                    <span class="text-slate-400 text-[11px] mt-1 block"><i class="bi bi-clock mr-1 text-primary"></i>{{ $rp->read_time }} min</span>
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
<section class="py-12 sm:py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-8">You Might Also Like</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($relatedPosts as $rp)
            @php $rpImg = $rp->featured_image ? asset('images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $rp->featured_image)) : asset('images/desert-safari-poster.avif'); @endphp
            <article class="bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 border border-slate-100 flex flex-col h-full group">
                <a href="{{ route('blog.show', $rp->slug) }}" class="block relative aspect-[16/10] overflow-hidden">
                    <img src="{{ $rpImg }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $rp->featured_image_alt ?: $rp->title }}" loading="lazy">
                    @if ($rp->category)
                        <span class="absolute top-3 left-3 bg-primary text-white rounded-full px-2.5 py-0.5 text-xs font-bold shadow-sm">{{ $rp->category?->name }}</span>
                    @endif
                </a>
                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="text-sm font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors leading-snug">
                        <a href="{{ route('blog.show', $rp->slug) }}">{{ $rp->title }}</a>
                    </h3>
                    @if ($rp->excerpt)
                        <p class="text-slate-500 text-xs line-clamp-2 mb-3 leading-relaxed">{{ $rp->excerpt }}</p>
                    @endif
                    <div class="text-[11px] text-slate-400 mt-auto pt-2 border-t border-slate-100">
                        <i class="bi bi-clock mr-1 text-primary"></i>{{ $rp->read_time }} min read
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Bottom CTA -->
<section class="py-12 sm:py-16 bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 text-white border-t border-primary/30">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">Ready for Your Dubai Adventure?</h2>
        <p class="text-slate-300 text-sm sm:text-base mb-6">Join thousands of satisfied guests who have experienced Dubai with Dunes Discovery Tourism.</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <button data-action="open-booking" class="btn-desert-animated rounded-full px-8 py-3.5 font-bold text-white text-sm shadow-lg cursor-pointer" @click="$store.modal.open('booking')">Book a Desert Safari</button>
            <a href="{{ route('tours.index') }}" class="border border-white/40 hover:border-white text-white rounded-full px-8 py-3.5 font-bold text-sm transition-colors">Browse All Tours</a>
        </div>
    </div>
</section>

<style>
.blog-content { font-size: 1.05rem; line-height: 1.85; color: #334155; }
.blog-content h2 { font-weight: 800; margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem; color: #0f172a; }
.blog-content h3 { font-weight: 700; margin-top: 1.75rem; margin-bottom: .75rem; font-size: 1.25rem; color: #0f172a; }
.blog-content h4 { font-weight: 700; margin-top: 1.5rem; margin-bottom: .5rem; color: #0f172a; }
.blog-content p  { margin-bottom: 1.25rem; }
.blog-content ul, .blog-content ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
.blog-content li { margin-bottom: .4rem; }
.blog-content blockquote { border-left: 4px solid #F69044; padding: .75rem 1.25rem; margin: 1.5rem 0; background: #fff8f0; border-radius: 0 12px 12px 0; color: #475569; font-style: italic; }
.blog-content img { max-width: 100%; border-radius: 16px; height: auto; margin: 1.5rem 0; }
.blog-content a { color: #F69044; text-decoration: none; font-weight: 600; }
.blog-content a:hover { text-decoration: underline; }
.blog-content table { width: 100% !important; border-collapse: collapse !important; margin: 1.5rem 0 !important; font-size: .95rem !important; box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important; border-radius: 12px !important; overflow: hidden !important; }
.blog-content table th, .blog-content table td { padding: .75rem 1rem !important; border: 1px solid #e2e8f0 !important; text-align: left !important; }
.blog-content table th, .blog-content table thead th, .blog-content table .table-dark th, .blog-content table tr th { background-color: #0f172a !important; color: #ffffff !important; font-weight: 700 !important; }
.blog-content table td { background-color: #ffffff !important; color: #1e293b !important; }
.blog-content table tr:nth-child(even) td { background-color: #f8fafc !important; }
</style>
@endsection
