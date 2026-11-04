<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\MediaManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveCmsAndMediaEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?: User::factory()->create();
    }

    public function test_homepage_has_all_sections_including_video_reviews(): void
    {
        // Run migration to seed sections
        $this->artisan('migrate');

        $homePage = Page::where('slug', 'home')->first();
        $this->assertNotNull($homePage, 'Homepage should exist in pages table');

        $sections = PageSection::where('page_id', $homePage->id)->get()->keyBy('section_key');

        $this->assertTrue($sections->has('videos'), 'Homepage should contain the "videos" section in page_sections');
        $this->assertTrue($sections->has('trust_bar'), 'Homepage should contain trust_bar section');
        $this->assertTrue($sections->has('silos'), 'Homepage should contain silos section');
        $this->assertTrue($sections->has('featured_tours'), 'Homepage should contain featured_tours section');
        $this->assertTrue($sections->has('reviews'), 'Homepage should contain reviews section');
        $this->assertTrue($sections->has('moments'), 'Homepage should contain moments section');
        $this->assertTrue($sections->has('concierge'), 'Homepage should contain concierge section');
        $this->assertTrue($sections->has('why_choose_us'), 'Homepage should contain why_choose_us section');
        $this->assertTrue($sections->has('faqs'), 'Homepage should contain faqs section');
        $this->assertTrue($sections->has('desert_cta'), 'Homepage should contain desert_cta section');

        // Verify edit page displays the sections in admin
        $response = $this->actingAs($this->admin)->get(route('admin.pages.edit', $homePage->id));
        $response->assertStatus(200);
        $response->assertSee('Customer Video Reviews Reel');
        $response->assertSee('Watch Real Travelers in the Dubai Dunes');
        $response->assertSee('Manage Video Reviews');
        $response->assertSee(route('admin.video-reviews.index'));
        $response->assertSee('Manage Guest Reviews');
        $response->assertSee(route('admin.reviews.index'));
        $response->assertSee('Manage Media & Moments');
        $response->assertSee(route('admin.media.index'));
        $response->assertSee('Manage Tours & Packages');
        $response->assertSee(route('admin.tours.index'));
        $response->assertSee('Manage FAQs Hub');
        $response->assertSee(route('admin.faqs.index'));
        $response->assertSee('Manage Bookings Funnel');
        $response->assertSee(route('admin.bookings.index'));
    }

    public function test_admin_settings_index_route_is_defined_and_accessible(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.settings.index'));
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));
        $response->assertStatus(200);
    }

    public function test_contact_faq_and_tours_views_render_dynamic_cms_content(): void
    {
        $this->artisan('migrate');

        $contactRes = $this->get('/contact');
        $contactRes->assertStatus(200);
        $contactRes->assertSee('Contact Dunes Discovery Tourism');

        $faqRes = $this->get('/faq');
        $faqRes->assertStatus(200);

        $toursRes = $this->get('/tours');
        $toursRes->assertStatus(200);
        $toursRes->assertSee('Explore All Dubai Desert Safari & Adventure Tours');
    }

    public function test_media_manager_deduplicates_format_variants_seamlessly(): void
    {
        // Create duplicate image format variants
        $webpAsset = MediaAsset::create([
            'uuid' => 'test-webp-1',
            'filename' => 'sunset-safari.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/sunset-safari.webp',
            'public_url' => '/images/sunset-safari.webp',
            'media_type' => 'image',
            'extension' => 'webp',
            'file_size_bytes' => 102400,
            'title' => 'Sunset Safari',
        ]);

        $jpgAsset = MediaAsset::create([
            'uuid' => 'test-jpg-1',
            'filename' => 'sunset-safari.jpg',
            'disk' => 'direct_public',
            'file_path' => 'images/sunset-safari.jpg',
            'public_url' => '/images/sunset-safari.jpg',
            'media_type' => 'image',
            'extension' => 'jpg',
            'file_size_bytes' => 204800,
            'title' => 'Sunset Safari',
        ]);

        $this->assertEquals(2, MediaAsset::where('title', 'Sunset Safari')->count());

        // Run deduplication
        $mediaService = app(MediaManagerService::class);
        $report = $mediaService->deduplicateFormatVariants();

        $this->assertGreaterThan(0, $report['deleted']);
        $this->assertEquals(1, MediaAsset::where('title', 'Sunset Safari')->count());

        $canonical = MediaAsset::where('title', 'Sunset Safari')->first();
        $this->assertEquals('webp', $canonical->extension);
        $this->assertArrayHasKey('format_variants', $canonical->extra_metadata ?? []);
        $this->assertArrayHasKey('jpg', $canonical->extra_metadata['format_variants']);
    }

    public function test_sidebar_navigation_renders_clean_media_management_link(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Media Management');
        // Ensure the old overlapping badge text is removed
        $response->assertDontSee('Photos &amp; Videos');
    }
}
