<?php

namespace App\Http\Middleware;

use App\Services\RedirectManagerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicRedirectMiddleware
{
    public function __construct(
        protected RedirectManagerService $redirectService
    ) {}

    /**
     * Handle an incoming request and evaluate dynamic CMS redirects.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only evaluate GET and HEAD requests
        if (!$request->isMethodSafe()) {
            return $next($request);
        }

        $path = $request->path();

        // Skip internal/admin/asset paths
        if (
            str_starts_with($path, 'admin') ||
            str_starts_with($path, 'api') ||
            str_starts_with($path, 'build') ||
            str_starts_with($path, 'images') ||
            str_starts_with($path, 'videos') ||
            str_starts_with($path, 'storage')
        ) {
            return $next($request);
        }

        $resolved = $this->redirectService->resolve('/' . $path);

        if ($resolved) {
            $target = $resolved['target'];
            $status = $resolved['status'];
            $queryString = $request->getQueryString();

            if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
                $finalUrl = $target . ($queryString ? (str_contains($target, '?') ? '&' : '?') . $queryString : '');
            } else {
                $finalUrl = url($target) . ($queryString ? '?' . $queryString : '');
            }

            return redirect($finalUrl, $status);
        }

        return $next($request);
    }
}
