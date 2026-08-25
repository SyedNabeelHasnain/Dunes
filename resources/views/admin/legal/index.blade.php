@extends('layouts.admin')

@section('page_title', 'Legal Pages & Policy Manager')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-800 text-dark mb-1">Legal & Policy Pages</h4>
        <p class="text-muted small mb-0">Manage Terms & Conditions, Privacy Policy content and legal disclosures.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
@endif

<div class="row g-4">
    @foreach($pages as $page)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold font-monospace">/{{ $page->slug }}</span>
                        <span class="badge bg-light text-muted border rounded-pill px-3">{{ $page->sections_count }} Sections</span>
                    </div>
                    <h5 class="fw-800 text-dark mb-2">{{ $page->title }}</h5>
                    <p class="text-muted small mb-4">{{ $page->subtitle ?: 'Standard site legal agreement disclosure.' }}</p>
                </div>

                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                    <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live
                    </a>
                    <a href="{{ route('admin.legal.edit', $page->id) }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Content
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
