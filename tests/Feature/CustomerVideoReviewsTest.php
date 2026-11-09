<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use App\Services\ReviewMediaGalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CustomerVideoReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected ?string $originalJsonBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $jsonPath = database_path('seeders/data/customer_video_reviews.json');
        if (file_exists($jsonPath)) {
            $this->originalJsonBackup = file_get_contents($jsonPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalJsonBackup !== null) {
            $jsonPath = database_path('seeders/data/customer_video_reviews.json');
            file_put_contents($jsonPath, $this->originalJsonBackup);
        }
        parent::tearDown();
    }

    public function test_service_loads_customer_video_reviews(): void
    {
        $service = app(ReviewMediaGalleryService::class);
        $videos = $service->getCustomerVideoReviews();

        $this->assertCount(28, $videos);
        $first = $videos[0];
        $this->assertEquals('video', $first['type']);
        $this->assertNotEmpty($first['url']);
        $this->assertNotEmpty($first['poster_url']);
        $this->assertNotEmpty($first['transcript']);
        $this->assertNotEmpty($first['duration_iso']);
        $this->assertArrayHasKey('placements', $first);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+04:00$/', $first['upload_date_iso']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+04:00$/', $first['published_date']);
    }

    public function test_all_twenty_eight_video_reviews_are_valid_and_indexed(): void
    {
        $service = app(ReviewMediaGalleryService::class);
        $videos = $service->getCustomerVideoReviews();

        $this->assertCount(28, $videos);
        $ids = array_column($videos, 'id');
        for ($i = 1; $i <= 28; $i++) {
            $expectedId = sprintf('ddt_video_review_%02d', $i);
            $this->assertContains($expectedId, $ids, "Review {$expectedId} must be present");
        }

        // Verify newly added November 2026 reviews have valid fields and files
        $review17 = collect($videos)->firstWhere('id', 'ddt_video_review_17');
        $this->assertNotNull($review17);
        $this->assertEquals('evening-desert-safari-spanish-review-1.mp4', $review17['filename']);
        $this->assertEquals('Elena & Friends', $review17['reviewer_name']);
        $this->assertEquals('ES', $review17['reviewer_country_code']);

        $review28 = collect($videos)->firstWhere('id', 'ddt_video_review_28');
        $this->assertNotNull($review28);
        $this->assertEquals('evening-desert-safari-hotel-pickup-uk-review-1.mp4', $review28['filename']);
        $this->assertEquals('Claire & Daniel', $review28['reviewer_name']);
        $this->assertEquals('GB', $review28['reviewer_country_code']);
    }

    public function test_service_filters_homepage_and_tour_video_reviews(): void
    {
        $service = app(ReviewMediaGalleryService::class);
        
        $homeVideos = $service->getHomepageVideoReviews(8);
        $this->assertNotEmpty($homeVideos);
        $this->assertLessThanOrEqual(8, count($homeVideos));

        $buggyVideos = $service->getTourVideoReviews('dune-buggy-dubai', 4);
        $this->assertNotEmpty($buggyVideos);
        $this->assertLessThanOrEqual(4, count($buggyVideos));
    }

    public function test_gallery_page_renders_with_video_reviews_and_schema(): void
    {
        $response = $this->get(route('gallery.index', ['category' => 'videos']));

        $response->assertStatus(200);
        $response->assertSee('VideoObject');
        $response->assertSee('buggy-ride-dubai-desert-review-1.mp4');
        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/"uploadDate":\s*"[0-9]{4}-[0-9]{2}-[0-9]{2}"/', $content, 'uploadDate in gallery must include timezone');
    }

    public function test_homepage_renders_guest_video_reels_section_and_schema(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Real Customer Video Reviews');
        $response->assertSee('Watch Real Travelers in the Dubai Dunes');
        $response->assertSee('View All 28 Videos');
        $response->assertSee('evening-desert-safari-hotel-pickup-uk-review-1.mp4');
        $response->assertSee('VideoObject');
        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/"uploadDate":\s*"[0-9]{4}-[0-9]{2}-[0-9]{2}"/', $content, 'uploadDate on homepage must include timezone');
        $this->assertStringContainsString('+04:00', $content, 'Homepage VideoObjects must contain +04:00 timezone offset');
    }

    public function test_tour_detail_page_renders_guest_video_reviews_section(): void
    {
        $tour = Tour::create([
            'name' => 'Dune Buggy Dubai Desert Safari',
            'slug' => 'dune-buggy-rental-dubai',
            'status' => 'active',
            'rating' => 5.0,
            'review_count' => 150,
            'hero_image' => 'images/evening-desert-safari.avif',
        ]);

        $response = $this->get('/'.$tour->slug);

        $response->assertStatus(200);
        $response->assertSee('Guest Video Reviews: Dune Buggy Dubai Desert Safari');
        $response->assertSee('VideoObject');
        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/"uploadDate":\s*"[0-9]{4}-[0-9]{2}-[0-9]{2}"/', $content, 'uploadDate on tour page must include timezone');
    }

    public function test_google_video_sitemap_returns_valid_xml_with_video_tags(): void
    {
        $response = $this->get('/sitemap-videos.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $response->assertSee('xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"', false);
        $response->assertSee('<video:video>', false);
        $response->assertSee('<video:thumbnail_loc>', false);
        $response->assertSee('<video:content_loc>', false);
        $response->assertSee('buggy-ride-dubai-desert-review-1.mp4');

        // Test alias route
        $aliasResponse = $this->get('/video-sitemap.xml');
        $aliasResponse->assertStatus(200);
    }

    public function test_admin_video_reviews_requires_auth(): void
    {
        $response = $this->get(route('admin.video-reviews.index'));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_and_manage_video_reviews(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.video-reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Video Reviews Management');
        $response->assertSee('Total Videos');
        $response->assertSee('buggy-ride-dubai-desert-review-1.mp4');
    }

    public function test_admin_can_toggle_placement_via_ajax(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/admin/video-reviews/ddt_video_review_01/toggle-placement', [
            'placement' => 'home',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'placement' => 'home',
        ]);
    }

    public function test_navigation_header_renders_gallery_link(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(localized_route('gallery.index'));
    }

    public function test_gallery_renders_photos_and_dedicated_video_reels_section(): void
    {
        $response = $this->get(route('gallery.index'));

        $response->assertStatus(200);
        $response->assertSee('Authentic Guest Photo Gallery');
        $response->assertSee('guest-video-reels');
        $response->assertSee('Watch Unfiltered Guest Stories');
    }

    public function test_analyze_endpoint_requires_auth(): void
    {
        $response = $this->postJson(route('admin.video-reviews.analyze'), []);
        $response->assertStatus(401);
    }

    public function test_analyze_endpoint_validates_required_input(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.video-reviews.analyze'), []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_analyze_endpoint_analyzes_existing_library_video_and_autofills(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.video-reviews.analyze'), [
            'existing_filename' => 'evening-desert-safari-spanish-review-1.mp4',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'video_filename',
                'poster_filename',
                'poster_url',
                'poster_jpg_url',
                'title',
                'reviewer_name',
                'reviewer_country',
                'reviewer_country_code',
                'category',
                'tour_slug',
                'tour_name',
                'rating',
                'duration',
                'duration_iso',
                'transcript',
                'english_translation',
                'caption',
                'description',
                'location',
                'keywords',
                'placements' => ['home', 'gallery', 'tour'],
                'is_active',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('evening-desert-safari-spanish-review-1.mp4', $data['video_filename']);
        $this->assertNotEmpty($data['title']);
        $this->assertNotEmpty($data['reviewer_name']);
        $this->assertEquals(5.0, $data['rating']);
        $this->assertGreaterThan(0, $data['duration']);
        $this->assertStringContainsString('PT', $data['duration_iso']);
        $this->assertNotEmpty($data['poster_filename']);
    }

    public function test_admin_can_publish_video_review_from_autofilled_data(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.video-reviews.store'), [
            'title' => 'VIP Dune Buggy Sunset Tour Review - Dunes Discovery Tourism',
            'reviewer_name' => 'Alexander & Group',
            'reviewer_country' => 'Germany',
            'reviewer_country_code' => 'DE',
            'category' => 'buggy_quad',
            'tour_slug' => 'dune-buggy-dubai',
            'rating' => 5.0,
            'duration' => 45,
            'caption' => 'The most exhilarating experience in the dunes! Highly recommended.',
            'description' => 'German travelers reviewing their thrilling Polaris dune buggy adventure with Dunes Discovery Tourism in Dubai.',
            'transcript' => 'Das war ein fantastisches Erlebnis in den Dünen von Dubai!',
            'location' => 'Lahbab Red Dunes, Dubai, UAE',
            'keywords' => 'dubai dune buggy, polaris rzr dubai, german review, desert safari',
            'existing_filename' => 'evening-desert-safari-spanish-review-1.mp4',
            'existing_poster' => 'evening-desert-safari-spanish-review-1.webp',
            'placement_home' => '1',
            'placement_gallery' => '1',
            'placement_tour' => '1',
        ]);

        $response->assertRedirect(route('admin.video-reviews.index'));
        $response->assertSessionHas('success');

        // Verify record was appended
        $records = json_decode(file_get_contents(database_path('seeders/data/customer_video_reviews.json')), true);
        $last = end($records);
        $this->assertEquals('VIP Dune Buggy Sunset Tour Review - Dunes Discovery Tourism', $last['title']);
        $this->assertEquals('Alexander & Group', $last['reviewer_name']);
        $this->assertEquals('DE', $last['reviewer_country_code']);
    }
}
