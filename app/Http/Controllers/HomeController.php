<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the website front home page.
     */
    public function index()
    {
        $categories = Category::with(['tours' => function ($query) {
            $query->where('status', 'active')->orderBy('priority', 'asc');
        }])->orderBy('priority', 'asc')->get();

        $bestsellers = Tour::where('status', 'active')
            ->where('is_bestseller', true)
            ->orderBy('priority', 'asc')
            ->get();

        $reviews = Review::where('status', 'approved')
            ->where('is_featured', true)
            ->orderBy('published_date', 'desc')
            ->limit(10)
            ->get();

        return view('index', compact('categories', 'bestsellers', 'reviews'));
    }
}
