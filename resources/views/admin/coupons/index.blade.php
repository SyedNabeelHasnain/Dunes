@extends('layouts.admin')

@section('page_title', 'Coupons & Promo Codes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Coupons & Promo Codes Management</h2>
        <p class="text-muted small mb-0">Create promotional codes, set discount limits, track customer redemptions, and drive tour conversions.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('admin.coupons.export') }}" class="btn btn-white shadow-sm border-0 rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-arrow-down text-success fs-5"></i>
            <span>Export CSV</span>
        </a>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary shadow-sm border-0 rounded-pill px-4 py-2 fw-800 text-white d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            <span>Create Promo Code</span>
        </a>
    </div>
</div>

<!-- 4 Key Performance Metric Cards -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Active Promos</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-ticket-perforated-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total_active'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Live and redeemable codes</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Redemptions</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-person-check-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-primary mb-0">{{ number_format($stats['total_redemptions'] ?? 0) }}</h3>
            <span class="text-primary small fw-bold" style="font-size: 0.75rem;"><i class="bi bi-graph-up-arrow me-1"></i>Guest checkouts</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Discounts Granted</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-piggy-bank-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">AED {{ number_format($stats['total_discount_given'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Total guest savings</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Promo Revenue</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-cash-stack fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-success mb-0">AED {{ number_format($stats['total_promo_revenue'] ?? 0) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Bookings generated via promos</span>
        </div>
    </div>
</div>

<!-- Coupons Table Card -->
<style>
.table-responsive {
    min-height: 260px;
    padding-bottom: 70px;
    overflow-y: visible !important;
}
.dropdown-menu {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>
<div class="card card-modern bg-white border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        
        <!-- Filter Controls -->
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Promo</label>
                <div class="input-group rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 shadow-none ps-0" placeholder="Code, title, description..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Discount Type</label>
                <select name="type" class="form-select rounded-pill border shadow-none" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="percentage" {{ $type === 'percentage' ? 'selected' : '' }}>Percentage (% OFF)</option>
                    <option value="fixed" {{ $type === 'fixed' ? 'selected' : '' }}>Flat Amount (AED)</option>
                    <option value="per_person" {{ $type === 'per_person' ? 'selected' : '' }}>Per Person (AED)</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                <select name="status" class="form-select rounded-pill border shadow-none" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill px-3 py-2 w-100 fw-bold">Filter</button>
                @if($search || $type || $status)
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>

        <!-- Table View -->
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0" id="couponsTable">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="border-0 ps-3">Promo Code</th>
                        <th class="border-0">Promotion Name</th>
                        <th class="border-0">Discount</th>
                        <th class="border-0">Rules & Scope</th>
                        <th class="border-0 text-center">Redemptions</th>
                        <th class="border-0 text-center">Validity Window</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-end pe-3 no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    @php
                        $now = now();
                        $isExpired = $coupon->valid_until && $now->gt($coupon->valid_until);
                        $isScheduled = $coupon->valid_from && $now->lt($coupon->valid_from);
                        $limitReached = $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit;
                        $pctUsed = $coupon->usage_limit ? round(($coupon->used_count / $coupon->usage_limit) * 100) : 0;
                    @endphp
                    <tr>
                        <!-- Code with 1-click copy -->
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white px-3 py-2 rounded-pill font-monospace fw-bold fs-6" style="letter-spacing: 1px;">
                                    {{ $coupon->code }}
                                </span>
                                <button type="button" class="btn btn-sm btn-light rounded-circle p-1 text-muted copy-code-btn" data-code="{{ $coupon->code }}" title="Copy Code">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            @if($coupon->is_featured)
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill mt-1" style="font-size: 0.7rem;"><i class="bi bi-star-fill me-1"></i>Featured</span>
                            @endif
                        </td>

                        <!-- Name & Applicability -->
                        <td>
                            <div class="fw-bold text-dark">{{ $coupon->name }}</div>
                            <div class="small text-muted">
                                @if($coupon->tour)
                                    <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-compass me-1"></i>{{ Str::limit($coupon->tour->name, 25) }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-globe me-1"></i>Sitewide (All Tours)</span>
                                @endif
                                @if($coupon->tier)
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $coupon->tier->display_name }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Discount Value & Type -->
                        <td>
                            @if($coupon->discount_type === 'percentage')
                                <div class="fw-800 text-success fs-5">{{ (float)$coupon->discount_value }}% OFF</div>
                                @if($coupon->max_discount)
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Cap: AED {{ number_format($coupon->max_discount) }}</small>
                                @endif
                            @elseif($coupon->discount_type === 'per_person')
                                <div class="fw-800 text-primary fs-5">AED {{ number_format($coupon->discount_value) }} <span class="small fs-6 fw-normal text-muted">/ guest</span></div>
                                @if($coupon->max_discount)
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Cap: AED {{ number_format($coupon->max_discount) }}</small>
                                @endif
                            @else
                                <div class="fw-800 text-dark fs-5">AED {{ number_format($coupon->discount_value) }} <span class="small fs-6 fw-normal text-muted">Flat</span></div>
                            @endif
                        </td>

                        <!-- Rules -->
                        <td>
                            <div class="small">
                                @if($coupon->min_spend > 0)
                                    <div class="text-muted"><i class="bi bi-cart me-1"></i>Min Spend: <strong>AED {{ number_format($coupon->min_spend) }}</strong></div>
                                @endif
                                @if($coupon->min_guests > 1)
                                    <div class="text-muted"><i class="bi bi-people me-1"></i>Min Guests: <strong>{{ $coupon->min_guests }}+</strong></div>
                                @endif
                                @if($coupon->first_time_only)
                                    <div class="text-danger fw-bold"><i class="bi bi-person-plus me-1"></i>First-time guests only</div>
                                @endif
                                @if(!$coupon->min_spend && $coupon->min_guests <= 1 && !$coupon->first_time_only)
                                    <span class="text-muted">No restrictions</span>
                                @endif
                            </div>
                        </td>

                        <!-- Usage Progress & Redemptions -->
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-bold view-usages-btn" data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}">
                                <i class="bi bi-eye me-1 text-primary"></i> {{ $coupon->used_count }} {{ $coupon->usage_limit ? "/ {$coupon->usage_limit}" : '' }}
                            </button>
                            @if($coupon->usage_limit)
                                <div class="progress mt-1 mx-auto" style="height: 4px; width: 80px;">
                                    <div class="progress-bar {{ $limitReached ? 'bg-danger' : 'bg-primary' }}" style="width: {{ min(100, $pctUsed) }}%"></div>
                                </div>
                            @endif
                        </td>

                        <!-- Validity Window -->
                        <td class="text-center">
                            @if($isExpired)
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i>Expired</span>
                                <small class="text-muted d-block mt-1" style="font-size:0.7rem;">{{ $coupon->valid_until->format('M j, Y') }}</small>
                            @elseif($isScheduled)
                                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1"><i class="bi bi-calendar-event me-1"></i>Starts {{ $coupon->valid_from->format('M j') }}</span>
                            @elseif($coupon->valid_until)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i>Until {{ $coupon->valid_until->format('M j, Y') }}</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">No Expiry</span>
                            @endif
                        </td>

                        <!-- Status Switch -->
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input status-toggle-switch" type="checkbox" role="switch" data-id="{{ $coupon->id }}" {{ $coupon->status === 'active' && !$isExpired && !$limitReached ? 'checked' : '' }} {{ $isExpired || $limitReached ? 'disabled' : '' }}>
                            </div>
                        </td>

                        <!-- Action Buttons -->
                        <td class="text-end pe-3">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-light border rounded-circle p-2 text-primary" title="Edit Promo" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                                </a>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle p-2 shadow-none" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="More Actions" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2" style="z-index: 1065; min-width: 200px;">
                                        <li>
                                            <a class="dropdown-item rounded-3 py-2 fw-bold" href="{{ route('admin.coupons.edit', $coupon->id) }}">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit Promo
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.coupons.duplicate', $coupon->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item rounded-3 py-2 fw-bold">
                                                    <i class="bi bi-copy me-2 text-info"></i> Duplicate Promo
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold view-usages-btn" data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}">
                                                <i class="bi bi-receipt me-2 text-success"></i> View Redemptions ({{ $coupon->used_count }})
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="delete-coupon-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item rounded-3 py-2 fw-bold text-danger delete-coupon-btn" data-code="{{ $coupon->code }}">
                                                    <i class="bi bi-trash me-2"></i> Archive Promo
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted opacity-50 mb-3"><i class="bi bi-ticket-perforated fs-1"></i></div>
                            <h5 class="fw-bold text-dark">No Promo Codes Found</h5>
                            <p class="text-muted small mb-3">Create high-converting coupons to boost bookings and reward returning travelers.</p>
                            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Create First Promo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Redemptions Audit Modal -->
<div class="modal fade" id="couponUsagesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div>
                    <h5 class="modal-title fw-800 text-dark mb-0" id="modalCouponTitle">Coupon Redemptions</h5>
                    <small class="text-muted" id="modalCouponSubtitle">Customer usage audit trail</small>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalCouponBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small fw-bold">Loading redemption logs...</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1-Click Code Copy
    document.querySelectorAll('.copy-code-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const code = this.dataset.code;
            navigator.clipboard.writeText(code).then(() => {
                const icon = this.querySelector('i');
                icon.className = 'bi bi-check-lg text-success';
                setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 1500);
            });
        });
    });

    // AJAX Toggle Status Switch
    document.querySelectorAll('.status-toggle-switch').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const couponId = this.dataset.id;
            const originalState = !this.checked;

            fetch(`/admin/coupons/${couponId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    this.checked = originalState;
                    Swal.fire('Error', data.message || 'Failed to update status.', 'error');
                }
            })
            .catch(() => {
                this.checked = originalState;
                Swal.fire('Error', 'Network error occurred.', 'error');
            });
        });
    });

    // Usages Audit Modal
    const usagesModal = new bootstrap.Modal(document.getElementById('couponUsagesModal'));
    document.querySelectorAll('.view-usages-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const couponId = this.dataset.id;
            const code = this.dataset.code;
            document.getElementById('modalCouponTitle').innerText = `Redemptions for ${code}`;
            document.getElementById('modalCouponBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small fw-bold">Loading redemption logs...</div>
                </div>
            `;
            usagesModal.show();

            fetch(`/admin/coupons/${couponId}/usages`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.usages.length > 0) {
                        let html = `
                            <div class="table-responsive">
                                <table class="table align-middle table-sm small mb-0">
                                    <thead class="bg-light text-muted text-uppercase">
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Booking Ref</th>
                                            <th class="text-end">Discount</th>
                                            <th class="text-end">Order Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        data.usages.forEach(u => {
                            const dateStr = new Date(u.used_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                            html += `
                                <tr>
                                    <td>${dateStr}</td>
                                    <td>
                                        <div class="fw-bold text-dark">${u.customer_name || 'Guest'}</div>
                                        <div class="text-muted">${u.customer_email}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">${u.booking_reference || ('#' + u.booking_id)}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">-AED ${parseFloat(u.discount_amount).toFixed(2)}</td>
                                    <td class="text-end fw-800 text-dark">AED ${parseFloat(u.order_final_total).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table></div>`;
                        document.getElementById('modalCouponBody').innerHTML = html;
                    } else {
                        document.getElementById('modalCouponBody').innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 mb-2 d-block opacity-50"></i>
                                <div class="fw-bold">No redemptions yet</div>
                                <small>This promo code has not been redeemed by any customers so far.</small>
                            </div>
                        `;
                    }
                })
                .catch(() => {
                    document.getElementById('modalCouponBody').innerHTML = `<div class="alert alert-danger mb-0">Failed to load redemptions log.</div>`;
                });
        });
    });

    // Delete Confirmation
    document.querySelectorAll('.delete-coupon-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = this.closest('.delete-coupon-form');
            const code = this.dataset.code;

            Swal.fire({
                title: `Archive Promo ${code}?`,
                text: "This promo code will be deactivated and archived from the active promotions list.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Archive It'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
@endsection
