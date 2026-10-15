<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CronHealthTest extends TestCase
{
    public function test_cron_status_returns_pending_when_no_heartbeat(): void
    {
        Cache::forget('scheduler_last_heartbeat');

        $response = $this->getJson('/api/v1/cron/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'pending',
                'cron_active' => false,
                'last_heartbeat' => null,
            ]);
    }

    public function test_cron_status_returns_active_when_heartbeat_is_recent(): void
    {
        Cache::put('scheduler_last_heartbeat', now()->toIso8601String(), 86400);

        $response = $this->getJson('/api/v1/cron/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'active',
                'cron_active' => true,
            ]);
    }

    public function test_web_route_redirects_to_api_cron_status(): void
    {
        $response = $this->get('/cron-status');
        $response->assertRedirect('/api/v1/cron/status');
    }

    public function test_cron_run_rejects_invalid_token(): void
    {
        $response = $this->getJson('/api/v1/cron/run?token=invalid_token');
        $response->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }

    public function test_cron_run_succeeds_with_valid_token(): void
    {
        Cache::forget('scheduler_last_heartbeat');

        $response = $this->getJson('/api/v1/cron/run?token=dunes_cron_2026');
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'cron_active' => true,
            ]);

        $this->assertNotNull(Cache::get('scheduler_last_heartbeat'));
    }
}
