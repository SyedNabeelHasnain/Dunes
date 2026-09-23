<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalItem;
use App\Models\LegalPage;
use App\Models\LegalSection;
use Illuminate\Http\Request;

class AdminLegalController extends Controller
{
    /**
     * Display a listing of Legal Pages.
     */
    public function index()
    {
        $pages = LegalPage::withCount('sections')->orderBy('id', 'asc')->get();

        return view('admin.legal.index', compact('pages'));
    }

    /**
     * Show form for editing a specific Legal Page.
     */
    public function edit(int $id)
    {
        $page = LegalPage::with(['sections.items'])->findOrFail($id);

        return view('admin.legal.edit', compact('page'));
    }

    /**
     * Update Legal Page title, description, and sections.
     */
    public function update(Request $request, int $id)
    {
        $page = LegalPage::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'subtitle_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
        ]);

        $page->update([
            'title' => $request->input('title'),
            'title_ar' => $request->input('title_ar'),
            'subtitle' => $request->input('subtitle'),
            'subtitle_ar' => $request->input('subtitle_ar'),
            'description' => $request->input('description'),
            'description_ar' => $request->input('description_ar'),
        ]);

        return redirect()->route('admin.legal.index')->with('success', 'Legal page updated successfully.');
    }

    /**
     * Add a section to a Legal Page.
     */
    public function addSection(Request $request, int $id)
    {
        $page = LegalPage::findOrFail($id);

        $request->validate([
            'heading' => 'required|string|max:255',
            'heading_ar' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:255',
            'subheading_ar' => 'nullable|string|max:255',
            'priority' => 'required|integer',
        ]);

        $page->sections()->create($request->only(['heading', 'heading_ar', 'subheading', 'subheading_ar', 'priority']));

        return redirect()->route('admin.legal.edit', $id)->with('success', 'Section added successfully.');
    }

    /**
     * Add an item to a Legal Section.
     */
    public function addItem(Request $request, int $sectionId)
    {
        $section = LegalSection::findOrFail($sectionId);

        $request->validate([
            'content' => 'required|string',
            'content_ar' => 'nullable|string',
            'priority' => 'required|integer',
        ]);

        $section->items()->create($request->only(['content', 'content_ar', 'priority']));

        return redirect()->route('admin.legal.edit', $section->page_id)->with('success', 'Item added successfully.');
    }

    /**
     * Delete a Legal Section.
     */
    public function deleteSection(int $id)
    {
        $section = LegalSection::findOrFail($id);
        $pageId = $section->page_id;
        $section->delete();

        return redirect()->route('admin.legal.edit', $pageId)->with('success', 'Section deleted successfully.');
    }

    /**
     * Delete a Legal Item.
     */
    public function deleteItem(int $id)
    {
        $item = LegalItem::findOrFail($id);
        $pageId = $item->section->page_id;
        $item->delete();

        return redirect()->route('admin.legal.edit', $pageId)->with('success', 'Item deleted successfully.');
    }
}
