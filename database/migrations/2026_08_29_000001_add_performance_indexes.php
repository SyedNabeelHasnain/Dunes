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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('tiers', function (Blueprint $table) {
            $table->index('priority');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->index('priority');
        });

        Schema::table('itineraries', function (Blueprint $table) {
            $table->index('priority');
        });

        Schema::table('content_items', function (Blueprint $table) {
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('tiers', function (Blueprint $table) {
            $table->dropIndex(['priority']);
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->dropIndex(['priority']);
        });

        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropIndex(['priority']);
        });

        Schema::table('content_items', function (Blueprint $table) {
            $table->dropIndex(['priority']);
        });
    }
};
