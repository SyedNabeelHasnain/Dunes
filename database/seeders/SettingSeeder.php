<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/settings.json');
        if (!File::exists($path)) {
            return;
        }

        $settings = json_decode(File::get($path), true);
        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['setting_key' => $s['setting_key']],
                ['setting_value' => $s['setting_value']]
            );
        }
    }
}
