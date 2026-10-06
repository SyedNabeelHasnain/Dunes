<?php

namespace Tests\Feature;

use App\Models\Review;
use Database\Seeders\ReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_seeder_populates_all_reviews_with_google_metadata(): void
    {
        $this->seed(ReviewSeeder::class);

        $totalCount = Review::count();
        $this->assertEquals(407, $totalCount);

        $googleCount = Review::where('source', 'google')->count();
        $this->assertEquals(329, $googleCount);

        $tripadvisorCount = Review::where('source', 'tripadvisor')->count();
        $this->assertEquals(78, $tripadvisorCount);

        $localGuidesCount = Review::where('source', 'google')->where('is_local_guide', true)->count();
        $this->assertEquals(46, $localGuidesCount);

        $ownerResponseCount = Review::where('source', 'google')->whereNotNull('owner_response_text')->count();
        $this->assertGreaterThanOrEqual(290, $ownerResponseCount);

        // Verify that photos are properly cast as an array
        $reviewWithPhotos = Review::where('source', 'google')
            ->whereNotNull('photos')
            ->get()
            ->first(fn ($r) => ! empty($r->photos));

        $this->assertNotNull($reviewWithPhotos);
        $this->assertIsArray($reviewWithPhotos->photos);
        $this->assertNotEmpty($reviewWithPhotos->photos);

        // Verify status and rating
        $fiveStarGoogle = Review::where('source', 'google')->where('rating', 5)->count();
        $this->assertEquals(323, $fiveStarGoogle);
    }
}
