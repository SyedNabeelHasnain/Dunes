<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
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

        $query = BlogPost::where('status', 'published')->orderBy('published_at', 'desc');

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
        
        $featuredPost = BlogPost::where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('published_at', 'desc')
            ->first() ?: BlogPost::where('status', 'published')->orderBy('published_at', 'desc')->first();

        return view('blog.index', compact('posts', 'categories', 'popularTags', 'featuredPost', 'categorySlug', 'tagSlug', 'search'));
    }

    /**
     * Display a specific blog post.
     */
    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->with(['category', 'tags', 'faqs'])
            ->firstOrFail();

        // Increment pages viewed or count details if required, or track session
        $relatedPosts = BlogPost::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $extra = BlogPost::where('id', '!=', $post->id)
                ->where('status', 'published')
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->orderBy('published_at', 'desc')
                ->limit(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($extra);
        }

        return view('blog.show', compact('post', 'relatedPosts'));
    }
}
