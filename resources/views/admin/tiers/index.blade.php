@extends('layouts.admin')

@section('page_title', 'Pricing Tiers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Pricing Tiers Structure</h2>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="tiersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tier Identity</th>
                        <th>Slug</th>
                        <th>Adoption</th>
                        <th>Status</th>
                        <th class="pe-4">Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiers as $tier)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light text-primary d-flex align-items-center justify-content-center rounded-circle border" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-{{ $tier->icon ?: 'star-fill' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $tier->display_name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $tier->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $tier->slug }}</code></td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold">
                                {{ $tier->tours->count() }} Tours Attached
                            </span>
                        </td>
                        <td>
                            @if($tier->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4 fw-bold text-dark">
                            {{ $tier->priority }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No pricing tiers defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
