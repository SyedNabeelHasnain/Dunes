<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqAssignment;
use App\Models\Tour;
use Illuminate\Http\Request;

class AdminFaqController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index()
    {
        $faqs = Faq::with('assignments')->orderBy('priority', 'asc')->get();
        $tours = Tour::where('status', 'active')->get();
        return view('admin.faqs.index', compact('faqs', 'tours'));
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'assignment_type' => 'required|string|in:general,tour',
            'tour_id' => 'nullable|integer',
        ]);

        $faq = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->assignment_type === 'general' ? 'general' : 'tour',
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        $entityId = $request->assignment_type === 'general' ? null : (int)$request->tour_id;

        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => $request->assignment_type,
            'entity_id' => $entityId,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, string $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'priority' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
            'assignment_type' => 'required|string|in:general,tour',
            'tour_id' => 'nullable|integer',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->assignment_type === 'general' ? 'general' : 'tour',
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        $entityId = $request->assignment_type === 'general' ? null : (int)$request->tour_id;

        FaqAssignment::where('faq_id', $faq->id)->delete();
        FaqAssignment::create([
            'faq_id' => $faq->id,
            'entity_type' => $request->assignment_type,
            'entity_id' => $entityId,
        ]);

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
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }
}
