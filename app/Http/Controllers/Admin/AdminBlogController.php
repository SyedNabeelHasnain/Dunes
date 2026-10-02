<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Language;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    use NormalizesLocalizedInputs;
    /**
     * Display a listing of blog posts.
     */
    public function index()
    {
        $posts = BlogPost::with('category')->orderBy('created_at', 'desc')->get();

        return view('admin.blogs.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tags = BlogTag::all();
        $languages = Language::getActive();

        return view('admin.blogs.create', compact('categories', 'tags', 'languages'));
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'excerpt' => 'required',
            'category_id' => 'required|integer',
            'status' => 'required|string|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:4096',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tags', 'featured_image']),
            ['title', 'subtitle', 'excerpt', 'content', 'meta_title', 'meta_desc', 'meta_keywords']
        );
        $baseSlug = Str::slug($this->extractSlugSource($request->title)) ?: 'article';
        $slug = $baseSlug;
        $counter = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $data['slug'] = $slug;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        if ($request->input('status') === 'published' && empty($request->input('published_at'))) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        // Sync tags
        if ($request->has('tags')) {
            $tagsInput = $request->input('tags', []);
            $tagIds = [];
            foreach ($tagsInput as $tagName) {
                $tag = BlogTag::firstOrCreate(['name' => trim($tagName), 'slug' => Str::slug(trim($tagName))]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        Cache::forget('sitemap_blogs_xml');
        Cache::forget('sitemap_images_xml');

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created successfully.');
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(string $id)
    {
        $post = BlogPost::with('tags')->findOrFail($id);
        $categories = BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tags = BlogTag::all();
        $languages = Language::getActive();

        return view('admin.blogs.edit', compact('post', 'categories', 'tags', 'languages'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'excerpt' => 'required',
            'category_id' => 'required|integer',
            'status' => 'required|string|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:5120',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->except(['tags', 'featured_image']),
            ['title', 'subtitle', 'excerpt', 'content', 'meta_title', 'meta_desc', 'meta_keywords']
        );
        if ($request->has('title')) {
            $baseSlug = Str::slug($this->extractSlugSource($request->title)) ?: 'article';
            $slug = $baseSlug;
            $counter = 1;
            while (BlogPost::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        if ($request->input('status') === 'published' && empty($post->published_at)) {
            $data['published_at'] = now();
        }

        $post->update($data);

        // Sync tags
        if ($request->has('tags')) {
            $tagsInput = $request->input('tags', []);
            $tagIds = [];
            foreach ($tagsInput as $tagName) {
                $tag = BlogTag::firstOrCreate(['name' => trim($tagName), 'slug' => Str::slug(trim($tagName))]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        Cache::forget('sitemap_blogs_xml');
        Cache::forget('sitemap_images_xml');

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        Cache::forget('sitemap_blogs_xml');
        Cache::forget('sitemap_images_xml');

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post deleted successfully.');
    }

    /**
     * Toggle published/draft status of a blog post.
     */
    public function toggleStatus(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->status = $post->status === 'published' ? 'draft' : 'published';
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
        $post->save();

        Cache::forget('sitemap_blogs_xml');
        Cache::forget('sitemap_images_xml');

        return response()->json([
            'success' => true,
            'status' => $post->status,
            'message' => 'Article status updated to '.ucfirst($post->status).'.',
        ]);
    }
}
