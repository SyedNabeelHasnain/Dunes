@extends('layouts.admin')

@section('page_title', 'Reviews')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Customer Reviews</h2>
    <button class="btn btn-primary rounded-pill px-4 fw-800" data-bs-toggle="modal" data-bs-target="#createReviewModal">
        <i class="bi bi-plus-lg me-2"></i> Log New Review
    </button>
</div>

<!-- Reviews Table Card -->
<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="reviewsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Reviewer</th>
                        <th>Review Content</th>
                        <th>Source</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $r)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $r->reviewer_name }}</div>
                            @if($r->is_featured)
                                <span class="badge bg-warning text-white small px-2 py-0.5 rounded-pill"><i class="bi bi-star-fill me-1"></i>Featured</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $r->review_title }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 380px;">{{ $r->review_text }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill text-capitalize">{{ $r->source }}</span>
                        </td>
                        <td>
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $r->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeColor = [
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger'
                                ][$r->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $r->status }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-muted">
                                {{ $r->published_date ? \Carbon\Carbon::parse($r->published_date)->format('M j, Y') : '' }}
                            </div>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-review-btn" 
                                        style="width: 32px; height: 32px;" 
                                        title="Edit Review"
                                        data-id="{{ $r->id }}"
                                        data-name="{{ $r->reviewer_name }}"
                                        data-title="{{ $r->review_title }}"
                                        data-text="{{ $r->review_text }}"
                                        data-source="{{ $r->source }}"
                                        data-rating="{{ $r->rating }}"
                                        data-status="{{ $r->status }}"
                                        data-featured="{{ $r->is_featured ? '1' : '0' }}"
                                        data-date="{{ $r->published_date }}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.reviews.destroy', $r->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Review" onclick="return confirm('Delete this review?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No reviews recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $reviews->links() }}
</div>

<!-- Create Review Modal -->
<div class="modal fade" id="createReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Log New Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.reviews.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="c_reviewer_name" class="form-label fw-bold text-dark">Reviewer Name</label>
                        <input type="text" name="reviewer_name" id="c_reviewer_name" class="form-control" required placeholder="e.g. John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="c_review_title" class="form-label fw-bold text-dark">Review Title</label>
                        <input type="text" name="review_title" id="c_review_title" class="form-control" placeholder="e.g. Unforgettable Experience!">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="c_source" class="form-label fw-bold text-dark small">Source</label>
                            <select name="source" id="c_source" class="form-select form-select-sm">
                                <option value="manual">Manual</option>
                                <option value="google">Google Reviews</option>
                                <option value="tripadvisor">TripAdvisor</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="c_rating" class="form-label fw-bold text-dark small">Rating (1-5)</label>
                            <select name="rating" id="c_rating" class="form-select form-select-sm">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="c_status" class="form-label fw-bold text-dark small">Status</label>
                            <select name="status" id="c_status" class="form-select form-select-sm">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="c_published_date" class="form-label fw-bold text-dark small">Published Date</label>
                            <input type="date" name="published_date" id="c_published_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="c_review_text" class="form-label fw-bold text-dark">Review Text</label>
                        <textarea name="review_text" id="c_review_text" class="form-control" rows="4" required placeholder="Paste the customer review details here..."></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="c_is_featured" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="c_is_featured">Show on Home Page Marquee</label>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Log Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Modify Customer Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editReviewForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="e_reviewer_name" class="form-label fw-bold text-dark">Reviewer Name</label>
                        <input type="text" name="reviewer_name" id="e_reviewer_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="e_review_title" class="form-label fw-bold text-dark">Review Title</label>
                        <input type="text" name="review_title" id="e_review_title" class="form-control">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="e_source" class="form-label fw-bold text-dark small">Source</label>
                            <select name="source" id="e_source" class="form-select form-select-sm">
                                <option value="manual">Manual</option>
                                <option value="google">Google Reviews</option>
                                <option value="tripadvisor">TripAdvisor</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="e_rating" class="form-label fw-bold text-dark small">Rating (1-5)</label>
                            <select name="rating" id="e_rating" class="form-select form-select-sm">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="e_status" class="form-label fw-bold text-dark small">Status</label>
                            <select name="status" id="e_status" class="form-select form-select-sm">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="e_published_date" class="form-label fw-bold text-dark small">Published Date</label>
                            <input type="date" name="published_date" id="e_published_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="e_review_text" class="form-label fw-bold text-dark">Review Text</label>
                        <textarea name="review_text" id="e_review_text" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="e_is_featured" value="1">
                        <label class="form-check-label fw-bold text-dark" for="e_is_featured">Show on Home Page Marquee</label>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-review-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const title = $(this).data('title');
        const text = $(this).data('text');
        const source = $(this).data('source');
        const rating = $(this).data('rating');
        const status = $(this).data('status');
        const featured = $(this).data('featured');
        const date = $(this).data('date');

        $('#e_reviewer_name').val(name);
        $('#e_review_title').val(title);
        $('#e_review_text').val(text);
        $('#e_source').val(source);
        $('#e_rating').val(rating);
        $('#e_status').val(status);
        $('#e_published_date').val(date);
        
        if (featured == '1') {
            $('#e_is_featured').prop('checked', true);
        } else {
            $('#e_is_featured').prop('checked', false);
        }

        // Set action url
        $('#editReviewForm').attr('action', `/admin/reviews/${id}`);

        // Show modal
        const myModal = new bootstrap.Modal(document.getElementById('editReviewModal'));
        myModal.show();
    });
});
</script>
@endpush
@endsection
