<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\IpRule;
use App\Models\RequestLog;
use App\Models\Tour;
use App\Models\UnblockRequest;
use App\Models\User;
use App\Services\IpSecurityService;
use App\Services\VisitorTrackerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IpSecurityAndFirewallTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->adminUser = User::first() ?: User::factory()->create();
    }

    /**
     * Test 1: Blocked IP receives 403 Forbidden with branded security notice.
     */
    public function test_blocked_ip_is_denied_access_and_receives_branded_403_page(): void
    {
        $blockedIp = '185.220.101.5';

        IpRule::create([
            'ip_address' => $blockedIp,
            'mode' => 'block',
            'label' => 'Malicious Test IP',
            'status' => 'active',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => $blockedIp])
            ->get('/');

        $response->assertStatus(403);
        $response->assertSee('Access Temporarily Restricted');
        $response->assertSee('Security & Access Governance', false);
        $response->assertSee($blockedIp);

        $rule = IpRule::where('ip_address', $blockedIp)->first();
        $this->assertGreaterThanOrEqual(1, $rule->blocked_attempts_count);
    }

    /**
     * Test 2: Blocked IP can access unblock appeal page and submit an appeal.
     */
    public function test_blocked_ip_can_access_unblock_appeal_page_and_submit_appeal(): void
    {
        $blockedIp = '185.220.101.6';

        IpRule::create([
            'ip_address' => $blockedIp,
            'mode' => 'block',
            'label' => 'Appeal Test IP',
            'status' => 'active',
        ]);

        // Access unblock page directly (renders branded 403 page)
        $viewResponse = $this->withServerVariables(['REMOTE_ADDR' => $blockedIp])
            ->get('/security/blocked');

        $viewResponse->assertStatus(403);
        $viewResponse->assertSee('Request Immediate Access Review');

        // Submit unblock appeal form
        $postResponse = $this->withServerVariables(['REMOTE_ADDR' => $blockedIp])
            ->post('/security/unblock-request', [
                'ip_address' => $blockedIp,
                'name' => 'John Tourist',
                'email' => 'john.tourist@example.com',
                'phone' => '+971501234567',
                'reason' => 'I am trying to book the Evening Desert Safari from my hotel Wi-Fi.',
            ]);

        $postResponse->assertSessionHas('appeal_success');

        $this->assertDatabaseHas('unblock_requests', [
            'ip_address' => $blockedIp,
            'name' => 'John Tourist',
            'email' => 'john.tourist@example.com',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 3: Ignored IP is identified and skipped from analytics recording.
     */
    public function test_ignored_ip_is_skipped_by_security_service_and_tracker(): void
    {
        $ignoredIp = '185.220.101.88';
        $regularIp = '185.220.101.99';

        IpRule::create([
            'ip_address' => $ignoredIp,
            'mode' => 'ignore_analytics',
            'label' => 'Office Testing IP',
            'status' => 'active',
        ]);

        $securityService = app(IpSecurityService::class);
        $trackerService = app(VisitorTrackerService::class);

        // Security service recognition
        $this->assertTrue($securityService->isIpIgnoredFromAnalytics($ignoredIp));
        $this->assertFalse($securityService->isIpIgnoredFromAnalytics($regularIp));

        // Tracker service skipping
        $this->assertTrue($trackerService->shouldSkip(['client_ip' => $ignoredIp]));
        $this->assertFalse($trackerService->shouldSkip(['client_ip' => $regularIp, 'bot_indicator' => 'Likely Human', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0']));
    }

    /**
     * Test 4: Ignored IPs are excluded from Admin Analytics metrics.
     */
    public function test_ignored_ips_are_excluded_from_admin_analytics_metrics(): void
    {
        $ignoredIp = '192.0.2.50';
        $regularIp = '192.0.2.60';

        // Create log records
        RequestLog::create([
            'session_id' => 'sess_ignored_1',
            'client_ip' => $ignoredIp,
            'request_uri' => '/safari-tours',
            'request_method' => 'GET',
            'request_timestamp' => now(),
            'bot_indicator' => 'Likely Human',
        ]);

        RequestLog::create([
            'session_id' => 'sess_regular_1',
            'client_ip' => $regularIp,
            'request_uri' => '/safari-tours',
            'request_method' => 'GET',
            'request_timestamp' => now(),
            'bot_indicator' => 'Likely Human',
        ]);

        // Mark $ignoredIp as ignored
        IpRule::create([
            'ip_address' => $ignoredIp,
            'mode' => 'ignore_analytics',
            'label' => 'QA Team',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/analytics');
        $response->assertStatus(200);

        // Verify view data
        $response->assertViewHas('ignoredCount', 1);
        $response->assertViewHas('uniqueIPs', 1); // Only the regular IP is counted!
    }

    /**
     * Test 5: Ignored IPs are excluded from Abandoned Draft Checkouts.
     */
    public function test_ignored_ips_are_excluded_from_abandoned_draft_checkouts(): void
    {
        $ignoredIp = '192.0.2.70';
        $regularIp = '192.0.2.80';

        $tour = Tour::create([
            'name' => 'Morning Dune Buggy Tour',
            'slug' => 'morning-dune-buggy-tour-unique',
            'status' => 'active',
            'base_price' => 400.00,
        ]);

        // Draft for ignored IP
        Booking::create([
            'reference' => 'DT-IGN-001',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'name' => 'Tester Nabeel',
            'email' => 'test@example.com',
            'phone' => '+971500000000',
            'status' => 'draft',
            'ip_address' => $ignoredIp,
            'date' => now()->addDays(2),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 800.00,
            'total' => 800.00,
            'source' => 'direct',
        ]);

        // Draft for customer
        Booking::create([
            'reference' => 'DT-GUEST-002',
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'name' => 'Real Guest',
            'email' => 'guest@example.com',
            'phone' => '+971555555555',
            'status' => 'draft',
            'ip_address' => $regularIp,
            'date' => now()->addDays(2),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'subtotal' => 800.00,
            'total' => 800.00,
            'source' => 'direct',
        ]);

        // Mark rule
        IpRule::create([
            'ip_address' => $ignoredIp,
            'mode' => 'ignore_analytics',
            'label' => 'Founder Testing',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/bookings/drafts');
        $response->assertStatus(200);

        // Real guest should appear, ignored IP draft should be filtered out
        $response->assertSee('Real Guest');
        $response->assertDontSee('Tester Nabeel');
    }

    /**
     * Test 6: Admin can store rule, quick-ignore, toggle mode/status, purge logs, and approve appeals.
     */
    public function test_admin_can_manage_rules_toggle_mode_and_purge_logs(): void
    {
        // 1. Admin accesses IP Rules management view
        $indexResponse = $this->actingAs($this->adminUser)->get('/admin/security/ip-rules');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('IP Firewall & Analytics Filters', false);

        // 2. Admin stores a new rule
        $storeResponse = $this->actingAs($this->adminUser)->post('/admin/security/ip-rules', [
            'ip_address' => '185.220.101.40',
            'mode' => 'ignore_analytics',
            'status' => 'active',
            'label' => 'Office Network',
            'reason' => 'Testing from Dubai office',
        ]);
        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('ip_rules', [
            'ip_address' => '185.220.101.40',
            'mode' => 'ignore_analytics',
        ]);

        $rule = IpRule::where('ip_address', '185.220.101.40')->first();

        // 3. Admin toggles mode (switches from ignore_analytics to block)
        $toggleModeResponse = $this->actingAs($this->adminUser)->post("/admin/security/ip-rules/{$rule->id}/toggle-mode");
        $toggleModeResponse->assertRedirect();
        $this->assertEquals('block', $rule->fresh()->mode);

        // 4. Admin toggles status (inactivates)
        $toggleStatusResponse = $this->actingAs($this->adminUser)->post("/admin/security/ip-rules/{$rule->id}/toggle-status");
        $toggleStatusResponse->assertRedirect();
        $this->assertEquals('inactive', $rule->fresh()->status);

        // 5. Admin purges logs for an IP
        RequestLog::create([
            'session_id' => 'sess_purge',
            'client_ip' => '185.220.101.40',
            'request_uri' => '/test-uri',
            'request_method' => 'GET',
            'request_timestamp' => now(),
        ]);
        $this->assertEquals(1, RequestLog::where('client_ip', '185.220.101.40')->count());

        $purgeResponse = $this->actingAs($this->adminUser)->post('/admin/security/ip-rules/purge-logs', [
            'ip_address' => '185.220.101.40',
        ]);
        $purgeResponse->assertRedirect();
        $this->assertEquals(0, RequestLog::where('client_ip', '185.220.101.40')->count());

        // 6. Admin handles unblock appeal
        $appeal = UnblockRequest::create([
            'client_ip' => '185.220.101.40',
            'ip_address' => '185.220.101.40',
            'name' => 'Tourist Appeal',
            'email' => 'tourist@example.com',
            'reason' => 'Hotel Wi-Fi issue',
            'status' => 'pending',
        ]);

        $approveResponse = $this->actingAs($this->adminUser)->post("/admin/security/unblock-requests/{$appeal->id}/approve");
        $approveResponse->assertRedirect();
        $this->assertEquals('approved_unblocked', $appeal->fresh()->status);
        $this->assertEquals('ignore_analytics', $rule->fresh()->mode);

        // 7. Admin deletes rule
        $deleteResponse = $this->actingAs($this->adminUser)->delete("/admin/security/ip-rules/{$rule->id}");
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('ip_rules', ['id' => $rule->id]);
    }
}
