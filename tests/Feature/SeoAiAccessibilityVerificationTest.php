<?php

namespace Tests\Feature;

use App\Models\RedirectRule;
use App\Models\Setting;
use App\Models\Tour;
use App\Services\RedirectManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAiAccessibilityVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test XML Sitemaps index and sub-sitemaps return HTTP 200 and valid XML.
     */
    public function test_xml_sitemaps_and_sub_sitemaps_render_valid_xml(): void
    {
        // Sitemap Index
        $response = $this->get('/sitemap_index.xml');
        $response->assertStatus(200);
        $this->assertTrue(str_contains((string) $response->getContent(), '<sitemapindex'));
        $this->assertTrue(str_contains((string) $response->getContent(), 'sitemap-tours.xml'));

        // Tours Sitemap
        $toursResp = $this->get('/sitemap-tours.xml');
        $toursResp->assertStatus(200);
        $this->assertTrue(str_contains((string) $toursResp->getContent(), '<urlset'));

        // Videos Sitemap
        $videosResp = $this->get('/sitemap-videos.xml');
        $videosResp->assertStatus(200);
        $this->assertTrue(str_contains((string) $videosResp->getContent(), '<urlset'));
    }

    /**
     * Test /llms.txt and /llms-full.txt machine-readable discovery feeds for GEO / LLMs.
     */
    public function test_llms_txt_and_full_manifest_return_markdown(): void
    {
        $resp = $this->get('/llms.txt');
        $resp->assertStatus(200);
        $this->assertTrue(str_starts_with((string) $resp->headers->get('content-type'), 'text/markdown'));
        $resp->assertSee('# Dunes Discovery Tourism');

        $respFull = $this->get('/llms-full.txt');
        $respFull->assertStatus(200);
        $this->assertTrue(str_starts_with((string) $respFull->headers->get('content-type'), 'text/markdown'));
        $respFull->assertSee('# Dunes Discovery Tourism');
    }

    /**
     * Test homepage renders WCAG 2.2 AA accessibility landmarks, skip link, and Schema.org JSON-LD.
     */
    public function test_homepage_accessibility_and_structured_data_graphs(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // WCAG 2.2 SC 2.4.1 Skip Link
        $response->assertSee('href="#main"', false);
        $response->assertSee('id="main"', false);
        $response->assertSee('tabindex="-1"', false);

        // Schema.org JSON-LD
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "TravelAgency"', false);
        $response->assertSee('"@type": "WebSite"', false);
        $response->assertSee('"@type": "WebPage"', false);
    }

    /**
     * Test security headers middleware is active on responses.
     */
    public function test_security_headers_are_present_on_responses(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test dynamic redirect chain flattening.
     */
    public function test_dynamic_redirect_chain_flattening_to_final_target(): void
    {
        $service = app(RedirectManagerService::class);

        // Rule 1: /step-a -> /step-b
        $service->saveRule([
            'source_path' => '/step-a',
            'target_path' => '/step-b',
            'status_code' => 301,
        ]);

        // Rule 2: /step-b -> /tours
        $service->saveRule([
            'source_path' => '/step-b',
            'target_path' => '/tours',
            'status_code' => 301,
        ]);

        // Requesting /step-a should resolve directly to /tours (preventing multi-hop chains)
        $response = $this->get('/step-a');
        $response->assertRedirect('/tours');
        $response->assertStatus(301);
    }
}
