<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            TierSeeder::class,
            AddonSeeder::class,
            TourSeeder::class,
            ContentItemSeeder::class,
            ItinerarySeeder::class,
            FaqSeeder::class,
            ReviewSeeder::class,
            LegalPageSeeder::class,
            BookingSeeder::class,
            BlogSeeder::class,
            UserSeeder::class,
        ]);
    }
}

