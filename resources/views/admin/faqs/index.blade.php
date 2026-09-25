@extends('layouts.admin')

@section('page_title', 'FAQs')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Frequently Asked Questions</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer inquiries, general service guidelines, and tour-specific FAQ answers.</p>
        </div>
        <button type="button" @click="$dispatch('open-add-faq')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
            <i class="bi bi-plus-lg"></i> Log New FAQ
        </button>
    </div>

    <!-- FAQs Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="faqsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Question</th>
                        <th class="py-3 px-4">Answer Snippet</th>
                        <th class="py-3 px-4">Assignment</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Priority</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($faqs as $faq)
                    @php
                        $assignment = $faq->assignments->first();
                        $assignmentType = $assignment ? $assignment->entity_type : 'general';
                        $entityId = $assignment ? $assignment->entity_id : null;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $faq->question }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-slate-500 text-xs truncate max-w-sm">{{ $faq->answer }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($assignmentType === 'general')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">General FAQ</span>
                            @else
                                @php
                                    $tourName = $tours->firstWhere('id', $entityId)->name ?? 'Deleted Tour';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200" title="{{ $tourName }}">
                                    Tour: {{ Str::limit($tourName, 25) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($faq->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize badge-interactive ajax-toggle-status cursor-pointer" data-url="{{ route('admin.faqs.toggle-status', $faq->id) }}" title="Click to toggle status">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize badge-interactive ajax-toggle-status cursor-pointer" data-url="{{ route('admin.faqs.toggle-status', $faq->id) }}" title="Click to toggle status">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-600 text-xs">
                            {{ $faq->priority }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" 
                                        class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-faq-btn cursor-pointer" 
                                        title="Edit FAQ"
                                        data-id="{{ $faq->id }}"
                                        data-question="{{ $faq->question }}"
                                        data-answer="{{ $faq->answer }}"
                                        data-priority="{{ $faq->priority }}"
                                        data-status="{{ $faq->status }}"
                                        data-type="{{ $assignmentType }}"
                                        data-tour-id="{{ $entityId }}"
                                        data-action="{{ route('admin.faqs.update', $faq->id) }}">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </button>
                                <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this FAQ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Delete FAQ">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400 text-xs">No FAQs loaded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create FAQ (Alpine.js) -->
<div x-data="{ open: false, isTour: false }" @open-add-faq.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-question-circle text-primary"></i> Log New FAQ
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="c_question" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Question *</label>
                    <input type="text" name="question" id="c_question" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required placeholder="e.g. What is the dress code?">
                </div>
                <div>
                    <label for="c_answer" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Answer *</label>
                    <textarea name="answer" id="c_answer" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" rows="4" required placeholder="Describe the answer details here..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c_assignment_type" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Assignment Scope</label>
                        <select name="assignment_type" id="c_assignment_type" @change="isTour = ($event.target.value === 'tour')" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="general">General (Global FAQ)</option>
                            <option value="tour">Tour-Specific FAQ</option>
                        </select>
                    </div>
                    <div x-show="isTour" x-cloak>
                        <label for="c_tour_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Target Tour</label>
                        <select name="tour_id" id="c_tour_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            @foreach($tours as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c_priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority *</label>
                        <input type="number" name="priority" id="c_priority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="99" required>
                    </div>
                    <div>
                        <label for="c_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status *</label>
                        <select name="status" id="c_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Create FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit FAQ (Alpine.js) -->
<div x-data="{ open: false, isTour: false }" @open-edit-faq.window="open = true; isTour = ($('#e_assignment_type').val() === 'tour')" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Modify FAQ Details
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="editFaqForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="e_question" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Question *</label>
                    <input type="text" name="question" id="e_question" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div>
                    <label for="e_answer" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Answer *</label>
                    <textarea name="answer" id="e_answer" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" rows="4" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="e_assignment_type" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Assignment Scope</label>
                        <select name="assignment_type" id="e_assignment_type" @change="isTour = ($event.target.value === 'tour')" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="general">General (Global FAQ)</option>
                            <option value="tour">Tour-Specific FAQ</option>
                        </select>
                    </div>
                    <div x-show="isTour" x-cloak>
                        <label for="e_tour_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Target Tour</label>
                        <select name="tour_id" id="e_tour_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            @foreach($tours as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="e_priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority *</label>
                        <input type="number" name="priority" id="e_priority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                    <div>
                        <label for="e_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status *</label>
                        <select name="status" id="e_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Update FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-faq-btn').on('click', function() {
        const id = $(this).data('id');
        const question = $(this).data('question');
        const answer = $(this).data('answer');
        const priority = $(this).data('priority');
        const status = $(this).data('status');
        const type = $(this).data('type');
        const tourId = $(this).data('tour-id');
        const action = $(this).data('action') || `/admin/faqs/${id}`;

        $('#e_question').val(question);
        $('#e_answer').val(answer);
        $('#e_priority').val(priority);
        $('#e_status').val(status);
        $('#e_assignment_type').val(type);
        
        if (type === 'tour') {
            $('#e_tour_id').val(tourId);
        } else {
            $('#e_tour_id').val('');
        }

        // Set action url
        $('#editFaqForm').attr('action', action);

        // Open Alpine modal
        window.dispatchEvent(new CustomEvent('open-edit-faq'));
    });
});
</script>
@endpush
@endsection
