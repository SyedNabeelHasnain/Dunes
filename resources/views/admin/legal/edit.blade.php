@extends('layouts.admin')

@section('page_title', 'Edit Legal Page: ' . $page->title)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="{{ route('admin.legal.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-chevron-left me-1"></i> Back to Legal Pages
    </a>
    <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
@endif

<div class="row g-4 mb-5">
    <!-- Page Meta Info -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-800 text-dark mb-4">Page Information</h5>
            <form action="{{ route('admin.legal.update', $page->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold text-dark">Page Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ $page->title }}" required>
                </div>
                <div class="mb-3">
                    <label for="subtitle" class="form-label fw-bold text-dark">Subtitle</label>
                    <input type="text" name="subtitle" id="subtitle" class="form-control" value="{{ $page->subtitle }}">
                </div>
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold text-dark">Introductory Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4">{{ $page->description }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold w-100">Save Page Details</button>
            </form>
        </div>
    </div>

    <!-- Sections & Clauses -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-800 text-dark mb-0">Page Sections & Clauses</h5>
            </div>

            @forelse($page->sections as $sec)
            <div class="p-3 bg-light rounded-4 border mb-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-secondary rounded-pill me-2">Order: {{ $sec->priority }}</span>
                        <strong class="text-dark fs-6">{{ $sec->heading }}</strong>
                        @if($sec->subheading)
                            <div class="text-muted small ms-1">{{ $sec->subheading }}</div>
                        @endif
                    </div>
                    <form action="{{ route('admin.legal.section.delete', $sec->id) }}" method="POST" onsubmit="return confirm('Delete this section and all its items?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width:32px; height:32px;"><i class="bi bi-trash"></i></button>
                    </form>
                </div>

                <!-- Clauses List -->
                <div class="ms-3 border-start ps-3 my-3">
                    @forelse($sec->items as $item)
                    <div class="d-flex justify-content-between align-items-start mb-2 bg-white p-2 rounded-3 border">
                        <span class="small text-dark">{{ $item->content }}</span>
                        <form action="{{ route('admin.legal.item.delete', $item->id) }}" method="POST" class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger p-0 border-0"><i class="bi bi-x-circle"></i></button>
                        </form>
                    </div>
                    @empty
                    <span class="text-muted small">No items under this section.</span>
                    @endforelse
                </div>

                <!-- Add Item Form -->
                <form action="{{ route('admin.legal.item.add', $sec->id) }}" method="POST" class="mt-3 border-top pt-2">
                    @csrf
                    <div class="row g-2 align-items-center">
                        <div class="col-8">
                            <input type="text" name="content" class="form-control form-control-sm" placeholder="Add clause text / bullet point..." required>
                        </div>
                        <div class="col-2">
                            <input type="number" name="priority" class="form-control form-control-sm" value="99">
                        </div>
                        <div class="col-2 text-end">
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">+ Item</button>
                        </div>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-muted small text-center py-4">No sections defined yet for this page.</p>
            @endforelse

            <!-- Add Section Form -->
            <div class="p-3 bg-white border rounded-4 mt-3">
                <h6 class="fw-bold text-dark mb-3">Add New Section Header</h6>
                <form action="{{ route('admin.legal.section.add', $page->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="heading" class="form-control form-control-sm" placeholder="Section Heading (e.g. Booking Terms)" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="subheading" class="form-control form-control-sm" placeholder="Subheading (optional)">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="priority" class="form-control form-control-sm" value="99" required>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">+ Add Section</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
