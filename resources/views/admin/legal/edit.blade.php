@extends('layouts.admin')

@section('page_title', 'Edit Legal Page: ' . $page->title)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="{{ route('admin.legal.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-chevron-left me-1"></i> Back to Legal Pages
    </a>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 font-monospace">/{{ $page->slug }}</span>
        <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Page Meta Info Form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-800 text-dark mb-4 d-flex align-items-center justify-content-between">
                <span>Page Information</span>
                <span class="badge bg-light text-muted border rounded-pill small fw-normal">Bilingual (EN / AR)</span>
            </h5>
            <form action="{{ route('admin.legal.update', $page->id) }}" method="POST">
                @csrf
                
                <!-- English Fields -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <span class="badge bg-primary text-white mb-2">English (Official)</span>
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold text-dark small">Page Title (EN)</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $page->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="subtitle" class="form-label fw-bold text-dark small">Subtitle (EN)</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control" value="{{ old('subtitle', $page->subtitle) }}">
                    </div>
                    <div>
                        <label for="description" class="form-label fw-bold text-dark small">Introductory Description (EN)</label>
                        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $page->description) }}</textarea>
                    </div>
                </div>

                <!-- Arabic Fields -->
                <div class="p-3 bg-light rounded-3 mb-4 border" dir="rtl">
                    <span class="badge bg-success text-white mb-2" style="direction: ltr;">العربية (النسخة القانونية)</span>
                    <div class="mb-3">
                        <label for="title_ar" class="form-label fw-bold text-dark small">عنوان الصفحة (عربي)</label>
                        <input type="text" name="title_ar" id="title_ar" class="form-control" value="{{ old('title_ar', $page->title_ar) }}">
                    </div>
                    <div class="mb-3">
                        <label for="subtitle_ar" class="form-label fw-bold text-dark small">العنوان الفرعي (عربي)</label>
                        <input type="text" name="subtitle_ar" id="subtitle_ar" class="form-control" value="{{ old('subtitle_ar', $page->subtitle_ar) }}">
                    </div>
                    <div>
                        <label for="description_ar" class="form-label fw-bold text-dark small">المقدمة والوصف (عربي)</label>
                        <textarea name="description_ar" id="description_ar" class="form-control" rows="4">{{ old('description_ar', $page->description_ar) }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold w-100">
                    <i class="bi bi-check2-circle me-1"></i> Save Page Details
                </button>
            </form>
        </div>
    </div>

    <!-- Sections & Clauses -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-800 text-dark mb-0">Page Sections & Clauses ({{ $page->sections->count() }})</h5>
            </div>

            @forelse($page->sections as $sec)
            <div class="p-3 bg-light rounded-4 border mb-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-secondary rounded-pill me-2">Order: {{ $sec->priority }}</span>
                        <strong class="text-dark fs-6">{{ $sec->heading }}</strong>
                        @if($sec->heading_ar)
                            <div class="text-primary small fw-semibold" dir="rtl">{{ $sec->heading_ar }}</div>
                        @endif
                        @if($sec->subheading)
                            <div class="text-muted small ms-1">{{ $sec->subheading }}</div>
                        @endif
                    </div>
                    <form action="{{ route('admin.legal.section.delete', $sec->id) }}" method="POST" class="delete-form" data-confirm="Are you sure you want to delete this section and all its items?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width:32px; height:32px;" title="Delete Section"><i class="bi bi-trash"></i></button>
                    </form>
                </div>

                <!-- Clauses List -->
                <div class="ms-2 border-start ps-3 my-3">
                    @forelse($sec->items as $item)
                    <div class="d-flex justify-content-between align-items-start mb-2 bg-white p-2 rounded-3 border">
                        <div class="small text-dark flex-grow-1">
                            <div>{{ $item->content }}</div>
                            @if($item->content_ar)
                                <div class="text-secondary small mt-1" dir="rtl">{{ $item->content_ar }}</div>
                            @endif
                        </div>
                        <form action="{{ route('admin.legal.item.delete', $item->id) }}" method="POST" class="ms-2 delete-form" data-confirm="Are you sure you want to delete this clause item?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete Item"><i class="bi bi-x-circle"></i></button>
                        </form>
                    </div>
                    @empty
                    <span class="text-muted small">No items under this section.</span>
                    @endforelse
                </div>

                <!-- Add Item Form (Bilingual) -->
                <form action="{{ route('admin.legal.item.add', $sec->id) }}" method="POST" class="mt-3 border-top pt-2">
                    @csrf
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <input type="text" name="content" class="form-control form-control-sm" placeholder="English clause text..." required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="content_ar" class="form-control form-control-sm" placeholder="نص البند بالعربية..." dir="rtl">
                        </div>
                        <div class="col-md-1">
                            <input type="number" name="priority" class="form-control form-control-sm" value="99">
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">+ Item</button>
                        </div>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-muted small text-center py-4">No sections defined yet for this page.</p>
            @endforelse

            <!-- Add Section Form (Bilingual) -->
            <div class="p-3 bg-white border rounded-4 mt-3">
                <h6 class="fw-bold text-dark mb-3">Add New Section Header</h6>
                <form action="{{ route('admin.legal.section.add', $page->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="heading" class="form-control form-control-sm" placeholder="Section Heading (EN)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="heading_ar" class="form-control form-control-sm" placeholder="عنوان القسم (عربي)" dir="rtl">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="subheading" class="form-control form-control-sm" placeholder="Subheading (EN - optional)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="subheading_ar" class="form-control form-control-sm" placeholder="العنوان الفرعي (عربي - اختياري)" dir="rtl">
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
