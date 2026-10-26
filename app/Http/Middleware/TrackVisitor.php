<?php

namespace App\Http\Middleware;

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
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // Only log genuine HTML page loads (exclude AJAX, API, feeds, sitemaps, bots, driver run-sheets, and admin panel)
        if ($request->isMethod('GET')
            && $response->getStatusCode() < 400
            && ! $request->ajax()
            && ! $request->is('admin*', 'api*', 'login', 'register', 'logout', 'up', 'driver/*', 'sitemap*', '*/sitemap*', 'feed.xml', '*/feed.xml', 'blog/feed.xml', '*/blog/feed.xml', 'robots.txt', 'llms*', '.well-known/*', 'badges/*', 'uploads/*', 'images/*', 'assets/*', 'build/*', 'storage/*', 'booking/*/ticket-pdf', '*/booking/*/ticket-pdf')
        ) {
            try {
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
