<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add index on tours (status, is_bestseller) if table exists
        if (Schema::hasTable('tours')) {
            try {
                Schema::table('tours', function (Blueprint $table) {
                    $table->index(['status', 'is_bestseller'], 'tours_status_bestseller_idx');
                });
            } catch (Throwable $e) {
            }
        }

        // Add index on tiers (tour_id, status) if table exists
        if (Schema::hasTable('tiers')) {
            try {
                Schema::table('tiers', function (Blueprint $table) {
                    $table->index(['tour_id', 'status'], 'tiers_tour_status_idx');
                });
            } catch (Throwable $e) {
            }
        }

        // Add index on addons (tour_id, status) if table exists
        if (Schema::hasTable('addons')) {
            try {
                Schema::table('addons', function (Blueprint $table) {
                    $table->index(['tour_id', 'status'], 'addons_tour_status_idx');
                });
            } catch (Throwable $e) {
            }
        }

        // Add index on coupon_usages (booking_id) if table exists
        if (Schema::hasTable('coupon_usages')) {
            try {
                Schema::table('coupon_usages', function (Blueprint $table) {
                    $table->index('booking_id', 'coupon_usages_booking_id_idx');
                });
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tours')) {
            try {
                Schema::table('tours', function (Blueprint $table) {
                    $table->dropIndex('tours_status_bestseller_idx');
                });
            } catch (Throwable $e) {
            }
        }

        if (Schema::hasTable('tiers')) {
            try {
                Schema::table('tiers', function (Blueprint $table) {
                    $table->dropIndex('tiers_tour_status_idx');
                });
            } catch (Throwable $e) {
            }
        }

        if (Schema::hasTable('addons')) {
            try {
                Schema::table('addons', function (Blueprint $table) {
                    $table->dropIndex('addons_tour_status_idx');
                });
            } catch (Throwable $e) {
            }
        }

        if (Schema::hasTable('coupon_usages')) {
            try {
                Schema::table('coupon_usages', function (Blueprint $table) {
                    $table->dropIndex('coupon_usages_booking_id_idx');
                });
            } catch (Throwable $e) {
            }
        }
    }
};
