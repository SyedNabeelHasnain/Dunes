<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync Tours
        $toursPath = database_path('seeders/data/tours.json');
        if (File::exists($toursPath) && Schema::hasTable('tours')) {
            $tours = json_decode(File::get($toursPath), true);
            if (is_array($tours)) {
                foreach ($tours as $t) {
                    $updates = [];
                    foreach (['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords'] as $col) {
                        if (isset($t[$col]) && Schema::hasColumn('tours', $col)) {
                            $updates[$col] = is_array($t[$col]) ? json_encode($t[$col], JSON_UNESCAPED_UNICODE) : $t[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('tours')
                            ->where('id', $t['id'])
                            ->orWhere('slug', $t['slug'])
                            ->update($updates);
                    }
                }
            }
        }

        // 2. Sync Tiers
        $tiersPath = database_path('seeders/data/tiers.json');
        if (File::exists($tiersPath) && Schema::hasTable('tiers')) {
            $tiers = json_decode(File::get($tiersPath), true);
            if (is_array($tiers)) {
                foreach ($tiers as $tier) {
                    $updates = [];
                    foreach (['name', 'display_name', 'description'] as $col) {
                        if (isset($tier[$col]) && Schema::hasColumn('tiers', $col)) {
                            $updates[$col] = is_array($tier[$col]) ? json_encode($tier[$col], JSON_UNESCAPED_UNICODE) : $tier[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('tiers')
                            ->where('id', $tier['id'])
                            ->orWhere('slug', $tier['slug'])
                            ->update($updates);
                    }
                }
            }
        }

        // 3. Sync Addons
        $addonsPath = database_path('seeders/data/addons.json');
        if (File::exists($addonsPath) && Schema::hasTable('addons')) {
            $addons = json_decode(File::get($addonsPath), true);
            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    $updates = [];
                    foreach (['name', 'description'] as $col) {
                        if (isset($addon[$col]) && Schema::hasColumn('addons', $col)) {
                            $updates[$col] = is_array($addon[$col]) ? json_encode($addon[$col], JSON_UNESCAPED_UNICODE) : $addon[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('addons')
                            ->where('id', $addon['id'])
                            ->orWhere('slug', $addon['slug'])
                            ->update($updates);
                    }
                }
            }
        }

        // 4. Sync Content Items
        $contentPath = database_path('seeders/data/content_items.json');
        if (File::exists($contentPath) && Schema::hasTable('content_items')) {
            $items = json_decode(File::get($contentPath), true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $updates = [];
                    foreach (['title', 'description'] as $col) {
                        if (isset($item[$col]) && Schema::hasColumn('content_items', $col)) {
                            $updates[$col] = is_array($item[$col]) ? json_encode($item[$col], JSON_UNESCAPED_UNICODE) : $item[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('content_items')
                            ->where('id', $item['id'])
                            ->update($updates);
                    }
                }
            }
        }

        // 5. Sync Itineraries
        $itineraryPath = database_path('seeders/data/itinerary.json');
        if (File::exists($itineraryPath) && Schema::hasTable('itineraries')) {
            $itineraries = json_decode(File::get($itineraryPath), true);
            if (is_array($itineraries)) {
                foreach ($itineraries as $it) {
                    $updates = [];
                    foreach (['title', 'description'] as $col) {
                        if (isset($it[$col]) && Schema::hasColumn('itineraries', $col)) {
                            $updates[$col] = is_array($it[$col]) ? json_encode($it[$col], JSON_UNESCAPED_UNICODE) : $it[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('itineraries')
                            ->where('id', $it['id'])
                            ->update($updates);
                    }
                }
            }
        }

        // 6. Sync FAQs
        $faqsPath = database_path('seeders/data/faqs.json');
        if (File::exists($faqsPath) && Schema::hasTable('faqs')) {
            $faqs = json_decode(File::get($faqsPath), true);
            if (is_array($faqs)) {
                foreach ($faqs as $faq) {
                    $updates = [];
                    foreach (['question', 'answer'] as $col) {
                        if (isset($faq[$col]) && Schema::hasColumn('faqs', $col)) {
                            $updates[$col] = is_array($faq[$col]) ? json_encode($faq[$col], JSON_UNESCAPED_UNICODE) : $faq[$col];
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('faqs')
                            ->where('id', $faq['id'])
                            ->update($updates);
                    }
                }
            }
        }

        // 7. Sync Categories and Blog Categories
        $catsPath = database_path('seeders/data/blog_categories.json');
        if (File::exists($catsPath)) {
            $cats = json_decode(File::get($catsPath), true);
            if (is_array($cats)) {
                foreach ($cats as $cat) {
                    $nameVal = is_array($cat['name']) ? json_encode($cat['name'], JSON_UNESCAPED_UNICODE) : $cat['name'];
                    $descVal = isset($cat['description']) ? (is_array($cat['description']) ? json_encode($cat['description'], JSON_UNESCAPED_UNICODE) : $cat['description']) : null;

                    if (Schema::hasTable('categories')) {
                        DB::table('categories')
                            ->where('id', $cat['id'])
                            ->orWhere('slug', $cat['slug'])
                            ->update(array_filter([
                                'name' => $nameVal,
                                'description' => $descVal,
                            ]));
                    }

                    if (Schema::hasTable('blog_categories')) {
                        DB::table('blog_categories')
                            ->where('id', $cat['id'])
                            ->orWhere('slug', $cat['slug'])
                            ->update(array_filter([
                                'name' => $nameVal,
                                'description' => $descVal,
                            ]));
                    }
                }
            }
        }

        // 8. Invalidate Caches
        try {
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_xml');
            Cache::forget('site_home_cache');
            Cache::forget('footer_tours');
            Cache::forget('header_navigation_menu');
        } catch (\Throwable $e) {
            // Ignore cache exceptions in migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
