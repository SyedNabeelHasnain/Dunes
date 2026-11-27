<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditRun;
use App\Models\ChangeApproval;
use App\Models\ConfigAuditLog;
use App\Models\PrivacyConsent;
use App\Models\RedirectRule;
use App\Models\RequestLog;
use App\Models\SeoTemplate;
use App\Models\User;
use App\Models\WebVitalsMetric;
use App\Services\Governance\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminGovernanceController extends Controller
{
    public function __construct(
        protected ApprovalWorkflowService $approvals,
    ) {}

    public function index(Request $request)
    {
        SeoTemplate::ensureDefaultsExist();

        $pendingApprovals = ChangeApproval::with(['requester', 'reviewer'])
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        $recentApprovals = ChangeApproval::with(['requester', 'reviewer'])
            ->where('status', '!=', 'pending_review')
            ->latest()
            ->take(20)
            ->get();

        $logQuery = ConfigAuditLog::query()->latest();
        if ($request->filled('section')) {
            $logQuery->where('section', $request->string('section'));
        }
        if ($request->filled('action')) {
            $logQuery->where('action', $request->string('action'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $logQuery->where(function ($q) use ($term) {
                $q->where('setting_key', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('user_email', 'like', $term);
            });
        }
        $auditLogs = $logQuery->paginate(25)->withQueryString();

        $latestAudit = AuditRun::latest()->first();
        $stats = [
            'pending_approvals' => $pendingApprovals->count(),
            'active_templates' => SeoTemplate::where('is_active', true)->count(),
            'active_redirects' => RedirectRule::where('is_active', true)->count(),
            'recent_404s' => class_exists(RequestLog::class)
                ? RequestLog::where('entity_type', 'error_404')->where('request_timestamp', '>=', now()->subDays(7))->count()
                : 0,
            'consents_7d' => PrivacyConsent::where('consented_at', '>=', now()->subDays(7))->count(),
            'vitals_7d' => WebVitalsMetric::where('recorded_at', '>=', now()->subDays(7))->count(),
            'staff_count' => User::count(),
            'latest_health_score' => $latestAudit?->health_score,
        ];

        $sections = ConfigAuditLog::query()->select('section')->distinct()->orderBy('section')->pluck('section');

        return view('admin.governance.index', compact(
            'pendingApprovals',
            'recentApprovals',
            'auditLogs',
            'stats',
            'sections',
        ));
    }

    public function approve(Request $request, int $id)
    {
        $approval = ChangeApproval::findOrFail($id);

        try {
            $this->approvals->approveAndApply($approval, $request->user(), $request->input('review_notes'));

            return redirect()->route('admin.governance.index')->with('success', "Approved and applied change #{$approval->id}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.governance.index')->withErrors(['approval' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, int $id)
    {
        $approval = ChangeApproval::findOrFail($id);

        try {
            $this->approvals->reject($approval, $request->user(), $request->input('review_notes'));

            return redirect()->route('admin.governance.index')->with('success', "Rejected change proposal #{$approval->id}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.governance.index')->withErrors(['approval' => $e->getMessage()]);
        }
    }

    public function exportLogs(Request $request): StreamedResponse
    {
        $logQuery = ConfigAuditLog::query()->latest();
        if ($request->filled('section')) {
            $logQuery->where('section', $request->string('section'));
        }
        if ($request->filled('action')) {
            $logQuery->where('action', $request->string('action'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $logQuery->where(function ($q) use ($term) {
                $q->where('setting_key', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('user_email', 'like', $term);
            });
        }
        $logs = $logQuery->limit(2000)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, sanitize_csv_row(['ID', 'Timestamp', 'Actor', 'Role', 'Section', 'Action', 'Key', 'Old Value', 'New Value', 'Summary', 'IP']));
            foreach ($logs as $log) {
                fputcsv($out, sanitize_csv_row([
                    $log->id,
                    optional($log->created_at)->toIso8601String(),
                    $log->user_email ?: $log->user_name,
                    $log->user_role,
                    $log->section,
                    $log->action,
                    $log->setting_key,
                    $log->old_value,
                    $log->new_value,
                    $log->summary,
                    $log->ip_address,
                ]));
            }
            fclose($out);
        }, 'dunes-config-audit-logs.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
