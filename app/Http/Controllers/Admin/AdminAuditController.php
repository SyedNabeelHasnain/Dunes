<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditIssue;
use App\Models\AuditRun;
use App\Models\User;
use App\Services\Audit\WebsiteAuditService;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditController extends Controller
{
    public function __construct(
        protected WebsiteAuditService $auditService,
        protected ConfigAuditService $auditLog,
    ) {}

    public function index(Request $request): View
    {
        $latestRun = $this->auditService->getLatestRun();
        $recentRuns = AuditRun::where('status', 'completed')->latest('id')->limit(15)->get();
        $methodology = $this->auditService->getScoringMethodology();
        $staffUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'role']);

        if (! $latestRun) {
            $issues = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            $comparison = null;
            $verificationCounts = [];

            return view('admin.audit.index', compact(
                'latestRun',
                'issues',
                'recentRuns',
                'comparison',
                'verificationCounts',
                'methodology',
                'staffUsers'
            ));
        }

        $query = AuditIssue::with('assignee')->where('audit_run_id', $latestRun->id);

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($verType = $request->input('verification_type')) {
            $query->where('verification_type', $verType);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('affected_url', 'like', "%{$search}%")
                    ->orWhere('evidence', 'like', "%{$search}%");
            });
        }

        $issues = $query
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $compareId = $request->integer('compare_with') ?: $latestRun->parent_run_id;
        $baselineRun = $compareId
            ? AuditRun::find($compareId)
            : AuditRun::where('status', 'completed')->where('id', '<', $latestRun->id)->latest('id')->first();
        $comparison = $this->auditService->compareRuns($latestRun, $baselineRun);

        $verificationCounts = AuditIssue::where('audit_run_id', $latestRun->id)
            ->selectRaw('verification_type, COUNT(*) as count')
            ->groupBy('verification_type')
            ->pluck('count', 'verification_type')
            ->toArray();

        return view('admin.audit.index', compact(
            'latestRun',
            'issues',
            'recentRuns',
            'comparison',
            'verificationCounts',
            'methodology',
            'staffUsers'
        ));
    }

    public function run(Request $request): RedirectResponse
    {
        $scanType = in_array($request->input('scan_type'), ['full', 'incremental', 'single_url'], true)
            ? (string) $request->input('scan_type')
            : 'full';
        $targetUrl = trim((string) $request->input('target_url', ''));

        $run = $this->auditService->runAudit(
            $scanType,
            auth()->id(),
            $targetUrl !== '' ? ['target_url' => $targetUrl] : []
        );

        $this->auditLog->log(
            section: 'audit',
            action: 'executed',
            settingKey: "audit_run:{$run->id}",
            oldValue: null,
            newValue: ['scan_type' => $scanType, 'score' => $run->health_score, 'issues' => $run->total_issues_count],
            entityType: AuditRun::class,
            entityId: $run->id,
            summary: "Executed {$scanType} audit (#{$run->id}): Score {$run->health_score}/100 ({$run->total_issues_count} findings)",
        );

        return redirect()->route('admin.audit.index')
            ->with('success', "Audit Run #{$run->id} ({$scanType}) completed! Overall Health Score: {$run->health_score}/100 ({$run->total_issues_count} findings in {$run->duration_ms}ms).");
    }

    public function show(int $id, Request $request): View
    {
        $run = AuditRun::findOrFail($id);
        $issues = $run->issues()
            ->with('assignee')
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $baselineRun = $run->parent_run_id
            ? AuditRun::find($run->parent_run_id)
            : AuditRun::where('status', 'completed')->where('id', '<', $run->id)->latest('id')->first();
        $comparison = $this->auditService->compareRuns($run, $baselineRun);

        return view('admin.audit.show', compact('run', 'issues', 'comparison'));
    }

    public function updateIssue(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $issue = AuditIssue::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,ignored'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'remediation_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = $issue->only(['status', 'assigned_to', 'remediation_notes']);
        $status = $validated['status'];

        $issue->update([
            'status' => $status,
            'assigned_to' => array_key_exists('assigned_to', $validated) ? $validated['assigned_to'] : $issue->assigned_to,
            'remediation_notes' => array_key_exists('remediation_notes', $validated) ? $validated['remediation_notes'] : $issue->remediation_notes,
            'remediated_by' => auth()->id(),
            'resolved_at' => $status === 'resolved' ? ($issue->resolved_at ?? now()) : null,
        ]);

        $this->auditLog->log(
            section: 'audit',
            action: 'updated',
            settingKey: "audit_issue:{$issue->id}",
            oldValue: $old,
            newValue: $issue->only(['status', 'assigned_to', 'remediation_notes']),
            entityType: AuditIssue::class,
            entityId: $issue->id,
            summary: "Updated issue #{$issue->id} ({$issue->check_key}) -> {$status}",
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'issue' => $issue->fresh('assignee'),
            ]);
        }

        return back()->with('success', "Issue #{$issue->id} updated ({$status}).");
    }

    public function export(int $id, string $format = 'csv'): Response|StreamedResponse|JsonResponse
    {
        $run = AuditRun::with('issues')->findOrFail($id);

        if ($format === 'json') {
            return response()->json($run);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"dunes-audit-run-{$run->id}-report.csv\"",
            'X-Robots-Tag' => 'noindex, nofollow',
        ];

        return response()->stream(function () use ($run) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, sanitize_csv_row(['ID', 'Category', 'Severity', 'Verification', 'Title', 'Status', 'Affected URL', 'Evidence', 'Recommended Action', 'Assigned To', 'Remediation Notes']));

            foreach ($run->issues as $issue) {
                fputcsv($handle, sanitize_csv_row([
                    $issue->id,
                    $issue->category,
                    $issue->severity,
                    $issue->verification_type,
                    $issue->title,
                    $issue->status,
                    $issue->affected_url,
                    $issue->evidence,
                    $issue->recommended_action,
                    $issue->assigned_to,
                    $issue->remediation_notes,
                ]));
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function auditPage(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'max:500'],
        ]);

        $path = '/'.ltrim((string) parse_url((string) $request->input('url'), PHP_URL_PATH), '/');
        if ($path === '') {
            $path = '/';
        }

        return response()->json($this->auditService->auditSingleUrl($path));
    }
}
