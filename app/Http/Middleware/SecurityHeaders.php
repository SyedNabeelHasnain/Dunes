<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $cspPolicy = "upgrade-insecure-requests; frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self' https:;";
        $referrerPolicy = 'strict-origin-when-cross-origin';
        $permissionsPolicy = 'camera=(), microphone=(), geolocation=(self)';
        $hstsEnabled = true;

        try {
            if (class_exists(\App\Services\SettingsService::class)) {
                $rawSettings = app(\App\Services\SettingsService::class)->all();
                $settings = $rawSettings instanceof \Illuminate\Support\Collection ? $rawSettings->all() : (is_array($rawSettings) ? $rawSettings : []);
                if (! empty($settings['security_referrer_policy'])) {
                    $referrerPolicy = preg_replace('/[\r\n]+/', '', trim((string) $settings['security_referrer_policy']));
                }
                if (! empty($settings['security_permissions_policy'])) {
                    $permissionsPolicy = preg_replace('/[\r\n]+/', '', trim((string) $settings['security_permissions_policy']));
                }
                if (array_key_exists('security_hsts_enabled', $settings) && $settings['security_hsts_enabled'] !== '') {
                    $hstsEnabled = (string) $settings['security_hsts_enabled'] !== '0';
                }
                if (! empty($settings['security_csp_extra'])) {
                    $extra = preg_replace('/[\r\n]+/', ' ', trim((string) $settings['security_csp_extra']));
                    if ($extra !== '') {
                        $cspPolicy .= ' '.$extra;
                    }
                }
            }
        } catch (\Throwable) {
            // Keep fail-safe baseline headers when DB is unavailable during early bootstrap or isolated unit tests.
        }

        if ($response && isset($response->headers)) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Referrer-Policy', $referrerPolicy);
            $response->headers->set('Permissions-Policy', $permissionsPolicy);
            if ($hstsEnabled) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            }
            $response->headers->set('X-XSS-Protection', '0');
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');
            $response->headers->set('Content-Security-Policy', $cspPolicy);
        }

        return $response;
    }
}
