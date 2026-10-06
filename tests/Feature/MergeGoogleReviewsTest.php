<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MergeGoogleReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected string $tempMasterPath;
    protected string $tempExtractedPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempMasterPath = storage_path('framework/testing/test_master_reviews.json');
        $this->tempExtractedPath = storage_path('framework/testing/test_extracted_reviews.json');

        File::ensureDirectoryExists(dirname($this->tempMasterPath));
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempMasterPath)) {
            File::delete($this->tempMasterPath);
        }
        if (File::exists($this->tempExtractedPath)) {
            File::delete($this->tempExtractedPath);
        }

        parent::tearDown();
    }

    public function test_merge_command_repairs_mislabeled_record_and_populates_photos(): void
    {
        $sampleMaster = [
            [
                'id' => 115,
                'source' => 'Google',
                'source_review_id' => 990878382,
                'review_url' => 'https://www.tripadvisor.com/ShowUserReviews-test.html',
                'published_date' => '2025-01-28',
                'reviewer_name' => 'Veronica M',
                'rating' => 5,
                'status' => 'approved',
                'is_featured' => 1,
            ],
            [
                'id' => 9,
                'source' => 'google',
                'source_review_id' => 'Ci9DQUlRQUNvZENodHljRjlv',
                'published_date' => '2025-12-27',
                'reviewer_name' => 'Parameswaran K',
                'rating' => 5,
                'status' => 'approved',
                'is_featured' => 0,
            ],
        ];

        File::put($this->tempMasterPath, json_encode($sampleMaster));

        $this->artisan('reviews:merge-extracted', [
            '--master-file' => $this->tempMasterPath,
        ])->assertSuccessful();

        $reloaded = json_decode(File::get($this->tempMasterPath), true);
        $this->assertEquals('tripadvisor', $reloaded[0]['source']);
        $this->assertIsArray($reloaded[0]['photos']);
        $this->assertIsArray($reloaded[1]['photos']);
    }

    public function test_merge_command_deduplicates_by_source_review_id_and_adds_new(): void
    {
        $sampleMaster = [
            [
                'id' => 1,
                'source' => 'google',
                'source_review_id' => 'rev_existing_1',
                'published_date' => '2025-12-20',
                'reviewer_name' => 'Existing Customer',
                'reviewer_avatar_url' => 'https://ui-avatars.com/api/?name=Existing',
                'photos' => [],
                'rating' => 5,
            ],
        ];
        File::put($this->tempMasterPath, json_encode($sampleMaster));

        $sampleExtracted = [
            // Exact match - should update avatar and photos
            [
                'source_review_id' => 'rev_existing_1',
                'reviewer_name' => 'Existing Customer',
                'reviewer_avatar_url' => 'https://lh3.googleusercontent.com/avatar_high_res.jpg',
                'published_date' => '2025-12-20',
                'photos' => ['https://lh3.googleusercontent.com/photo1.jpg'],
                'rating' => 5,
            ],
            // Brand new review - should add
            [
                'source_review_id' => 'rev_new_2',
                'reviewer_name' => 'New Customer',
                'reviewer_avatar_url' => 'https://lh3.googleusercontent.com/avatar_new.jpg',
                'published_date' => '2026-02-10',
                'photos' => ['https://lh3.googleusercontent.com/photo2.jpg'],
                'rating' => 5,
                'review_text' => 'Magnificent desert experience!',
            ],
        ];
        File::put($this->tempExtractedPath, json_encode($sampleExtracted));

        $this->artisan('reviews:merge-extracted', [
            '--master-file' => $this->tempMasterPath,
            '--source-file' => $this->tempExtractedPath,
            '--sync-db' => true,
        ])->assertSuccessful();

        $reloaded = json_decode(File::get($this->tempMasterPath), true);
        $this->assertCount(2, $reloaded);

        // Verify existing updated
        $this->assertEquals('https://lh3.googleusercontent.com/avatar_high_res.jpg', $reloaded[0]['reviewer_avatar_url']);
        $this->assertEquals(['https://lh3.googleusercontent.com/photo1.jpg'], $reloaded[0]['photos']);

        // Verify new added
        $this->assertEquals('New Customer', $reloaded[1]['reviewer_name']);
        $this->assertEquals('rev_new_2', $reloaded[1]['source_review_id']);

        // Verify database has both
        $this->assertDatabaseHas('reviews', [
            'source_review_id' => 'rev_existing_1',
            'reviewer_avatar_url' => 'https://lh3.googleusercontent.com/avatar_high_res.jpg',
        ]);
        $this->assertDatabaseHas('reviews', [
            'source_review_id' => 'rev_new_2',
            'reviewer_name' => 'New Customer',
        ]);
    }
}
