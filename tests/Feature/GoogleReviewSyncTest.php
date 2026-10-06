<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\GoogleReviewSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleReviewSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['setting_key' => 'google_place_id'],
            ['setting_value' => 'ChIJbWsIEIVEdEER4uHEhb2dbcQ']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'google_maps_api_key'],
            ['setting_value' => 'test-api-key-12345']
        );
    }

    public function test_service_normalizes_and_upserts_google_reviews(): void
    {
        $service = app(GoogleReviewSyncService::class);

        $rawReview = [
            'author_name' => 'Alexander Wright',
            'profile_photo_url' => 'https://example.com/avatar.jpg',
            'author_url' => 'https://maps.google.com/contrib/alexander',
            'rating' => 5,
            'text' => 'The evening desert safari exceeded all expectations! Outstanding dune bashing and great dinner.',
            'time' => 1760000000,
        ];

        $normalized = $service->normalizeReview($rawReview);

        $this->assertNotNull($normalized);
        $this->assertEquals('Alexander Wright', $normalized['reviewer_name']);
        $this->assertEquals(5.0, $normalized['rating']);
        $this->assertEquals('approved', $normalized['status']);
        $this->assertTrue($normalized['is_featured']);
        $this->assertNotEmpty($normalized['source_review_id']);
    }

    public function test_service_executes_sync_and_updates_database_and_cache(): void
    {
        Cache::flush();

        // Mock Google Places API response
        Http::fake([
            'https://places.googleapis.com/*' => Http::response([
                'displayName' => ['text' => 'Dunes Discovery Tourism LLC'],
                'rating' => 4.9,
                'userRatingCount' => 1520,
                'reviews' => [
                    [
                        'name' => 'places/ChIJbWsIEIVEdEER4uHEhb2dbcQ/reviews/rev_test_1',
                        'rating' => 5,
                        'text' => ['text' => 'Incredible red dunes adventure with exceptional service!'],
                        'authorAttribution' => [
                            'displayName' => 'Sarah Connor',
                            'photoUri' => 'https://example.com/sarah.jpg',
                            'uri' => 'https://maps.google.com/contrib/sarah',
                        ],
                        'publishTime' => '2026-10-05T10:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        $service = app(GoogleReviewSyncService::class);
        $result = $service->sync(true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['added']);
        $this->assertEquals(1, $result['total']);

        // Verify review exists in database
        $this->assertDatabaseHas('reviews', [
            'source' => 'google',
            'reviewer_name' => 'Sarah Connor',
            'rating' => 5.0,
            'status' => 'approved',
        ]);

        // Verify last synced setting was stored
        $this->assertNotNull(Setting::where('setting_key', 'google_reviews_last_synced_at')->value('setting_value'));
    }

    public function test_service_respects_rate_limit_cooldown(): void
    {
        Cache::flush();

        $service = app(GoogleReviewSyncService::class);

        // First sync with force=true sets cooldown
        $result1 = $service->sync(true);
        $this->assertTrue($result1['success']);

        // Immediate second sync without force should be rate-limited
        $result2 = $service->sync(false);
        $this->assertFalse($result2['success']);
        $this->assertTrue($result2['rate_limited']);
        $this->assertStringContainsString('Rate limit', $result2['message']);
    }

    public function test_artisan_command_syncs_google_reviews(): void
    {
        Cache::flush();

        $this->artisan('reviews:sync-google --force')
            ->expectsOutputToContain('Starting automated Google Reviews synchronization...')
            ->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'source' => 'google',
        ]);
    }

    public function test_admin_sync_endpoint_requires_authentication(): void
    {
        $response = $this->post(route('admin.reviews.sync-google'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_trigger_sync_endpoint(): void
    {
        Cache::flush();

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.reviews.sync-google'), [
            'force' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'added',
            'updated',
            'total',
            'last_synced_at',
            'message',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_reviews_index_displays_sync_button_and_badges(): void
    {
        $admin = User::factory()->create();

        // Create reviews from multiple sources
        Review::create([
            'source' => 'google',
            'source_review_id' => 'g_test_101',
            'reviewer_name' => 'John Google',
            'rating' => 5,
            'review_title' => 'Google Review Title',
            'review_text' => 'Loved the trip!',
            'status' => 'approved',
            'is_featured' => true,
            'published_date' => now()->toDateString(),
            'imported_at' => now(),
        ]);

        Review::create([
            'source' => 'tripadvisor',
            'source_review_id' => 'ta_test_102',
            'reviewer_name' => 'Alice TripAdvisor',
            'rating' => 5,
            'review_title' => 'TripAdvisor Review Title',
            'review_text' => 'Great dune bashing!',
            'status' => 'approved',
            'is_featured' => true,
            'published_date' => now()->toDateString(),
            'imported_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('Sync Google Reviews');
        $response->assertSee('syncGoogleReviewsBtn');
        $response->assertSee('bi bi-google');
        $response->assertSee('bi bi-compass-fill');
        $response->assertSee('John Google');
        $response->assertSee('Alice TripAdvisor');
    }

    public function test_home_page_smart_selection_includes_high_quality_google_reviews(): void
    {
        Cache::flush();

        // Create an unfeatured 5-star Google review
        Review::create([
            'source' => 'google',
            'source_review_id' => 'g_unfeatured_1',
            'reviewer_name' => 'Unfeatured Google Reviewer',
            'rating' => 5,
            'review_title' => 'Awesome safari',
            'review_text' => 'High quality desert experience with friendly driver Malik.',
            'status' => 'approved',
            'is_featured' => false,
            'published_date' => now()->toDateString(),
            'imported_at' => now(),
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Verify it was rendered on the home page
        $response->assertSee('Unfeatured Google Reviewer');

        // Verify cached in site_social_proof_feed
        $cached = Cache::get('site_social_proof_feed');
        $this->assertNotNull($cached);
        $this->assertTrue($cached->contains('reviewer_name', 'Unfeatured Google Reviewer'));
    }
}
