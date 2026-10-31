<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Tour;
use App\Services\IndexNowService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TourObserver
{
    /**
     * Handle the Tour "saved" event.
     */
    public function saved(Tour $tour): void
    {
        $this->syncCachesAndIndexNow($tour);
    }

    /**
     * Handle the Tour "deleted" event.
     */
    public function deleted(Tour $tour): void
    {
        $this->syncCachesAndIndexNow($tour);
    }

    /**
     * Flush stale caches and push updated URLs to IndexNow.
     */
    protected function syncCachesAndIndexNow(Tour $tour): void
    {
        try {
            // Flush related caches
            Cache::forget('site_tours_header_cache');
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_xml');
            Cache::forget('site_llms_txt');
            Cache::forget('site_llms_full_txt');
            Cache::forget('feeds_tours_rss_xml');
            Cache::forget('site_home_cache');

            // Collect URLs for IndexNow
            $urls = [
                url('/' . $tour->slug),
                url('/tours'),
                url('/'),
            ];

            try {
                $languages = Language::where('is_active', true)->where('is_default', false)->get();
                foreach ($languages as $lang) {
                    $urls[] = url('/' . $lang->code . '/' . $tour->slug);
                    $urls[] = url('/' . $lang->code . '/tours');
                    $urls[] = url('/' . $lang->code);
                }
            } catch (\Throwable $e) {
                // Fallback standard locales
                foreach (['ar', 'ru', 'es', 'it'] as $code) {
                    $urls[] = url('/' . $code . '/' . $tour->slug);
                }
            }

            // Submit to IndexNow safely
            if (app()->bound(IndexNowService::class)) {
                $service = app(IndexNowService::class);
                if ($service->isEnabled()) {
                    $service->submit($urls);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('TourObserver IndexNow submission notice: ' . $e->getMessage());
        }
    }
}
