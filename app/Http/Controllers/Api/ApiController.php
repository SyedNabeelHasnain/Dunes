<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected $tracker;

    public function __construct(VisitorTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Resolve GeoIP details for current visitor.
     */
    public function geoip(): JsonResponse
    {
        $ipInfo = $this->tracker->resolveClientIp();
        $ip = $ipInfo['client_ip'];
        $details = $this->tracker->ipLookup($ip);

        if (!$details) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unable to resolve IP location',
                'ip' => $ip
            ], 404);
        }

        return response()->json(array_merge([
            'status' => 'success',
            'ip' => $ip
        ], $details));
    }
}
