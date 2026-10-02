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
     * Retrieve all active pages with their sections (cached).
     */
    public function getPage(string $slug): ?Page
    {
        try {
            $pages = Cache::remember('cms_pages_cache', 3600, function () {
                return Page::with(['sections' => function ($q) {
                    $q->where('is_active', true)->orderBy('order', 'asc');
                }])->where('status', 'published')->get();
            });

            return $pages->firstWhere('slug', $slug);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get a specific page section by page slug and section key.
     */
    public function getSection(string $pageSlug, string $sectionKey): ?PageSection
    {
        $page = $this->getPage($pageSlug);
        if (!$page) {
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
        if ($section && !empty($section->title)) {
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
        if ($section && !empty($section->subtitle)) {
            return $section->subtitle;
        }

        return $default ?? '';
    }

    /**
     * Retrieve active menu items for a specific location.
     */
    public function getMenuItems(string $location): Collection
    {
        try {
            return Cache::remember("cms_menu_{$location}_cache", 3600, function () use ($location) {
                return MenuItem::where('location', $location)
                    ->where('is_active', true)
                    ->orderBy('order', 'asc')
                    ->get();
            });
        } catch (\Throwable $e) {
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
            Cache::forget('cms_menu_header_cache');
            Cache::forget('cms_menu_footer_desert_safaris_cache');
            Cache::forget('cms_menu_footer_tours_cruises_cache');
            Cache::forget('cms_menu_footer_trust_policies_cache');
            Cache::forget('cms_menu_footer_contact_help_cache');
        } catch (\Throwable $e) {
        }
    }
}
