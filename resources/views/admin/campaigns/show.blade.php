@extends('layouts.admin')

@section('page_title', 'Campaign Analytics: ' . $campaign->title)

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.campaigns.index') }}" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition shadow-2xs">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    {{ $campaign->title }}
                    @if($campaign->status === 'sent')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="bi bi-check2-all text-[11px]"></i> Completed
                        </span>
                    @elseif($campaign->status === 'sending')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                            <i class="bi bi-arrow-repeat spin text-[11px]"></i> Sending...
                        </span>
                    @elseif($campaign->status === 'scheduled')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            <i class="bi bi-clock-history text-[11px]"></i> Scheduled
                        </span>
                    @elseif($campaign->status === 'draft')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="bi bi-pencil-square text-[11px]"></i> Draft
                        </span>
                    @endif
                </h1>
            </div>
            <div class="text-xs text-slate-500 mt-1 pl-12">
                <strong class="text-slate-700">Subject:</strong> {{ $campaign->subject }}
                @if($campaign->sent_at)
                    &bull; Dispatched on {{ $campaign->sent_at->format('M d, Y h:i A') }}
                @elseif($campaign->scheduled_at)
                    &bull; Scheduled for {{ $campaign->scheduled_at->format('M d, Y h:i A') }}
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="$dispatch('open-test-email')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 hover:bg-sky-50 text-sky-700 text-xs font-bold transition cursor-pointer">
                <i class="bi bi-send-check"></i> Send Test Copy
            </button>
            @if(in_array($campaign->status, ['draft', 'scheduled']))
                <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="inline" id="sendNowForm">
                    @csrf
                    <button type="button" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition cursor-pointer" id="btnSendNow">
                        <i class="bi bi-send-fill"></i> Send Broadcast Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- 6 Analytics Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Audience</div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($campaign->total_recipients ?: $campaign->sent_count) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 truncate">
                {{ $campaign->target_type === 'all' ? 'All Active' : ($campaign->group ? $campaign->group->name : 'Segment') }}
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Delivered</div>
            <div class="text-2xl font-black text-primary">{{ number_format($campaign->sent_count) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">
                {{ $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100, 1) : 100 }}% delivery
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Opens (Unique)</div>
            <div class="text-2xl font-black text-sky-600">{{ number_format($campaign->opened_count) }}</div>
            <div class="text-[11px] text-sky-600 font-bold mt-1">
                {{ $campaign->open_rate }}% open rate
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Link Clicks</div>
            <div class="text-2xl font-black text-amber-500">{{ number_format($campaign->clicked_count) }}</div>
            <div class="text-[11px] text-amber-600 font-bold mt-1">
                {{ $campaign->click_rate }}% click rate
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bounces</div>
            <div class="text-2xl font-black text-rose-600">{{ number_format($campaign->bounced_count) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">
                {{ $campaign->bounce_rate }}% bounce rate
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Unsubscribed</div>
            <div class="text-2xl font-black text-slate-600">{{ number_format($campaign->unsubscribed_count) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">
                {{ $campaign->unsubscribe_rate }}% opt-out
            </div>
        </div>
    </div>

    <!-- Engagement Rates Progress Bars -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <h2 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5 mb-4">
            <i class="bi bi-graph-up-arrow text-primary"></i> Engagement Performance Benchmarks
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="bi bi-envelope-open text-sky-500"></i> Unique Open Rate
                    </span>
                    <span class="text-xs font-black text-sky-600">{{ $campaign->open_rate }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-sky-500 h-2.5 rounded-full" style="width: {{ $campaign->open_rate }}%;"></div>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Industry travel average: ~22% - 28%</div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="bi bi-cursor text-amber-500"></i> Click-to-Open Rate
                    </span>
                    <span class="text-xs font-black text-amber-500">{{ $campaign->click_rate }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-amber-400 h-2.5 rounded-full" style="width: {{ $campaign->click_rate }}%;"></div>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Industry travel average: ~2.5% - 4.5%</div>
            </div>
        </div>
    </div>

    <!-- Recipient Logs Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <form action="{{ route('admin.campaigns.show', $campaign->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-primary transition" placeholder="Search recipient email, name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="sm:col-span-4">
                <select name="log_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Delivery Statuses</option>
                    <option value="sent" {{ request('log_status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="opened" {{ request('log_status') === 'opened' ? 'selected' : '' }}>Opened</option>
                    <option value="clicked" {{ request('log_status') === 'clicked' ? 'selected' : '' }}>Clicked</option>
                    <option value="bounced" {{ request('log_status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                    <option value="failed" {{ request('log_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'log_status']))
                    <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Recipient Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Recipient</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Opened</th>
                        <th class="py-3 px-4">Clicked Links</th>
                        <th class="py-3 px-4">Dispatched At</th>
                        <th class="py-3 px-4 text-right pe-4">Diagnostic Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $log->subscriber ? $log->subscriber->full_name : 'Guest' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $log->subscriber ? $log->subscriber->email : 'N/A' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($log->status === 'clicked')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="bi bi-cursor text-[11px]"></i> Clicked
                                </span>
                            @elseif($log->status === 'opened')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    <i class="bi bi-envelope-open text-[11px]"></i> Opened
                                </span>
                            @elseif($log->status === 'sent')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="bi bi-check2 text-[11px]"></i> Delivered
                                </span>
                            @elseif($log->status === 'bounced')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="bi bi-exclamation-octagon text-[11px]"></i> Bounced
                                </span>
                            @elseif($log->status === 'failed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="bi bi-x-circle text-[11px]"></i> Failed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize">
                                    {{ ucfirst($log->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($log->opened_at)
                                <div class="text-xs font-bold text-sky-600 flex items-center gap-1">
                                    <i class="bi bi-check2"></i> {{ $log->opened_at->format('M d, h:i A') }}
                                </div>
                                <div class="text-[10px] text-slate-400">{{ $log->opened_at->diffForHumans($log->sent_at, true) }} after send</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Not opened yet</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($log->clicks && $log->clicks->count() > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ $log->clicks->count() }} click(s)
                                </span>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5" title="{{ $log->clicks->first()->target_url ?? $log->clicks->first()->url }}">
                                    {{ $log->clicks->first()->target_url ?? $log->clicks->first()->url }}
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">No clicks</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500">
                            {{ $log->sent_at ? $log->sent_at->format('M d, h:i:s A') : 'Pending' }}
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            @if($log->error_message)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200 truncate max-w-xs" title="{{ $log->error_message }}">
                                    <i class="bi bi-bug"></i> {{ $log->error_message }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[10px] bg-slate-100 text-slate-500 border border-slate-200">
                                    #{{ substr($log->tracking_token, 0, 10) }}...
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400 text-xs">
                            <i class="bi bi-inbox text-4xl block mb-2 opacity-50"></i>
                            <h3 class="font-bold text-slate-700 mb-1">No Recipient Logs Found</h3>
                            <span class="text-slate-400">Logs will appear once the campaign is dispatched to your subscribers.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100 bg-white">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Test Email (Alpine.js) -->
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
                        <h3 class="text-base font-black text-slate-900">Send Test Copy</h3>
                        <div class="text-[11px] text-slate-400">Dispatch a live preview of this campaign to your inbox</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Recipient Email Address:</label>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSendNow = document.getElementById('btnSendNow');
    if (btnSendNow) {
        btnSendNow.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('sendNowForm');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Dispatch Broadcast Campaign?',
                    text: 'Are you sure you want to broadcast this campaign to all recipients now? Batches will dispatch immediately.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Send Broadcast!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Send broadcast campaign now?')) {
                    form.submit();
                }
            }
        });
    }
});

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
        const response = await fetch('{{ route("admin.campaigns.send-test", $campaign->id) }}', {
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
            resultBox.textContent = data.message || 'Diagnostic copy sent successfully! Check your inbox.';
        } else {
            resultBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-700 border border-rose-200';
            resultBox.textContent = data.message || 'Failed to dispatch test email. Check your SMTP settings.';
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
</script>
@endpush
@endsection
