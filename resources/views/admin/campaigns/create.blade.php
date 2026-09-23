@extends('layouts.admin')

@section('page_title', 'Create Marketing Campaign')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.campaigns.index') }}" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition shadow-2xs">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Launch Email Marketing Campaign</h1>
                <p class="text-xs text-slate-500 mt-0.5">Configure campaign audience, template, sender branding, and dispatches.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="$dispatch('open-test-email')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 hover:bg-sky-50 text-sky-700 text-xs font-bold transition cursor-pointer">
                <i class="bi bi-send-check"></i> Send Test Email
            </button>
            <button type="button" @click="$dispatch('open-schedule-modal')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-amber-200 hover:bg-amber-50 text-amber-700 text-xs font-bold transition cursor-pointer">
                <i class="bi bi-clock-history"></i> Schedule
            </button>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition cursor-pointer" onclick="submitCampaignForm('save_draft')">
                <i class="bi bi-save"></i> Save Draft
            </button>
            <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer" onclick="confirmDispatch()">
                <i class="bi bi-send-fill"></i> Send Campaign Now
            </button>
        </div>
    </div>

    <!-- Main Campaign Form -->
    <form id="campaignForm" action="{{ route('admin.campaigns.store') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="campaignAction" value="save_draft">
        <input type="hidden" name="scheduled_at" id="campaignScheduledAt" value="">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Settings & Content (Col 7) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Step 1: Audience Targeting -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="bi bi-people-fill text-primary"></i> 1. Select Target Audience
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="p-4 rounded-2xl border-2 border-primary bg-primary/5 cursor-pointer flex items-start gap-3 transition target-card" id="targetAllCard">
                            <input class="rounded-full text-primary focus:ring-primary w-4 h-4 mt-0.5" type="radio" name="target_type" id="targetAll" value="all" checked onchange="handleTargetChange()">
                            <div>
                                <div class="font-black text-slate-900 text-xs">All Active Subscribers</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Broadcast to every active, unsubscribed recipient</div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary text-white">
                                        {{ number_format($totalActiveSubscribers) }} Recipients
                                    </span>
                                </div>
                            </div>
                        </label>

                        <label class="p-4 rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-50 cursor-pointer flex items-start gap-3 transition target-card" id="targetGroupCard">
                            <input class="rounded-full text-primary focus:ring-primary w-4 h-4 mt-0.5" type="radio" name="target_type" id="targetGroup" value="group" onchange="handleTargetChange()">
                            <div>
                                <div class="font-black text-slate-900 text-xs">Specific Segment Group</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Target a specific tagged customer or inquiry list</div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                        {{ $groups->count() }} Available Groups
                                    </span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="hidden pt-2" id="groupSelectorWrapper">
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Select Audience Group *</label>
                        <select name="group_id" id="groupSelect" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                            <option value="">Choose a group...</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp->id }}" data-count="{{ $grp->active_count }}">
                                    {{ $grp->name }} ({{ number_format($grp->active_count) }} active subscribers)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Step 2: Campaign Identity & Subject -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="bi bi-envelope-paper-fill text-primary"></i> 2. Campaign Headers & Subject
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Campaign Internal Title *</label>
                            <input type="text" name="title" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('title') border-rose-300 ring-rose-200 @enderror" placeholder="e.g. October 2026 VIP Sunset Desert Safari Promo" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                            <div class="sm:col-span-8">
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Subject Line *</label>
                                <input type="text" name="subject" id="campaignSubject" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('subject') border-rose-300 ring-rose-200 @enderror" placeholder="e.g. Special Dubai Experience for @{{first_name}}!" value="{{ old('subject') }}" required>
                                @error('subject')
                                    <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Preheader Teaser</label>
                                <input type="text" name="preview_text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Inbox summary snippet..." value="{{ old('preview_text') }}">
                            </div>
                        </div>

                        <!-- Sender Branding -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">From Name</label>
                                <input type="text" name="from_name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ old('from_name', $defaultFromName) }}">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">From Email</label>
                                <input type="email" name="from_email" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ old('from_email', $defaultFromAddress) }}">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">Reply-To Email</label>
                                <input type="email" name="reply_to" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ old('reply_to', $defaultReplyTo) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Template & HTML Content -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-3">
                        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="bi bi-file-earmark-code-fill text-primary"></i> 3. Design & Content
                        </h2>
                        <div class="flex items-center gap-2">
                            <select class="rounded-xl border border-primary/40 px-3 py-1.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary" id="templatePicker" onchange="loadSelectedTemplate()">
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
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase text-slate-500 mr-1">Insert Tag:</span>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{first_name}}">@{{first_name}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{last_name}}">@{{last_name}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{subscriber_name}}">@{{subscriber_name}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{email}}">@{{email}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{unsubscribe_url}}">@{{unsubscribe_url}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{site_name}}">@{{site_name}}</button>
                        <button type="button" class="px-2 py-0.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200 transition cursor-pointer tag-pill" data-tag="@{{current_year}}">@{{current_year}}</button>
                    </div>

                    <!-- Hidden container storing templates json for instantaneous loading -->
                    <script type="application/json" id="templatesData">
                        {!! json_encode($templates->keyBy('id')) !!}
                    </script>

                    <textarea name="content_html" id="campaignHtml" class="w-full font-mono rounded-xl p-4 text-xs bg-slate-950 text-sky-300 border border-slate-800 outline-hidden focus:ring-1 focus:ring-primary @error('content_html') border-rose-500 @enderror" rows="22" style="line-height: 1.6;" required>{{ old('content_html', $templates->first() ? $templates->first()->content_html : '') }}</textarea>
                    @error('content_html')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Right Column: Live Responsive Preview (Col 5) -->
            <div class="lg:col-span-5 sticky top-24 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-eye-fill text-primary"></i> Live Device Preview
                        </h3>
                        <div class="inline-flex items-center bg-slate-100 rounded-xl p-0.5 border border-slate-200 text-xs">
                            <button type="button" class="px-2.5 py-1 rounded-lg font-bold bg-primary text-white shadow-2xs transition cursor-pointer" id="previewDesktopBtn">
                                <i class="bi bi-display mr-1"></i> Desktop
                            </button>
                            <button type="button" class="px-2.5 py-1 rounded-lg font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer" id="previewMobileBtn">
                                <i class="bi bi-phone mr-1"></i> Mobile
                            </button>
                        </div>
                    </div>

                    <div id="previewWrapper" class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 flex justify-center items-center transition-all p-2" style="min-height: 580px;">
                        <iframe id="campaignPreviewIframe" class="w-full bg-white rounded-lg border border-slate-200 shadow-sm transition-all" style="height: 580px; width: 100%;" sandbox="allow-same-origin"></iframe>
                    </div>

                    <div class="p-3 bg-sky-50 rounded-xl border border-sky-200 flex items-start gap-2.5 text-xs text-sky-800">
                        <i class="bi bi-info-circle-fill text-sky-500 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Automatic Tracking:</strong> Tracking pixels for email opens and link click redirects are automatically injected upon dispatch.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Diagnostic Test Email (Alpine.js) -->
<div x-data="{ open: false }" @open-test-email.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-200">
                        <i class="bi bi-send-check-fill text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Send Test Email</h3>
                        <div class="text-[11px] text-slate-400">Verify template rendering in your personal inbox</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Send Diagnostic Copy To:</label>
                    <input type="email" id="testRecipientEmail" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="yourname@domain.com" value="{{ auth()->user()->email ?? '' }}">
                </div>
                <div id="testResultBox" class="hidden p-3 rounded-xl text-xs"></div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Close</button>
                <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs transition cursor-pointer" id="btnSendTest" onclick="sendDiagnosticTestEmail()">
                    <i class="bi bi-send"></i> Send Test
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Schedule Broadcast (Alpine.js) -->
<div x-data="{ open: false }" @open-schedule-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                        <i class="bi bi-clock-history text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Schedule Email Broadcast</h3>
                        <div class="text-[11px] text-slate-400">Automate campaign delivery at a future date and time</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Broadcast Date & Time (Dubai Local Time / UTC+4):</label>
                    <input type="datetime-local" id="scheduleDatetimeInput" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                    <div class="text-slate-400 text-[11px] mt-1">The system scheduler scans and broadcasts automatically every 5 minutes.</div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition cursor-pointer" onclick="confirmSchedule()">
                    <i class="bi bi-calendar-check"></i> Confirm Schedule
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
        groupWrapper.classList.remove('hidden');
        groupCard.classList.remove('border-slate-200', 'bg-slate-50/50');
        groupCard.classList.add('border-primary', 'bg-primary/5');
        allCard.classList.remove('border-primary', 'bg-primary/5');
        allCard.classList.add('border-slate-200', 'bg-slate-50/50');
    } else {
        groupWrapper.classList.add('hidden');
        allCard.classList.remove('border-slate-200', 'bg-slate-50/50');
        allCard.classList.add('border-primary', 'bg-primary/5');
        groupCard.classList.remove('border-primary', 'bg-primary/5');
        groupCard.classList.add('border-slate-200', 'bg-slate-50/50');
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
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
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
    submitCampaignForm('schedule');
}

async function sendDiagnosticTestEmail() {
    const emailInput = document.getElementById('testRecipientEmail');
    const resultBox = document.getElementById('testResultBox');
    const sendBtn = document.getElementById('btnSendTest');

    const email = emailInput.value.trim();
    if (!email) {
        resultBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-700 border border-rose-200';
        resultBox.textContent = 'Please enter a valid email address.';
        resultBox.classList.remove('hidden');
        return;
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Sending...';
    resultBox.classList.add('hidden');

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
            resultBox.className = 'p-3 rounded-xl text-xs bg-emerald-50 text-emerald-700 border border-emerald-200';
            resultBox.textContent = data.message || 'Diagnostic test email sent successfully! Check your inbox.';
        } else {
            resultBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-700 border border-rose-200';
            resultBox.textContent = data.message || 'Failed to dispatch test email. Check your SMTP server settings.';
        }
        resultBox.classList.remove('hidden');
    } catch (err) {
        resultBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-700 border border-rose-200';
        resultBox.textContent = 'Network error while attempting test dispatch.';
        resultBox.classList.remove('hidden');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send mr-1"></i> Send Test';
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

    if (desktopBtn && mobileBtn) {
        desktopBtn.addEventListener('click', function() {
            previewWrapper.style.maxWidth = '100%';
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
