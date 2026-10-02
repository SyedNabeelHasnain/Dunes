<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\MenuItem;
use App\Services\CmsContentService;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    use NormalizesLocalizedInputs;

    public function index(Request $request)
    {
        $location = $request->query('location', 'header');
        $validLocations = [
            'header' => 'Header Navigation',
            'footer_desert_safaris' => 'Footer: Desert Safaris (Col 2)',
            'footer_tours_cruises' => 'Footer: Tours & Cruises (Col 3)',
            'footer_trust_policies' => 'Footer: Trust & Policies (Col 4)',
        ];

        if (! array_key_exists($location, $validLocations)) {
            $location = 'header';
        }

        $items = MenuItem::where('location', $location)->orderBy('order', 'asc')->get();
        $languages = Language::getActive();

        return view('admin.menus.index', compact('items', 'location', 'validLocations', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
            'label' => 'required',
            'url' => 'required|string',
            'route_name' => 'nullable|string',
            'target' => 'nullable|string|in:_self,_blank',
            'order' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['label']
        );

        MenuItem::create([
            'location' => $request->input('location'),
            'label' => $data['label'],
            'url' => $request->input('url'),
            'route_name' => $request->input('route_name'),
            'target' => $request->input('target', '_self'),
            'icon' => $request->input('icon'),
            'order' => (int) $request->input('order', 0),
            'is_active' => true,
        ]);

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.menus.index', ['location' => $request->input('location')])
            ->with('success', 'Menu link created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $item = MenuItem::findOrFail($id);

        $request->validate([
            'label' => 'required',
            'url' => 'required|string',
            'route_name' => 'nullable|string',
            'target' => 'nullable|string|in:_self,_blank',
            'order' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['label']
        );

        $item->update([
            'label' => $data['label'],
            'url' => $request->input('url'),
            'route_name' => $request->input('route_name'),
            'target' => $request->input('target', '_self'),
            'icon' => $request->input('icon'),
            'order' => (int) $request->input('order', $item->order),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $item->is_active,
        ]);

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.menus.index', ['location' => $item->location])
            ->with('success', 'Menu link updated successfully.');
    }

    public function destroy(int $id)
    {
        $item = MenuItem::findOrFail($id);
        $location = $item->location;
        $item->delete();

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.menus.index', ['location' => $location])
            ->with('success', 'Menu link deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        app(CmsContentService::class)->clearCache();

        return response()->json([
            'success' => true,
            'is_active' => $item->is_active,
            'message' => 'Status updated.',
        ]);
    }
}
