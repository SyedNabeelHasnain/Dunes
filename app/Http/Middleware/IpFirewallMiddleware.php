<?php

namespace App\Http\Middleware;

use App\Services\IpSecurityService;
use App\Services\VisitorTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpFirewallMiddleware
{
    protected IpSecurityService $ipSecurity;
    protected VisitorTrackerService $tracker;

    public function __construct(IpSecurityService $ipSecurity, VisitorTrackerService $tracker)
    {
        $this->ipSecurity = $ipSecurity;
        $this->tracker = $tracker;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve genuine client IP through multi-tier proxy/CDN detection
        $ipData = $this->tracker->resolveClientIp();
        $clientIp = $ipData['client_ip'] ?: ($request->ip() ?: '0.0.0.0');

        // Always whitelist emergency endpoints so blocked users can appeal and health checks pass
        if ($this->isWhitelistedRoute($request)) {
            return $next($request);
        }

        // Administrative failsafe: Never lock out authenticated staff from the admin panel
        if ($request->is('admin*') && auth()->check()) {
            return $next($request);
        }

        // 1. IP Blocking Check (Firewall Mode)
        if ($this->ipSecurity->isIpBlocked($clientIp)) {
            $this->ipSecurity->recordBlockedHit($clientIp);

            if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Restricted: Your IP address has been flagged and restricted by Dunes Discovery Tourism security firewall.',
                    'blocked' => true,
                    'ip' => $clientIp,
                ], 403);
            }

            return response()->view('security.blocked', [
                'clientIp' => $clientIp,
            ], 403);
        }

        // 2. IP Analytics Exclusion Check (Ignore Mode)
        if ($this->ipSecurity->isIpIgnoredFromAnalytics($clientIp)) {
            $request->attributes->set('is_analytics_ignored', true);
        }

        return $next($request);
    }

    /**
     * Determine if the requested URI is whitelisted from IP firewall restrictions.
     */
    protected function isWhitelistedRoute(Request $request): bool
    {
        return $request->is(
            'security/blocked',
            'security/unblock-request',
            'up',
            'api/v1/cron/*',
            'robots.txt',
            '.well-known/*'
        );
    }
}
