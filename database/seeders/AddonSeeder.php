<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/addons.json');
        if (!File::exists($path)) {
            return;
        }

        $addons = json_decode(File::get($path), true);
        foreach ($addons as $a) {
            Addon::updateOrCreate(
                ['id' => $a['id']],
                [
                    'slug' => $a['slug'],
                    'name' => $a['name'],
                    'description' => $a['description'],
                    'icon' => $a['icon'],
                    'default_price' => (float)$a['default_price'],
                    'status' => $a['status'] ?: 'active',
                    'priority' => (int)$a['priority'],
                ]
            );
        }
    }
}
