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
        // Execute the request first to let session start
        $response = $next($request);

        // Only log GET requests that are HTML page loads (exclude AJAX, API, files, or admin panel)
        if ($request->isMethod('GET') 
            && !$request->ajax() 
            && !$request->is('admin*') 
            && !$request->is('api*') 
            && !$request->is('login') 
            && !$request->is('register') 
            && !$request->is('logout')
        ) {
            try {
                $ctx = $this->tracker->collectRequestContext('navigation');
                $this->tracker->logRequest('page_view', 0, 'navigation', $ctx);
            } catch (\Exception $e) {
                // Fail silently so tracking errors do not crash user experience
            }
        }

        return $response;
    }
}
