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
        if (Schema::hasTable('coupons')) {
            $now = now();

            // 1. MATCH5 - Unlocked via Safari Matcher AI Concierge
            DB::table('coupons')->updateOrInsert(
                ['code' => 'MATCH5'],
                [
                    'name' => 'Safari Matcher AI 5% Discount',
                    'description' => 'Special 5% discount unlocked via Safari Matcher AI interactive recommendation concierge.',
                    'discount_type' => 'percentage',
                    'discount_value' => 5.00,
                    'min_spend' => 0.00,
                    'max_discount' => null,
                    'min_guests' => 1,
                    'usage_limit' => null,
                    'usage_limit_per_user' => 10,
                    'used_count' => 0,
                    'valid_from' => $now,
                    'valid_until' => null,
                    'tour_date_from' => null,
                    'tour_date_to' => null,
                    'tour_id' => null,
                    'tier_id' => null,
                    'first_time_only' => false,
                    'is_featured' => true,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // 2. SAVE5 - Unlocked via Exit-Intent Cart Saver
            DB::table('coupons')->updateOrInsert(
                ['code' => 'SAVE5'],
                [
                    'name' => 'Smart Exit-Intent Cart Saver 5% Discount',
                    'description' => 'Exclusive 5% instant discount unlocked to save booking before leaving.',
                    'discount_type' => 'percentage',
                    'discount_value' => 5.00,
                    'min_spend' => 0.00,
                    'max_discount' => null,
                    'min_guests' => 1,
                    'usage_limit' => null,
                    'usage_limit_per_user' => 10,
                    'used_count' => 0,
                    'valid_from' => $now,
                    'valid_until' => null,
                    'tour_date_from' => null,
                    'tour_date_to' => null,
                    'tour_id' => null,
                    'tier_id' => null,
                    'first_time_only' => false,
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
            DB::table('coupons')->whereIn('code', ['MATCH5', 'SAVE5'])->delete();
        }
    }
};
