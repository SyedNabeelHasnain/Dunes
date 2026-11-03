<?php

namespace App\Http\Controllers;

use App\Models\IpRule;
use App\Models\UnblockRequest;
use App\Services\NotificationManagementService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityAppealController extends Controller
{
    protected VisitorTrackerService $tracker;

    public function __construct(VisitorTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Display the standalone branded blocked page.
     */
    public function showBlocked(Request $request)
    {
        $ipData = $this->tracker->resolveClientIp();
        $clientIp = $ipData['client_ip'] ?: ($request->ip() ?: '0.0.0.0');

        return response()->view('security.blocked', [
            'clientIp' => $clientIp,
        ], 403);
    }

    /**
     * Process an unblock review appeal submitted by a visitor.
     */
    public function submitUnblockRequest(Request $request)
    {
        $ipData = $this->tracker->resolveClientIp();
        $fallbackIp = $ipData['client_ip'] ?: ($request->ip() ?: '0.0.0.0');

        $validated = $request->validate([
            'ip_address' => 'nullable|string|max:45',
            'name' => 'nullable|string|max:150',
            'full_name' => 'nullable|string|max:150',
            'email' => 'required|email:filter|max:150',
            'phone' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:2000',
        ]);

        $name = trim((string) ($validated['name'] ?? ($validated['full_name'] ?? '')));
        $reason = trim((string) ($validated['reason'] ?? ($validated['explanation'] ?? '')));
        $actualClientIp = ! empty($validated['ip_address']) ? trim($validated['ip_address']) : $fallbackIp;

        if (empty($name)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please provide your full name.'], 422);
            }
            return back()->withErrors(['name' => 'Please provide your full name.']);
        }

        if (empty($reason)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please provide an explanation.'], 422);
            }
            return back()->withErrors(['reason' => 'Please provide an explanation.']);
        }

        try {
            // Find linked IP rule if exists
            $linkedRule = IpRule::where('ip_address', $actualClientIp)->first();

            $unblockRequest = UnblockRequest::create([
                'ip_rule_id' => $linkedRule?->id,
                'ip_address' => $actualClientIp,
                'name' => $name,
                'email' => trim(strtolower($validated['email'])),
                'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
                'reason' => $reason,
                'user_agent' => $request->userAgent(),
                'status' => 'pending',
            ]);

            // Notify admin through operational notification service if available
            try {
                $notificationService = app(NotificationManagementService::class);
                $notificationService->dispatchWebhook('security.unblock_requested', [
                    'id' => $unblockRequest->id,
                    'ip_address' => $actualClientIp,
                    'name' => $unblockRequest->name,
                    'email' => $unblockRequest->email,
                    'phone' => $unblockRequest->phone,
                    'reason' => $unblockRequest->reason,
                    'created_at' => now()->toIso8601String(),
                ]);
            } catch (\Throwable $notifEx) {
                Log::warning('Failed dispatching webhook for unblock request: ' . $notifEx->getMessage());
            }

            Log::info("Visitor unblock appeal submitted for IP {$actualClientIp} by {$unblockRequest->email}");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your review request has been recorded and submitted to Dunes Discovery Tourism security and dispatch operations desk. If urgent, please connect via our official WhatsApp desk.',
                ]);
            }

            return back()->with('appeal_success', 'Your review request has been recorded and submitted to Dunes Discovery Tourism security and dispatch operations desk.');
        } catch (\Throwable $e) {
            Log::error('Failed to save unblock review request: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to record unblock request at this moment. Please contact our 24/7 team on WhatsApp.',
                ], 500);
            }

            return back()->with('appeal_error', 'Unable to record unblock request at this moment. Please contact our 24/7 team on WhatsApp.');
        }
    }
}
