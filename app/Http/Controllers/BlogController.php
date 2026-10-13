<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Tour;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts.
     */
    public function index(Request $request)
    {

        $categorySlug = $request->input('category');
        $tagSlug = $request->input('tag');
        $search = $request->input('search');

        $query = BlogPost::where('status', 'published')->with(['category', 'tags'])->orderBy('published_at', 'desc');

        if ($categorySlug) {
            $category = BlogCategory::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($tagSlug) {
            $tag = BlogTag::where('slug', $tagSlug)->first();
            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tag_id', $tag->id);
                });
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();
        $categories = BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get();
        $popularTags = BlogTag::withCount('posts')->orderBy('posts_count', 'desc')->limit(12)->get();

        $featuredPosts = BlogPost::where('status', 'published')
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        if ($featuredPosts->count() < 3) {
            $excludeIds = $featuredPosts->pluck('id')->toArray();
            $fillers = BlogPost::where('status', 'published')
                ->whereNotIn('id', $excludeIds)
                ->with('category')
                ->orderBy('published_at', 'desc')
                ->take(3 - $featuredPosts->count())
                ->get();
            $featuredPosts = $featuredPosts->concat($fillers);
        }

        $featuredPost = $featuredPosts->first();
        $sideFeatured = $featuredPosts->slice(1, 2);

        $settingsService = app(SettingsService::class);
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        if ($currentLocale === 'en' || empty($currentLocale)) {
            $defaultTitle = "Dubai Desert Safari & Travel Blog ({$currentYear}) | Expert Insights | Dunes Discovery";
            $defaultDesc = 'Read insider travel tips, desert safari packing guides, buggy safety advice, and Dubai itinerary recommendations by Dunes Discovery Tourism.';
            $defaultKeys = 'dubai travel blog, desert safari guide, dubai desert tips, travel advice dubai';

            $pageTitle = $categorySlug
                ? ucwords(str_replace('-', ' ', $categorySlug))." Guides ({$currentYear}) | Dunes Discovery Blog"
                : ($settingsService->get('seo_blog_title') ?: $defaultTitle);
            $pageDesc = $settingsService->get('seo_blog_description') ?: $defaultDesc;
            $pageKeys = $settingsService->get('seo_blog_keywords') ?: $defaultKeys;
        } else {
            $pageTitle = $categorySlug
                ? ucwords(str_replace('-', ' ', $categorySlug))." ({$currentYear}) | ".__('ui.nav.blog')
                : ($settingsService->get("seo_blog_title_{$currentLocale}") ?: __('ui.seo.blog_title', ['year' => $currentYear]));
            $pageDesc = $settingsService->get("seo_blog_description_{$currentLocale}") ?: __('ui.seo.blog_description');
            $pageKeys = $settingsService->get("seo_blog_keywords_{$currentLocale}") ?: __('ui.seo.blog_keywords');
        }

        $ogImageSetting = $settingsService->get('seo_blog_og_image');
        $ogImage = resolve_og_image($ogImageSetting, 'images/desert-safari-dubai-dune-discovery-tourism-HD.avif');

        $isFiltered = ! empty($categorySlug) || ! empty($tagSlug) || ! empty($search) || ! empty($request->except('page'));
        $pageRobots = $isFiltered
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $canonical = (! $isFiltered && $posts->currentPage() > 1)
            ? localized_route('blog.index', ['page' => $posts->currentPage()])
            : localized_route('blog.index');

        return view('blog.index', compact('posts', 'categories', 'popularTags', 'featuredPost', 'sideFeatured', 'categorySlug', 'tagSlug', 'search', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'pageRobots'));
    }

    /**
     * Display a specific blog post.
     */
    public function show(...$params)
    {
        $slug = (string) end($params);
        $post = BlogPost::where('slug', $slug)
            ->with(['category', 'tags', 'faqs'])
            ->first();

        if (! $post) {
            abort(404);
        }

        // Increment pages viewed or count details if required, or track session
        $relatedPosts = BlogPost::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $extra = BlogPost::where('id', '!=', $post->id)
                ->where('status', 'published')
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->with('category')
                ->orderBy('published_at', 'desc')
                ->limit(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($extra);
        }

        $featImgPath = $post->featured_image_url;
        $currentLocale = app()->getLocale();

        $defaultEnCanonical = url('/blog/'.$post->slug);
        $customCanonical = (! empty($post->canonical_url) && rtrim($post->canonical_url, '/') !== rtrim($defaultEnCanonical, '/'))
            ? $post->canonical_url
            : null;

        $canonical = ($currentLocale && $currentLocale !== 'en')
            ? localized_route('blog.show', $post->slug)
            : ($customCanonical ?: localized_route('blog.show', $post->slug));

        $ogImage = $post->og_image ? resolve_og_image($post->og_image) : $featImgPath;

        $baseTitle = $post->meta_title ?: $post->title.' | Dunes Discovery';
        $baseDesc = $post->meta_desc ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 155));

        if ($currentLocale && $currentLocale !== 'en') {
            $hasLocTitle = method_exists($post, 'hasTranslation') && $post->hasTranslation('meta_title', $currentLocale);
            $hasLocDesc = method_exists($post, 'hasTranslation') && $post->hasTranslation('meta_desc', $currentLocale);
            $blogLabel = __('ui.nav.blog');
            $pageTitle = $hasLocTitle ? $baseTitle : "{$post->title} | {$blogLabel} — Dunes Discovery";
            $pageDesc = $hasLocDesc ? $baseDesc : "{$blogLabel}: {$baseDesc}";
        } else {
            $pageTitle = $baseTitle;
            $pageDesc = $baseDesc;
        }

        $pageKeys = $post->meta_keywords ?: 'dubai, desert safari, travel';
        $ogType = 'article';
        $pageRobots = (! empty(request()->query()))
            ? 'noindex, follow'
            : ($post->robots ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');

        // Intelligently resolve the most relevant tour for in-article conversion
        $matchedTour = $this->resolveMatchedTour($post);

        // Resolve top 2 alternative tour recommendations
        $recommendedTours = Tour::where('status', 'active')
            ->when($matchedTour, fn ($q) => $q->where('id', '!=', $matchedTour->id))
            ->with(['category', 'tiers'])
            ->orderBy('priority', 'asc')
            ->limit(2)
            ->get();

        // Inject the interactive tour card directly into post HTML content
        $processedContent = $this->injectTourCardIntoContent($post->content ?? '', $matchedTour, $post);

        if (! empty($processedContent)) {
            $processedContent = str_replace(
                [
                    '/luxury-vip-desert-safari-dubai',
                    '/vip-desert-safari-dubai',
                    '/cancellation-policy',
                    '/custom-safari',
                    '/pricing-guide',
                ],
                [
                    '/evening-desert-safari-dubai',
                    '/evening-desert-safari-dubai',
                    '/cancellation-refund-policy',
                    '/build-your-own-safari',
                    '/rate-card',
                ],
                $processedContent
            );
        }

        if ($currentLocale && $currentLocale !== 'en' && ! empty($processedContent)) {
            $processedContent = (string) preg_replace(
                '#href=(["\'])/(?!'.$currentLocale.'(/|$)|admin|api|ajax|images|assets|build|storage|http)#i',
                'href=$1/'.$currentLocale.'/',
                $processedContent
            );
        }

        return view('blog.show', compact('post', 'relatedPosts', 'matchedTour', 'recommendedTours', 'processedContent', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage', 'ogType', 'pageRobots'));
    }

    /**
     * Intelligently resolve the most relevant active tour for a blog post.
     */
    protected function resolveMatchedTour(BlogPost $post): ?Tour
    {
        $tourSlugs = [
            'evening-desert-safari-dubai',
            'morning-desert-safari-dubai',
            'overnight-desert-safari-dubai',
            'desert-safari-quad-biking-dubai',
            'dubai-city-tour',
            'abu-dhabi-city-tour-from-dubai',
            'dhow-cruise-catamaran-cruise-dinner-dubai',
            'dune-buggy-rental-dubai',
        ];

        $matchedSlug = null;

        // 1. Check if post content explicitly links to a core tour
        $pattern = '/href=[\'"]\/('.implode('|', $tourSlugs).')[\'"]/i';
        if (preg_match($pattern, $post->content ?? '', $matches)) {
            $matchedSlug = $matches[1];
        }

        // 2. Fallback: analyze post title, slug, and tags
        if (! $matchedSlug) {
            $text = strtolower(($post->title ?? '').' '.($post->slug ?? ''));
            if ($post->tags && $post->tags->count() > 0) {
                $text .= ' '.strtolower($post->tags->pluck('name')->implode(' '));
            }

            if (str_contains($text, 'buggy') || str_contains($text, 'can-am') || str_contains($text, 'polaris') || str_contains($text, 'rzr')) {
                $matchedSlug = 'dune-buggy-rental-dubai';
            } elseif (str_contains($text, 'quad') || str_contains($text, 'atv') || str_contains($text, 'bike') || str_contains($text, 'biking')) {
                $matchedSlug = 'desert-safari-quad-biking-dubai';
            } elseif (str_contains($text, 'morning') || str_contains($text, 'sunrise') || str_contains($text, 'breakfast')) {
                $matchedSlug = 'morning-desert-safari-dubai';
            } elseif (str_contains($text, 'overnight') || str_contains($text, 'camping') || str_contains($text, 'stargazing')) {
                $matchedSlug = 'overnight-desert-safari-dubai';
            } elseif (str_contains($text, 'abu dhabi') || str_contains($text, 'sheikh zayed') || str_contains($text, 'grand mosque') || str_contains($text, 'louvre')) {
                $matchedSlug = 'abu-dhabi-city-tour-from-dubai';
            } elseif (str_contains($text, 'cruise') || str_contains($text, 'dhow') || str_contains($text, 'catamaran') || str_contains($text, 'yacht') || str_contains($text, 'marina dinner')) {
                $matchedSlug = 'dhow-cruise-catamaran-cruise-dinner-dubai';
            } elseif (str_contains($text, 'city tour') || str_contains($text, 'burj khalifa') || str_contains($text, 'sightseeing') || str_contains($text, 'old dubai')) {
                $matchedSlug = 'dubai-city-tour';
            } else {
                $matchedSlug = 'evening-desert-safari-dubai';
            }
        }

        // Fetch tour from database
        $tour = Tour::where('slug', $matchedSlug)
            ->where('status', 'active')
            ->with(['category', 'tiers'])
            ->first();

        // Safe fallback if specific tour not found or inactive
        if (! $tour) {
            $tour = Tour::where('is_bestseller', true)
                ->where('status', 'active')
                ->with(['category', 'tiers'])
                ->first() ?? Tour::where('status', 'active')->with(['category', 'tiers'])->first();
        }

        return $tour;
    }

    /**
     * Inject the in-article interactive tour card into the blog post HTML content.
     */
    protected function injectTourCardIntoContent(string $content, ?Tour $tour, BlogPost $post): string
    {
        if (! $tour || empty($content)) {
            return $content;
        }

        $cardHtml = view('partials.in-article-tour-card', [
            'tour' => $tour,
            'post' => $post,
            'variant' => 'mid-article',
        ])->render();

        // 1. If post contains the Special VIP Recommendation blockquote, replace it directly
        if (preg_match('/<blockquote[^>]*>.*?Special VIP Recommendation.*?<\/blockquote>/si', $content)) {
            return (string) preg_replace('/<blockquote[^>]*>.*?Special VIP Recommendation.*?<\/blockquote>/si', $cardHtml, $content, 1);
        }

        // 2. If post has a table, inject right after the table / table container
        if (str_contains($content, '</table>')) {
            $tableEnd = strpos($content, '</table>') + strlen('</table>');
            $afterTable = substr($content, $tableEnd, 30);
            if (preg_match('/^\s*<\/div>/i', $afterTable, $divMatch)) {
                $pos = $tableEnd + strlen($divMatch[0]);
            } else {
                $pos = $tableEnd;
            }

            return substr_replace($content, "\n".$cardHtml."\n", $pos, 0);
        }

        // 3. Fallback: inject after the second closing paragraph </p> or first </h2>
        $firstH2 = strpos($content, '</h2>');
        if ($firstH2 !== false) {
            $pos = $firstH2 + strlen('</h2>');

            return substr_replace($content, "\n".$cardHtml."\n", $pos, 0);
        }

        return $cardHtml."\n".$content;
    }

    /**
     * Generate official RSS 2.0 / Atom syndication feed for travel publications and news readers.
     */
    public function feed()
    {
        $posts = Cache::remember('site_blog_rss_feed', 3600, function () {
            return BlogPost::where('status', 'published')
                ->with(['category', 'tags'])
                ->orderBy('published_at', 'desc')
                ->limit(30)
                ->get();
        });

        $latestPost = $posts->first();
        $lastBuildDate = $latestPost && $latestPost->published_at
            ? $latestPost->published_at->toRfc2822String()
            : now()->toRfc2822String();

        $content = view('blog.feed', [
            'posts' => $posts,
            'lastBuildDate' => $lastBuildDate,
            'channelTitle' => 'Dunes Discovery Tourism | Dubai Desert Safari & Adventure Blog',
            'channelLink' => localized_route('blog.index'),
            'channelDescription' => 'Expert insider guides, desert safari tips, dune buggy safety, and cultural Dubai travel insights by DTCM-licensed operator Dunes Discovery Tourism.',
            'channelLanguage' => app()->getLocale() ?: 'en',
            'feedUrl' => url(request()->getPathInfo() ?: '/feed.xml'),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
