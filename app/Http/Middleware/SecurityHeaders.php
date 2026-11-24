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

        if ($response && method_exists($response, 'header')) {
            $cspPolicy = "upgrade-insecure-requests; frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self' https:;";
            $referrerPolicy = 'strict-origin-when-cross-origin';
            $permissionsPolicy = 'camera=(), microphone=(), geolocation=(self)';
            $hstsEnabled = true;

            try {
                if (class_exists(\App\Services\SettingsService::class)) {
                    $settings = app(\App\Services\SettingsService::class)->all();
                    if (! empty($settings['security_referrer_policy'])) {
                        $referrerPolicy = trim((string) $settings['security_referrer_policy']);
                    }
                    if (! empty($settings['security_permissions_policy'])) {
                        $permissionsPolicy = trim((string) $settings['security_permissions_policy']);
                    }
                    if (array_key_exists('security_hsts_enabled', $settings)) {
                        $hstsEnabled = (string) $settings['security_hsts_enabled'] !== '0';
                    }
                    if (! empty($settings['security_csp_extra'])) {
                        $cspPolicy .= ' '.trim((string) $settings['security_csp_extra']);
                    }
                }
            } catch (\Throwable) {
                // Keep fail-safe baseline headers when DB is unavailable during early bootstrap or isolated unit tests.
            }

            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('Referrer-Policy', $referrerPolicy);
            $response->header('Permissions-Policy', $permissionsPolicy);
            if ($hstsEnabled) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            }
            $response->header('X-XSS-Protection', '0');
            $response->header('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
            $response->header('Cross-Origin-Resource-Policy', 'same-site');
            $response->header('Content-Security-Policy', $cspPolicy);
        } elseif ($response && isset($response->headers)) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
            $response->headers->set('Content-Security-Policy', "upgrade-insecure-requests; frame-ancestors 'self';");
        }

        return $response;
    }
}
