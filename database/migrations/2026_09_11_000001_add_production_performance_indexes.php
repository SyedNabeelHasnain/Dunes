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
        if (Schema::hasTable('faq_assignments')) {
            Schema::table('faq_assignments', function (Blueprint $table) {
                $table->index(['entity_type', 'entity_id'], 'faq_assignments_entity_composite_idx');
            });
        }

        if (Schema::hasTable('booking_addons')) {
            Schema::table('booking_addons', function (Blueprint $table) {
                $table->index('addon_id', 'booking_addons_addon_id_idx');
            });
        }

        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->index(['status', 'priority'], 'tours_status_priority_idx');
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['status', 'is_featured'], 'reviews_status_featured_idx');
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['status', 'is_featured'], 'coupons_status_featured_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('faq_assignments')) {
            Schema::table('faq_assignments', function (Blueprint $table) {
                $table->dropIndex('faq_assignments_entity_composite_idx');
            });
        }

        if (Schema::hasTable('booking_addons')) {
            Schema::table('booking_addons', function (Blueprint $table) {
                $table->dropIndex('booking_addons_addon_id_idx');
            });
        }

        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropIndex('tours_status_priority_idx');
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_status_featured_idx');
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('coupons_status_featured_idx');
            });
        }
    }
};
