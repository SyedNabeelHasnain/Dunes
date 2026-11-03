<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HeatmapService;
use App\Services\IpSecurityService;
use App\Services\SessionRecordingService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionTelemetryController extends Controller
{
    protected SessionRecordingService $recordingService;
    protected HeatmapService $heatmapService;
    protected VisitorTrackerService $tracker;
    protected IpSecurityService $ipSecurity;

    public function __construct(
        SessionRecordingService $recordingService,
        HeatmapService $heatmapService,
        VisitorTrackerService $tracker,
        IpSecurityService $ipSecurity
    ) {
        $this->recordingService = $recordingService;
        $this->heatmapService = $heatmapService;
        $this->tracker = $tracker;
        $this->ipSecurity = $ipSecurity;
    }

    /**
     * Ingest batch of rrweb session recording events.
     */
    public function ingestRecordingBatch(Request $request): JsonResponse
    {
        try {
            if (! $this->recordingService->isEnabled()) {
                return response()->json(['skipped' => 'recordings_disabled'], 200);
            }

            $ipInfo = $this->tracker->resolveClientIp($request->all());
            $clientIp = $ipInfo['client_ip'];

            // Strict Exclusion: Firewall and Ignored IPs
            if ($this->ipSecurity->isIpBlocked($clientIp) || $this->ipSecurity->isIpIgnoredFromAnalytics($clientIp)) {
                return response()->json(['skipped' => 'ip_excluded'], 200);
            }

            // Strict Exclusion: Bot Detection
            $uaData = $this->tracker->parseUserAgent();
            $isBot = ($this->tracker->classifyBot($uaData) === 'Likely Bot') || $request->boolean('is_webdriver');
            if ($isBot) {
                return response()->json(['skipped' => 'bot_excluded'], 200);
            }

            // Decode incoming events payload (handles json string or array, payload could come via beacon or raw body)
            $rawEvents = $request->input('events');
            if (is_string($rawEvents)) {
                $events = json_decode($rawEvents, true) ?: [];
            } elseif (is_array($rawEvents)) {
                $events = $rawEvents;
            } else {
                // Check raw request content
                $content = $request->getContent();
                if (! empty($content)) {
                    $parsed = json_decode($content, true);
                    $events = $parsed['events'] ?? [];
                } else {
                    $events = [];
                }
            }

            if (empty($events)) {
                return response()->json(['error' => 'empty_events'], 422);
            }

            $sessionId = $request->input('session_id') ?: ($request->cookie('dunes_session') ?: session()->getId());
            $geo = $this->tracker->ipLookup($clientIp);

            $meta = [
                'client_ip' => $clientIp,
                'country' => $geo['country'] ?? null,
                'city' => $geo['city'] ?? null,
                'device_type' => $uaData['device_type'] ?? 'desktop',
                'browser_name' => $uaData['browser_name'] ?? null,
                'os_name' => $uaData['os_name'] ?? null,
                'current_page' => $request->input('current_page', $request->header('referer')),
                'is_final' => $request->boolean('is_final'),
                'is_bot' => false,
            ];

            $recording = $this->recordingService->ingestBatch($sessionId, $events, $meta);

            return response()->json([
                'success' => (bool) $recording,
                'session_id' => $recording?->session_id,
                'events_count' => $recording?->events_count,
                'duration' => $recording?->duration_seconds,
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recording batch ingestion error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Ingest click coordinates for heatmaps.
     */
    public function recordHeatmapClick(Request $request): JsonResponse
    {
        try {
            if (! $this->heatmapService->isEnabled()) {
                return response()->json(['skipped' => 'heatmaps_disabled'], 200);
            }

            $ipInfo = $this->tracker->resolveClientIp($request->all());
            $clientIp = $ipInfo['client_ip'];

            if ($this->ipSecurity->isIpBlocked($clientIp) || $this->ipSecurity->isIpIgnoredFromAnalytics($clientIp)) {
                return response()->json(['skipped' => 'ip_excluded'], 200);
            }

            $uaData = $this->tracker->parseUserAgent();
            if ($this->tracker->classifyBot($uaData) === 'Likely Bot') {
                return response()->json(['skipped' => 'bot_excluded'], 200);
            }

            $data = [
                'session_id' => $request->input('session_id'),
                'page_path' => $request->input('page_path', '/'),
                'device_type' => $uaData['device_type'] ?? 'desktop',
                'x_ratio' => $request->input('x_ratio'),
                'y_ratio' => $request->input('y_ratio'),
                'click_x' => $request->input('click_x'),
                'click_y' => $request->input('click_y'),
                'viewport_width' => $request->input('viewport_width'),
                'viewport_height' => $request->input('viewport_height'),
                'target_tag' => $request->input('target_tag'),
                'target_text' => $request->input('target_text'),
            ];

            $saved = $this->heatmapService->recordClick($data, $clientIp);

            return response()->json(['success' => $saved], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Heatmap click ingestion error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Ingest max scroll depth for heatmaps.
     */
    public function recordHeatmapScroll(Request $request): JsonResponse
    {
        try {
            if (! $this->heatmapService->isEnabled()) {
                return response()->json(['skipped' => 'heatmaps_disabled'], 200);
            }

            $ipInfo = $this->tracker->resolveClientIp($request->all());
            $clientIp = $ipInfo['client_ip'];

            if ($this->ipSecurity->isIpBlocked($clientIp) || $this->ipSecurity->isIpIgnoredFromAnalytics($clientIp)) {
                return response()->json(['skipped' => 'ip_excluded'], 200);
            }

            $uaData = $this->tracker->parseUserAgent();
            if ($this->tracker->classifyBot($uaData) === 'Likely Bot') {
                return response()->json(['skipped' => 'bot_excluded'], 200);
            }

            $data = [
                'session_id' => $request->input('session_id'),
                'page_path' => $request->input('page_path', '/'),
                'device_type' => $uaData['device_type'] ?? 'desktop',
                'max_scroll_percent' => $request->input('max_scroll_percent'),
                'viewport_height' => $request->input('viewport_height'),
                'page_height' => $request->input('page_height'),
            ];

            $saved = $this->heatmapService->recordScroll($data, $clientIp);

            return response()->json(['success' => $saved], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Heatmap scroll ingestion error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 200);
        }
    }
}
