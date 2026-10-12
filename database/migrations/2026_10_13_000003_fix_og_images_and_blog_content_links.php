<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize SEO OG image paths in settings and blog_posts so no page or crawler
     * encounters relative /blog/images/... or legacy /images/og-*.jpg 404 paths,
     * and normalize any legacy internal links inside blog_posts content.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'setting_key') && Schema::hasColumn('settings', 'setting_value')) {
            $ogSettingReplacements = [
                'seo_default_og_image' => [
                    'old' => ['/images/og-default.jpg', 'images/og-default.jpg'],
                    'new' => '/images/desert-safari-poster.avif',
                ],
                'seo_home_og_image' => [
                    'old' => ['/images/og-home.jpg', 'images/og-home.jpg'],
                    'new' => '/images/evening-desert-safari-dubai-dune-discovery-tourism.avif',
                ],
                'seo_tours_og_image' => [
                    'old' => ['/images/og-tours.jpg', 'images/og-tours.jpg'],
                    'new' => '/images/dubai-desert-safari-tour-dune-discovery-tourism.avif',
                ],
                'seo_blog_og_image' => [
                    'old' => ['/images/og-blog.jpg', 'images/og-blog.jpg'],
                    'new' => '/images/desert-safari-dubai-dune-discovery-tourism-HD.avif',
                ],
                'seo_about_og_image' => [
                    'old' => ['/images/og-about.jpg', 'images/og-about.jpg'],
                    'new' => '/images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif',
                ],
                'seo_contact_og_image' => [
                    'old' => ['/images/og-contact.jpg', 'images/og-contact.jpg'],
                    'new' => '/images/dubai-desert-safari-camp-dune-discovery-tourism.avif',
                ],
                'seo_faq_og_image' => [
                    'old' => ['/images/og-faq.jpg', 'images/og-faq.jpg'],
                    'new' => '/images/dubai-desert-safari-dune-bashing-dune-discovery-tourism.avif',
                ],
                'seo_rate_card_og_image' => [
                    'old' => ['/images/og-rate-card.jpg', 'images/og-rate-card.jpg'],
                    'new' => '/images/dubai-desert-safari-bbq-dune-discovery-tourism.avif',
                ],
            ];

            foreach ($ogSettingReplacements as $key => $spec) {
                DB::table('settings')
                    ->where('setting_key', $key)
                    ->whereIn('setting_value', $spec['old'])
                    ->update(['setting_value' => $spec['new'], 'updated_at' => now()]);
            }
        }

        if (Schema::hasTable('blog_posts')) {
            $posts = DB::table('blog_posts')->get(['id', 'og_image', 'content']);

            $legacyLinkReplacements = [
                '/luxury-vip-desert-safari-dubai' => '/evening-desert-safari-dubai',
                '\/luxury-vip-desert-safari-dubai' => '\/evening-desert-safari-dubai',
                '/vip-desert-safari-dubai' => '/evening-desert-safari-dubai',
                '\/vip-desert-safari-dubai' => '\/evening-desert-safari-dubai',
                '/cancellation-policy' => '/cancellation-refund-policy',
                '\/cancellation-policy' => '\/cancellation-refund-policy',
                '/custom-safari' => '/build-your-own-safari',
                '\/custom-safari' => '\/build-your-own-safari',
                '/pricing-guide' => '/rate-card',
                '\/pricing-guide' => '\/rate-card',
            ];

            foreach ($posts as $post) {
                $updates = [];

                if (! empty($post->og_image) && ! str_starts_with($post->og_image, 'http://') && ! str_starts_with($post->og_image, 'https://')) {
                    $cleanOg = ltrim($post->og_image, '/');
                    if (! str_starts_with($cleanOg, 'images/') && ! str_starts_with($cleanOg, 'storage/') && ! str_starts_with($cleanOg, 'uploads/')) {
                        $cleanOg = 'images/'.$cleanOg;
                    }
                    $normalizedOg = '/'.$cleanOg;
                    if ($normalizedOg !== $post->og_image) {
                        $updates['og_image'] = $normalizedOg;
                    }
                }

                if (! empty($post->content)) {
                    $updatedContent = str_replace(
                        array_keys($legacyLinkReplacements),
                        array_values($legacyLinkReplacements),
                        $post->content
                    );
                    if ($updatedContent !== $post->content) {
                        $updates['content'] = $updatedContent;
                    }
                }

                if (! empty($updates)) {
                    DB::table('blog_posts')->where('id', $post->id)->update($updates);
                }
            }
        }

        try {
            Cache::forget('site_settings_all');
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_blogs_xml');
            Cache::forget('sitemap_pages_xml');
            Cache::forget('sitemap_images_xml');
            Cache::forget('site_home_cache');
        } catch (Throwable $e) {
            // Ignore cache driver exceptions during CLI migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive SEO data normalization
    }
};
