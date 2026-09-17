@extends('layouts.admin')

@section('page_title', 'Email Templates Gallery')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-800 text-dark mb-1">
                <i class="bi bi-envelope-paper-heart-fill text-primary me-2"></i>Email Templates Gallery
            </h4>
            <div class="text-muted small">Design and customize mobile-responsive HTML email templates with dynamic personalization tags.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-send-plus"></i> New Campaign
            </a>
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Create Template
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row g-4">
        @forelse($templates as $template)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white d-flex flex-column overflow-hidden position-relative">
                <div class="card-header bg-light border-0 p-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        @if($template->is_system)
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                <i class="bi bi-shield-check me-1"></i> System Default
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-1 small fw-bold">
                                <i class="bi bi-palette me-1"></i> Custom Layout
                            </span>
                        @endif
                    </div>
                    <span class="text-muted extra-small">
                        <i class="bi bi-megaphone me-1"></i> {{ $template->campaigns_count }} campaign(s)
                    </span>
                </div>

                <div class="card-body p-4 flex-grow-1">
                    <h5 class="fw-800 text-dark mb-1 text-truncate" title="{{ $template->name }}">{{ $template->name }}</h5>
                    <div class="text-muted small mb-3 text-truncate">
                        <strong>Subject:</strong> {{ $template->subject }}
                    </div>
                    
                    @if($template->preview_text)
                    <div class="p-2.5 px-3 bg-light rounded-3 text-muted extra-small mb-3 text-truncate border">
                        <i class="bi bi-eye text-primary me-1"></i> {{ $template->preview_text }}
                    </div>
                    @endif

                    <!-- Visual Mini Thumbnail Frame -->
                    <div class="border rounded-3 overflow-hidden bg-light position-relative mb-3" style="height: 160px;">
                        <iframe src="{{ route('admin.email-templates.preview', $template->id) }}" class="w-100 h-100 border-0 pointer-events-none" style="transform: scale(0.65); transform-origin: top left; width: 154%; height: 154%; pointer-events: none;" loading="lazy"></iframe>
                        <div class="position-absolute inset-0 bg-transparent" style="cursor: pointer;" onclick="openTemplatePreview({{ $template->id }}, '{{ addslashes($template->name) }}')"></div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-muted extra-small">
                        <span><i class="bi bi-clock-history me-1"></i> Updated {{ $template->updated_at->diffForHumans() }}</span>
                        <span class="font-monospace text-secondary">#{{ $template->slug }}</span>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 px-4 d-flex align-items-center justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold" onclick="openTemplatePreview({{ $template->id }}, '{{ addslashes($template->name) }}')">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('admin.campaigns.create', ['template_id' => $template->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2.5" title="Start Campaign with this template">
                            <i class="bi bi-send-plus"></i>
                        </a>
                        <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" title="Edit Template">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(!$template->is_system)
                        <form action="{{ route('admin.email-templates.destroy', $template->id) }}" method="POST" class="d-inline delete-template-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 btn-delete-template" data-name="{{ $template->name }}" title="Delete Template">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-envelope-paper text-muted display-4 mb-3 opacity-50"></i>
                <h5 class="fw-bold text-dark">No Email Templates Found</h5>
                <p class="text-muted small mb-4">Start by creating your first responsive email marketing template.</p>
                <div>
                    <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Create Template
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Template Live Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-envelope-check text-primary fs-4"></i>
                    <div>
                        <h6 class="modal-title fw-800 text-white mb-0" id="previewModalLabel">Template Preview</h6>
                        <span class="extra-small text-white-50">Live rendered with dynamic tags populated</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm bg-black rounded-pill p-0.5 border border-secondary" role="group">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="previewDesktopBtn" onclick="setPreviewDevice('desktop')">
                            <i class="bi bi-display me-1"></i> Desktop
                        </button>
                        <button type="button" class="btn btn-sm text-white rounded-pill px-3" id="previewMobileBtn" onclick="setPreviewDevice('mobile')">
                            <i class="bi bi-phone me-1"></i> Mobile
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10 d-flex justify-content-center align-items-center" style="min-height: 540px;">
                <div id="previewContainer" class="w-100 transition-all p-2 p-md-4" style="max-width: 100%; transition: max-width 0.3s ease;">
                    <iframe id="previewIframe" src="about:blank" class="w-100 rounded-3 shadow border bg-white" style="height: 650px;" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
const previewIframe = document.getElementById('previewIframe');
const previewLabel = document.getElementById('previewModalLabel');
const previewContainer = document.getElementById('previewContainer');
const previewDesktopBtn = document.getElementById('previewDesktopBtn');
const previewMobileBtn = document.getElementById('previewMobileBtn');

function openTemplatePreview(templateId, templateName) {
    previewLabel.textContent = `Preview: ${templateName}`;
    previewIframe.src = `/admin/email-templates/${templateId}/preview`;
    setPreviewDevice('desktop');
    previewModal.show();
}

function setPreviewDevice(device) {
    if (device === 'mobile') {
        previewContainer.style.maxWidth = '420px';
        previewMobileBtn.classList.remove('text-white');
        previewMobileBtn.classList.add('btn-primary');
        previewDesktopBtn.classList.remove('btn-primary');
        previewDesktopBtn.classList.add('text-white');
    } else {
        previewContainer.style.maxWidth = '100%';
        previewDesktopBtn.classList.remove('text-white');
        previewDesktopBtn.classList.add('btn-primary');
        previewMobileBtn.classList.remove('btn-primary');
        previewMobileBtn.classList.add('text-white');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-template').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.dataset.name;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Template?',
                    text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Are you sure you want to delete "${name}"?`)) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
