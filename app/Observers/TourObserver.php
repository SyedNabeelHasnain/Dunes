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
            // Flush related caches (all sitemaps, feeds, search catalogs, header tours)
            Cache::forget('site_tours_header_cache');
            Cache::forget('feeds_tours_rss_xml');
            Cache::forget('site_home_cache');
            \App\Http\Controllers\SitemapController::flushAllSitemapCaches();
            \App\Http\Controllers\GoogleThingsToDoFeedController::flushCache();

            $activeCodes = ['en', 'ar', 'ru', 'es', 'it'];
            try {
                $dbCodes = Language::where('is_active', true)->pluck('code')->all();
                if (! empty($dbCodes)) {
                    $activeCodes = array_values(array_unique(array_merge($activeCodes, $dbCodes)));
                }
            } catch (\Throwable) {
            }

            foreach ($activeCodes as $code) {
                Cache::forget('site_active_tours_search_' . $code);
                Cache::forget('site_search_modal_catalog_v4_' . $code);
            }

            // Never submit unpublished/draft tours; during tests only submit if IndexNowService is explicitly bound/mocked
            if ($tour->status !== 'active') {
                return;
            }

            if (app()->runningUnitTests()) {
                if (! app()->bound(IndexNowService::class)) {
                    return;
                }
            } elseif (app()->runningInConsole()) {
                return;
            }

            $urls = [url('/' . $tour->slug)];
            foreach ($activeCodes as $code) {
                if ($code !== 'en') {
                    $urls[] = url('/' . $code . '/' . $tour->slug);
                }
            }

            $service = app(IndexNowService::class);
            if ($service->isEnabled()) {
                if (app()->runningUnitTests()) {
                    $service->submit($urls);
                } else {
                    // Submit asynchronously after HTTP response so admin saves never stall
                    dispatch(static function () use ($urls) {
                        try {
                            app(IndexNowService::class)->submit($urls);
                        } catch (\Throwable $ex) {
                            Log::warning('TourObserver async IndexNow failed: ' . $ex->getMessage());
                        }
                    })->afterResponse();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('TourObserver IndexNow submission notice: ' . $e->getMessage());
        }
    }
}
