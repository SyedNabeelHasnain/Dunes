<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index()
    {
        $reviews = Review::orderBy('published_date', 'desc')->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Store a new manual/imported review.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'review_text' => 'required|string',
            'status' => 'required|string|in:approved,pending,rejected',
        ]);

        Review::create([
            'reviewer_name' => $request->reviewer_name,
            'rating' => (float)$request->rating,
            'review_text' => $request->review_text,
            'review_title' => $request->review_title,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'source' => $request->input('source', 'manual'),
            'published_date' => $request->input('published_date') ?: now()->toDateString(),
            'imported_at' => now(),
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review added successfully.');
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'review_text' => 'required|string',
            'status' => 'required|string|in:approved,pending,rejected',
        ]);

        $review->update([
            'reviewer_name' => $request->reviewer_name,
            'rating' => (float)$request->rating,
            'review_text' => $request->review_text,
            'review_title' => $request->review_title,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'published_date' => $request->input('published_date') ?: $review->published_date,
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified review.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}
