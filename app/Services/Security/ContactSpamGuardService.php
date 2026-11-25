<?php

namespace App\Services\Security;

use App\Models\IpRule;
use App\Models\SpamEvent;
use App\Models\VerifiedEmail;
use App\Services\IpSecurityService;
use App\Services\SettingsService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Multi-Layer Enterprise Anti-Spam, Bot Shield & Backscatter-Prevention Engine.
 *
 * Defense-in-depth layers executed in order:
 *  Layer 1: IP Firewall, per-IP / per-email rate limits & signed single-use HMAC form token
 *  Layer 2: Multi-trap invisible honeypots, human dwell time, JS behavioral telemetry & SHA-256 micro-PoW
 *  Layer 3: Server-side Google reCAPTCHA (v3/Enterprise) & Cloudflare Turnstile verification
 *  Layer 4: Heuristic risk-scoring engine (name sanity, gibberish/entropy, casino/phishing lexicon,
 *           URL flood, disposable email & MX validation, cross-IP template-burst deduplication)
 *  Layer 5: On-spot Email OTP verification enforcement (contact form) & auto-escalation to IP Firewall
 */
class ContactSpamGuardService
{
    public const HONEYPOT_FIELDS = ['website_url', 'company_fax', 'confirm_email_alt'];

    public const DEFAULT_POW_DIFFICULTY = 3; // 3 leading hex zeros (~4,096 avg hashes = ~15-30ms in JS)

    /**
     * Built-in high-precision disposable/throwaway email domains.
     *
     * @var list<string>
     */
    public const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
        'guerrillamailblock.com', 'sharklasers.com', 'grr.la', 'pokemail.net', 'spam4.me',
        'tempmail.com', 'temp-mail.org', 'temp-mail.io', '10minutemail.com', '10minutemail.net',
        'yopmail.com', 'yopmail.fr', 'yopmail.net', 'trashmail.com', 'trashmail.net',
        'trashmail.me', 'dispostable.com', 'maildrop.cc', 'getnada.com', 'nada.email',
        'mohmal.com', 'fakeinbox.com', 'throwawaymail.com', 'mintemail.com', 'mytrashmail.com',
        'mailnesia.com', 'getairmail.com', 'mailcatch.com', 'tempr.email', 'discard.email',
        'emailondeck.com', 'incognitomail.org', 'burnermail.io', 'tempail.com', 'moakt.com',
        'harakirimail.com', 'spamgourmet.com', 'anonymbox.com', 'inboxkitten.com', 'mailsac.com',
        'minuteinbox.com', 'mail7.io', 'crazymailing.com', 'tmpmail.org', 'tmpmail.net',
        'dropmail.me', 'emailfake.com', 'generator.email', 'fakemail.net', 'luxusmail.org',
        'tmailor.com', 'linshi-email.com', 'guerrillamail.biz', 'guerrillamail.de', 'binkmail.com',
    ];

    /**
     * High-signal spam / phishing / casino / SEO-pitch phrases mapped to risk weights.
     *
     * @var array<string, int>
     */
    public const SPAM_PHRASE_WEIGHTS = [
        'new player drop' => 65,
        'slot project' => 65,
        'slot platform' => 65,
        'no upfront deposit' => 60,
        'zero deposit' => 55,
        'active promo code' => 55,
        'personal promo code' => 55,
        'unlocked access key' => 60,
        'personal access key' => 55,
        'ready to be credited' => 55,
        'reserved for your bonus account' => 60,
        'bonus account' => 45,
        'partner club' => 45,
        'online club' => 40,
        'fresh online club' => 60,
        'exclusive gaming project' => 60,
        'balance notice' => 50,
        'account alert' => 45,
        'before they shut it down' => 55,
        'spots left' => 30,
        '30-second sign-up' => 45,
        'complete registration to credit' => 55,
        'create your profile to credit' => 55,
        'free spins' => 50,
        'online casino' => 55,
        'crypto recovery' => 55,
        'bitcoin recovery' => 55,
        'forex signal' => 45,
        'whatsapp blast' => 45,
        'guest post opportunity' => 40,
        'backlink package' => 45,
        'rank #1 on google' => 45,
        'first page of google' => 40,
        'seo services' => 30,
        'viagra' => 65,
        'cialis' => 65,
        'porn' => 60,
        'escort service' => 60,
        'wire transfer inheritance' => 65,
    ];

    public function __construct(
        protected SettingsService $settings,
        protected IpSecurityService $ipSecurity,
        protected VisitorTrackerService $tracker,
    ) {}

    /**
     * Issue a server-signed, IP-bound, single-use challenge token containing PoW parameters.
     */
    public function issueChallengeToken(string $context = 'contact', ?string $ip = null): array
    {
        $clientIp = $ip ?: $this->resolveClientIp();
        $nonce = bin2hex(random_bytes(16));
        $issuedAt = time();
        $difficulty = $this->getPowDifficulty();

        $payload = [
            'ctx' => $context,
            'iat' => $issuedAt,
            'nonce' => $nonce,
            'iph' => hash('sha256', $clientIp.'|'.$context),
            'diff' => $difficulty,
        ];

        $encoded = rtrim(strtr(base64_encode((string) json_encode($payload)), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $encoded, $this->signingSecret());
        $token = $encoded.'.'.$sig;

        return [
            'token' => $token,
            'issued_at' => $issuedAt,
            'difficulty' => $difficulty,
            'min_dwell_seconds' => $this->getMinDwellSeconds(),
        ];
    }

    /**
     * Verify a previously issued challenge token, its TTL, minimum dwell time, replay nonce, and PoW solution.
     */
    public function verifyChallengeToken(
        string $token,
        string $expectedContext,
        ?string $powNonce,
        ?string $clientIp = null,
        bool $consumeNonce = true,
        bool $requirePow = true,
    ): array {
        $token = trim($token);
        if ($token === '' || ! str_contains($token, '.')) {
            return ['valid' => false, 'reason' => 'missing_challenge_token'];
        }

        [$encoded, $sig] = explode('.', $token, 2);
        $expectedSig = hash_hmac('sha256', $encoded, $this->signingSecret());
        if (! hash_equals($expectedSig, $sig)) {
            return ['valid' => false, 'reason' => 'invalid_challenge_signature'];
        }

        $decodedJson = base64_decode(strtr($encoded, '-_', '+/'), true);
        $payload = is_string($decodedJson) ? json_decode($decodedJson, true) : null;
        if (! is_array($payload)) {
            return ['valid' => false, 'reason' => 'malformed_challenge_token'];
        }

        if (($payload['ctx'] ?? '') !== $expectedContext) {
            return ['valid' => false, 'reason' => 'challenge_context_mismatch'];
        }

        $issuedAt = (int) ($payload['iat'] ?? 0);
        $now = time();
        $elapsed = $now - $issuedAt;

        if ($issuedAt <= 0 || $elapsed < 0 || $elapsed > 7200) {
            return ['valid' => false, 'reason' => 'challenge_token_expired'];
        }

        $minDwell = $this->getMinDwellSeconds();
        if ($elapsed < $minDwell) {
            return [
                'valid' => false,
                'reason' => 'submitted_too_fast',
                'elapsed_seconds' => $elapsed,
            ];
        }

        $nonce = (string) ($payload['nonce'] ?? '');
        if ($nonce === '') {
            return ['valid' => false, 'reason' => 'missing_challenge_nonce'];
        }

        $nonceCacheKey = "antispam_used_nonce:{$expectedContext}:{$nonce}";
        if (Cache::has($nonceCacheKey)) {
            return ['valid' => false, 'reason' => 'challenge_token_replayed'];
        }

        $resolvedIp = $clientIp ?: $this->resolveClientIp();
        $expectedIpHash = hash('sha256', $resolvedIp.'|'.$expectedContext);
        $ipMatched = hash_equals((string) ($payload['iph'] ?? ''), $expectedIpHash);

        $difficulty = max(0, (int) ($payload['diff'] ?? self::DEFAULT_POW_DIFFICULTY));
        if ($requirePow && $difficulty > 0) {
            $powStr = trim((string) $powNonce);
            if ($powStr === '') {
                return ['valid' => false, 'reason' => 'missing_pow_solution'];
            }
            $hash = hash('sha256', $token.':'.$powStr);
            if (! str_starts_with($hash, str_repeat('0', $difficulty))) {
                return ['valid' => false, 'reason' => 'invalid_pow_solution'];
            }
        }

        if ($consumeNonce) {
            Cache::put($nonceCacheKey, 1, now()->addHours(2));
        }

        return [
            'valid' => true,
            'reason' => null,
            'elapsed_seconds' => $elapsed,
            'ip_matched' => $ipMatched,
        ];
    }

    /**
     * Full ordered 6-layer evaluation for Contact Form submissions.
     */
    public function evaluateContactSubmission(Request $request): array
    {
        $clientIp = $this->resolveClientIp($request);
        $name = trim((string) $request->input('name', ''));
        $email = trim(strtolower((string) $request->input('email', '')));
        $phone = trim((string) $request->input('phone', ''));
        $subject = trim((string) $request->input('subject', ''));
        $message = trim((string) $request->input('message', ''));

        $reasons = [];
        $score = 0;
        $strictMode = $this->isStrictMode();
        $enforceInteractive = $this->shouldEnforceInteractiveChecks();
        $meta = [
            'ip' => $clientIp,
            'strict_mode' => $strictMode,
            'interactive_enforced' => $enforceInteractive,
        ];

        // ── LAYER 1A: Multi-Trap Invisible Honeypots (ALWAYS enforced) ────────
        foreach (self::HONEYPOT_FIELDS as $field) {
            if (trim((string) $request->input($field, '')) !== '') {
                $reasons[] = "honeypot_filled:{$field}";
                $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, 100, 'blocked', $reasons);

                return [
                    'verdict' => 'block',
                    'stealth_drop' => true,
                    'score' => 100,
                    'reasons' => $reasons,
                    'require_verification' => false,
                    'http_status' => 200,
                    'message' => 'Thank you! Your message has been sent successfully.',
                    'meta' => $meta,
                ];
            }
        }

        // ── LAYER 1B: Multi-Tier Rate Limiting (IP + Email) ───────────────────
        if (! app()->runningUnitTests() || config('antispam.enforce_rate_limits_in_tests', false)) {
            $ipMinKey = "antispam:contact:ip_min:{$clientIp}";
            $ipHourKey = "antispam:contact:ip_hr:{$clientIp}";
            $emailKey = 'antispam:contact:email:'.md5($email);

            if (RateLimiter::tooManyAttempts($ipMinKey, 3) || RateLimiter::tooManyAttempts($ipHourKey, 8)) {
                $reasons[] = 'rate_limit_ip_exceeded';
                $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, 80, 'blocked', $reasons);

                return [
                    'verdict' => 'block',
                    'stealth_drop' => false,
                    'score' => 80,
                    'reasons' => $reasons,
                    'require_verification' => false,
                    'http_status' => 429,
                    'message' => 'Too many inquiry submissions from your connection. Please wait a few minutes or message us directly on WhatsApp.',
                    'meta' => $meta,
                ];
            }

            if ($email !== '' && RateLimiter::tooManyAttempts($emailKey, 3)) {
                $reasons[] = 'rate_limit_email_exceeded';
                $this->logSpamEvent($request, 'contact', $clientIp, $email, $name, $subject, 65, 'blocked', $reasons);

                return [
                    'verdict' => 'block',
                    'stealth_drop' => false,
                    'score' => 65,
                    'reasons' => $reasons,
                    'require_verification' => false,
                    'http_status' => 429,
                    'message' => 'We recently received inquiries from this email address. Please wait a few minutes before sending another message.',
                    'meta' => $meta,
                ];
            }

            RateLimiter::hit($ipMinKey, 60);
            RateLimiter::hit($ipHourKey, 3600);
            if ($email !== '') {
                RateLimiter::hit($emailKey, 600);
            }
        }

        // ── LAYER 1C & 2: Signed Token, Dwell Time, Micro-PoW & Telemetry ─────
        $tokenInput = trim((string) $request->input('_dunes_token', ''));
        $powNonce = (string) $request->input('_dunes_pow', '');
        if ($enforceInteractive || $tokenInput !== '') {
            $tokenCheck = $this->verifyChallengeToken($tokenInput, 'contact', $powNonce, $clientIp, true, true);
            $meta['challenge'] = $tokenCheck;

            if (! $tokenCheck['valid']) {
                $reason = $tokenCheck['reason'] ?? 'invalid_challenge_token';
                $reasons[] = $reason;

                if ($reason === 'submitted_too_fast') {
                    $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, 85, 'blocked', $reasons);

                    return [
                        'verdict' => 'block',
                        'stealth_drop' => false,
                        'score' => 85,
                        'reasons' => $reasons,
                        'require_verification' => false,
                        'http_status' => 422,
                        'message' => 'Submission was too fast. Please review your message for a moment and submit again.',
                        'meta' => $meta,
                    ];
                }

                // Missing/invalid/replayed token or PoW = headless bot bypassing the page
                $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, 90, 'blocked', $reasons);

                return [
                    'verdict' => 'block',
                    'stealth_drop' => false,
                    'score' => 90,
                    'reasons' => $reasons,
                    'require_verification' => false,
                    'http_status' => 422,
                    'message' => 'Your security session expired or is invalid. Please refresh the Contact page and try again.',
                    'meta' => $meta,
                ];
            }

            if (! ($tokenCheck['ip_matched'] ?? true)) {
                $score += 15;
                $reasons[] = 'challenge_ip_changed';
            }

            // Behavioral telemetry score (pointer, keydown, focus events)
            $telemetryRaw = (string) $request->input('_dunes_telemetry', '');
            $telemetry = json_decode($telemetryRaw, true);
            if (is_array($telemetry)) {
                $interactions = (int) ($telemetry['keystrokes'] ?? 0)
                    + (int) ($telemetry['pointer'] ?? 0)
                    + (int) ($telemetry['focus'] ?? 0);
                $meta['interactions'] = $interactions;
                if ($interactions < 2) {
                    $score += 25;
                    $reasons[] = 'low_human_interaction_telemetry';
                }
            } elseif ($strictMode) {
                $score += 20;
                $reasons[] = 'missing_behavioral_telemetry';
            }
        }

        // ── LAYER 3: Server-Side Bot Verification (reCAPTCHA + Turnstile) ─────
        if ($enforceInteractive) {
            $botVerify = $this->verifyExternalChallenges($request, $clientIp, 'contact');
            $meta['bot_challenges'] = $botVerify;
            $score += $botVerify['score_penalty'];
            $reasons = array_merge($reasons, $botVerify['reasons']);

            if ($botVerify['hard_reject']) {
                $finalScore = min(100, max(75, $score));
                $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, $finalScore, 'blocked', $reasons);

                return [
                    'verdict' => 'block',
                    'stealth_drop' => false,
                    'score' => $finalScore,
                    'reasons' => array_values(array_unique($reasons)),
                    'require_verification' => false,
                    'http_status' => 422,
                    'message' => 'Automated security verification failed. Please refresh the page and complete the security check.',
                    'meta' => $meta,
                ];
            }
        }

        // ── LAYER 4: Intelligent Content, Entropy, Domain & Burst Scoring ─────
        $heuristic = $this->scorePayload([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
        ], $clientIp, true);

        $score = min(100, $score + $heuristic['score']);
        $reasons = array_values(array_unique(array_merge($reasons, $heuristic['reasons'])));
        $meta['heuristic_score'] = $heuristic['score'];
        $meta['hard_content_block'] = $heuristic['hard_block'];

        $blockThreshold = $strictMode ? 55 : 65;
        $quarantineThreshold = $strictMode ? 30 : 38;

        if ($heuristic['hard_block'] || $score >= $blockThreshold) {
            $isStealth = ! $heuristic['user_fixable'];
            $this->recordAndEscalate($request, 'contact', $clientIp, $email, $name, $subject, $score, 'blocked', $reasons);

            return [
                'verdict' => 'block',
                'stealth_drop' => $isStealth,
                'score' => $score,
                'reasons' => $reasons,
                'require_verification' => false,
                'http_status' => $isStealth ? 200 : 422,
                'message' => $isStealth
                    ? 'Thank you! Your message has been sent successfully.'
                    : ($heuristic['user_error'] ?: 'Your message could not be submitted because it triggered our anti-spam security filter. Please remove links or promotional text and try again.'),
                'meta' => $meta,
            ];
        }

        // ── LAYER 5: On-Spot Email OTP Verification Enforcement ───────────────
        $otpRequired = $this->isContactOtpRequired($enforceInteractive);
        $isVerified = $email !== '' && VerifiedEmail::isVerified($email);
        $meta['email_verified'] = $isVerified;
        $meta['otp_required'] = $otpRequired;

        if ($otpRequired && ! $isVerified) {
            return [
                'verdict' => 'require_otp',
                'stealth_drop' => false,
                'score' => $score,
                'reasons' => $reasons,
                'require_verification' => true,
                'http_status' => 422,
                'message' => 'Please verify your email address with the 6-digit OTP code before sending your inquiry.',
                'meta' => $meta,
            ];
        }

        // ── Verdict: Quarantine vs Clean ──────────────────────────────────────
        if ($score >= $quarantineThreshold) {
            $this->logSpamEvent($request, 'contact', $clientIp, $email, $name, $subject, $score, 'quarantined', $reasons);

            return [
                'verdict' => 'quarantine',
                'stealth_drop' => false,
                'score' => $score,
                'reasons' => $reasons,
                'require_verification' => false,
                'http_status' => 200,
                'message' => 'Thank you! Your message has been received and queued for review.',
                'meta' => $meta,
            ];
        }

        return [
            'verdict' => 'clean',
            'stealth_drop' => false,
            'score' => $score,
            'reasons' => $reasons,
            'require_verification' => false,
            'http_status' => 200,
            'message' => 'Thank you! Your message has been sent successfully.',
            'meta' => $meta,
        ];
    }

    /**
     * Evaluate side-door lead forms (Welcome Offer popup, Newsletter subscription) so bots cannot pivot there.
     */
    public function evaluateLeadSubmission(Request $request, string $channel): array
    {
        $clientIp = $this->resolveClientIp($request);
        $name = trim((string) $request->input('name', $request->input('first_name', '')));
        $email = trim(strtolower((string) $request->input('email', '')));
        $phone = trim((string) $request->input('phone', ''));

        $reasons = [];

        foreach (self::HONEYPOT_FIELDS as $field) {
            if (trim((string) $request->input($field, '')) !== '') {
                $reasons[] = "honeypot_filled:{$field}";
                $this->recordAndEscalate($request, $channel, $clientIp, $email, $name, '', 100, 'blocked', $reasons);

                return ['allowed' => false, 'stealth_drop' => true, 'score' => 100, 'reasons' => $reasons, 'http_status' => 200, 'message' => 'Thank you!'];
            }
        }

        $heuristic = $this->scorePayload([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => '',
            'message' => '',
        ], $clientIp, true);

        if ($heuristic['hard_block'] || $heuristic['score'] >= 50) {
            $this->recordAndEscalate($request, $channel, $clientIp, $email, $name, '', $heuristic['score'], 'blocked', $heuristic['reasons']);

            return [
                'allowed' => false,
                'stealth_drop' => false,
                'score' => $heuristic['score'],
                'reasons' => $heuristic['reasons'],
                'http_status' => 422,
                'message' => $heuristic['user_error'] ?: 'Please enter a valid personal name and email address.',
            ];
        }

        return ['allowed' => true, 'stealth_drop' => false, 'score' => $heuristic['score'], 'reasons' => $heuristic['reasons'], 'http_status' => 200, 'message' => null];
    }

    /**
     * Protect OTP sending endpoint against OTP-bombing / backscatter spam bots.
     */
    public function guardOtpRequest(Request $request, string $email): array
    {
        $clientIp = $this->resolveClientIp($request);
        $email = trim(strtolower($email));
        $reasons = [];

        // Honeypot check if present
        foreach (self::HONEYPOT_FIELDS as $field) {
            if (trim((string) $request->input($field, '')) !== '') {
                $reasons[] = "otp_honeypot:{$field}";
                $this->recordAndEscalate($request, 'otp', $clientIp, $email, '', 'OTP', 100, 'blocked', $reasons);

                return ['allowed' => false, 'http_status' => 422, 'message' => 'Unable to process verification request.'];
            }
        }

        // Disposable email domain check
        $domain = Str::after($email, '@');
        if ($domain !== '' && $this->isDisposableOrBlockedDomain($domain)) {
            $reasons[] = 'otp_disposable_email_domain';
            $this->logSpamEvent($request, 'otp', $clientIp, $email, '', 'OTP', 70, 'blocked', $reasons);

            return [
                'allowed' => false,
                'http_status' => 422,
                'message' => 'Disposable or temporary email addresses are not supported. Please use a valid personal or business email.',
            ];
        }

        // Per-IP OTP send cap across ALL target emails (stops bots bombing 100 different Gmail victims!)
        if (! app()->runningUnitTests() || config('antispam.enforce_rate_limits_in_tests', false)) {
            $ipOtpKey = "antispam:otp_ip:{$clientIp}";
            if (RateLimiter::tooManyAttempts($ipOtpKey, 5)) {
                $wait = RateLimiter::availableIn($ipOtpKey);
                $reasons[] = 'otp_ip_rate_limit_exceeded';
                $this->recordAndEscalate($request, 'otp', $clientIp, $email, '', 'OTP', 80, 'blocked', $reasons);

                return [
                    'allowed' => false,
                    'http_status' => 429,
                    'message' => "Too many verification code requests from your network. Please wait {$wait}s and try again.",
                ];
            }
            RateLimiter::hit($ipOtpKey, 900); // 5 OTPs per 15 min per IP
        }

        // DNS MX verification (outside tests unless enabled)
        if ($domain !== '' && $this->shouldCheckEmailMx() && ! $this->hasValidMailDns($domain)) {
            $reasons[] = 'otp_email_domain_no_mx';

            return [
                'allowed' => false,
                'http_status' => 422,
                'message' => 'That email domain does not appear to accept mail. Please double-check your email address for typos.',
            ];
        }

        return ['allowed' => true, 'http_status' => 200, 'message' => null];
    }

    /**
     * Pure heuristic & intelligence scoring engine (0–100).
     * Reused BOTH for live submissions AND for retroactive DB spam scanning in Admin Inquiries!
     */
    public function scorePayload(array $payload, ?string $clientIp = null, bool $trackBurst = false): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim(strtolower((string) ($payload['email'] ?? '')));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        $score = 0;
        $reasons = [];
        $hardBlock = false;
        $userFixable = false;
        $userError = null;

        // 1. Name Sanity Checks (directly stops Screenshot-2 attack pattern)
        if ($name !== '') {
            if ($this->containsUrlOrDomain($name)) {
                $score += 85;
                $reasons[] = 'name_contains_url_or_domain';
                $hardBlock = true;
                $userFixable = true;
                $userError = 'Please enter your real name without website links or URLs.';
            }

            $nameLen = mb_strlen($name);
            if ($nameLen > 120) {
                $score += 80;
                $reasons[] = 'name_extreme_length';
                $hardBlock = true;
                $userFixable = true;
                $userError = $userError ?: 'Full name is too long. Please enter your first and last name only.';
            } elseif ($nameLen > 80) {
                $score += 55;
                $reasons[] = 'name_excessive_length';
                $userFixable = true;
                $userError = $userError ?: 'Full name must be under 80 characters.';
            }

            // Currency amounts or promotional bait inside Name field ($3000, €500, promo code, bonus, etc.)
            if (preg_match('/([$€£¥]\s*\d{2,}|\b\d{3,}\s*(?:usd|eur|bonus|reward|free)\b)/i', $name)) {
                $score += 75;
                $reasons[] = 'name_currency_promo_bait';
                $hardBlock = true;
            }

            // Star/emoji/symbol flooding in Name (★★★💰🔥🔑)
            preg_match_all('/[\x{2600}-\x{27BF}\x{1F300}-\x{1FAFF}★☆✦✧•●◆◇►▶♦♣♠♥]/u', $name, $emojiMatches);
            $emojiCount = count($emojiMatches[0] ?? []);
            if ($emojiCount >= 2) {
                $score += 50;
                $reasons[] = "name_emoji_symbol_flood:{$emojiCount}";
                if ($emojiCount >= 4) {
                    $hardBlock = true;
                }
            }

            // PHP script / suspicious path inside Name
            if (preg_match('/\b[a-z0-9_-]+\.php(?:\?|\b)/i', $name)) {
                $score += 80;
                $reasons[] = 'name_php_script_link';
                $hardBlock = true;
            }
        }

        // 2. Gibberish / High-Entropy Bot String Detection (catches v7bgl0, ws8rs, x30lux, dqz78, etc.)
        if ($subject !== '' && $this->isGibberishToken($subject)) {
            $score += 40;
            $reasons[] = 'gibberish_subject';
        }
        if ($message !== '' && $this->isGibberishToken($message)) {
            $score += 40;
            $reasons[] = 'gibberish_message';
        }
        if ($name !== '' && $this->isGibberishToken($name)) {
            $score += 35;
            $reasons[] = 'gibberish_name';
        }

        // 3. Weighted Casino / Phishing / Crypto / SEO Lexicon across combined payload
        $combined = mb_strtolower(trim($name.' '.$subject.' '.$message));
        $matchedPhrases = 0;
        foreach (self::SPAM_PHRASE_WEIGHTS as $phrase => $weight) {
            if ($phrase !== '' && str_contains($combined, $phrase)) {
                $score += $weight;
                $reasons[] = 'spam_phrase:'.Str::slug($phrase, '_');
                $matchedPhrases++;
                if ($weight >= 60) {
                    $hardBlock = true;
                }
            }
        }
        if ($matchedPhrases >= 2) {
            $hardBlock = true;
        }

        // Custom CMS blocked keywords
        foreach ($this->getCustomBlockedKeywords() as $kw) {
            if ($kw !== '' && str_contains($combined, $kw)) {
                $score += 65;
                $reasons[] = 'custom_blocked_keyword:'.Str::slug($kw, '_');
                $hardBlock = true;
            }
        }

        // 4. URL & Suspicious Script Link Analysis across Subject + Message
        $bodyText = trim($subject.' '.$message);
        if ($bodyText !== '') {
            preg_match_all('/(?:https?:\/\/|www\.)[^\s<>"]+/i', $bodyText, $urlMatches);
            $urlCount = count($urlMatches[0] ?? []);
            if ($urlCount >= 2) {
                $score += 65;
                $reasons[] = "multiple_urls_in_body:{$urlCount}";
                $hardBlock = true;
            } elseif ($urlCount === 1) {
                $score += 30;
                $reasons[] = 'url_in_body';
            }

            if (preg_match('/\/go-[a-z0-9_-]+\.php\?/i', $combined)) {
                $score += 80;
                $reasons[] = 'phishing_redirect_script_pattern';
                $hardBlock = true;
            }
        }

        // 5. Email Domain Intelligence (Disposable / Custom Blocked / MX)
        if ($email !== '' && str_contains($email, '@')) {
            $domain = Str::after($email, '@');
            if ($this->isDisposableOrBlockedDomain($domain)) {
                $score += 60;
                $reasons[] = 'disposable_or_blocked_email_domain';
                $hardBlock = true;
                $userFixable = true;
                $userError = $userError ?: 'Please use a permanent personal or business email address (disposable email services are blocked).';
            } elseif ($trackBurst && $this->shouldCheckEmailMx() && ! $this->hasValidMailDns($domain)) {
                $score += 35;
                $reasons[] = 'email_domain_no_mx';
            }
        }

        // 6. Cross-IP Fuzzy Template Burst Detection (stops distributed rotating-IP botnets!)
        if ($trackBurst && mb_strlen($combined) >= 25) {
            $templateHash = $this->computeTemplateFingerprint($name, $message);
            if ($templateHash !== null) {
                $burstKey = "antispam_template_burst:{$templateHash}";
                $hits = (int) Cache::get($burstKey, 0) + 1;
                Cache::put($burstKey, $hits, now()->addHours(6));
                if ($hits >= 3) {
                    $score += 65;
                    $reasons[] = "botnet_template_burst:{$hits}";
                    $hardBlock = true;
                } elseif ($hits === 2) {
                    $score += 40;
                    $reasons[] = 'duplicate_template_repeat';
                }
            }
        }

        return [
            'score' => min(100, $score),
            'reasons' => array_values(array_unique($reasons)),
            'hard_block' => $hardBlock,
            'user_fixable' => $userFixable,
            'user_error' => $userError,
        ];
    }

    /**
     * Zero-reflection greeting sanitizer for all outbound customer emails.
     * Prevents phishing/spam text stuffed into "name" from EVER being echoed to third-party email addresses.
     * Preserves clean 1-3 word human names (letters/hyphens/apostrophes only, max 40 chars); collapses anything suspicious to "Valued Guest".
     */
    public static function sanitizeGreetingName(?string $name): string
    {
        $raw = trim(strip_tags((string) $name));
        if ($raw === '' || mb_strlen($raw) > 40) {
            return 'Valued Guest';
        }

        // Reject anything containing URLs, domains, email symbols, currency, digits, or punctuation/emojis
        if (preg_match('/(?:https?:\/\/|www\.|\.php\b|\.com\b|\.org\b|\.net\b|@|[$€£¥]|\d|[^\p{L}\s\'\-])/iu', $raw)) {
            return 'Valued Guest';
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', $raw) ?: []));
        if (count($parts) === 0 || count($parts) > 3) {
            return 'Valued Guest';
        }

        $cleanParts = [];
        foreach ($parts as $part) {
            if (! preg_match("/^[\p{L}'\-]{2,20}$/u", $part)) {
                return 'Valued Guest';
            }
            $cleanParts[] = mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
        }

        return implode(' ', $cleanParts);
    }

    /**
     * Detect if text contains a URL, domain, or script path.
     */
    public function containsUrlOrDomain(string $text): bool
    {
        if (preg_match('/(?:https?:\/\/|ftp:\/\/|www\.)/i', $text)) {
            return true;
        }

        return (bool) preg_match('/\b[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.(?:com|org|net|info|biz|ru|cn|mx|jp|br|xyz|top|vip|club|site|online|click|link|php)\b/i', $text);
    }

    /**
     * Detect random gibberish / high-entropy strings like "v7bgl0", "ws8rs", "x30lux", "dqz78", "5yuxrq".
     */
    public function isGibberishToken(string $text): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '' || str_contains($trimmed, ' ')) {
            return false; // Multi-word natural text handled separately
        }

        // Must be pure ASCII alphanumeric (leave non-Latin scripts like Arabic/Russian/Chinese alone!)
        if (! preg_match('/^[A-Za-z0-9]{4,14}$/', $trimmed)) {
            return false;
        }

        // Ignore obvious legitimate single-word inquiries/codes (e.g. "Hello", "Safari", "Booking", "Pricing", "FAQ", "Dubai")
        $lower = strtolower($trimmed);
        $hasLetters = (bool) preg_match('/[a-z]/', $lower);
        $hasDigits = (bool) preg_match('/[0-9]/', $lower);

        // Mixed letters + digits inside a short 4-10 char single token (not a year like 2026 or booking ref like DDT-...)
        if ($hasLetters && $hasDigits && strlen($lower) >= 4 && strlen($lower) <= 10) {
            if (! preg_match('/^(?:20\d{2}|safari\d*|dubai\d*|vip\d*|tour\d*|day\d*)$/i', $lower)) {
                return true;
            }
        }

        // Pure alpha token with 5+ chars and extremely low vowel ratio (e.g. "wsxrs", "qwrty", "zxcvb")
        if ($hasLetters && ! $hasDigits && strlen($lower) >= 5) {
            $vowels = preg_match_all('/[aeiouy]/', $lower);
            $ratio = $vowels / strlen($lower);
            if ($ratio < 0.16) {
                return true;
            }
            if (preg_match('/[bcdfghjklmnpqrstvwxz]{5,}/', $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute a normalized template fingerprint ignoring URLs, numbers, emojis, and short random codes
     * so rotating-IP botnets sending slightly mutated templates still produce the same hash.
     */
    protected function computeTemplateFingerprint(string $name, string $message): ?string
    {
        $text = mb_strtolower($name.' '.$message);
        $text = preg_replace('/https?:\/\/\S+/i', ' URL ', $text);
        $text = preg_replace('/www\.\S+/i', ' URL ', $text);
        $text = preg_replace('/\d+/', ' NUM ', $text);
        $text = preg_replace('/[^\p{L}\s]/u', ' ', $text);
        $text = trim((string) preg_replace('/\s+/', ' ', (string) $text));

        if (mb_strlen($text) < 25) {
            return null;
        }

        // Take canonical sorted unique meaningful words (length >= 4)
        $words = array_values(array_unique(array_filter(
            explode(' ', $text),
            fn ($w) => mb_strlen($w) >= 4
        )));
        sort($words);

        if (count($words) < 4) {
            return null;
        }

        return hash('sha256', implode('|', array_slice($words, 0, 25)));
    }

    /**
     * Verify Google reCAPTCHA (v3/Enterprise) AND Cloudflare Turnstile server-side whenever configured.
     */
    protected function verifyExternalChallenges(Request $request, string $clientIp, string $action): array
    {
        $reasons = [];
        $scorePenalty = 0;
        $hardReject = false;
        $details = [];

        // 1. Cloudflare Turnstile (if enabled and secret key configured)
        $turnstileEnabled = $this->settings->get('turnstile_enabled', '0') === '1';
        $turnstileSecret = trim((string) $this->settings->get('turnstile_secret_key', ''));
        if ($turnstileEnabled && $turnstileSecret !== '') {
            $cfToken = trim((string) $request->input('cf-turnstile-response', ''));
            if ($cfToken === '') {
                $reasons[] = 'turnstile_token_missing';
                $scorePenalty += 65;
                $hardReject = true;
                $details['turnstile'] = ['verified' => false, 'reason' => 'missing'];
            } else {
                try {
                    $resp = Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                        'secret' => $turnstileSecret,
                        'response' => $cfToken,
                        'remoteip' => $clientIp,
                    ]);
                    $body = $resp->json() ?: [];
                    $ok = (bool) ($body['success'] ?? false);
                    $details['turnstile'] = ['verified' => $ok, 'error_codes' => $body['error-codes'] ?? []];
                    if (! $ok) {
                        $reasons[] = 'turnstile_verification_failed';
                        $scorePenalty += 70;
                        $hardReject = true;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Cloudflare Turnstile verification network error: '.$e->getMessage());
                    $details['turnstile'] = ['verified' => null, 'network_error' => true];
                }
            }
        }

        // 2. Google reCAPTCHA v3 / Enterprise (if secret key configured)
        $rcSecret = trim((string) $this->settings->get('recaptcha_secret_key', ''));
        $rcEnforce = $this->settings->get('antispam_recaptcha_enabled', '1') === '1';
        if ($rcEnforce && $rcSecret !== '') {
            $rcToken = trim((string) $request->input('g-recaptcha-response', ''));
            $minScore = (float) $this->settings->get('antispam_recaptcha_min_score', '0.5');
            $minScore = max(0.1, min(0.95, $minScore));

            if ($rcToken === '') {
                $reasons[] = 'recaptcha_token_missing';
                $scorePenalty += 55;
                if ($this->isStrictMode()) {
                    $hardReject = true;
                }
                $details['recaptcha'] = ['verified' => false, 'reason' => 'missing'];
            } else {
                try {
                    $resp = Http::asForm()->timeout(6)->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $rcSecret,
                        'response' => $rcToken,
                        'remoteip' => $clientIp,
                    ]);
                    $body = $resp->json() ?: [];
                    $ok = (bool) ($body['success'] ?? false);
                    $rcScore = isset($body['score']) ? (float) $body['score'] : ($ok ? 0.9 : 0.0);
                    $details['recaptcha'] = [
                        'verified' => $ok,
                        'score' => $rcScore,
                        'action' => $body['action'] ?? null,
                        'error_codes' => $body['error-codes'] ?? [],
                    ];

                    if (! $ok) {
                        $reasons[] = 'recaptcha_verification_failed';
                        $scorePenalty += 65;
                        $hardReject = true;
                    } elseif ($rcScore < $minScore) {
                        $reasons[] = "recaptcha_low_score:{$rcScore}";
                        $scorePenalty += ($rcScore < 0.3 ? 65 : 40);
                        if ($rcScore < 0.3 || $this->isStrictMode()) {
                            $hardReject = true;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Google reCAPTCHA verification network error: '.$e->getMessage());
                    $details['recaptcha'] = ['verified' => null, 'network_error' => true];
                }
            }
        }

        return [
            'score_penalty' => $scorePenalty,
            'reasons' => $reasons,
            'hard_reject' => $hardReject,
            'details' => $details,
        ];
    }

    /**
     * Log blocked spam event and auto-escalate repeat abusive IPs into `ip_rules` firewall.
     */
    protected function recordAndEscalate(
        Request $request,
        string $channel,
        string $ip,
        string $email,
        string $name,
        string $subject,
        int $score,
        string $actionTaken,
        array $reasons,
    ): void {
        $escalated = $this->escalateIpIfAbusive($ip, $reasons);
        $finalAction = $escalated ? 'auto_ip_blocked' : $actionTaken;
        $this->logSpamEvent($request, $channel, $ip, $email, $name, $subject, $score, $finalAction, $reasons);
    }

    /**
     * Auto-add persistent spammer IP to `ip_rules` after threshold confirmed hits within 24h.
     */
    public function escalateIpIfAbusive(string $ip, array $reasons = []): bool
    {
        $ip = trim($ip);
        if (
            $ip === ''
            || in_array(strtolower($ip), ['0.0.0.0', '127.0.0.1', '::1', 'localhost', 'not available'], true)
            || ! filter_var($ip, FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        $autoBlockEnabled = $this->settings->get('antispam_auto_block_ip_enabled', '1') === '1';
        if (! $autoBlockEnabled) {
            return false;
        }

        $threshold = max(1, (int) $this->settings->get('antispam_auto_block_threshold', '2'));
        $counterKey = "antispam_ip_strikes:{$ip}";
        $strikes = (int) Cache::get($counterKey, 0) + 1;
        Cache::put($counterKey, $strikes, now()->addHours(24));

        if ($strikes < $threshold) {
            return false;
        }

        return $this->blockIpInFirewall($ip, 'Auto-blocked by Anti-Spam Shield ('.implode(', ', array_slice($reasons, 0, 3)).')');
    }

    /**
     * Immediately block an IP address in `ip_rules` and clear firewall cache.
     */
    public function blockIpInFirewall(string $ip, string $reason = 'Blocked spam source'): bool
    {
        $ip = trim($ip);
        if (
            $ip === ''
            || in_array(strtolower($ip), ['0.0.0.0', '127.0.0.1', '::1', 'localhost', 'not available'], true)
            || ! filter_var($ip, FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        try {
            if (! Schema::hasTable('ip_rules')) {
                return false;
            }

            $existing = IpRule::where('ip_address', $ip)->whereNull('ip_mask')->first();
            if ($existing) {
                $existing->update([
                    'mode' => 'block',
                    'status' => 'active',
                    'reason' => Str::limit($reason, 250),
                    'blocked_attempts_count' => (int) $existing->blocked_attempts_count + 1,
                    'last_activity_at' => now(),
                ]);
            } else {
                IpRule::create([
                    'ip_address' => $ip,
                    'ip_mask' => null,
                    'mode' => 'block',
                    'label' => 'Auto-Blocked Spam Bot',
                    'reason' => Str::limit($reason, 250),
                    'status' => 'active',
                    'blocked_attempts_count' => 1,
                    'last_activity_at' => now(),
                ]);
            }

            $this->ipSecurity->clearRulesCache();

            return true;
        } catch (\Throwable $e) {
            Log::warning("Failed to add auto-block IP rule for {$ip}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Persist lightweight audit record of blocked/quarantined spam activity.
     */
    public function logSpamEvent(
        Request $request,
        string $channel,
        string $ip,
        string $email,
        string $name,
        string $subject,
        int $score,
        string $actionTaken,
        array $reasons,
    ): void {
        try {
            if (! Schema::hasTable('spam_events')) {
                return;
            }

            SpamEvent::create([
                'channel' => Str::limit($channel, 40, ''),
                'ip_address' => Str::limit($ip, 64, ''),
                'email' => $email !== '' ? Str::limit($email, 255, '') : null,
                'name_snippet' => $name !== '' ? Str::limit($name, 155) : null,
                'subject_snippet' => $subject !== '' ? Str::limit($subject, 155) : null,
                'spam_score' => min(100, max(0, $score)),
                'action_taken' => Str::limit($actionTaken, 32, ''),
                'reasons' => array_values(array_unique($reasons)),
                'user_agent' => Str::limit((string) $request->userAgent(), 250),
                'request_path' => Str::limit('/'.ltrim($request->path(), '/'), 120, ''),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('Could not log SpamEvent: '.$e->getMessage());
        }
    }

    /**
     * Retrieve live 24h / 7d Anti-Spam telemetry metrics for Admin Dashboard & Settings.
     */
    public function getTelemetrySummary(): array
    {
        try {
            if (! Schema::hasTable('spam_events')) {
                return ['blocked_24h' => 0, 'blocked_7d' => 0, 'auto_ip_blocked_7d' => 0, 'quarantined_7d' => 0];
            }

            return [
                'blocked_24h' => SpamEvent::whereIn('action_taken', ['blocked', 'auto_ip_blocked'])
                    ->where('created_at', '>=', now()->subHours(24))->count(),
                'blocked_7d' => SpamEvent::whereIn('action_taken', ['blocked', 'auto_ip_blocked'])
                    ->where('created_at', '>=', now()->subDays(7))->count(),
                'auto_ip_blocked_7d' => SpamEvent::where('action_taken', 'auto_ip_blocked')
                    ->where('created_at', '>=', now()->subDays(7))->count(),
                'quarantined_7d' => SpamEvent::where('action_taken', 'quarantined')
                    ->where('created_at', '>=', now()->subDays(7))->count(),
            ];
        } catch (\Throwable) {
            return ['blocked_24h' => 0, 'blocked_7d' => 0, 'auto_ip_blocked_7d' => 0, 'quarantined_7d' => 0];
        }
    }

    public function isDisposableOrBlockedDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '') {
            return false;
        }

        if (in_array($domain, self::DISPOSABLE_DOMAINS, true)) {
            return true;
        }

        foreach ($this->getCustomBlockedDomains() as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.'.$blocked)) {
                return true;
            }
        }

        return false;
    }

    public function isContactOtpRequired(?bool $interactiveOverride = null): bool
    {
        $enforce = $interactiveOverride ?? $this->shouldEnforceInteractiveChecks();
        if (! $enforce) {
            // In unit tests, only require OTP if explicitly set in DB/config
            $raw = $this->settings->get('contact_email_otp_required');

            return $raw === '1';
        }

        return $this->settings->get('contact_email_otp_required', '1') === '1';
    }

    public function shouldEnforceInteractiveChecks(): bool
    {
        if (app()->runningUnitTests()) {
            return (bool) config('antispam.enforce_interactive_in_tests', false);
        }

        return $this->settings->get('antispam_enabled', '1') === '1';
    }

    public function isStrictMode(): bool
    {
        return $this->settings->get('antispam_mode', 'maximum') === 'maximum';
    }

    public function getMinDwellSeconds(): int
    {
        $val = (int) $this->settings->get('antispam_min_dwell_seconds', '3');

        return max(1, min(30, $val));
    }

    public function getPowDifficulty(): int
    {
        return $this->isStrictMode() ? self::DEFAULT_POW_DIFFICULTY : 2;
    }

    protected function shouldCheckEmailMx(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return $this->settings->get('antispam_check_email_mx', '1') === '1';
    }

    protected function hasValidMailDns(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === '' || str_ends_with($domain, '.local')) {
            return false;
        }

        return Cache::remember("antispam_mx:{$domain}", now()->addHours(12), function () use ($domain) {
            if (! function_exists('checkdnsrr')) {
                return true;
            }

            return @checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A');
        });
    }

    /**
     * @return list<string>
     */
    protected function getCustomBlockedKeywords(): array
    {
        $raw = (string) $this->settings->get('antispam_custom_blocked_keywords', '');
        if (trim($raw) === '') {
            return [];
        }

        $items = preg_split('/[\r\n,]+/', $raw) ?: [];

        return array_values(array_filter(array_map(fn ($s) => mb_strtolower(trim($s)), $items)));
    }

    /**
     * @return list<string>
     */
    protected function getCustomBlockedDomains(): array
    {
        $raw = (string) $this->settings->get('antispam_custom_blocked_domains', '');
        if (trim($raw) === '') {
            return [];
        }

        $items = preg_split('/[\r\n,]+/', $raw) ?: [];

        return array_values(array_filter(array_map(fn ($s) => ltrim(strtolower(trim($s)), '@'), $items)));
    }

    protected function signingSecret(): string
    {
        return (string) config('app.key', 'dunes-antispam-fallback-key').'|contact-shield-v1';
    }

    protected function resolveClientIp(?Request $request = null): string
    {
        try {
            $resolved = $this->tracker->resolveClientIp();
            if (! empty($resolved['client_ip'])) {
                return (string) $resolved['client_ip'];
            }
        } catch (\Throwable) {
        }

        return (string) ($request?->ip() ?: request()->ip() ?: '127.0.0.1');
    }
}
