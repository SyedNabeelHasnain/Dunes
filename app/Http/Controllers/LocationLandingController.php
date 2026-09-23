<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use App\Services\LocationLandingService;

class LocationLandingController extends Controller
{
    /**
     * Display a programmatic geo-location pickup landing page.
     */
    public function show(string $location)
    {
        $locationData = LocationLandingService::find($location);

        if (! $locationData) {
            abort(404);
        }

        try {
            $safariTours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('slug', 'like', '%safari%')
                        ->orWhere('slug', 'like', '%buggy%')
                        ->orWhere('name', 'like', '%Safari%')
                        ->orWhere('name', 'like', '%Desert%');
                })
                ->with(['tiers', 'category'])
                ->orderBy('is_bestseller', 'desc')
                ->orderBy('priority', 'asc')
                ->get();

            if ($safariTours->isEmpty()) {
                $safariTours = Tour::where('status', 'active')
                    ->with(['tiers', 'category'])
                    ->orderBy('priority', 'asc')
                    ->take(6)
                    ->get();
            }
        } catch (\Throwable $e) {
            $safariTours = collect();
        }

        try {
            $reviews = Review::where('status', 'approved')
                ->orderBy('rating', 'desc')
                ->orderBy('published_date', 'desc')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            $reviews = collect();
        }

        $allLocations = LocationLandingService::getLocations();

        $pageTitle = $locationData['meta_title'];
        $pageDesc = $locationData['meta_desc'];
        $canonical = url('/'.$locationData['slug']);
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('pages.location-tour', compact(
            'locationData',
            'safariTours',
            'reviews',
            'allLocations',
            'pageTitle',
            'pageDesc',
            'canonical',
            'ogImage'
        ));
    }
}
