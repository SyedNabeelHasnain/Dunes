<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\TripAdvisorReviewSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripAdvisorReviewSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['setting_key' => 'tripadvisor_location_id'],
            ['setting_value' => '29026644']
        );
        Setting::updateOrCreate(
            ['setting_key' => 'tripadvisor_api_key'],
            ['setting_value' => 'test-ta-api-key-99999']
        );
    }

    public function test_service_normalizes_tripadvisor_api_format_reviews(): void
    {
        $service = app(TripAdvisorReviewSyncService::class);

        $rawApiReview = [
            'id' => 1045272087,
            'lang' => 'en',
            'location_id' => 29026644,
            'published_date' => '2026-01-07T14:30:00Z',
            'rating' => 5,
            'title' => 'Best experience ever with Malik',
            'text' => 'Highly recommended! He picks you up from your hotel and drops you off. Always making sure you are comfortable.',
            'url' => 'https://www.tripadvisor.com/ShowUserReviews-g295424-d29026644-r1045272087.html',
            'user' => [
                'username' => 'Petra T',
                'user_location' => ['name' => 'London, UK'],
                'avatar' => [
                    'medium' => 'https://example.com/avatar_petra.jpg',
                ],
            ],
            'owner_response' => [
                'text' => 'Thank you Petra for choosing Dunes Discovery Tourism LLC!',
                'published_date' => '2026-01-08T10:00:00Z',
            ],
        ];

        $normalized = $service->normalizeReview($rawApiReview);

        $this->assertNotNull($normalized);
        $this->assertEquals('1045272087', $normalized['source_review_id']);
        $this->assertEquals('Petra T', $normalized['reviewer_name']);
        $this->assertEquals('https://example.com/avatar_petra.jpg', $normalized['reviewer_avatar_url']);
        $this->assertEquals(5.0, $normalized['rating']);
        $this->assertEquals('Best experience ever with Malik', $normalized['review_title']);
        $this->assertEquals('approved', $normalized['status']);
        $this->assertTrue($normalized['is_featured']);
        $this->assertEquals('2026-01-07', $normalized['published_date']);
        $this->assertEquals('Thank you Petra for choosing Dunes Discovery Tourism LLC!', $normalized['owner_response_text']);
    }

    public function test_service_normalizes_seeder_format_reviews(): void
    {
        $service = app(TripAdvisorReviewSyncService::class);

        $rawSeederReview = [
            'source' => 'tripadvisor',
            'source_review_id' => 1044304899,
            'reviewer_name' => 'David M',
            'reviewer_avatar_url' => 'images/avatars/avatar_2.jpg',
            'reviewer_profile_url' => 'https://www.tripadvisor.com/Profile/davidm',
            'review_url' => 'https://www.tripadvisor.com/ShowUserReviews-g295424-d29026644-r1044304899.html',
            'rating' => 5,
            'review_title' => 'Spectacular desert sunset',
            'review_text' => 'We did the evening VIP desert safari with dune buggies. Malik was an outstanding host and driver!',
            'published_date' => '2026-01-02',
            'status' => 'approved',
            'is_featured' => 1,
        ];

        $normalized = $service->normalizeReview($rawSeederReview);

        $this->assertNotNull($normalized);
        $this->assertEquals('1044304899', $normalized['source_review_id']);
        $this->assertEquals('David M', $normalized['reviewer_name']);
        $this->assertEquals('images/avatars/avatar_2.jpg', $normalized['reviewer_avatar_url']);
        $this->assertEquals(5.0, $normalized['rating']);
        $this->assertEquals('approved', $normalized['status']);
        $this->assertTrue($normalized['is_featured']);
        $this->assertEquals('2026-01-02', $normalized['published_date']);
    }

    public function test_service_executes_sync_and_updates_database_and_cache(): void
    {
        Cache::flush();

        // Mock TripAdvisor Content API details and reviews endpoints
        Http::fake([
            'https://api.content.tripadvisor.com/api/v1/location/*/details*' => Http::response([
                'location_id' => '29026644',
                'name' => 'Dunes Discovery Tourism LLC',
                'web_url' => 'https://www.tripadvisor.com/Attraction_Review-g295424-d29026644-Reviews-Dunes_Discovery.html',
                'rating' => '5.0',
                'num_reviews' => '78',
            ], 200),
            'https://api.content.tripadvisor.com/api/v1/location/*/reviews*' => Http::response([
                'data' => [
                    [
                        'id' => 99887766,
                        'published_date' => '2026-10-06T10:00:00Z',
                        'rating' => 5,
                        'title' => 'Unbelievable Dune Bashing Experience',
                        'text' => 'The quad bikes, sandboarding, and live evening show were unbelievable! Highly recommend Dunes Discovery.',
                        'url' => 'https://www.tripadvisor.com/ShowUserReviews-g295424-d29026644-r99887766.html',
                        'user' => [
                            'username' => 'Catherine Bell',
                            'avatar' => [
                                'medium' => 'https://example.com/catherine.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(TripAdvisorReviewSyncService::class);
        $result = $service->sync(true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['added']);
        $this->assertEquals(1, $result['total']);

        // Verify review stored in database
        $this->assertDatabaseHas('reviews', [
            'source' => 'tripadvisor',
            'source_review_id' => '99887766',
            'reviewer_name' => 'Catherine Bell',
            'rating' => 5.0,
            'status' => 'approved',
        ]);

        // Verify last synced setting was stored
        $this->assertNotNull(Setting::where('setting_key', 'tripadvisor_last_synced_at')->value('setting_value'));
        $this->assertEquals('5.0', Setting::where('setting_key', 'tripadvisor_rating')->value('setting_value'));
    }

    public function test_service_fallback_to_verified_catalog_when_no_api_key(): void
    {
        Cache::flush();

        // Clear API key to force fallback behavior
        Setting::where('setting_key', 'tripadvisor_api_key')->delete();
        config(['services.tripadvisor.api_key' => null]);
        putenv('TRIPADVISOR_API_KEY=');

        $service = app(TripAdvisorReviewSyncService::class);
        $result = $service->sync(true);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['total']);
        $this->assertDatabaseHas('reviews', [
            'source' => 'tripadvisor',
        ]);
    }

    public function test_service_respects_rate_limit_cooldown(): void
    {
        Cache::flush();

        $service = app(TripAdvisorReviewSyncService::class);

        // First sync with force=true sets cooldown
        $result1 = $service->sync(true);
        $this->assertTrue($result1['success']);

        // Immediate second sync without force should be rate-limited
        $result2 = $service->sync(false);
        $this->assertFalse($result2['success']);
        $this->assertTrue($result2['rate_limited']);
        $this->assertStringContainsString('Rate limit', $result2['message']);
    }

    public function test_artisan_command_syncs_tripadvisor_reviews(): void
    {
        Cache::flush();

        $this->artisan('reviews:sync-tripadvisor --force')
            ->expectsOutputToContain('Starting automated TripAdvisor Reviews synchronization...')
            ->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'source' => 'tripadvisor',
        ]);
    }

    public function test_admin_sync_endpoint_requires_authentication(): void
    {
        $response = $this->post(route('admin.reviews.sync-tripadvisor'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_trigger_sync_endpoint(): void
    {
        Cache::flush();

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.reviews.sync-tripadvisor'), [
            'force' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'added',
            'updated',
            'total',
            'rating',
            'reviews_count',
            'last_synced_at',
            'message',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_reviews_index_displays_tripadvisor_sync_button(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('id="syncTripAdvisorReviewsBtn"', false);
        $response->assertSee('Sync TripAdvisor', false);
    }
}
