<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\SettingsService;
use Illuminate\Http\Request;

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
        $currentYear = date('Y');
        $defaultTitle = "Dubai Desert Safari & Travel Blog ({$currentYear}) | Expert Insights | Dunes Discovery";
        $defaultDesc = 'Read insider travel tips, desert safari packing guides, buggy safety advice, and Dubai itinerary recommendations by Dunes Discovery Tourism.';
        $defaultKeys = 'dubai travel blog, desert safari guide, dubai desert tips, travel advice dubai';

        $pageTitle = $categorySlug
            ? ucwords(str_replace('-', ' ', $categorySlug))." Guides ({$currentYear}) | Dunes Discovery Blog"
            : ($settingsService->get('seo_blog_title') ?: $defaultTitle);
        $pageDesc = $settingsService->get('seo_blog_description') ?: $defaultDesc;
        $pageKeys = $settingsService->get('seo_blog_keywords') ?: $defaultKeys;
        $ogImageSetting = $settingsService->get('seo_blog_og_image');
        $ogImage = $ogImageSetting ? asset(ltrim($ogImageSetting, '/')) : asset('images/desert-safari-poster.avif');
        $canonical = $categorySlug ? route('blog.index', ['category' => $categorySlug]) : route('blog.index');

        return view('blog.index', compact('posts', 'categories', 'popularTags', 'featuredPost', 'sideFeatured', 'categorySlug', 'tagSlug', 'search', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display a specific blog post.
     */
    public function show(string $slug)
    {
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

        return view('blog.show', compact('post', 'relatedPosts'));
    }
}
