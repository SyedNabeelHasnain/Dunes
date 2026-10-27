<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $title = 'Dubai Desert Safari from AED 79 (2026) | 4x4 Lahbab Red Dunes | Dunes Discovery';
        $desc = '★ 4.9/5 Rated (2,800+ Reviews). Book Dubai Desert Safari from AED 79. Includes 4x4 Lahbab Red Dunes bashing, camel ride, live BBQ & pickup. Instant WhatsApp voucher & pay on arrival.';

        DB::table('settings')->updateOrInsert(
            ['setting_key' => 'seo_home_title'],
            ['setting_value' => $title, 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['setting_key' => 'seo_home_description'],
            ['setting_value' => $desc, 'updated_at' => now()]
        );

        Cache::forget('site_settings_all');
        Cache::forget('site_home_cache');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original generic title if rolled back
        DB::table('settings')->where('setting_key', 'seo_home_title')->update([
            'setting_value' => 'Dunes Discovery Tourism | Dubai Desert Safari & Adventure Tours 2026',
            'updated_at' => now(),
        ]);

        DB::table('settings')->where('setting_key', 'seo_home_description')->update([
            'setting_value' => 'Experience the best desert safari in Dubai with Dunes Discovery Tourism. High-octane dune bashing, quad biking, camel rides, falconry & 5-star VIP BBQ dinners.',
            'updated_at' => now(),
        ]);

        Cache::forget('site_settings_all');
        Cache::forget('site_home_cache');
    }
};
