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
            if (! Schema::hasColumn('reviews', 'is_local_guide')) {
                $table->boolean('is_local_guide')->default(false)->after('reviewer_name');
            }
            if (! Schema::hasColumn('reviews', 'reviewer_reviews_count')) {
                $table->unsignedInteger('reviewer_reviews_count')->nullable()->after('is_local_guide');
            }
            if (! Schema::hasColumn('reviews', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('rating');
            }
            if (! Schema::hasColumn('reviews', 'owner_response_text')) {
                $table->text('owner_response_text')->nullable()->after('review_text');
            }
            if (! Schema::hasColumn('reviews', 'owner_response_date')) {
                $table->dateTime('owner_response_date')->nullable()->after('owner_response_text');
            }
            if (! Schema::hasColumn('reviews', 'language')) {
                $table->string('language', 10)->nullable()->after('photos');
            }
            if (! Schema::hasColumn('reviews', 'visited_in')) {
                $table->string('visited_in', 50)->nullable()->after('language');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $columns = [
                'is_local_guide',
                'reviewer_reviews_count',
                'likes_count',
                'owner_response_text',
                'owner_response_date',
                'language',
                'visited_in',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
