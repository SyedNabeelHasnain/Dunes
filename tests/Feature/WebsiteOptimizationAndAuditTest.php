<?php

namespace Tests\Feature;

use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\RedirectRule;
use App\Models\Tour;
use App\Models\User;
use App\Services\Audit\WebsiteAuditService;
use App\Services\RedirectManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteOptimizationAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?: User::factory()->create([
            'email' => 'admin@dunesdiscovery.com',
        ]);
    }

    /**
     * Test WebsiteAuditService runs full audit, calculates traceable category scores and records issues.
     */
    public function test_audit_service_executes_multi_discipline_scan(): void
    {
        // Seed a tour with a short title to trigger a test issue
        Tour::create([
            'slug' => 'short-title-tour',
            'name' => 'Safari',
            'meta_title' => 'Safari',
            'meta_desc' => 'Short desert safari tour in red dunes of Dubai.',
            'status' => 'active',
            'short_desc' => 'Safari',
            'full_desc' => 'Detailed itinerary of safari tour with dune bashing and camp dinner.',
        ]);

        $auditService = app(WebsiteAuditService::class);
        $run = $auditService->runAudit('full', $this->admin->id);

        $this->assertInstanceOf(AuditRun::class, $run);
        $this->assertEquals('completed', $run->status);
        $this->assertGreaterThan(0, $run->health_score);
        $this->assertLessThanOrEqual(100, $run->health_score);
        $this->assertGreaterThan(0, $run->duration_ms);
        $this->assertIsArray($run->category_scores);

        // Verify all 9 disciplines are tracked in category scores
        $expectedCategories = [
            'technical_seo',
            'seo',
            'performance',
            'security',
            'accessibility',
            'ai_readiness',
            'structured_data',
            'content_quality',
            'privacy',
        ];

        foreach ($expectedCategories as $cat) {
            $this->assertArrayHasKey($cat, $run->category_scores);
            $this->assertArrayHasKey('score', $run->category_scores[$cat]);
            $this->assertArrayHasKey('counts', $run->category_scores[$cat]);
        }

        // Verify issues are recorded in the database
        $this->assertDatabaseHas('audit_runs', ['id' => $run->id, 'status' => 'completed']);
        $this->assertGreaterThan(0, AuditIssue::where('audit_run_id', $run->id)->count());
    }

    /**
     * Test Admin Audit Dashboard renders successfully for authenticated administrators.
     */
    public function test_admin_audit_dashboard_renders_with_scores_and_filters(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit.index'));

        $response->assertStatus(200);
        $response->assertSee('Website Optimization & Quality Audit Engine', false);
        $response->assertSee('Overall Health');
        $response->assertSee('Run Full Audit Now');
    }

    /**
     * Test triggering a manual audit via Admin POST route.
     */
    public function test_admin_can_trigger_full_audit_run(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.audit.run'), [
            'scan_type' => 'full',
        ]);

        $response->assertRedirect(route('admin.audit.index'));
        $response->assertSessionHas('success');

        $latestRun = AuditRun::latest('id')->first();
        $this->assertNotNull($latestRun);
        $this->assertEquals('completed', $latestRun->status);
    }

    /**
     * Test updating issue remediation status, assignee, and notes.
     */
    public function test_admin_can_update_issue_remediation_status(): void
    {
        $run = AuditRun::create([
            'scan_type' => 'full',
            'status' => 'completed',
            'health_score' => 90,
        ]);

        $issue = AuditIssue::create([
            'audit_run_id' => $run->id,
            'category' => 'seo',
            'check_key' => 'seo_test_issue',
            'title' => 'Test SEO Title Issue',
            'severity' => 'medium',
            'status' => 'open',
            'affected_url' => url('/test'),
        ]);

        $response = $this->actingAs($this->admin)->putJson(route('admin.audit.issues.update', $issue->id), [
            'status' => 'resolved',
            'assigned_to' => $this->admin->id,
            'remediation_notes' => 'Fixed in CMS page settings.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $issue->refresh();
        $this->assertEquals('resolved', $issue->status);
        $this->assertEquals($this->admin->id, $issue->assigned_to);
        $this->assertNotNull($issue->resolved_at);
        $this->assertEquals('Fixed in CMS page settings.', $issue->remediation_notes);
    }

    /**
     * Test CSV export of audit run findings.
     */
    public function test_admin_can_export_audit_report_to_csv(): void
    {
        $run = AuditRun::create([
            'scan_type' => 'full',
            'status' => 'completed',
            'health_score' => 88,
        ]);

        AuditIssue::create([
            'audit_run_id' => $run->id,
            'category' => 'performance',
            'check_key' => 'perf_test_check',
            'title' => 'Sample Image Compression Issue',
            'severity' => 'low',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit.export', ['id' => $run->id, 'format' => 'csv']));

        $response->assertStatus(200);
        $this->assertTrue(str_contains((string) $response->headers->get('content-disposition'), 'dunes-audit-run-'));
    }

    /**
     * Test RedirectManagerService detects loops and chains.
     */
    public function test_redirect_manager_detects_self_referencing_and_multi_hop_loops(): void
    {
        $service = app(RedirectManagerService::class);

        // Self-referencing loop
        $selfLoop = $service->detectLoopOrChain('/desert-safari', '/desert-safari');
        $this->assertTrue($selfLoop['is_loop']);
        $this->assertFalse($selfLoop['is_chain']);

        // Save rule A -> B
        $service->saveRule([
            'source_path' => '/tour-old-a',
            'target_path' => '/tour-old-b',
            'status_code' => 301,
        ]);

        // Detect loop when creating B -> A
        $loop = $service->detectLoopOrChain('/tour-old-b', '/tour-old-a');
        $this->assertTrue($loop['is_loop']);

        // Detect chain when creating C -> A (Chain: C -> A -> B)
        $chain = $service->detectLoopOrChain('/tour-old-c', '/tour-old-a');
        $this->assertFalse($chain['is_loop']);
        $this->assertTrue($chain['is_chain']);
        $this->assertEquals('/tour-old-b', $chain['final_target']);
    }

    /**
     * Test dynamic redirect execution and hit counting via middleware.
     */
    public function test_dynamic_redirect_executes_and_increments_hits(): void
    {
        $service = app(RedirectManagerService::class);
        $rule = $service->saveRule([
            'source_path' => '/legacy-dune-buggy-link',
            'target_path' => '/dune-buggy-rental-dubai',
            'status_code' => 301,
            'notes' => 'Old brochure link',
        ]);

        $response = $this->get('/legacy-dune-buggy-link');

        $response->assertRedirect('/dune-buggy-rental-dubai');
        $response->assertStatus(301);

        $rule->refresh();
        $this->assertEquals(1, $rule->hit_count);
        $this->assertNotNull($rule->last_hit_at);
    }

    /**
     * Test Admin Redirects Index and test simulation AJAX endpoint.
     */
    public function test_admin_redirect_simulator_and_management(): void
    {
        $service = app(RedirectManagerService::class);
        $service->saveRule([
            'source_path' => '/legacy-evening-safari',
            'target_path' => '/evening-desert-safari-dubai',
            'status_code' => 301,
        ]);

        // Test simulator AJAX
        $testResponse = $this->actingAs($this->admin)->getJson(route('admin.api.redirects.test', [
            'path' => '/legacy-evening-safari',
        ]));

        $testResponse->assertStatus(200);
        $testResponse->assertJson([
            'success' => true,
            'matched' => true,
            'status_code' => 301,
            'target' => '/evening-desert-safari-dubai',
        ]);

        // Test creating a new redirect via admin post
        $postResponse = $this->actingAs($this->admin)->post(route('admin.settings.redirects.store'), [
            'source_path' => '/old-quad-bike',
            'target_path' => '/desert-safari-quad-biking-dubai',
            'status_code' => 301,
            'notes' => 'Preserves organic backlink',
        ]);

        $postResponse->assertRedirect(route('admin.settings.redirects.index'));
        $this->assertDatabaseHas('redirect_rules', [
            'source_path' => '/old-quad-bike',
            'target_path' => '/desert-safari-quad-biking-dubai',
            'status_code' => 301,
        ]);
    }

    public function test_upgraded_eleven_category_single_url_audit_comparison_and_artisan_command(): void
    {
        $service = app(WebsiteAuditService::class);
        $first = $service->runAudit('incremental', $this->admin->id);
        $second = $service->runAudit('full', $this->admin->id);

        $this->assertArrayHasKey('ux', $second->category_scores);
        $this->assertArrayHasKey('infrastructure', $second->category_scores);
        $this->assertSame($first->id, $second->parent_run_id);

        $diff = $service->compareRuns($second, $first);
        $this->assertNotNull($diff);
        $this->assertArrayHasKey('score_delta', $diff);

        $pageRes = $this->actingAs($this->admin)->postJson(route('admin.audit.page'), ['url' => '/']);
        $pageRes->assertStatus(200)->assertJsonStructure(['url', 'http_status', 'health_score', 'issues']);

        $this->artisan('audit:run --type=incremental')->assertSuccessful();
        $this->actingAs($this->admin)->get(route('admin.audit.show', $second->id))->assertStatus(200);
    }
}
