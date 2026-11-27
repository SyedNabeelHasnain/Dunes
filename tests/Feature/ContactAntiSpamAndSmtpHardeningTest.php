<?php

namespace Tests\Feature;

use App\Mail\ContactAcknowledgement;
use App\Mail\ContactNotification;
use App\Mail\OtpMail;
use App\Mail\WelcomeOfferMail;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\IpRule;
use App\Models\Setting;
use App\Models\SpamEvent;
use App\Models\User;
use App\Models\VerifiedEmail;
use App\Services\EmailMarketingService;
use App\Services\Mail\SmtpDiagnosticService;
use App\Services\Security\ContactSpamGuardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactAntiSpamAndSmtpHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('antispam:contact:ip_min:127.0.0.1');
        RateLimiter::clear('antispam:contact:ip_hr:127.0.0.1');
    }

    private function solvePow(string $token, int $difficulty): string
    {
        $prefix = str_repeat('0', $difficulty);
        for ($i = 0; $i < 200000; $i++) {
            if (str_starts_with(hash('sha256', $token.':'.$i), $prefix)) {
                return (string) $i;
            }
        }

        return '0';
    }

    private function makeAgedChallenge(string $context = 'contact', string $ip = '127.0.0.1', int $ageSeconds = 10): array
    {
        $guard = app(ContactSpamGuardService::class);
        $challenge = $guard->issueChallengeToken($context, $ip);

        // Re-sign payload with aged timestamp so minimum human dwell check passes instantly in test
        [$encoded] = explode('.', $challenge['token'], 2);
        $payload = json_decode(base64_decode(strtr($encoded, '-_', '+/')), true);
        $payload['iat'] = time() - $ageSeconds;
        $newEncoded = rtrim(strtr(base64_encode((string) json_encode($payload)), '+/', '-_'), '=');
        $secret = (string) config('app.key', 'dunes-antispam-fallback-key').'|contact-shield-v1';
        $newToken = $newEncoded.'.'.hash_hmac('sha256', $newEncoded, $secret);

        return [
            'token' => $newToken,
            'pow' => $this->solvePow($newToken, (int) $challenge['difficulty']),
            'difficulty' => (int) $challenge['difficulty'],
        ];
    }

    public function test_contact_page_renders_on_spot_otp_ui_honeypots_and_signed_challenge_token(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('id="contactForm"', false);
        $response->assertSee('action="'.route('contact.submit').'"', false);
        $response->assertSee('name="_dunes_token"', false);
        $response->assertSee('name="_dunes_pow"', false);
        $response->assertSee('name="website_url"', false);
        $response->assertSee('name="company_fax"', false);
        $response->assertSee('name="confirm_email_alt"', false);
        $response->assertSee('id="contactOtpFieldsWrapper"', false);
        $response->assertSee('id="contactSendEmailOtpBtn"', false);
    }

    public function test_screenshot2_casino_phishing_payload_is_blocked_with_zero_emails_and_auto_blocks_ip(): void
    {
        Mail::fake();

        $botIp = '185.220.101.44';
        $payload = [
            'name' => '★★★💰 NEW PLAYER DROP: $3000 is ready to be credited on A fresh slot project — open https://rrmkaryacollege.org/News/go-na.php?1p33f0 🔑',
            'email' => 'sandipbubu1988@gmail.com',
            'subject' => 'v7bgl0',
            'message' => 'copy the active promo code and complete registration to credit it right away before only 17 hours left! ★★★',
        ];

        // Strike 1
        $res1 = $this->withServerVariables(['REMOTE_ADDR' => $botIp])
            ->postJson('/api/v1/contact/submit', $payload);
        $res1->assertStatus(422);

        // Strike 2 -> triggers automatic IP Firewall escalation
        $res2 = $this->withServerVariables(['REMOTE_ADDR' => $botIp])
            ->postJson('/api/v1/contact/submit', $payload);
        $res2->assertStatus(422);

        $this->assertDatabaseCount('contacts', 0);
        Mail::assertNothingSent();
        $this->assertGreaterThanOrEqual(2, SpamEvent::where('ip_address', $botIp)->count());
        $this->assertDatabaseHas('ip_rules', [
            'ip_address' => $botIp,
            'mode' => 'block',
            'status' => 'active',
        ]);

        // Strike 3 -> blocked at API middleware level BEFORE even reaching controller!
        $res3 = $this->withServerVariables(['REMOTE_ADDR' => $botIp])
            ->postJson('/api/v1/contact/submit', [
                'name' => 'Clean Name',
                'email' => 'clean@example.com',
                'subject' => 'Hello',
                'message' => 'Trying to reach API while IP blocked.',
            ]);
        $res3->assertStatus(403);
        $res3->assertJson(['blocked' => true]);
    }

    public function test_honeypot_trap_silently_drops_bot_and_sends_zero_emails(): void
    {
        Mail::fake();

        $res = $this->postJson(route('contact.submit'), [
            'name' => 'Normal Bot Name',
            'email' => 'victim@gmail.com',
            'subject' => 'Safari question',
            'message' => 'Hello I want to ask about desert safari pricing.',
            'company_fax' => '+15551234567', // Hidden honeypot filled by bot
        ]);

        // Stealth drop returns 200 to deceive bots without persisting or mailing
        $res->assertOk();
        $this->assertDatabaseCount('contacts', 0);
        Mail::assertNothingSent();
        $this->assertDatabaseHas('spam_events', ['channel' => 'contact', 'action_taken' => 'blocked']);
    }

    public function test_interactive_layers_enforce_token_dwell_pow_recaptcha_turnstile_and_email_otp_in_order(): void
    {
        Mail::fake();
        config(['antispam.enforce_interactive_in_tests' => true]);

        Setting::updateOrCreate(['setting_key' => 'contact_email_otp_required'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'recaptcha_secret_key'], ['setting_value' => 'test-rc-secret']);
        Setting::updateOrCreate(['setting_key' => 'antispam_recaptcha_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'turnstile_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'turnstile_secret_key'], ['setting_value' => 'test-ts-secret']);
        app(\App\Services\SettingsService::class)->clearCache();

        // 1. Missing signed challenge token -> blocked
        $noTokenRes = $this->postJson(route('contact.submit'), [
            'name' => 'Sarah Al Maktoum',
            'email' => 'sarah.guest@example.com',
            'subject' => 'Evening Safari',
            'message' => 'We are a family of 4 visiting Dubai next week.',
        ]);
        $noTokenRes->assertStatus(422);
        $noTokenRes->assertJson(['blocked' => true]);

        // 2. Too-fast submission (0s dwell time) -> blocked
        $fastChallenge = app(ContactSpamGuardService::class)->issueChallengeToken('contact', '127.0.0.1');
        $fastPow = $this->solvePow($fastChallenge['token'], (int) $fastChallenge['difficulty']);
        $fastRes = $this->postJson(route('contact.submit'), [
            'name' => 'Sarah Al Maktoum',
            'email' => 'sarah.guest@example.com',
            'subject' => 'Evening Safari',
            'message' => 'We are a family of 4 visiting Dubai next week.',
            '_dunes_token' => $fastChallenge['token'],
            '_dunes_pow' => $fastPow,
            '_dunes_telemetry' => json_encode(['keystrokes' => 25, 'pointer' => 5, 'focus' => 3]),
        ]);
        $fastRes->assertStatus(422);
        $this->assertStringContainsString('too fast', strtolower((string) $fastRes->json('message')));

        // 3. Valid token + PoW + telemetry + passing Turnstile & reCAPTCHA, but UNVERIFIED email -> require_verification = true (422)
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true], 200),
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true, 'score' => 0.9], 200),
        ]);

        $challenge1 = $this->makeAgedChallenge('contact', '127.0.0.1', 12);
        $unverifiedRes = $this->postJson(route('contact.submit'), [
            'name' => 'Sarah Al Maktoum',
            'email' => 'sarah.guest@example.com',
            'subject' => 'Evening Safari',
            'message' => 'We are a family of 4 visiting Dubai next week.',
            '_dunes_token' => $challenge1['token'],
            '_dunes_pow' => $challenge1['pow'],
            '_dunes_telemetry' => json_encode(['keystrokes' => 30, 'pointer' => 8, 'focus' => 4]),
            'cf-turnstile-response' => 'valid-ts-token',
            'g-recaptcha-response' => 'valid-rc-token',
        ]);
        $unverifiedRes->assertStatus(422);
        $unverifiedRes->assertJson(['success' => false, 'require_verification' => true]);
        $this->assertDatabaseCount('contacts', 0);

        // 4. Verify email on-spot via VerifiedEmail (same table populated by OTP verify), submit with fresh token -> 200 OK + Emails sent!
        VerifiedEmail::markVerified('sarah.guest@example.com', '127.0.0.1');
        $challenge2 = $this->makeAgedChallenge('contact', '127.0.0.1', 15);

        $verifiedRes = $this->postJson(route('contact.submit'), [
            'name' => 'Sarah Al Maktoum',
            'email' => 'sarah.guest@example.com',
            'subject' => 'Evening Safari',
            'message' => 'We are a family of 4 visiting Dubai next week and want VIP seating.',
            '_dunes_token' => $challenge2['token'],
            '_dunes_pow' => $challenge2['pow'],
            '_dunes_telemetry' => json_encode(['keystrokes' => 35, 'pointer' => 12, 'focus' => 5]),
            'cf-turnstile-response' => 'valid-ts-token',
            'g-recaptcha-response' => 'valid-rc-token',
        ]);
        $verifiedRes->assertOk();
        $verifiedRes->assertJson(['success' => true, 'verified' => true]);
        $this->assertDatabaseHas('contacts', [
            'email' => 'sarah.guest@example.com',
            'status' => 'new',
            'is_spam' => false,
            'is_verified' => true,
        ]);

        Mail::assertSent(ContactNotification::class);
        Mail::assertSent(ContactAcknowledgement::class);

        // 5. Replaying the exact same single-use challenge token fails immediately
        $replayRes = $this->postJson(route('contact.submit'), [
            'name' => 'Sarah Al Maktoum',
            'email' => 'sarah.guest@example.com',
            'subject' => 'Replay Attempt',
            'message' => 'Trying to replay an already used security token.',
            '_dunes_token' => $challenge2['token'],
            '_dunes_pow' => $challenge2['pow'],
            '_dunes_telemetry' => json_encode(['keystrokes' => 35, 'pointer' => 12, 'focus' => 5]),
            'cf-turnstile-response' => 'valid-ts-token',
            'g-recaptcha-response' => 'valid-rc-token',
        ]);
        $replayRes->assertStatus(422);
    }

    public function test_zero_reflection_greeting_sanitizer_strips_phishing_ads_and_urls(): void
    {
        $this->assertSame(
            'Valued Guest',
            ContactSpamGuardService::sanitizeGreetingName('★★★💰 NEW PLAYER DROP: $3000 https://evil.org/go-na.php?1p33f0')
        );
        $this->assertSame(
            'Valued Guest',
            ContactSpamGuardService::sanitizeGreetingName('visit example.com now')
        );
        $this->assertSame(
            'Valued Guest',
            ContactSpamGuardService::sanitizeGreetingName('v7bgl0')
        );
        $this->assertSame(
            'Ahmed Al Mansoori',
            ContactSpamGuardService::sanitizeGreetingName('ahmed al mansoori')
        );
    }

    public function test_unverified_clean_contact_does_not_dispatch_customer_ack_by_default(): void
    {
        Mail::fake();

        $res = $this->postJson(route('contact.submit'), [
            'name' => 'John Traveler',
            'email' => 'john.unverified@example.com',
            'subject' => 'Morning Safari Inquiry',
            'message' => 'Can you confirm hotel pickup time from Dubai Marina?',
        ]);

        $res->assertOk();
        Mail::assertSent(ContactNotification::class);
        // Under default 'verified_only' backscatter protection mode, unverified external email receives NO auto-ack!
        Mail::assertNotSent(ContactAcknowledgement::class);
    }

    public function test_welcome_offer_and_otp_endpoints_block_spam_and_disposable_domains(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['setting_key' => 'promo_welcome_modal_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'welcome_popup_active'], ['setting_value' => '1']);
        app(\App\Services\SettingsService::class)->clearCache();

        // 1. Welcome offer blocks phishing name (closes secondary reflection hole)
        $offerRes = $this->postJson('/api/v1/welcome-offer/claim', [
            'name' => 'FREE $3000 BONUS https://evil-slot.org/go-bw.php?123',
            'email' => 'victim@gmail.com',
            'phone' => '+971501234567',
        ]);
        $offerRes->assertStatus(422);
        Mail::assertNotSent(WelcomeOfferMail::class);

        // 2. OTP endpoint blocks disposable email domain
        $otpRes = $this->postJson('/api/v1/email/otp/send', [
            'email' => 'spammer123@mailinator.com',
        ]);
        $otpRes->assertStatus(422);
        Mail::assertNotSent(OtpMail::class);
    }

    public function test_smtp_diagnostic_classifies_554_account_suspension_vs_invalid_customer_email_and_configures_failover(): void
    {
        $diagSvc = app(SmtpDiagnosticService::class);

        $hostinger554 = $diagSvc->recordFailure(
            'draft_recovery',
            'Expected response code "250" but got code "554", with message "554 5.7.1 Outbound sending is disabled for this account".',
            'sugamgiri99@gmail.com'
        );

        $this->assertSame('account_suspended', $hostinger554['category']);
        $this->assertSame('smtp_server', $hostinger554['source']);
        $this->assertSame('554 5.7.1', $hostinger554['smtp_code']);

        $health = $diagSvc->getHealthStatus();
        $this->assertSame('degraded', $health['status']);

        $badRecipient = $diagSvc->diagnose('550 5.1.1 The email account that you tried to reach does not exist.', 'typo@gmail.com');
        $this->assertSame('recipient_rejected', $badRecipient['category']);
        $this->assertSame('recipient_email', $badRecipient['source']);

        // Verify runtime automatic failover mailer wiring
        Setting::updateOrCreate(['setting_key' => 'smtp_driver'], ['setting_value' => 'smtp']);
        Setting::updateOrCreate(['setting_key' => 'smtp_host'], ['setting_value' => 'smtp.hostinger.com']);
        Setting::updateOrCreate(['setting_key' => 'smtp_port'], ['setting_value' => '465']);
        Setting::updateOrCreate(['setting_key' => 'smtp_encryption'], ['setting_value' => 'ssl']);
        Setting::updateOrCreate(['setting_key' => 'smtp_fallback_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'smtp_fallback_host'], ['setting_value' => 'smtp-relay.brevo.com']);
        Setting::updateOrCreate(['setting_key' => 'smtp_fallback_port'], ['setting_value' => '587']);
        Setting::updateOrCreate(['setting_key' => 'smtp_fallback_encryption'], ['setting_value' => 'tls']);
        app(\App\Services\SettingsService::class)->clearCache();

        app(EmailMarketingService::class)->applySmtpConfig(true);

        $this->assertSame('failover', config('mail.default'));
        $this->assertSame(['smtp', 'smtp_fallback'], config('mail.mailers.failover.mailers'));
        $this->assertSame('smtp-relay.brevo.com', config('mail.mailers.smtp_fallback.host'));
    }

    public function test_admin_inquiries_scan_and_purge_wipes_historical_spam_while_preserving_clean_leads(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $legit = Contact::create([
            'name' => 'Amina Hassan',
            'email' => 'amina.hassan@example.com',
            'phone' => '+971501112222',
            'subject' => 'Private Desert Safari for 6 Guests',
            'message' => 'Hello, we would like to book a private Hummer desert safari this Friday.',
            'status' => 'new',
            'is_spam' => false,
            'ip_address' => '94.200.10.20',
        ]);

        $spam = Contact::create([
            'name' => '★★★💰 BALANCE NOTICE: $3000 is ready to be credited on Our partner club — proceed to http://peroa.com.br/js/go-bw.php?4k53qd 🔑',
            'email' => 'blapbenigno1576@gmail.com',
            'phone' => '',
            'subject' => 'bw6no5',
            'message' => 'copy the active promo code and create your profile to credit it right away before only 278 spots left!',
            'status' => 'new',
            'is_spam' => false,
            'ip_address' => '193.32.162.88',
        ]);

        // 1. Preview scan identifies ONLY the spam row
        $preview = $this->actingAs($admin)->postJson(route('admin.inquiries.scan-purge-spam'), [
            'mode' => 'preview',
        ]);
        $preview->assertOk();
        $preview->assertJson([
            'success' => true,
            'spam_count' => 1,
            'unique_ips_count' => 1,
        ]);

        // 2. Purge mode deletes spam row + blocks bot IP while keeping legitimate inquiry untouched!
        $purge = $this->actingAs($admin)->postJson(route('admin.inquiries.scan-purge-spam'), [
            'mode' => 'purge',
            'block_ips' => true,
        ]);
        $purge->assertOk();
        $purge->assertJson([
            'success' => true,
            'processed' => 1,
            'blocked_ips' => 1,
        ]);

        $this->assertDatabaseHas('contacts', ['id' => $legit->id]);
        $this->assertDatabaseMissing('contacts', ['id' => $spam->id]);
        $this->assertDatabaseHas('ip_rules', [
            'ip_address' => '193.32.162.88',
            'mode' => 'block',
            'status' => 'active',
        ]);
    }
}
