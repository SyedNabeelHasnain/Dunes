<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = BlogCategory::orderBy('priority', 'asc')->get();
        return view('admin.blog-categories.index', compact('categories'));
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
            'slug' => Str::slug($request->name),
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
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $id,
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
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
}
