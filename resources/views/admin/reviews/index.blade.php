@extends('layouts.admin')

@section('page_title', 'Customer Reviews & Ratings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Customer Reviews & Ratings Hub</h2>
        <p class="text-muted small mb-0">Manage customer testimonials, moderate Google/TripAdvisor reviews, and showcase ratings on the website.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#createReviewModal">
        <i class="bi bi-plus-lg me-2"></i> Log New Review
    </button>
</div>

<!-- 4 Key Performance Metric Cards -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Reviews</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-chat-heart-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">All logged customer feedback</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Average Rating</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-star-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">★ {{ number_format($stats['avg_rating'] ?? 5.0, 1) }}</h3>
            <span class="text-success small fw-bold" style="font-size: 0.75rem;">{{ $stats['five_star_pct'] ?? 100 }}% 5-Star Reviews</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Live on Site</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-circle-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-success mb-0">{{ number_format($stats['approved'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Approved testimonials</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Pending Moderation</span>
                <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-clock-history fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-danger mb-0">{{ number_format($stats['pending'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Awaiting approval</span>
        </div>
    </div>
</div>

<!-- 2 Interactive Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-bar-chart-fill text-warning me-2"></i>Star Rating Distribution</h6>
                    <span class="text-muted small">Customer satisfaction across star ratings</span>
                </div>
            </div>
            <div style="height: 220px;">
                <canvas id="ratingDistributionChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-800 text-dark mb-1"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Review Sources</h6>
                    <span class="text-muted small">Google vs TripAdvisor vs Direct</span>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="reviewSourceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Multi-Parameter Filter Toolbar -->
<div class="card card-modern border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('admin.reviews.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label for="reviewSearch" class="form-label small fw-bold text-dark mb-1">Search Reviews</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="reviewSearch" class="form-control border-start-0" placeholder="Reviewer, Title, Content..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="ratingFilter" class="form-label small fw-bold text-dark mb-1">Rating</label>
                <select name="rating" id="ratingFilter" class="form-select">
                    <option value="">All Ratings</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars ★★★★★</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars ★★★★☆</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars ★★★☆☆</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars ★★☆☆☆</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star ★☆☆☆☆</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="statusFilter" class="form-label small fw-bold text-dark mb-1">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Live)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" title="Apply Filter"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Reviews Table Card -->
<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4 p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="reviewsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Reviewer</th>
                        <th>Review Content</th>
                        <th>Source</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
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
                        <td class="text-center" data-order="{{ $r->rating }}">
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $r->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="text-center" data-order="{{ $r->status }}">
                            @php
                                $badgeColor = [
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger'
                                ][$r->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill badge-interactive ajax-toggle-status" data-url="{{ route('admin.reviews.toggle-status', $r->id) }}" title="Click to toggle status">{{ $r->status }}</span>
                        </td>
                        <td data-order="{{ $r->published_date ? \Carbon\Carbon::parse($r->published_date)->timestamp : 0 }}">
                            <div class="small fw-semibold text-muted">
                                {{ $r->published_date ? \Carbon\Carbon::parse($r->published_date)->format('M j, Y') : '' }}
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-review-btn" 
                                        style="width: 34px; height: 34px;" 
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
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete Review" onclick="return confirm('Delete this review?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-muted opacity-50"></i>
                            No customer reviews match your filter parameters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingDistributionChart');
    if (ratingCtx) {
        const ratingData = @json($ratingDistribution);
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ['5 Stars ★', '4 Stars ★', '3 Stars ★', '2 Stars ★', '1 Star ★'],
                datasets: [{
                    label: 'Reviews',
                    data: [ratingData[5] || 0, ratingData[4] || 0, ratingData[3] || 0, ratingData[2] || 0, ratingData[1] || 0],
                    backgroundColor: ['#F58F43', '#fbbf24', '#fcd34d', '#9ca3af', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#f5f5f5' } },
                    y: { beginAtZero: true, grid: { color: '#f5f5f5' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Review Source Breakdown Doughnut Chart
    const sourceCtx = document.getElementById('reviewSourceChart');
    if (sourceCtx) {
        const sourceData = @json($sourceBreakdown);
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: sourceData.map(s => (s.source || 'Direct').toUpperCase()),
                datasets: [{
                    data: sourceData.map(s => s.count),
                    backgroundColor: ['#4285F4', '#00af87', '#F58F43', '#8b5cf6'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    // Edit Review Modal
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
