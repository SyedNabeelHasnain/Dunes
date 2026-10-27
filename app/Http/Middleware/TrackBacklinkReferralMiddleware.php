<?php

namespace App\Http\Middleware;

use App\Models\Backlink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackBacklinkReferralMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect GET requests to public pages
        if ($request->isMethod('GET') && ! $request->is('admin*', 'api*', 'up', 'build/*', 'assets/*', 'images/*')) {
            try {
                $this->detectAndAttributeReferral($request);
            } catch (\Throwable $e) {
                // Fail silently so referral tracking never interferes with user experience
            }
        }

        return $next($request);
    }

    /**
     * Inspect UTM parameters and HTTP Referer to match against registered backlinks.
     */
    protected function detectAndAttributeReferral(Request $request): void
    {
        $utmSource = trim((string) $request->query('utm_source'));
        $referer = trim((string) $request->header('referer'));
        $matchedBacklink = null;

        // 1. Prioritize explicit UTM campaign source match
        if ($utmSource !== '') {
            $matchedBacklink = Backlink::where('status', 'active')
                ->where(function ($q) use ($utmSource) {
                    $q->where('utm_source', $utmSource)
                        ->orWhere('referring_domain', $utmSource);
                })
                ->first();
        }

        // 2. Fall back to HTTP Referer header if UTM not present or no match
        if (! $matchedBacklink && $referer !== '') {
            $currentHost = parse_url(url('/'), PHP_URL_HOST);
            $refererHost = parse_url($referer, PHP_URL_HOST);

            // Avoid self-referrals
            if ($refererHost && (! $currentHost || ! str_contains(strtolower($refererHost), strtolower($currentHost)))) {
                $normalizedDomain = Backlink::normalizeDomain($referer);
                if ($normalizedDomain !== '') {
                    $matchedBacklink = Backlink::where('status', 'active')
                        ->where('referring_domain', $normalizedDomain)
                        ->first();
                }
            }
        }

        // 3. If backlink is matched, record visit and persist attribution
        if ($matchedBacklink) {
            $ip = $request->ip() ?: 'unknown';
            $debounceKey = 'backlink_ref_visit_' . $matchedBacklink->id . '_' . md5($ip . '_' . $request->header('user-agent'));

            // Debounce visit increment to 1 per 30 minutes per client
            if (! Cache::has($debounceKey)) {
                Cache::put($debounceKey, true, 1800);
                $matchedBacklink->increment('referral_visits_count');
            }

            // Store in session and queue 30-day attribution cookie
            session(['attributed_backlink_id' => $matchedBacklink->id]);
            cookie()->queue(cookie('dunes_backlink_ref', (string) $matchedBacklink->id, 60 * 24 * 30, null, null, false, false));
        }
    }
}
