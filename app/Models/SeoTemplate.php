<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class SeoTemplate extends Model
{
    protected $table = 'seo_templates';

    protected $fillable = [
        'content_type',
        'label',
        'title_pattern',
        'description_pattern',
        'keywords_pattern',
        'robots_default',
        'og_type_default',
        'schema_type_default',
        'sitemap_enabled',
        'sitemap_priority',
        'sitemap_changefreq',
        'include_brand_suffix',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'sitemap_enabled' => 'boolean',
        'sitemap_priority' => 'float',
        'include_brand_suffix' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Built-in baseline defaults for every content type.
     */
    public static function defaultDefinitions(): array
    {
        $robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return [
            'home' => [
                'label' => 'Homepage',
                'title_pattern' => '{title}',
                'description_pattern' => '{description}',
                'keywords_pattern' => 'dubai desert safari, evening desert safari dubai, red dunes desert safari',
                'robots_default' => $robots,
                'og_type_default' => 'website',
                'schema_type_default' => 'TravelAgency',
                'sitemap_enabled' => true,
                'sitemap_priority' => 1.00,
                'sitemap_changefreq' => 'daily',
                'include_brand_suffix' => false,
            ],
            'tour' => [
                'label' => 'Safari & Tour Packages',
                'title_pattern' => '{title} | {site_name} ({year})',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => '{focus_keyword}, dubai desert safari, {category}',
                'robots_default' => $robots,
                'og_type_default' => 'product',
                'schema_type_default' => 'TouristTrip',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.90,
                'sitemap_changefreq' => 'weekly',
                'include_brand_suffix' => true,
            ],
            'blog_post' => [
                'label' => 'Blog & Travel Guides',
                'title_pattern' => '{title} | {site_name} Journal',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => '{focus_keyword}, {category}, dubai travel guide',
                'robots_default' => $robots,
                'og_type_default' => 'article',
                'schema_type_default' => 'BlogPosting',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.80,
                'sitemap_changefreq' => 'weekly',
                'include_brand_suffix' => true,
            ],
            'wiki_article' => [
                'label' => 'Desert Knowledge Wiki Articles',
                'title_pattern' => '{title} — Dubai Desert Wiki | {site_name}',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => '{focus_keyword}, {category}, dubai desert facts',
                'robots_default' => $robots,
                'og_type_default' => 'article',
                'schema_type_default' => 'Article',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.75,
                'sitemap_changefreq' => 'monthly',
                'include_brand_suffix' => true,
            ],
            'wiki_pillar' => [
                'label' => 'Wiki Pillar Hubs',
                'title_pattern' => '{title} — Dubai Desert Wiki | {site_name}',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => '{title}, dubai desert safari guide',
                'robots_default' => $robots,
                'og_type_default' => 'website',
                'schema_type_default' => 'CollectionPage',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.75,
                'sitemap_changefreq' => 'monthly',
                'include_brand_suffix' => true,
            ],
            'page' => [
                'label' => 'CMS Static & Landing Pages',
                'title_pattern' => '{title} | {site_name}',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => 'dunes discovery tourism, dubai desert safari',
                'robots_default' => $robots,
                'og_type_default' => 'website',
                'schema_type_default' => 'WebPage',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.70,
                'sitemap_changefreq' => 'monthly',
                'include_brand_suffix' => true,
            ],
            'legal_page' => [
                'label' => 'Legal & Compliance Policies',
                'title_pattern' => '{title} | {site_name}',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => 'dunes discovery policy, dubai tour operator terms',
                'robots_default' => 'index, follow',
                'og_type_default' => 'website',
                'schema_type_default' => 'WebPage',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.40,
                'sitemap_changefreq' => 'yearly',
                'include_brand_suffix' => true,
            ],
            'location_landing' => [
                'label' => 'Location Pickup Landing Pages',
                'title_pattern' => '{title} | {site_name}',
                'description_pattern' => '{excerpt}',
                'keywords_pattern' => 'desert safari pickup, dubai hotel pickup safari',
                'robots_default' => $robots,
                'og_type_default' => 'website',
                'schema_type_default' => 'TouristAttraction',
                'sitemap_enabled' => true,
                'sitemap_priority' => 0.80,
                'sitemap_changefreq' => 'monthly',
                'include_brand_suffix' => true,
            ],
        ];
    }

    public static function ensureDefaultsExist(): void
    {
        if (! Schema::hasTable('seo_templates')) {
            return;
        }

        $defaults = self::defaultDefinitions();
        if (self::query()->count() >= count($defaults)) {
            return;
        }

        foreach ($defaults as $contentType => $attrs) {
            try {
                self::firstOrCreate(
                    ['content_type' => $contentType],
                    array_merge($attrs, ['is_active' => true])
                );
            } catch (QueryException) {
                // Safe under concurrent requests attempting first-time default seeding
            }
        }
    }
}
