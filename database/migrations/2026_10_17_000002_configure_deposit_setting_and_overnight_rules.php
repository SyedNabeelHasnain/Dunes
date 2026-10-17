<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $now = now();
            $exists = DB::table('settings')->where('setting_key', 'deposit_percentage')->exists();
            if ($exists) {
                DB::table('settings')->where('setting_key', 'deposit_percentage')->update([
                    'setting_value' => '25',
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('settings')->insert([
                    'setting_key' => 'deposit_percentage',
                    'setting_value' => '25',
                    'description' => 'Configurable online advance deposit percentage (default 25%)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('settings')->where('setting_key', 'ziina_advance_percent')->update([
                'setting_value' => '25',
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('tour_tiers')) {
            DB::table('tour_tiers')->where('id', 1)->update([
                'old_price' => 99,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tour_tiers')) {
            DB::table('tour_tiers')->where('id', 1)->update([
                'old_price' => 130,
            ]);
        }
    }
};
