<?php

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
        // 1. Purge all specific search and model caches via Cache facade
        $keys = [
            'site_active_tours_search_en',
            'site_active_tours_search_ar',
            'site_active_tours_search_ru',
            'site_active_tours_search_es',
            'site_active_tours_search_it',
            'site_tours_header_cache',
            'admin_dashboard_kpis',
            'site_settings_cache',
        ];

        foreach ($keys as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                // Ignore driver-specific cache forget errors
            }
        }

        // 2. Direct database table cache cleanup if cache table exists
        try {
            if (Schema::hasTable('cache')) {
                DB::table('cache')
                    ->where('key', 'LIKE', '%site_active_tours_search%')
                    ->orWhere('key', 'LIKE', '%site_tours_header%')
                    ->orWhere('key', 'LIKE', '%admin_dashboard_kpis%')
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Ignore if using array or non-database cache in testing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cache flush is non-reversible and stateless
    }
};
