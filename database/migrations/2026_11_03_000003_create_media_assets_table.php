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
        if (! Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('filename', 255);
                $table->string('original_name', 255)->nullable();
                $table->string('disk', 30)->default('direct_public'); // direct_public (public/*) or public (storage/app/public/*)
                $table->string('file_path', 500);
                $table->string('public_url', 500)->index();
                $table->string('media_type', 20)->default('image')->index(); // image, video, document, audio
                $table->string('mime_type', 100)->nullable();
                $table->string('extension', 10)->nullable()->index();
                $table->unsignedBigInteger('file_size_bytes')->default(0);
                $table->unsignedSmallInteger('width')->nullable();
                $table->unsignedSmallInteger('height')->nullable();
                $table->unsignedSmallInteger('duration_seconds')->nullable();
                $table->string('title', 255)->nullable();
                $table->string('alt_text', 255)->nullable();
                $table->text('caption')->nullable();
                $table->string('category', 50)->default('general')->index(); // tours, blogs, reviews, banners, site, general
                $table->string('folder', 100)->default('root')->index();
                $table->boolean('is_optimized')->default(false);
                $table->unsignedInteger('usage_count')->default(0)->index();
                $table->json('usage_locations')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['media_type', 'category']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
