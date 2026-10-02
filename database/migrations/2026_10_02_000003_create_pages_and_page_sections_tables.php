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
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slug', 100)->unique();
                $table->string('name', 255);
                $table->text('title')->nullable();
                $table->text('subtitle')->nullable();
                $table->text('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('status', 50)->default('published')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('page_sections')) {
            Schema::create('page_sections', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('page_id');
                $table->string('section_key', 100);
                $table->string('name', 255);
                $table->text('title')->nullable();
                $table->text('subtitle')->nullable();
                $table->longText('body')->nullable();
                $table->longText('extra_data')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
                $table->index(['page_id', 'section_key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
    }
};
