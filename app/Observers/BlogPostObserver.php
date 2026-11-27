<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Models\Language;
use App\Services\IndexNowService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlogPostObserver
{
    /**
     * Handle the BlogPost "saved" event.
     */
    public function saved(BlogPost $post): void
    {
        $this->syncCachesAndIndexNow($post);
    }

    /**
     * Handle the BlogPost "deleted" event.
     */
    public function deleted(BlogPost $post): void
    {
        $this->syncCachesAndIndexNow($post);
    }

    /**
     * Flush stale caches and push updated URLs to IndexNow.
     */
    protected function syncCachesAndIndexNow(BlogPost $post): void
    {
        try {
            \App\Http\Controllers\SitemapController::flushAllSitemapCaches();
            Cache::forget('blog_sidebar_categories_counts');

            if ($post->status !== 'published') {
                return;
            }

            if (app()->runningUnitTests()) {
                if (! app()->bound(IndexNowService::class)) {
                    return;
                }
            } elseif (app()->runningInConsole()) {
                return;
            }

            $urls = [url('/blog/' . $post->slug)];
            $activeCodes = ['ar', 'ru', 'es', 'it'];
            try {
                $dbCodes = Language::where('is_active', true)->where('is_default', false)->pluck('code')->all();
                if (! empty($dbCodes)) {
                    $activeCodes = $dbCodes;
                }
            } catch (\Throwable) {
            }
            foreach ($activeCodes as $code) {
                $urls[] = url('/' . $code . '/blog/' . $post->slug);
            }

            $service = app(IndexNowService::class);
            if ($service->isEnabled()) {
                if (app()->runningUnitTests()) {
                    $service->submit($urls);
                } else {
                    dispatch(static function () use ($urls) {
                        try {
                            app(IndexNowService::class)->submit($urls);
                        } catch (\Throwable $ex) {
                            Log::warning('BlogPostObserver async IndexNow failed: ' . $ex->getMessage());
                        }
                    })->afterResponse();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('BlogPostObserver IndexNow submission notice: ' . $e->getMessage());
        }
    }
}
