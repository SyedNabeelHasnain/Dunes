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
}
