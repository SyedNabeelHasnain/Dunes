<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LegalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Pages
        $pagesPath = database_path('seeders/data/legal_pages.json');
        if (File::exists($pagesPath)) {
            $pages = json_decode(File::get($pagesPath), true);
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

        // 2. Seed Sections
        $sectionsPath = database_path('seeders/data/legal_sections.json');
        if (File::exists($sectionsPath)) {
            $sections = json_decode(File::get($sectionsPath), true);
            foreach ($sections as $s) {
                DB::table('legal_sections')->updateOrInsert(
                    ['id' => $s['id']],
                    [
                        'page_id' => $s['page_id'],
                        'heading' => $s['heading'],
                        'heading_ar' => $s['heading_ar'] ?? null,
                        'subheading' => $s['subheading'] ?? null,
                        'subheading_ar' => $s['subheading_ar'] ?? null,
                        'priority' => (int) $s['priority'],
                    ]
                );
            }
        }

        // 3. Seed Items
        $itemsPath = database_path('seeders/data/legal_items.json');
        if (File::exists($itemsPath)) {
            $items = json_decode(File::get($itemsPath), true);
            foreach ($items as $item) {
                DB::table('legal_items')->updateOrInsert(
                    ['id' => $item['id']],
                    [
                        'section_id' => $item['section_id'],
                        'content' => $item['content'],
                        'content_ar' => $item['content_ar'] ?? null,
                        'priority' => (int) $item['priority'],
                    ]
                );
            }
        }
    }
}
