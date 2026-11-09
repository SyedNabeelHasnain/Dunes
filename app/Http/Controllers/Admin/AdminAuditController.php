<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\User;
use App\Services\Audit\WebsiteAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditController extends Controller
{
    public function __construct(
        protected WebsiteAuditService $auditService
    ) {}

    /**
     * Display the Website Optimization & Audit Dashboard.
     */
    public function index(Request $request): View
    {
        $latestRun = $this->auditService->getLatestRun();

        // If no audit has ever run, execute an initial discovery audit automatically
        if (!$latestRun) {
            $latestRun = $this->auditService->runAudit('full', auth()->id());
        }

        $query = $latestRun->issues();

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        if ($sev = $request->input('severity')) {
            $query->where('severity', $sev);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('affected_url', 'like', "%{$search}%")
                    ->orWhere('check_key', 'like', "%{$search}%");
            });
        }

        $issues = $query->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->paginate(15)
            ->withQueryString();

        $recentRuns = AuditRun::where('status', 'completed')
            ->latest('id')
            ->take(5)
            ->get();

        $admins = User::all();

        return view('admin.audit.index', compact('latestRun', 'issues', 'recentRuns', 'admins'));
    }

    /**
     * Trigger a new audit run.
     */
    public function run(Request $request): RedirectResponse
    {
        $scanType = $request->input('scan_type', 'full');
        $run = $this->auditService->runAudit($scanType, auth()->id());

        return redirect()->route('admin.audit.index')
            ->with('success', "Full website audit completed in {$run->duration_ms}ms with Overall Health Score: {$run->health_score}/100.");
    }

    /**
     * Display detailed report for a past audit run.
     */
    public function show(int $id, Request $request): View
    {
        $run = AuditRun::with('issues')->findOrFail($id);
        $issues = $run->issues()->paginate(25)->withQueryString();
        $admins = User::all();

        return view('admin.audit.show', compact('run', 'issues', 'admins'));
    }

    /**
     * Update remediation status, assignee, and notes for an issue.
     */
    public function updateIssue(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $issue = AuditIssue::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,ignored',
            'assigned_to' => 'nullable|exists:users,id',
            'remediation_notes' => 'nullable|string|max:2000',
        ]);

        $issue->status = $validated['status'];
        $issue->assigned_to = $validated['assigned_to'] ?? null;
        $issue->remediation_notes = $validated['remediation_notes'] ?? null;

        if ($validated['status'] === 'resolved' && !$issue->resolved_at) {
            $issue->resolved_at = now();
        } elseif ($validated['status'] !== 'resolved') {
            $issue->resolved_at = null;
        }

        $issue->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Issue status updated successfully.',
                'issue' => $issue,
            ]);
        }

        return back()->with('success', 'Remediation status updated.');
    }

    /**
     * Export audit issues report as CSV or JSON.
     */
    public function export(int $id, string $format = 'csv'): Response|StreamedResponse|JsonResponse
    {
        $run = AuditRun::with('issues')->findOrFail($id);

        if ($format === 'json') {
            return response()->json($run);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"dunes-audit-run-{$run->id}-report.csv\"",
        ];

        return response()->stream(function () use ($run) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Category', 'Severity', 'Title', 'Status', 'Affected URL', 'Evidence', 'Recommended Action', 'Assigned To', 'Remediation Notes']);

            foreach ($run->issues as $issue) {
                fputcsv($handle, [
                    $issue->id,
                    $issue->category,
                    $issue->severity,
                    $issue->title,
                    $issue->status,
                    $issue->affected_url,
                    $issue->evidence,
                    $issue->recommended_action,
                    $issue->assigned_to,
                    $issue->remediation_notes,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
