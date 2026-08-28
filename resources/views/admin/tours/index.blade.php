@extends('layouts.admin')

@section('page_title', 'Tours Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Tours & Experiences Inventory</h2>
        <p class="text-muted small mb-0">Manage desert safari packages, activity durations, best-seller highlights, and live visibility.</p>
    </div>
    <a href="{{ route('admin.tours.create') }}" class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm"><i class="bi bi-plus-lg me-2"></i> Add New Tour</a>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="toursTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Tour Name & Slug</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th class="text-center">Status (Click to toggle)</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $t)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark fs-6">{{ $t->name }}</div>
                            <div class="text-muted small font-monospace">/{{ $t->slug }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold small text-uppercase">
                                {{ $t->category ? str_replace('-', ' ', $t->category->name) : 'General' }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium text-dark"><i class="bi bi-clock me-1 text-muted"></i>{{ $t->duration ?: 'Flexible' }}</div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 align-items-center justify-content-center">
                                @if($t->status === 'active')
                                    <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill badge-interactive ajax-toggle-status" data-url="{{ route('admin.tours.toggle-status', $t->id) }}" title="Click to toggle status">Active</span>
                                @else
                                    <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill badge-interactive ajax-toggle-status" data-url="{{ route('admin.tours.toggle-status', $t->id) }}" title="Click to toggle status">Hidden</span>
                                @endif
                                
                                @if($t->is_bestseller)
                                    <span class="badge bg-warning text-white px-2 py-1 rounded-pill" title="Featured Best Seller"><i class="bi bi-fire me-1"></i>Best</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.tours.edit', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Edit Tour Details">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="{{ route('tours.show', $t->slug) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Live Preview">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <form action="{{ route('admin.tours.destroy', $t->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete Tour" onclick="return confirm('Are you sure you want to delete this tour and all its associations?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No tours found in inventory.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
