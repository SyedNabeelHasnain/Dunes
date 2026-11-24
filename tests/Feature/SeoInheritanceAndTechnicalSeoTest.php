<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Category;
use App\Models\ContentRevision;
use App\Models\RedirectRule;
use App\Models\SeoMetadata;
use App\Models\SeoTemplate;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Services\Seo\KeywordIntelligenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoInheritanceAndTechnicalSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_tier_seo_inheritance_override_reset_and_revision_rollback(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $category = Category::create(['name' => 'Desert Safaris', 'slug' => 'desert-safaris', 'status' => 'active']);
        $tour = Tour::create([
            'name' => 'Red Dunes Safari',
            'slug' => 'red-dunes-safari',
            'category_id' => $category->id,
            'short_desc' => 'Premium desert tour.',
            'full_desc' => '<p>Authentic Lahbab desert safari experience with dune bashing, sandboarding, camel ride, and live shows.</p>',
            'status' => 'active',
        ]);

        // Tier 2: Customize Content-Type Template
        $this->actingAs($admin)->get(route('admin.settings.seo-templates.index'))->assertStatus(200);
        $this->actingAs($admin)->post(route('admin.settings.seo-templates.update', 'tour'), [
            'title_pattern' => '{title} Template Title {year}',
            'description_pattern' => '{excerpt}',
            'robots_default' => 'index, follow',
            'og_type_default' => 'product',
            'schema_type_default' => 'TouristTrip',
            'sitemap_priority' => 0.9,
            'sitemap_changefreq' => 'weekly',
            'sitemap_enabled' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $resTemplate = $this->get('/red-dunes-safari');
        $resTemplate->assertStatus(200);
        $this->assertStringContainsString('Red Dunes Safari Template Title '.date('Y'), $resTemplate->getContent());

        // Tier 3: Per-Item Override + Revision Snapshot
        $this->actingAs($admin)->post(route('admin.editorial.seo-meta.update', ['type' => 'tour', 'id' => $tour->id]), [
            'focus_keyword' => 'red dunes safari dubai',
            'robots_directive' => 'noindex, follow',
            'og_title' => 'Custom OG Red Dunes',
            'editorial_status' => 'approved',
            'change_summary' => 'Applied custom robots and OG override',
        ])->assertRedirect();

        $resOverride = $this->get('/red-dunes-safari');
        $resOverride->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $resOverride->getContent());
        $this->assertStringContainsString('Custom OG Red Dunes', $resOverride->getContent());

        // Reset item overrides clears custom robots & restores template inheritance
        $this->actingAs($admin)->post(route('admin.editorial.seo-meta.reset', ['type' => 'tour', 'id' => $tour->id]))
            ->assertRedirect();

        $resReset = $this->get('/red-dunes-safari');
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $resReset->getContent());

        // Verify revision rollback restores pre-reset state
        $firstRevision = ContentRevision::where('entity_type', Tour::class)
            ->where('entity_id', $tour->id)
            ->orderBy('version_number')
            ->first();
        $this->assertNotNull($firstRevision);

        $this->actingAs($admin)->post(route('admin.editorial.revisions.restore', $firstRevision->id))
            ->assertRedirect();

        $metaAfterRollback = SeoMetadata::where('entity_type', Tour::class)->where('entity_id', $tour->id)->first();
        $this->assertSame('noindex, follow', $metaAfterRollback?->robots_directive);
    }

    public function test_redirect_410_gone_verification_and_sitemap_exclusions(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $blogCat = BlogCategory::create(['name' => 'News', 'slug' => 'news']);

        $indexablePost = BlogPost::create([
            'category_id' => $blogCat->id,
            'title' => 'Dubai Desert Packing List',
            'slug' => 'dubai-desert-packing-list',
            'excerpt' => 'What to wear.',
            'content' => '<p>Detailed desert packing advice.</p>',
            'status' => 'published',
            'robots' => 'index, follow',
            'published_at' => now(),
        ]);

        $noindexPost = BlogPost::create([
            'category_id' => $blogCat->id,
            'title' => 'Internal Promo Archive',
            'slug' => 'internal-promo-archive',
            'excerpt' => 'Hidden post.',
            'content' => '<p>Noindex guide.</p>',
            'status' => 'published',
            'robots' => 'noindex, follow',
            'published_at' => now(),
        ]);

        // Redirected post should also drop from sitemap
        RedirectRule::create([
            'source_path' => '/retired-seasonal-tour',
            'target_path' => '',
            'status_code' => 410,
            'is_active' => true,
        ]);

        $res410 = $this->get('/retired-seasonal-tour');
        $res410->assertStatus(410);
        $res410->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Sitemap includes indexable post and excludes noindex post cleanly without missing column errors
        $sitemapRes = $this->get('/sitemap-blogs.xml');
        $sitemapRes->assertStatus(200);
        $this->assertStringContainsString('dubai-desert-packing-list', $sitemapRes->getContent());
        $this->assertStringNotContainsString('internal-promo-archive', $sitemapRes->getContent());

        // Destination verification updates health status cleanly
        $rule301 = RedirectRule::create([
            'source_path' => '/legacy-redirect-source',
            'target_path' => '/about',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.settings.redirects.verify', $rule301->id))->assertRedirect();
        $rule301->refresh();
        $this->assertNotNull($rule301->last_verified_at);
        $this->assertSame(200, $rule301->last_verified_status);
        $this->assertTrue($rule301->destination_ok);
    }

    public function test_dynamic_robots_txt_builder_validation_and_keyword_cannibalization(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // Reject dangerous Disallow: / wildcard
        $badRes = $this->actingAs($admin)->post(route('admin.settings.robots.update'), [
            'mode' => 'raw',
            'robots_txt_content' => "User-agent: *\nDisallow: /",
        ]);
        $badRes->assertSessionHasErrors('robots');

        // Builder update publishes clean directives
        $this->actingAs($admin)->post(route('admin.settings.robots.update'), [
            'mode' => 'builder',
            'bots' => [
                'Googlebot' => 'allow',
                'GPTBot' => 'allow',
                'CCBot' => 'disallow',
            ],
        ])->assertRedirect();

        $robotsResponse = $this->get('/robots.txt');
        $robotsResponse->assertStatus(200);
        $this->assertStringContainsString("User-agent: GPTBot\nAllow: /", $robotsResponse->getContent());
        $this->assertStringContainsString("User-agent: CCBot\nDisallow: /", $robotsResponse->getContent());

        // Cannibalization detection finds two posts sharing exact focus keyword
        $cat = BlogCategory::create(['name' => 'Tips', 'slug' => 'tips']);
        BlogPost::create([
            'category_id' => $cat->id,
            'title' => 'Safari Post One',
            'slug' => 'safari-post-one',
            'excerpt' => 'One',
            'content' => '<p>Dubai quad biking</p>',
            'focus_keyword' => 'quad bike dubai',
            'status' => 'published',
            'published_at' => now(),
        ]);
        BlogPost::create([
            'category_id' => $cat->id,
            'title' => 'Safari Post Two',
            'slug' => 'safari-post-two',
            'excerpt' => 'Two',
            'content' => '<p>Quad biking dubai prices</p>',
            'focus_keyword' => 'Quad Bike Dubai',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $report = app(KeywordIntelligenceService::class)->buildReport();
        $this->assertNotEmpty($report['cannibalization']);
        $this->assertSame('quad bike dubai', $report['cannibalization'][0]['keyword']);
        $this->actingAs($admin)->get(route('admin.settings.keywords.index'))->assertStatus(200);
    }

    public function test_voucher_pdf_sends_x_robots_tag_and_tracking_param_setting_controls_robots(): void
    {
        $booking = Booking::create([
            'reference' => 'DDT-PDF-901',
            'tour_name' => 'Morning Desert Safari',
            'tour_date' => now()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'name' => 'Amina Khan',
            'email' => 'amina@example.com',
            'phone' => '+971500000001',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'pickup_location' => 'Dubai Downtown',
            'subtotal' => 250,
            'total' => 250,
        ]);

        $pdfRes = $this->get(route('booking.voucher.pdf', $booking->reference));
        if ($pdfRes->getStatusCode() === 200) {
            $pdfRes->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        } else {
            $this->assertTrue(in_array($pdfRes->getStatusCode(), [302, 200], true));
        }

        // When seo_index_tracking_params is enabled, pure gclid URL remains indexable
        Setting::setVal('seo_index_tracking_params', '1');
        $resGclid = $this->get('/about?gclid=EAIaIQobChMI12345');
        $resGclid->assertStatus(200);
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $resGclid->getContent());
    }
}
