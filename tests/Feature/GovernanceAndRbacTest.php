<?php

namespace Tests\Feature;

use App\Models\ChangeApproval;
use App\Models\ConfigAuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceAndRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_blocked_from_mutations_and_security_routes(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'is_active' => true]);

        $this->actingAs($viewer)->get(route('admin.audit.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('admin.governance.users.index'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('admin.settings.update'), ['seo_home_title' => 'Hacked'])->assertStatus(403);
        $this->actingAs($viewer)->post(route('admin.audit.run'))->assertStatus(403);
    }

    public function test_editor_cannot_edit_global_security_or_rbac_but_can_access_governance_view_if_permitted(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)->get(route('admin.security.ip-rules'))->assertStatus(403);
        $this->actingAs($editor)->get(route('admin.governance.users.index'))->assertStatus(403);
        $this->actingAs($editor)->post(route('admin.settings.update'), ['seo_default_title' => 'Blocked'])->assertStatus(403);
    }

    public function test_deactivated_user_is_logged_out_and_blocked(): void
    {
        $staff = User::factory()->create(['role' => 'seo_manager', 'is_active' => false]);

        $response = $this->actingAs($staff)->get(route('admin.audit.index'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_config_audit_service_redacts_secrets_and_logs_diffs(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        app(ConfigAuditService::class)->log(
            section: 'security',
            action: 'updated',
            settingKey: 'ziina_access_token',
            oldValue: 'old-secret-1234',
            newValue: 'new-secret-5678',
            actor: $admin,
        );

        $log = ConfigAuditLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('[REDACTED]', $log->old_value);
        $this->assertSame('[REDACTED]', $log->new_value);
    }

    public function test_seo_manager_global_seo_update_routes_through_approval_workflow_until_super_admin_approves(): void
    {
        $seoManager = User::factory()->create(['role' => 'seo_manager', 'is_active' => true]);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        Setting::setVal('seo_default_title', 'Original Title', 'seo');

        $response = $this->actingAs($seoManager)->post(route('admin.settings.update'), [
            'seo_default_title' => 'Proposed New Global Title',
        ]);
        $response->assertRedirect();

        // Live setting must remain unchanged while awaiting peer approval
        $this->assertSame('Original Title', Setting::getVal('seo_default_title'));

        $approval = ChangeApproval::where('status', 'pending_review')->latest()->first();
        $this->assertNotNull($approval);

        // SEO manager cannot self-approve
        $this->actingAs($seoManager)->post(route('admin.governance.approvals.approve', $approval->id))->assertStatus(403);

        // Super admin approves and publishes
        $this->actingAs($superAdmin)->post(route('admin.governance.approvals.approve', $approval->id))->assertRedirect();
        $this->assertSame('Proposed New Global Title', Setting::getVal('seo_default_title'));
    }

    public function test_super_admin_can_render_governance_hub_and_manage_staff_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $this->actingAs($superAdmin)->get(route('admin.governance.index'))->assertStatus(200);
        $this->actingAs($superAdmin)->get(route('admin.governance.users.index'))->assertStatus(200);

        $this->actingAs($superAdmin)->post(route('admin.governance.users.store'), [
            'name' => 'Lead Editor',
            'email' => 'editor@dunesdiscoverytourism.com',
            'password' => 'StrongPass#2026',
            'role' => 'editor',
        ])->assertRedirect();

        $created = User::where('email', 'editor@dunesdiscoverytourism.com')->first();
        $this->assertNotNull($created);
        $this->assertSame('editor', $created->role);
        $this->assertTrue($created->hasPermission('content.edit'));
        $this->assertFalse($created->hasPermission('security.manage'));
    }
}
