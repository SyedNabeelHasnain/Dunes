<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    /**
     * Display a listing of blog posts.
     */
    public function index()
    {
        $posts = BlogPost::with('category')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.blogs.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = BlogCategory::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tags = BlogTag::all();
        return view('admin.blogs.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'required|string|max:500',
            'category_id' => 'required|integer',
            'status' => 'required|string|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:4096',
        ]);

        $data = $request->except(['tags', 'featured_image']);
        $data['slug'] = Str::slug($request->title);

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
        return view('admin.blogs.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'required|string|max:500',
            'category_id' => 'required|integer',
            'status' => 'required|string|in:draft,published,scheduled',
        ]);

        $data = $request->except(['tags', 'featured_image']);
        $data['slug'] = Str::slug($request->title);

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

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog post deleted successfully.');
    }
}
