@extends('layouts.admin')

@section('page_title', 'FAQs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Frequently Asked Questions</h2>
    <button class="btn btn-primary rounded-pill px-4 fw-800" data-bs-toggle="modal" data-bs-target="#createFaqModal">
        <i class="bi bi-plus-lg me-2"></i> Log New FAQ
    </button>
</div>

<!-- FAQs Table Card -->
<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="faqsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Question</th>
                        <th>Answer Snippet</th>
                        <th>Assignment</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    @php
                        $assignment = $faq->assignments->first();
                        $assignmentType = $assignment ? $assignment->entity_type : 'general';
                        $entityId = $assignment ? $assignment->entity_id : null;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark fs-6">{{ $faq->question }}</div>
                        </td>
                        <td>
                            <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $faq->answer }}</div>
                        </td>
                        <td>
                            @if($assignmentType === 'general')
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">General FAQ</span>
                            @else
                                @php
                                    $tourName = $tours->firstWhere('id', $entityId)->name ?? 'Deleted Tour';
                                @endphp
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill" title="{{ $tourName }}">
                                    Tour: {{ Str::limit($tourName, 25) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($faq->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ $faq->priority }}</td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-faq-btn" 
                                        style="width: 32px; height: 32px;" 
                                        title="Edit FAQ"
                                        data-id="{{ $faq->id }}"
                                        data-question="{{ $faq->question }}"
                                        data-answer="{{ $faq->answer }}"
                                        data-priority="{{ $faq->priority }}"
                                        data-status="{{ $faq->status }}"
                                        data-type="{{ $assignmentType }}"
                                        data-tour-id="{{ $entityId }}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete FAQ" onclick="return confirm('Delete this FAQ?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No FAQs loaded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create FAQ Modal -->
<div class="modal fade" id="createFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Log New FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.faqs.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="c_question" class="form-label fw-bold text-dark">Question</label>
                        <input type="text" name="question" id="c_question" class="form-control" required placeholder="e.g. What is the dress code?">
                    </div>
                    <div class="mb-3">
                        <label for="c_answer" class="form-label fw-bold text-dark">Answer</label>
                        <textarea name="answer" id="c_answer" class="form-control" rows="4" required placeholder="Describe the answer details here..."></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="c_assignment_type" class="form-label fw-bold text-dark small">Assignment Scope</label>
                            <select name="assignment_type" id="c_assignment_type" class="form-select form-select-sm" onchange="toggleTourSelector('c')">
                                <option value="general">General (Global FAQ)</option>
                                <option value="tour">Tour-Specific FAQ</option>
                            </select>
                        </div>
                        <div class="col-6 d-none" id="c_tour_selector_group">
                            <label for="c_tour_id" class="form-label fw-bold text-dark small">Select Target Tour</label>
                            <select name="tour_id" id="c_tour_id" class="form-select form-select-sm">
                                @foreach($tours as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label for="c_priority" class="form-label fw-bold text-dark small">Priority</label>
                            <input type="number" name="priority" id="c_priority" class="form-control form-control-sm" value="99" required>
                        </div>
                        <div class="col-6">
                            <label for="c_status" class="form-label fw-bold text-dark small">Status</label>
                            <select name="status" id="c_status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Create FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Modify FAQ Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editFaqForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="e_question" class="form-label fw-bold text-dark">Question</label>
                        <input type="text" name="question" id="e_question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="e_answer" class="form-label fw-bold text-dark">Answer</label>
                        <textarea name="answer" id="e_answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="e_assignment_type" class="form-label fw-bold text-dark small">Assignment Scope</label>
                            <select name="assignment_type" id="e_assignment_type" class="form-select form-select-sm" onchange="toggleTourSelector('e')">
                                <option value="general">General (Global FAQ)</option>
                                <option value="tour">Tour-Specific FAQ</option>
                            </select>
                        </div>
                        <div class="col-6" id="e_tour_selector_group">
                            <label for="e_tour_id" class="form-label fw-bold text-dark small">Select Target Tour</label>
                            <select name="tour_id" id="e_tour_id" class="form-select form-select-sm">
                                @foreach($tours as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label for="e_priority" class="form-label fw-bold text-dark small">Priority</label>
                            <input type="number" name="priority" id="e_priority" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label for="e_status" class="form-label fw-bold text-dark small">Status</label>
                            <select name="status" id="e_status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleTourSelector(prefix) {
    const type = $(`#${prefix}_assignment_type`).val();
    if(type === 'tour') {
        $(`#${prefix}_tour_selector_group`).removeClass('d-none');
    } else {
        $(`#${prefix}_tour_selector_group`).addClass('d-none');
    }
}

$(document).ready(function() {
    $('.edit-faq-btn').on('click', function() {
        const id = $(this).data('id');
        const question = $(this).data('question');
        const answer = $(this).data('answer');
        const priority = $(this).data('priority');
        const status = $(this).data('status');
        const type = $(this).data('type');
        const tourId = $(this).data('tour-id');

        $('#e_question').val(question);
        $('#e_answer').val(answer);
        $('#e_priority').val(priority);
        $('#e_status').val(status);
        $('#e_assignment_type').val(type);
        
        if (type === 'tour') {
            $('#e_tour_selector_group').removeClass('d-none');
            $('#e_tour_id').val(tourId);
        } else {
            $('#e_tour_selector_group').addClass('d-none');
            $('#e_tour_id').val('');
        }

        // Set action url
        $('#editFaqForm').attr('action', `/admin/faqs/${id}`);

        // Show modal
        const myModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
        myModal.show();
    });
});
</script>
@endpush
@endsection
