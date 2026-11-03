<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpRule;
use App\Models\UnblockRequest;
use App\Services\IpSecurityService;
use App\Services\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSecurityController extends Controller
{
    protected IpSecurityService $ipSecurity;
    protected VisitorTrackerService $tracker;

    public function __construct(IpSecurityService $ipSecurity, VisitorTrackerService $tracker)
    {
        $this->ipSecurity = $ipSecurity;
        $this->tracker = $tracker;
    }

    /**
     * Display Security & IP Access Control Management Center.
     */
    public function index(Request $request)
    {
        $currentIpData = $this->tracker->resolveClientIp();
        $currentAdminIp = $currentIpData['client_ip'] ?: ($request->ip() ?: '127.0.0.1');

        // Check if current admin IP is already ignored or blocked
        $currentAdminRule = IpRule::where('ip_address', $currentAdminIp)->first();

        // 1. Query IP Rules
        $query = IpRule::with('creator')->latest();

        $tab = $request->input('tab', 'all');
        if ($tab === 'blocked') {
            $query->where('mode', 'block')->where('status', 'active');
        } elseif ($tab === 'ignored') {
            $query->where('mode', 'ignore_analytics')->where('status', 'active');
        } elseif ($tab === 'inactive') {
            $query->where('status', 'inactive');
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $rules = $query->paginate(20)->withQueryString();

        // 2. Query Unblock Appeals
        $appealStatus = $request->input('appeal_status', 'all');
        $appealQuery = UnblockRequest::with(['ipRule', 'reviewer'])->latest();

        if ($appealStatus !== 'all') {
            $appealQuery->where('status', $appealStatus);
        }

        $unblockRequests = $appealQuery->paginate(15, ['*'], 'appeals_page')->withQueryString();

        // 3. Summary Metrics
        $stats = [
            'total_rules' => IpRule::count(),
            'active_blocks' => IpRule::where('mode', 'block')->where('status', 'active')->count(),
            'active_ignored' => IpRule::where('mode', 'ignore_analytics')->where('status', 'active')->count(),
            'pending_appeals' => UnblockRequest::where('status', 'pending')->count(),
            'total_blocked_hits' => (int) IpRule::where('mode', 'block')->sum('blocked_attempts_count'),
        ];

        return view('admin.security.ip-rules', compact(
            'rules',
            'unblockRequests',
            'stats',
            'currentAdminIp',
            'currentAdminRule',
            'tab',
            'appealStatus'
        ));
    }

    /**
     * Store a new IP rule.
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'ip_mask' => 'nullable|integer|min:1|max:128',
            'mode' => 'nullable|string|in:ignore_analytics,block,monitor',
            'action_mode' => 'nullable|string|in:ignore_analytics,block,monitor',
            'label' => 'nullable|string|max:150',
            'reason' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive',
            'purge_existing_logs' => 'nullable|boolean',
        ]);

        $ip = trim($validated['ip_address']);
        $mode = $validated['mode'] ?? ($validated['action_mode'] ?? 'ignore_analytics');
        $status = $validated['status'] ?? 'active';

        // Check for duplicates
        $existing = IpRule::where('ip_address', $ip)
            ->where('ip_mask', $validated['ip_mask'] ?? null)
            ->first();

        if ($existing) {
            return back()->with('error', "A security rule already exists for IP {$ip}. You can edit or toggle the existing rule below.");
        }

        $rule = IpRule::create([
            'ip_address' => $ip,
            'ip_mask' => $validated['ip_mask'] ?? null,
            'mode' => $mode,
            'label' => $validated['label'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => $status,
            'created_by' => auth()->id(),
        ]);

        $this->ipSecurity->clearRulesCache();

        $purgeNotice = '';
        if ($request->boolean('purge_existing_logs') && $mode === 'ignore_analytics') {
            $purged = $this->ipSecurity->purgeHistoricalLogsForIp($ip);
            $purgeNotice = " and purged {$purged} historical request log entries";
        }

        return back()->with('success', "Security rule for IP {$ip} created successfully{$purgeNotice}.");
    }

    /**
     * Quick 1-click action: Ignore current connected administrator IP from analytics.
     */
    public function quickIgnoreCurrentIp(Request $request)
    {
        $ipData = $this->tracker->resolveClientIp();
        $currentIp = $ipData['client_ip'] ?: ($request->ip() ?: '127.0.0.1');

        $rule = IpRule::updateOrCreate(
            ['ip_address' => $currentIp],
            [
                'ip_mask' => null,
                'mode' => 'ignore_analytics',
                'label' => 'Administrator Station (' . (auth()->user()->name ?? 'Admin') . ')',
                'reason' => 'Automatically ignored via Quick 1-Click Action to prevent testing and QA visits from skewing analytics data.',
                'status' => 'active',
                'created_by' => auth()->id(),
            ]
        );

        $this->ipSecurity->clearRulesCache();

        $purgedCount = 0;
        if ($request->boolean('purge_logs', true)) {
            $purgedCount = $this->ipSecurity->purgeHistoricalLogsForIp($currentIp);
        }

        $msg = "Your current IP {$currentIp} is now ignored from all analytics and telemetry tracking.";
        if ($purgedCount > 0) {
            $msg .= " Also cleaned {$purgedCount} past test pageview logs from this IP.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'ip' => $currentIp,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Toggle rule mode between 'ignore_analytics' and 'block'.
     */
    public function toggleRuleMode(Request $request, int $id)
    {
        $rule = IpRule::findOrFail($id);

        $newMode = $rule->mode === 'block' ? 'ignore_analytics' : 'block';
        $rule->mode = $newMode;
        $rule->save();

        $this->ipSecurity->clearRulesCache();

        $modeLabel = $newMode === 'block' ? 'Access Blocked (Firewall Mode)' : 'Ignored from Analytics';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Rule for {$rule->ip_address} switched to {$modeLabel}.",
                'mode' => $newMode,
            ]);
        }

        return back()->with('success', "Rule for {$rule->ip_address} switched to {$modeLabel}.");
    }

    /**
     * Toggle rule active/inactive status.
     */
    public function toggleRuleStatus(Request $request, int $id)
    {
        $rule = IpRule::findOrFail($id);

        $newStatus = $rule->status === 'active' ? 'inactive' : 'active';
        $rule->status = $newStatus;
        $rule->save();

        $this->ipSecurity->clearRulesCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Rule for {$rule->ip_address} is now {$newStatus}.",
                'status' => $newStatus,
            ]);
        }

        return back()->with('success', "Rule for {$rule->ip_address} is now {$newStatus}.");
    }

    /**
     * Update an IP rule.
     */
    public function updateRule(Request $request, int $id)
    {
        $rule = IpRule::findOrFail($id);

        $validated = $request->validate([
            'mode' => 'required|string|in:ignore_analytics,block,monitor',
            'label' => 'nullable|string|max:150',
            'reason' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,inactive',
        ]);

        $rule->update($validated);
        $this->ipSecurity->clearRulesCache();

        return back()->with('success', "Security rule for IP {$rule->ip_address} updated successfully.");
    }

    /**
     * Delete an IP rule.
     */
    public function destroyRule(Request $request, int $id)
    {
        $rule = IpRule::findOrFail($id);
        $ip = $rule->ip_address;
        $rule->delete();

        $this->ipSecurity->clearRulesCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Security rule for IP {$ip} permanently deleted.",
            ]);
        }

        return back()->with('success', "Security rule for IP {$ip} permanently deleted.");
    }

    /**
     * Purge historical logs for an IP address.
     */
    public function purgeLogs(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
        ]);

        $ip = trim($validated['ip_address']);
        $count = $this->ipSecurity->purgeHistoricalLogsForIp($ip);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully purged {$count} historical request log entries for IP {$ip}.",
                'count' => $count,
            ]);
        }

        return back()->with('success', "Successfully purged {$count} historical request log entries for IP {$ip}.");
    }

    /**
     * Accept visitor unblock request (unblock IP or switch to ignored).
     */
    public function approveUnblockRequest(Request $request, int $id)
    {
        $appeal = UnblockRequest::findOrFail($id);

        $action = $request->input('target_action', 'switch_to_ignored'); // 'switch_to_ignored' or 'delete_rule'
        $adminNotes = trim((string) $request->input('admin_notes', 'Approved by administrator.'));

        $ip = $appeal->ip_address;
        $rule = IpRule::where('ip_address', $ip)->first();

        if ($rule) {
            if ($action === 'switch_to_ignored') {
                $rule->update([
                    'mode' => 'ignore_analytics',
                    'status' => 'active',
                    'reason' => 'Unblocked following guest appeal review. Retained in ignore mode to protect analytics metrics.',
                ]);
            } else {
                $rule->update([
                    'status' => 'inactive',
                    'reason' => 'Deactivated following guest appeal review.',
                ]);
            }
        }

        $appeal->update([
            'status' => 'approved_unblocked',
            'admin_notes' => $adminNotes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->ipSecurity->clearRulesCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Unblock appeal for {$ip} approved successfully. The IP is now unblocked and allowed to browse.",
            ]);
        }

        return back()->with('success', "Unblock appeal for {$ip} approved successfully. The IP is now unblocked and allowed to browse.");
    }

    /**
     * Reject visitor unblock request (keep IP blocked).
     */
    public function rejectUnblockRequest(Request $request, int $id)
    {
        $appeal = UnblockRequest::findOrFail($id);
        $adminNotes = trim((string) $request->input('admin_notes', 'Rejected by administrator: suspicious activity confirmed.'));

        $appeal->update([
            'status' => 'rejected_kept_blocked',
            'admin_notes' => $adminNotes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Unblock appeal for {$appeal->ip_address} has been rejected. The IP remains blocked.",
            ]);
        }

        return back()->with('success', "Unblock appeal for {$appeal->ip_address} has been rejected. The IP remains blocked.");
    }

    /**
     * Delete an unblock appeal record.
     */
    public function destroyUnblockRequest(Request $request, int $id)
    {
        $appeal = UnblockRequest::findOrFail($id);
        $appeal->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appeal record deleted.',
            ]);
        }

        return back()->with('success', 'Appeal record deleted.');
    }
}
