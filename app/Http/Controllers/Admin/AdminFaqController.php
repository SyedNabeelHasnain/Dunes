<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Language;
use App\Models\Tour;
use App\Traits\NormalizesLocalizedInputs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminFaqController extends Controller
{
    use NormalizesLocalizedInputs;

    /**
     * Display a listing of FAQs.
     */
    public function index()
    {
        $faqs = Faq::with('assignments')->orderBy('priority', 'asc')->get();
        $tours = Tour::where('status', 'active')->get();
        $languages = Language::getActive();

        return view('admin.faqs.index', compact('faqs', 'tours', 'languages'));
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'assignment_type' => 'required|string|in:general,tour',
            'tour_id' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['question', 'answer']
        );
        $data['category'] = $request->assignment_type === 'general' ? 'general' : 'tour';
        $data['priority'] = (int) $request->input('priority', 0);
        $data['status'] = $request->input('status', 'active');

        $faq = Faq::create($data);

        $entityId = $request->assignment_type === 'general' ? null : (int) $request->tour_id;

        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => $request->assignment_type,
            'entity_id' => $entityId,
        ]);

        Cache::forget('site_home_cache');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, string $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'assignment_type' => 'required|string|in:general,tour',
            'tour_id' => 'nullable|integer',
        ]);

        $data = $this->normalizeLocalizedData(
            $request->all(),
            ['question', 'answer']
        );
        $data['category'] = $request->assignment_type === 'general' ? 'general' : 'tour';
        $data['priority'] = (int) $request->input('priority', 0);
        $data['status'] = $request->input('status', 'active');

        $faq->update($data);

        $entityId = $request->assignment_type === 'general' ? null : (int) $request->tour_id;

        FaqAssignment::where('faq_id', $faq->id)->delete();
        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => $request->assignment_type,
            'entity_id' => $entityId,
        ]);

        Cache::forget('site_home_cache');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(string $id)
    {
        $faq = Faq::findOrFail($id);
        FaqAssignment::where('faq_id', $faq->id)->delete();
        $faq->delete();

        Cache::forget('site_home_cache');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Toggle active/inactive status of an FAQ.
     */
    public function toggleStatus(string $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->status = $faq->status === 'active' ? 'inactive' : 'active';
        $faq->save();

        Cache::forget('site_home_cache');

        return response()->json([
            'success' => true,
            'status' => $faq->status,
            'message' => 'FAQ status updated to '.ucfirst($faq->status).'.',
        ]);
    }
}
