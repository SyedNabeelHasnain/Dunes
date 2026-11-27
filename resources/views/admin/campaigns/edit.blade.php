@extends('layouts.admin')

@section('page_title', 'Edit Campaign: ' . $campaign->title)

@section('content')
<div class="space-y-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition shadow-2xs">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Email Campaign</h1>
                <p class="text-xs text-slate-500 mt-0.5">Update draft or scheduled campaign content, audience targeting, and sender headers.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                Cancel
            </a>
            <button type="submit" form="campaignEditForm" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="bi bi-check2-circle"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="campaignEditForm" action="{{ route('admin.campaigns.update', $campaign->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="bi bi-people-fill text-primary"></i> 1. Target Audience
                    </h2>
                    @php
                        $selectedTarget = old('target_type', $campaign->target_type ?: 'all');
                        $selectedGroup = old('group_id', $campaign->group_id);
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex items-start gap-3 transition {{ $selectedTarget === 'all' ? 'border-primary bg-primary/5' : 'border-slate-200 bg-slate-50/50' }}" id="targetAllCard">
                            <input class="rounded-full text-primary focus:ring-primary w-4 h-4 mt-0.5" type="radio" name="target_type" id="targetAll" value="all" {{ $selectedTarget === 'all' ? 'checked' : '' }} onchange="handleEditTargetChange()">
                            <div>
                                <div class="font-black text-slate-900 text-xs">All Active Subscribers</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ number_format($totalActiveSubscribers) }} active subscribed recipients</div>
                            </div>
                        </label>
                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex items-start gap-3 transition {{ $selectedTarget === 'group' ? 'border-primary bg-primary/5' : 'border-slate-200 bg-slate-50/50' }}" id="targetGroupCard">
                            <input class="rounded-full text-primary focus:ring-primary w-4 h-4 mt-0.5" type="radio" name="target_type" id="targetGroup" value="group" {{ $selectedTarget === 'group' ? 'checked' : '' }} onchange="handleEditTargetChange()">
                            <div>
                                <div class="font-black text-slate-900 text-xs">Specific Segment Group</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $groups->count() }} available groups</div>
                            </div>
                        </label>
                    </div>

                    <div class="{{ $selectedTarget === 'group' ? '' : 'hidden' }} pt-2" id="groupSelectorWrapper">
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Audience Group *</label>
                        <select name="group_id" id="groupSelect" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                            <option value="">Choose a group...</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp->id }}" {{ (string) $selectedGroup === (string) $grp->id ? 'selected' : '' }}>
                                    {{ $grp->name }} ({{ number_format($grp->active_count) }} active subscribers)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="bi bi-envelope-paper-fill text-primary"></i> 2. Campaign Headers &amp; Schedule
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Campaign Internal Title *</label>
                            <input type="text" name="title" value="{{ old('title', $campaign->title) }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                            <div class="sm:col-span-8">
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Subject Line *</label>
                                <input type="text" name="subject" id="campaignSubject" value="{{ old('subject', $campaign->subject) }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Preheader Teaser</label>
                                <input type="text" name="preview_text" value="{{ old('preview_text', $campaign->preview_text) }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">From Name</label>
                                <input type="text" name="from_name" value="{{ old('from_name', $campaign->from_name ?: $defaultFromName) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">From Email</label>
                                <input type="email" name="from_email" value="{{ old('from_email', $campaign->from_email ?: $defaultFromAddress) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">Reply-To Email</label>
                                <input type="email" name="reply_to" value="{{ old('reply_to', $campaign->reply_to ?: $defaultReplyTo) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-1.5">Scheduled Dispatch Time (Optional — leave blank to keep as Draft)</label>
                            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '') }}" class="w-full sm:w-72 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-3">
                        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="bi bi-file-earmark-code-fill text-primary"></i> 3. Email HTML Content
                        </h2>
                        <select name="template_id" id="templatePicker" onchange="loadEditTemplate()" class="rounded-xl border border-primary/40 px-3 py-1.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                            <option value="">Keep current / Load Template...</option>
                            @foreach($templates as $tmpl)
                                <option value="{{ $tmpl->id }}" {{ (string) old('template_id', $campaign->template_id) === (string) $tmpl->id ? 'selected' : '' }}>{{ $tmpl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <script type="application/json" id="templatesData">
                        {!! json_encode($templates->keyBy('id'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
                    </script>
                    <textarea name="content_html" id="campaignHtml" rows="20" required class="w-full font-mono rounded-xl p-4 text-xs bg-slate-950 text-sky-300 border border-slate-800 outline-hidden focus:ring-1 focus:ring-primary" style="line-height: 1.6;">{{ old('content_html', $campaign->content_html) }}</textarea>
                </div>
            </div>

            <div class="lg:col-span-5 sticky top-24">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-3">
                    <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <i class="bi bi-eye-fill text-primary"></i> Live Preview
                    </h3>
                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 p-2" style="min-height: 540px;">
                        <iframe id="campaignPreviewIframe" class="w-full bg-white rounded-lg border border-slate-200" style="height: 540px;" sandbox="allow-same-origin"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const editTemplatesMap = JSON.parse(document.getElementById('templatesData')?.textContent || '{}');
const editEditor = document.getElementById('campaignHtml');
const editIframe = document.getElementById('campaignPreviewIframe');

function handleEditTargetChange() {
    const isGroup = document.getElementById('targetGroup').checked;
    document.getElementById('groupSelectorWrapper').classList.toggle('hidden', !isGroup);
}

function loadEditTemplate() {
    const val = document.getElementById('templatePicker').value;
    if (val && editTemplatesMap[val] && confirm('Replace current HTML content with the selected template?')) {
        editEditor.value = editTemplatesMap[val].content_html || '';
        updateEditPreview();
    }
}

function updateEditPreview() {
    if (!editEditor || !editIframe) return;
    let rawHtml = editEditor.value;
    const dummyTags = {
        '@{{subscriber_name}}': 'Alex Turner',
        '@{{first_name}}': 'Alex',
        '@{{last_name}}': 'Turner',
        '@{{email}}': 'alex.turner@example.com',
        '@{{unsubscribe_url}}': '#unsubscribe-sample',
        '@{{site_name}}': @json($defaultFromName),
        '@{{site_url}}': window.location.origin,
        '@{{current_year}}': new Date().getFullYear().toString()
    };
    for (const [tag, val] of Object.entries(dummyTags)) {
        rawHtml = rawHtml.replaceAll(tag, val);
    }
    const doc = editIframe.contentDocument || editIframe.contentWindow.document;
    doc.open();
    doc.write(rawHtml);
    doc.close();
}

document.addEventListener('DOMContentLoaded', function() {
    let t;
    editEditor?.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(updateEditPreview, 350);
    });
    setTimeout(updateEditPreview, 250);
});
</script>
@endpush
@endsection
