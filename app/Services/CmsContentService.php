<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CmsContentService
{
    /**
     * Retrieve all active pages with their sections (cached as plain arrays for serializable_classes=false safety).
     */
    public function getPage(string $slug): ?Page
    {
        try {
            $payload = Cache::remember('cms_pages_cache', 3600, function () {
                return Page::with(['sections' => function ($q) {
                    $q->orderBy('order', 'asc');
                }])->where('status', 'published')->get()->map(function (Page $page) {
                    return [
                        'attributes' => $page->getAttributes(),
                        'sections' => $page->sections->map(fn (PageSection $s) => $s->getAttributes())->all(),
                    ];
                })->all();
            });

            if (! is_array($payload)) {
                Cache::forget('cms_pages_cache');

                return Page::with(['sections' => fn ($q) => $q->orderBy('order', 'asc')])
                    ->where('status', 'published')
                    ->where('slug', $slug)
                    ->first();
            }

            foreach ($payload as $entry) {
                if (! is_array($entry) || ! isset($entry['attributes']) || ($entry['attributes']['slug'] ?? null) !== $slug) {
                    continue;
                }

                $page = (new Page)->newFromBuilder($entry['attributes']);
                $sections = collect($entry['sections'] ?? [])->map(fn (array $s) => (new PageSection)->newFromBuilder($s));
                $page->setRelation('sections', $sections);

                return $page;
            }

            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CmsContentService::getPage failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Check if a section is visible (active) or default true if not set.
     */
    public function isSectionVisible(string $pageSlug, string $sectionKey): bool
    {
        $section = $this->getSection($pageSlug, $sectionKey);
        if ($section) {
            return (bool) $section->is_active;
        }

        return true;
    }

    /**
     * Get a specific page section by page slug and section key.
     */
    public function getSection(string $pageSlug, string $sectionKey): ?PageSection
    {
        $page = $this->getPage($pageSlug);
        if (! $page) {
            return null;
        }

        return $page->sections->firstWhere('section_key', $sectionKey);
    }

    /**
     * Get translated title of a section or fallback.
     */
    public function getSectionTitle(string $pageSlug, string $sectionKey, ?string $default = null): string
    {
        $section = $this->getSection($pageSlug, $sectionKey);
        if ($section && ! empty($section->title)) {
            return $section->title;
        }

        return $default ?? '';
    }

    /**
     * Get translated subtitle of a section or fallback.
     */
    public function getSectionSubtitle(string $pageSlug, string $sectionKey, ?string $default = null): string
    {
        $section = $this->getSection($pageSlug, $sectionKey);
        if ($section && ! empty($section->subtitle)) {
            return $section->subtitle;
        }

        return $default ?? '';
    }

    /**
     * Get translated body / tag badge of a section or fallback.
     */
    public function getSectionBody(string $pageSlug, string $sectionKey, ?string $default = null): string
    {
        $section = $this->getSection($pageSlug, $sectionKey);
        if ($section && ! empty($section->body)) {
            return $section->body;
        }

        return $default ?? '';
    }

    /**
     * Retrieve active menu items for a specific location (cached as plain arrays for serializable_classes=false safety).
     */
    public function getMenuItems(string $location): Collection
    {
        try {
            $payload = Cache::remember("cms_menu_{$location}_cache", 3600, function () use ($location) {
                return MenuItem::where('location', $location)
                    ->where('is_active', true)
                    ->orderBy('order', 'asc')
                    ->get()
                    ->map(fn (MenuItem $item) => $item->getAttributes())
                    ->all();
            });

            if (! is_array($payload)) {
                Cache::forget("cms_menu_{$location}_cache");

                return MenuItem::where('location', $location)
                    ->where('is_active', true)
                    ->orderBy('order', 'asc')
                    ->get();
            }

            return collect($payload)
                ->filter(fn ($attrs) => is_array($attrs))
                ->map(fn (array $attrs) => (new MenuItem)->newFromBuilder($attrs))
                ->values();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("CmsContentService::getMenuItems({$location}) failed: ".$e->getMessage());

            return collect();
        }
    }

    /**
     * Purge all CMS content caches.
     */
    public function clearCache(): void
    {
        try {
            Cache::forget('cms_pages_cache');
            $locations = [
                'header',
                'footer_desert_safaris',
                'footer_tours_cruises',
                'footer_trust_policies',
                'footer_contact_help',
            ];
            try {
                $dbLocations = MenuItem::query()->distinct()->pluck('location')->filter()->all();
                $locations = array_values(array_unique(array_merge($locations, $dbLocations)));
            } catch (\Throwable $e) {
                // Keep static defaults
            }
            foreach ($locations as $loc) {
                Cache::forget("cms_menu_{$loc}_cache");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CmsContentService::clearCache failed: '.$e->getMessage());
        }
    }
}
