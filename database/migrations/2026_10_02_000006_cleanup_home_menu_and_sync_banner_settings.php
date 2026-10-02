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
        // 1. Remove "Home" menu item from header navigation (Home is navigated via the Brand Logo)
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')
                ->where('location', 'header')
                ->where(function ($q) {
                    $q->where('route_name', 'home')
                        ->orWhere('url', '/');
                })
                ->delete();
        }

        // 2. Synchronize promotional banner settings
        if (Schema::hasTable('settings')) {
            $bannerActive = DB::table('settings')->where('setting_key', 'top_promo_banner_active')->value('setting_value');
            $bannerEnabled = DB::table('settings')->where('setting_key', 'promo_top_banner_enabled')->value('setting_value');

            // If either setting was set to '0' (disabled), ensure both are explicitly set to '0'
            if ($bannerActive === '0' || $bannerEnabled === '0') {
                DB::table('settings')->updateOrInsert(['setting_key' => 'top_promo_banner_active'], ['setting_value' => '0']);
                DB::table('settings')->updateOrInsert(['setting_key' => 'promo_top_banner_enabled'], ['setting_value' => '0']);
            }

            // 3. Synchronize welcome offer modal settings
            $popupActive = DB::table('settings')->where('setting_key', 'welcome_popup_active')->value('setting_value');
            $popupEnabled = DB::table('settings')->where('setting_key', 'promo_welcome_modal_enabled')->value('setting_value');

            if ($popupActive === '0' || $popupEnabled === '0') {
                DB::table('settings')->updateOrInsert(['setting_key' => 'welcome_popup_active'], ['setting_value' => '0']);
                DB::table('settings')->updateOrInsert(['setting_key' => 'promo_welcome_modal_enabled'], ['setting_value' => '0']);
            }
        }

        // 4. Synchronize coupon DUNESWELCOME if marked inactive in coupons table
        if (Schema::hasTable('coupons') && Schema::hasTable('settings')) {
            $dunesWelcome = DB::table('coupons')->where('code', 'DUNESWELCOME')->first();
            if ($dunesWelcome && $dunesWelcome->status === 'inactive') {
                DB::table('settings')->updateOrInsert(['setting_key' => 'top_promo_banner_active'], ['setting_value' => '0']);
                DB::table('settings')->updateOrInsert(['setting_key' => 'promo_top_banner_enabled'], ['setting_value' => '0']);
            }
        }

        Cache::forget('site_settings_cache');
        Cache::forget('site_home_cache');
        Cache::forget('cms_menu_header_cache');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible migration
    }
};
