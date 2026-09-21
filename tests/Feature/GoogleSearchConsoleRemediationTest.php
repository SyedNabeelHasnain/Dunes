<?php

namespace Tests\Feature;

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSearchConsoleRemediationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Homepage Canonical Trailing Slash & Single AggregateRating
     */
    public function test_homepage_canonical_has_trailing_slash_and_single_aggregate_rating(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Canonical must end with '/'
        preg_match('/<link rel="canonical" href="([^"]+)">/', $content, $m);
        $this->assertNotEmpty($m, 'Canonical link tag must be present on homepage');
        $canonical = $m[1];
        $this->assertStringEndsWith('/', $canonical, 'Homepage canonical URL must terminate with a trailing slash');

        // 2. AggregateRating must occur EXACTLY once on the homepage
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(1, $aggregateRatingCount, 'Homepage must have strictly ONE AggregateRating in JSON-LD');

        // 3. Review count must be 2847
        $this->assertStringContainsString('"reviewCount": "2847"', $content);

        // 4. VideoObject must be present and linked to organization
        $this->assertStringContainsString('"VideoObject"', $content);
        $this->assertStringContainsString('#organization', $content);

        // 5. Exactly 1 TravelAgency definition
        $travelAgencyCount = substr_count($content, '"TravelAgency"');
        $this->assertEquals(1, $travelAgencyCount, 'Homepage must have strictly ONE TravelAgency entity in JSON-LD');
    }

    /**
     * Test 2: Subpage (/about) Canonical Has NO Trailing Slash & Zero AggregateRating
     */
    public function test_subpages_have_no_trailing_slash_and_no_aggregate_rating(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Canonical must NOT end with '/'
        preg_match('/<link rel="canonical" href="([^"]+)">/', $content, $m);
        $this->assertNotEmpty($m, 'Canonical link tag must be present on about page');
        $canonical = $m[1];
        $this->assertFalse(str_ends_with($canonical, '/'), 'Subpage canonical must NOT terminate with a trailing slash');

        // 2. AggregateRating must be 0 on about page
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(0, $aggregateRatingCount, 'About page must not contain AggregateRating');
    }

    /**
     * Test 3: Tour Detail Page Has Strictly ONE AggregateRating on #trip and No Duplicate Entities
     */
    public function test_tour_detail_page_has_strictly_one_aggregate_rating_on_product_trip(): void
    {
        $tour = Tour::create([
            'name' => 'Morning Desert Safari Dubai',
            'slug' => 'morning-desert-safari-dubai',
            'short_desc' => 'Sunrise dune bashing adventure in Dubai.',
            'hero_image' => 'desert-safari-dubai-morning-desert-safari.avif',
            'status' => 'active',
            'duration' => '4 Hours',
            'rating' => 4.9,
            'review_count' => 120,
        ]);

        $response = $this->get('/' . $tour->slug);
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Strictly ONE AggregateRating on the entire page
        $aggregateRatingCount = substr_count($content, '"@type": "AggregateRating"');
        $this->assertEquals(1, $aggregateRatingCount, 'Tour detail page must have strictly ONE AggregateRating');

        // 2. Tour Product / TouristTrip schema is present
        $this->assertStringContainsString('"TouristTrip"', $content);

        // 3. Brand references layout #organization
        $this->assertStringContainsString('"brand":', $content);
        $this->assertStringContainsString('#organization', $content);

        // 4. Exactly 1 WebPage definition across the page
        $webPageCount = substr_count($content, '"@type": "WebPage"');
        $this->assertEquals(1, $webPageCount, 'Tour detail page must have strictly ONE WebPage entity');
    }

    /**
     * Test 4: Pricing Guide Duplicate Route 301 Redirects to Rate Card
     */
    public function test_pricing_guide_route_301_redirects_to_rate_card(): void
    {
        $response = $this->get('/pricing-guide');
        $response->assertStatus(301);
        $response->assertRedirect('/rate-card');
    }

    /**
     * Test 5: Sitemap pages() Homepage Entry Has Trailing Slash
     */
    public function test_sitemap_pages_homepage_entry_has_trailing_slash(): void
    {
        $response = $this->get('/sitemap-pages.xml');
        $response->assertStatus(200);

        $content = $response->getContent();

        preg_match_all('/<loc>(.*?)<\/loc>/', $content, $matches);
        $this->assertNotEmpty($matches[1], 'Sitemap must contain URLs');

        $firstLoc = $matches[1][0];
        $this->assertStringEndsWith('/', $firstLoc, 'Sitemap homepage entry must terminate with a trailing slash');

        $secondLoc = $matches[1][1];
        $this->assertFalse(str_ends_with($secondLoc, '/'), 'Sitemap subpage entry must NOT terminate with a trailing slash');
    }
}
