@extends('layouts.admin')

@section('page_title', $template->exists ? 'Edit Email Template: ' . $template->name : 'Create Email Template')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-1" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-800 text-dark mb-0">
                    {{ $template->exists ? 'Edit Template: ' . $template->name : 'Create Responsive Email Template' }}
                </h4>
            </div>
            <div class="text-muted small ps-4 ms-2">HTML email layout with responsive styles and dynamic subscriber interpolation tags.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-bold">
                Cancel
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm" onclick="document.getElementById('templateForm').submit();">
                <i class="bi bi-check-lg me-1"></i> {{ $template->exists ? 'Save Changes' : 'Create Template' }}
            </button>
        </div>
    </div>

    <!-- Main Layout -->
    <form id="templateForm" action="{{ $template->exists ? route('admin.email-templates.update', $template->id) : route('admin.email-templates.store') }}" method="POST">
        @csrf
        @if($template->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <!-- Left Column: Form & Code Editor -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-800 text-dark mb-3">
                        <i class="bi bi-sliders2 text-primary me-2"></i>Template Details
                    </h5>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="templateName" class="form-control rounded-3 py-2 @error('name') is-invalid @enderror" placeholder="e.g. VIP Seasonal Offer, Monthly Digest" value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Default Subject Line <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="templateSubject" class="form-control rounded-3 py-2 @error('subject') is-invalid @enderror" placeholder="e.g. Special Dubai Desert Invitation for @{{first_name}}" value="{{ old('subject', $template->subject) }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Preheader / Preview Text</label>
                            <input type="text" name="preview_text" class="form-control rounded-3 py-2 @error('preview_text') is-invalid @enderror" placeholder="Short teaser seen in email clients..." value="{{ old('preview_text', $template->preview_text) }}">
                            @error('preview_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Personalization Tags Helper Card -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-800 text-dark mb-0">
                            <i class="bi bi-braces-asterisk text-primary me-2"></i>Personalization & Merge Tags
                        </h6>
                        <span class="text-muted extra-small">Click any tag pill to insert into editor</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pt-2">
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{first_name}}">@{{first_name}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{last_name}}">@{{last_name}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{subscriber_name}}">@{{subscriber_name}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{email}}">@{{email}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{unsubscribe_url}}">@{{unsubscribe_url}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{site_name}}">@{{site_name}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{site_url}}">@{{site_url}}</button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 font-monospace tag-pill" data-tag="@{{current_year}}">@{{current_year}}</button>
                    </div>
                </div>

                <!-- HTML Content Editor -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-800 text-dark mb-0">
                            <i class="bi bi-code-slash text-primary me-2"></i>HTML Email Content <span class="text-danger">*</span>
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" id="refreshPreviewBtn">
                                <i class="bi bi-arrow-repeat me-1"></i> Update Preview
                            </button>
                        </div>
                    </div>

                    @php
                        $defaultHtml = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{site_name}}</title>
<style>
body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
.email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; }
.header { background: #0a192f; padding: 32px 24px; text-align: center; }
.header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 0.5px; }
.header span { color: #F58F43; }
.content { padding: 32px 24px; color: #334155; line-height: 1.6; }
.content h2 { color: #0f172a; font-size: 20px; font-weight: 700; margin-top: 0; }
.btn { display: inline-block; background: #F58F43; color: #ffffff !important; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; margin: 20px 0; }
.footer { background: #f1f5f9; padding: 24px; text-align: center; font-size: 12px; color: #64748b; }
.footer a { color: #F58F43; text-decoration: underline; }
</style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>{{site_name}}</h1>
    </div>
    <div class="content">
        <h2>Hello {{first_name}},</h2>
        <p>We are delighted to share exclusive updates, seasonal desert experiences, and premium offers with you.</p>
        <p>Discover our top-rated adventures curated especially for your next journey in Dubai.</p>
        <div style="text-align: center;">
            <a href="{{site_url}}/tours" class="btn">Explore Experiences</a>
        </div>
    </div>
    <div class="footer">
        <p>&copy; {{current_year}} {{site_name}}. All rights reserved.</p>
        <p>You received this email because you subscribed to our marketing updates.</p>
        <p><a href="{{unsubscribe_url}}">Unsubscribe from this list</a></p>
    </div>
</div>
</body>
</html>';
                    @endphp
                    <textarea name="content_html" id="contentHtml" class="form-control font-monospace rounded-3 p-3 @error('content_html') is-invalid @enderror" rows="22" style="font-size: 0.82rem; line-height: 1.5; background: #0f172a; color: #38bdf8;" required>{{ old('content_html', $template->content_html ?: $defaultHtml) }}</textarea>
                    @error('content_html')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="mt-4">
                        <label class="form-label fw-bold small text-dark">Plain Text Fallback (Optional)</label>
                        <textarea name="content_plain" class="form-control font-monospace rounded-3 p-2.5 text-muted" rows="4" placeholder="Plain text version for text-only email clients...">{{ old('content_plain', $template->content_plain) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Live Preview -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 sticky-top" style="top: 85px; z-index: 10;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-800 text-dark mb-0">
                            <i class="bi bi-eye-fill text-primary me-2"></i>Live Device Preview
                        </h6>
                        <div class="btn-group btn-group-sm bg-light rounded-pill p-0.5 border" role="group">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="deviceDesktopBtn">
                                <i class="bi bi-display me-1"></i> Desktop
                            </button>
                            <button type="button" class="btn btn-sm text-secondary rounded-pill px-3" id="deviceMobileBtn">
                                <i class="bi bi-phone me-1"></i> Mobile (375px)
                            </button>
                        </div>
                    </div>

                    <!-- Sandbox Iframe Container -->
                    <div id="livePreviewContainer" class="rounded-3 border overflow-hidden bg-light d-flex justify-content-center align-items-center transition-all" style="min-height: 600px;">
                        <iframe id="livePreviewIframe" class="w-100 bg-white border-0 shadow-sm transition-all" style="height: 600px; width: 100%;" sandbox="allow-same-origin"></iframe>
                    </div>
                    <div class="text-muted extra-small text-center mt-2">
                        Preview interpolates sample subscriber: <span class="fw-bold text-dark">Alex Turner (alex.turner@example.com)</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('contentHtml');
    const iframe = document.getElementById('livePreviewIframe');
    const container = document.getElementById('livePreviewContainer');
    const desktopBtn = document.getElementById('deviceDesktopBtn');
    const mobileBtn = document.getElementById('deviceMobileBtn');
    const refreshBtn = document.getElementById('refreshPreviewBtn');

    function updateLivePreview() {
        if (!editor || !iframe) return;

        let rawHtml = editor.value;

        // Sample tags interpolation for preview
        const dummyTags = {
            '@{{subscriber_name}}': 'Alex Turner',
            '@{{first_name}}': 'Alex',
            '@{{last_name}}': 'Turner',
            '@{{email}}': 'alex.turner@example.com',
            '@{{unsubscribe_url}}': '#unsubscribe-sample',
            '@{{site_name}}': 'Dunes Discovery Tourism',
            '@{{site_url}}': window.location.origin,
            '@{{current_year}}': new Date().getFullYear().toString()
        };

        for (const [tag, val] of Object.entries(dummyTags)) {
            rawHtml = rawHtml.replaceAll(tag, val);
        }

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(rawHtml);
        doc.close();
    }

    // Insert tag helper
    document.querySelectorAll('.tag-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            const tag = this.dataset.tag;
            const startPos = editor.selectionStart;
            const endPos = editor.selectionEnd;
            const currentVal = editor.value;

            editor.value = currentVal.substring(0, startPos) + tag + currentVal.substring(endPos, currentVal.length);
            editor.focus();
            editor.selectionStart = editor.selectionEnd = startPos + tag.length;

            updateLivePreview();
        });
    });

    // Auto-update on typing with debounce
    let debounceTimer;
    editor.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateLivePreview, 400);
    });

    refreshBtn.addEventListener('click', updateLivePreview);

    // Device toggling
    desktopBtn.addEventListener('click', function() {
        container.style.maxWidth = '100%';
        iframe.style.width = '100%';
        desktopBtn.classList.remove('text-secondary');
        desktopBtn.classList.add('btn-primary');
        mobileBtn.classList.remove('btn-primary');
        mobileBtn.classList.add('text-secondary');
    });

    mobileBtn.addEventListener('click', function() {
        iframe.style.width = '375px';
        mobileBtn.classList.remove('text-secondary');
        mobileBtn.classList.add('btn-primary');
        desktopBtn.classList.remove('btn-primary');
        desktopBtn.classList.add('text-secondary');
    });

    // Initial render
    setTimeout(updateLivePreview, 300);
});
</script>
@endpush
@endsection
