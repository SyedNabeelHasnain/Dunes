@extends('layouts.admin')

@section('page_title', 'Create Marketing Campaign')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-1" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-800 text-dark mb-0">Launch Email Marketing Campaign</h4>
            </div>
            <div class="text-muted small ps-4 ms-2">Configure campaign audience, template, sender branding, and dispatches.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-info rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                <i class="bi bi-send-check"></i> Send Test Email
            </button>
            <button type="button" class="btn btn-outline-warning text-dark rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                <i class="bi bi-clock-history text-warning"></i> Schedule
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 btn-sm fw-bold" onclick="submitCampaignForm('save_draft')">
                <i class="bi bi-save me-1"></i> Save Draft
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" onclick="confirmDispatch()">
                <i class="bi bi-send-fill"></i> Send Campaign Now
            </button>
        </div>
    </div>

    <!-- Main Campaign Form -->
    <form id="campaignForm" action="{{ route('admin.campaigns.store') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="campaignAction" value="save_draft">
        <input type="hidden" name="scheduled_at" id="campaignScheduledAt" value="">

        <div class="row g-4">
            <!-- Left Column: Settings & Content -->
            <div class="col-12 col-xl-7">
                <!-- Step 1: Audience Targeting -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-800 text-dark mb-3">
                        <i class="bi bi-people-fill text-primary me-2"></i>1. Select Target Audience
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-check card p-3 rounded-4 border-2 border-primary bg-primary bg-opacity-10 cursor-pointer h-100 target-card" id="targetAllCard">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="target_type" id="targetAll" value="all" checked onchange="handleTargetChange()">
                                    <div>
                                        <div class="fw-800 text-dark">All Active Subscribers</div>
                                        <div class="text-muted extra-small">Broadcast to every active, unsubscribed recipient</div>
                                        <div class="mt-2">
                                            <span class="badge bg-primary rounded-pill px-2.5 py-1">
                                                {{ number_format($totalActiveSubscribers) }} Recipients
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-check card p-3 rounded-4 border-2 border-light bg-light cursor-pointer h-100 target-card" id="targetGroupCard">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="target_type" id="targetGroup" value="group" onchange="handleTargetChange()">
                                    <div>
                                        <div class="fw-800 text-dark">Specific Segment Group</div>
                                        <div class="text-muted extra-small">Target a specific tagged customer or inquiry list</div>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary rounded-pill px-2.5 py-1">
                                                {{ $groups->count() }} Available Groups
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 d-none" id="groupSelectorWrapper">
                            <label class="form-label fw-bold small text-dark">Select Audience Group <span class="text-danger">*</span></label>
                            <select name="group_id" id="groupSelect" class="form-select rounded-3 py-2">
                                <option value="">Choose a group...</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->id }}" data-count="{{ $grp->active_count }}">
                                        {{ $grp->name }} ({{ number_format($grp->active_count) }} active subscribers)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Campaign Identity & Subject -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-800 text-dark mb-3">
                        <i class="bi bi-envelope-paper-fill text-primary me-2"></i>2. Campaign Headers & Subject
                    </h5>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Campaign Internal Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3 py-2 @error('title') is-invalid @enderror" placeholder="e.g. October 2026 VIP Sunset Desert Safari Promo" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold small text-dark">Email Subject Line <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="campaignSubject" class="form-control rounded-3 py-2 @error('subject') is-invalid @enderror" placeholder="e.g. Special Dubai Experience for @{{first_name}}!" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Preheader Teaser</label>
                            <input type="text" name="preview_text" class="form-control rounded-3 py-2" placeholder="Inbox summary snippet..." value="{{ old('preview_text') }}">
                        </div>

                        <!-- Sender Branding (Collapsible or visible) -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold extra-small text-muted text-uppercase">From Name</label>
                            <input type="text" name="from_name" class="form-control rounded-3 py-2 small" value="{{ old('from_name', $defaultFromName) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold extra-small text-muted text-uppercase">From Email</label>
                            <input type="email" name="from_email" class="form-control rounded-3 py-2 small" value="{{ old('from_email', $defaultFromAddress) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold extra-small text-muted text-uppercase">Reply-To Email</label>
                            <input type="email" name="reply_to" class="form-control rounded-3 py-2 small" value="{{ old('reply_to', $defaultReplyTo) }}">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Template & HTML Content -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="bi bi-file-earmark-code-fill text-primary me-2"></i>3. Design & Content
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm rounded-pill py-1 px-3 border-primary" id="templatePicker" onchange="loadSelectedTemplate()">
                                <option value="">Load from Template Library...</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ request('template_id') == $tmpl->id ? 'selected' : '' }} data-subject="{{ $tmpl->subject }}" data-preview="{{ $tmpl->preview_text }}">
                                        {{ $tmpl->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="template_id" id="hiddenTemplateId" value="{{ request('template_id') }}">
                        </div>
                    </div>

                    <!-- Merge Tags Helper -->
                    <div class="mb-3 p-2 bg-light rounded-3 d-flex flex-wrap gap-1.5 align-items-center border">
                        <span class="extra-small fw-bold text-muted me-1">Insert Tag:</span>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{first_name}}">@{{first_name}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{last_name}}">@{{last_name}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{subscriber_name}}">@{{subscriber_name}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{email}}">@{{email}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{unsubscribe_url}}">@{{unsubscribe_url}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{site_name}}">@{{site_name}}</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill px-2 py-0.5 font-monospace extra-small tag-pill" data-tag="@{{current_year}}">@{{current_year}}</button>
                    </div>

                    <!-- Hidden container storing templates json for instantaneous loading -->
                    <script type="application/json" id="templatesData">
                        {!! json_encode($templates->keyBy('id')) !!}
                    </script>

                    <textarea name="content_html" id="campaignHtml" class="form-control font-monospace rounded-3 p-3 @error('content_html') is-invalid @enderror" rows="22" style="font-size: 0.82rem; line-height: 1.5; background: #0f172a; color: #38bdf8;" required>{{ old('content_html', $templates->first() ? $templates->first()->content_html : '') }}</textarea>
                    @error('content_html')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Right Column: Live Responsive Preview -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 sticky-top" style="top: 85px; z-index: 10;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-800 text-dark mb-0">
                            <i class="bi bi-eye-fill text-primary me-2"></i>Live Device Preview
                        </h6>
                        <div class="btn-group btn-group-sm bg-light rounded-pill p-0.5 border" role="group">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="previewDesktopBtn">
                                <i class="bi bi-display me-1"></i> Desktop
                            </button>
                            <button type="button" class="btn btn-sm text-secondary rounded-pill px-3" id="previewMobileBtn">
                                <i class="bi bi-phone me-1"></i> Mobile
                            </button>
                        </div>
                    </div>

                    <div id="previewWrapper" class="rounded-3 border overflow-hidden bg-light d-flex justify-content-center align-items-center transition-all" style="min-height: 580px;">
                        <iframe id="campaignPreviewIframe" class="w-100 bg-white border-0 shadow-sm transition-all" style="height: 580px; width: 100%;" sandbox="allow-same-origin"></iframe>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 mt-3 mb-0 p-3 small d-flex gap-2">
                        <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>Automatic Tracking:</strong> Tracking pixels for email opens and link click redirects are automatically injected upon dispatch.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Diagnostic Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                        <i class="bi bi-send-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="testEmailModalLabel">Send Test Email</h5>
                        <div class="text-muted extra-small">Verify template rendering in your personal inbox</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Send Diagnostic Copy To:</label>
                    <input type="email" id="testRecipientEmail" class="form-control rounded-3 py-2" placeholder="yourname@domain.com" value="{{ auth()->user()->email ?? '' }}">
                </div>
                <div id="testResultBox" class="alert d-none rounded-3 small"></div>
            </div>
            <div class="modal-footer border-0 bg-light p-3 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info text-white rounded-pill px-4 fw-bold" id="btnSendTest" onclick="sendDiagnosticTestEmail()">
                    <i class="bi bi-send me-1"></i> Send Test
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Broadcast Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="scheduleModalLabel">Schedule Email Broadcast</h5>
                        <div class="text-muted extra-small">Automate campaign delivery at a future date and time</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Broadcast Date & Time (Dubai Local Time / UTC+4):</label>
                    <input type="datetime-local" id="scheduleDatetimeInput" class="form-control rounded-3 py-2" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                    <div class="form-text extra-small text-muted mt-1">The system scheduler scans and broadcasts automatically every 5 minutes.</div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-dark rounded-pill px-4 fw-bold" onclick="confirmSchedule()">
                    <i class="bi bi-calendar-check me-1"></i> Confirm Schedule
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const templatesMap = JSON.parse(document.getElementById('templatesData').textContent || '{}');
const editor = document.getElementById('campaignHtml');
const iframe = document.getElementById('campaignPreviewIframe');
const previewWrapper = document.getElementById('previewWrapper');
const desktopBtn = document.getElementById('previewDesktopBtn');
const mobileBtn = document.getElementById('previewMobileBtn');

function handleTargetChange() {
    const isGroup = document.getElementById('targetGroup').checked;
    const groupWrapper = document.getElementById('groupSelectorWrapper');
    const allCard = document.getElementById('targetAllCard');
    const groupCard = document.getElementById('targetGroupCard');

    if (isGroup) {
        groupWrapper.classList.remove('d-none');
        groupCard.classList.remove('border-light', 'bg-light');
        groupCard.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        allCard.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        allCard.classList.add('border-light', 'bg-light');
    } else {
        groupWrapper.classList.add('d-none');
        allCard.classList.remove('border-light', 'bg-light');
        allCard.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        groupCard.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        groupCard.classList.add('border-light', 'bg-light');
    }
}

function loadSelectedTemplate() {
    const picker = document.getElementById('templatePicker');
    const val = picker.value;
    document.getElementById('hiddenTemplateId').value = val;

    if (val && templatesMap[val]) {
        const tmpl = templatesMap[val];
        editor.value = tmpl.content_html;
        if (!document.getElementById('campaignSubject').value.trim()) {
            document.getElementById('campaignSubject').value = tmpl.subject;
        }
        updateLivePreview();
    }
}

function updateLivePreview() {
    if (!editor || !iframe) return;

    let rawHtml = editor.value;
    const dummyTags = {
        '@{{subscriber_name}}': 'Alex Turner',
        '@{{first_name}}': 'Alex',
        '@{{last_name}}': 'Turner',
        '@{{email}}': 'alex.turner@example.com',
        '@{{unsubscribe_url}}': '#unsubscribe-sample',
        '@{{site_name}}': '{{ addslashes($defaultFromName) }}',
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

function submitCampaignForm(actionVal) {
    document.getElementById('campaignAction').value = actionVal;
    document.getElementById('campaignForm').submit();
}

function confirmDispatch() {
    const isGroup = document.getElementById('targetGroup').checked;
    const groupSelect = document.getElementById('groupSelect');

    if (isGroup && !groupSelect.value) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Target Group Required',
                text: 'Please select a specific audience group to target.',
                icon: 'warning',
                confirmButtonColor: '#F58F43'
            });
        } else {
            alert('Please select a target group.');
        }
        return;
    }

    const title = document.querySelector('input[name="title"]').value.trim() || 'Campaign';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Broadcast Campaign Now?',
            text: `Are you ready to send "${title}"? Dispatches will start immediately and throttle through batches.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Send Immediately!'
        }).then((result) => {
            if (result.isConfirmed) {
                submitCampaignForm('send_now');
            }
        });
    } else {
        if (confirm(`Send "${title}" immediately?`)) {
            submitCampaignForm('send_now');
        }
    }
}

function confirmSchedule() {
    const isGroup = document.getElementById('targetGroup').checked;
    const groupSelect = document.getElementById('groupSelect');

    if (isGroup && !groupSelect.value) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Target Group Required',
                text: 'Please select a specific audience group to target before scheduling.',
                icon: 'warning',
                confirmButtonColor: '#F58F43'
            });
        } else {
            alert('Please select a target group.');
        }
        return;
    }

    const val = document.getElementById('scheduleDatetimeInput').value;
    if (!val) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Broadcast Time Required',
                text: 'Please select a future date and time for the broadcast.',
                icon: 'warning',
                confirmButtonColor: '#F58F43'
            });
        } else {
            alert('Please select a date and time.');
        }
        return;
    }

    document.getElementById('campaignScheduledAt').value = val;
    const modalEl = document.getElementById('scheduleModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();

    submitCampaignForm('schedule');
}

async function sendDiagnosticTestEmail() {
    const emailInput = document.getElementById('testRecipientEmail');
    const resultBox = document.getElementById('testResultBox');
    const sendBtn = document.getElementById('btnSendTest');

    const email = emailInput.value.trim();
    if (!email) {
        resultBox.className = 'alert alert-danger rounded-3 small';
        resultBox.textContent = 'Please enter a valid email address.';
        resultBox.classList.remove('d-none');
        return;
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
    resultBox.classList.add('d-none');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const response = await fetch('{{ route("admin.settings.mail.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ test_email: email })
        });

        const data = await response.json();
        if (data.success) {
            resultBox.className = 'alert alert-success rounded-3 small';
            resultBox.textContent = data.message || 'Diagnostic test email sent successfully! Check your inbox.';
        } else {
            resultBox.className = 'alert alert-danger rounded-3 small';
            resultBox.textContent = data.message || 'Failed to dispatch test email. Check your SMTP server settings.';
        }
        resultBox.classList.remove('d-none');
    } catch (err) {
        resultBox.className = 'alert alert-danger rounded-3 small';
        resultBox.textContent = 'Network error while attempting test dispatch.';
        resultBox.classList.remove('d-none');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send me-1"></i> Send Test';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Merge tag pills click
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

    // Auto-update preview on typing
    let debounceTimer;
    editor.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateLivePreview, 400);
    });

    desktopBtn.addEventListener('click', function() {
        previewWrapper.style.maxWidth = '100%';
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
