<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\LegalPage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Arabic columns if they do not exist
        if (Schema::hasTable('legal_pages')) {
            Schema::table('legal_pages', function (Blueprint $table) {
                if (!Schema::hasColumn('legal_pages', 'title_ar')) {
                    $table->string('title_ar', 255)->nullable()->after('title');
                }
                if (!Schema::hasColumn('legal_pages', 'subtitle_ar')) {
                    $table->string('subtitle_ar', 255)->nullable()->after('subtitle');
                }
                if (!Schema::hasColumn('legal_pages', 'description_ar')) {
                    $table->text('description_ar')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('legal_sections')) {
            Schema::table('legal_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('legal_sections', 'heading_ar')) {
                    $table->string('heading_ar', 255)->nullable()->after('heading');
                }
                if (!Schema::hasColumn('legal_sections', 'subheading_ar')) {
                    $table->string('subheading_ar', 255)->nullable()->after('subheading');
                }
            });
        }

        if (Schema::hasTable('legal_items')) {
            Schema::table('legal_items', function (Blueprint $table) {
                if (!Schema::hasColumn('legal_items', 'content_ar')) {
                    $table->text('content_ar')->nullable()->after('content');
                }
            });
        }

        // 2. Seed Pages
        $pagesPath = database_path('seeders/data/legal_pages.json');
        if (File::exists($pagesPath)) {
            $pages = json_decode(File::get($pagesPath), true);
            if (is_array($pages)) {
                foreach ($pages as $p) {
                    LegalPage::updateOrCreate(
                        ['id' => $p['id']],
                        [
                            'slug' => $p['slug'],
                            'title' => $p['title'],
                            'title_ar' => $p['title_ar'] ?? null,
                            'subtitle' => $p['subtitle'] ?? null,
                            'subtitle_ar' => $p['subtitle_ar'] ?? null,
                            'description' => $p['description'] ?? null,
                            'description_ar' => $p['description_ar'] ?? null,
                        ]
                    );
                }
            }
        }

        // 3. Seed Sections
        $sectionsPath = database_path('seeders/data/legal_sections.json');
        if (File::exists($sectionsPath)) {
            $sections = json_decode(File::get($sectionsPath), true);
            if (is_array($sections)) {
                foreach ($sections as $s) {
                    DB::table('legal_sections')->updateOrInsert(
                        ['id' => $s['id']],
                        [
                            'page_id' => $s['page_id'],
                            'heading' => $s['heading'],
                            'heading_ar' => $s['heading_ar'] ?? null,
                            'subheading' => $s['subheading'] ?? null,
                            'subheading_ar' => $s['subheading_ar'] ?? null,
                            'priority' => (int)$s['priority'],
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // 4. Seed Items
        $itemsPath = database_path('seeders/data/legal_items.json');
        if (File::exists($itemsPath)) {
            $items = json_decode(File::get($itemsPath), true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    DB::table('legal_items')->updateOrInsert(
                        ['id' => $item['id']],
                        [
                            'section_id' => $item['section_id'],
                            'content' => $item['content'],
                            'content_ar' => $item['content_ar'] ?? null,
                            'priority' => (int)$item['priority'],
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('legal_items') && Schema::hasColumn('legal_items', 'content_ar')) {
            Schema::table('legal_items', function (Blueprint $table) {
                $table->dropColumn('content_ar');
            });
        }

        if (Schema::hasTable('legal_sections')) {
            Schema::table('legal_sections', function (Blueprint $table) {
                if (Schema::hasColumn('legal_sections', 'heading_ar')) {
                    $table->dropColumn('heading_ar');
                }
                if (Schema::hasColumn('legal_sections', 'subheading_ar')) {
                    $table->dropColumn('subheading_ar');
                }
            });
        }

        if (Schema::hasTable('legal_pages')) {
            Schema::table('legal_pages', function (Blueprint $table) {
                if (Schema::hasColumn('legal_pages', 'title_ar')) {
                    $table->dropColumn('title_ar');
                }
                if (Schema::hasColumn('legal_pages', 'subtitle_ar')) {
                    $table->dropColumn('subtitle_ar');
                }
                if (Schema::hasColumn('legal_pages', 'description_ar')) {
                    $table->dropColumn('description_ar');
                }
            });
        }
    }
};
