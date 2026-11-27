<?php

namespace App\Services\Audit\Checkers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class SecurityChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. APP_DEBUG Exposure Check (OWASP ASVS 5.0 - Error Handling & Information Disclosure)
        $appEnv = Config::get('app.env');
        $appDebug = Config::get('app.debug');

        if ($appEnv === 'production' && $appDebug === true) {
            $issues[] = [
                'category' => 'security',
                'check_key' => 'sec_app_debug_enabled_in_production',
                'title' => 'CRITICAL: APP_DEBUG Enabled in Production Environment',
                'description' => 'Detailed stack traces, database credentials, server paths, and session tokens will be displayed to unauthorized users upon any unhandled exception (OWASP A05:2021 Security Misconfiguration).',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => "APP_ENV={$appEnv}, APP_DEBUG=true",
                'recommended_action' => 'Immediately set APP_DEBUG=false in the production .env file and execute php artisan config:cache.',
            ];
        }

        // 2. Application Encryption Key
        $appKey = Config::get('app.key');
        if (empty($appKey)) {
            $issues[] = [
                'category' => 'security',
                'check_key' => 'sec_missing_app_key',
                'title' => 'Missing Application Encryption Key',
                'description' => 'APP_KEY is not configured. Sessions, encrypted cookies, and password hashes cannot be securely signed.',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'Config app.key is empty.',
                'recommended_action' => 'Run php artisan key:generate in production environment.',
            ];
        }

        // 3. Security Headers Middleware Check
        $bootstrapApp = base_path('bootstrap/app.php');
        $hasSecurityHeaders = false;
        if (File::exists($bootstrapApp)) {
            $code = File::get($bootstrapApp);
            if (str_contains($code, 'SecurityHeaders') || str_contains($code, 'SecurityHeaders::class')) {
                $hasSecurityHeaders = true;
            }
        }

        if (!$hasSecurityHeaders) {
            $issues[] = [
                'category' => 'security',
                'check_key' => 'sec_security_headers_middleware_inactive',
                'title' => 'OWASP SecurityHeaders Middleware Not Registered Globally',
                'description' => 'HTTP response headers (Content-Security-Policy, Strict-Transport-Security, X-Frame-Options, X-Content-Type-Options) are missing, leaving users vulnerable to clickjacking and MIME-type sniffing.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'SecurityHeaders middleware not found in bootstrap/app.php global middleware stack.',
                'recommended_action' => 'Register App\Http\Middleware\SecurityHeaders::class in bootstrap/app.php.',
            ];
        }

        // 4. Live Probed HTTP Security Header Enforcement Check
        $requiredHeaders = [
            'content-security-policy' => 'Content-Security-Policy',
            'x-content-type-options' => 'X-Content-Type-Options',
            'x-frame-options' => 'X-Frame-Options',
            'referrer-policy' => 'Referrer-Policy',
        ];
        foreach (($context['probed_pages'] ?? []) as $path => $probe) {
            if ((int) ($probe['status'] ?? 500) !== 200) {
                continue;
            }
            $headers = $probe['headers'] ?? null;
            if (! is_array($headers) || $headers === []) {
                continue;
            }
            $missing = [];
            foreach ($requiredHeaders as $hdrKey => $hdrLabel) {
                if (empty($headers[$hdrKey])) {
                    $missing[] = $hdrLabel;
                }
            }
            if ($missing !== []) {
                $issues[] = [
                    'category' => 'security',
                    'check_key' => 'sec_missing_live_response_headers',
                    'title' => "Missing Live HTTP Security Headers on {$path}",
                    'description' => 'Probed HTTP response omitted one or more baseline OWASP security headers.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => 'Missing headers: '.implode(', ', $missing),
                    'recommended_action' => 'Verify SecurityHeaders middleware and Security > HTTP Security Headers policy.',
                ];
            }
        }

        // 5. Production Cron Token Advisory
        if ($appEnv === 'production') {
            $cronToken = trim((string) Config::get('services.cron.secret_token', ''));
            if ($cronToken === '' || $cronToken === 'dunes_cron_2026') {
                $issues[] = [
                    'category' => 'security',
                    'check_key' => 'sec_default_cron_token',
                    'title' => 'Production CRON_SECRET_TOKEN Using Default or Empty Value',
                    'description' => 'Web cron trigger endpoint should use a unique high-entropy CRON_SECRET_TOKEN in production .env.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url('/cron/run'),
                    'evidence' => 'CRON_SECRET_TOKEN is unset or still matches the legacy default value.',
                    'recommended_action' => 'Set a strong 32+ character CRON_SECRET_TOKEN in production .env and update scheduled curl jobs.',
                ];
            }
        }

        return $issues;
    }
}
