<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\IpRule;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveVisitorTelemetryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?: User::factory()->create();
    }

    public function test_active_visitors_polling_returns_enriched_telemetry(): void
    {
        $sessionToken = 'live-test-session-123';
        $ip = '198.51.100.44';

        // 1. Create simulated human request logs in last 2 minutes
        RequestLog::create([
            'session_id' => $sessionToken,
            'client_ip' => $ip,
            'request_uri' => '/tours/evening-desert-safari-dubai',
            'request_timestamp' => now()->subMinutes(1),
            'bot_indicator' => 'Likely Human',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'device_type' => 'Mobile',
            'browser_name' => 'Safari',
            'os_name' => 'iOS',
            'entity_type' => 'page_view',
        ]);

        // 2. Create associated draft checkout
        Booking::create([
            'reference' => 'DFT261103LIVE1',
            'status' => 'draft',
            'draft_token' => $sessionToken,
            'ip_address' => $ip,
            'name' => 'John Live Traveler',
            'email' => 'john.live@example.com',
            'phone' => '+971501112233',
            'tour_name' => 'Evening Desert Safari Dubai',
            'tour_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'subtotal' => 650.00,
            'total' => 650.00,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.active-visitors'));

        $response->assertOk()
            ->assertJsonStructure([
                'count',
                'visitors' => [
                    '*' => [
                        'session_id', 'client_ip', 'country', 'city', 'request_uri',
                        'pages_count', 'duration_seconds', 'has_draft', 'draft_ref',
                    ],
                ],
            ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, $data['count']);
        $visitor = collect($data['visitors'])->firstWhere('client_ip', $ip);
        $this->assertNotNull($visitor);
        $this->assertTrue($visitor['has_draft']);
        $this->assertEquals('DFT261103LIVE1', $visitor['draft_ref']);
    }

    public function test_visitor_journey_inspector_api_returns_clickstream_and_friction_alerts(): void
    {
        $sessionToken = 'clickstream-journey-456';
        $ip = '198.51.100.55';

        // Step 1: Visited Home
        RequestLog::create([
            'session_id' => $sessionToken,
            'client_ip' => $ip,
            'request_uri' => '/',
            'request_timestamp' => now()->subMinutes(4),
            'bot_indicator' => 'Likely Human',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'device_type' => 'Desktop',
            'browser_name' => 'Chrome',
            'os_name' => 'Windows',
            'entity_type' => 'page_view',
        ]);

        // Step 2: Visited Tour Detail
        RequestLog::create([
            'session_id' => $sessionToken,
            'client_ip' => $ip,
            'request_uri' => '/tours/vip-hummer-desert-safari',
            'request_timestamp' => now()->subMinutes(2),
            'bot_indicator' => 'Likely Human',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'device_type' => 'Desktop',
            'browser_name' => 'Chrome',
            'os_name' => 'Windows',
            'entity_type' => 'page_view',
        ]);

        // Step 3: Hit 404 broken page (Friction point)
        RequestLog::create([
            'session_id' => $sessionToken,
            'client_ip' => $ip,
            'request_uri' => '/tours/old-broken-slug',
            'request_timestamp' => now()->subMinutes(1),
            'bot_indicator' => 'Likely Human',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'device_type' => 'Desktop',
            'browser_name' => 'Chrome',
            'os_name' => 'Windows',
            'entity_type' => 'error_404',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.api.visitor-journey', [
            'sessionId' => $sessionToken,
            'client_ip' => $ip,
        ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'visitor' => [
                    'client_ip' => $ip,
                    'total_steps' => 3,
                ],
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['friction_alerts']);
        $has404Alert = collect($data['friction_alerts'])->contains('type', '404_not_found');
        $this->assertTrue($has404Alert);

        $this->assertCount(3, $data['journey_steps']);
        $this->assertArrayHasKey('current_screen', $data);
        $this->assertNotEmpty($data['current_screen']['title']);
    }

    public function test_visitor_journey_supports_query_parameters_and_synthesizes_fresh_profile(): void
    {
        $freshIp = '2001:4860:7:1532::fa';

        // Call journey via query parameters without any prior request logs
        $response = $this->actingAs($this->admin)->getJson('/admin/api/visitor-journey?client_ip=' . urlencode($freshIp));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'visitor' => [
                    'client_ip' => $freshIp,
                    'is_active_now' => true,
                ],
            ]);

        $data = $response->json();
        $this->assertArrayHasKey('current_screen', $data);
        $this->assertEquals('/', $data['current_screen']['uri']);
        $this->assertNotEmpty($data['journey_steps']);
    }

    public function test_quick_ignore_action_adds_rule_and_purges_telemetry(): void
    {
        $targetIp = '198.51.100.99';

        RequestLog::create([
            'client_ip' => $targetIp,
            'request_uri' => '/test-page',
            'request_timestamp' => now(),
            'bot_indicator' => 'Likely Human',
        ]);

        $this->assertDatabaseHas('request_logs', ['client_ip' => $targetIp]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.api.visitors.quick-ignore'), [
            'ip' => $targetIp,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('ip_rules', [
            'ip_address' => $targetIp,
            'mode' => 'ignore_analytics',
        ]);

        // Request logs for ignored testing IP should be purged
        $this->assertDatabaseMissing('request_logs', ['client_ip' => $targetIp]);
    }

    public function test_quick_block_action_adds_firewall_rule(): void
    {
        $abusiveIp = '203.0.113.88';

        $response = $this->actingAs($this->admin)->postJson(route('admin.api.visitors.quick-block'), [
            'ip' => $abusiveIp,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('ip_rules', [
            'ip_address' => $abusiveIp,
            'mode' => 'block',
        ]);
    }

    public function test_live_visitors_hub_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.live-visitors'));

        $response->assertOk()
            ->assertViewIs('admin.analytics.live-visitors')
            ->assertSee('Live Visitors &amp; Telemetry Hub', false)
            ->assertSee('Live Streaming');
    }
}
