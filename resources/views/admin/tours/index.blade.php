@extends('layouts.admin')

@section('page_title', 'Tours')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Tour Inventory</h2>
    <a href="{{ route('admin.tours.create') }}" class="btn btn-primary rounded-pill px-4 fw-800"><i class="bi bi-plus-lg me-2"></i> Add New Tour</a>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="toursTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tour Name</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $t)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark fs-6">{{ $t->name }}</div>
                            <div class="text-muted small">/{{ $t->slug }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold small text-uppercase">
                                {{ $t->category ? str_replace('-', ' ', $t->category->name) : 'No Category' }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium text-muted">{{ $t->duration }}</div>
                        </td>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                @if($t->status === 'active')
                                    <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                                @else
                                    <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Hidden</span>
                                @endif
                                
                                @if($t->is_bestseller)
                                    <span class="badge bg-warning text-white px-2 py-1 rounded-pill"><i class="bi bi-fire me-1"></i>Best</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.tours.edit', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Tour Details">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="{{ route('tours.show', $t->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Live Preview">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <form action="{{ route('admin.tours.destroy', $t->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Tour" onclick="return confirm('Are you sure you want to delete this tour and all its associations?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No tours found in inventory.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
