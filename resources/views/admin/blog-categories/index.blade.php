@extends('layouts.admin')

@section('page_title', 'Blog Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Blog Categories</h2>
    <button class="btn btn-primary rounded-pill px-4 fw-800" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-lg me-2"></i> Add New Category
    </button>
</div>

<!-- Categories Table -->
<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Category Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark fs-6">{{ $cat->name }}</div>
                        </td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td><span class="text-muted small">{{ $cat->description ?: '-' }}</span></td>
                        <td class="fw-bold text-dark">{{ $cat->priority }}</td>
                        <td>
                            @if($cat->status === 'active')
                                <span class="badge bg-success text-capitalize px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center edit-cat-btn" 
                                        style="width: 32px; height: 32px;" 
                                        title="Edit Category"
                                        data-id="{{ $cat->id }}"
                                        data-name="{{ $cat->name }}"
                                        data-desc="{{ $cat->description }}"
                                        data-priority="{{ $cat->priority }}"
                                        data-status="{{ $cat->status }}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.blog-categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Category" onclick="return confirm('Delete this blog category?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No categories defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Add Blog Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.blog-categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="c_name" class="form-label fw-bold text-dark">Category Name</label>
                        <input type="text" name="name" id="c_name" class="form-control" required placeholder="e.g. Travel Guides">
                    </div>
                    <div class="mb-3">
                        <label for="c_desc" class="form-label fw-bold text-dark">Description</label>
                        <input type="text" name="description" id="c_desc" class="form-control" placeholder="Short description of category content">
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
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark">Edit Blog Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="e_name" class="form-label fw-bold text-dark">Category Name</label>
                        <input type="text" name="name" id="e_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="e_desc" class="form-label fw-bold text-dark">Description</label>
                        <input type="text" name="description" id="e_desc" class="form-control">
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
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.edit-cat-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const desc = $(this).data('desc');
        const priority = $(this).data('priority');
        const status = $(this).data('status');

        $('#e_name').val(name);
        $('#e_desc').val(desc);
        $('#e_priority').val(priority);
        $('#e_status').val(status);

        // Update form action
        $('#editCategoryForm').attr('action', `/admin/blog-categories/${id}`);

        // Open modal
        const myModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        myModal.show();
    });
});
</script>
@endpush
@endsection
