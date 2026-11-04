<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_journey_endpoint_returns_json_successfully(): void
    {
        $admin = User::first() ?: User::factory()->create();

        $response = $this->actingAs($admin)->getJson('/admin/api/visitor-journey?session_id=test_session_123&client_ip=127.0.0.1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'visitor',
            'current_screen',
            'friction_alerts',
            'journey_steps',
        ]);
    }
}
