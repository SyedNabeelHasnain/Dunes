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

    public function test_merge_command_imports_and_maps_csv_reviews_with_rich_metadata(): void
    {
        $tempCsvPath = storage_path('framework/testing/test_reviews.csv');

        $sampleMaster = [
            [
                'id' => 1,
                'source' => 'tripadvisor',
                'source_review_id' => 'ta_100',
                'reviewer_name' => 'TripAdvisor Traveler',
                'rating' => 5,
                'published_date' => '2025-05-10',
                'photos' => [],
            ],
        ];
        File::put($this->tempMasterPath, json_encode($sampleMaster));

        $csvContent = "\"address\",\"businessProfileId\",\"categories/0\",\"categories/1\",\"categoryName\",\"cid\",\"city\",\"countryCode\",\"fid\",\"hotelStars\",\"imageUrl\",\"isAdvertisement\",\"isLocalGuide\",\"kgmid\",\"language\",\"likesCount\",\"location/lat\",\"location/lng\",\"name\",\"neighborhood\",\"originalLanguage\",\"permanentlyClosed\",\"placeId\",\"postalCode\",\"price\",\"publishAt\",\"publishedAtDate\",\"rating\",\"responseFromOwnerDate\",\"responseFromOwnerText\",\"reviewId\",\"reviewImageUrls/0\",\"reviewImageUrls/1\",\"reviewImageUrls/2\",\"reviewImageUrls/3\",\"reviewImageUrls/4\",\"reviewImageUrls/5\",\"reviewImageUrls/6\",\"reviewImageUrls/7\",\"reviewOrigin\",\"reviewUrl\",\"reviewerId\",\"reviewerNumberOfReviews\",\"reviewerPhotoUrl\",\"reviewerUrl\",\"reviewsCount\",\"scrapedAt\",\"searchString\",\"stars\",\"state\",\"street\",\"temporarilyClosed\",\"text\",\"textTranslated\",\"title\",\"totalScore\",\"translatedLanguage\",\"url\",\"visitedIn\"\n" .
            ",\"1522201601161585131\",\"Tour agency\",\"Service establishment\",\"Tour agency\",\"14154142641213989346\",,,\"0x4174448510086b6d:0xc46d9dbd85c4e1e2\",,,\"false\",\"true\",\"/g/11vk63ds_v\",\"en\",\"2\",\"24.35\",\"53.94\",\"Aron Popthorn\",,\"en\",\"false\",\"ChIJbWsIEIVEdEER4uHEhb2dbcQ\",,,\"2 days ago\",\"2026-10-04T14:04:18.925Z\",,\"2026-10-04T16:00:00.000Z\",\"Thank you for visiting!\",\"g_csv_test_1\",\"https://lh3.googleusercontent.com/photo1.jpg\",,,,,,,,\"Google\",\"https://www.google.com/maps/reviews/test\",\"105827233594346110028\",\"15\",\"https://lh3.googleusercontent.com/avatar.jpg\",\"https://www.google.com/maps/contrib/aron\",\"329\",\"2026-10-06T19:23:43.222Z\",\"DDT\",\"5\",,,\"false\",\"Greatest service experience!\",,,\"5\",\"en\",,\"September 2026\"\n";

        File::put($tempCsvPath, $csvContent);

        $this->artisan('reviews:merge-extracted', [
            '--master-file' => $this->tempMasterPath,
            '--source-file' => $tempCsvPath,
            '--sync-db' => true,
        ])->assertSuccessful();

        $reloaded = json_decode(File::get($this->tempMasterPath), true);
        $this->assertCount(2, $reloaded);

        // Verify CSV record mapped in JSON
        $csvItem = $reloaded[1];
        $this->assertEquals('g_csv_test_1', $csvItem['source_review_id']);
        $this->assertEquals('Aron Popthorn', $csvItem['reviewer_name']);
        $this->assertEquals(5.0, $csvItem['rating']);
        $this->assertTrue($csvItem['is_local_guide']);
        $this->assertEquals(15, $csvItem['reviewer_reviews_count']);
        $this->assertEquals(2, $csvItem['likes_count']);
        $this->assertEquals('Thank you for visiting!', $csvItem['owner_response_text']);
        $this->assertEquals('en', $csvItem['language']);
        $this->assertEquals('September 2026', $csvItem['visited_in']);
        $this->assertEquals(['https://lh3.googleusercontent.com/photo1.jpg'], $csvItem['photos']);

        // Verify database sync
        $this->assertDatabaseHas('reviews', [
            'source_review_id' => 'g_csv_test_1',
            'reviewer_name' => 'Aron Popthorn',
            'is_local_guide' => true,
            'reviewer_reviews_count' => 15,
            'likes_count' => 2,
            'owner_response_text' => 'Thank you for visiting!',
            'language' => 'en',
            'visited_in' => 'September 2026',
        ]);

        if (File::exists($tempCsvPath)) {
            File::delete($tempCsvPath);
        }
    }
}

