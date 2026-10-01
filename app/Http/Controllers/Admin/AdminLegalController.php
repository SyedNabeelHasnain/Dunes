<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\LegalItem;
use App\Models\LegalPage;
use App\Models\LegalSection;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;

class AdminLegalController extends Controller
{
    use NormalizesLocalizedInputs;

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
        $languages = Language::getActive();

        return view('admin.legal.edit', compact('page', 'languages'));
    }

    /**
     * Update Legal Page title, description, and sections.
     */
    public function update(Request $request, int $id)
    {
        $page = LegalPage::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'subtitle' => 'nullable',
            'description' => 'nullable',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['title', 'subtitle', 'description']
        );

        $page->update($data);

        return redirect()->route('admin.legal.index')->with('success', 'Legal page updated successfully.');
    }

    /**
     * Add a section to a Legal Page.
     */
    public function addSection(Request $request, int $id)
    {
        $page = LegalPage::findOrFail($id);

        $request->validate([
            'heading' => 'required',
            'subheading' => 'nullable',
            'priority' => 'required|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['heading', 'subheading']
        );
        $data['priority'] = (int) $request->input('priority', 0);

        $page->sections()->create($data);

        return redirect()->route('admin.legal.edit', $id)->with('success', 'Section added successfully.');
    }

    /**
     * Add an item to a Legal Section.
     */
    public function addItem(Request $request, int $sectionId)
    {
        $section = LegalSection::findOrFail($sectionId);

        $request->validate([
            'content' => 'required',
            'priority' => 'required|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['content']
        );
        $data['priority'] = (int) $request->input('priority', 0);

        $section->items()->create($data);

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
