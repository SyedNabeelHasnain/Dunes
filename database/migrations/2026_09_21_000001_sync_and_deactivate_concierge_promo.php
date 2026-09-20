<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // 1. Synchronize and set promotional settings
        if (Schema::hasTable('settings')) {
            $settingsToSync = [
                // Safari Match Concierge promo settings (Deactivated for now)
                'concierge_promo_active' => '0',
                'concierge_promo_discount' => '5',
                'concierge_promo_code' => 'MATCH5',

                // First-Time Visitor 25% Top Announcement Banner
                'promo_top_banner_enabled' => '1',
                'top_promo_banner_active' => '1',
                'promo_top_banner_code' => 'DUNESWELCOME',
                'top_promo_banner_code' => 'DUNESWELCOME',
                'promo_top_banner_discount' => '25',
                'promo_top_banner_badge' => 'Limited Time Offer',
                'top_promo_banner_text' => 'Special Online Exclusive: Get 25% OFF on all Desert Safari Tours! • 100% Free 24h Cancellation',

                // First-Time Visitor 25% Welcome Offer Modal
                'promo_welcome_modal_enabled' => '1',
                'welcome_popup_active' => '1',
                'promo_welcome_modal_discount' => '25',
                'welcome_popup_discount' => '25',
                'promo_welcome_modal_headline' => 'Unlock Exclusive 25% OFF',
                'welcome_popup_headline' => 'Unlock Exclusive 25% OFF',
                'promo_welcome_modal_subheadline' => 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.',
                'welcome_popup_subheadline' => 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.',
                'promo_welcome_modal_timer_minutes' => '15',
                'welcome_popup_timer_mins' => '15',
                'promo_welcome_modal_delay_seconds' => '5',
                'welcome_popup_delay_sec' => '5',
            ];

            foreach ($settingsToSync as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['setting_key' => $key],
                    ['setting_value' => $value, 'updated_at' => $now]
                );
            }
        }

        // 2. Ensure coupons table has correct statuses and codes
        if (Schema::hasTable('coupons')) {
            // Deactivate MATCH5 coupon
            DB::table('coupons')->updateOrInsert(
                ['code' => 'MATCH5'],
                [
                    'name' => 'Safari Match Concierge 5% Discount',
                    'description' => 'Special 5% discount unlocked via Safari Match Concierge interactive recommendation quiz.',
                    'discount_type' => 'percentage',
                    'discount_value' => 5.00,
                    'min_spend' => 0.00,
                    'max_discount' => null,
                    'min_guests' => 1,
                    'usage_limit' => null,
                    'usage_limit_per_user' => 10,
                    'status' => 'inactive', // Deactivated for now as per user directive
                    'is_featured' => false,
                    'updated_at' => $now,
                ]
            );

            // Ensure 25% DUNESWELCOME promo code exists and is ACTIVE
            DB::table('coupons')->updateOrInsert(
                ['code' => 'DUNESWELCOME'],
                [
                    'name' => 'First-Time Welcome 25% Promo (Top Banner)',
                    'description' => 'Official 25% discount code featured on top announcement bar for all desert safaris.',
                    'discount_type' => 'percentage',
                    'discount_value' => 25.00,
                    'min_spend' => 0.00,
                    'max_discount' => null,
                    'min_guests' => 1,
                    'usage_limit' => null,
                    'usage_limit_per_user' => 10,
                    'used_count' => 0,
                    'valid_from' => $now->copy()->subDay(),
                    'valid_until' => null,
                    'first_time_only' => false,
                    'is_featured' => true,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Ensure 25% FIRST25 promo code exists and is ACTIVE
            DB::table('coupons')->updateOrInsert(
                ['code' => 'FIRST25'],
                [
                    'name' => 'First-Time Guest 25% Promo',
                    'description' => 'Official 25% welcome promo code for first-time visitors.',
                    'discount_type' => 'percentage',
                    'discount_value' => 25.00,
                    'min_spend' => 0.00,
                    'max_discount' => null,
                    'min_guests' => 1,
                    'usage_limit' => null,
                    'usage_limit_per_user' => 1,
                    'used_count' => 0,
                    'valid_from' => $now->copy()->subDay(),
                    'valid_until' => null,
                    'first_time_only' => true,
                    'is_featured' => true,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('coupons')) {
            DB::table('coupons')->whereIn('code', ['DUNESWELCOME', 'FIRST25'])->delete();
        }
    }
};
