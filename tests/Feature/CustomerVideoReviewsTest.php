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

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_service_loads_sixteen_customer_video_reviews(): void
    {
        $service = app(ReviewMediaGalleryService::class);
        $videos = $service->getCustomerVideoReviews();

        $this->assertCount(16, $videos);
        $first = $videos[0];
        $this->assertEquals('video', $first['type']);
        $this->assertNotEmpty($first['url']);
        $this->assertNotEmpty($first['poster_url']);
        $this->assertNotEmpty($first['transcript']);
        $this->assertNotEmpty($first['duration_iso']);
        $this->assertArrayHasKey('placements', $first);
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
    }

    public function test_homepage_renders_guest_video_reels_section_and_schema(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Real Customer Video Reviews');
        $response->assertSee('Watch Real Travelers in the Dubai Dunes');
        $response->assertSee('VideoObject');
    }

    public function test_tour_detail_page_renders_guest_video_reviews_section(): void
    {
        $tour = Tour::create([
            'name' => 'Dune Buggy Dubai Desert Safari',
            'slug' => 'dune-buggy-dubai',
            'status' => 'active',
            'rating' => 5.0,
            'review_count' => 150,
            'hero_image' => 'images/evening-desert-safari.avif',
        ]);

        $response = $this->get('/dune-buggy-dubai');

        $response->assertStatus(200);
        $response->assertSee('Guest Video Reviews: Dune Buggy Dubai Desert Safari');
        $response->assertSee('VideoObject');
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
}
