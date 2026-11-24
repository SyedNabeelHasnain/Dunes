<?php

namespace Tests\Feature;

use App\Models\AiCitationObservation;
use App\Models\PrivacyConsent;
use App\Models\Setting;
use App\Models\User;
use App\Models\WebVitalsMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyRumAndAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_has_charset_within_first_512_bytes_and_gates_tracking_behind_consent(): void
    {
        Setting::setVal('meta_pixel_id', '1234567890');
        Setting::setVal('meta_active', '1');
        Setting::setVal('clarity_id', 'testclarity99');
        Setting::setVal('clarity_active', '1');

        $response = $this->get('/');
        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. <meta charset="UTF-8"> must be within first 512 bytes
        $charsetPos = strpos($html, '<meta charset="UTF-8">');
        $this->assertNotFalse($charsetPos);
        $this->assertLessThan(512, $charsetPos, '<meta charset="UTF-8"> must appear near top of <head>');

        // 2. Consent Mode v2 default-denied and Consent UI are rendered
        $this->assertStringContainsString("gtag('consent', 'default'", $html);
        $this->assertStringContainsString("'analytics_storage': (c.analytics ? 'granted' : 'denied')", $html);
        $this->assertStringContainsString('Cookie &amp; Privacy Preferences', $html);
        $this->assertStringContainsString('Cookie Preferences</button>', $html);

        // 3. Unconsented Meta Pixel <noscript> beacon must NOT fire prior to consent
        $this->assertStringNotContainsString('https://www.facebook.com/tr?id=1234567890&ev=PageView&noscript=1', $html);

        // 4. Footer pickup hubs use <h3> under accessible <h2> instead of skipping to <h4>
        $this->assertStringContainsString('<h3 class="text-xs font-black uppercase tracking-wider text-amber-400">Doorstep Hotel Pickup Hubs Across Dubai</h3>', $html);
    }

    public function test_privacy_consent_endpoint_records_hashed_consent_and_sets_cookie(): void
    {
        $res = $this->postJson('/api/v1/privacy/consent', [
            'action' => 'custom',
            'analytics' => true,
            'marketing' => false,
            'functional' => true,
            'page_url' => 'http://localhost/tours',
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'consent' => [
                    'necessary' => true,
                    'analytics' => true,
                    'marketing' => false,
                    'functional' => true,
                ],
            ])
            ->assertCookie('ddt_consent_v1');

        $record = PrivacyConsent::first();
        $this->assertNotNull($record);
        $this->assertSame('custom', $record->action);
        $this->assertTrue($record->analytics);
        $this->assertFalse($record->marketing);
        $this->assertSame(64, strlen((string) $record->ip_hash));
    }

    public function test_rum_vitals_endpoint_and_admin_health_and_ai_dashboards(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->postJson('/api/v1/rum/vitals', [
            'page_path' => '/evening-desert-safari-dubai',
            'metric_name' => 'LCP',
            'metric_value' => 1920,
            'device_type' => 'mobile',
        ])->assertStatus(201)->assertJson(['rating' => 'good']);

        $this->postJson('/api/v1/rum/vitals', [
            'page_path' => '/tours',
            'metric_name' => 'CLS',
            'metric_value' => 0.31,
            'device_type' => 'desktop',
        ])->assertStatus(201)->assertJson(['rating' => 'poor']);

        $this->assertSame(2, WebVitalsMetric::count());

        $this->actingAs($admin)->get(route('admin.settings.privacy.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.health.index'))
            ->assertStatus(200)
            ->assertSee('1920ms')
            ->assertSee('0.31');

        $this->actingAs($admin)->get(route('admin.ai-visibility.index'))->assertStatus(200);
        $this->actingAs($admin)->post(route('admin.ai-visibility.observations.store'), [
            'platform' => 'perplexity',
            'prompt_query' => 'best desert safari dubai licensed tour operator',
            'cited_url' => 'https://dunesdiscoverytourism.com/',
            'brand_mentioned' => '1',
            'citation_position' => 2,
            'sentiment' => 'positive',
        ])->assertRedirect();

        $this->assertDatabaseHas('ai_citation_observations', [
            'platform' => 'perplexity',
            'prompt_query' => 'best desert safari dubai licensed tour operator',
        ]);
    }
}
