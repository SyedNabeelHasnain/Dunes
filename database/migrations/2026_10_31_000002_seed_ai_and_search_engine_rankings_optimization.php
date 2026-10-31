<?php

use App\Services\IndexNowService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update missing ai_summary on blog posts
        if (Schema::hasTable('blog_posts') && Schema::hasColumn('blog_posts', 'ai_summary')) {
            DB::table('blog_posts')
                ->where('id', 13)
                ->orWhere('slug', 'desert-safari-vs-dune-buggy-rental-dubai-comparison')
                ->update([
                    'ai_summary' => 'Choose a Desert Safari (from AED 79) for a comprehensive cultural adventure featuring 4x4 dune bashing, BBQ buffet dinner, camel riding, and live entertainment. Choose Dune Buggy Rental (from AED 599) for high-octane self-drive racing in 1000cc Can-Am Maverick buggies across open red dunes with professional lead guides.',
                ]);

            DB::table('blog_posts')
                ->where('id', 14)
                ->orWhere('slug', 'is-dune-bashing-safe-for-pregnant-women-kids-seniors')
                ->update([
                    'ai_summary' => 'Dune bashing is strictly prohibited for pregnant travelers and individuals with severe spinal or cardiac conditions under DET safety laws. Children under 3 require private vehicles with car seats. Seniors and expectant mothers can enjoy gentle nature safaris, direct camp drop-offs, and stargazing dinner experiences safely.',
                ]);
        }

        // 2. Ensure physical IndexNow key file exists in public/
        try {
            if (app()->bound(IndexNowService::class)) {
                app(IndexNowService::class)->ensureKeyFileExists();
            }
        } catch (\Throwable $e) {
            // Ignore key file creation errors during migration
        }

        // 3. Invalidate relevant caches
        try {
            Cache::forget('site_llms_txt');
            Cache::forget('site_llms_full_txt');
            Cache::forget('feeds_tours_rss_xml');
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_blogs_xml');
            Cache::forget('sitemap_xml');
            Cache::forget('site_home_cache');
            Cache::forget('site_tours_header_cache');
        } catch (\Throwable $e) {
            // Ignore cache exceptions
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
