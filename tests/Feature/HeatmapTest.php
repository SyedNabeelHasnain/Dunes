<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorHeatmapClick;
use App\Models\VisitorHeatmapScroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeatmapTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?: User::factory()->create();
    }

    public function test_heatmap_click_is_ingested_and_stored(): void
    {
        $response = $this->postJson('/api/telemetry/heatmaps/click', [
            'session_id' => 'click-test-123',
            'page_path' => '/tours/evening-desert-safari',
            'x_ratio' => 0.4523,
            'y_ratio' => 0.2815,
            'click_x' => 540,
            'click_y' => 320,
            'viewport_width' => 1200,
            'viewport_height' => 800,
            'target_tag' => 'BUTTON',
            'target_text' => 'Book Safari Now',
        ], [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36',
            'REMOTE_ADDR' => '198.51.100.61',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('visitor_heatmap_clicks', [
            'session_id' => 'click-test-123',
            'page_path' => '/tours/evening-desert-safari',
            'target_tag' => 'BUTTON',
            'target_text' => 'Book Safari Now',
        ]);
    }

    public function test_heatmap_scroll_is_ingested_and_stored(): void
    {
        $response = $this->postJson('/api/telemetry/heatmaps/scroll', [
            'session_id' => 'scroll-test-123',
            'page_path' => '/about-us',
            'max_scroll_percent' => 85,
            'viewport_height' => 900,
            'page_height' => 3200,
        ], [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36',
            'REMOTE_ADDR' => '198.51.100.62',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('visitor_heatmap_scrolls', [
            'session_id' => 'scroll-test-123',
            'page_path' => '/about-us',
            'max_scroll_percent' => 85,
        ]);
    }

    public function test_admin_can_retrieve_aggregated_heatmap_data(): void
    {
        // Seed clicks
        VisitorHeatmapClick::create([
            'session_id' => 's1',
            'page_path' => '/tours',
            'device_type' => 'desktop',
            'x_ratio' => 0.5,
            'y_ratio' => 0.3,
            'click_x' => 600,
            'click_y' => 300,
            'viewport_width' => 1200,
            'viewport_height' => 800,
            'target_tag' => 'A',
            'target_text' => 'Desert Safari',
            'created_at' => now(),
        ]);

        VisitorHeatmapClick::create([
            'session_id' => 's2',
            'page_path' => '/tours',
            'device_type' => 'desktop',
            'x_ratio' => 0.52,
            'y_ratio' => 0.31,
            'click_x' => 620,
            'click_y' => 310,
            'viewport_width' => 1200,
            'viewport_height' => 800,
            'target_tag' => 'A',
            'target_text' => 'Desert Safari',
            'created_at' => now(),
        ]);

        // Seed scrolls
        VisitorHeatmapScroll::create([
            'session_id' => 's1',
            'page_path' => '/tours',
            'device_type' => 'desktop',
            'max_scroll_percent' => 75,
            'viewport_height' => 800,
            'page_height' => 2000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/analytics/heatmaps/data?page=/tours');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'page_path' => '/tours',
                'total_clicks' => 2,
                'scroll' => [
                    'avg_scroll_percent' => 75,
                ],
            ],
        ]);
    }

    public function test_admin_can_access_heatmaps_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/analytics/heatmaps');
        $response->assertStatus(200);
        $response->assertSee('Heatmap & Scroll Analytics');
    }
}
