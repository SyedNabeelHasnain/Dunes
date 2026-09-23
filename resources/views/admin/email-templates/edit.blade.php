@extends('layouts.admin')

@section('page_title', $template->exists ? 'Edit Email Template: ' . $template->name : 'Create Email Template')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.email-templates.index') }}" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition shadow-2xs">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">
                    {{ $template->exists ? 'Edit Template: ' . $template->name : 'Create Responsive Email Template' }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">HTML email layout with responsive styles and dynamic subscriber interpolation tags.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                Cancel
            </a>
            <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer" onclick="document.getElementById('templateForm').submit();">
                <i class="bi bi-check-lg"></i> {{ $template->exists ? 'Save Changes' : 'Create Template' }}
            </button>
        </div>
    </div>

    <!-- Main Layout -->
    <form id="templateForm" action="{{ $template->exists ? route('admin.email-templates.update', $template->id) : route('admin.email-templates.store') }}" method="POST">
        @csrf
        @if($template->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Form & Code Editor (Col 7) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Details Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="bi bi-sliders2 text-primary"></i> Template Details
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Template Name *</label>
                            <input type="text" name="name" id="templateName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('name') border-rose-300 ring-rose-200 @enderror" placeholder="e.g. VIP Seasonal Offer, Monthly Digest" value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Default Subject Line *</label>
                                <input type="text" name="subject" id="templateSubject" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('subject') border-rose-300 ring-rose-200 @enderror" placeholder="e.g. Special Dubai Desert Invitation for @{{first_name}}" value="{{ old('subject', $template->subject) }}" required>
                                @error('subject')
                                    <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Preheader / Preview Text</label>
                                <input type="text" name="preview_text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('preview_text') border-rose-300 ring-rose-200 @enderror" placeholder="Short teaser seen in email clients..." value="{{ old('preview_text', $template->preview_text) }}">
                                @error('preview_text')
                                    <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personalization Tags Helper Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-braces-asterisk text-primary"></i> Personalization & Merge Tags
                        </h3>
                        <span class="text-[11px] text-slate-400">Click any tag pill to insert into editor</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{first_name}}">@{{first_name}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{last_name}}">@{{last_name}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{subscriber_name}}">@{{subscriber_name}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{email}}">@{{email}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{unsubscribe_url}}">@{{unsubscribe_url}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{site_name}}">@{{site_name}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{site_url}}">@{{site_url}}</button>
                        <button type="button" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-semibold border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{current_year}}">@{{current_year}}</button>
                    </div>
                </div>

                <!-- HTML Content Editor -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-code-slash text-primary"></i> HTML Email Content <span class="text-rose-500">*</span>
                        </h3>
                        <button type="button" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-primary/30 hover:bg-primary/5 text-primary text-xs font-bold transition cursor-pointer" id="refreshPreviewBtn">
                            <i class="bi bi-arrow-repeat"></i> Update Preview
                        </button>
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
                    <textarea name="content_html" id="contentHtml" class="w-full font-mono rounded-xl p-4 text-xs bg-slate-950 text-sky-300 border border-slate-800 outline-hidden focus:ring-1 focus:ring-primary @error('content_html') border-rose-500 @enderror" rows="22" style="line-height: 1.6;" required>{{ old('content_html', $template->content_html ?: $defaultHtml) }}</textarea>
                    @error('content_html')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Plain Text Fallback (Optional)</label>
                        <textarea name="content_plain" class="w-full font-mono rounded-xl border border-slate-200 p-3 text-xs text-slate-700 outline-hidden focus:border-primary bg-slate-50/50" rows="4" placeholder="Plain text version for text-only email clients...">{{ old('content_plain', $template->content_plain) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Live Preview (Col 5) -->
            <div class="lg:col-span-5 sticky top-24">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-eye-fill text-primary"></i> Live Device Preview
                        </h3>
                        <div class="inline-flex items-center bg-slate-100 rounded-xl p-0.5 border border-slate-200 text-xs">
                            <button type="button" class="px-2.5 py-1 rounded-lg font-bold bg-primary text-white shadow-2xs transition cursor-pointer" id="deviceDesktopBtn">
                                <i class="bi bi-display mr-1"></i> Desktop
                            </button>
                            <button type="button" class="px-2.5 py-1 rounded-lg font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer" id="deviceMobileBtn">
                                <i class="bi bi-phone mr-1"></i> Mobile
                            </button>
                        </div>
                    </div>

                    <!-- Sandbox Iframe Container -->
                    <div id="livePreviewContainer" class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 flex justify-center items-center transition-all p-2" style="min-height: 600px;">
                        <iframe id="livePreviewIframe" class="w-full bg-white rounded-lg border border-slate-200 shadow-sm transition-all" style="height: 600px; width: 100%;" sandbox="allow-same-origin"></iframe>
                    </div>
                    <div class="text-slate-400 text-[11px] text-center">
                        Preview interpolates sample: <span class="font-bold text-slate-700">Alex Turner (alex.turner@example.com)</span>
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

    if (refreshBtn) {
        refreshBtn.addEventListener('click', updateLivePreview);
    }

    // Device toggling
    if (desktopBtn && mobileBtn) {
        desktopBtn.addEventListener('click', function() {
            iframe.style.width = '100%';
            desktopBtn.classList.remove('text-slate-600');
            desktopBtn.classList.add('bg-primary', 'text-white', 'shadow-2xs');
            mobileBtn.classList.remove('bg-primary', 'text-white', 'shadow-2xs');
            mobileBtn.classList.add('text-slate-600');
        });

        mobileBtn.addEventListener('click', function() {
            iframe.style.width = '375px';
            mobileBtn.classList.remove('text-slate-600');
            mobileBtn.classList.add('bg-primary', 'text-white', 'shadow-2xs');
            desktopBtn.classList.remove('bg-primary', 'text-white', 'shadow-2xs');
            desktopBtn.classList.add('text-slate-600');
        });
    }

    // Initial render
    setTimeout(updateLivePreview, 300);
});
</script>
@endpush
@endsection
