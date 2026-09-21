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

        // 1. Deactivate Exit-Intent Cart Saver (SAVE5) promotional setting
        if (Schema::hasTable('settings')) {
            $settingsToSync = [
                'exit_intent_promo_active' => '0',
                'exit_intent_promo_discount' => '5',
                'exit_intent_promo_code' => 'SAVE5',
            ];

            foreach ($settingsToSync as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['setting_key' => $key],
                    ['setting_value' => $value, 'updated_at' => $now]
                );
            }
        }

        // 2. Deactivate SAVE5 coupon in database
        if (Schema::hasTable('coupons')) {
            DB::table('coupons')->updateOrInsert(
                ['code' => 'SAVE5'],
                [
                    'name' => 'Exit-Intent Cart Saver 5% Discount',
                    'description' => 'System discount code (5% off) - Deactivated in favor of 25% Welcome Offer',
                    'discount_type' => 'percentage',
                    'discount_value' => 5.00,
                    'min_spend' => 0.00,
                    'min_guests' => 1,
                    'status' => 'inactive',
                    'is_featured' => false,
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
        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('setting_key', [
                'exit_intent_promo_active',
                'exit_intent_promo_discount',
                'exit_intent_promo_code',
            ])->delete();
        }
    }
};
