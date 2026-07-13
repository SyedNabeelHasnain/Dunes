@extends('layouts.admin')

@section('page_title', 'Addons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Tour Addons</h2>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="addonsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Addon Name</th>
                        <th>Slug</th>
                        <th>Default Price</th>
                        <th>Adoption</th>
                        <th>Status</th>
                        <th class="pe-4">Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($addons as $addon)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light text-success d-flex align-items-center justify-content-center rounded-circle border" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-{{ $addon->icon ?: 'plus-lg' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $addon->name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $addon->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $addon->slug }}</code></td>
                        <td class="fw-bold text-dark">AED {{ number_format($addon->default_price, 2) }}</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">
                                {{ $addon->tours->count() }} Tours Attached
                            </span>
                        </td>
                        <td>
                            @if($addon->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4 fw-bold text-dark">
                            {{ $addon->priority }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No addons defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
