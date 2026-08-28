<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of reviews with Analytics & Filters.
     */
    public function index(Request $request)
    {
        $rating = $request->input('rating');
        $status = $request->input('status');
        $source = $request->input('source');
        $search = $request->input('search');

        $query = Review::query();

        if ($rating) {
            $query->where('rating', (float)$rating);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($source) {
            $query->where('source', $source);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reviewer_name', 'like', "%{$search}%")
                  ->orWhere('review_title', 'like', "%{$search}%")
                  ->orWhere('review_text', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('published_date', 'desc')->get();

        // 1. Review Key Performance Statistics
        $totalReviews = Review::count();
        $avgRating = $totalReviews > 0 ? round((float)Review::avg('rating'), 1) : 5.0;
        $approvedCount = Review::where('status', 'approved')->count();
        $pendingCount = Review::where('status', 'pending')->count();
        $fiveStarCount = Review::where('rating', '>=', 5)->count();
        $fiveStarPct = $totalReviews > 0 ? round(($fiveStarCount / $totalReviews) * 100) : 100;

        $stats = [
            'total' => $totalReviews,
            'avg_rating' => $avgRating,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'five_star_pct' => $fiveStarPct,
        ];

        // 2. Star Rating Distribution
        $ratingDistribution = [
            5 => Review::where('rating', '>=', 4.5)->count(),
            4 => Review::whereBetween('rating', [3.5, 4.49])->count(),
            3 => Review::whereBetween('rating', [2.5, 3.49])->count(),
            2 => Review::whereBetween('rating', [1.5, 2.49])->count(),
            1 => Review::where('rating', '<', 1.5)->count(),
        ];

        // 3. Review Source Breakdown
        $sourceBreakdown = Review::select('source', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        return view('admin.reviews.index', compact(
            'reviews',
            'stats',
            'ratingDistribution',
            'sourceBreakdown',
            'rating',
            'status',
            'source',
            'search'
        ));
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

    /**
     * Toggle status of a review (approved <-> pending).
     */
    public function toggleStatus(string $id)
    {
        $review = Review::findOrFail($id);
        $review->status = $review->status === 'approved' ? 'pending' : 'approved';
        $review->save();

        return response()->json([
            'success' => true,
            'status' => $review->status,
            'message' => 'Review status updated to ' . ucfirst($review->status) . '.'
        ]);
    }
}
