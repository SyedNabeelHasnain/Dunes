@extends('layouts.admin')

@section('page_title', 'Daily Tour Operations & Logistics Hub')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1"><i class="bi bi-truck-flatbed text-primary me-2"></i>Daily Tour Operations & Dispatch Hub</h2>
        <p class="text-muted small mb-0">Cluster hotel pickups by Dubai zones, allocate 4x4 safari vehicles, and broadcast driver details to guests.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <!-- Date Selector Shortcuts -->
        <div class="btn-group rounded-pill shadow-sm bg-white p-1 border">
            <a href="{{ route('admin.operations.index', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-sm {{ $selectedDate === now()->format('Y-m-d') ? 'btn-primary text-white fw-bold' : 'btn-light text-dark' }} rounded-pill px-3">
                Today ({{ now()->format('M j') }})
            </a>
            <a href="{{ route('admin.operations.index', ['date' => now()->addDay()->format('Y-m-d')]) }}" class="btn btn-sm {{ $selectedDate === now()->addDay()->format('Y-m-d') ? 'btn-primary text-white fw-bold' : 'btn-light text-dark' }} rounded-pill px-3">
                Tomorrow ({{ now()->addDay()->format('M j') }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.operations.index') }}" class="d-flex align-items-center gap-2">
            <input type="date" name="date" class="form-control form-control-sm rounded-pill border shadow-none" value="{{ $selectedDate }}" onchange="this.form.submit()" style="height: 38px;">
        </form>

        <a href="{{ route('admin.operations.export', ['date' => $selectedDate]) }}" class="btn btn-white border-0 shadow-sm rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-spreadsheet text-success fs-5"></i>
            <span>Export Manifest</span>
        </a>
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer"></i>
            <span>Print Dispatch</span>
        </button>
    </div>
</div>

<!-- 4 Operations Operational Metric Cards -->
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Pickups</span>
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bi bi-geo-alt-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ number_format($stats['total_bookings']) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Bookings scheduled for {{ $targetDate->format('M j, Y') }}</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Total Guests</span>
                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-people-fill fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-primary mb-0">{{ number_format($stats['total_guests']) }}</h3>
            <span class="text-muted small" style="font-size: 0.75rem;">{{ $stats['total_adults'] }} Adults, {{ $stats['total_children'] }} Children</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">4x4 Vehicles Needed</span>
                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-truck fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-dark mb-0">{{ $stats['vehicles_needed'] }} <small class="fs-6 fw-normal text-muted">Vehicles</small></h3>
            <span class="text-muted small" style="font-size: 0.75rem;">Est. capacity 6 guests / Land Cruiser</span>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card card-modern h-100 p-3 bg-white border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem;">Confirmed Status</span>
                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-check-all fs-5"></i></span>
            </div>
            <h3 class="fw-800 text-success mb-0">{{ $stats['confirmed_count'] }} <span class="fs-6 fw-normal text-muted">/ {{ $stats['total_bookings'] }}</span></h3>
            <span class="text-muted small" style="font-size: 0.75rem;">{{ $stats['pending_count'] }} pending confirmations</span>
        </div>
    </div>
</div>

<!-- Zone Clusters Accordion / Sections -->
@php $hasAnyBookings = false; @endphp
@foreach($zones as $zoneName => $zoneBookings)
    @if(count($zoneBookings) > 0)
        @php $hasAnyBookings = true; @endphp
        <div class="card card-modern bg-white border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold"><i class="bi bi-geo-alt me-1"></i>{{ $zoneName }}</span>
                    <span class="fw-bold text-dark">{{ count($zoneBookings) }} Booking{{ count($zoneBookings) > 1 ? 's' : '' }}</span>
                </div>
                <div class="small text-muted fw-bold">
                    Total Guests: {{ array_sum(array_map(fn($b) => (int)$b->adults + (int)$b->children, $zoneBookings)) }}
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light small text-uppercase fw-bold text-muted" style="font-size: 0.72rem;">
                            <tr>
                                <th class="ps-4">Ref & Time</th>
                                <th>Guest Contact</th>
                                <th>Hotel / Pickup Location</th>
                                <th>Tour & Package</th>
                                <th class="text-center">Guests</th>
                                <th>Add-ons</th>
                                <th>Driver & Vehicle</th>
                                <th class="pe-4 text-end">Broadcast</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($zoneBookings as $b)
                            @php
                                $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                $pickupTime = $b->pickup_time ?: '3:00 PM - 3:30 PM';
                                $driverName = 'Assigned Driver';
                                $plateNum = 'Land Cruiser';
                                
                                if ($b->special_requests && preg_match('/\[DISPATCH:\s*Driver:\s*([^\|]+)\s*\|\s*Phone:\s*([^\|]+)\s*\|\s*Plate:\s*([^\]]+)\]/i', $b->special_requests, $m)) {
                                    $driverName = trim($m[1]);
                                    $plateNum = trim($m[3]);
                                }

                                $dispatchMsg = "Hi {$b->name}! Your Dunes Discovery Tourism safari captain {$driverName} in {$plateNum} will pick you up at {$b->pickup_location} around {$pickupTime} on {$targetDate->format('M j')}. See you in the dunes! 🏜️";
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-800 text-dark">#{{ $b->reference }}</div>
                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.7rem;">{{ $b->status }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $b->name }}</div>
                                    <div class="text-muted small font-monospace">{{ $b->phone }}</div>
                                    <a href="https://wa.me/{{ $waVal }}" target="_blank" class="small text-success text-decoration-none fw-bold"><i class="bi bi-whatsapp me-1"></i>Chat</a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="bi bi-building me-1 text-primary"></i>{{ $b->pickup_location }}</div>
                                    <div class="text-muted small"><i class="bi bi-clock me-1"></i>Pickup: <strong>{{ $pickupTime }}</strong></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $b->tour_name }}</div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.72rem;">{{ $b->tier_name }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark">{{ (int)$b->adults + (int)$b->children }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $b->adults }}A / {{ $b->children }}C</small>
                                </td>
                                <td>
                                    @if($b->addons && $b->addons->count() > 0)
                                        @foreach($b->addons as $addon)
                                            <span class="badge bg-success-subtle text-success rounded-pill mb-1 d-inline-block" style="font-size: 0.7rem;">
                                                <i class="bi bi-plus-circle me-1"></i>{{ $addon->addon_name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">None</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-bold edit-driver-btn" data-id="{{ $b->id }}" data-ref="{{ $b->reference }}" data-time="{{ $b->pickup_time }}" data-notes="{{ htmlspecialchars($b->special_requests ?? '') }}">
                                        <i class="bi bi-person-badge text-primary me-1"></i>
                                        <span>{{ $driverName !== 'Assigned Driver' ? $driverName : 'Assign Driver' }}</span>
                                    </button>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($dispatchMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-sm d-inline-flex align-items-center gap-1" title="Send WhatsApp Pickup Notice">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Send Notice</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endforeach

@if(!$hasAnyBookings)
<div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-5 text-center mb-4">
    <i class="bi bi-calendar2-check fs-1 text-muted opacity-50 mb-3 d-block"></i>
    <h5 class="fw-bold text-dark">No Tour Pickups Scheduled for {{ $targetDate->format('M j, Y') }}</h5>
    <p class="text-muted small mb-3">All scheduled bookings for this date will be grouped automatically into Dubai logistics zones.</p>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">View All Bookings</a>
</div>
@endif

<!-- Driver Assignment Modal -->
<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="" id="assignDriverForm" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <h5 class="modal-title fw-800 text-dark mb-0">Assign Safari Captain & Vehicle</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Pickup Time Window</label>
                    <input type="text" name="pickup_time" id="modalPickupTime" class="form-control rounded-pill border shadow-none" placeholder="e.g. 3:00 PM - 3:30 PM">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Driver / Safari Captain Name</label>
                    <input type="text" name="driver_name" id="modalDriverName" class="form-control rounded-pill border shadow-none" placeholder="e.g. Captain Rashid Khan">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Driver Contact Phone</label>
                    <input type="text" name="driver_phone" id="modalDriverPhone" class="form-control rounded-pill border shadow-none" placeholder="e.g. +971 50 123 4567">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">4x4 Vehicle Plate / Model</label>
                    <input type="text" name="vehicle_plate" id="modalVehiclePlate" class="form-control rounded-pill border shadow-none" placeholder="e.g. Land Cruiser (Plate A 12345)">
                </div>
            </div>
            <div class="modal-footer border-top bg-light p-3">
                <button type="button" class="btn btn-light rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-800 text-white shadow-sm">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('assignDriverModal');
    const form = document.getElementById('assignDriverForm');
    const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    document.querySelectorAll('.edit-driver-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const time = this.dataset.time || '';
            const notes = this.dataset.notes || '';

            form.action = `/admin/operations/${id}/assign-driver`;
            document.getElementById('modalPickupTime').value = time;

            const m = notes.match(/\[DISPATCH:\s*Driver:\s*([^\|]+)\s*\|\s*Phone:\s*([^\|]+)\s*\|\s*Plate:\s*([^\]]+)\]/i);
            if (m) {
                document.getElementById('modalDriverName').value = m[1].trim();
                document.getElementById('modalDriverPhone').value = m[2].trim();
                document.getElementById('modalVehiclePlate').value = m[3].trim();
            } else {
                document.getElementById('modalDriverName').value = '';
                document.getElementById('modalDriverPhone').value = '';
                document.getElementById('modalVehiclePlate').value = '';
            }

            if (bsModal) bsModal.show();
        });
    });
});
</script>
@endpush
@endsection