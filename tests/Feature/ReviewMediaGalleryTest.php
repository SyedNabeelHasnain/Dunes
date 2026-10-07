<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Review;
use App\Models\Tour;
use App\Services\ReviewMediaGalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewMediaGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_service_extracts_photos_and_strictly_excludes_avatars(): void
    {
        $service = app(ReviewMediaGalleryService::class);

        $avatarUrl = 'https://ui-avatars.com/api/?name=John+Doe';
        $realPhoto1 = 'https://lh3.googleusercontent.com/test-photo-1=s1600';
        $realPhoto2 = 'https://lh3.googleusercontent.com/test-photo-2=s1600';

        Review::create([
            'source' => 'google',
            'reviewer_name' => 'John Doe',
            'reviewer_avatar_url' => $avatarUrl,
            'rating' => 5,
            'review_title' => 'Amazing Red Dunes Bashing',
            'review_text' => 'We enjoyed high red dunes safari and quad biking with our guide.',
            'published_date' => now()->subDays(2),
            'status' => 'approved',
            'photos' => [
                $avatarUrl, // Avatar should be filtered out
                $realPhoto1,
                $realPhoto2,
                'https://example.com/avatar_3.jpg', // Placeholder avatar should be filtered out
            ],
        ]);

        // Non-approved review should be completely ignored
        Review::create([
            'source' => 'tripadvisor',
            'reviewer_name' => 'Spam Bot',
            'rating' => 1,
            'review_text' => 'Bad experience',
            'status' => 'pending',
            'photos' => ['https://example.com/spam-photo.jpg'],
        ]);

        $extracted = $service->extractAllReviewMedia();

        $this->assertCount(2, $extracted);
        $extractedUrls = collect($extracted)->pluck('url')->all();
        $this->assertContains($realPhoto1, $extractedUrls);
        $this->assertContains($realPhoto2, $extractedUrls);
        $this->assertNotContains($avatarUrl, $extractedUrls);
        $this->assertNotContains('https://example.com/avatar_3.jpg', $extractedUrls);
        $this->assertNotContains('https://example.com/spam-photo.jpg', $extractedUrls);
    }

    public function test_service_filters_out_broken_media_links(): void
    {
        $service = app(ReviewMediaGalleryService::class);

        $healthyUrl = 'https://example.com/healthy-dune.jpg';
        $brokenUrl = 'https://example.com/broken-link-404.jpg';

        Http::fake([
            $healthyUrl => Http::response('image-bytes', 200),
            $brokenUrl => Http::response('Not Found', 404),
        ]);

        $this->assertTrue($service->isUrlHealthy($healthyUrl, true));
        $this->assertFalse($service->isUrlHealthy($brokenUrl, true));

        $testItems = [
            ['id' => '1', 'url' => $healthyUrl],
            ['id' => '2', 'url' => $brokenUrl],
        ];

        $healthyItems = $service->filterHealthyItems($testItems);
        $this->assertCount(1, $healthyItems);
        $this->assertEquals($healthyUrl, $healthyItems[0]['url']);
    }

    public function test_api_report_broken_endpoint_blacklists_url_and_invalidates_cache(): void
    {
        $service = app(ReviewMediaGalleryService::class);
        $testUrl = 'https://example.com/reported-broken-photo.jpg';

        // Pre-cache as healthy
        $cacheKey = 'gallery_media_status_' . md5($testUrl);
        Cache::put($cacheKey, true, 3600);
        $this->assertTrue((bool) Cache::get($cacheKey));

        $response = $this->postJson(route('api.gallery.report-broken'), [
            'url' => $testUrl,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // After reporting, the status must be false
        $this->assertFalse((bool) Cache::get($cacheKey));
    }

    public function test_gallery_page_renders_successfully_with_schema(): void
    {
        $photoUrl = 'https://example.com/safari-guest-shot.jpg';

        Http::fake([
            $photoUrl => Http::response('image-content', 200),
        ]);

        Review::create([
            'source' => 'google',
            'reviewer_name' => 'Elena Rostova',
            'rating' => 5,
            'review_title' => 'Sunset dune experience',
            'review_text' => 'The sunset camel ride was magical and food was delicious.',
            'published_date' => now()->subDay(),
            'status' => 'approved',
            'photos' => [$photoUrl],
        ]);

        $response = $this->get(route('gallery.index'));

        $response->assertOk();
        $response->assertSee('Captured in the Dunes by Our Travelers');
        $response->assertSee('Elena Rostova');
        $response->assertSee('Sunset &amp; Camels', false);
        $response->assertSee('ImageGallery');
    }

    public function test_home_page_renders_guest_gallery_section(): void
    {
        $photoUrl = 'https://example.com/home-gallery-test.jpg';

        Http::fake([
            $photoUrl => Http::response('image-content', 200),
        ]);

        Review::create([
            'source' => 'google',
            'reviewer_name' => 'Michael Chang',
            'rating' => 5,
            'review_title' => 'Unbelievable buggy ride',
            'review_text' => 'The 1000cc buggy tour across red dunes was exhilarating!',
            'published_date' => now()->subDay(),
            'status' => 'approved',
            'photos' => [$photoUrl],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Captured in the Dunes by Our Travelers');
        $response->assertSee('Michael Chang');
    }

    public function test_tour_page_renders_guest_gallery_section(): void
    {
        $photoUrl = 'https://example.com/tour-guest-action.jpg';

        Http::fake([
            $photoUrl => Http::response('image-content', 200),
        ]);

        $category = Category::create([
            'name' => 'Desert Safari',
            'slug' => 'desert-safari',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tour = Tour::create([
            'category_id' => $category->id,
            'name' => 'Evening Red Dunes Desert Safari',
            'slug' => 'evening-desert-safari-dubai',
            'status' => 'active',
            'rating' => 4.9,
            'review_count' => 120,
        ]);

        Review::create([
            'source' => 'tripadvisor',
            'reviewer_name' => 'Sarah Connor',
            'rating' => 5,
            'review_title' => 'Evening Desert Safari BBQ',
            'review_text' => 'The live show and BBQ dinner in the desert camp was 5 stars.',
            'published_date' => now()->subDays(3),
            'status' => 'approved',
            'photos' => [$photoUrl],
        ]);

        $response = $this->get(route('tours.show', ['slug' => $tour->slug]));

        $response->assertOk();
        $response->assertSee('Guest Photos');
        $response->assertSee('Sarah Connor');
    }

    public function test_verify_gallery_media_command_runs_successfully(): void
    {
        $photoUrl = 'https://example.com/cmd-verified-photo.jpg';

        Http::fake([
            $photoUrl => Http::response('image-data', 200),
        ]);

        Review::create([
            'source' => 'google',
            'reviewer_name' => 'David Miller',
            'rating' => 5,
            'status' => 'approved',
            'photos' => [$photoUrl],
        ]);

        $exitCode = Artisan::call('gallery:verify-media', ['--force' => true]);

        $this->assertEquals(0, $exitCode);
    }
}
