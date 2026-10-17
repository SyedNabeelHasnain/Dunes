<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tiers') && ! Schema::hasColumn('tiers', 'guest_count')) {
            Schema::table('tiers', function (Blueprint $table) {
                $table->integer('guest_count')->nullable()->default(null)->after('description');
            });
        }

        if (Schema::hasTable('tour_tiers') && ! Schema::hasColumn('tour_tiers', 'guest_count')) {
            Schema::table('tour_tiers', function (Blueprint $table) {
                $table->integer('guest_count')->nullable()->default(null)->after('price_type');
            });
        }

        if (Schema::hasTable('tiers')) {
            DB::table('tiers')->where('id', 1)->update(['guest_count' => 1]);
            DB::table('tiers')->where('id', 2)->update(['guest_count' => 1]);
            DB::table('tiers')->where('id', 3)->update(['guest_count' => 1]);
            DB::table('tiers')->where('id', 4)->update(['guest_count' => 6]);
            DB::table('tiers')->where('id', 5)->update(['guest_count' => 2]);
            DB::table('tiers')->where('id', 6)->update(['guest_count' => 4]);
            DB::table('tiers')->where('id', 7)->update(['guest_count' => 6]);

            DB::table('tiers')->where('name', 'like', '%2 Adult%')->orWhere('slug', 'like', '%2-adult%')->update(['guest_count' => 2]);
            DB::table('tiers')->where('name', 'like', '%4 Adult%')->orWhere('slug', 'like', '%4-adult%')->update(['guest_count' => 4]);
            DB::table('tiers')->where('name', 'like', '%6 Adult%')->orWhere('slug', 'like', '%6-adult%')->update(['guest_count' => 6]);
        }

        if (Schema::hasTable('tour_tiers')) {
            DB::table('tour_tiers')->where('tour_id', 3)->where('tier_id', 5)->update(['guest_count' => 2, 'price_type' => 'per tent']);
            DB::table('tour_tiers')->where('tour_id', 3)->where('tier_id', 6)->update(['guest_count' => 4, 'price_type' => 'per tent']);
            DB::table('tour_tiers')->where('tour_id', 3)->where('tier_id', 7)->update(['guest_count' => 6, 'price_type' => 'per tent']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tour_tiers') && Schema::hasColumn('tour_tiers', 'guest_count')) {
            Schema::table('tour_tiers', function (Blueprint $table) {
                $table->dropColumn('guest_count');
            });
        }

        if (Schema::hasTable('tiers') && Schema::hasColumn('tiers', 'guest_count')) {
            Schema::table('tiers', function (Blueprint $table) {
                $table->dropColumn('guest_count');
            });
        }
    }
};
