<?php

namespace App\Http\Middleware;

use App\Services\Http\InternalHttpProbeService;
use App\Services\SyntheticTesting\SyntheticVisitorSession;
use App\Services\VisitorTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    protected $tracker;

    public function __construct(VisitorTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Persist lightweight session attribution BEFORE StartSession writes the session cookie
        if (
            ! app()->runningUnitTests()
            && ! InternalHttpProbeService::isProbing()
            && $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->is('admin*', 'api*', 'assets/*', 'build/*', 'storage/*', 'images/*', 'uploads/*')
            && $request->hasSession()
        ) {
            try {
                $session = $request->session();
                if (! $session->has('tracking_session_start')) {
                    $session->put('tracking_session_start', time());
                    $session->put('tracking_landing', $request->fullUrl());
                }
                if ($request->filled('gclid')) {
                    $session->put('google_ads_gclid', mb_substr((string) $request->query('gclid'), 0, 150));
                }
                if ($request->filled('fbclid')) {
                    $session->put('meta_ads_fbclid', mb_substr((string) $request->query('fbclid'), 0, 150));
                }
                $pages = (array) $session->get('tracking_pages', []);
                $currentUri = $request->getRequestUri();
                if (empty($pages) || end($pages) !== $currentUri) {
                    $pages[] = mb_substr($currentUri, 0, 250);
                    if (count($pages) > 25) {
                        $pages = array_slice($pages, -25);
                    }
                    $session->put('tracking_pages', $pages);
                }
            } catch (\Throwable) {
            }
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (app()->runningUnitTests() || InternalHttpProbeService::isProbing() || SyntheticVisitorSession::isSimulating()) {
            return;
        }

        // Respect IP exclusion rules (e.g. administrator/internal testing IPs)
        if ($request->attributes->get('is_analytics_ignored') === true) {
            return;
        }

        // Only log genuine HTML page loads (exclude AJAX, API, feeds, sitemaps, bots, driver run-sheets, and admin panel)
        if ($request->isMethod('GET')
            && $response->getStatusCode() < 400
            && ! $request->ajax()
            && ! $request->is('admin*', 'api*', 'security/*', 'login', 'register', 'logout', 'up', 'driver/*', 'sitemap*', '*/sitemap*', 'feed.xml', '*/feed.xml', 'blog/feed.xml', '*/blog/feed.xml', 'robots.txt', 'llms*', '.well-known/*', 'badges/*', 'uploads/*', 'images/*', 'assets/*', 'build/*', 'storage/*', 'booking/*/ticket-pdf', '*/booking/*/ticket-pdf')
        ) {
            try {
                // Secondary check for ignored IP
                $ipData = $this->tracker->resolveClientIp();
                $clientIp = $ipData['client_ip'] ?: ($request->ip() ?: '');
                if (! empty($clientIp) && app(\App\Services\IpSecurityService::class)->isIpIgnoredFromAnalytics($clientIp)) {
                    return;
                }
                $ua = strtolower((string) $request->userAgent());
                foreach (['bot', 'crawler', 'spider', 'slurp', 'curl', 'wget', 'lighthouse', 'pagespeed', 'headlesschrome', 'inspect', 'uptime', 'pingdom'] as $botPat) {
                    if ($ua !== '' && str_contains($ua, $botPat)) {
                        return;
                    }
                }

                $ctx = $this->tracker->collectRequestContext('navigation');
                $this->tracker->logRequest('page_view', 0, 'navigation', $ctx);
            } catch (\Throwable $e) {
                // Fail silently so tracking errors do not crash user experience
            }
        }
    }
}
