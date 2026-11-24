<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\AiCitationObservation;
use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ChangeApproval;
use App\Models\ConfigAuditLog;
use App\Models\Page;
use App\Models\PrivacyConsent;
use App\Models\RedirectRule;
use App\Models\RequestLog;
use App\Models\SeoTemplate;
use App\Models\Tour;
use App\Models\User;
use App\Models\WebVitalsMetric;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use App\Services\Governance\ConfigAuditService;
use App\Services\Seo\SeoResolverService;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionRegressionAndSessionIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function resetInMemorySessionAndAuth(): void
    {
        $this->app['auth']->forgetGuards();
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        $this->app->forgetInstance('auth.driver');
    }

    private function extractSessionCookieValue(\Illuminate\Testing\TestResponse $response): string
    {
        $cookieName = (string) config('session.cookie');
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return (string) $cookie->getValue();
            }
        }

        $this->fail("Expected response cookie '{$cookieName}' was not emitted.");
    }

    private function decodeSessionIdFromCookie(string $cookieValue): string
    {
        $decrypted = app('encrypter')->decrypt($cookieValue, false);
        $prefix = CookieValuePrefix::create((string) config('session.cookie'), app('encrypter')->getKey());

        return str_starts_with($decrypted, $prefix)
            ? substr($decrypted, strlen($prefix))
            : $decrypted;
    }

    public function test_database_session_and_csrf_survive_incremental_scan_full_audit_live_page_audit_and_redirect_verify(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create([
            'email' => 'session-admin@dunesdiscovery.com',
            'password' => Hash::make('StrongPass123!'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        RedirectRule::create([
            'source_path' => '/legacy-test-source',
            'target_path' => '/tours',
            'status_code' => 301,
            'match_type' => 'exact',
            'is_active' => true,
        ]);
        $redirectId = RedirectRule::where('source_path', '/legacy-test-source')->value('id');

        // Step 1: GET /login to acquire initial guest session cookie & CSRF token
        $loginFormResponse = $this->get(route('login'));
        $loginFormResponse->assertStatus(200);
        $guestCookie = $this->extractSessionCookieValue($loginFormResponse);
        $csrfToken = (string) session()->token();
        $this->assertNotEmpty($csrfToken);

        // Step 2: Real POST /login with credentials & CSRF
        $this->resetInMemorySessionAndAuth();
        $loginResponse = $this->withUnencryptedCookie((string) config('session.cookie'), $guestCookie)
            ->post(route('login'), [
                '_token' => $csrfToken,
                'email' => 'session-admin@dunesdiscovery.com',
                'password' => 'StrongPass123!',
            ]);
        $loginResponse->assertRedirect();
        $authCookie = $this->extractSessionCookieValue($loginResponse);
        $adminSessionId = $this->decodeSessionIdFromCookie($authCookie);
        $adminCsrfToken = (string) session()->token();

        $this->assertDatabaseHas('sessions', ['id' => $adminSessionId, 'user_id' => $admin->id]);

        // Step 3: Trigger POST /admin/audit/run (Incremental Scan) simulating fresh browser request
        $this->resetInMemorySessionAndAuth();
        $incResponse = $this->withUnencryptedCookie((string) config('session.cookie'), $authCookie)
            ->post(route('admin.audit.run'), ['_token' => $adminCsrfToken, 'scan_type' => 'incremental']);
        $incResponse->assertRedirect(route('admin.audit.index'));
        $authCookieAfterInc = $this->extractSessionCookieValue($incResponse);
        $this->assertSame($adminSessionId, $this->decodeSessionIdFromCookie($authCookieAfterInc));
        $this->assertSame([], URL::getDefaultParameters());
        $this->assertSame('en', App::getLocale());

        // Step 4: Next click MUST stay logged in (no redirect to /login)
        $this->resetInMemorySessionAndAuth();
        $govResponse = $this->withUnencryptedCookie((string) config('session.cookie'), $authCookieAfterInc)
            ->get(route('admin.governance.index'));
        $govResponse->assertStatus(200);

        // Step 5: Trigger POST /admin/audit/run (Full Scan) with exact same session cookie + CSRF
        $this->resetInMemorySessionAndAuth();
        $fullResponse = $this->withUnencryptedCookie((string) config('session.cookie'), $authCookieAfterInc)
            ->post(route('admin.audit.run'), ['_token' => $adminCsrfToken, 'scan_type' => 'full']);
        $fullResponse->assertRedirect(route('admin.audit.index'));
        $authCookieAfterFull = $this->extractSessionCookieValue($fullResponse);
        $this->assertSame($adminSessionId, $this->decodeSessionIdFromCookie($authCookieAfterFull));

        // Step 6: Trigger POST /admin/audit/page (Editor Live Page Audit Drawer with credentials)
        $this->resetInMemorySessionAndAuth();
        $pageAuditResponse = $this->withCredentials()
            ->withUnencryptedCookie((string) config('session.cookie'), $authCookieAfterFull)
            ->postJson(route('admin.audit.page'), [
                '_token' => $adminCsrfToken,
                'url' => '/ar',
            ]);
        $pageAuditResponse->assertStatus(200)->assertJsonStructure(['url', 'path', 'http_status', 'health_score', 'issues_count', 'issues']);
        $authCookieAfterDrawer = $this->extractSessionCookieValue($pageAuditResponse);
        $this->assertSame($adminSessionId, $this->decodeSessionIdFromCookie($authCookieAfterDrawer));
        // Locale from /ar internal probe must never leak back onto the parent request globals
        $this->assertSame('en', App::getLocale());
        $this->assertSame([], URL::getDefaultParameters());

        // Step 7: Trigger POST /admin/settings/redirects/{id}/verify
        $this->resetInMemorySessionAndAuth();
        $verifyResponse = $this->withUnencryptedCookie((string) config('session.cookie'), $authCookieAfterDrawer)
            ->post(route('admin.settings.redirects.verify', $redirectId), ['_token' => $adminCsrfToken]);
        $verifyResponse->assertRedirect(route('admin.settings.redirects.index'));
        $authCookieAfterVerify = $this->extractSessionCookieValue($verifyResponse);
        $this->assertSame($adminSessionId, $this->decodeSessionIdFromCookie($authCookieAfterVerify));

        // Step 8: Final GET confirms session DB row, user_id, and CSRF token are still completely valid
        $this->resetInMemorySessionAndAuth();
        $finalHealth = $this->withUnencryptedCookie((string) config('session.cookie'), $authCookieAfterVerify)
            ->get(route('admin.health.index'));
        $finalHealth->assertStatus(200);
        $this->assertDatabaseHas('sessions', ['id' => $adminSessionId, 'user_id' => $admin->id]);
    }

    public function test_all_governance_health_ai_visibility_redirect_and_seo_routes_render_with_seeded_mysql_parity_data(): void
    {
        $this->assertFalse(Schema::hasColumn('request_logs', 'action_type'));
        $this->assertTrue(Schema::hasColumn('request_logs', 'entity_type'));

        $admin = User::factory()->create([
            'email' => 'parity-admin@dunesdiscovery.com',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Seed 404 logs, AI bot hits, and AI referrer visits using true schema column entity_type
        RequestLog::create([
            'entity_type' => 'error_404',
            'request_timestamp' => now()->subHour(),
            'request_method' => 'GET',
            'request_uri' => '/missing-old-safari-url',
            'client_ip' => '203.0.113.10',
        ]);
        RequestLog::create([
            'entity_type' => 'navigation',
            'request_timestamp' => now()->subMinutes(20),
            'request_method' => 'GET',
            'request_uri' => '/tours',
            'user_agent' => 'GPTBot/1.0',
            'referrer_url' => 'https://chatgpt.com/c/example',
            'client_ip' => '203.0.113.11',
        ]);

        ChangeApproval::create([
            'section' => 'seo',
            'target_key' => 'seo_home_title',
            'summary' => 'Update Homepage Meta Title',
            'payload' => ['seo_home_title' => 'New Title'],
            'previous_payload' => ['seo_home_title' => 'Old Title'],
            'status' => 'pending_review',
            'requested_by' => $admin->id,
        ]);

        app(ConfigAuditService::class)->log(
            section: 'system',
            action: 'sync',
            settingKey: 'cron_sync',
            oldValue: '0',
            newValue: '1',
            actor: null,
        );

        PrivacyConsent::create([
            'consent_id' => 'consent-test-123',
            'analytics' => true,
            'marketing' => false,
            'functional' => true,
            'consent_version' => 'v2',
            'action' => 'accept_all',
            'consented_at' => now(),
        ]);

        WebVitalsMetric::create([
            'page_path' => '/',
            'metric_name' => 'LCP',
            'metric_value' => 1450.5,
            'rating' => 'good',
            'recorded_at' => now(),
        ]);

        AiCitationObservation::create([
            'platform' => 'chatgpt',
            'prompt_query' => 'best desert safari dubai',
            'cited_url' => 'https://dunesdiscoverytourism.com/',
            'brand_mentioned' => true,
            'citation_position' => 1,
            'sentiment' => 'positive',
            'observed_at' => now(),
            'recorded_by' => $admin->id,
        ]);

        $auditRun = AuditRun::create([
            'scan_type' => 'incremental',
            'status' => 'completed',
            'health_score' => 94,
            'total_issues_count' => 1,
            'critical_count' => 0,
            'high_count' => 0,
            'medium_count' => 1,
            'low_count' => 0,
            'scanned_urls_count' => 5,
            'duration_ms' => 120,
            'category_scores' => ['technical_seo' => ['score' => 95, 'passed' => 9, 'failed' => 1]],
            'triggered_by' => $admin->id,
        ]);
        AuditIssue::create([
            'audit_run_id' => $auditRun->id,
            'category' => 'ux',
            'check_key' => 'ux_unresolved_404_spike',
            'title' => 'Test 404 Issue',
            'description' => 'Sample finding',
            'severity' => 'medium',
            'verification_type' => 'automated',
            'status' => 'open',
            'affected_url' => 'https://dunesdiscoverytourism.com/missing-old-safari-url',
        ]);

        RedirectRule::create([
            'source_path' => '/old-page',
            'target_path' => '/tours',
            'status_code' => 301,
            'match_type' => 'exact',
            'is_active' => true,
            'last_verified_at' => now(),
            'last_verified_status' => 200,
            'destination_ok' => true,
            'created_by' => $admin->id,
        ]);

        SeoTemplate::ensureDefaultsExist();

        $cat = Category::first() ?: Category::create(['name' => 'Desert Safari', 'slug' => 'desert-safari']);
        $tour = Tour::create([
            'name' => 'Seeded Regression Safari',
            'slug' => 'seeded-regression-safari',
            'category_id' => $cat->id,
            'duration' => '6 Hours',
            'status' => 'active',
        ]);
        $blogCat = BlogCategory::first() ?: BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = BlogPost::create([
            'title' => 'Seeded Blog Post',
            'slug' => 'seeded-blog-post',
            'category_id' => $blogCat->id,
            'content' => 'Detailed safari article body.',
            'status' => 'published',
        ]);
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            ['name' => 'Home', 'title' => 'Dunes Discovery Tourism', 'status' => 'published']
        );
        $pillar = WikiPillar::create([
            'slug' => 'dubai-safari-guide',
            'name' => 'Dubai Safari Guide',
            'status' => 'active',
        ]);
        $article = WikiArticle::create([
            'pillar_id' => $pillar->id,
            'slug' => 'evening-dune-bashing-tips',
            'title' => 'Evening Dune Bashing Tips',
            'excerpt' => 'Essential preparation and safety tips for your evening desert safari.',
            'content' => 'Comprehensive dunes safety and itinerary guide.',
            'status' => 'published',
        ]);

        $routesToRender = [
            route('admin.governance.index'),
            route('admin.governance.users.index'),
            route('admin.governance.logs.export'),
            route('admin.health.index'),
            route('admin.ai-visibility.index'),
            route('admin.audit.index'),
            route('admin.audit.show', $auditRun->id),
            route('admin.settings.privacy.index'),
            route('admin.settings.robots.index'),
            route('admin.settings.seo-templates.index'),
            route('admin.settings.keywords.index'),
            route('admin.settings.redirects.index'),
            route('admin.settings.seo'),
            route('admin.tours.edit', $tour->id),
            route('admin.blogs.edit', $post->id),
            route('admin.pages.edit', $homePage->id),
            route('admin.wiki.articles.edit', $article->id),
        ];

        foreach ($routesToRender as $url) {
            $res = $this->actingAs($admin)->withoutExceptionHandling()->get($url);
            $res->assertStatus(200);
        }

        // Verify Home page editorial drawer uses '/' rather than broken '/home'
        $homePageRes = $this->actingAs($admin)->get(route('admin.pages.edit', $homePage->id));
        $homePageRes->assertDontSee(url('/home'), false);
    }

    public function test_null_actor_and_non_model_view_variables_do_not_throw_php84_type_errors_and_tracking_toggle_persists(): void
    {
        // PHP 8.4 fatal TypeError regression: method_exists(null, ...)
        $log = app(ConfigAuditService::class)->log(
            section: 'automation',
            action: 'ran',
            settingKey: 'scheduled_job',
            actor: null,
        );
        $this->assertSame('system', $log->user_role);
        $this->assertFalse(Gate::forUser(null)->allows('seo.manage'));

        // Non-Model 'page' variable in Blade view data must not crash SeoResolverService
        $resolved = app(SeoResolverService::class)->applyToLayoutVariables([
            'page' => 2,
        ]);
        $this->assertIsArray($resolved);

        // Concurrency-safe repeat call to SeoTemplate::ensureDefaultsExist()
        SeoTemplate::ensureDefaultsExist();
        SeoTemplate::ensureDefaultsExist();
        $this->assertGreaterThanOrEqual(6, SeoTemplate::count());

        // Admin SEO Settings persistence for seo_index_tracking_params
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'seo_form_submitted' => '1',
                'seo_index_tracking_params' => '1',
            ])
            ->assertRedirect();

        $seoView = $this->actingAs($admin)->get(route('admin.settings.seo'));
        $seoView->assertStatus(200);
        $seoView->assertSee('name="seo_index_tracking_params" id="seo_index_tracking_params" value="1" checked', false);
    }

    public function test_health_dashboard_survives_malformed_heartbeat_and_oversized_log_tail_safely(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        \Illuminate\Support\Facades\Cache::put('scheduler_last_heartbeat', 'not-a-valid-date-string', 60);

        $logPath = storage_path('logs/laravel.log');
        $hadOriginal = file_exists($logPath);
        $originalBackup = $hadOriginal ? file_get_contents($logPath) : null;

        try {
            // Write > 100 KB log payload with multi-byte UTF-8 error line beyond the 64 KB window
            $paddingLine = "[2026-11-24 00:00:00] production.INFO: normal heartbeat log padding 0123456789\n";
            $bigLog = str_repeat($paddingLine, 1400)
                . "[2026-11-24 12:00:00] production.ERROR: Simulated UTF-8 desert tour error صحراء\n";
            file_put_contents($logPath, $bigLog);

            $res = $this->actingAs($admin)->withoutExceptionHandling()->get(route('admin.health.index'));
            $res->assertStatus(200);
            $res->assertSee('Simulated UTF-8 desert tour error');
        } finally {
            if ($hadOriginal) {
                file_put_contents($logPath, $originalBackup);
            } elseif (file_exists($logPath)) {
                @unlink($logPath);
            }
            \Illuminate\Support\Facades\Cache::forget('scheduler_last_heartbeat');
        }
    }
}
