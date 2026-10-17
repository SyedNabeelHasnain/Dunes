<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'short_desc',
        'full_desc',
        'meta_title',
        'meta_desc',
        'meta_keywords',
    ];

    protected $fillable = [
        'slug', 'name', 'category_id', 'short_desc', 'full_desc', 'duration',
        'pickup_time', 'dropoff_time', 'min_age', 'group_size', 'languages',
        'hero_image', 'thumb_image', 'og_image', 'video_url', 'gallery_images', 'rating',
        'review_count', 'is_bestseller', 'is_featured', 'status', 'priority',
        'meta_title', 'meta_desc', 'meta_keywords',
    ];

    protected $casts = [
        'is_bestseller' => 'boolean',
        'is_featured' => 'boolean',
        'gallery_images' => 'array',
        'rating' => 'float',
        'review_count' => 'integer',
        'min_age' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get all resolved image URLs for this tour (fixed image first, followed by related/gallery images).
     */
    public function getImageList(): array
    {
        $urls = [];

        // 1. Fixed primary image (thumbnail, or hero image)
        $primary = $this->thumb_image ?: $this->hero_image;
        if ($primary) {
            $urls[] = resolve_tour_image($primary);
        }

        // 2. Hero image if distinct from thumbnail
        if ($this->hero_image && $this->hero_image !== $primary) {
            $urls[] = resolve_tour_image($this->hero_image);
        }

        // 3. User-uploaded or admin-specified gallery images
        if (! empty($this->gallery_images) && is_array($this->gallery_images)) {
            foreach ($this->gallery_images as $gImg) {
                if (! empty($gImg)) {
                    $urls[] = resolve_tour_image($gImg);
                }
            }
        }

        // 4. Curated related images catalog from public/images
        $related = static::getDefaultGalleryForSlug($this->slug, $this->category?->slug);
        foreach ($related as $relFile) {
            $urls[] = resolve_tour_image($relFile);
        }

        // Default fallback if still empty
        if (empty($urls)) {
            $urls[] = resolve_tour_image('desert-safari-poster.avif');
        }

        return array_values(array_unique($urls));
    }

    /**
     * Accessor for $tour->image_urls
     */
    public function getImageUrlsAttribute(): array
    {
        return $this->getImageList();
    }

    /**
     * Map each tour slug / category to its curated collection of high-resolution related images.
     */
    public static function getDefaultGalleryForSlug(?string $slug, ?string $categorySlug = null): array
    {
        $catalog = [
            'evening-desert-safari-dubai' => [
                'evening-desert-safari-dubai-4x4-land-cruiser-lahbab-dunes.webp',
                'evening-desert-safari-dubai-dune-bashing-suv-climb.webp',
                'evening-desert-safari-dubai-hero.avif',
                'evening-desert-safari-dubai-hut-camp-entrance-dune-discovery-tourism.avif',
                'dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                'Evening-desert-safari-night-lights-desert-dune-discovery-tourism.avif',
                'dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
            ],
            'morning-desert-safari-dubai' => [
                'morning-desert-safari-dubai-golden-desert-landscape.webp',
                'morning-desert-safari-dubai-land-cruiser-convoy-ridge.webp',
                'morning-desert-safari-dubai-sunrise-over-red-dunes.webp',
                'morning-desert-safari-dubai-hero.avif',
                'desert-safari-dubai-jeep-in-desert.avif',
                'desert-safari-dune-bashing.avif',
            ],
            'overnight-desert-safari-dubai' => [
                'overnight-desert-safari-dubai-stargazing-milky-way-dunes.webp',
                'evening-desert-safari-dubai-hut-camp-entrance-dune-discovery-tourism.avif',
                'Evening-desert-safari-night-lights-desert-dune-discovery-tourism.avif',
                'morning-desert-safari-dubai-sunrise-over-red-dunes.webp',
                'dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                'overnight-desert-safari-dubai-hero.avif',
            ],
            'desert-safari-quad-biking-dubai' => [
                'desert-safari-quad-biking-dubai-atv-rider-safety-flag.webp',
                'desert-safari-quad-biking-dubai-riders-golden-dunes.webp',
                'dubai-desert-safari-buggy-dune-discovery-tourism.avif',
                'desert-dubai-bashing-dune-drive-dune-discovery-tourism.avif',
                'desert-safari-quad-biking-hero.avif',
            ],
            'dubai-city-tour' => [
                'dubai-city-tour-burj-khalifa-downtown-skyline-panorama.webp',
                'dubai-city-tour-burj-khalifa-twilight-architectural-portrait.webp',
                'dubai-city-tour-burj-al-arab-aerial-palm-jumeirah.webp',
                'dubai-city-tour-burj-al-arab-madinat-jumeirah-palms.webp',
                'dubai-city-tour-atlantis-the-palm-resort-waters.webp',
                'dubai-city-tour-dubai-creek-abra-boat-al-seef-wind-towers.webp',
                'dubai-city-tour-hero.avif',
            ],
            'abu-dhabi-city-tour-from-dubai' => [
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-marble-courtyard.webp',
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-facade-kandura.webp',
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-arched-colonnade.webp',
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-chandelier-interior.webp',
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-twilight-reflection.webp',
                'abu-dhabi-city-tour-louvre-abu-dhabi-floating-dome.webp',
                'abu-dhabi-city-tour-hero.avif',
            ],
            'dhow-cruise-catamaran-cruise-dinner-dubai' => [
                'dhow-cruise-catamaran-dubai-marina-illuminated-night-skyline.webp',
                'dhow-cruise-catamaran-dubai-marina-traditional-wooden-boat.webp',
                'dubai-marina-dinner-dhow-cruise-dune-discovery-tourism.avif',
                'dhow-cruise-dinner-dubai-dune-disovery-tourism.avif',
                'dubai-marina-dhow-cruise-hero.avif',
                'ocean-empress-dhow-cruise-hero.avif',
            ],
            'dune-buggy-rental-dubai' => [
                'dune-buggy-rental-dubai-can-am-maverick-x3-desert.webp',
                'dune-buggy-rental-dubai-polaris-rzr-convoy-red-dunes.webp',
                'dubai-desert-safari-buggy-dune-discovery-tourism.avif',
                'desert-safari-quad-biking-dubai-riders-golden-dunes.webp',
                'desert-safari-quad-biking-hero.avif',
            ],
        ];

        if ($slug && isset($catalog[$slug])) {
            return $catalog[$slug];
        }

        if ($categorySlug === 'desert-safari') {
            return [
                'evening-desert-safari-dubai-4x4-land-cruiser-lahbab-dunes.webp',
                'morning-desert-safari-dubai-golden-desert-landscape.webp',
                'dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
                'dubai-desert-safari-bbq-dune-discovery-tourism.avif',
            ];
        }

        if ($categorySlug === 'city-tours') {
            return [
                'dubai-city-tour-burj-khalifa-downtown-skyline-panorama.webp',
                'dubai-city-tour-atlantis-the-palm-resort-waters.webp',
                'abu-dhabi-city-tour-sheikh-zayed-grand-mosque-marble-courtyard.webp',
            ];
        }

        if ($categorySlug === 'cruises') {
            return [
                'dhow-cruise-catamaran-dubai-marina-illuminated-night-skyline.webp',
                'dhow-cruise-dinner-dubai-dune-disovery-tourism.avif',
            ];
        }

        return [
            'evening-desert-safari-dubai-4x4-land-cruiser-lahbab-dunes.webp',
            'evening-desert-safari-dubai-hero.avif',
            'dubai-desert-safari-bbq-dune-discovery-tourism.avif',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class)->orderBy('priority', 'asc');
    }

    public function tiers()
    {
        return $this->belongsToMany(Tier::class, 'tour_tiers')
            ->withPivot('price', 'old_price', 'price_type', 'guest_count')
            ->orderBy('tiers.priority', 'asc');
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'tour_addons')
            ->withPivot('price')
            ->orderBy('addons.priority', 'asc');
    }

    public function contentItems()
    {
        return $this->belongsToMany(ContentItem::class, 'tour_content', 'tour_id', 'content_id')
            ->withPivot('tier_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
