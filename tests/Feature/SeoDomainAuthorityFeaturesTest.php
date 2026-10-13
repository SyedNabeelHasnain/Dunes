<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappInquiry;
use App\Services\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoDomainAuthorityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test Sunset & Golden Hour Guide renders with 200 OK, astronomical schedule, and structured schema.
     */
    public function test_sunset_guide_page_renders_with_schema_and_astronomical_data(): void
    {
        $response = $this->get('/dubai-desert-sunset-times');

        $response->assertStatus(200);
        $response->assertViewIs('pages.sunset-guide');
        $response->assertSee('Dubai Desert Sunset Times');
        $response->assertSee('Lahbab Red Dunes');
        $response->assertSee('WebApplication');
        $response->assertSee('FAQPage');
        $response->assertSee('BreadcrumbList');
    }

    /**
     * Test Press Kit page renders with 200 OK, fact sheet, and embeddable partner badges.
     */
    public function test_press_kit_page_renders_with_brand_assets_and_badges(): void
    {
        $response = $this->get('/press-kit');

        $response->assertStatus(200);
        $response->assertViewIs('pages.press-kit');
        $response->assertSee('Press Kit');
        $response->assertSee('Official Fact Sheet');
        $response->assertSee('1430583'); // DET license number
        $response->assertSee('badges/verified-safari-partner.svg');
        $response->assertSee('badges/top-rated-operator.svg');
        $response->assertSee('badges/det-licensed-badge.svg');
    }

    /**
     * Test that all 3 public partner SVG badge files exist on disk and contain valid SVG markup.
     */
    public function test_partner_badges_svg_files_exist_and_are_valid_svg(): void
    {
        $badges = [
            'public/badges/verified-safari-partner.svg',
            'public/badges/top-rated-operator.svg',
            'public/badges/det-licensed-badge.svg',
        ];

        foreach ($badges as $badgePath) {
            $this->assertFileExists(base_path($badgePath));
            $content = File::get(base_path($badgePath));
            $this->assertStringContainsString('<svg', $content);
            $this->assertStringContainsString('</svg>', $content);
        }
    }

    /**
     * Test Blog RSS syndication feeds return valid XML with content and channel.
     */
    public function test_blog_rss_feed_endpoints_return_valid_xml(): void
    {
        // Seed a sample published blog post
        BlogPost::create([
            'title' => 'Top Sunset Photography Tips in Lahbab Red Dunes',
            'slug' => 'top-sunset-photography-tips-lahbab-red-dunes',
            'excerpt' => 'Discover the best camera settings for golden hour safari photography.',
            'content' => '<p>Golden hour in Dubai desert creates majestic crimson shadows across the dunes.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response1 = $this->get('/blog/feed.xml');
        $response1->assertStatus(200);
        $this->assertStringContainsString('xml', $response1->headers->get('Content-Type'));
        $response1->assertSee('<rss version="2.0"', false);
        $response1->assertSee('Top Sunset Photography Tips', false);

        $response2 = $this->get('/feed.xml');
        $response2->assertStatus(200);
        $this->assertStringContainsString('xml', $response2->headers->get('Content-Type'));
        $response2->assertSee('<rss version="2.0"', false);
    }

    /**
     * Test IndexNow search engine domain verification key endpoint.
     */
    public function test_indexnow_verification_key_endpoint(): void
    {
        $key = '9f2b87e2467d4ab8a9b31d871e4f9b88';

        // Valid key request should return 200 with key content
        $response = $this->get("/{$key}.txt");
        $response->assertStatus(200);
        $this->assertEquals($key, trim($response->getContent()));

        // Invalid key request should return 404
        $invalidResponse = $this->get('/unknown_fake_key.txt');
        $invalidResponse->assertStatus(404);
    }

    /**
     * Test IndexNowService sends HTTP payload to api.indexnow.org.
     */
    public function test_indexnow_service_submits_payload_to_api(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'OK'], 200),
        ]);

        $service = app(IndexNowService::class);
        $result = $service->submit(['https://dunesdiscoverytourism.com/dubai-desert-sunset-times']);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals(1, $result['count']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.indexnow.org/indexnow'
                && isset($request['urlList'])
                && in_array('https://dunesdiscoverytourism.com/dubai-desert-sunset-times', $request['urlList']);
        });
    }

    /**
     * Test IndexNow Artisan console command.
     */
    public function test_indexnow_artisan_command(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'OK'], 200),
        ]);

        $this->artisan('seo:indexnow --all')
            ->assertExitCode(0);
    }

    /**
     * Test IndexNowService handles SiteVerificationNotCompleted gracefully.
     */
    public function test_indexnow_service_handles_site_verification_not_completed_gracefully(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response([
                'errorCode' => 'SiteVerificationNotCompleted',
                'message' => 'Site Verification is not completed. Please wait for some time for the verification to complete and try again.',
                'details' => null,
            ], 403),
        ]);

        $service = app(IndexNowService::class);
        $result = $service->submit(['https://dunesdiscoverytourism.com/tours']);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['is_verification_pending']);
        $this->assertEquals(403, $result['status']);
        $this->assertStringContainsString('Site verification is in progress', $result['message']);
        $this->assertStringContainsString('dunesdiscoverytourism.com', $result['message']);
    }

    /**
     * Test IndexNowService guarantees absolute HTTPS URLs and physical verification key file.
     */
    public function test_indexnow_service_guarantees_https_host_and_key_file(): void
    {
        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response(['message' => 'OK'], 200),
        ]);

        $service = app(IndexNowService::class);
        $this->assertEquals('dunesdiscoverytourism.com', $service->getHost());
        $this->assertEquals('https://dunesdiscoverytourism.com/' . $service->getKey() . '.txt', $service->getKeyLocation());

        $service->ensureKeyFileExists();
        $filePath = public_path($service->getKey() . '.txt');
        $this->assertFileExists($filePath);
        $this->assertEquals($service->getKey(), trim((string) file_get_contents($filePath)));

        // Submit relative and localhost URLs, assert they normalize to https://dunesdiscoverytourism.com
        $service->submit(['/about', 'http://localhost/tours']);

        Http::assertSent(function ($request) {
            return $request['host'] === 'dunesdiscoverytourism.com'
                && $request['keyLocation'] === 'https://dunesdiscoverytourism.com/9f2b87e2467d4ab8a9b31d871e4f9b88.txt'
                && in_array('https://dunesdiscoverytourism.com/about', $request['urlList'])
                && in_array('https://dunesdiscoverytourism.com/tours', $request['urlList']);
        });
    }

    /**
     * Test Gallery page includes BreadcrumbList schema and deep-link handlers.
     */
    public function test_gallery_deep_linking_and_breadcrumb_schema(): void
    {
        $response = $this->get('/gallery');

        $response->assertStatus(200);
        $response->assertSee('BreadcrumbList');
        $response->assertSee('currentShareUrl');
        $response->assertSee('copyShareLink');
    }

    /**
     * Test XML sitemap includes the new high-value pages.
     */
    public function test_sitemap_includes_sunset_guide_and_press_kit(): void
    {
        $response = $this->get('/sitemap-pages.xml');

        $response->assertStatus(200);
        $response->assertSee('/dubai-desert-sunset-times');
        $response->assertSee('/press-kit');
    }

    /**
     * Test Admin SEO settings view includes all new controls and can update them.
     */
    public function test_admin_seo_settings_includes_sunset_press_and_indexnow_keys(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/settings/seo');
        $response->assertStatus(200);
        $response->assertSee('seo_sunset_title');
        $response->assertSee('seo_press_title');
        $response->assertSee('indexnow_key');
        $response->assertSee('indexnow_enabled');

        // Test updating SEO settings
        $updateResponse = $this->actingAs($admin)->post('/admin/settings/update', [
            'seo_sunset_title' => 'Custom Sunset Title',
            'seo_press_title' => 'Custom Press Title',
            'indexnow_key' => 'customkey1234567890abcdef123456',
            'indexnow_form_submitted' => '1',
            'indexnow_enabled' => '1',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('Custom Sunset Title', Setting::where('setting_key', 'seo_sunset_title')->value('setting_value'));
        $this->assertEquals('Custom Press Title', Setting::where('setting_key', 'seo_press_title')->value('setting_value'));

        @unlink(public_path('customkey1234567890abcdef123456.txt'));
    }

    /**
     * Test Admin Dashboard KPI real-time counter calculations and live refresh API.
     */
    public function test_admin_dashboard_kpis_realtime_counters(): void
    {
        $admin = User::factory()->create();

        WhatsappInquiry::create([
            'name' => 'Sara Al-Maktoum',
            'phone' => '+971501234567',
            'tour_name' => 'Sunset Safari',
            'page_url' => '/dubai-desert-sunset-times',
            'message_text' => 'Booking inquiry for sunset tour',
        ]);

        $controller = app(AdminDashboardController::class);
        $kpis = $controller->computeRealtimeKpis();

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('revenue', $kpis);
        $this->assertArrayHasKey('total', $kpis);
        $this->assertArrayHasKey('whatsapp_leads', $kpis);
        $this->assertArrayHasKey('active_tours', $kpis);
        $this->assertArrayHasKey('total_tours', $kpis);
        $this->assertArrayHasKey('total_reviews', $kpis);
        $this->assertArrayHasKey('approved_reviews', $kpis);
        $this->assertArrayHasKey('avg_rating', $kpis);
        $this->assertArrayHasKey('published_posts', $kpis);
        $this->assertArrayHasKey('total_posts', $kpis);
        $this->assertArrayHasKey('today_departures', $kpis);
        $this->assertArrayHasKey('upcoming_departures', $kpis);
        $this->assertArrayHasKey('subscribers_count', $kpis);
        $this->assertArrayHasKey('campaigns_count', $kpis);
        $this->assertGreaterThanOrEqual(1, $kpis['whatsapp_leads']);

        // Verify live JSON endpoint returns all metrics
        $apiResp = $this->actingAs($admin)->get('/admin/api/kpis');
        $apiResp->assertStatus(200);
        $apiResp->assertJsonPath('success', true);
        $apiResp->assertJsonStructure([
            'success',
            'stats' => [
                'revenue', 'total', 'confirmed', 'completed', 'pending',
                'active_tours', 'total_tours', 'total_reviews', 'published_posts',
                'today_departures', 'upcoming_departures', 'subscribers_count',
            ],
        ]);

        // Verify dashboard view renders the Operations & Inventory hub
        $viewResp = $this->actingAs($admin)->get('/admin/dashboard');
        $viewResp->assertStatus(200);
        $viewResp->assertSee('Tours Inventory');
        $viewResp->assertSee('Reviews & Rating', false);
        $viewResp->assertSee('Travel Blog Articles');
        $viewResp->assertSee('Operations & Departures', false);
    }

    /**
     * Test admin can view, update, and manage hero video poster and sitewide default OG images.
     */
    public function test_admin_can_manage_hero_poster_and_default_meta_images(): void
    {
        $admin = User::factory()->create();

        // 1. Verify fields appear on General Settings and SEO Settings pages
        $generalResp = $this->actingAs($admin)->get('/admin/settings/general');
        $generalResp->assertStatus(200);
        $generalResp->assertSee('site_hero_poster');
        $generalResp->assertSee('site_hero_poster_file');
        $generalResp->assertSee('seo_default_og_image');

        $seoResp = $this->actingAs($admin)->get('/admin/settings/seo');
        $seoResp->assertStatus(200);
        $seoResp->assertSee('site_hero_poster');
        $seoResp->assertSee('seo_default_og_image');

        // 2. Update via text path
        $updateResp = $this->actingAs($admin)->post('/admin/settings/update', [
            'site_hero_poster' => '/images/desert-safari-dubai-dune-discovery-tourism-HD.avif',
            'seo_default_og_image' => '/images/desert-safari-dubai-dune-discovery-tourism-HD.avif',
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals('/images/desert-safari-dubai-dune-discovery-tourism-HD.avif', Setting::where('setting_key', 'site_hero_poster')->value('setting_value'));
        $this->assertEquals('/images/desert-safari-dubai-dune-discovery-tourism-HD.avif', Setting::where('setting_key', 'seo_default_og_image')->value('setting_value'));

        // 3. Verify Homepage renders the updated hero poster in video tag and preload link
        $homeResp = $this->get('/');
        $homeResp->assertStatus(200);
        $homeResp->assertSee('desert-safari-dubai-dune-discovery-tourism-HD.avif');

        // 4. Update via file upload
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->image('custom_poster.jpg', 900, 600);
        $uploadResp = $this->actingAs($admin)->post('/admin/settings/update', [
            'site_hero_poster_file' => $fakeFile,
        ]);
        $uploadResp->assertRedirect();
        $storedPoster = Setting::where('setting_key', 'site_hero_poster')->value('setting_value');
        $this->assertStringStartsWith('/images/hero-poster-', $storedPoster);
        if (file_exists(public_path(ltrim($storedPoster, '/')))) {
            @unlink(public_path(ltrim($storedPoster, '/')));
        }
    }
}
