<?php

namespace App\Http\Controllers;

use App\Services\ReviewMediaGalleryService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    protected ReviewMediaGalleryService $galleryService;
    protected SettingsService $settings;

    public function __construct(ReviewMediaGalleryService $galleryService, SettingsService $settings)
    {
        $this->galleryService = $galleryService;
        $this->settings = $settings;
    }

    /**
     * Display the authentic customer reviews photos & videos gallery.
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'all');
        $source = $request->query('source', 'all');
        $type = $request->query('type', 'all');

        $allVerifiedItems = $this->galleryService->getGalleryItems();
        $categoryStats = $this->galleryService->getCategoryStats($allVerifiedItems);

        $filteredItems = $this->galleryService->getGalleryItems([
            'category' => $category,
            'source' => $source,
            'type' => $type,
        ]);

        $categories = ReviewMediaGalleryService::CATEGORIES;

        // SEO Metadata
        $currentLocale = app()->getLocale();
        $canonical = ($currentLocale && $currentLocale !== 'en') ? url('/' . $currentLocale . '/gallery') : url('/gallery');
        $currentYear = date('Y');

        $pageTitle = "Customer Safari Gallery {$currentYear} | Real Traveler Photos & Videos | Dunes Discovery";
        $pageDesc = "Browse authentic, unfiltered desert safari photos and videos uploaded by verified guests on Google Maps and TripAdvisor. Experience real red dunes adventures, quad biking, and camp evenings in Dubai.";
        $pageKeys = "dubai desert safari gallery, desert safari photos, customer reviews photos dubai, real dunes discovery photos, buggy rental pictures, evening safari camp images";

        $firstItem = ! empty($filteredItems) ? $filteredItems[0] : null;
        $ogImage = $firstItem ? $firstItem['url'] : asset('images/evening-desert-safari-dubai-dune-discovery-tourism.avif');

        // Schema.org ImageGallery Graph
        $schemaImages = [];
        foreach (array_slice($filteredItems, 0, 15) as $item) {
            $schemaImages[] = [
                '@type' => 'ImageObject',
                'contentUrl' => $item['url'],
                'name' => $item['review_title'] ?: 'Dubai Desert Safari Experience',
                'caption' => $item['review_text'] ?: 'Photo by ' . $item['reviewer_name'],
                'author' => [
                    '@type' => 'Person',
                    'name' => $item['reviewer_name'],
                ],
                'datePublished' => $item['published_date'] ?: now()->toDateString(),
            ];
        }

        return view('gallery.index', compact(
            'filteredItems',
            'categories',
            'category',
            'source',
            'type',
            'categoryStats',
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'schemaImages',
            'currentLocale'
        ));
    }

    /**
     * API Endpoint: Report a broken media link to be excluded dynamically from all future views.
     */
    public function reportBroken(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');
        $this->galleryService->markUrlAsBroken($url);

        return response()->json([
            'success' => true,
            'message' => 'Media link verified and excluded from active gallery feeds.',
        ]);
    }
}
