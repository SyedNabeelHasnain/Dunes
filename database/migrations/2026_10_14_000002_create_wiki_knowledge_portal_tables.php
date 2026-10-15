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
        if (! Schema::hasTable('wiki_pillars')) {
            Schema::create('wiki_pillars', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->text('name');
                $table->text('headline')->nullable();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('hero_image')->nullable();
                $table->integer('priority')->default(0);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wiki_articles')) {
            Schema::create('wiki_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pillar_id')->constrained('wiki_pillars')->cascadeOnDelete();
                $table->string('slug')->unique();
                $table->text('title');
                $table->text('subtitle')->nullable();
                $table->text('excerpt');
                $table->longText('content');
                $table->string('hero_image')->nullable();
                $table->text('table_of_contents')->nullable();
                $table->text('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->string('reviewed_by_name')->default('Malik Awan');
                $table->string('reviewed_by_title')->default('Lead Safari Marshal & Operations (DET #1430583)');
                $table->timestamp('last_reviewed_at')->nullable();
                $table->integer('reading_time_minutes')->default(5);
                $table->integer('priority')->default(0);
                $table->integer('view_count')->default(0);
                $table->string('status')->default('published');
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wiki_glossary_terms')) {
            Schema::create('wiki_glossary_terms', function (Blueprint $table) {
                $table->id();
                $table->string('term_key')->unique();
                $table->text('term');
                $table->text('definition');
                $table->foreignId('wiki_article_id')->nullable()->constrained('wiki_articles')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wiki_article_tours')) {
            Schema::create('wiki_article_tours', function (Blueprint $table) {
                $table->foreignId('wiki_article_id')->constrained('wiki_articles')->cascadeOnDelete();
                $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
                $table->integer('sort_order')->default(0);
                $table->primary(['wiki_article_id', 'tour_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wiki_article_tours');
        Schema::dropIfExists('wiki_glossary_terms');
        Schema::dropIfExists('wiki_articles');
        Schema::dropIfExists('wiki_pillars');
    }
};
