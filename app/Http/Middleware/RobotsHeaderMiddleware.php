<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces HTTP X-Robots-Tag headers on transactional, private, API, and
 * noindex-rendered responses so search engines & AI crawlers respect indexability
 * even on non-HTML assets or before DOM parsing.
 */
class RobotsHeaderMiddleware
{
    /**
     * Route prefixes (English root or localized /{locale}/...) that must never be indexed.
     *
     * @var list<string>
     */
    private const STRICT_NOINDEX_PREFIXES = [
        'api',
        'ajax.php',
        'thankyou',
        'payment-cancel',
        'checkout',
        'driver',
        'unsubscribe',
        'security/blocked',
        'security/unblock-request',
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'verify-email',
        'confirm-password',
        'profile',
        'email/track',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response || ! isset($response->headers) || $response->headers->has('X-Robots-Tag')) {
            return $response;
        }

        $path = trim($request->path(), '/');
        $normalizedPath = (string) preg_replace('#^[a-z]{2}(?:-[a-z]{2})?/#i', '', $path);

        foreach (self::STRICT_NOINDEX_PREFIXES as $prefix) {
            if (
                $path === $prefix || str_starts_with($path, $prefix.'/')
                || $normalizedPath === $prefix || str_starts_with($normalizedPath, $prefix.'/')
            ) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

                return $response;
            }
        }

        // Voucher HTML & PDF routes
        if (preg_match('#^(?:[a-z]{2}/)?booking/[^/]+/(?:voucher|ticket-pdf|voucher-pdf)$#i', $path)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        // Mirror HTML <meta name="robots" content="...noindex..."> onto HTTP X-Robots-Tag
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if (str_contains($contentType, 'text/html')) {
            $content = $response->getContent();
            if (is_string($content) && $content !== '') {
                $headSlice = substr($content, 0, 12000);
                if (
                    preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']+)["\']/i', $headSlice, $m)
                    || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']robots["\']/i', $headSlice, $m)
                ) {
                    $directive = trim($m[1]);
                    if (stripos($directive, 'noindex') !== false) {
                        $response->headers->set('X-Robots-Tag', $directive);
                    }
                }
            }
        }

        return $response;
    }
}
