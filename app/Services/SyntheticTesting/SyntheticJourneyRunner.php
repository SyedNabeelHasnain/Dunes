<?php

namespace App\Services\SyntheticTesting;

use App\Mail\SyntheticTestAlertMail;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingPayment;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\EmailOtp;
use App\Models\RequestLog;
use App\Models\Setting;
use App\Models\SpamEvent;
use App\Models\SyntheticTestRun;
use App\Models\SyntheticTestStep;
use App\Models\Tour;
use App\Models\VerifiedEmail;
use App\Models\WhatsappInquiry;
use App\Services\NotificationManagementService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Automated Visitor Journey Simulator & E2E Flow Testing Engine.
 *
 * Emulates a real site visitor sequentially across Booking Checkout,
 * WhatsApp Inquiry, and Contact Us Inquiry (including Anti-Spam Bot Shield),
 * persists step-by-step telemetry and assertions, cleans up synthetic DB/disk
 * artifacts deterministically, and alerts administrators on failures.
 */
class SyntheticJourneyRunner
{
    public const VALID_FLOWS = ['booking', 'whatsapp', 'contact'];

    public const VALID_FREQUENCIES = ['hourly', 'every_6_hours', 'daily', 'weekly', 'custom_cron'];

    public function __construct(
        protected SettingsService $settings,
        protected SyntheticVisitorSession $visitorSession,
        protected NotificationManagementService $notifications,
    ) {}

    /**
     * Load normalized simulator & schedule settings from CMS Settings.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $rawFlows = $this->settings->get('synthetic_test_flows', 'booking,whatsapp,contact');
        $flows = array_values(array_intersect(
            self::VALID_FLOWS,
            array_filter(array_map('trim', explode(',', (string) $rawFlows)))
        ));
        if (empty($flows)) {
            $flows = self::VALID_FLOWS;
        }

        $frequency = (string) $this->settings->get('synthetic_test_frequency', 'daily');
        if (! in_array($frequency, self::VALID_FREQUENCIES, true)) {
            $frequency = 'daily';
        }

        $mailMode = (string) $this->settings->get('synthetic_test_mail_mode', 'intercept');
        if (! in_array($mailMode, ['intercept', 'live_smtp'], true)) {
            $mailMode = 'intercept';
        }

        $device = (string) $this->settings->get('synthetic_test_device', 'desktop');
        if (! in_array($device, ['desktop', 'mobile'], true)) {
            $device = 'desktop';
        }

        $tourId = $this->settings->get('synthetic_test_tour_id');

        return [
            'enabled' => $this->settings->get('synthetic_test_enabled', '0') === '1',
            'frequency' => $frequency,
            'time' => (string) $this->settings->get('synthetic_test_time', '06:00'),
            'weekday' => max(0, min(6, (int) $this->settings->get('synthetic_test_weekday', '1'))),
            'custom_cron' => (string) $this->settings->get('synthetic_test_custom_cron', '0 6 * * *'),
            'flows' => $flows,
            'locale' => (string) $this->settings->get('synthetic_test_locale', 'en'),
            'device_profile' => $device,
            'tour_id' => $tourId ? (int) $tourId : null,
            'mail_mode' => $mailMode,
            'recipient_email' => trim((string) $this->settings->get('synthetic_test_recipient_email', '')),
            'include_bot_shield_check' => $this->settings->get('synthetic_test_bot_shield', '1') === '1',
            'include_coupon_check' => $this->settings->get('synthetic_test_coupon_check', '1') === '1',
            'auto_cleanup' => $this->settings->get('synthetic_test_auto_cleanup', '1') === '1',
            'alert_on_failure' => $this->settings->get('synthetic_test_alert_on_failure', '1') === '1',
            'alert_recipients' => trim((string) $this->settings->get('synthetic_test_alert_recipients', '')),
            'retention_days' => max(7, min(365, (int) $this->settings->get('synthetic_test_retention_days', '30'))),
        ];
    }

    /**
     * Persist simulator schedule and runtime configuration to settings table.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSettings(array $data): void
    {
        $flows = array_values(array_intersect(
            self::VALID_FLOWS,
            (array) ($data['flows'] ?? self::VALID_FLOWS)
        ));
        if (empty($flows)) {
            $flows = self::VALID_FLOWS;
        }

        $map = [
            'synthetic_test_enabled' => ! empty($data['enabled']) ? '1' : '0',
            'synthetic_test_frequency' => (string) ($data['frequency'] ?? 'daily'),
            'synthetic_test_time' => (string) ($data['time'] ?? '06:00'),
            'synthetic_test_weekday' => (string) ((int) ($data['weekday'] ?? 1)),
            'synthetic_test_custom_cron' => trim((string) ($data['custom_cron'] ?? '0 6 * * *')),
            'synthetic_test_flows' => implode(',', $flows),
            'synthetic_test_locale' => (string) ($data['locale'] ?? 'en'),
            'synthetic_test_device' => (string) ($data['device_profile'] ?? 'desktop'),
            'synthetic_test_tour_id' => ! empty($data['tour_id']) ? (string) ((int) $data['tour_id']) : '',
            'synthetic_test_mail_mode' => (string) ($data['mail_mode'] ?? 'intercept'),
            'synthetic_test_recipient_email' => trim((string) ($data['recipient_email'] ?? '')),
            'synthetic_test_bot_shield' => ! empty($data['include_bot_shield_check']) ? '1' : '0',
            'synthetic_test_coupon_check' => ! empty($data['include_coupon_check']) ? '1' : '0',
            'synthetic_test_auto_cleanup' => ! empty($data['auto_cleanup']) ? '1' : '0',
            'synthetic_test_alert_on_failure' => ! empty($data['alert_on_failure']) ? '1' : '0',
            'synthetic_test_alert_recipients' => trim((string) ($data['alert_recipients'] ?? '')),
            'synthetic_test_retention_days' => (string) max(7, min(365, (int) ($data['retention_days'] ?? 30))),
        ];

        foreach ($map as $key => $val) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $val]
            );
        }

        $this->settings->clearCache();
    }

    /**
     * Build a 5-field cron expression (in Asia/Dubai) from the saved schedule config.
     *
     * @param  array<string, mixed>|null  $cfg
     */
    public function buildCronExpression(?array $cfg = null): string
    {
        $cfg ??= $this->getSettings();
        $frequency = $cfg['frequency'] ?? 'daily';
        $time = (string) ($cfg['time'] ?? '06:00');
        $parts = explode(':', $time);
        $hour = max(0, min(23, (int) ($parts[0] ?? 6)));
        $minute = max(0, min(59, (int) ($parts[1] ?? 0)));
        $weekday = max(0, min(6, (int) ($cfg['weekday'] ?? 1)));

        return match ($frequency) {
            'hourly' => "{$minute} * * * *",
            'every_6_hours' => "{$minute} */6 * * *",
            'weekly' => "{$minute} {$hour} * * {$weekday}",
            'custom_cron' => $this->sanitizeCronExpression((string) ($cfg['custom_cron'] ?? '0 6 * * *')),
            default => "{$minute} {$hour} * * *",
        };
    }

    public function getNextScheduledRunAt(?array $cfg = null): ?Carbon
    {
        $cfg ??= $this->getSettings();
        if (empty($cfg['enabled'])) {
            return null;
        }

        try {
            $expr = new CronExpression($this->buildCronExpression($cfg));
            $next = $expr->getNextRunDate(now('Asia/Dubai')->toDateTime());

            return Carbon::instance($next)->setTimezone('Asia/Dubai');
        } catch (\Throwable) {
            return null;
        }
    }

    public function isDueNow(?Carbon $nowDubai = null, ?array $cfg = null): bool
    {
        $cfg ??= $this->getSettings();
        if (empty($cfg['enabled'])) {
            return false;
        }

        $nowDubai = ($nowDubai ? $nowDubai->copy() : now('Asia/Dubai'))->setTimezone('Asia/Dubai');

        try {
            $expr = new CronExpression($this->buildCronExpression($cfg));
            if (! $expr->isDue($nowDubai->toDateTime())) {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        $slotKey = 'synthetic_test_slot:'.$nowDubai->format('YmdHi');

        return Cache::add($slotKey, 1, 120);
    }

    /**
     * Execute an end-to-end synthetic QA test run across selected visitor flows.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function execute(array $overrides = [], string $triggerType = 'manual', ?int $triggeredBy = null): SyntheticTestRun
    {
        $defaults = $this->getSettings();
        $cfg = array_merge($defaults, $overrides);

        $flows = array_values(array_intersect(
            self::VALID_FLOWS,
            (array) ($cfg['flows'] ?? self::VALID_FLOWS)
        ));
        if (empty($flows)) {
            $flows = self::VALID_FLOWS;
        }

        $locale = preg_replace('/[^a-zA-Z\-]/', '', (string) ($cfg['locale'] ?? 'en')) ?: 'en';
        $device = in_array($cfg['device_profile'] ?? 'desktop', ['desktop', 'mobile'], true)
            ? (string) $cfg['device_profile']
            : 'desktop';
        $mailMode = in_array($cfg['mail_mode'] ?? 'intercept', ['intercept', 'live_smtp'], true)
            ? (string) $cfg['mail_mode']
            : 'intercept';
        $autoCleanup = (bool) ($cfg['auto_cleanup'] ?? true);

        $run = SyntheticTestRun::create([
            'trigger_type' => in_array($triggerType, ['manual', 'scheduled', 'cli'], true) ? $triggerType : 'manual',
            'status' => 'running',
            'flows_requested' => $flows,
            'locale' => $locale,
            'device_profile' => $device,
            'mail_mode' => $mailMode,
            'auto_cleanup' => $autoCleanup,
            'cleanup_completed' => false,
            'total_flows' => count($flows),
            'triggered_by' => $triggeredBy,
            'started_at' => now(),
        ]);

        $artifacts = [
            'booking_ids' => [],
            'draft_ids' => [],
            'whatsapp_inquiry_ids' => [],
            'contact_ids' => [],
            'spam_event_ids' => [],
            'request_log_ids' => [],
            'verified_emails' => [],
            'email_otp_emails' => [],
            'voucher_pdf_paths' => [],
        ];

        $startedAt = microtime(true);
        $passedFlows = 0;
        $failedFlows = 0;
        $flowResults = [];

        foreach ($flows as $flow) {
            $flowPassed = false;
            try {
                $flowPassed = $this->visitorSession->runIsolated(
                    function (SyntheticVisitorSession $session) use ($run, $flow, $cfg, &$artifacts): bool {
                        return match ($flow) {
                            'booking' => $this->runBookingFlow($run, $session, $cfg, $artifacts),
                            'whatsapp' => $this->runWhatsappFlow($run, $session, $cfg, $artifacts),
                            'contact' => $this->runContactFlow($run, $session, $cfg, $artifacts),
                            default => false,
                        };
                    },
                    [
                        'locale' => $locale,
                        'device_profile' => $device,
                        'mail_mode' => $mailMode,
                        'client_ip' => $this->generateSyntheticClientIp($flow),
                    ]
                );
            } catch (\Throwable $e) {
                $this->recordStep(
                    run: $run,
                    flow: $flow,
                    order: 99,
                    key: "{$flow}_fatal",
                    title: ucfirst($flow).' Simulator Fatal Exception',
                    method: null,
                    endpoint: null,
                    httpStatus: 500,
                    status: 'failed',
                    durationMs: 0,
                    assertionSummary: null,
                    errorMessage: $e->getMessage(),
                );
                $flowPassed = false;
            }

            $flowResults[$flow] = $flowPassed;
            if ($flowPassed) {
                $passedFlows++;
            } else {
                $failedFlows++;
            }
        }

        // Persist artifact manifest before cleanup
        $run->artifacts = $artifacts;
        $run->save();

        if ($autoCleanup) {
            $this->cleanupRun($run);
        }

        $totalSteps = SyntheticTestStep::where('synthetic_test_run_id', $run->id)->count();
        $passedSteps = SyntheticTestStep::where('synthetic_test_run_id', $run->id)->where('status', 'passed')->count();
        $failedSteps = SyntheticTestStep::where('synthetic_test_run_id', $run->id)->where('status', 'failed')->count();

        $status = $failedFlows === 0
            ? 'passed'
            : ($passedFlows > 0 ? 'partial' : 'failed');

        $durationMs = max(1, (int) round((microtime(true) - $startedAt) * 1000));
        $flowSummaryParts = [];
        foreach ($flowResults as $fName => $ok) {
            $flowSummaryParts[] = ucfirst($fName).': '.($ok ? 'PASS' : 'FAIL');
        }

        $run->update([
            'status' => $status,
            'passed_flows' => $passedFlows,
            'failed_flows' => $failedFlows,
            'total_steps' => $totalSteps,
            'passed_steps' => $passedSteps,
            'failed_steps' => $failedSteps,
            'duration_ms' => $durationMs,
            'summary' => implode(' · ', $flowSummaryParts)." ({$passedSteps}/{$totalSteps} steps passed in {$durationMs} ms)",
            'finished_at' => now(),
        ]);

        $run->refresh();

        if ($status !== 'passed') {
            $this->sendFailureAlertIfEnabled($run, $cfg);
        }

        return $run;
    }

    /**
     * Flow 1: End-to-End Booking Checkout Journey (Page -> Tiers -> Coupon -> OTP -> Draft -> Checkout -> Thankyou -> Voucher/PDF).
     */
    protected function runBookingFlow(
        SyntheticTestRun $run,
        SyntheticVisitorSession $session,
        array $cfg,
        array &$artifacts
    ): bool {
        $locale = (string) ($cfg['locale'] ?? 'en');
        $prefix = ($locale !== '' && $locale !== 'en') ? "/{$locale}" : '';
        $stepOrder = 1;
        $flowOk = true;

        $tour = $this->resolveTargetTour($cfg['tour_id'] ?? null);
        if (! $tour) {
            $this->recordStep($run, 'booking', $stepOrder, 'booking_resolve_tour', 'Resolve Active Tour & Pricing Tier', null, null, null, 'failed', 0, null, 'No active tour with configured pricing tiers found.');

            return false;
        }

        // 1. Load Tour Detail Page
        $tourPath = "{$prefix}/{$tour->slug}";
        $res = $session->get($tourPath);
        $csrf = $session->csrfToken();
        $pageOk = $res['status'] === 200 && $csrf !== '' && str_contains($res['body'], $tour->slug);
        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_page_load', 'Load Tour Detail Page & Establish Session',
            'GET', $tourPath, $res['status'], $pageOk ? 'passed' : 'failed', $res['duration_ms'],
            $pageOk ? 'HTTP 200 rendered tour page with active CSRF session token.' : null,
            $pageOk ? null : "Expected HTTP 200 with tour slug & CSRF token, got HTTP {$res['status']}."
        );
        if (! $pageOk) {
            return false;
        }

        // 2. Fetch Dynamic Tour Tiers via /ajax.php?action=getTiers
        $tiersEndpoint = "/ajax.php?action=getTiers&tour_id={$tour->id}";
        $tiersRes = $session->get($tiersEndpoint, ['X-Requested-With' => 'XMLHttpRequest']);
        $tiers = (array) ($tiersRes['json']['tiers'] ?? []);
        $selectedTier = null;
        foreach ($tiers as $candidate) {
            if ((float) ($candidate['price'] ?? 0) > 0) {
                $selectedTier = $candidate;
                break;
            }
        }
        $tiersOk = $tiersRes['status'] === 200 && is_array($tiersRes['json']) && $selectedTier !== null;
        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_fetch_tiers', 'Fetch Dynamic Pricing Tiers via AJAX',
            'GET', $tiersEndpoint, $tiersRes['status'], $tiersOk ? 'passed' : 'failed', $tiersRes['duration_ms'],
            $tiersOk ? 'Resolved '.count($tiers)." tier(s); selected tier #{$selectedTier['id']} ({$selectedTier['name']}) @ AED {$selectedTier['price']}." : null,
            $tiersOk ? null : 'AJAX getTiers failed or returned zero active tiers with price > 0.',
            ['tier_count' => count($tiers), 'selected_tier_id' => $selectedTier['id'] ?? null]
        );
        if (! $tiersOk) {
            return false;
        }

        $tierId = (int) $selectedTier['id'];
        $tourDate = now('Asia/Dubai')->addDays(5)->format('Y-m-d');

        // 3. Optional Promo Coupon Validation Check
        if (! empty($cfg['include_coupon_check'])) {
            $coupon = Coupon::where('status', 'active')
                ->where('code', '!=', '')
                ->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })
                ->first();

            if ($coupon) {
                $couponRes = $session->postJson('/api/v1/coupon/validate', [
                    'code' => $coupon->code,
                    'subtotal' => (float) $selectedTier['price'] * 2,
                    'tour_id' => $tour->id,
                    'tier_id' => $tierId,
                    'adults' => 2,
                    'children' => 0,
                    'date' => $tourDate,
                ]);
                $couponOk = in_array($couponRes['status'], [200, 422], true) && is_array($couponRes['json']);
                $this->recordStep(
                    $run, 'booking', $stepOrder++, 'booking_coupon_check', 'Validate Promo Coupon API Endpoint',
                    'POST', '/api/v1/coupon/validate', $couponRes['status'], $couponOk ? 'passed' : 'failed', $couponRes['duration_ms'],
                    $couponOk ? "Coupon endpoint responded cleanly for code {$coupon->code} (valid=".((bool) ($couponRes['json']['valid'] ?? false) ? 'true' : 'false').').' : null,
                    $couponOk ? null : "Unexpected response HTTP {$couponRes['status']} from /api/v1/coupon/validate."
                );
                if (! $couponOk) {
                    $flowOk = false;
                }
            } else {
                $this->recordStep(
                    $run, 'booking', $stepOrder++, 'booking_coupon_check', 'Validate Promo Coupon API Endpoint',
                    'POST', '/api/v1/coupon/validate', null, 'skipped', 0,
                    'Skipped: no active coupon configured in database.', null
                );
            }
        }

        // 4. Email OTP Send + Verify (Using unique test email or configured live recipient)
        $tokenSlug = strtolower(Str::random(8));
        $mailMode = (string) ($cfg['mail_mode'] ?? 'intercept');
        $testEmail = ($mailMode === 'live_smtp' && ! empty($cfg['recipient_email']))
            ? strtolower(trim((string) $cfg['recipient_email']))
            : "qa.booking.{$tokenSlug}@dunesdiscoverytourism.com";

        $wasPreVerified = VerifiedEmail::where('email', $testEmail)->exists();

        $otpSend = $session->postJson('/api/v1/email/otp/send', ['email' => $testEmail]);
        $alreadyVerified = (bool) ($otpSend['json']['already_verified'] ?? false);
        $otpOk = false;
        $otpErr = null;
        $otpLatency = $otpSend['duration_ms'];

        if ($otpSend['status'] === 200 && ! empty($otpSend['json']['success'])) {
            if (! $wasPreVerified) {
                $artifacts['email_otp_emails'][] = $testEmail;
            }
            if ($alreadyVerified) {
                $otpOk = true;
            } else {
                $otpCode = $session->findLatestOtpCodeForEmail($testEmail);
                if (! $otpCode) {
                    $otpErr = 'OTP send succeeded, but 6-digit OTP code could not be captured from dispatched email.';
                } else {
                    $otpVerify = $session->postJson('/api/v1/email/otp/verify', [
                        'email' => $testEmail,
                        'otp' => $otpCode,
                    ]);
                    $otpLatency += $otpVerify['duration_ms'];
                    $otpOk = $otpVerify['status'] === 200
                        && ! empty($otpVerify['json']['success'])
                        && VerifiedEmail::where('email', $testEmail)->exists();
                    if (! $otpOk) {
                        $otpErr = $otpVerify['json']['message'] ?? "OTP verify failed with HTTP {$otpVerify['status']}.";
                    } elseif (! $wasPreVerified) {
                        $artifacts['verified_emails'][] = $testEmail;
                    }
                }
            }
        } else {
            $otpErr = $otpSend['json']['message'] ?? "OTP send failed with HTTP {$otpSend['status']}.";
        }

        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_email_otp', 'Execute Email OTP Dispatch & Verification',
            'POST', '/api/v1/email/otp/send -> /verify', $otpSend['status'], $otpOk ? 'passed' : 'failed', $otpLatency,
            $otpOk ? ($alreadyVerified ? "Email {$testEmail} already verified via status guard." : "6-digit OTP sent and verified in DB for {$testEmail}.") : null,
            $otpErr
        );
        if (! $otpOk) {
            return false;
        }

        // 5. Save Abandoned Checkout Draft
        $draftRes = $session->postJson('/booking/draft', [
            'tour_id' => $tour->id,
            'tier_id' => $tierId,
            'date' => $tourDate,
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'name' => 'Tariq Al Hashimi',
            'email' => $testEmail,
            'phone' => '+971500000101',
            'pickup_location' => 'Dubai Mall Main Entrance, Downtown Dubai',
            'payment_method' => 'cash',
            'utm_source' => 'synthetic_qa',
        ]);
        $draftId = (int) ($draftRes['json']['draft_id'] ?? 0);
        $draftToken = (string) ($draftRes['json']['draft_token'] ?? '');
        $draftOk = $draftRes['status'] === 200 && ! empty($draftRes['json']['success']) && $draftId > 0 && $draftToken !== '';
        if ($draftId > 0) {
            $artifacts['draft_ids'][] = $draftId;
        }
        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_save_draft', 'Create & Persist Checkout Lead Draft',
            'POST', '/booking/draft', $draftRes['status'], $draftOk ? 'passed' : 'failed', $draftRes['duration_ms'],
            $draftOk ? "Draft #{$draftId} created with isolated draft_token." : null,
            $draftOk ? null : ($draftRes['json']['message'] ?? "Draft save failed with HTTP {$draftRes['status']}.")
        );
        if (! $draftOk) {
            $flowOk = false;
        }

        // 6. Submit Full Cash Booking Checkout
        $addonId = DB::table('addons')
            ->join('tour_addons', 'addons.id', '=', 'tour_addons.addon_id')
            ->where('tour_addons.tour_id', $tour->id)
            ->where('addons.status', 'active')
            ->value('addons.id');

        $checkoutRes = $session->postJson('/api/v1/booking/checkout', [
            'tour_id' => $tour->id,
            'tier_id' => $tierId,
            'date' => $tourDate,
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'location' => 'Dubai Mall Main Entrance, Downtown Dubai',
            'requests' => 'Automated QA Simulator E2E verification booking.',
            'name' => 'Tariq Al Hashimi',
            'email' => $testEmail,
            'phone' => '+971500000101',
            'payment_method' => 'cash',
            'addons' => $addonId ? [(int) $addonId] : [],
            'draft_id' => $draftId ?: null,
            'draft_token' => $draftToken ?: null,
        ]);

        $bookingRef = (string) ($checkoutRes['json']['reference'] ?? '');
        $booking = $bookingRef !== '' ? Booking::where('reference', $bookingRef)->first() : null;
        if ($booking) {
            $artifacts['booking_ids'][] = $booking->id;
            if ($booking->request_log_id) {
                $artifacts['request_log_ids'][] = (int) $booking->request_log_id;
            }
            $artifacts['voucher_pdf_paths'][] = storage_path('app/vouchers/'.$booking->reference.'.pdf');
        }

        $capturedEmails = $session->getCapturedEmails();
        $bookingEmails = array_values(array_filter(
            $capturedEmails,
            fn (array $m) => $bookingRef !== '' && (str_contains($m['subject'] ?? '', $bookingRef) || str_contains($m['html_body'] ?? '', $bookingRef))
        ));

        $checkoutOk = $checkoutRes['status'] === 200
            && ! empty($checkoutRes['json']['success'])
            && $booking !== null
            && (float) $booking->total > 0;

        $draftConverted = $draftId > 0
            ? (Booking::where('id', $draftId)->value('status') === 'converted')
            : true;

        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_checkout_submit', 'Complete Authoritative Booking Checkout & Notifications',
            'POST', '/api/v1/booking/checkout', $checkoutRes['status'], ($checkoutOk && $draftConverted) ? 'passed' : 'failed', $checkoutRes['duration_ms'],
            ($checkoutOk && $draftConverted) ? "Booking #{$bookingRef} persisted (AED {$booking->total}), draft converted, and ".count($bookingEmails).' booking email(s) rendered.' : null,
            ($checkoutOk && $draftConverted) ? null : ($checkoutRes['json']['message'] ?? 'Checkout failed or draft was not transitioned to converted.'),
            [
                'reference' => $bookingRef,
                'total' => $booking ? (float) $booking->total : null,
                'emails_rendered' => array_map(fn ($e) => ['to' => $e['to'], 'subject' => $e['subject'], 'attachments' => $e['attachments']], $bookingEmails),
            ]
        );
        if (! $checkoutOk || ! $draftConverted || ! $booking) {
            return false;
        }

        // 7. Load Thank-You Confirmation Page
        $thankyouPath = "{$prefix}/thankyou?ref=".urlencode($bookingRef);
        $tyRes = $session->get($thankyouPath);
        $tyOk = $tyRes['status'] === 200 && str_contains($tyRes['body'], $bookingRef);
        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_thankyou_page', 'Verify Thank-You Confirmation Landing Page',
            'GET', $thankyouPath, $tyRes['status'], $tyOk ? 'passed' : 'failed', $tyRes['duration_ms'],
            $tyOk ? "Thank-you page rendered booking #{$bookingRef} cleanly." : null,
            $tyOk ? null : "Thank-you page returned HTTP {$tyRes['status']} or omitted #{$bookingRef}."
        );
        if (! $tyOk) {
            $flowOk = false;
        }

        // 8. Verify Digital Boarding Pass & A4 PDF Ticket Generation
        $voucherPath = "{$prefix}/booking/{$bookingRef}/voucher";
        $vRes = $session->get($voucherPath);
        $pdfPath = "{$prefix}/booking/{$bookingRef}/ticket-pdf";
        $pRes = $session->get($pdfPath);

        $pdfValid = $pRes['status'] === 200
            && str_contains(strtolower($pRes['headers']['content-type'] ?? ''), 'application/pdf')
            && str_starts_with($pRes['body'], '%PDF')
            && strlen($pRes['body']) > 500;
        $voucherOk = $vRes['status'] === 200 && str_contains($vRes['body'], $bookingRef) && $pdfValid;

        $this->recordStep(
            $run, 'booking', $stepOrder++, 'booking_voucher_and_pdf', 'Render Digital Voucher & A4 DomPDF Boarding Pass',
            'GET', $pdfPath, $pRes['status'], $voucherOk ? 'passed' : 'failed', $vRes['duration_ms'] + $pRes['duration_ms'],
            $voucherOk ? 'Digital voucher rendered and valid %PDF binary generated ('.number_format(strlen($pRes['body'])).' bytes).' : null,
            $voucherOk ? null : "Voucher HTTP {$vRes['status']} / PDF HTTP {$pRes['status']} (len=".strlen($pRes['body']).').'
        );
        if (! $voucherOk) {
            $flowOk = false;
        }

        return $flowOk;
    }

    /**
     * Flow 2: End-to-End WhatsApp Lead Journey (Storefront -> API Log -> DB/Mail -> AJAX Parity & GET 405 Guard).
     */
    protected function runWhatsappFlow(
        SyntheticTestRun $run,
        SyntheticVisitorSession $session,
        array $cfg,
        array &$artifacts
    ): bool {
        $locale = (string) ($cfg['locale'] ?? 'en');
        $prefix = ($locale !== '' && $locale !== 'en') ? "/{$locale}" : '';
        $stepOrder = 1;
        $flowOk = true;

        // 1. Storefront WhatsApp Widget Boot Check
        $homePath = $prefix !== '' ? $prefix : '/';
        $homeRes = $session->get($homePath);
        $homeOk = $homeRes['status'] === 200
            && (str_contains($homeRes['body'], 'WHATSAPP_PREFILL_MESSAGE') || str_contains($homeRes['body'], 'wa.me/'));
        $this->recordStep(
            $run, 'whatsapp', $stepOrder++, 'whatsapp_storefront_check', 'Verify Storefront WhatsApp Widget & Config Globals',
            'GET', $homePath, $homeRes['status'], $homeOk ? 'passed' : 'failed', $homeRes['duration_ms'],
            $homeOk ? 'Storefront rendered HTTP 200 with active WhatsApp CTA configuration.' : null,
            $homeOk ? null : "Storefront failed WhatsApp widget check (HTTP {$homeRes['status']})."
        );
        if (! $homeOk) {
            return false;
        }

        // 2. Submit WhatsApp Lead via /api/v1/whatsapp/log
        $waRes = $session->postJson('/api/v1/whatsapp/log', [
            'name' => 'Layla Hassan',
            'phone' => '+971500000123',
            'email' => 'qa.whatsapp.'.strtolower(Str::random(6)).'@dunesdiscoverytourism.com',
            'tour_name' => 'Evening Desert Safari Dubai',
            'page_url' => $homePath,
            'message_text' => 'Hello Dunes Discovery, QA synthetic simulator checking VIP table availability for 2 guests.',
            'utm_source' => 'synthetic_qa',
            'traffic_channel' => 'Direct',
            'journey_timeline' => [
                ['step' => 1, 'url' => $homePath, 'title' => 'Landing Page', 'duration_seconds' => 14],
            ],
        ]);

        $waId = (int) ($waRes['json']['inquiry_id'] ?? 0);
        $refCode = (string) ($waRes['json']['ref_code'] ?? '');
        $redirectUrl = (string) ($waRes['json']['redirect_url'] ?? '');
        if ($waId > 0) {
            $artifacts['whatsapp_inquiry_ids'][] = $waId;
        }

        $apiOk = $waRes['status'] === 200
            && ! empty($waRes['json']['success'])
            && $waId > 0
            && $refCode !== ''
            && str_starts_with($redirectUrl, 'https://wa.me/')
            && str_contains(urldecode($redirectUrl), $refCode);

        $this->recordStep(
            $run, 'whatsapp', $stepOrder++, 'whatsapp_api_submit', 'Log WhatsApp Inquiry & Build Tagged Redirect URL',
            'POST', '/api/v1/whatsapp/log', $waRes['status'], $apiOk ? 'passed' : 'failed', $waRes['duration_ms'],
            $apiOk ? "Lead #{$waId} logged with correlation ref #{$refCode} and valid wa.me redirect URL." : null,
            $apiOk ? null : ($waRes['json']['message'] ?? 'WhatsApp API failed to log lead or generate tagged redirect_url.')
        );
        if (! $apiOk) {
            return false;
        }

        // 3. Verify Database Persistence, RequestLog & Admin Lead Mail
        $started = microtime(true);
        $inquiry = WhatsappInquiry::find($waId);
        if ($inquiry && $inquiry->request_log_id) {
            $artifacts['request_log_ids'][] = (int) $inquiry->request_log_id;
        }
        $waEmails = array_values(array_filter(
            $session->getCapturedEmails(),
            fn (array $m) => str_contains(($m['subject'] ?? '').($m['html_body'] ?? ''), $refCode)
        ));
        $dbOk = $inquiry !== null
            && $inquiry->ref_code === $refCode
            && is_array($inquiry->journey_timeline)
            && ! empty($inquiry->journey_timeline)
            && $inquiry->request_log_id !== null;
        $dbDuration = max(1, (int) round((microtime(true) - $started) * 1000));

        $this->recordStep(
            $run, 'whatsapp', $stepOrder++, 'whatsapp_db_and_mail_verify', 'Verify WhatsApp DB Record, Timeline & Email Alert',
            null, 'whatsapp_inquiries', null, $dbOk ? 'passed' : 'failed', $dbDuration,
            $dbOk ? "DB record #{$waId} verified with ref_code={$refCode}, journey timeline, request_log #{$inquiry->request_log_id}, and ".count($waEmails).' alert email(s).' : null,
            $dbOk ? null : 'WhatsappInquiry DB verification failed (missing ref_code, journey_timeline, or request_log_id).',
            ['emails_rendered' => array_map(fn ($e) => ['to' => $e['to'], 'subject' => $e['subject']], $waEmails)]
        );
        if (! $dbOk) {
            $flowOk = false;
        }

        // 4. Legacy /ajax.php POST Parity + GET 405 Method Guard
        $ajaxPost = $session->post('/ajax.php', [
            'action' => 'log_whatsapp',
            'name' => 'Layla Hassan',
            'phone' => '+971500000124',
            'tour_name' => 'Morning Desert Safari Dubai',
            'page_url' => $homePath,
            'message_text' => 'QA legacy ajax gateway check.',
        ], ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxId = (int) ($ajaxPost['json']['inquiry_id'] ?? 0);
        if ($ajaxId > 0) {
            $artifacts['whatsapp_inquiry_ids'][] = $ajaxId;
            $ajaxRow = WhatsappInquiry::find($ajaxId);
            if ($ajaxRow && $ajaxRow->request_log_id) {
                $artifacts['request_log_ids'][] = (int) $ajaxRow->request_log_id;
            }
        }
        $ajaxGet = $session->get('/ajax.php?action=log_whatsapp');
        $parityOk = $ajaxPost['status'] === 200
            && ! empty($ajaxPost['json']['success'])
            && $ajaxGet['status'] === 405;

        $this->recordStep(
            $run, 'whatsapp', $stepOrder++, 'whatsapp_ajax_parity_and_guard', 'Verify /ajax.php POST Parity & GET 405 Guard',
            'POST/GET', '/ajax.php', $ajaxPost['status'], $parityOk ? 'passed' : 'failed', $ajaxPost['duration_ms'] + $ajaxGet['duration_ms'],
            $parityOk ? 'POST /ajax.php succeeded (200) and GET /ajax.php?action=log_whatsapp properly rejected (405).' : null,
            $parityOk ? null : "Expected POST=200 & GET=405, got POST={$ajaxPost['status']} & GET={$ajaxGet['status']}."
        );
        if (! $parityOk) {
            $flowOk = false;
        }

        return $flowOk;
    }

    /**
     * Flow 3: End-to-End Contact Us Journey (Page Load -> PoW Solve -> OTP -> Bot Shield Probe -> Submit -> DB/Mail).
     */
    protected function runContactFlow(
        SyntheticTestRun $run,
        SyntheticVisitorSession $session,
        array $cfg,
        array &$artifacts
    ): bool {
        $locale = (string) ($cfg['locale'] ?? 'en');
        $prefix = ($locale !== '' && $locale !== 'en') ? "/{$locale}" : '';
        $stepOrder = 1;
        $flowOk = true;

        // 1. Load Contact Page & Parse Security Challenge Token
        $contactPath = "{$prefix}/contact";
        $pageRes = $session->get($contactPath);
        $challengeToken = '';
        if (preg_match('/<input[^>]*name=["\']_dunes_token["\'][^>]*value=["\']([^"\']+)["\'][^>]*>/i', $pageRes['body'], $m)) {
            $challengeToken = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        } elseif (preg_match('/<input[^>]*value=["\']([^"\']+)["\'][^>]*name=["\']_dunes_token["\'][^>]*>/i', $pageRes['body'], $m)) {
            $challengeToken = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }
        $hasHoneypot = str_contains($pageRes['body'], 'name="website_url"');
        $loadOk = $pageRes['status'] === 200 && $challengeToken !== '' && $hasHoneypot;

        $this->recordStep(
            $run, 'contact', $stepOrder++, 'contact_page_load', 'Load Contact Page & Extract Signed Security Token',
            'GET', $contactPath, $pageRes['status'], $loadOk ? 'passed' : 'failed', $pageRes['duration_ms'],
            $loadOk ? 'Contact page rendered HTTP 200 with signed _dunes_token and multi-trap honeypots.' : null,
            $loadOk ? null : "Contact page HTTP {$pageRes['status']} or missing _dunes_token / honeypot markup."
        );
        if (! $loadOk) {
            return false;
        }

        // 2. Solve Cryptographic SHA-256 Micro Proof-of-Work Challenge
        $powStarted = microtime(true);
        $difficulty = $this->extractPowDifficulty($challengeToken);
        $prefixZeros = str_repeat('0', $difficulty);
        $powNonce = null;
        $iterations = 0;

        for ($i = 0; $i < 200000; $i++) {
            $iterations++;
            $candidate = (string) $i;
            if ($difficulty === 0 || str_starts_with(hash('sha256', $challengeToken.':'.$candidate), $prefixZeros)) {
                $powNonce = $candidate;
                break;
            }
        }
        $powMs = max(1, (int) round((microtime(true) - $powStarted) * 1000));
        $powOk = $powNonce !== null;

        $this->recordStep(
            $run, 'contact', $stepOrder++, 'contact_pow_solve', "Solve SHA-256 Micro-PoW Challenge (Difficulty {$difficulty})",
            null, null, null, $powOk ? 'passed' : 'failed', $powMs,
            $powOk ? "Solved PoW nonce={$powNonce} in {$iterations} hash iterations ({$powMs} ms)." : null,
            $powOk ? null : "Failed to find PoW solution within 200,000 iterations for difficulty {$difficulty}."
        );
        if (! $powOk) {
            return false;
        }

        // 3. Email OTP Verification on Contact Flow
        $tokenSlug = strtolower(Str::random(8));
        $mailMode = (string) ($cfg['mail_mode'] ?? 'intercept');
        $contactEmail = ($mailMode === 'live_smtp' && ! empty($cfg['recipient_email']))
            ? strtolower(trim((string) $cfg['recipient_email']))
            : "qa.contact.{$tokenSlug}@dunesdiscoverytourism.com";

        $wasPreVerified = VerifiedEmail::where('email', $contactEmail)->exists();
        $otpSend = $session->postJson('/api/v1/email/otp/send', ['email' => $contactEmail]);
        $alreadyVerified = (bool) ($otpSend['json']['already_verified'] ?? false);
        $otpOk = false;
        $otpErr = null;
        $otpLatency = $otpSend['duration_ms'];

        if ($otpSend['status'] === 200 && ! empty($otpSend['json']['success'])) {
            if (! $wasPreVerified) {
                $artifacts['email_otp_emails'][] = $contactEmail;
            }
            if ($alreadyVerified) {
                $otpOk = true;
            } else {
                $otpCode = $session->findLatestOtpCodeForEmail($contactEmail);
                if (! $otpCode) {
                    $otpErr = 'OTP send succeeded, but 6-digit OTP could not be captured for verification.';
                } else {
                    $otpVerify = $session->postJson('/api/v1/email/otp/verify', [
                        'email' => $contactEmail,
                        'otp' => $otpCode,
                    ]);
                    $otpLatency += $otpVerify['duration_ms'];
                    $otpOk = $otpVerify['status'] === 200
                        && ! empty($otpVerify['json']['success'])
                        && VerifiedEmail::where('email', $contactEmail)->exists();
                    if (! $otpOk) {
                        $otpErr = $otpVerify['json']['message'] ?? "OTP verification failed with HTTP {$otpVerify['status']}.";
                    } elseif (! $wasPreVerified) {
                        $artifacts['verified_emails'][] = $contactEmail;
                    }
                }
            }
        } else {
            $otpErr = $otpSend['json']['message'] ?? "OTP send failed with HTTP {$otpSend['status']}.";
        }

        $this->recordStep(
            $run, 'contact', $stepOrder++, 'contact_email_otp', 'Execute Contact Email OTP Verification',
            'POST', '/api/v1/email/otp/send -> /verify', $otpSend['status'], $otpOk ? 'passed' : 'failed', $otpLatency,
            $otpOk ? "Email {$contactEmail} verified cleanly for Contact inquiry." : null,
            $otpErr
        );
        if (! $otpOk) {
            return false;
        }

        // 4. Optional Anti-Spam Bot Shield Verification Probe (uses separate fresh token without consuming human token)
        if (! empty($cfg['include_bot_shield_check'])) {
            $botEmail = "bot.probe.{$tokenSlug}@mailinator.com";
            $botRes = $session->withBotShieldProbe(function (SyntheticVisitorSession $s) use ($botEmail) {
                return $s->postJson('/api/v1/contact/submit', [
                    'name' => 'Bot Casino Promo',
                    'email' => $botEmail,
                    'subject' => 'x30lux',
                    'message' => 'NEW PLAYER DROP no upfront deposit active promo code http://example.invalid/go-na.php?1',
                    'website_url' => 'https://bot-trap.invalid',
                ]);
            });

            $botBlockedCleanly = Contact::where('email', $botEmail)->count() === 0;
            $spamEvent = SpamEvent::where('email', $botEmail)->latest('id')->first();
            if ($spamEvent) {
                $artifacts['spam_event_ids'][] = $spamEvent->id;
            }
            $shieldOk = $botBlockedCleanly && $spamEvent !== null;

            $this->recordStep(
                $run, 'contact', $stepOrder++, 'contact_bot_shield_probe', 'Probe 6-Layer Anti-Spam Bot Shield Defense',
                'POST', '/api/v1/contact/submit', $botRes['status'], $shieldOk ? 'passed' : 'failed', $botRes['duration_ms'],
                $shieldOk ? "Bot payload stealth-blocked cleanly; SpamEvent #{$spamEvent->id} logged with score {$spamEvent->spam_score} and zero Contact DB pollution." : null,
                $shieldOk ? null : 'Anti-spam bot probe was not intercepted or failed to log SpamEvent.'
            );
            if (! $shieldOk) {
                $flowOk = false;
            }
        }

        // 5. Legitimate Human Visitor Contact Inquiry Submission
        $contactSubject = 'Desert Safari Family Inquiry '.$tokenSlug;
        $submitRes = $session->postJson('/api/v1/contact/submit', [
            'name' => 'Nadia Al Mansoori',
            'email' => $contactEmail,
            'phone' => '+971500000456',
            'subject' => $contactSubject,
            'message' => 'Hello Dunes team, our family of four will be visiting Dubai next month and would like details on hotel pickup timing and vegetarian BBQ options.',
            '_dunes_token' => $challengeToken,
            '_dunes_pow' => $powNonce,
            '_dunes_telemetry' => json_encode(['keystrokes' => 54, 'pointer' => 18, 'focus' => 5]),
            'website_url' => '',
            'company_fax' => '',
            'confirm_email_alt' => '',
            'utm_source' => 'synthetic_qa',
        ]);

        $submitOk = $submitRes['status'] === 200
            && ! empty($submitRes['json']['success'])
            && empty($submitRes['json']['blocked'])
            && empty($submitRes['json']['require_verification']);

        $this->recordStep(
            $run, 'contact', $stepOrder++, 'contact_legitimate_submit', 'Submit Valid Visitor Inquiry with PoW & Telemetry',
            'POST', '/api/v1/contact/submit', $submitRes['status'], $submitOk ? 'passed' : 'failed', $submitRes['duration_ms'],
            $submitOk ? 'Contact submission accepted cleanly without false-positive spam trigger.' : null,
            $submitOk ? null : ($submitRes['json']['message'] ?? "Contact submission failed with HTTP {$submitRes['status']}.")
        );
        if (! $submitOk) {
            return false;
        }

        // 6. Verify Contact Record, RequestLog Link & Admin/Customer Emails
        $started = microtime(true);
        $contact = Contact::where('email', $contactEmail)
            ->where('subject', $contactSubject)
            ->latest('id')
            ->first();

        if ($contact) {
            $artifacts['contact_ids'][] = $contact->id;
            if ($contact->request_log_id) {
                $artifacts['request_log_ids'][] = (int) $contact->request_log_id;
            }
        }

        $contactEmails = array_values(array_filter(
            $session->getCapturedEmails(),
            fn (array $m) => str_contains(($m['subject'] ?? '').($m['html_body'] ?? ''), $contactSubject)
                || in_array($contactEmail, (array) ($m['to'] ?? []), true)
        ));

        $dbOk = $contact !== null
            && ! $contact->is_spam
            && $contact->status === 'new'
            && $contact->is_verified
            && $contact->request_log_id !== null;
        $dbMs = max(1, (int) round((microtime(true) - $started) * 1000));

        $this->recordStep(
            $run, 'contact', $stepOrder++, 'contact_db_and_mail_verify', 'Verify Clean Contact Persistence & Acknowledgement Emails',
            null, 'contacts', null, $dbOk ? 'passed' : 'failed', $dbMs,
            $dbOk ? "Contact #{$contact->id} stored cleanly (is_spam=false, is_verified=true, request_log #{$contact->request_log_id}) and ".count($contactEmails).' email(s) rendered.' : null,
            $dbOk ? null : 'Contact record missing, unexpectedly quarantined, or unlinked from RequestLog.',
            ['emails_rendered' => array_map(fn ($e) => ['to' => $e['to'], 'subject' => $e['subject']], $contactEmails)]
        );
        if (! $dbOk) {
            $flowOk = false;
        }

        return $flowOk;
    }

    /**
     * Deterministically purge ONLY the synthetic database & disk artifacts created by a specific run.
     *
     * @return array<string, int>
     */
    public function cleanupRun(SyntheticTestRun $run): array
    {
        $artifacts = is_array($run->artifacts) ? $run->artifacts : [];
        $deleted = [
            'bookings' => 0,
            'whatsapp_inquiries' => 0,
            'contacts' => 0,
            'spam_events' => 0,
            'request_logs' => 0,
            'verified_emails' => 0,
            'email_otps' => 0,
            'pdf_files' => 0,
        ];

        $bookingIds = array_values(array_unique(array_map('intval', array_merge(
            (array) ($artifacts['booking_ids'] ?? []),
            (array) ($artifacts['draft_ids'] ?? [])
        ))));
        $whatsappIds = array_values(array_unique(array_map('intval', (array) ($artifacts['whatsapp_inquiry_ids'] ?? []))));
        $contactIds = array_values(array_unique(array_map('intval', (array) ($artifacts['contact_ids'] ?? []))));
        $spamEventIds = array_values(array_unique(array_map('intval', (array) ($artifacts['spam_event_ids'] ?? []))));
        $requestLogIds = array_values(array_unique(array_map('intval', (array) ($artifacts['request_log_ids'] ?? []))));
        $verifiedEmails = array_values(array_unique(array_filter(array_map('strval', (array) ($artifacts['verified_emails'] ?? [])))));
        $otpEmails = array_values(array_unique(array_filter(array_map('strval', array_merge(
            (array) ($artifacts['email_otp_emails'] ?? []),
            $verifiedEmails
        )))));
        $pdfPaths = array_values(array_unique(array_filter(array_map('strval', (array) ($artifacts['voucher_pdf_paths'] ?? [])))));

        DB::transaction(function () use (
            $bookingIds,
            $whatsappIds,
            $contactIds,
            $spamEventIds,
            $requestLogIds,
            $verifiedEmails,
            $otpEmails,
            &$deleted
        ): void {
            if (! empty($bookingIds)) {
                $linkedRequestLogIds = Booking::withTrashed()
                    ->whereIn('id', $bookingIds)
                    ->whereNotNull('request_log_id')
                    ->pluck('request_log_id')
                    ->all();
                $requestLogIds = array_values(array_unique(array_merge($requestLogIds, array_map('intval', $linkedRequestLogIds))));

                // Restore coupon used_count if any synthetic booking incremented it
                $usages = CouponUsage::whereIn('booking_id', $bookingIds)->get();
                foreach ($usages as $usage) {
                    if ($usage->coupon_id) {
                        Coupon::where('id', $usage->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
                    }
                    $usage->delete();
                }

                BookingAddon::whereIn('booking_id', $bookingIds)->delete();
                BookingPayment::withTrashed()->whereIn('booking_id', $bookingIds)->forceDelete();
                $deleted['bookings'] = Booking::withTrashed()->whereIn('id', $bookingIds)->forceDelete();
            }

            if (! empty($whatsappIds)) {
                $deleted['whatsapp_inquiries'] = WhatsappInquiry::whereIn('id', $whatsappIds)->delete();
            }

            if (! empty($contactIds)) {
                $deleted['contacts'] = Contact::whereIn('id', $contactIds)->delete();
            }

            if (! empty($spamEventIds)) {
                $deleted['spam_events'] = SpamEvent::whereIn('id', $spamEventIds)->delete();
            }

            if (! empty($requestLogIds)) {
                $deleted['request_logs'] = RequestLog::whereIn('id', $requestLogIds)->delete();
            }

            if (! empty($verifiedEmails)) {
                $deleted['verified_emails'] = VerifiedEmail::whereIn('email', $verifiedEmails)->delete();
            }

            if (! empty($otpEmails)) {
                $deleted['email_otps'] = EmailOtp::whereIn('email', $otpEmails)->delete();
            }
        });

        foreach ($pdfPaths as $pdfPath) {
            if ($pdfPath !== '' && is_file($pdfPath) && @unlink($pdfPath)) {
                $deleted['pdf_files']++;
            }
        }

        $run->cleanup_completed = true;
        $run->save();

        return $deleted;
    }

    public function pruneOldRuns(?int $days = null): int
    {
        $cfg = $this->getSettings();
        $retention = max(1, $days ?? (int) ($cfg['retention_days'] ?? 30));
        $cutoff = now()->subDays($retention);

        $count = 0;
        SyntheticTestRun::where('created_at', '<', $cutoff)
            ->chunkById(50, function ($runs) use (&$count): void {
                foreach ($runs as $run) {
                    if ($run->hasUncleanedArtifacts()) {
                        try {
                            $this->cleanupRun($run);
                        } catch (\Throwable) {
                        }
                    }
                    $run->delete();
                    $count++;
                }
            });

        return $count;
    }

    protected function generateSyntheticClientIp(string $flow): string
    {
        $octet = random_int(10, 245);

        return match ($flow) {
            'booking' => "198.51.100.{$octet}",
            'whatsapp' => "203.0.113.{$octet}",
            default => "192.0.2.{$octet}",
        };
    }

    protected function resolveTargetTour(?int $preferredTourId): ?Tour
    {
        if ($preferredTourId) {
            $preferred = Tour::where('id', $preferredTourId)
                ->where('status', 'active')
                ->whereHas('tiers', fn ($q) => $q->where('tiers.status', 'active'))
                ->first();
            if ($preferred) {
                return $preferred;
            }
        }

        $evening = Tour::where('slug', 'evening-desert-safari-dubai')
            ->where('status', 'active')
            ->whereHas('tiers', fn ($q) => $q->where('tiers.status', 'active'))
            ->first();
        if ($evening) {
            return $evening;
        }

        return Tour::where('status', 'active')
            ->whereHas('tiers', fn ($q) => $q->where('tiers.status', 'active'))
            ->orderBy('priority')
            ->first();
    }

    protected function extractPowDifficulty(string $token): int
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return 3;
        }
        $decoded = base64_decode(strtr($parts[0], '-_', '+/'), true);
        $payload = is_string($decoded) ? json_decode($decoded, true) : null;

        return max(0, min(5, (int) ($payload['diff'] ?? 3)));
    }

    protected function sanitizeCronExpression(string $expr): string
    {
        $expr = trim($expr);

        try {
            new CronExpression($expr);

            return $expr;
        } catch (\Throwable) {
            return '0 6 * * *';
        }
    }

    protected function recordStep(
        SyntheticTestRun $run,
        string $flow,
        int $order,
        string $key,
        string $title,
        ?string $method,
        ?string $endpoint,
        ?int $httpStatus,
        string $status,
        int $durationMs,
        ?string $assertionSummary,
        ?string $errorMessage,
        array $metadata = []
    ): SyntheticTestStep {
        return SyntheticTestStep::create([
            'synthetic_test_run_id' => $run->id,
            'flow' => $flow,
            'step_order' => $order,
            'step_key' => $key,
            'title' => Str::limit($title, 180, ''),
            'http_method' => $method,
            'endpoint' => $endpoint ? Str::limit($endpoint, 255, '') : null,
            'http_status' => $httpStatus,
            'status' => $status,
            'duration_ms' => max(0, $durationMs),
            'assertion_summary' => $assertionSummary,
            'error_message' => $errorMessage,
            'metadata' => ! empty($metadata) ? $metadata : null,
        ]);
    }

    protected function sendFailureAlertIfEnabled(SyntheticTestRun $run, array $cfg): void
    {
        if (empty($cfg['alert_on_failure'])) {
            return;
        }

        $failedSteps = $run->steps()->where('status', 'failed')->get();
        if ($failedSteps->isEmpty()) {
            return;
        }

        try {
            $rawRecipients = trim((string) ($cfg['alert_recipients'] ?? ''));
            $recipients = $rawRecipients !== ''
                ? array_values(array_filter(array_map('trim', explode(',', $rawRecipients)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)))
                : [$this->settings->getAdminEmail()];

            $primary = array_shift($recipients) ?: $this->settings->getAdminEmail();

            $mail = new SyntheticTestAlertMail($run, $failedSteps);
            $sender = $this->notifications->getSenderIdentity();
            $mail->from($sender['email'], $sender['name'])->replyTo($sender['reply_to'], $sender['name']);

            if (! empty($recipients)) {
                $mail->cc($recipients);
            }

            Mail::to($primary)->send($mail);

            $this->notifications->dispatchWebhook('synthetic_test.failed', [
                'run_id' => $run->id,
                'trigger_type' => $run->trigger_type,
                'status' => $run->status,
                'summary' => $run->summary,
                'failed_steps' => $failedSteps->map(fn (SyntheticTestStep $s) => [
                    'flow' => $s->flow,
                    'step' => $s->title,
                    'http_status' => $s->http_status,
                    'error' => $s->error_message,
                ])->all(),
            ]);

            $run->alert_sent = true;
            $run->save();
        } catch (\Throwable $e) {
            Log::warning("SyntheticTestRun #{$run->id} alert dispatch failed: ".$e->getMessage());
        }
    }
}
