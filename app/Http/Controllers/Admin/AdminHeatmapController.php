<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\HeatmapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminHeatmapController extends Controller
{
    protected HeatmapService $heatmapService;

    public function __construct(HeatmapService $heatmapService)
    {
        $this->heatmapService = $heatmapService;
    }

    /**
     * Display Heatmap analytics visualizer.
     */
    public function index(Request $request): View
    {
        $pages = $this->heatmapService->getAvailablePages();
        $selectedPage = $request->input('page', $pages[0]['page_path'] ?? '/');
        $selectedDevice = $request->input('device', 'all');
        $days = (int) $request->input('days', 30);

        $heatmapData = $this->heatmapService->getHeatmapData($selectedPage, $selectedDevice, $days);
        $isEnabled = $this->heatmapService->isEnabled();

        return view('admin.analytics.heatmaps.index', compact('pages', 'selectedPage', 'selectedDevice', 'days', 'heatmapData', 'isEnabled'));
    }

    /**
     * Async JSON endpoint to get heatmap data for page/device.
     */
    public function getData(Request $request): JsonResponse
    {
        $page = $request->input('page', '/');
        $device = $request->input('device', 'all');
        $days = (int) $request->input('days', 30);

        $data = $this->heatmapService->getHeatmapData($page, $device, $days);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Purge heatmap logs.
     */
    public function purge(Request $request): RedirectResponse
    {
        $days = (int) $request->input('days', 30);
        $res = $this->heatmapService->purgeOldHeatmaps($days);

        return back()->with('success', "Purged {$res['deleted_clicks']} click logs and {$res['deleted_scrolls']} scroll logs older than {$days} days.");
    }

    /**
     * Update heatmap settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        Setting::updateOrCreate(
            ['key' => 'heatmaps_enabled'],
            ['value' => $request->boolean('heatmaps_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Heatmap preferences updated successfully.');
    }
}
