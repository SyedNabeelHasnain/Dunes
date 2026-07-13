<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'slug' => 'desert-safari',
                'name' => 'Desert Safaris',
                'icon' => 'bi-sun-fill',
                'priority' => 1,
            ],
            [
                'id' => 2,
                'slug' => 'city-tour',
                'name' => 'City Tours',
                'icon' => 'bi-building-fill',
                'priority' => 2,
            ],
            [
                'id' => 3,
                'slug' => 'water-activity',
                'name' => 'Cruises & Water Activities',
                'icon' => 'bi-water',
                'priority' => 3,
            ],
            [
                'id' => 4,
                'slug' => 'day-trip',
                'name' => 'Day Trips',
                'icon' => 'bi-map-fill',
                'priority' => 4,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['id' => $cat['id']], $cat);
        }
    }
}
