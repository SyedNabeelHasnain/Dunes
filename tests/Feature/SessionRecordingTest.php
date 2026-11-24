<?php

namespace Tests\Feature;

use App\Models\IpRule;
use App\Models\User;
use App\Models\VisitorSessionRecording;
use App\Services\IpSecurityService;
use App\Services\SessionRecordingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SessionRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SessionRecordingService $recordingService;
    protected IpSecurityService $ipSecurity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?: User::factory()->create();
        $this->recordingService = app(SessionRecordingService::class);
        $this->ipSecurity = app(IpSecurityService::class);
    }

    public function test_human_session_recording_batch_is_ingested_and_compressed_to_disk(): void
    {
        $sessionId = 'test-session-human-' . uniqid();
        $events = [
            ['type' => 0, 'data' => ['href' => 'http://localhost/'], 'timestamp' => 1730600000000],
            ['type' => 2, 'data' => ['node' => ['tag' => 'div']], 'timestamp' => 1730600010000],
            ['type' => 3, 'data' => ['source' => 1, 'positions' => [['x' => 100, 'y' => 200]]], 'timestamp' => 1730600030000],
        ];

        $response = $this->postJson('/api/telemetry/recordings/batch', [
            'session_id' => $sessionId,
            'events' => $events,
            'current_page' => '/tours',
            'is_final' => false,
            'is_webdriver' => false,
        ], [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'REMOTE_ADDR' => '198.51.100.50',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'session_id' => $sessionId,
            'events_count' => 3,
        ]);

        // Assert record created in database
        $this->assertDatabaseHas('visitor_session_recordings', [
            'session_id' => $sessionId,
            'events_count' => 3,
            'landing_page' => '/tours',
        ]);

        $recording = VisitorSessionRecording::where('session_id', $sessionId)->first();
        $this->assertNotNull($recording);
        $this->assertNotEmpty($recording->storage_path);

        // Assert file exists on disk and is gzipped
        $filePath = storage_path('app/' . $recording->storage_path);
        $this->assertTrue(File::exists($filePath));

        // Read and decompress
        $savedEvents = $this->recordingService->getRecordingEvents($recording);
        $this->assertCount(3, $savedEvents);
        $this->assertEquals(1730600000000, $savedEvents[0]['timestamp']);

        // Clean up
        $this->recordingService->deleteRecording($recording);
    }

    public function test_automated_bots_are_strictly_rejected_and_never_recorded(): void
    {
        $sessionId = 'bot-session-' . uniqid();
        $events = [
            ['type' => 0, 'data' => [], 'timestamp' => 1730600000000],
        ];

        // 1. Bot user-agent
        $response = $this->postJson('/api/telemetry/recordings/batch', [
            'session_id' => $sessionId,
            'events' => $events,
            'current_page' => '/',
        ], [
            'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['skipped' => 'bot_excluded']);
        $this->assertDatabaseMissing('visitor_session_recordings', ['session_id' => $sessionId]);

        // 2. Webdriver flag
        $response2 = $this->postJson('/api/telemetry/recordings/batch', [
            'session_id' => 'bot-webdriver-' . uniqid(),
            'events' => $events,
            'is_webdriver' => true,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['skipped' => 'bot_excluded']);
    }

    public function test_ignored_ip_firewall_rules_strictly_prevent_recording(): void
    {
        $ignoredIp = '198.51.100.88';
        IpRule::create([
            'ip_address' => $ignoredIp,
            'mode' => 'ignore_analytics',
            'status' => 'active',
            'label' => 'Internal QA Office',
        ]);
        $this->ipSecurity->clearRulesCache();

        $sessionId = 'ignored-ip-session-' . uniqid();
        $response = $this->postJson('/api/telemetry/recordings/batch', [
            'session_id' => $sessionId,
            'events' => [['type' => 0, 'timestamp' => 1730600000000]],
        ], [
            'REMOTE_ADDR' => $ignoredIp,
            'X-Forwarded-For' => $ignoredIp,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['skipped' => 'ip_excluded']);
        $this->assertDatabaseMissing('visitor_session_recordings', ['session_id' => $sessionId]);
    }

    public function test_admin_can_retrieve_session_events_for_replay(): void
    {
        $sessionId = 'replay-test-' . uniqid();
        $events = [
            ['type' => 0, 'data' => ['node' => 'html'], 'timestamp' => 1730600000000],
            ['type' => 2, 'data' => ['node' => 'body'], 'timestamp' => 1730600010000],
        ];

        $recording = $this->recordingService->ingestBatch($sessionId, $events, [
            'client_ip' => '198.51.100.99',
            'device_type' => 'desktop',
            'browser_name' => 'Chrome',
            'current_page' => '/contact',
        ]);

        $this->assertNotNull($recording);

        // Guest cannot access admin events
        $guestRes = $this->getJson("/admin/analytics/recordings/{$recording->id}/events");
        $guestRes->assertStatus(401);

        // Authenticated admin can access events
        $authRes = $this->actingAs($this->admin)->getJson("/admin/analytics/recordings/{$recording->id}/events");
        $authRes->assertStatus(200);
        $authRes->assertJson([
            'success' => true,
            'events_count' => 2,
            'recording' => [
                'session_id' => $sessionId,
                'device_type' => 'desktop',
            ],
        ]);

        $this->recordingService->deleteRecording($recording);
    }

    public function test_admin_can_access_recordings_index_page(): void
    {
        VisitorSessionRecording::create([
            'session_id' => 'view-test-' . uniqid(),
            'client_ip' => '198.51.100.11',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'device_type' => 'desktop',
            'landing_page' => '/tours',
            'duration_seconds' => 45,
            'events_count' => 12,
            'is_starred' => false,
            'has_inquiry' => false,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/analytics/recordings');
        $response->assertStatus(200);
        $response->assertSee('Session Recordings & Replay');
    }

    public function test_admin_can_toggle_starred_status_on_recording(): void
    {
        $recording = VisitorSessionRecording::create([
            'session_id' => 'star-test-' . uniqid(),
            'client_ip' => '198.51.100.77',
            'is_starred' => false,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->postJson("/admin/analytics/recordings/{$recording->id}/toggle-star");
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_starred' => true]);

        $this->assertTrue($recording->fresh()->is_starred);
    }

    public function test_retention_purge_command_removes_old_recordings_and_preserves_starred(): void
    {
        // 1. Old unstarred recording (20 days old)
        $oldUnstarred = VisitorSessionRecording::create([
            'session_id' => 'old-unstarred-' . uniqid(),
            'client_ip' => '198.51.100.11',
            'is_starred' => false,
        ]);
        \Illuminate\Support\Facades\DB::table('visitor_session_recordings')
            ->where('id', $oldUnstarred->id)
            ->update(['created_at' => now()->subDays(20)->toDateTimeString()]);

        // 2. Old starred recording (20 days old) - MUST be preserved!
        $oldStarred = VisitorSessionRecording::create([
            'session_id' => 'old-starred-' . uniqid(),
            'client_ip' => '198.51.100.12',
            'is_starred' => true,
        ]);
        \Illuminate\Support\Facades\DB::table('visitor_session_recordings')
            ->where('id', $oldStarred->id)
            ->update(['created_at' => now()->subDays(20)->toDateTimeString()]);

        // 3. Fresh recording (2 days old)
        $freshRec = VisitorSessionRecording::create([
            'session_id' => 'fresh-rec-' . uniqid(),
            'client_ip' => '198.51.100.13',
            'is_starred' => false,
        ]);

        // Run purge command with 14-day threshold
        $this->artisan('telemetry:purge-recordings', ['--days' => 14])
            ->assertExitCode(0);

        // Assert old unstarred was pruned
        $this->assertDatabaseMissing('visitor_session_recordings', ['id' => $oldUnstarred->id]);

        // Assert old starred was preserved
        $this->assertDatabaseHas('visitor_session_recordings', ['id' => $oldStarred->id]);

        // Assert fresh recording was preserved
        $this->assertDatabaseHas('visitor_session_recordings', ['id' => $freshRec->id]);
    }

    public function test_gzip_base64_payload_and_out_of_order_batches_sort_meta_before_fullsnapshot(): void
    {
        $sessionId = 'gz-multipage-' . uniqid();

        // Batch B (later incremental scroll) arrives BEFORE Batch A (initial Meta + FullSnapshot sharing identical timestamp)
        $batchLater = [
            ['type' => 3, 'data' => ['source' => 3, 'x' => 0, 'y' => 250], 'timestamp' => 1730600005000],
        ];
        $this->postJson('/api/telemetry/recordings/batch', [
            'session_id' => $sessionId,
            'events' => $batchLater,
            'current_page' => '/',
        ], [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'REMOTE_ADDR' => '198.51.100.60',
        ])->assertStatus(200)->assertJson(['success' => true]);

        // Now send compressed gzip+base64 initial batch with FullSnapshot (2) placed BEFORE Meta (4) at same timestamp
        $batchInitial = [
            ['type' => 2, 'data' => ['node' => ['id' => 1, 'type' => 0]], 'timestamp' => 1730600000000],
            ['type' => 4, 'data' => ['href' => 'https://dunesdiscoverytourism.com/', 'width' => 1280, 'height' => 800], 'timestamp' => 1730600000000],
        ];
        $gzB64 = base64_encode(gzencode(json_encode($batchInitial)));

        // Simulate text/plain beacon payload parsing as well as events_gz_b64 decoding
        $rawRes = $this->call('POST', '/api/telemetry/recordings/batch', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'REMOTE_ADDR' => '198.51.100.60',
            'CONTENT_TYPE' => 'text/plain;charset=UTF-8',
        ], json_encode([
            'session_id' => $sessionId,
            'events_gz_b64' => $gzB64,
            'current_page' => '/tours',
            'is_final' => true,
        ]));
        $rawRes->assertStatus(200)->assertJson(['success' => true, 'session_id' => $sessionId, 'events_count' => 3]);

        $recording = VisitorSessionRecording::where('session_id', $sessionId)->firstOrFail();
        $eventsRes = $this->actingAs($this->admin)->getJson("/admin/analytics/recordings/{$recording->id}/events");
        $eventsRes->assertStatus(200)->assertJson([
            'success' => true,
            'replayable' => true,
            'has_full_snapshot' => true,
            'events_count' => 3,
        ]);

        // Assert Meta (4) strictly precedes FullSnapshot (2), which precedes Incremental (3)
        $returnedTypes = array_column($eventsRes->json('events'), 'type');
        $this->assertSame([4, 2, 3], $returnedTypes);
        $this->assertCount(1, $eventsRes->json('pages'));

        $this->recordingService->deleteRecording($recording);
    }

    public function test_incomplete_stream_without_fullsnapshot_is_flagged_unreplayable_and_missing_meta_is_synthesized(): void
    {
        $orphanId = 'orphan-inc-' . uniqid();
        $orphan = $this->recordingService->ingestBatch($orphanId, [
            ['type' => 3, 'data' => ['source' => 1], 'timestamp' => 1730600001000],
            ['type' => 3, 'data' => ['source' => 1], 'timestamp' => 1730600002000],
        ], ['client_ip' => '198.51.100.61', 'current_page' => '/']);

        $orphanRes = $this->actingAs($this->admin)->getJson("/admin/analytics/recordings/{$orphan->id}/events");
        $orphanRes->assertStatus(200)->assertJson([
            'success' => true,
            'replayable' => false,
            'has_full_snapshot' => false,
        ]);
        $this->assertNotEmpty($orphanRes->json('reason'));
        $this->recordingService->deleteRecording($orphan);

        // Stream with FullSnapshot + trailing incremental, preceded by orphan type 3 and missing Meta
        $noMetaId = 'no-meta-' . uniqid();
        $noMeta = $this->recordingService->ingestBatch($noMetaId, [
            ['type' => 3, 'data' => ['source' => 1], 'timestamp' => 1730600000500],
            ['type' => 2, 'data' => ['node' => ['id' => 1]], 'timestamp' => 1730600001000],
            ['type' => 3, 'data' => ['source' => 3], 'timestamp' => 1730600003000],
        ], ['client_ip' => '198.51.100.62', 'device_type' => 'mobile', 'current_page' => '/tours']);

        $repairedRes = $this->actingAs($this->admin)->getJson("/admin/analytics/recordings/{$noMeta->id}/events");
        $repairedRes->assertStatus(200)->assertJson([
            'success' => true,
            'replayable' => true,
            'has_full_snapshot' => true,
            'has_meta' => false,
        ]);
        $repairedEvents = $repairedRes->json('events');
        // Leading orphan type 3 stripped so playback starts at FullSnapshot + mobile recommended_meta supplied for player
        $this->assertSame([2, 3], array_column($repairedEvents, 'type'));
        $this->assertSame(390, $repairedRes->json('recommended_meta.width'));
        $this->recordingService->deleteRecording($noMeta);
    }
}
