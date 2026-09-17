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
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'photos')) {
                $table->json('photos')->nullable()->after('review_text');
            }
            if (!Schema::hasColumn('reviews', 'booking_id')) {
                $table->unsignedInteger('booking_id')->nullable()->after('source_review_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'photos')) {
                $table->dropColumn('photos');
            }
            if (Schema::hasColumn('reviews', 'booking_id')) {
                $table->dropColumn('booking_id');
            }
        });
    }
};
