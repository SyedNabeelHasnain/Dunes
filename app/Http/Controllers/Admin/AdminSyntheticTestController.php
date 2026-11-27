<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Language;
use App\Models\SyntheticTestRun;
use App\Models\SyntheticTestStep;
use App\Models\Tour;
use App\Models\WhatsappInquiry;
use App\Services\Governance\ConfigAuditService;
use App\Services\Mail\SmtpDiagnosticService;
use App\Services\SyntheticTesting\SyntheticJourneyRunner;
use Cron\CronExpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminSyntheticTestController extends Controller
{
    public function __construct(
        protected SyntheticJourneyRunner $runner,
        protected ConfigAuditService $auditLog,
    ) {}

    public function index(Request $request): View
    {
        $settings = $this->runner->getSettings();
        $nextScheduledAt = $this->runner->getNextScheduledRunAt($settings);
        $cronExpression = $this->runner->buildCronExpression($settings);

        $latestRun = SyntheticTestRun::with(['steps', 'triggeredByUser'])
            ->latest('id')
            ->first();

        $query = SyntheticTestRun::with('triggeredByUser')->latest('id');
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($trigger = $request->input('trigger_type')) {
            $query->where('trigger_type', $trigger);
        }
        $runs = $query->paginate(15)->withQueryString();

        // 30-day metrics
        $recentRuns = SyntheticTestRun::where('created_at', '>=', now()->subDays(30))->get();
        $totalRecent = $recentRuns->count();
        $passedRecent = $recentRuns->where('status', 'passed')->count();
        $passRate30d = $totalRecent > 0 ? round(($passedRecent / $totalRecent) * 100, 1) : null;
        $avgDurationMs = $totalRecent > 0 ? (int) round($recentRuns->avg('duration_ms')) : null;

        // Per-flow latest status
        $flowStatuses = [];
        foreach (SyntheticJourneyRunner::VALID_FLOWS as $flow) {
            $latestFlowSteps = SyntheticTestStep::where('flow', $flow)
                ->latest('synthetic_test_run_id')
                ->limit(15)
                ->get();
            if ($latestFlowSteps->isEmpty()) {
                $flowStatuses[$flow] = ['status' => 'never', 'run_id' => null, 'duration_ms' => 0, 'at' => null];
            } else {
                $targetRunId = $latestFlowSteps->first()->synthetic_test_run_id;
                $runSteps = $latestFlowSteps->where('synthetic_test_run_id', $targetRunId);
                $hasFail = $runSteps->contains('status', 'failed');
                $flowStatuses[$flow] = [
                    'status' => $hasFail ? 'failed' : 'passed',
                    'run_id' => $targetRunId,
                    'duration_ms' => (int) $runSteps->sum('duration_ms'),
                    'steps_passed' => $runSteps->where('status', 'passed')->count(),
                    'steps_total' => $runSteps->count(),
                ];
            }
        }

        $tours = Tour::where('status', 'active')
            ->whereHas('tiers', fn ($q) => $q->where('tiers.status', 'active'))
            ->orderBy('priority')
            ->get(['id', 'name', 'slug']);

        $languages = Schema::hasTable('languages')
            ? Language::getActive()
            : collect([(object) ['code' => 'en', 'name' => 'English']]);

        $defaultTestEmail = $this->runner->resolveDefaultTestRecipientEmail($settings['recipient_email'] ?? '');
        $uncleanedRunsCount = SyntheticTestRun::where('cleanup_completed', false)
            ->get()
            ->filter(fn (SyntheticTestRun $r) => $r->hasUncleanedArtifacts())
            ->count();
        $smtpHealth = app(SmtpDiagnosticService::class)->getHealthStatus();

        return view('admin.qa-simulator.index', compact(
            'settings',
            'nextScheduledAt',
            'cronExpression',
            'latestRun',
            'runs',
            'totalRecent',
            'passedRecent',
            'passRate30d',
            'avgDurationMs',
            'flowStatuses',
            'tours',
            'languages',
            'defaultTestEmail',
            'uncleanedRunsCount',
            'smtpHealth',
        ));
    }

    public function run(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'flows' => 'required|array|min:1',
            'flows.*' => 'string|in:booking,whatsapp,contact',
            'locale' => 'nullable|string|max:12',
            'device_profile' => 'nullable|string|in:desktop,mobile',
            'tour_id' => 'nullable|integer|exists:tours,id',
            'mail_mode' => 'nullable|string|in:intercept,live_smtp',
            'recipient_email' => 'nullable|email:filter|max:255',
            'include_bot_shield_check' => 'nullable|boolean',
            'include_coupon_check' => 'nullable|boolean',
            'auto_cleanup' => 'nullable|boolean',
        ]);

        $overrides = [
            'flows' => array_values(array_unique($validated['flows'])),
            'locale' => $validated['locale'] ?? 'en',
            'device_profile' => $validated['device_profile'] ?? 'desktop',
            'tour_id' => ! empty($validated['tour_id']) ? (int) $validated['tour_id'] : null,
            'mail_mode' => $validated['mail_mode'] ?? 'live_smtp',
            'recipient_email' => $validated['recipient_email'] ?? '',
            'include_bot_shield_check' => $request->boolean('include_bot_shield_check', true),
            'include_coupon_check' => $request->boolean('include_coupon_check', true),
            'auto_cleanup' => $request->boolean('auto_cleanup', false),
        ];

        $run = $this->runner->execute($overrides, 'manual', auth()->id());

        try {
            $this->auditLog->log(
                section: 'qa_simulator',
                action: 'executed',
                settingKey: "synthetic_run:{$run->id}",
                oldValue: null,
                newValue: [
                    'status' => $run->status,
                    'flows' => $run->flows_requested,
                    'locale' => $run->locale,
                    'mail_mode' => $run->mail_mode,
                    'auto_cleanup' => $run->auto_cleanup,
                ],
                entityType: SyntheticTestRun::class,
                entityId: $run->id,
                summary: "Executed E2E Visitor Simulator Run #{$run->id} ({$run->summary})",
            );
        } catch (\Throwable) {
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $run->status === 'passed',
                'run' => $run->load('steps'),
            ]);
        }

        $flashType = $run->status === 'passed' ? 'success' : 'error';

        return redirect()
            ->route('admin.qa-simulator.show', $run->id)
            ->with($flashType, "Simulator Run #{$run->id} finished [".strtoupper($run->status)."]: {$run->summary}");
    }

    public function show(int $id): View
    {
        $run = SyntheticTestRun::with(['steps', 'triggeredByUser'])->findOrFail($id);
        $stepsByFlow = $run->steps->groupBy('flow');
        $artifacts = is_array($run->artifacts) ? $run->artifacts : [];

        $createdBookings = ! empty($artifacts['booking_ids'])
            ? Booking::whereIn('id', array_map('intval', (array) $artifacts['booking_ids']))->get()
            : collect();
        $createdWhatsapps = ! empty($artifacts['whatsapp_inquiry_ids'])
            ? WhatsappInquiry::whereIn('id', array_map('intval', (array) $artifacts['whatsapp_inquiry_ids']))->get()
            : collect();
        $createdContacts = ! empty($artifacts['contact_ids'])
            ? Contact::whereIn('id', array_map('intval', (array) $artifacts['contact_ids']))->get()
            : collect();

        return view('admin.qa-simulator.show', compact(
            'run',
            'stepsByFlow',
            'createdBookings',
            'createdWhatsapps',
            'createdContacts',
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'frequency' => 'required|string|in:hourly,every_6_hours,daily,weekly,custom_cron',
            'time' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'weekday' => 'required|integer|min:0|max:6',
            'custom_cron' => 'nullable|string|max:120',
            'flows' => 'required|array|min:1',
            'flows.*' => 'string|in:booking,whatsapp,contact',
            'locale' => 'required|string|max:12',
            'device_profile' => 'required|string|in:desktop,mobile',
            'tour_id' => 'nullable|integer|exists:tours,id',
            'mail_mode' => 'required|string|in:intercept,live_smtp',
            'recipient_email' => 'nullable|email:filter|max:255',
            'include_bot_shield_check' => 'nullable|boolean',
            'include_coupon_check' => 'nullable|boolean',
            'auto_cleanup' => 'nullable|boolean',
            'alert_on_failure' => 'nullable|boolean',
            'alert_recipients' => 'nullable|string|max:500',
            'retention_days' => 'required|integer|min:7|max:365',
        ]);

        if ($validated['frequency'] === 'custom_cron') {
            $expr = trim((string) ($validated['custom_cron'] ?? ''));
            if ($expr === '' || ! CronExpression::isValidExpression($expr)) {
                return back()
                    ->withInput()
                    ->withErrors(['custom_cron' => 'Please enter a valid 5-field cron expression (e.g. "0 6 * * *").']);
            }
        }

        $old = $this->runner->getSettings();

        $payload = [
            'enabled' => $request->boolean('enabled'),
            'frequency' => $validated['frequency'],
            'time' => $validated['time'],
            'weekday' => (int) $validated['weekday'],
            'custom_cron' => trim((string) ($validated['custom_cron'] ?? '0 6 * * *')) ?: '0 6 * * *',
            'flows' => array_values(array_unique($validated['flows'])),
            'locale' => $validated['locale'],
            'device_profile' => $validated['device_profile'],
            'tour_id' => ! empty($validated['tour_id']) ? (int) $validated['tour_id'] : null,
            'mail_mode' => $validated['mail_mode'],
            'recipient_email' => $validated['recipient_email'] ?? '',
            'include_bot_shield_check' => $request->boolean('include_bot_shield_check'),
            'include_coupon_check' => $request->boolean('include_coupon_check'),
            'auto_cleanup' => $request->boolean('auto_cleanup'),
            'alert_on_failure' => $request->boolean('alert_on_failure'),
            'alert_recipients' => $validated['alert_recipients'] ?? '',
            'retention_days' => (int) $validated['retention_days'],
        ];

        $this->runner->saveSettings($payload);

        try {
            $this->auditLog->log(
                section: 'qa_simulator',
                action: 'updated',
                settingKey: 'synthetic_test_schedule',
                oldValue: $old,
                newValue: $payload,
                summary: 'Updated automated E2E QA Simulator schedule & runtime preferences',
            );
        } catch (\Throwable) {
        }

        return redirect()
            ->route('admin.qa-simulator.index')
            ->with('success', 'Automated E2E QA schedule and simulator settings saved successfully.');
    }

    public function cleanup(int $id): RedirectResponse
    {
        $run = SyntheticTestRun::findOrFail($id);
        $counts = $this->runner->cleanupRun($run);

        $totalRemoved = array_sum($counts);

        return back()->with('success', "Cleaned up {$totalRemoved} synthetic test artifact(s) for Run #{$run->id}.");
    }

    public function cleanupAll(): RedirectResponse
    {
        $result = $this->runner->cleanupAllUncleanedRuns();

        return back()->with(
            'success',
            "Cleaned up {$result['total_artifacts_removed']} dummy test artifact(s) across {$result['runs_cleaned']} uncleaned run(s)."
        );
    }

    public function export(int $id): Response
    {
        $run = SyntheticTestRun::with(['steps', 'triggeredByUser'])->findOrFail($id);
        $json = json_encode($run->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response((string) $json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="synthetic-test-run-'.$run->id.'.json"',
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $run = SyntheticTestRun::findOrFail($id);
        if ($run->hasUncleanedArtifacts()) {
            $this->runner->cleanupRun($run);
        }
        $run->delete();

        return redirect()
            ->route('admin.qa-simulator.index')
            ->with('success', "Synthetic test run #{$id} deleted.");
    }
}
