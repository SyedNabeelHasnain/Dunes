<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_templates')) {
            Schema::create('seo_templates', function (Blueprint $table) {
                $table->id();
                $table->string('content_type', 64)->unique();
                $table->string('label', 128);
                $table->string('title_pattern', 255)->nullable();
                $table->text('description_pattern')->nullable();
                $table->text('keywords_pattern')->nullable();
                $table->string('robots_default', 128)->default('index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
                $table->string('og_type_default', 32)->default('website');
                $table->string('schema_type_default', 64)->nullable();
                $table->boolean('sitemap_enabled')->default(true);
                $table->decimal('sitemap_priority', 3, 2)->default(0.70);
                $table->string('sitemap_changefreq', 24)->default('weekly');
                $table->boolean('include_brand_suffix')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seo_metadata')) {
            Schema::create('seo_metadata', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 128);
                $table->unsignedBigInteger('entity_id');
                $table->string('focus_keyword', 190)->nullable()->index();
                $table->json('secondary_keywords')->nullable();
                $table->string('search_intent', 32)->nullable();
                $table->string('canonical_override', 500)->nullable();
                $table->string('robots_directive', 128)->nullable();
                $table->string('og_title', 255)->nullable();
                $table->text('og_description')->nullable();
                $table->string('og_image', 500)->nullable();
                $table->string('twitter_card', 32)->nullable();
                $table->string('schema_type_override', 64)->nullable();
                $table->longText('schema_json_override')->nullable();
                $table->boolean('include_in_sitemap')->nullable();
                $table->string('breadcrumb_label', 160)->nullable();
                $table->string('editorial_status', 32)->default('published')->index();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->string('reviewed_by_name', 120)->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->unsignedSmallInteger('stale_after_days')->nullable();
                $table->timestamps();

                $table->unique(['entity_type', 'entity_id'], 'seo_meta_entity_unique');
            });
        }

        if (! Schema::hasTable('content_revisions')) {
            Schema::create('content_revisions', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 128);
                $table->unsignedBigInteger('entity_id');
                $table->unsignedInteger('version_number')->default(1);
                $table->string('title', 255)->nullable();
                $table->longText('snapshot');
                $table->string('change_summary', 255)->nullable();
                $table->string('editorial_status', 32)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_by_name', 120)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['entity_type', 'entity_id', 'version_number'], 'content_revisions_entity_version_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('seo_metadata');
        Schema::dropIfExists('seo_templates');
    }
};
