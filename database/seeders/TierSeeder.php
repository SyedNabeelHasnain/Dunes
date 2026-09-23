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
                'name' => $t['name'],
                'display_name' => $t['display_name'],
                'description' => $t['description'],
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
