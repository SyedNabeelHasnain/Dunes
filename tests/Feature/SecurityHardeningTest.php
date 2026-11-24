<?php

namespace Tests\Feature;

use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\Backlink;
use App\Models\User;
use App\Services\Security\SafeUrlValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_url_validator_blocks_ssrf_targets(): void
    {
        $unsafeUrls = [
            'http://127.0.0.1/admin',
            'http://localhost/server-status',
            'http://169.254.169.254/latest/meta-data/',
            'http://10.0.0.5/internal',
            'http://172.16.0.10/',
            'http://192.168.1.1/',
            'http://100.64.0.1/',
            'http://[::1]/',
            'http://metadata.google.internal/computeMetadata/v1/',
            'http://service.local/api',
            'file:///etc/passwd',
            'https://user:pass@example.com/',
            'https://example.com:22/',
        ];

        foreach ($unsafeUrls as $url) {
            $threw = false;
            try {
                SafeUrlValidator::assertSafeUrl($url, false);
            } catch (InvalidArgumentException) {
                $threw = true;
            }
            $this->assertTrue($threw, "Failed asserting that unsafe URL was blocked: {$url}");
        }

        $this->assertSame(
            'https://example.com/dubai-desert-guide',
            SafeUrlValidator::assertSafeUrl('https://example.com/dubai-desert-guide', false)
        );
    }

    public function test_backlink_health_verification_blocks_internal_ssrf_targets(): void
    {
        Http::fake();

        $backlink = Backlink::create([
            'source_domain' => '169.254.169.254',
            'source_url' => 'http://169.254.169.254/latest/meta-data/',
            'target_url' => 'https://dunesdiscoverytourism.com/',
            'anchor_text' => 'Desert Safari',
            'link_type' => 'dofollow',
            'status' => 'pending',
        ]);

        $result = $backlink->verifyHealth();

        $this->assertFalse($result['success']);
        $this->assertSame('broken', $result['status']);
        Http::assertNothingSent();
    }

    public function test_security_headers_enforce_owasp_baseline(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-site');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    public function test_audit_csv_export_neutralizes_formula_injection(): void
    {
        $user = User::factory()->create();
        $run = AuditRun::create([
            'scan_type' => 'full',
            'status' => 'completed',
            'health_score' => 90,
            'category_scores' => ['seo' => 90],
        ]);
        AuditIssue::create([
            'audit_run_id' => $run->id,
            'category' => 'seo',
            'check_key' => 'formula_test',
            'title' => '=cmd|\'/C calc\'!A0',
            'severity' => 'medium',
            'status' => 'open',
            'verification_type' => 'automated',
            'affected_url' => '+SUM(A1:A2)',
            'evidence' => '@HYPERLINK("http://evil.example")',
            'recommended_action' => '-1+1',
        ]);

        $response = $this->actingAs($user)->get(route('admin.audit.export', ['id' => $run->id, 'format' => 'csv']));
        $response->assertStatus(200);

        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=cmd", $csv);
        $this->assertStringContainsString("'+SUM(A1:A2)", $csv);
        $this->assertStringContainsString("'@HYPERLINK", $csv);
        $this->assertStringContainsString("'-1+1", $csv);
    }

    public function test_cron_run_supports_bearer_header_and_sets_no_store_headers(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer dunes_cron_2026',
        ])->postJson('/api/v1/cron/run');

        $response->assertStatus(200)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
