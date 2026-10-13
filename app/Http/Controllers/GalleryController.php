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
        $canonical = localized_route('gallery.index');
        $currentYear = date('Y');

        $localizedGalleryMeta = [
            'ar' => [
                'title' => "معرض صور وفيديوهات سفاري صحراء دبي ({$currentYear}) | صور ضيوف حقيقية | Dunes Discovery",
                'desc' => 'تصفح صوراً وفيديوهات حقيقية غير مفلترة لرحلات سفاري صحراء دبي بعدسة ضيوفنا الموثقين على خرائط جوجل وتريب أدفايزر في كثبان لهباب الحمراء.',
            ],
            'ru' => [
                'title' => "Фотогалерея пустынного сафари в Дубае ({$currentYear}) | Реальные фото и видео гостей | Dunes Discovery",
                'desc' => 'Смотрите реальные фотографии и видео сафари в пустыне Дубая от проверенных гостей из Google Maps и TripAdvisor: красные дюны Лахбаб, багги и бедуинский лагерь.',
            ],
            'es' => [
                'title' => "Galería de Fotos y Videos del Safari en Dubái ({$currentYear}) | Clientes Reales | Dunes Discovery",
                'desc' => 'Explora fotos y videos auténticos sin filtros del safari por el desierto de Dubái subidos por viajeros verificados en Google Maps y TripAdvisor.',
            ],
            'it' => [
                'title' => "Galleria Foto e Video Safari nel Deserto di Dubai ({$currentYear}) | Ospiti Reali | Dunes Discovery",
                'desc' => 'Sfoglia foto e video autentici del safari nel deserto di Dubai caricati da ospiti verificati su Google Maps e TripAdvisor tra le dune rosse di Lahbab.',
            ],
        ];

        $pageTitle = $localizedGalleryMeta[$currentLocale]['title'] ?? "Customer Safari Gallery {$currentYear} | Real Traveler Photos & Videos | Dunes Discovery";
        $pageDesc = $localizedGalleryMeta[$currentLocale]['desc'] ?? 'Browse authentic, unfiltered desert safari photos and videos uploaded by verified guests on Google Maps and TripAdvisor. Experience real red dunes adventures, quad biking, and camp evenings in Dubai.';
        $pageKeys = 'dubai desert safari gallery, desert safari photos, customer reviews photos dubai, real dunes discovery photos, buggy rental pictures, evening safari camp images';

        $pageRobots = (! empty($request->query()))
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $firstItem = ! empty($filteredItems) ? $filteredItems[0] : null;
        $ogImage = $firstItem ? $firstItem['url'] : asset('images/evening-desert-safari-dubai-dune-discovery-tourism.avif');

        // Schema.org ImageGallery Graph
        $schemaImages = [];
        $photoItems = collect($filteredItems)->filter(fn ($i) => ($i['type'] ?? 'image') !== 'video')->take(34);
        foreach ($photoItems as $item) {
            $imageObj = [
                '@type' => 'ImageObject',
                'contentUrl' => $item['url'],
                'name' => $item['review_title'] ?: 'Dubai Desert Safari Experience',
                'caption' => $item['caption'] ?? ($item['review_text'] ?: 'Photo by '.$item['reviewer_name']),
                'author' => [
                    '@type' => ($item['source'] ?? '') === 'ddt' ? 'Organization' : 'Person',
                    'name' => $item['reviewer_name'],
                ],
                'datePublished' => $item['published_date'] ?: now()->toDateString(),
            ];

            if (! empty($item['description'])) {
                $imageObj['description'] = $item['description'];
            }
            if (! empty($item['location'])) {
                $imageObj['contentLocation'] = [
                    '@type' => 'Place',
                    'name' => $item['location'],
                ];
            }
            if (! empty($item['keywords']) && is_array($item['keywords'])) {
                $imageObj['keywords'] = implode(', ', $item['keywords']);
            }

            $schemaImages[] = $imageObj;
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
            'pageRobots',
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
