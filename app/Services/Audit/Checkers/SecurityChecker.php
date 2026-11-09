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

        return $issues;
    }
}
