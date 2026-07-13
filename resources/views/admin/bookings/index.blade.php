@extends('layouts.admin')

@section('page_title', 'Bookings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Manage Bookings</h2>
    <!-- Optional filters or action buttons -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 fw-bold">All</a>
        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 fw-bold">Pending</a>
        <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ $status === 'confirmed' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 fw-bold">Confirmed</a>
        <a href="{{ route('admin.bookings.index', ['status' => 'completed']) }}" class="btn btn-sm {{ $status === 'completed' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 fw-bold">Completed</a>
        <a href="{{ route('admin.bookings.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ $status === 'cancelled' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 fw-bold">Cancelled</a>
    </div>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="bookingsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ref</th>
                        <th>Customer</th>
                        <th>Tour / Activity</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-800 text-dark">#{{ $b->reference }}</div>
                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $b->created_at->format('M j, g:ia') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->name }}</div>
                            <div class="text-muted small">{{ $b->phone }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $b->tour_name }}</div>
                            <div class="badge bg-light text-primary border rounded-pill mt-1" style="font-size: 0.7rem;">{{ $b->tier_name }}</div>
                        </td>
                        <td>
                            <div class="small fw-medium text-dark">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : '' }}</div>
                        </td>
                        <td class="fw-800 text-primary">AED {{ number_format($b->total) }}</td>
                        <td>
                            @php
                                $badgeColor = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ][$b->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $b->status }}</span>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="View Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @php
                                    $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                    $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                                @endphp
                                <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="WhatsApp Chat">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endsection
