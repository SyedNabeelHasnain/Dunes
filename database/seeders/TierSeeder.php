<?php

namespace Database\Seeders;

use App\Models\Tier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/tiers.json');
        if (!File::exists($path)) {
            return;
        }

        $tiers = json_decode(File::get($path), true);
        foreach ($tiers as $t) {
            Tier::updateOrCreate(
                ['id' => $t['id']],
                [
                    'slug' => $t['slug'],
                    'name' => $t['name'],
                    'display_name' => $t['display_name'],
                    'description' => $t['description'],
                    'icon' => $t['icon'],
                    'badge' => $t['badge'],
                    'color' => $t['color'],
                    'is_popular' => (bool)$t['is_popular'],
                    'priority' => (int)$t['priority'],
                    'status' => $t['status'] ?: 'active',
                ]
            );
        }
    }
}
