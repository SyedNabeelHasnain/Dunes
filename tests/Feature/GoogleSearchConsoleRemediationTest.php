<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Booking;
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

        $response = $this->get('/'.$tour->slug);
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

    /**
     * Test 6: Blog Post Server Renders og:type article and Canonical
     */
    public function test_blog_post_server_renders_og_type_article_and_canonical(): void
    {
        $category = BlogCategory::create([
            'name' => 'Safari Guides',
            'slug' => 'safari-guides',
            'status' => 'active',
        ]);

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Ultimate Dubai Safari Guide',
            'slug' => 'ultimate-dubai-safari-guide',
            'excerpt' => 'Everything you need to know before booking your desert safari in Dubai.',
            'content' => 'Complete guide to quad biking, dune bashing, and BBQ dinner in Dubai.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get('/blog/'.$post->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta property="og:type" content="article">', $content);
        $this->assertStringNotContainsString("setAttribute('content', 'article')", $content, 'Client-side og:type modification script must be removed');
    }

    /**
     * Test 7: Internal Search Returns noindex, follow Directives
     */
    public function test_internal_search_has_noindex_follow_robots_directive(): void
    {
        $response = $this->get('/search?q=safari');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
    }

    /**
     * Test 8: Sensitive and Transactional Routes Have noindex, nofollow Directives
     */
    public function test_sensitive_and_transactional_routes_have_noindex_nofollow_directive(): void
    {
        // 1. Thank you page
        $responseThankYou = $this->get('/thankyou');
        $responseThankYou->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseThankYou->getContent());

        // 2. Payment Cancel page
        $responseCancel = $this->get('/payment-cancel');
        $responseCancel->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseCancel->getContent());

        // 3. Voucher page
        $booking = Booking::create([
            'reference' => 'TEST-ROBOTS-101',
            'tour_name' => 'Evening Desert Safari',
            'tour_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+971501234567',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'pickup_location' => 'Dubai Marina',
            'subtotal' => 300,
            'total' => 300,
        ]);
        $responseVoucher = $this->get('/booking/'.$booking->reference.'/voucher');
        $responseVoucher->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseVoucher->getContent());

        // 4. Unsubscribe page (404/invalid token scenario)
        $responseUnsub = $this->get('/unsubscribe/invalid-or-sample-token');
        $responseUnsub->assertStatus(404);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $responseUnsub->getContent());
    }

    /**
     * Test 9: Tour Without Reviews Omits AggregateRating Schema
     */
    public function test_tour_without_reviews_omits_aggregate_rating(): void
    {
        $tour = Tour::create([
            'name' => 'New Quad Safari Without Reviews',
            'slug' => 'new-quad-safari-no-reviews',
            'short_desc' => 'Adventure safari with no reviews yet.',
            'hero_image' => 'quad-safari.avif',
            'status' => 'active',
            'duration' => '3 Hours',
            'rating' => 5.0,
            'review_count' => 0,
        ]);

        $response = $this->get('/'.$tour->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('"@type": "AggregateRating"', $content, 'Tour with 0 reviews must not emit fabricated AggregateRating');
    }

    /**
     * Test 10: Sitemap pages() Includes lastmod Tag For Every Entry
     */
    public function test_sitemap_pages_includes_lastmod_timestamps(): void
    {
        $response = $this->get('/sitemap-pages.xml');
        $response->assertStatus(200);

        $content = $response->getContent();

        preg_match_all('/<url>/', $content, $urls);
        preg_match_all('/<lastmod>(.*?)<\/lastmod>/', $content, $lastmods);

        $this->assertNotEmpty($urls[0]);
        $this->assertEquals(count($urls[0]), count($lastmods[0]), 'Every URL entry in sitemap-pages must have a lastmod timestamp');
    }

    /**
     * Test 11: Core Public Pages Have Strictly ONE <h1> Heading
     */
    public function test_all_core_public_pages_have_strictly_one_h1_heading(): void
    {
        $routes = [
            '/',
            '/about',
            '/contact',
            '/faq',
            '/tours',
            '/rate-card',
            '/blog',
            '/payment-cancel',
            '/thankyou',
            '/review/guest',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);

            $content = $response->getContent();
            $h1Count = substr_count(strtolower($content), '<h1');
            $this->assertEquals(1, $h1Count, "Route [{$route}] must have strictly ONE <h1> heading");
        }
    }

    /**
     * Test 12: Rate Card Page Has Valid BreadcrumbList Schema
     */
    public function test_rate_card_page_has_valid_breadcrumb_list_schema(): void
    {
        $response = $this->get('/rate-card');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $content);
        $this->assertStringContainsString('"name": "Rate Card"', $content);
    }

    /**
     * Test 13: BreadcrumbList Home Entries Match Homepage Canonical Trailing Slash
     */
    public function test_breadcrumb_list_home_entries_match_canonical_trailing_slash(): void
    {
        $routes = ['/about', '/contact', '/faq', '/rate-card', '/tours', '/blog'];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);

            $content = $response->getContent();
            $this->assertStringContainsString('"name"', $content);
            $this->assertStringContainsString('"Home"', $content);
            preg_match('/"name"\s*:\s*"Home"\s*,\s*"item"\s*:\s*"([^"]+)"/i', $content, $matches);
            $this->assertNotEmpty($matches, "Home breadcrumb item must be present on [{$route}]");
            $this->assertStringEndsWith('/', $matches[1], "Breadcrumb home entry on [{$route}] must end with trailing slash");
        }
    }

    /**
     * Test 14: 404 Error Page Renders HTTP 404 and Has Noindex Directive
     */
    public function test_404_error_page_renders_status_and_noindex_directive(): void
    {
        $response = $this->get('/non-existent-page-url-xyz');
        $response->assertStatus(404);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
        $this->assertStringContainsString('<h1', $content);
    }

    /**
     * Test 15: Multi-Engine Site Verification Tags (Google & Bing)
     */
    public function test_multi_engine_site_verification_tags(): void
    {
        putenv('GOOGLE_SITE_VERIFICATION=google_test_token_123');
        putenv('BING_SITE_VERIFICATION=bing_test_token_456');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<meta name="google-site-verification" content="google_test_token_123">', $content);
        $this->assertStringContainsString('<meta name="msvalidate.01" content="bing_test_token_456">', $content);

        putenv('GOOGLE_SITE_VERIFICATION');
        putenv('BING_SITE_VERIFICATION');
    }

    /**
     * Test 16: Semantic Buttons for Interactive Modal Triggers
     */
    public function test_interactive_booking_triggers_use_semantic_buttons(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Desktop & Mobile booking triggers should not be <a href="#" data-action="open-booking">
        $this->assertStringNotContainsString('<a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-extrabold text-xs text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-sm hover:shadow-md transition-all cursor-pointer" href="#" data-action="open-booking">', $content);
        $this->assertStringContainsString('data-action="open-booking"', $content);
    }

    /**
     * Test 17: TravelAgency Schema Has Map Linked to Coordinates
     */
    public function test_travel_agency_schema_has_map_linked_to_coordinates(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"hasMap": "https://maps.google.com/?q=25.2048,55.2708"', $content);
    }

    /**
     * Test 18: Blog Post FAQ Schema Produces Valid JSON
     */
    public function test_blog_post_faq_schema_handles_quotes_and_produces_valid_json(): void
    {
        $category = BlogCategory::create([
            'name' => 'Safari Tips',
            'slug' => 'safari-tips',
        ]);

        $post = BlogPost::create([
            'title' => 'Guide to "Dune Bashing" in Dubai',
            'slug' => 'guide-to-dune-bashing',
            'excerpt' => 'Complete guide with quotes "and" formatting.',
            'content' => 'Full article body with plenty of details about desert safari safety.',
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->faqs()->create([
            'question' => 'Is "dune bashing" safe for kids?',
            'answer' => 'Yes, our "experienced" drivers use 5-point harnesses and roll cages.',
            'priority' => 1,
        ]);

        $response = $this->get('/blog/'.$post->slug);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"@type": "FAQPage"', $content);
        $this->assertStringContainsString('Is \"dune bashing\" safe for kids?', $content);
    }

    /**
     * Test 19: RFC 9116 Security Policy (.well-known/security.txt)
     */
    public function test_security_txt_is_accessible_and_rfc_9116_compliant(): void
    {
        $path = public_path('.well-known/security.txt');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('Contact: mailto:security@dunesdiscoverytourism.com', $content);
        $this->assertStringContainsString('Canonical: https://dunesdiscoverytourism.com/.well-known/security.txt', $content);
        $this->assertStringContainsString('Expires:', $content);

        // HTTP Endpoint Test
        $response = $this->get('/.well-known/security.txt');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');

        // Shorthand 301 Redirect Test
        $redirect = $this->get('/security.txt');
        $redirect->assertStatus(301);
        $redirect->assertRedirect('/.well-known/security.txt');
    }

    /**
     * Test 20: AI LLM GEO Markdown Endpoints
     */
    public function test_llms_txt_and_full_txt_render_markdown(): void
    {
        $respIndex = $this->get('/llms.txt');
        $respIndex->assertStatus(200);
        $respIndex->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
        $this->assertStringContainsString('Dunes Discovery Tourism', $respIndex->getContent());

        $respFull = $this->get('/llms-full.txt');
        $respFull->assertStatus(200);
        $respFull->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
        $this->assertStringContainsString('Dunes Discovery Tourism', $respFull->getContent());
    }

    /**
     * Test 21: Complete Remediation of All 8 Google Search Console Page Indexing Categories
     */
    public function test_gsc_eight_indexing_categories_remediation(): void
    {
        // 1. robots.txt allows Googlebot to read noindex tags on /search, /thankyou, /payment-cancel, /unsubscribe/
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringNotContainsString("Disallow: /search\n", $robots);
        $this->assertStringNotContainsString('Disallow: /thankyou', $robots);
        $this->assertStringNotContainsString('Disallow: /payment-cancel', $robots);
        $this->assertStringNotContainsString('Disallow: /unsubscribe/', $robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);

        // 2. Legacy /luxury-vip-desert-safari-dubai 301 redirects (EN & localized)
        $this->get('/luxury-vip-desert-safari-dubai')
            ->assertStatus(301)
            ->assertRedirect('/evening-desert-safari-dubai');
        $this->get('/ar/luxury-vip-desert-safari-dubai')
            ->assertStatus(301)
            ->assertRedirect(url('/ar/evening-desert-safari-dubai'));

        // 3. Press kit missing .avif image 301 redirects
        $this->get('/images/sunset-desert-safari-dubai-dunes-discovery-tourism.avif')
            ->assertStatus(301)
            ->assertRedirect('/images/dubai-desert-safar-camel-ride-dune-discovery-tourism.avif');

        // 4. Localized /review/guest routes return 200 OK instead of 404
        foreach (['ar', 'ru', 'es', 'it'] as $loc) {
            $resReview = $this->get("/{$loc}/review/guest");
            $resReview->assertStatus(200);
            $resReview->assertSee('<link rel="canonical" href="http://localhost/'.$loc.'/review/guest">', false);
        }

        // 5. Inactive / unknown 2-letter prefixes do not serve duplicate 200 OK English pages
        $this->get('/fr')->assertStatus(301)->assertRedirect(url('/'));
        $this->get('/de')->assertStatus(301)->assertRedirect(url('/'));
        $this->get('/zz')->assertStatus(404);

        // 6. Localized blog posts have self-referential canonicals (even if canonical_url was English in DB)
        $category = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = BlogPost::create([
            'title' => 'Ultimate Dubai Desert Safari Guide 2026',
            'slug' => 'ultimate-dubai-desert-safari-guide-2026',
            'excerpt' => 'Complete guide to Dubai desert safari.',
            'content' => '<p>Detailed guide content.</p>',
            'category_id' => $category->id,
            'status' => 'published',
            'canonical_url' => 'https://dunesdiscoverytourism.com/blog/ultimate-dubai-desert-safari-guide-2026',
            'published_at' => now(),
        ]);

        $resArBlog = $this->get('/ar/blog/'.$post->slug);
        $resArBlog->assertStatus(200);
        $resArBlog->assertSee('<link rel="canonical" href="http://localhost/ar/blog/'.$post->slug.'">', false);

        // 7. Query-filtered pages (/tours?category=..., /blog?category=..., /gallery?category=...) use noindex, follow and suppress hreflang
        $resFilteredTours = $this->get('/tours?category=desert-safari');
        $resFilteredTours->assertStatus(200);
        $resFilteredTours->assertSee('<meta name="robots" content="noindex, follow">', false);
        $resFilteredTours->assertSee('<link rel="canonical" href="http://localhost/tours">', false);
        $resFilteredTours->assertDontSee('hreflang=', false);

        // 8. Geo-location landing pages (EN & localized) render 200 OK with self-referential canonicals
        $this->get('/desert-safari-from-dubai-marina')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="http://localhost/desert-safari-from-dubai-marina">', false);
        $this->get('/ar/desert-safari-from-dubai-marina')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="http://localhost/ar/desert-safari-from-dubai-marina">', false);

        // 9. Relative /blog/images/* and legacy /images/og-*.jpg 301 redirects
        $this->get('/blog/images/evening-desert-safari-dubai-dune-discovery-tourism.avif')
            ->assertStatus(301)
            ->assertRedirect(url('/images/evening-desert-safari-dubai-dune-discovery-tourism.avif'));
        $this->get('/ar/blog/images/evening-desert-safari-dubai-dune-discovery-tourism.avif')
            ->assertStatus(301)
            ->assertRedirect(url('/images/evening-desert-safari-dubai-dune-discovery-tourism.avif'));
        $this->get('/images/og-home.jpg')
            ->assertStatus(301)
            ->assertRedirect('/images/evening-desert-safari-dubai-dune-discovery-tourism.avif');

        // 10. Root-level blog post slug accessed at /{slug} 301 redirects to /blog/{slug}
        $this->get('/'.$post->slug)
            ->assertStatus(301)
            ->assertRedirect(url('/blog/'.$post->slug));
        $this->get('/ar/'.$post->slug)
            ->assertStatus(301)
            ->assertRedirect(url('/ar/blog/'.$post->slug));

        // 11. Localized /tours/{slug} 301 redirects and localized RSS feed works
        $this->get('/ar/tours/evening-desert-safari-dubai')
            ->assertStatus(301)
            ->assertRedirect(url('/ar/evening-desert-safari-dubai'));
        $this->get('/ar/blog/feed.xml')
            ->assertStatus(200);
    }

    /**
     * Test 22: Paginated Blog Hreflang Parity, Relative OG Image Normalization, and Multi-Language Preservation
     */
    public function test_paginated_blog_hreflang_and_multilang_preservation(): void
    {
        $category = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
        for ($i = 1; $i <= 15; $i++) {
            BlogPost::create([
                'title' => [
                    'en' => "Safari Article {$i}",
                    'ar' => "مقال سفاري {$i}",
                    'ru' => "Статья сафари {$i}",
                    'es' => "Artículo de safari {$i}",
                    'it' => "Articolo safari {$i}",
                ],
                'slug' => "safari-article-{$i}",
                'excerpt' => "Excerpt {$i}",
                'content' => "<p>Content {$i}</p>",
                'featured_image' => 'images/evening-desert-safari-dubai-dune-discovery-tourism.avif',
                'og_image' => 'images/evening-desert-safari-dubai-dune-discovery-tourism.avif',
                'category_id' => $category->id,
                'status' => 'published',
                'published_at' => now()->subMinutes($i),
            ]);
        }

        // 1. Paginated /blog?page=2 keeps ?page=2 on both canonical and hreflang alternates
        $resPage2 = $this->get('/blog?page=2');
        $resPage2->assertStatus(200);
        $resPage2->assertSee('<link rel="canonical" href="http://localhost/blog?page=2">', false);
        $resPage2->assertSee('hreflang="ar" href="http://localhost/ar/blog?page=2"', false);

        // 2. Relative og_image is resolved to absolute /images/... URL on blog detail page
        $resPost = $this->get('/blog/safari-article-1');
        $resPost->assertStatus(200);
        $resPost->assertSee('<meta property="og:image" content="http://localhost/images/evening-desert-safari-dubai-dune-discovery-tourism.avif">', false);

        // 3. Updating a translatable attribute with a scalar string or partial array preserves other languages
        $firstPost = BlogPost::where('slug', 'safari-article-1')->firstOrFail();
        $firstPost->update(['title' => 'Updated English Title']);
        $firstPost->refresh();

        $this->assertEquals('Updated English Title', $firstPost->getTranslation('title', 'en', false));
        $this->assertEquals('مقال سفاري 1', $firstPost->getTranslation('title', 'ar', false));
        $this->assertEquals('Статья сафари 1', $firstPost->getTranslation('title', 'ru', false));
        $this->assertEquals('Artículo de safari 1', $firstPost->getTranslation('title', 'es', false));
        $this->assertEquals('Articolo safari 1', $firstPost->getTranslation('title', 'it', false));
    }
}
