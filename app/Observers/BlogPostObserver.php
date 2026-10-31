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
            Cache::forget('sitemap_blogs_xml');
            Cache::forget('sitemap_xml');
            Cache::forget('site_llms_txt');
            Cache::forget('site_llms_full_txt');

            if ($post->status !== 'published') {
                return;
            }

            $urls = [
                url('/blog/' . $post->slug),
                url('/blog'),
            ];

            try {
                $languages = Language::where('is_active', true)->where('is_default', false)->get();
                foreach ($languages as $lang) {
                    $urls[] = url('/' . $lang->code . '/blog/' . $post->slug);
                    $urls[] = url('/' . $lang->code . '/blog');
                }
            } catch (\Throwable $e) {
                foreach (['ar', 'ru', 'es', 'it'] as $code) {
                    $urls[] = url('/' . $code . '/blog/' . $post->slug);
                }
            }

            if (app()->bound(IndexNowService::class)) {
                $service = app(IndexNowService::class);
                if ($service->isEnabled()) {
                    $service->submit($urls);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('BlogPostObserver IndexNow submission notice: ' . $e->getMessage());
        }
    }
}
