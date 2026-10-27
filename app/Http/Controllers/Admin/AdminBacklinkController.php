<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backlink;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminBacklinkController extends Controller
{
    /**
     * Display a listing of backlinks with KPI metrics.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $relType = $request->input('rel_type');
        $platform = $request->input('platform');
        $search = $request->input('search');

        $query = Backlink::query();

        if ($status) {
            $query->where('status', $status);
        }
        if ($relType) {
            $query->where('link_type', $relType);
        }
        if ($platform) {
            $query->where('category', $platform);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('source_url', 'like', "%{$search}%")
                    ->orWhere('referring_domain', 'like', "%{$search}%")
                    ->orWhere('target_url', 'like', "%{$search}%")
                    ->orWhere('anchor_text', 'like', "%{$search}%")
                    ->orWhere('partner_name', 'like', "%{$search}%")
                    ->orWhere('campaign', 'like', "%{$search}%");
            });
        }

        $backlinks = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        // High-level KPI aggregations
        $totalCount = Backlink::count();
        $activeDofollowCount = Backlink::where('status', 'active')->where('link_type', 'dofollow')->count();
        $totalReferrals = (int) Backlink::sum('referral_visits_count');
        $totalAttributedRevenue = (float) Backlink::sum('revenue_generated_aed');
        $lostBrokenCount = Backlink::whereIn('status', ['lost', 'broken'])->count();

        // Available platforms for filter dropdown
        $platforms = Backlink::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.backlinks.index', compact(
            'backlinks',
            'totalCount',
            'activeDofollowCount',
            'totalReferrals',
            'totalAttributedRevenue',
            'lostBrokenCount',
            'platforms',
            'status',
            'relType',
            'platform',
            'search'
        ));
    }

    /**
     * Store a newly created backlink in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'source_url' => 'required|url|max:1000',
            'target_url' => 'required|url|max:1000',
            'anchor_text' => 'nullable|string|max:255',
            'rel_type' => 'required|in:dofollow,nofollow,ugc,sponsored',
            'status' => 'required|in:active,lost,pending,broken',
            'domain_authority' => 'nullable|integer|min:0|max:100',
            'platform' => 'nullable|string|max:100',
            'partner_name' => 'nullable|string|max:255',
            'partner_email' => 'nullable|email|max:255',
            'cost_aed' => 'nullable|numeric|min:0',
            'campaign' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'live_check' => 'nullable|boolean',
        ]);

        try {
            $backlink = Backlink::create([
                'source_url' => $validated['source_url'],
                'target_url' => $validated['target_url'],
                'anchor_text' => $validated['anchor_text'] ?? null,
                'rel_type' => $validated['rel_type'],
                'status' => $validated['status'],
                'domain_authority' => $validated['domain_authority'] ?? null,
                'platform' => $validated['platform'] ?? 'other',
                'partner_name' => $validated['partner_name'] ?? null,
                'partner_email' => $validated['partner_email'] ?? null,
                'cost_aed' => $validated['cost_aed'] ?? 0.00,
                'campaign' => $validated['campaign'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Optional immediate live verification check
            if ($request->boolean('live_check')) {
                $backlink->verifyHealth();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backlink registered successfully.',
                    'backlink' => $backlink->fresh(),
                ]);
            }

            return redirect()->route('admin.backlinks.index')->with('success', 'Backlink added and tracked successfully.');
        } catch (\Throwable $e) {
            Log::error('Error storing backlink: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save backlink: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to save backlink. Please try again.');
        }
    }

    /**
     * Update the specified backlink in storage.
     */
    public function update(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $backlink = Backlink::findOrFail($id);

        $validated = $request->validate([
            'source_url' => 'required|url|max:1000',
            'target_url' => 'required|url|max:1000',
            'anchor_text' => 'nullable|string|max:255',
            'rel_type' => 'required|in:dofollow,nofollow,ugc,sponsored',
            'status' => 'required|in:active,lost,pending,broken',
            'domain_authority' => 'nullable|integer|min:0|max:100',
            'platform' => 'nullable|string|max:100',
            'partner_name' => 'nullable|string|max:255',
            'partner_email' => 'nullable|email|max:255',
            'cost_aed' => 'nullable|numeric|min:0',
            'campaign' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'live_check' => 'nullable|boolean',
        ]);

        try {
            $backlink->update([
                'source_url' => $validated['source_url'],
                'target_url' => $validated['target_url'],
                'anchor_text' => $validated['anchor_text'] ?? null,
                'rel_type' => $validated['rel_type'],
                'status' => $validated['status'],
                'domain_authority' => $validated['domain_authority'] ?? null,
                'platform' => $validated['platform'] ?? $backlink->platform,
                'partner_name' => $validated['partner_name'] ?? null,
                'partner_email' => $validated['partner_email'] ?? null,
                'cost_aed' => $validated['cost_aed'] ?? 0.00,
                'campaign' => $validated['campaign'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->boolean('live_check')) {
                $backlink->verifyHealth();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backlink updated successfully.',
                    'backlink' => $backlink->fresh(),
                ]);
            }

            return redirect()->route('admin.backlinks.index')->with('success', 'Backlink updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Error updating backlink: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update backlink: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to update backlink.');
        }
    }

    /**
     * Remove the specified backlink from storage.
     */
    public function destroy(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $backlink = Backlink::findOrFail($id);

        try {
            $backlink->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backlink deleted successfully.',
                ]);
            }

            return redirect()->route('admin.backlinks.index')->with('success', 'Backlink deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Error deleting backlink: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete backlink.',
                ], 500);
            }

            return back()->with('error', 'Failed to delete backlink.');
        }
    }

    /**
     * Run an immediate health check on a single backlink.
     */
    public function verifySingle(Request $request, int|string $id): JsonResponse
    {
        $backlink = Backlink::findOrFail($id);
        $result = $backlink->verifyHealth();

        return response()->json([
            'success' => true,
            'status' => $backlink->status,
            'rel_type' => $backlink->rel_type,
            'http_status' => $backlink->http_status,
            'last_crawled_at' => $backlink->last_crawled_at?->diffForHumans(),
            'result' => $result,
        ]);
    }

    /**
     * Run health check verification on all active/pending backlinks.
     */
    public function verifyAll(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 25), 50);
        $backlinks = Backlink::whereIn('status', ['active', 'pending', 'lost'])
            ->orderByRaw('last_crawled_at IS NULL DESC, last_crawled_at ASC')
            ->limit($limit)
            ->get();

        $verified = 0;
        $active = 0;
        $lost = 0;

        foreach ($backlinks as $backlink) {
            $result = $backlink->verifyHealth();
            $verified++;
            if ($result['status'] === 'active') {
                $active++;
            } else {
                $lost++;
            }
        }

        return response()->json([
            'success' => true,
            'verified' => $verified,
            'active' => $active,
            'lost' => $lost,
            'message' => "Health scan complete: {$verified} links checked ({$active} active, {$lost} lost/error).",
        ]);
    }

    /**
     * Display the Syndication API Hub and Embed Generator.
     */
    public function syndication(): View
    {
        $tours = Tour::where('status', 'active')
            ->orderBy('priority', 'asc')
            ->get(['id', 'name', 'slug', 'starting_price', 'duration']);

        $feedUrl = url('/feeds/tours.xml');
        $apiUrl = url('/api/v1/syndication/tours');
        $widgetScriptUrl = asset('widgets/safari-card.js');

        return view('admin.backlinks.syndication', compact(
            'tours',
            'feedUrl',
            'apiUrl',
            'widgetScriptUrl'
        ));
    }
}
