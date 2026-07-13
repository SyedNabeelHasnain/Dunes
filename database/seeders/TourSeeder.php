<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $toursPath = database_path('seeders/data/tours.json');
        if (!File::exists($toursPath)) {
            return;
        }

        $tours = json_decode(File::get($toursPath), true);
        foreach ($tours as $t) {
            // Map category enum string to category_id
            $categorySlug = $t['category'];
            $category = Category::where('slug', $categorySlug)->first();
            $categoryId = $category ? $category->id : null;

            Tour::updateOrCreate(
                ['id' => $t['id']],
                [
                    'slug' => $t['slug'],
                    'name' => $t['name'],
                    'category_id' => $categoryId,
                    'short_desc' => $t['short_desc'],
                    'full_desc' => $t['full_desc'],
                    'duration' => $t['duration'],
                    'pickup_time' => $t['pickup_time'],
                    'dropoff_time' => $t['dropoff_time'],
                    'min_age' => (int)($t['min_age'] ?? 3),
                    'group_size' => $t['group_size'],
                    'languages' => $t['languages'] ?: 'English, Arabic',
                    'hero_image' => $t['hero_image'],
                    'thumb_image' => $t['thumb_image'],
                    'og_image' => $t['og_image'],
                    'video_url' => $t['video_url'],
                    'rating' => (float)($t['rating'] ?? 4.8),
                    'review_count' => (int)($t['review_count'] ?? 0),
                    'is_bestseller' => (bool)$t['is_bestseller'],
                    'is_featured' => (bool)$t['is_featured'],
                    'status' => $t['status'] ?: 'active',
                    'priority' => (int)$t['priority'],
                    'meta_title' => $t['meta_title'],
                    'meta_desc' => $t['meta_desc'],
                    'meta_keywords' => $t['meta_keywords'],
                ]
            );
        }

        // Seed Tour Tiers Relationships
        $tourTiersPath = database_path('seeders/data/tour_tiers.json');
        if (File::exists($tourTiersPath)) {
            $tourTiers = json_decode(File::get($tourTiersPath), true);
            foreach ($tourTiers as $tt) {
                DB::table('tour_tiers')->updateOrInsert(
                    ['tour_id' => $tt['tour_id'], 'tier_id' => $tt['tier_id']],
                    [
                        'price' => (float)$tt['price'],
                        'old_price' => $tt['old_price'] ? (float)$tt['old_price'] : null,
                        'price_type' => $tt['price_type'] ?: 'per person',
                    ]
                );
            }
        }

        // Seed Tour Addons Relationships
        $tourAddonsPath = database_path('seeders/data/tour_addons.json');
        if (File::exists($tourAddonsPath)) {
            $tourAddons = json_decode(File::get($tourAddonsPath), true);
            foreach ($tourAddons as $ta) {
                DB::table('tour_addons')->updateOrInsert(
                    ['tour_id' => $ta['tour_id'], 'addon_id' => $ta['addon_id']],
                    [
                        'price' => (float)$ta['price'],
                    ]
                );
            }
        }
    }
}
