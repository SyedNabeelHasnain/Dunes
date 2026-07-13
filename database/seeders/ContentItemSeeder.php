<?php

namespace Database\Seeders;

use App\Models\ContentItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemsPath = database_path('seeders/data/content_items.json');
        if (!File::exists($itemsPath)) {
            return;
        }

        $items = json_decode(File::get($itemsPath), true);
        foreach ($items as $item) {
            ContentItem::updateOrCreate(
                ['id' => $item['id']],
                [
                    'type' => $item['type'],
                    'icon' => $item['icon'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'priority' => (int)$item['priority'],
                ]
            );
        }

        // Seed Tour Content Mapping
        $tourContentPath = database_path('seeders/data/tour_content.json');
        if (File::exists($tourContentPath)) {
            $tourContent = json_decode(File::get($tourContentPath), true);
            foreach ($tourContent as $tc) {
                DB::table('tour_content')->updateOrInsert(
                    [
                        'id' => $tc['id']
                    ],
                    [
                        'tour_id' => $tc['tour_id'],
                        'tier_id' => $tc['tier_id'] ? (int)$tc['tier_id'] : null,
                        'content_id' => $tc['content_id'],
                    ]
                );
            }
        }
    }
}
