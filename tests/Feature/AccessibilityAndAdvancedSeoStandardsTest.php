<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Tier;
use App\Models\Tour;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityAndAdvancedSeoStandardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(SettingsService::class)->clearCache();
    }

    public function test_homepage_faq_accordion_implements_complete_wai_aria_standards(): void
    {
        $category = Category::create([
            'name' => 'Desert Safaris',
            'slug' => 'desert-safari',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tour = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'category_id' => $category->id,
            'duration' => '6 Hours',
            'status' => 'active',
            'priority' => 1,
            'is_bestseller' => true,
        ]);

        $faq = Faq::create([
            'question' => 'What is included in the desert safari?',
            'answer' => 'Dune bashing in 4x4 Land Cruiser, camel ride, sandboarding, and live BBQ dinner.',
            'category' => 'general',
            'status' => 'active',
            'priority' => 1,
        ]);

        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => 'general',
            'entity_id' => null,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify WAI-ARIA states on trigger buttons and panel containers
        $content = $response->getContent();
        $this->assertStringContainsString('id="home-faq-btn-0"', $content);
        $this->assertStringContainsString('aria-controls="home-faq-panel-0"', $content);
        $this->assertStringContainsString(':aria-expanded="activeFaq === 0 ? \'true\' : \'false\'"', $content);
        $this->assertStringContainsString('id="home-faq-panel-0"', $content);
        $this->assertStringContainsString('role="region"', $content);
        $this->assertStringContainsString('aria-labelledby="home-faq-btn-0"', $content);
    }

    public function test_faq_page_accordion_implements_complete_wai_aria_standards(): void
    {
        $faq = Faq::create([
            'question' => 'Can pregnant women participate?',
            'answer' => 'Pregnant women are advised against dune bashing due to off-road bumps.',
            'category' => 'safety',
            'status' => 'active',
            'priority' => 1,
        ]);

        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => 'general',
            'entity_id' => null,
        ]);

        $response = $this->get('/faq');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="page-faq-btn-0"', $content);
        $this->assertStringContainsString('aria-controls="page-faq-panel-0"', $content);
        $this->assertStringContainsString(':aria-expanded="openFaq === 0 ? \'true\' : \'false\'"', $content);
        $this->assertStringContainsString('id="page-faq-panel-0"', $content);
        $this->assertStringContainsString('role="region"', $content);
        $this->assertStringContainsString('aria-labelledby="page-faq-btn-0"', $content);
    }

    public function test_modals_have_aria_dialog_semantics_and_title_linkages(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Booking Modal
        $this->assertStringContainsString('id="bookingModal"', $content);
        $this->assertStringContainsString('role="dialog"', $content);
        $this->assertStringContainsString('aria-modal="true"', $content);
        $this->assertStringContainsString('aria-labelledby="bookingModalTitle"', $content);

        // Search Modal
        $this->assertStringContainsString('id="globalSearchModal"', $content);
        $this->assertStringContainsString('role="dialog"', $content);
        $this->assertStringContainsString('aria-modal="true"', $content);

        // WhatsApp Modal
        $this->assertStringContainsString('id="whatsappModal"', $content);
        $this->assertStringContainsString('aria-labelledby="whatsappModalLabel"', $content);
    }

    public function test_homepage_schema_graph_includes_howto_and_breadcrumb_schemas(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // BreadcrumbList schema
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $content);
        $this->assertStringContainsString('#breadcrumb', $content);

        // HowTo schema
        $this->assertStringContainsString('"@type": "HowTo"', $content);
        $this->assertStringContainsString('#howto-book', $content);
        $this->assertStringContainsString('How to Book a Dubai Desert Safari', $content);
        $this->assertStringContainsString('"@type": "HowToStep"', $content);
        $this->assertStringContainsString('Select Safari Experience', $content);
        $this->assertStringContainsString('Choose Date & Guest Count', $content);
    }

    public function test_tour_detail_schema_includes_howto_prep_and_itinerary(): void
    {
        $category = Category::create([
            'name' => 'Desert Safaris',
            'slug' => 'desert-safari',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tour = Tour::create([
            'name' => 'Evening Desert Safari Dubai',
            'slug' => 'evening-desert-safari-dubai',
            'category_id' => $category->id,
            'duration' => '6 Hours',
            'status' => 'active',
            'priority' => 1,
        ]);

        $tier = Tier::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'price' => 79.00,
        ]);
        $tour->tiers()->attach($tier->id, ['price' => 79.00]);

        $response = $this->get('/evening-desert-safari-dubai');
        $response->assertStatus(200);

        $content = $response->getContent();

        // HowTo Prep schema
        $this->assertStringContainsString('#howto-prep', $content);
        $this->assertStringContainsString('How to Prepare for Evening Desert Safari Dubai', $content);
        $this->assertStringContainsString('Wear Comfortable Breathable Clothing', $content);
        $this->assertStringContainsString('Pack Desert Sun & Sand Protection', $content);

        // Offer schema attributes
        $this->assertStringContainsString('"priceValidUntil"', $content);
        $this->assertStringContainsString('"availability": "https://schema.org/InStock"', $content);

        // BreadcrumbList
        $this->assertStringContainsString('#breadcrumb', $content);
    }

    public function test_reduced_motion_accessibility_rules_are_compiled_in_stylesheet(): void
    {
        $cssPath = resource_path('css/app.css');
        $this->assertFileExists($cssPath);

        $css = file_get_contents($cssPath);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('animation-duration: 0.01ms', $css);
        $this->assertStringContainsString('scroll-behavior: auto', $css);
    }

    public function test_opensearch_and_manifest_standards_are_present(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('rel="manifest"', $content);
        $this->assertStringContainsString('rel="search"', $content);
        $this->assertStringContainsString('type="application/opensearchdescription+xml"', $content);

        // Verify public opensearch and manifest files exist
        $this->assertFileExists(public_path('opensearch.xml'));
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('site.webmanifest'));
    }

    public function test_llms_txt_and_full_are_accessible_and_follow_standards(): void
    {
        $response = $this->get('/llms.txt');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
        $this->assertStringContainsString('# Dunes Discovery Tourism', $response->getContent());
        $this->assertStringContainsString('## Desert Safaris & Off-Road Adventures', $response->getContent());

        $responseFull = $this->get('/llms-full.txt');
        $responseFull->assertStatus(200);
        $responseFull->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
    }

    public function test_robots_txt_allows_generative_ai_crawlers(): void
    {
        $this->assertFileExists(public_path('robots.txt'));
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('User-agent: GPTBot', $robots);
        $this->assertStringContainsString('User-agent: ClaudeBot', $robots);
        $this->assertStringContainsString('User-agent: PerplexityBot', $robots);
        $this->assertStringContainsString('User-agent: Google-Extended', $robots);
        $this->assertStringContainsString('User-agent: ChatGPT-User', $robots);
        $this->assertStringContainsString('llms.txt', $robots);
    }

    public function test_all_15_location_landing_hubs_render_and_have_self_referential_canonicals(): void
    {
        $locations = \App\Services\LocationLandingService::getLocations();
        $this->assertCount(15, $locations, 'Expected exactly 15 programmatic location landing hubs');

        foreach ($locations as $key => $loc) {
            $url = '/' . $loc['slug'];
            $response = $this->get($url);
            $response->assertStatus(200);

            $content = $response->getContent();
            $this->assertStringContainsString($loc['headline'], $content, "Missing headline for {$loc['slug']}");
            $this->assertStringContainsString('<link rel="canonical" href="http://localhost/' . $loc['slug'] . '">', $content);
        }
    }

    public function test_location_hubs_are_internally_linked_in_navigation_and_footer(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Pickup Hubs', $content);
        $this->assertStringContainsString('Doorstep Hotel Pickup Hubs Across Dubai', $content);

        foreach (\App\Services\LocationLandingService::getLocations() as $loc) {
            $this->assertStringContainsString($loc['slug'], $content, "Missing internal link for hub {$loc['slug']}");
        }
    }

    public function test_homepage_ctr_optimized_title_and_description_render(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Dubai Desert Safari from AED 79 (2026) | 4x4 Lahbab Red Dunes | Dunes Discovery', $content);
        $this->assertStringContainsString('★ 4.9/5 Rated (2,800+ Reviews)', $content);
        $this->assertStringContainsString('<meta name="twitter:site" content="@dunesdiscovery">', $content);

        // SpeakableSpecification should only contain meaningful content, not header/footer chrome
        $this->assertStringNotContainsString('"cssSelector": ["#header"', $content);
        $this->assertStringNotContainsString('"#footer"', $content);
        $this->assertStringContainsString('"cssSelector": [".page-summary", ".hero-title", "h1"]', $content);
    }

    public function test_global_floating_whatsapp_button_is_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="globalWhatsappFloat"', $content);
        $this->assertStringContainsString('class="whatsapp-floating-btn', $content);
        $this->assertStringContainsString('Chat with Us', $content);
    }

    public function test_faq_schema_is_stacked_into_schema_section(): void
    {
        $faq = Faq::create([
            'question' => 'What is the refund policy?',
            'answer' => 'Full refund for cancellations made 24 hours prior to departure.',
            'category' => 'general',
            'status' => 'active',
            'priority' => 1,
        ]);

        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => 'general',
            'entity_id' => null,
        ]);

        $response = $this->get('/faq');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"@type": "FAQPage"', $content);
        $this->assertStringContainsString('What is the refund policy?', $content);
    }

    public function test_all_15_location_hubs_have_accurate_multi_safari_timing_schedules(): void
    {
        $locations = \App\Services\LocationLandingService::getLocations();

        foreach ($locations as $key => $loc) {
            $this->assertArrayHasKey('schedules', $loc, "Location {$key} missing schedules array");
            $this->assertArrayHasKey('morning', $loc['schedules'], "Location {$key} missing morning schedule");
            $this->assertArrayHasKey('evening', $loc['schedules'], "Location {$key} missing evening schedule");
            $this->assertArrayHasKey('overnight', $loc['schedules'], "Location {$key} missing overnight schedule");
            $this->assertArrayHasKey('quad_buggy', $loc['schedules'], "Location {$key} missing quad_buggy schedule");

            // Specific Authoritative Timings Check
            $this->assertEquals('07:00 AM – 07:30 AM', $loc['schedules']['morning']['pickup']);
            $this->assertEquals('10:30 AM – 11:00 AM', $loc['schedules']['morning']['return']);
            $this->assertEquals('02:30 PM – 03:00 PM', $loc['schedules']['evening']['pickup']);
            $this->assertEquals('09:00 PM – 09:30 PM', $loc['schedules']['evening']['return']);
            $this->assertEquals('02:45 PM – 03:15 PM', $loc['schedules']['overnight']['pickup']);
            $this->assertEquals('06:30 AM – 07:00 AM (Next Day)', $loc['schedules']['overnight']['return']);
            $this->assertEquals('02:00 PM – 02:30 PM', $loc['schedules']['quad_buggy']['pickup']);
            $this->assertEquals('09:00 PM – 09:30 PM', $loc['schedules']['quad_buggy']['return']);

            // Verify landing page renders multi-safari schedules
            $response = $this->get('/' . $loc['slug']);
            $response->assertStatus(200);
            $content = $response->getContent();

            $this->assertStringContainsString('Morning Safari', $content);
            $this->assertStringContainsString('Evening Safari', $content);
            $this->assertStringContainsString('Overnight Safari', $content);
            $this->assertStringContainsString('Schedule Matrix', $content);
            $this->assertStringContainsString('Safari Timings & Schedule', $content);
            $this->assertStringContainsString($loc['schedules']['morning']['pickup'], $content);
            $this->assertStringContainsString($loc['schedules']['morning']['return'], $content);
            $this->assertStringContainsString($loc['schedules']['evening']['pickup'], $content);
            $this->assertStringContainsString($loc['schedules']['evening']['return'], $content);
            $this->assertStringContainsString($loc['schedules']['overnight']['pickup'], $content);
            $this->assertStringContainsString($loc['schedules']['overnight']['return'], $content);
        }
    }
}
