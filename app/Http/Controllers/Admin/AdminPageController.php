<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\CmsContentService;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    use NormalizesLocalizedInputs;

    public function index()
    {
        $pages = Page::withCount('sections')->orderBy('id', 'asc')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(int $id)
    {
        $page = Page::with('sections')->findOrFail($id);
        $languages = Language::getActive();

        return view('admin.pages.edit', compact('page', 'languages'));
    }

    public function update(Request $request, int $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required',
            'subtitle' => 'nullable',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
            'status' => 'required|string|in:published,draft',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['title', 'subtitle', 'meta_title', 'meta_description']
        );

        $page->update([
            'name' => $request->input('name'),
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'status' => $request->input('status', 'published'),
        ]);

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.pages.edit', $id)->with('success', 'Page content updated successfully.');
    }

    public function addSection(Request $request, int $pageId)
    {
        $page = Page::findOrFail($pageId);

        $request->validate([
            'section_key' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'title' => 'nullable',
            'subtitle' => 'nullable',
            'body' => 'nullable',
            'order' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['title', 'subtitle', 'body']
        );

        $page->sections()->create([
            'section_key' => $request->input('section_key'),
            'name' => $request->input('name'),
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'] ?? null,
            'extra_data' => $request->input('extra_data'),
            'order' => (int) $request->input('order', 0),
            'is_active' => true,
        ]);

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.pages.edit', $pageId)->with('success', 'Section created successfully.');
    }

    public function updateSection(Request $request, int $pageId, int $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)->findOrFail($sectionId);

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable',
            'subtitle' => 'nullable',
            'body' => 'nullable',
            'order' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['title', 'subtitle', 'body']
        );

        $section->update([
            'name' => $request->input('name'),
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'] ?? null,
            'extra_data' => $request->input('extra_data'),
            'order' => (int) $request->input('order', $section->order),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $section->is_active,
        ]);

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.pages.edit', $pageId)->with('success', 'Section updated successfully.');
    }

    public function deleteSection(int $pageId, int $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)->findOrFail($sectionId);
        $section->delete();

        app(CmsContentService::class)->clearCache();

        return redirect()->route('admin.pages.edit', $pageId)->with('success', 'Section deleted successfully.');
    }

    public function toggleSectionStatus(int $pageId, int $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)->findOrFail($sectionId);
        $section->update(['is_active' => ! $section->is_active]);

        app(CmsContentService::class)->clearCache();

        return response()->json([
            'success' => true,
            'is_active' => $section->is_active,
            'message' => 'Section status toggled.',
        ]);
    }
}
