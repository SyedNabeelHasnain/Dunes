<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedirectRule;
use App\Models\RequestLog;
use App\Models\User;
use App\Services\Governance\ConfigAuditService;
use App\Services\Http\InternalHttpProbeService;
use App\Services\RedirectManagerService;
use App\Services\Security\SafeUrlValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRedirectController extends Controller
{
    public function __construct(
        protected RedirectManagerService $redirectService,
        protected ConfigAuditService $audit,
        protected InternalHttpProbeService $probeService,
    ) {}

    /**
     * Display list of dynamic redirect rules and unresolved 404 monitor.
     */
    public function index(Request $request): View
    {
        $query = RedirectRule::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('source_path', 'like', "%{$search}%")
                    ->orWhere('target_path', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($code = $request->input('status_code')) {
            $query->where('status_code', (int) $code);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->input('status') === '1');
        }

        $rules = $query->orderBy('hit_count', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $creators = User::whereIn('id', $rules->pluck('created_by')->filter()->unique())->pluck('name', 'id');

        $stats = [
            'total' => RedirectRule::count(),
            'active' => RedirectRule::where('is_active', true)->count(),
            'total_hits' => RedirectRule::sum('hit_count'),
            'permanent_count' => RedirectRule::whereIn('status_code', [301, 308])->count(),
            'temporary_count' => RedirectRule::whereIn('status_code', [302, 307])->count(),
            'gone_count' => RedirectRule::where('status_code', 410)->count(),
        ];

        $top404s = collect();
        try {
            $activeSources = RedirectRule::where('is_active', true)->pluck('source_path')
                ->map(fn ($p) => RedirectRule::normalizePath((string) $p))
                ->flip();

            $top404s = RequestLog::where('entity_type', 'error_404')
                ->whereNotNull('request_uri')
                ->select('request_uri', DB::raw('COUNT(*) as hits'), DB::raw('MAX(request_timestamp) as last_seen_at'))
                ->groupBy('request_uri')
                ->orderByDesc('hits')
                ->limit(30)
                ->get()
                ->filter(function ($row) use ($activeSources) {
                    $norm = RedirectRule::normalizePath((string) parse_url((string) $row->request_uri, PHP_URL_PATH));

                    return ! isset($activeSources[$norm]) && $norm !== '/';
                })
                ->take(12)
                ->values();
        } catch (\Throwable) {
            $top404s = collect();
        }

        return view('admin.settings.redirects', compact('rules', 'stats', 'creators', 'top404s'));
    }

    /**
     * Create a new redirect rule (or 410 Gone) with loop & chain detection.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'source_path' => 'required|string|max:500',
            'target_path' => 'required_unless:status_code,410|nullable|string|max:500',
            'status_code' => 'required|integer|in:301,302,307,308,410',
            'notes' => 'nullable|string|max:1000',
            'source_type' => 'nullable|string|max:32',
        ]);

        $statusCode = (int) $request->input('status_code');
        $targetPath = (string) $request->input('target_path', '');

        if ($statusCode !== 410 && (str_starts_with($targetPath, 'http://') || str_starts_with($targetPath, 'https://'))) {
            if (! filter_var($targetPath, FILTER_VALIDATE_URL)) {
                return back()->withInput()->with('error', 'External target URL is malformed.');
            }
        }

        $loopCheck = $this->redirectService->detectLoopOrChain(
            $request->input('source_path'),
            $targetPath,
            null,
            $statusCode
        );

        if ($loopCheck['is_loop']) {
            return back()->withInput()->with('error', $loopCheck['message']);
        }

        $rule = $this->redirectService->saveRule([
            'source_path' => $request->input('source_path'),
            'target_path' => $targetPath,
            'status_code' => $statusCode,
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active', true),
            'source_type' => $request->input('source_type', 'manual'),
            'created_by' => auth()->id(),
        ]);

        $this->audit->log(
            section: 'redirects',
            action: 'created',
            settingKey: $rule->source_path,
            oldValue: null,
            newValue: ['target_path' => $rule->target_path, 'status_code' => $rule->status_code, 'is_active' => $rule->is_active],
            entityType: RedirectRule::class,
            entityId: $rule->id,
            summary: "Created HTTP {$rule->status_code} rule for {$rule->source_path}",
        );

        $msg = "Redirect rule created successfully (HTTP {$rule->status_code}).";
        if ($loopCheck['is_chain']) {
            $msg .= ' ' . $loopCheck['message'];
        }

        return redirect()->route('admin.settings.redirects.index')->with('success', $msg);
    }

    /**
     * Update an existing redirect rule.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'source_path' => 'required|string|max:500',
            'target_path' => 'required_unless:status_code,410|nullable|string|max:500',
            'status_code' => 'required|integer|in:301,302,307,308,410',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $existing = RedirectRule::findOrFail($id);
        $statusCode = (int) $request->input('status_code');
        $targetPath = (string) $request->input('target_path', '');

        $loopCheck = $this->redirectService->detectLoopOrChain(
            $request->input('source_path'),
            $targetPath,
            $id,
            $statusCode
        );

        if ($loopCheck['is_loop']) {
            return back()->withInput()->with('error', $loopCheck['message']);
        }

        $oldSnapshot = ['source_path' => $existing->source_path, 'target_path' => $existing->target_path, 'status_code' => $existing->status_code, 'is_active' => $existing->is_active];

        $updated = $this->redirectService->saveRule([
            'source_path' => $request->input('source_path'),
            'target_path' => $targetPath,
            'status_code' => $statusCode,
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active'),
        ], $id);

        $this->audit->log(
            section: 'redirects',
            action: 'updated',
            settingKey: $updated->source_path,
            oldValue: $oldSnapshot,
            newValue: ['source_path' => $updated->source_path, 'target_path' => $updated->target_path, 'status_code' => $updated->status_code, 'is_active' => $updated->is_active],
            entityType: RedirectRule::class,
            entityId: $updated->id,
            summary: "Updated HTTP {$updated->status_code} rule for {$updated->source_path}",
        );

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect rule updated successfully.');
    }

    /**
     * Delete a redirect rule.
     */
    public function destroy(int $id): RedirectResponse
    {
        $existing = RedirectRule::findOrFail($id);
        $snapshot = ['source_path' => $existing->source_path, 'target_path' => $existing->target_path, 'status_code' => $existing->status_code];

        $this->redirectService->deleteRule($id);

        $this->audit->log(
            section: 'redirects',
            action: 'deleted',
            settingKey: $snapshot['source_path'],
            oldValue: $snapshot,
            newValue: null,
            entityType: RedirectRule::class,
            entityId: $id,
            summary: "Deleted redirect rule {$snapshot['source_path']}",
        );

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect rule removed.');
    }

    /**
     * Verify destination URL health for an existing rule.
     */
    public function verify(int $id): RedirectResponse
    {
        $rule = RedirectRule::findOrFail($id);

        if ($rule->status_code === 410) {
            $rule->update([
                'last_verified_at' => now(),
                'last_verified_status' => 410,
                'destination_ok' => true,
            ]);

            return redirect()->route('admin.settings.redirects.index')->with('success', "Verified {$rule->source_path}: Intentional HTTP 410 Gone.");
        }

        $target = trim((string) $rule->target_path);
        $status = 0;

        try {
            if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
                SafeUrlValidator::assertSafeUrl($target);
                $res = SafeUrlValidator::httpClient(6, 3)->get($target);
                $status = $res->status();
            } else {
                $status = $this->probeService->probePath($target)['status'];
            }
        } catch (\Throwable) {
            $status = 0;
        }

        $ok = $status >= 200 && $status < 300;
        $rule->update([
            'last_verified_at' => now(),
            'last_verified_status' => $status,
            'destination_ok' => $ok,
        ]);

        return redirect()->route('admin.settings.redirects.index')->with(
            $ok ? 'success' : 'error',
            $ok
                ? "Verified destination '{$target}': HTTP {$status} OK."
                : "Destination check warning for '{$target}': returned HTTP {$status}."
        );
    }

    /**
     * AJAX endpoint to simulate URL redirection path and loop check.
     */
    public function test(Request $request): JsonResponse
    {
        $path = $request->query('path', '');
        if (empty($path)) {
            return response()->json(['success' => false, 'message' => 'Path parameter is required.'], 400);
        }

        $resolved = $this->redirectService->resolve($path);

        if (!$resolved) {
            return response()->json([
                'success' => true,
                'matched' => false,
                'source' => RedirectRule::normalizePath($path),
                'message' => 'No active redirect rule matched. Request proceeds to normal route resolution.',
            ]);
        }

        return response()->json([
            'success' => true,
            'matched' => true,
            'source' => RedirectRule::normalizePath($path),
            'target' => $resolved['target'],
            'status_code' => $resolved['status'],
            'hops' => $resolved['hops'],
            'message' => "Matches redirect rule to {$resolved['target']} (HTTP {$resolved['status']}).",
        ]);
    }
}
