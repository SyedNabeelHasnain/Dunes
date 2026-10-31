<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/tiers.json');
        if (! File::exists($path)) {
            return;
        }

        $tiers = json_decode(File::get($path), true);
        foreach ($tiers as $t) {
            DB::table('tiers')->insertOrIgnore([
                'id' => $t['id'],
                'slug' => $t['slug'],
                'name' => is_array($t['name']) ? json_encode($t['name'], JSON_UNESCAPED_UNICODE) : $t['name'],
                'display_name' => is_array($t['display_name']) ? json_encode($t['display_name'], JSON_UNESCAPED_UNICODE) : $t['display_name'],
                'description' => is_array($t['description']) ? json_encode($t['description'], JSON_UNESCAPED_UNICODE) : $t['description'],
                'guest_count' => isset($t['guest_count']) ? (int) $t['guest_count'] : 1,
                'icon' => $t['icon'],
                'badge' => $t['badge'],
                'color' => $t['color'],
                'is_popular' => (bool) $t['is_popular'],
                'priority' => (int) $t['priority'],
                'status' => $t['status'] ?: 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
