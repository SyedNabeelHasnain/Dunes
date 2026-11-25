<?php

namespace Tests\Feature;

use App\Http\Controllers\SitemapController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Category;
use App\Models\ChangeApproval;
use App\Models\ConfigAuditLog;
use App\Models\Contact;
use App\Models\PrivacyConsent;
use App\Models\RequestLog;
use App\Models\Review;
use App\Models\SeoMetadata;
use App\Models\SeoTemplate;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\Tour;
use App\Models\User;
use App\Models\WebVitalsMetric;
use App\Models\WhatsappInquiry;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use App\Services\Audit\Checkers\AccessibilityChecker;
use App\Services\Audit\Checkers\ContentQualityChecker;
use App\Services\Audit\Checkers\SecurityChecker;
use App\Services\Audit\Checkers\SeoAuditChecker;
use App\Services\Audit\Checkers\TechnicalSeoChecker;
use App\Services\Audit\Checkers\UxChecker;
use App\Services\Security\SvgSanitizer;
use App\Services\Seo\CrawlerLogAnalyzerService;
use App\Services\Seo\KeywordIntelligenceService;
use App\Services\Seo\SearchConsoleIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnterprisePlatformWave2ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_svg_sanitizer_strips_xss_xxe_and_blocks_php_polyglots(): void
    {
        $maliciousSvg = '<?xml version="1.0"?><!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
            .'<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">'
            .'<script>alert("xss")</script>'
            .'<foreignObject><iframe src="javascript:alert(2)"></iframe></foreignObject>'
            .'<a href="javascript:alert(3)"><rect width="10" height="10" style="fill:expression(alert(4))"/></a>'
            .'<circle cx="5" cy="5" r="4" fill="#F69044"/></svg>';

        $cleaned = SvgSanitizer::sanitize($maliciousSvg);
        $this->assertStringNotContainsString('<script', strtolower($cleaned));
        $this->assertStringNotContainsString('onload=', strtolower($cleaned));
        $this->assertStringNotContainsString('javascript:', strtolower($cleaned));
        $this->assertStringNotContainsString('foreignobject', strtolower($cleaned));
        $this->assertStringNotContainsString('<!doctype', strtolower($cleaned));
        $this->assertStringNotContainsString('expression(', strtolower($cleaned));
        $this->assertStringContainsString('<circle', $cleaned);

        // Non-SVG PHP polyglot must be rejected
        $tmpPolyglot = tempnam(sys_get_temp_dir(), 'poly_');
        file_put_contents($tmpPolyglot, "\xFF\xD8\xFF\xE0<?php echo 'polyglot'; ?>");
        $threw = false;
        try {
            SvgSanitizer::assertNoScriptPolyglot($tmpPolyglot, 'jpg');
        } catch (\InvalidArgumentException $e) {
            $threw = true;
        } finally {
            @unlink($tmpPolyglot);
        }
        $this->assertTrue($threw, 'Expected SvgSanitizer::assertNoScriptPolyglot to reject PHP polyglot.');
    }

    public function test_backend_rbac_blocks_editor_from_system_security_privacy_and_ops_endpoints(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)->post(route('admin.clear-cache'))->assertStatus(403);
        $this->actingAs($editor)->post(route('admin.run-migrations'))->assertStatus(403);
        $this->actingAs($editor)->get(route('admin.settings.notifications'))->assertStatus(403);
        $this->actingAs($editor)->post(route('admin.security.headers.update'), [
            'security_referrer_policy' => 'no-referrer',
        ])->assertStatus(403);
        $this->actingAs($editor)->get(route('admin.settings.privacy.dsar', ['email' => 'guest@example.com']))->assertStatus(403);
    }

    public function test_security_headers_cms_control_enforces_approval_workflow_and_updates_live_headers(): void
    {
        $seoManager = User::factory()->create(['role' => 'seo_manager', 'is_active' => true]);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        // SEO manager does not have security.manage -> blocked
        $this->actingAs($seoManager)->post(route('admin.security.headers.update'), [
            'security_referrer_policy' => 'same-origin',
            'security_permissions_policy' => 'camera=(), microphone=()',
            'security_hsts_enabled' => '1',
        ])->assertStatus(403);

        // Super admin applies live headers immediately
        $this->actingAs($superAdmin)->post(route('admin.security.headers.update'), [
            'security_referrer_policy' => 'same-origin',
            'security_permissions_policy' => 'camera=(), microphone=(), payment=()',
            'security_hsts_enabled' => '1',
            'security_csp_extra' => "worker-src 'self';",
        ])->assertRedirect();

        $res = $this->get('/about');
        $res->assertStatus(200);
        $res->assertHeader('Referrer-Policy', 'same-origin');
        $res->assertHeader('Permissions-Policy', 'camera=(), microphone=(), payment=()');
        $this->assertStringContainsString("worker-src 'self';", (string) $res->headers->get('Content-Security-Policy'));
        $res->assertHeader('X-XSS-Protection', '0');

        // Also test ApprovalWorkflowService governance path for security section
        $approval = app(\App\Services\Governance\ApprovalWorkflowService::class)->propose(
            section: 'security',
            targetKey: 'security_http_headers',
            summary: 'Propose strict-origin referrer policy',
            payload: ['security_referrer_policy' => 'strict-origin'],
            previousPayload: ['security_referrer_policy' => 'same-origin'],
            requester: $seoManager,
        );
        $this->assertSame('pending_review', $approval->status);
        $this->actingAs($superAdmin)->post(route('admin.governance.approvals.approve', $approval->id))->assertRedirect();
        $this->get('/about')->assertHeader('Referrer-Policy', 'strict-origin');
    }

    public function test_seo_templates_control_sitemaps_and_noindex_external_canonicals_excluded_everywhere(): void
    {
        SeoTemplate::ensureDefaultsExist();
        $cat = Category::create(['name' => 'Desert', 'slug' => 'desert', 'status' => 'active']);
        $blogCat = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);

        $publicTour = Tour::create([
            'name' => 'Evening Red Dunes Safari',
            'slug' => 'evening-red-dunes-safari',
            'category_id' => $cat->id,
            'short_desc' => 'Premium Lahbab safari.',
            'full_desc' => '<p>Full Lahbab desert safari itinerary.</p>',
            'hero_image' => 'desert-safari-poster.avif',
            'status' => 'active',
        ]);

        $noindexTour = Tour::create([
            'name' => 'Hidden Partner Tour',
            'slug' => 'hidden-partner-tour',
            'category_id' => $cat->id,
            'short_desc' => 'Hidden.',
            'full_desc' => '<p>Private partner tour.</p>',
            'hero_image' => 'desert-safari-poster.avif',
            'status' => 'active',
        ]);
        SeoMetadata::create([
            'entity_type' => Tour::class,
            'entity_id' => $noindexTour->id,
            'robots_directive' => 'noindex, nofollow',
        ]);

        $externalCanonicalBlog = BlogPost::create([
            'category_id' => $blogCat->id,
            'title' => 'Syndicated External Partner Guide',
            'slug' => 'syndicated-external-partner-guide',
            'excerpt' => 'External canonical post.',
            'content' => '<p>Syndicated guide.</p>',
            'status' => 'published',
            'canonical_url' => 'https://partner-magazine.example.com/dubai-desert',
            'published_at' => now(),
        ]);

        // Customize Tour template priority & changefreq
        SeoTemplate::where('content_type', 'tour')->update([
            'sitemap_enabled' => true,
            'sitemap_priority' => 0.7,
            'sitemap_changefreq' => 'monthly',
        ]);
        SitemapController::flushAllSitemapCaches();

        $tourMap = $this->get('/sitemap-tours.xml');
        $tourMap->assertStatus(200);
        $this->assertStringContainsString('evening-red-dunes-safari', $tourMap->getContent());
        $this->assertStringNotContainsString('hidden-partner-tour', $tourMap->getContent());
        $this->assertStringContainsString('<priority>0.7</priority>', $tourMap->getContent());
        $this->assertStringContainsString('<changefreq>monthly</changefreq>', $tourMap->getContent());

        // Image sitemap must also exclude noindex tour
        $imageMap = $this->get('/sitemap-images.xml');
        $imageMap->assertStatus(200);
        $this->assertStringContainsString('evening-red-dunes-safari', $imageMap->getContent());
        $this->assertStringNotContainsString('hidden-partner-tour', $imageMap->getContent());

        // Blog sitemap must exclude externally canonicalized blog
        $blogMap = $this->get('/sitemap-blogs.xml');
        $blogMap->assertStatus(200);
        $this->assertStringNotContainsString('syndicated-external-partner-guide', $blogMap->getContent());

        // llms.txt must exclude noindex tour
        $llms = $this->get('/llms.txt');
        $llms->assertStatus(200);
        $this->assertStringContainsString('Evening Red Dunes Safari', $llms->getContent());
        $this->assertStringNotContainsString('Hidden Partner Tour', $llms->getContent());

        // Disabling blog sitemap via SeoTemplate removes sitemap-blogs.xml from index
        SeoTemplate::where('content_type', 'blog_post')->update(['sitemap_enabled' => false]);
        SitemapController::flushAllSitemapCaches();
        $indexXml = $this->get('/sitemap_index.xml')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('sitemap-blogs.xml', $indexXml);
    }

    public function test_robots_header_middleware_emits_x_robots_tag_on_transactional_and_api_routes(): void
    {
        $this->get('/thankyou')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/api/v1/tours/search?q=desert')->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Filtered parameterized URL mirrors meta noindex onto X-Robots-Tag
        $filtered = $this->get('/tours?category=desert');
        $filtered->assertStatus(200);
        $this->assertStringContainsString('noindex', strtolower((string) $filtered->headers->get('X-Robots-Tag')));
    }

    public function test_privacy_retention_purge_command_and_gdpr_dsar_lookup_export_erase(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        Setting::setVal('privacy_log_retention_days', '60', 'privacy');

        // Old consent (> 60 days) and recent consent
        PrivacyConsent::create([
            'consent_id' => 'old-consent-001',
            'action' => 'accept_all',
            'analytics' => true,
            'marketing' => false,
            'functional' => true,
            'consent_version' => '1.0',
            'locale' => 'en',
            'consented_at' => now()->subDays(90),
        ]);
        PrivacyConsent::create([
            'consent_id' => 'fresh-consent-002',
            'action' => 'reject_all',
            'analytics' => false,
            'marketing' => false,
            'functional' => false,
            'consent_version' => '1.0',
            'locale' => 'en',
            'consented_at' => now()->subDays(5),
        ]);

        WebVitalsMetric::create([
            'metric_name' => 'LCP',
            'metric_value' => 1800,
            'rating' => 'good',
            'page_path' => '/',
            'recorded_at' => now()->subDays(120),
        ]);

        Artisan::call('privacy:purge-expired');
        $this->assertDatabaseMissing('privacy_consents', ['consent_id' => 'old-consent-001']);
        $this->assertDatabaseHas('privacy_consents', ['consent_id' => 'fresh-consent-002']);
        $this->assertSame(0, WebVitalsMetric::count());

        // DSAR records across Subscriber, Contact, WhatsappInquiry, and statutory Booking
        $email = 'dsar.guest@example.com';
        Subscriber::create(['email' => $email, 'first_name' => 'Sara', 'status' => 'subscribed']);
        Contact::create(['name' => 'Sara Guest', 'email' => $email, 'subject' => 'Info', 'message' => 'Hello', 'status' => 'new']);
        WhatsappInquiry::create(['name' => 'Sara Guest', 'email' => $email, 'phone' => '+971501234567', 'message_text' => 'Hi']);
        Booking::create([
            'reference' => 'DDT-DSAR-1',
            'tour_name' => 'Evening Safari',
            'tour_date' => now()->addDay()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'name' => 'Sara Guest',
            'email' => $email,
            'phone' => '+971501234567',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'pickup_location' => 'Dubai Marina',
            'subtotal' => 300,
            'total' => 300,
        ]);

        // Lookup
        $this->actingAs($admin)
            ->get(route('admin.settings.privacy.dsar', ['email' => $email]))
            ->assertStatus(200)
            ->assertSee($email);

        // Export
        $exportRes = $this->actingAs($admin)
            ->post(route('admin.settings.privacy.dsar-export'), ['email' => $email]);
        $exportRes->assertStatus(200)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertJsonPath('records.counts.subscribers', 1)
            ->assertJsonPath('records.counts.bookings', 1);

        // Erase / Anonymize
        $this->actingAs($admin)
            ->post(route('admin.settings.privacy.dsar-erase'), [
                'email' => $email,
                'confirm_erase' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('subscribers', ['email' => $email]);
        $this->assertDatabaseMissing('contacts', ['email' => $email]);
        $this->assertDatabaseMissing('whatsapp_inquiries', ['email' => $email]);
        // Statutory booking retained
        $this->assertDatabaseHas('bookings', ['reference' => 'DDT-DSAR-1']);
        $this->assertTrue(ConfigAuditLog::where('action', 'dsar_erased')->exists());
    }

    public function test_crawler_log_analyzer_and_search_console_adapter_fail_honestly(): void
    {
        RequestLog::create([
            'request_uri' => '/tours',
            'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'bot_indicator' => 'Known Bot',
            'entity_type' => 'page',
            'request_timestamp' => now()->subHour(),
        ]);
        RequestLog::create([
            'request_uri' => '/llms.txt',
            'user_agent' => 'GPTBot/1.0',
            'bot_indicator' => 'Known Bot',
            'entity_type' => 'page',
            'request_timestamp' => now()->subHour(),
        ]);
        RequestLog::create([
            'request_uri' => '/missing-old-tour',
            'user_agent' => 'AhrefsBot/7.0',
            'bot_indicator' => 'Known Bot',
            'entity_type' => 'error_404',
            'request_timestamp' => now()->subHour(),
        ]);

        $report = app(CrawlerLogAnalyzerService::class)->analyze(30);
        $this->assertTrue($report['has_data']);
        $this->assertSame(1, $report['search_engine_hits']);
        $this->assertSame(1, $report['ai_bot_hits']);
        $this->assertSame(1, $report['scraper_hits']);
        $this->assertSame(1, $report['wasted_404_bot_hits']);

        // Unconfigured Search Console returns honest awaiting_credentials with zero fake rows
        $sc = app(SearchConsoleIntegrationService::class);
        $gscResult = $sc->fetchGoogleSearchAnalytics();
        $this->assertFalse($gscResult['success']);
        $this->assertSame('awaiting_credentials', $gscResult['status']);
        $this->assertSame([], $gscResult['rows']);

        // Expired token returns honest credentials_expired when remote returns 401
        Setting::setVal('gsc_oauth_access_token', 'expired-token-demo', 'seo');
        app(\App\Services\SettingsService::class)->clearCache();
        Http::fake(['https://www.googleapis.com/*' => Http::response(['error' => 'Unauthorized'], 401)]);
        $expiredResult = $sc->fetchGoogleSearchAnalytics();
        $this->assertFalse($expiredResult['success']);
        $this->assertSame('credentials_expired', $expiredResult['status']);
    }

    public function test_wiki_search_does_not_leak_draft_articles_and_live_search_returns_articles_and_locations(): void
    {
        $pillar = WikiPillar::create(['name' => 'Desert Safety', 'slug' => 'desert-safety', 'status' => 'active']);
        $draftArticle = WikiArticle::create([
            'pillar_id' => $pillar->id,
            'title' => 'Internal Draft Dune Protocol',
            'slug' => 'internal-draft-dune-protocol',
            'excerpt' => 'Draft only.',
            'content' => '<p>Unpublished draft.</p>',
            'status' => 'draft',
        ]);
        WikiGlossaryTerm::create([
            'term' => 'Lahbab Draft Term',
            'term_key' => 'lahbab-draft-term',
            'definition' => 'Definition referencing a draft wiki article.',
            'wiki_article_id' => $draftArticle->id,
        ]);

        $wikiSearch = $this->getJson('/wiki/search?q=Lahbab');
        $wikiSearch->assertStatus(200);
        $this->assertStringNotContainsString('internal-draft-dune-protocol', $wikiSearch->getContent());

        // Live search returns published blog + Dubai location hubs cleanly
        $blogCat = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        BlogPost::create([
            'category_id' => $blogCat->id,
            'title' => 'Lahbab Red Dunes Sunset Photography',
            'slug' => 'lahbab-red-dunes-sunset-photography',
            'excerpt' => 'Best camera settings.',
            'content' => '<p>Sunset tips.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $live = $this->getJson('/api/v1/tours/search?q=Lahbab');
        $live->assertStatus(200)
            ->assertJsonPath('success', true);
        $data = $live->json();
        $this->assertNotEmpty($data['articles']);
        $this->assertNotEmpty($data['locations']);
    }

    public function test_gtm_noscript_is_consent_gated_and_json_ld_uses_dynamic_settings(): void
    {
        Setting::setVal('google_active', '1', 'tracking');
        Setting::setVal('google_gtm_id', 'GTM-TEST999', 'tracking');
        Setting::setVal('privacy_strict_consent_gating', '1', 'privacy');
        Setting::setVal('site_name', 'Dunes Discovery Dynamic Brand', 'general');
        Setting::setVal('social_instagram', 'https://www.instagram.com/customduneshandle', 'general');
        app(\App\Services\SettingsService::class)->clearCache();

        $res = $this->get('/about');
        $res->assertStatus(200);
        $html = $res->getContent();

        // GTM noscript iframe must NOT emit before consent under strict gating
        $this->assertStringNotContainsString('googletagmanager.com/ns.html?id=GTM-TEST999', $html);
        // Consent-gated loader function IS present
        $this->assertStringContainsString('window._loadGtm', $html);
        // Dynamic @graph JSON-LD reflects CMS site_name and social_instagram
        $this->assertStringContainsString('Dunes Discovery Dynamic Brand', $html);
        $this->assertStringContainsString('https://www.instagram.com/customduneshandle', $html);
    }

    public function test_deepened_audit_checkers_detect_stale_duplicate_a11y_and_broken_internal_links(): void
    {
        $cat = Category::create(['name' => 'Safaris', 'slug' => 'safaris', 'status' => 'active']);
        $tour1 = Tour::create([
            'name' => 'Safari Alpha',
            'slug' => 'safari-alpha',
            'category_id' => $cat->id,
            'short_desc' => 'Identical long meta description used across two tours for testing duplication.',
            'full_desc' => '<p>Tour one content.</p>',
            'status' => 'active',
        ]);
        $tour2 = Tour::create([
            'name' => 'Safari Beta',
            'slug' => 'safari-beta',
            'category_id' => $cat->id,
            'short_desc' => 'Identical long meta description used across two tours for testing duplication.',
            'full_desc' => '<p>Tour two content.</p>',
            'status' => 'active',
        ]);

        // Stale content metadata overdue by 100 days
        SeoMetadata::create([
            'entity_type' => Tour::class,
            'entity_id' => $tour1->id,
            'stale_after_days' => 30,
            'reviewed_at' => now()->subDays(100),
        ]);

        $seoIssues = app(SeoAuditChecker::class)->run();
        $this->assertTrue(collect($seoIssues)->contains('check_key', 'seo_duplicate_meta_description'));

        $contentIssues = app(ContentQualityChecker::class)->run();
        $this->assertTrue(collect($contentIssues)->contains('check_key', 'content_stale_review_overdue'));

        // Probed page with missing label, empty button, broken internal link, and missing security headers
        $badProbe = [
            '/sample-bad-page' => [
                'status' => 200,
                'html' => '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>'
                    .'<body><h1>Title</h1><input type="text" name="email"><button></button><a href="/nonexistent-broken-internal-xyz-999">Broken</a></body></html>',
                'headers' => ['content-type' => 'text/html'],
            ],
        ];

        $uxIssues = app(UxChecker::class)->run(['probed_pages' => $badProbe]);
        $this->assertTrue(collect($uxIssues)->contains('check_key', 'ux_broken_internal_link'));

        $a11yIssues = app(AccessibilityChecker::class)->run(['probed_pages' => $badProbe]);
        $this->assertTrue(collect($a11yIssues)->contains('check_key', 'a11y_unlabeled_form_controls'));
        $this->assertTrue(collect($a11yIssues)->contains('check_key', 'a11y_empty_interactive_control'));

        $secIssues = app(SecurityChecker::class)->run(['probed_pages' => $badProbe]);
        $this->assertTrue(collect($secIssues)->contains('check_key', 'sec_missing_live_response_headers'));

        // Editor accessibility check catches missing alt and generic anchor
        $scorecard = app(KeywordIntelligenceService::class)->analyzeOnPage(
            'Evening Desert Safari Dubai Guide',
            'Discover authentic red dunes evening desert safari packages in Dubai with instant booking.',
            '<h1>Duplicate H1</h1><img src="/a.avif"><a href="/tours">click here</a>',
            'evening desert safari'
        );
        $this->assertNotEmpty($scorecard['accessibility_checks']);
    }
}
