<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedirectRule;
use App\Services\RedirectManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRedirectController extends Controller
{
    public function __construct(
        protected RedirectManagerService $redirectService
    ) {}

    /**
     * Display list of dynamic redirect rules.
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

        $stats = [
            'total' => RedirectRule::count(),
            'active' => RedirectRule::where('is_active', true)->count(),
            'total_hits' => RedirectRule::sum('hit_count'),
            'permanent_count' => RedirectRule::where('status_code', 301)->count(),
            'temporary_count' => RedirectRule::whereIn('status_code', [302, 307, 308])->count(),
        ];

        return view('admin.settings.redirects', compact('rules', 'stats'));
    }

    /**
     * Create a new redirect rule with loop & chain detection.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'source_path' => 'required|string|max:500',
            'target_path' => 'required|string|max:500',
            'status_code' => 'required|integer|in:301,302,307,308',
            'notes' => 'nullable|string|max:1000',
        ]);

        $loopCheck = $this->redirectService->detectLoopOrChain(
            $request->input('source_path'),
            $request->input('target_path')
        );

        if ($loopCheck['is_loop']) {
            return back()->withInput()->with('error', $loopCheck['message']);
        }

        $rule = $this->redirectService->saveRule([
            'source_path' => $request->input('source_path'),
            'target_path' => $request->input('target_path'),
            'status_code' => (int) $request->input('status_code'),
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        $msg = "Redirect rule created successfully (HTTP {$rule->status_code}).";
        if ($loopCheck['is_chain']) {
            $msg .= " " . $loopCheck['message'];
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
            'target_path' => 'required|string|max:500',
            'status_code' => 'required|integer|in:301,302,307,308',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $loopCheck = $this->redirectService->detectLoopOrChain(
            $request->input('source_path'),
            $request->input('target_path'),
            $id
        );

        if ($loopCheck['is_loop']) {
            return back()->withInput()->with('error', $loopCheck['message']);
        }

        $this->redirectService->saveRule([
            'source_path' => $request->input('source_path'),
            'target_path' => $request->input('target_path'),
            'status_code' => (int) $request->input('status_code'),
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active'),
        ], $id);

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect rule updated successfully.');
    }

    /**
     * Delete a redirect rule.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->redirectService->deleteRule($id);

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect rule removed.');
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
