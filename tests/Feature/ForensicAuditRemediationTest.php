<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostFaq;
use App\Models\BlogTag;
use App\Models\Booking;
use App\Models\Category;
use App\Models\ContentItem;
use App\Models\Driver;
use App\Models\EmailCampaign;
use App\Models\EmailOtp;
use App\Models\IpRule;
use App\Models\LegalItem;
use App\Models\LegalPage;
use App\Models\LegalSection;
use App\Models\MediaAsset;
use App\Models\Review;
use App\Models\Setting;
use App\Models\SpamEvent;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use App\Models\VerifiedEmail;
use App\Models\WhatsappInquiry;
use App\Models\WikiArticle;
use App\Models\WikiGlossaryTerm;
use App\Models\WikiPillar;
use App\Services\CmsContentService;
use App\Services\MediaManagerService;
use App\Services\Security\SafeUrlValidator;
use App\Services\Security\SvgSanitizer;
use App\Services\SessionRecordingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ForensicAuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_ajax_legal_content_renders_valid_link_and_get_mutations_return_405(): void
    {
        $page = LegalPage::firstOrCreate(
            ['slug' => 'terms'],
            [
                'title' => ['en' => 'Terms & Conditions'],
                'subtitle' => ['en' => 'Booking Terms'],
                'description' => ['en' => 'Legal terms summary.'],
            ]
        );
        $section = LegalSection::firstOrCreate(
            ['page_id' => $page->id, 'priority' => 1],
            ['heading' => ['en' => 'General'], 'subheading' => ['en' => 'Overview']]
        );
        LegalItem::firstOrCreate(
            ['section_id' => $section->id, 'priority' => 1],
            ['content' => ['en' => 'All bookings are subject to availability.']]
        );

        $response = $this->post('/ajax.php', [
            'action' => 'get_legal_content',
            'type' => 'terms',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $html = (string) $response->json('html');
        $this->assertStringContainsString('/terms', $html);
        $this->assertStringContainsString('Terms', $html);

        // State-changing action via GET must be rejected with 405
        $this->get('/ajax.php?action=booking')->assertStatus(405);
        $this->get('/ajax.php?action=contact')->assertStatus(405);
    }

    public function test_localized_non_slug_routes_and_voucher_controller_bind_reference_cleanly(): void
    {
        $voucherUrl = localized_route('booking.voucher', ['reference' => 'DT-LOCAL-901'], 'ar');
        $this->assertStringContainsString('/ar/booking/DT-LOCAL-901/voucher', $voucherUrl);

        $recoverUrl = localized_route('booking.recover', 'DT-LOCAL-901', 'ar');
        $this->assertStringContainsString('/ar/checkout/DT-LOCAL-901', $recoverUrl);

        $booking = Booking::create([
            'reference' => 'DT-LOCAL-901',
            'tour_name' => 'Evening Desert Safari Dubai',
            'tier_name' => 'Classic',
            'tour_date' => now()->addDays(5)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'name' => 'Amira Guest',
            'email' => 'amira@example.com',
            'phone' => '+971501112233',
            'subtotal' => 200,
            'addons_total' => 0,
            'total' => 200,
            'currency' => 'AED',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'balance_due' => 200,
        ]);

        $res = $this->get('/ar/booking/'.$booking->reference.'/voucher');
        $res->assertStatus(200);
        $res->assertSee('DT-LOCAL-901');
    }

    public function test_set_locale_blocks_backslash_open_redirect_and_keeps_url_defaults_clean(): void
    {
        $res = $this->get('/en/%5Cexample.com/phish');
        $location = (string) $res->headers->get('Location', '');

        $this->assertStringNotContainsString('\\', $location);
        $this->assertStringNotContainsString('//example.com', $location);

        // Regular English request must leave URL default parameters clean (no ['locale' => null])
        $this->get('/about')->assertStatus(200);
        $this->assertSame([], URL::getDefaultParameters());
    }

    public function test_thankyou_wildcard_ref_and_draft_save_idor_are_blocked(): void
    {
        $victimDraft = Booking::create([
            'reference' => 'DFT261126ABCD',
            'tour_name' => 'Evening Desert Safari Dubai',
            'tier_name' => 'Classic',
            'tour_date' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'name' => 'Victim Customer',
            'email' => 'victim@example.com',
            'phone' => '+971509998877',
            'subtotal' => 200,
            'addons_total' => 0,
            'total' => 200,
            'currency' => 'AED',
            'status' => 'draft',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'balance_due' => 200,
            'draft_token' => 'secret-victim-token-123456',
        ]);

        // Wildcard LIKE pattern must not match arbitrary booking via /thankyou?ref=%
        $res = $this->get('/thankyou?ref=%25');
        $res->assertDontSee('Victim Customer');
        $res->assertDontSee('victim@example.com');

        // Unauthenticated attacker submitting victim's draft_id without victim's draft_token must not overwrite it
        $attack = $this->postJson('/booking/draft', [
            'draft_id' => $victimDraft->id,
            'draft_token' => 'wrong-attacker-token',
            'name' => 'Attacker Name',
            'email' => 'attacker@example.com',
            'phone' => '+971500000001',
            'tour_name' => 'Evening Desert Safari Dubai',
            'tour_date' => now()->addDays(4)->toDateString(),
            'adults' => 1,
        ]);

        $this->assertContains($attack->getStatusCode(), [200, 403, 422]);
        $victimDraft->refresh();
        $this->assertSame('Victim Customer', $victimDraft->name);
        $this->assertSame('victim@example.com', $victimDraft->email);
    }

    public function test_svg_sanitizer_blocks_entity_encoded_javascript_smil_and_tail_polyglot(): void
    {
        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg">'
            .'<a href="&#x6a;avascript:alert(1)"><rect width="10" height="10"/></a>'
            .'<animate attributeName="href" to="javascript:alert(2)"/>'
            .'<foreignObject><div>evil</div></foreignObject>'
            .'<circle cx="5" cy="5" r="4"/>'
            .'</svg>';

        $clean = SvgSanitizer::sanitize($maliciousSvg);

        $this->assertStringNotContainsStringIgnoringCase('javascript:', $clean);
        $this->assertStringNotContainsStringIgnoringCase('<animate', $clean);
        $this->assertStringNotContainsStringIgnoringCase('foreignObject', $clean);
        $this->assertStringContainsString('<circle', $clean);

        // Full-file polyglot detection past byte 2048 including <?= short echo tag
        $tmpFile = tempnam(sys_get_temp_dir(), 'svg_poly_');
        file_put_contents($tmpFile, str_repeat('A', 4096).'<?= system("id") ?>');
        try {
            $caught = false;
            try {
                SvgSanitizer::assertNoScriptPolyglot($tmpFile, 'jpg');
            } catch (\InvalidArgumentException) {
                $caught = true;
            }
            $this->assertTrue($caught, 'assertNoScriptPolyglot must detect <?= beyond byte 2048.');
        } finally {
            @unlink($tmpFile);
        }
    }

    public function test_safe_url_validator_blocks_obfuscated_loopback_ips(): void
    {
        foreach (['http://127.1/', 'http://2130706433/', 'http://0177.0.0.1/', 'http://0x7f000001/'] as $blockedUrl) {
            $caught = false;
            try {
                SafeUrlValidator::assertSafeUrl($blockedUrl, resolveDns: false);
            } catch (\InvalidArgumentException) {
                $caught = true;
            }
            $this->assertTrue($caught, "SafeUrlValidator must block obfuscated loopback URL: {$blockedUrl}");
        }

        $this->assertSame(
            'https://dunesdiscoverytourism.com/tours',
            SafeUrlValidator::assertSafeUrl('https://dunesdiscoverytourism.com/tours', resolveDns: false)
        );
    }

    public function test_rbac_fails_closed_and_redacts_secrets_from_public_settings(): void
    {
        $unknownRoleUser = User::factory()->create(['role' => 'unrecognized_legacy_role', 'is_active' => true]);
        $this->assertSame('viewer', $unknownRoleUser->normalizedRole());
        $this->assertFalse($unknownRoleUser->isSuperAdmin());

        // Public settings helper must strip all sensitive keys
        Setting::updateOrCreate(['setting_key' => 'smtp_pass'], ['setting_value' => 'super-secret-smtp-pass']);
        Setting::updateOrCreate(['setting_key' => 'ziina_access_token'], ['setting_value' => 'super-secret-ziina-token']);
        Setting::updateOrCreate(['setting_key' => 'deepl_auth_key'], ['setting_value' => 'super-secret-deepl-key']);
        Setting::updateOrCreate(['setting_key' => 'notification_webhook_secret'], ['setting_value' => 'super-secret-webhook']);
        Setting::updateOrCreate(['setting_key' => 'site_name'], ['setting_value' => 'Dunes Discovery Tourism']);

        $settings = app(SettingsService::class);
        $settings->clearCache();
        $public = $settings->publicAll();

        $this->assertArrayNotHasKey('smtp_pass', $public);
        $this->assertArrayNotHasKey('ziina_access_token', $public);
        $this->assertArrayNotHasKey('deepl_auth_key', $public);
        $this->assertArrayNotHasKey('notification_webhook_secret', $public);
        $this->assertSame('Dunes Discovery Tourism', $public['site_name'] ?? null);

        // SEO manager must NOT be able to overwrite security/payment secrets via general settings update
        $seoManager = User::factory()->create(['role' => 'seo_manager', 'is_active' => true]);
        $this->actingAs($seoManager)
            ->post(route('admin.settings.update'), [
                'ziina_access_token' => 'hijacked-token',
                'smtp_pass' => 'hijacked-pass',
            ]);

        $settings->clearCache();
        $this->assertSame('super-secret-ziina-token', $settings->get('ziina_access_token'));
        $this->assertSame('super-secret-smtp-pass', $settings->get('smtp_pass'));
    }

    public function test_gallery_invalid_filter_neutralizes_xss(): void
    {
        $res = $this->get('/gallery?type='.urlencode('"><script>alert(1)</script>'));
        $res->assertStatus(200);
        $res->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_serializable_classes_false_round_trips_cached_cms_and_home_feeds(): void
    {
        $this->assertFalse(config('cache.serializable_classes'));

        Cache::flush();

        // Two consecutive hits verify both cache-miss population AND cache-hit hydration
        $cms = app(CmsContentService::class);
        $page1 = $cms->getPage('home');
        $page2 = $cms->getPage('home');
        $this->assertSame($page1?->slug, $page2?->slug);

        $menu1 = $cms->getMenuItems('header');
        $menu2 = $cms->getMenuItems('header');
        $this->assertSame($menu1->count(), $menu2->count());

        $this->get('/')->assertStatus(200);
        $this->get('/')->assertStatus(200);

        $this->get('/tours')->assertStatus(200);
        $this->get('/tours')->assertStatus(200);
    }

    public function test_media_manager_reports_and_session_recording_schema_fixes(): void
    {
        $admin = $this->createSuperAdmin();

        Review::create([
            'source' => 'website',
            'reviewer_name' => 'Schema Check Guest',
            'rating' => 5.0,
            'review_text' => 'Fantastic desert experience.',
            'status' => 'approved',
            'photos' => ['images/test-review-asset.webp'],
        ]);

        Driver::create([
            'name' => 'Marshal Rashid',
            'phone' => '+971501234567',
            'photo' => 'images/test-driver-asset.webp',
            'status' => 'active',
        ]);

        $asset = MediaAsset::create([
            'filename' => 'test-review-asset.webp',
            'original_name' => 'test-review-asset.webp',
            'disk' => 'public',
            'folder' => 'images',
            'file_path' => 'images/test-review-asset.webp',
            'public_url' => '/images/test-review-asset.webp',
            'mime_type' => 'image/webp',
            'media_type' => 'image',
            'extension' => 'webp',
            'file_size_bytes' => 12345,
        ]);

        $usages = app(MediaManagerService::class)->resolveAssetUsages($asset);
        $this->assertIsArray($usages);

        $picker = $this->actingAs($admin)->getJson(route('admin.media.picker-items'));
        $picker->assertStatus(200);
        $items = $picker->json('assets') ?? [];
        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('public_url', $items[0]);
        $this->assertArrayHasKey('clean_public_url', $items[0]);

        $this->actingAs($admin)->get(route('admin.reports.index'))->assertStatus(200);

        $stats = app(SessionRecordingService::class)->getStorageStats();
        $this->assertArrayHasKey('retention_days', $stats);
    }

    public function test_privacy_purge_command_prunes_expired_otps_and_aged_spam_events(): void
    {
        EmailOtp::unguard();
        try {
            EmailOtp::create([
                'email' => 'old-otp@example.com',
                'otp' => '123456',
                'expires_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
            ]);
            EmailOtp::create([
                'email' => 'fresh-otp@example.com',
                'otp' => '654321',
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ]);
        } finally {
            EmailOtp::reguard();
        }

        SpamEvent::create([
            'channel' => 'contact',
            'ip_address' => '203.0.113.10',
            'email' => 'bot@spam.invalid',
            'spam_score' => 95,
            'action_taken' => 'blocked',
            'created_at' => now()->subDays(120),
        ]);
        SpamEvent::create([
            'channel' => 'contact',
            'ip_address' => '203.0.113.11',
            'email' => 'recent@spam.invalid',
            'spam_score' => 80,
            'action_taken' => 'blocked',
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('privacy:purge-expired')->assertExitCode(0);

        $this->assertDatabaseMissing('email_otps', ['email' => 'old-otp@example.com']);
        $this->assertDatabaseHas('email_otps', ['email' => 'fresh-otp@example.com']);
        $this->assertDatabaseMissing('spam_events', ['email' => 'bot@spam.invalid']);
        $this->assertDatabaseHas('spam_events', ['email' => 'recent@spam.invalid']);
    }

    public function test_wave3_all_twelve_admin_crud_modules_operate_cleanly(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        // 1. BlogPostFaq CRUD
        $blogCat = BlogCategory::firstOrCreate(
            ['slug' => 'forensic-guides'],
            ['name' => ['en' => 'Forensic Guides'], 'status' => 'active']
        );
        $post = BlogPost::create([
            'slug' => 'forensic-test-post',
            'title' => ['en' => 'Forensic Test Post'],
            'category_id' => $blogCat->id,
            'excerpt' => ['en' => 'Summary'],
            'content' => ['en' => '<p>Body</p>'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->post(route('admin.blogs.faqs.store', $post->id), [
            'question' => ['en' => 'Is pickup included?'],
            'answer' => ['en' => 'Yes, 4x4 hotel pickup is included.'],
            'priority' => 1,
        ])->assertRedirect(route('admin.blogs.edit', $post->id));

        $blogFaq = BlogPostFaq::where('post_id', $post->id)->firstOrFail();
        $this->put(route('admin.blogs.faqs.update', $blogFaq->id), [
            'question' => ['en' => 'Is 4x4 pickup included?'],
            'answer' => ['en' => 'Yes, across all Dubai hotels.'],
            'priority' => 2,
        ])->assertRedirect(route('admin.blogs.edit', $post->id));
        $this->assertSame(2, $blogFaq->fresh()->priority);

        $this->delete(route('admin.blogs.faqs.destroy', $blogFaq->id))
            ->assertRedirect(route('admin.blogs.edit', $post->id));
        $this->assertDatabaseMissing('blog_post_faqs', ['id' => $blogFaq->id]);

        // 2. BlogTag CRUD
        $this->post(route('admin.blog-tags.store'), ['name' => 'Forensic Desert Tag'])
            ->assertRedirect(route('admin.blog-categories.index'));
        $tag = BlogTag::where('slug', 'forensic-desert-tag')->firstOrFail();
        $this->put(route('admin.blog-tags.update', $tag->id), ['name' => 'Updated Desert Tag'])
            ->assertRedirect(route('admin.blog-categories.index'));
        $this->assertSame('updated-desert-tag', $tag->fresh()->slug);
        $this->delete(route('admin.blog-tags.destroy', $tag->id))
            ->assertRedirect(route('admin.blog-categories.index'));
        $this->assertDatabaseMissing('blog_tags', ['id' => $tag->id]);

        // 3. ContentItem CRUD
        $createCi = $this->postJson(route('admin.tours.content-items.create'), [
            'type' => 'inclusion',
            'title' => ['en' => 'Sunset Photo Stop'],
            'description' => ['en' => 'High red dune viewpoint'],
            'priority' => 5,
        ]);
        $createCi->assertStatus(200)->assertJson(['success' => true]);
        $ciId = $createCi->json('item.id');

        $this->putJson(route('admin.tours.content-items.update', $ciId), [
            'type' => 'inclusion',
            'title' => ['en' => 'Lahbab Sunset Photo Stop'],
            'priority' => 3,
        ])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame(3, ContentItem::findOrFail($ciId)->priority);

        $this->deleteJson(route('admin.tours.content-items.destroy', $ciId))
            ->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('content_items', ['id' => $ciId]);

        // 4. Category CRUD
        $catRes = $this->postJson(route('admin.categories.create'), ['name' => 'Forensic Experiences']);
        $catRes->assertStatus(200)->assertJson(['success' => true]);
        $catId = $catRes->json('category.id');

        $this->putJson(route('admin.categories.update', $catId), [
            'name' => 'VIP Forensic Experiences',
            'priority' => 7,
        ])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame('vip-forensic-experiences', Category::findOrFail($catId)->slug);

        $this->deleteJson(route('admin.categories.destroy', $catId))
            ->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('categories', ['id' => $catId]);

        // 5. WikiPillar & 6. WikiGlossaryTerm Update CRUD
        $pillar = WikiPillar::create([
            'slug' => 'forensic-pillar',
            'name' => ['en' => 'Forensic Pillar'],
            'status' => 'active',
            'priority' => 1,
        ]);
        $this->put(route('admin.wiki.pillars.update', $pillar->id), [
            'name' => ['en' => 'Updated Forensic Pillar'],
            'slug' => 'forensic-pillar',
            'status' => 'active',
            'priority' => 4,
        ])->assertRedirect(route('admin.wiki.pillars'));
        $this->assertSame(4, $pillar->fresh()->priority);

        $term = WikiGlossaryTerm::create([
            'term_key' => 'forensic-dune',
            'term' => ['en' => 'Forensic Dune'],
            'definition' => ['en' => 'Initial definition'],
        ]);
        $this->put(route('admin.wiki.glossary.update', $term->id), [
            'term' => ['en' => 'Lahbab Red Dune'],
            'term_key' => 'forensic-dune',
            'definition' => ['en' => 'Updated definition'],
        ])->assertRedirect(route('admin.wiki.glossary'));
        $this->assertSame('Lahbab Red Dune', $term->fresh()->getTranslation('term', 'en'));

        // 7. LegalSection & LegalItem Update CRUD
        $legalPage = LegalPage::firstOrCreate(
            ['slug' => 'privacy'],
            ['title' => ['en' => 'Privacy Policy']]
        );
        $legalSection = LegalSection::create([
            'page_id' => $legalPage->id,
            'heading' => ['en' => 'Data Retention'],
            'priority' => 1,
        ]);
        $this->put(route('admin.legal.section.update', $legalSection->id), [
            'heading' => ['en' => 'Updated Data Retention'],
            'subheading' => ['en' => 'Retention details'],
            'priority' => 2,
        ])->assertRedirect(route('admin.legal.edit', $legalPage->id));
        $this->assertSame(2, $legalSection->fresh()->priority);

        $legalItem = LegalItem::create([
            'section_id' => $legalSection->id,
            'content' => ['en' => 'Initial clause'],
            'priority' => 1,
        ]);
        $this->put(route('admin.legal.item.update', $legalItem->id), [
            'content' => ['en' => 'Updated clause text'],
            'priority' => 3,
        ])->assertRedirect(route('admin.legal.edit', $legalPage->id));
        $this->assertSame(3, $legalItem->fresh()->priority);

        // 8. IpRule Update CRUD
        $ipRule = IpRule::create([
            'ip_address' => '198.51.100.42',
            'mode' => 'monitor',
            'label' => 'QA Probe',
            'status' => 'active',
        ]);
        $this->put(route('admin.security.ip-rules.update', $ipRule->id), [
            'mode' => 'ignore_analytics',
            'label' => 'Updated QA Probe',
            'reason' => 'Internal monitoring',
            'status' => 'active',
        ])->assertRedirect();
        $this->assertSame('ignore_analytics', $ipRule->fresh()->mode);

        // 9. EmailCampaign Edit View & Update CRUD
        $campaign = EmailCampaign::create([
            'title' => 'Draft Desert Promo',
            'subject' => 'Desert Offer',
            'target_type' => 'all',
            'content_html' => '<p>Hello Desert Explorer</p>',
            'status' => 'draft',
        ]);
        $this->get(route('admin.campaigns.edit', $campaign->id))->assertStatus(200);
        $this->put(route('admin.campaigns.update', $campaign->id), [
            'title' => 'Updated Desert Promo',
            'subject' => 'Updated Desert Offer',
            'target_type' => 'all',
            'content_html' => '<p>Updated HTML Content</p>',
        ])->assertRedirect(route('admin.campaigns.show', $campaign->id));
        $this->assertSame('Updated Desert Promo', $campaign->fresh()->title);

        // 10. Heatmap Settings & Purge
        $this->post(route('admin.analytics.heatmaps.settings'), ['heatmaps_enabled' => '0'])
            ->assertRedirect();
        $this->assertSame('0', Setting::where('setting_key', 'heatmaps_enabled')->value('setting_value'));
        $this->post(route('admin.analytics.heatmaps.purge'), ['days' => 30])->assertRedirect();

        // 11. SpamEvent Purge
        SpamEvent::create([
            'channel' => 'contact',
            'ip_address' => '198.51.100.99',
            'spam_score' => 99,
            'action_taken' => 'blocked',
            'created_at' => now()->subDays(10),
        ]);
        $this->post(route('admin.security.spam-events.purge'), ['days' => 0])
            ->assertRedirect(route('admin.security.ip-rules', ['tab' => 'spam']));
        $this->assertSame(0, SpamEvent::count());

        // 12. VerifiedEmail Manual Verify & Revoke
        $this->post(route('admin.security.verified-emails.store'), [
            'email' => 'vip.guest@example.com',
        ])->assertRedirect(route('admin.security.ip-rules', ['tab' => 'verified_emails']));
        $ve = VerifiedEmail::where('email', 'vip.guest@example.com')->firstOrFail();

        $this->delete(route('admin.security.verified-emails.destroy', $ve->id))
            ->assertRedirect(route('admin.security.ip-rules', ['tab' => 'verified_emails']));
        $this->assertDatabaseMissing('verified_emails', ['id' => $ve->id]);
    }

    public function test_wave5_schema_columns_indexes_and_whatsapp_ref_code_persistence(): void
    {
        $this->assertTrue(Schema::hasTable('wiki_article_tours'));
        $this->assertTrue(Schema::hasColumn('tour_tiers', 'child_price'));
        $this->assertTrue(Schema::hasColumn('whatsapp_inquiries', 'ref_code'));

        // Verify key performance indexes exist
        $this->assertTrue(Schema::hasIndex('tiers', 'tiers_status_idx') || Schema::hasIndex('tiers', ['status']));
        $this->assertTrue(Schema::hasIndex('wiki_articles', 'wiki_articles_status_idx') || Schema::hasIndex('wiki_articles', ['status']));
        $this->assertTrue(Schema::hasIndex('audit_runs', 'audit_runs_status_created_idx') || Schema::hasIndex('audit_runs', ['status', 'created_at']));
        $this->assertTrue(Schema::hasIndex('reviews', 'reviews_published_date_idx') || Schema::hasIndex('reviews', ['published_date']));

        // Verify WhatsApp log persists ref_code
        $waRes = $this->postJson('/api/v1/whatsapp/log', [
            'name' => 'Forensic Lead',
            'phone' => '+971501239999',
            'tour_name' => 'Evening Desert Safari Dubai',
            'page_url' => 'https://dunesdiscoverytourism.com/evening-desert-safari-dubai',
            'message' => 'Hello, interested in VIP safari.',
        ]);

        $waRes->assertStatus(200)->assertJson(['success' => true]);
        $refCode = $waRes->json('ref_code');
        $this->assertNotEmpty($refCode);
        $this->assertDatabaseHas('whatsapp_inquiries', [
            'id' => $waRes->json('inquiry_id'),
            'ref_code' => $refCode,
        ]);

        // Verify LegalPage titles are NOT double-encoded JSON
        foreach (DB::table('legal_pages')->get() as $page) {
            if (is_string($page->title) && str_starts_with(trim($page->title), '{')) {
                $decoded = json_decode($page->title, true);
                if (is_array($decoded) && isset($decoded['en']) && is_string($decoded['en'])) {
                    $this->assertFalse(
                        str_starts_with(trim($decoded['en']), '{"en":'),
                        "LegalPage #{$page->id} title is double-encoded JSON."
                    );
                }
            }
        }
    }

    public function test_error_pages_render_safely_without_javascript_href(): void
    {
        $html500 = view('errors.500')->render();
        $this->assertStringNotContainsString('javascript:location.reload', $html500);
        $this->assertStringContainsString('window.location.reload()', $html500);

        $html429 = view('errors.429')->render();
        $this->assertStringNotContainsString('javascript:location.reload', $html429);
        $this->assertStringContainsString('window.location.reload()', $html429);
    }
}
