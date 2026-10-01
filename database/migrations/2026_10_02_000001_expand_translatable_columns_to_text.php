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
        // 1. Tours table
        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->text('name')->change();
                $table->text('meta_title')->nullable()->change();
                $table->text('meta_keywords')->nullable()->change();
            });
        }

        // 2. Categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->text('name')->change();
                if (Schema::hasColumn('categories', 'meta_title')) {
                    $table->text('meta_title')->nullable()->change();
                }
                if (Schema::hasColumn('categories', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable()->change();
                }
            });
        }

        // 3. Tiers table
        if (Schema::hasTable('tiers')) {
            Schema::table('tiers', function (Blueprint $table) {
                $table->text('name')->change();
                $table->text('display_name')->nullable()->change();
            });
        }

        // 4. Addons table
        if (Schema::hasTable('addons')) {
            Schema::table('addons', function (Blueprint $table) {
                $table->text('name')->change();
            });
        }

        // 5. Content Items table
        if (Schema::hasTable('content_items')) {
            Schema::table('content_items', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }

        // 6. Itineraries table
        if (Schema::hasTable('itineraries')) {
            Schema::table('itineraries', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }

        // 7. Blog Posts table
        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->text('title')->change();
                $table->text('subtitle')->nullable()->change();
                $table->text('meta_title')->nullable()->change();
                $table->text('meta_keywords')->nullable()->change();
            });
        }

        // 8. Blog Categories table
        if (Schema::hasTable('blog_categories')) {
            Schema::table('blog_categories', function (Blueprint $table) {
                $table->text('name')->change();
                if (Schema::hasColumn('blog_categories', 'meta_title')) {
                    $table->text('meta_title')->nullable()->change();
                }
                if (Schema::hasColumn('blog_categories', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable()->change();
                }
            });
        }

        // 9. Blog Tags table
        if (Schema::hasTable('blog_tags')) {
            Schema::table('blog_tags', function (Blueprint $table) {
                $table->text('name')->change();
            });
        }

        // 10. Email Templates table
        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->text('subject')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible or preserve text columns
    }
};
