<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogCategoryController extends Controller
{
    /**
     * Display a listing of categories and blog tags.
     */
    public function index()
    {
        $categories = BlogCategory::orderBy('priority', 'asc')->get();
        $tags = BlogTag::withCount('posts')->orderBy('name', 'asc')->get();

        return view('admin.blog-categories.index', compact('categories', 'tags'));
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category-'.time();
        $candidate = $base;
        $counter = 2;
        while (BlogCategory::where('slug', $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $base.'-'.$counter++;
        }

        return $candidate;
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        BlogCategory::create([
            'name' => $request->name,
            'slug' => $this->uniqueSlug((string) $request->name),
            'priority' => $request->priority,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, string $id)
    {
        $category = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,'.$id,
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $this->uniqueSlug((string) $request->name, $category->id),
            'priority' => $request->priority,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(string $id)
    {
        $category = BlogCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Create a new blog tag.
     */
    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120|unique:blog_tags,name',
        ]);

        $name = trim((string) $request->input('name'));
        $base = Str::slug($name) ?: 'tag-'.time();
        $slug = $base;
        $i = 2;
        while (BlogTag::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        BlogTag::create(['name' => $name, 'slug' => $slug]);

        return redirect()->route('admin.blog-categories.index')->with('success', "Blog tag '{$name}' created successfully.");
    }

    /**
     * Update an existing blog tag.
     */
    public function updateTag(Request $request, string $id)
    {
        $tag = BlogTag::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:120|unique:blog_tags,name,'.$tag->id,
        ]);

        $name = trim((string) $request->input('name'));
        $base = Str::slug($name) ?: 'tag-'.time();
        $slug = $base;
        $i = 2;
        while (BlogTag::where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $tag->update(['name' => $name, 'slug' => $slug]);

        return redirect()->route('admin.blog-categories.index')->with('success', "Blog tag '{$name}' updated successfully.");
    }

    /**
     * Delete a blog tag and detach from posts.
     */
    public function destroyTag(string $id)
    {
        $tag = BlogTag::findOrFail($id);
        $name = $tag->name;
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', "Blog tag '{$name}' deleted successfully.");
    }
}
