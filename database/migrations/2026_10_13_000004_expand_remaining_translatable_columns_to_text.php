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
        if (Schema::hasTable('legal_pages')) {
            Schema::table('legal_pages', function (Blueprint $table) {
                if (Schema::hasColumn('legal_pages', 'title')) {
                    $table->text('title')->nullable()->change();
                }
                if (Schema::hasColumn('legal_pages', 'subtitle')) {
                    $table->text('subtitle')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('legal_sections')) {
            Schema::table('legal_sections', function (Blueprint $table) {
                if (Schema::hasColumn('legal_sections', 'heading')) {
                    $table->text('heading')->nullable()->change();
                }
                if (Schema::hasColumn('legal_sections', 'subheading')) {
                    $table->text('subheading')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                if (Schema::hasColumn('email_templates', 'preview_text')) {
                    $table->text('preview_text')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Column expansion to text is non-destructive and safe to retain on rollback.
    }
};
