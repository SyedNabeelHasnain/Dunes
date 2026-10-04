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

        if (Schema::hasTable('coupons') && Schema::hasTable('settings')) {
            // 1. Check MATCH5 coupon status and align concierge_promo_active setting
            $matchStatus = DB::table('coupons')->where('code', 'MATCH5')->value('status');
            if ($matchStatus === 'active') {
                DB::table('settings')->updateOrInsert(
                    ['setting_key' => 'concierge_promo_active'],
                    ['setting_value' => '1', 'updated_at' => $now]
                );
            }

            // 2. Check SAVE5 coupon status and align exit_intent_promo_active setting
            $saveStatus = DB::table('coupons')->where('code', 'SAVE5')->value('status');
            if ($saveStatus === 'active') {
                DB::table('settings')->updateOrInsert(
                    ['setting_key' => 'exit_intent_promo_active'],
                    ['setting_value' => '1', 'updated_at' => $now]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal needed
    }
};
