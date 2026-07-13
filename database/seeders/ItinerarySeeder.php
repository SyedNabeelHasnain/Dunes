<?php

namespace Database\Seeders;

use App\Models\Itinerary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ItinerarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/itinerary.json');
        if (!File::exists($path)) {
            return;
        }

        $itineraries = json_decode(File::get($path), true);
        foreach ($itineraries as $it) {
            Itinerary::updateOrCreate(
                ['id' => $it['id']],
                [
                    'tour_id' => $it['tour_id'],
                    'time' => $it['time'],
                    'title' => $it['title'],
                    'description' => $it['description'],
                    'icon' => $it['icon'],
                    'duration' => $it['duration'],
                    'priority' => (int)$it['priority'],
                ]
            );
        }
    }
}
