<?php

namespace Tests\Feature;

use App\Models\Backlink;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BacklinkTrackingAndSyndicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Cache::flush();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@dunesdiscoverytourism.com'],
            ['name' => 'Admin Manager', 'password' => bcrypt('password123')]
        );
    }

    /**
     * Test Backlink model creation, URL normalization, and tracking URL builder.
     */
    public function test_backlink_model_creation_and_attributes(): void
    {
        $backlink = Backlink::create([
            'source_url' => 'https://www.tripadvisor.com/Attraction_Review-Dubai-Desert-Safari',
            'target_url' => 'https://dunesdiscoverytourism.com/evening-desert-safari-dubai',
            'anchor_text' => 'Dunes Discovery Tourism Dubai',
            'rel_type' => 'dofollow',
            'status' => 'active',
            'domain_authority' => 93,
            'platform' => 'travel_directory',
            'partner_name' => 'TripAdvisor Partner',
            'campaign' => 'organic_listings',
        ]);

        $this->assertDatabaseHas('backlinks', [
            'id' => $backlink->id,
            'referring_domain' => 'tripadvisor.com',
            'link_type' => 'dofollow',
            'status' => 'active',
            'domain_authority_est' => 93,
        ]);

        $this->assertEquals('tripadvisor.com', $backlink->source_domain);
        $this->assertEquals('dofollow', $backlink->rel_type);
        $this->assertEquals(93, $backlink->domain_authority);

        $trackingUrl = $backlink->buildTrackingUrl(['campaign' => 'spring_promo']);
        $this->assertStringContainsString('utm_source=tripadvisor_com', $trackingUrl);
        $this->assertStringContainsString('utm_medium=referral', $trackingUrl);
        $this->assertStringContainsString('utm_campaign=spring_promo', $trackingUrl);
    }

    /**
     * Test referral tracking middleware increments visits and sets 30-day cookie.
     */
    public function test_referral_tracking_middleware_captures_utm_source_and_sets_cookie(): void
    {
        $backlink = Backlink::create([
            'source_url' => 'https://www.uaetraveler.com/safari-guides',
            'target_url' => 'https://dunesdiscoverytourism.com/evening-desert-safari-dubai',
            'anchor_text' => 'Evening Desert Safari Booking',
            'rel_type' => 'dofollow',
            'status' => 'active',
            'utm_source' => 'uaetraveler_com',
            'campaign' => 'blogger_network',
        ]);

        $response = $this->get('/?utm_source=uaetraveler_com&utm_campaign=blogger_network');

        $response->assertOk();
        $response->assertCookie('dunes_backlink_ref', (string) $backlink->id);

        $this->assertEquals(1, $backlink->fresh()->referral_visits);

        // Same session debounce: second visit in same session does not increment visits twice
        $this->get('/?utm_source=uaetraveler_com&utm_campaign=blogger_network');
        $this->assertEquals(1, $backlink->fresh()->referral_visits);
    }

    /**
     * Test booking attribution links backlink_id and increments attributed revenue.
     */
    public function test_booking_attribution_links_backlink_and_increments_revenue(): void
    {
        $backlink = Backlink::create([
            'source_url' => 'https://dubaitravelblog.com/best-desert-safari',
            'target_url' => 'https://dunesdiscoverytourism.com/tours',
            'anchor_text' => 'Book Direct & Save',
            'rel_type' => 'dofollow',
            'status' => 'active',
        ]);

        $tour = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'short_desc' => 'Iconic desert safari experience.',
            'status' => 'active',
            'starting_price' => 150.00,
            'duration' => '6 Hours',
        ]);

        // Direct Booking creation with attributed backlink
        $booking = Booking::create([
            'reference' => 'DDT' . date('ymd') . 'TEST1',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'name' => 'John Traveler',
            'email' => 'john.traveler@example.com',
            'phone' => '+971501234567',
            'adults' => 2,
            'pickup_location' => 'Marina Dubai',
            'subtotal' => 300.00,
            'total' => 300.00,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'backlink_id' => $backlink->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'email' => 'john.traveler@example.com',
            'backlink_id' => $backlink->id,
        ]);

        // Increment backlink conversions
        $backlink->increment('bookings_count');
        $backlink->increment('revenue_generated_aed', 300.00);

        $this->assertEquals(1, $backlink->fresh()->bookings_count);
        $this->assertEquals(300.00, $backlink->fresh()->attributed_revenue_aed);
        $this->assertEquals(1, $backlink->bookings()->count());
    }

    /**
     * Test public syndication REST API structure, timings, DET license, and schema.
     */
    public function test_public_syndication_api_endpoint_structure_and_timings(): void
    {
        Tour::create([
            'name' => 'Morning Desert Safari Dubai',
            'slug' => 'morning-desert-safari-dubai',
            'short_desc' => 'Dune bashing, sandboarding and camel ride in sunrise.',
            'status' => 'active',
            'starting_price' => 120.00,
            'duration' => '4 Hours',
            'priority' => 1,
        ]);

        $response = $this->getJson('/api/v1/syndication/tours');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'syndication_version',
            'operator' => [
                'name',
                'license_det',
                'portal_url',
                'official_support',
            ],
            'count',
            'tours' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'url',
                    'starting_price_aed',
                    'currency',
                    'duration',
                    'timings' => [
                        'pickup',
                        'dropoff',
                        'schedule_notes',
                    ],
                    'schema_org',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('1430583', $data['operator']['license_det']);
        
        // Find morning safari and assert authoritative timings
        $morningTour = collect($data['tours'])->firstWhere('slug', 'morning-desert-safari-dubai');
        $this->assertNotNull($morningTour);
        $this->assertEquals('07:00 AM – 07:30 AM', $morningTour['timings']['pickup']);
        $this->assertEquals('10:30 AM – 11:00 AM', $morningTour['timings']['dropoff']);
    }

    /**
     * Test filtering public syndication API by tour slug.
     */
    public function test_public_syndication_api_filter_by_tour_slug(): void
    {
        Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'short_desc' => 'Sunset dune drive, BBQ dinner, and shows.',
            'status' => 'active',
            'starting_price' => 150.00,
            'duration' => '6 Hours',
        ]);

        Tour::create([
            'name' => 'Quad Biking Dubai',
            'slug' => 'desert-safari-quad-biking-dubai',
            'short_desc' => 'High-powered ATV desert adventure.',
            'status' => 'active',
            'starting_price' => 200.00,
            'duration' => '3 Hours',
        ]);

        $response = $this->getJson('/api/v1/syndication/tours?tour=evening-desert-safari-dubai');

        $response->assertOk();
        $tours = $response->json('tours');
        $this->assertCount(1, $tours);
        $this->assertEquals('evening-desert-safari-dubai', $tours[0]['slug']);
        $this->assertEquals('02:30 PM – 03:00 PM', $tours[0]['timings']['pickup']);
        $this->assertEquals('09:00 PM – 09:30 PM', $tours[0]['timings']['dropoff']);
    }

    /**
     * Test public syndicated tours RSS 2.0 XML feed.
     */
    public function test_public_syndicated_tours_rss_feed_output(): void
    {
        Tour::create([
            'name' => 'Overnight Desert Safari Dubai',
            'slug' => 'overnight-desert-safari-dubai',
            'short_desc' => 'Sleep under the desert stars with morning breakfast.',
            'status' => 'active',
            'starting_price' => 350.00,
            'duration' => '16 Hours',
        ]);

        $response = $this->get('/feeds/tours.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $this->assertStringContainsString('<rss version="2.0"', $response->getContent());
        $this->assertStringContainsString('Overnight Desert Safari Dubai', $response->getContent());
        $this->assertStringContainsString('DET License #1430583', $response->getContent());
    }

    /**
     * Test embeddable vanilla JS widget file exists and is accessible.
     */
    public function test_embeddable_safari_card_widget_asset_exists(): void
    {
        $widgetPath = public_path('widgets/safari-card.js');
        $this->assertFileExists($widgetPath);

        $content = file_get_contents($widgetPath);
        $this->assertStringContainsString('Dunes Discovery Tourism - Embedded Tour Card Widget', $content);
        $this->assertStringContainsString('dunes-safari-widget', $content);
    }

    /**
     * Test Admin Backlinks Index and KPI calculations.
     */
    public function test_admin_backlinks_index_and_kpis(): void
    {
        Backlink::create([
            'source_url' => 'https://uaetouristguides.com/safari-ranking',
            'target_url' => 'https://dunesdiscoverytourism.com',
            'anchor_text' => 'Top Rated Safari Dubai',
            'rel_type' => 'dofollow',
            'status' => 'active',
            'referral_visits' => 24,
            'attributed_revenue_aed' => 600.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/backlinks');

        $response->assertOk();
        $response->assertViewIs('admin.backlinks.index');
        $response->assertSee('Backlink Tracking');
        $response->assertSee('uaetouristguides.com');
        $response->assertSee('Top Rated Safari Dubai');
        $response->assertSee('600.00');
    }

    /**
     * Test Admin Backlinks CRUD operations (store, update, destroy).
     */
    public function test_admin_backlink_store_update_and_destroy_crud(): void
    {
        // Store
        $storeResponse = $this->actingAs($this->adminUser)->post('/admin/backlinks', [
            'source_url' => 'https://travelmag.com/dubai-tours',
            'target_url' => 'https://dunesdiscoverytourism.com/tours',
            'anchor_text' => 'Authentic Desert Safari Experience',
            'rel_type' => 'dofollow',
            'status' => 'active',
            'domain_authority' => 68,
            'platform' => 'guest_post',
            'partner_name' => 'Editor TravelMag',
            'cost_aed' => 0.00,
            'campaign' => 'travelmag_feature',
            'notes' => 'Permanent guest article with lifetime dofollow link',
        ]);

        $storeResponse->assertRedirect('/admin/backlinks');
        $this->assertDatabaseHas('backlinks', [
            'referring_domain' => 'travelmag.com',
            'anchor_text' => 'Authentic Desert Safari Experience',
            'domain_authority_est' => 68,
        ]);

        $backlink = Backlink::where('referring_domain', 'travelmag.com')->first();

        // Update
        $updateResponse = $this->actingAs($this->adminUser)->put("/admin/backlinks/{$backlink->id}", [
            'source_url' => 'https://travelmag.com/dubai-tours-updated',
            'target_url' => 'https://dunesdiscoverytourism.com/evening-desert-safari-dubai',
            'anchor_text' => 'Best Dubai Safari 2026',
            'rel_type' => 'dofollow',
            'status' => 'active',
            'domain_authority' => 70,
            'platform' => 'guest_post',
        ]);

        $updateResponse->assertRedirect('/admin/backlinks');
        $this->assertDatabaseHas('backlinks', [
            'id' => $backlink->id,
            'anchor_text' => 'Best Dubai Safari 2026',
            'domain_authority_est' => 70,
        ]);

        // Destroy
        $deleteResponse = $this->actingAs($this->adminUser)->delete("/admin/backlinks/{$backlink->id}");
        $deleteResponse->assertRedirect('/admin/backlinks');
        $this->assertDatabaseMissing('backlinks', ['id' => $backlink->id]);
    }

    /**
     * Test single backlink verification endpoint with mocked HTTP.
     */
    public function test_admin_backlink_verify_health(): void
    {
        Http::fake([
            'https://partner-portal.com/reviews' => Http::response(
                '<html><body><p>Check out <a href="https://dunesdiscoverytourism.com/tours" rel="external">Dunes Safari</a></p></body></html>',
                200
            ),
        ]);

        $backlink = Backlink::create([
            'source_url' => 'https://partner-portal.com/reviews',
            'target_url' => 'https://dunesdiscoverytourism.com/tours',
            'rel_type' => 'dofollow',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/admin/backlinks/{$backlink->id}/verify");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'active',
            'http_status' => 200,
        ]);

        $this->assertEquals('active', $backlink->fresh()->status);
        $this->assertEquals(200, $backlink->fresh()->http_status_code);
        $this->assertNotNull($backlink->fresh()->last_checked_at);
    }

    /**
     * Test Syndication Hub admin view rendering.
     */
    public function test_admin_syndication_hub_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/backlinks/syndication');

        $response->assertOk();
        $response->assertViewIs('admin.backlinks.syndication');
        $response->assertSee('Content Syndication &amp; Embed Widget Hub', false);
        $response->assertSee('Embeddable Safari Card Generator');
        $response->assertSee('/api/v1/syndication/tours');
        $response->assertSee('/feeds/tours.xml');
    }

    /**
     * Test Artisan command seo:verify-backlinks runs successfully.
     */
    public function test_artisan_verify_backlinks_command(): void
    {
        Http::fake([
            '*' => Http::response('<html><body>No link</body></html>', 200),
        ]);

        Backlink::create([
            'source_url' => 'https://mock-partner.com/listing',
            'target_url' => 'https://dunesdiscoverytourism.com',
            'status' => 'pending',
        ]);

        $exitCode = Artisan::call('seo:verify-backlinks', ['--limit' => 5]);
        $this->assertEquals(0, $exitCode);
    }
}
