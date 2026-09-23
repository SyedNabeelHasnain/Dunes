@extends('layouts.admin')

@section('page_title', 'Email Marketing Campaigns')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-megaphone-fill text-primary"></i> Email Marketing Campaigns
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Broadcast newsletters, flash promotions, and booking alerts with live engagement tracking.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-palette text-slate-500"></i> Templates Gallery
            </a>
            <a href="{{ route('admin.subscribers.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-people text-slate-500"></i> Subscribers
            </a>
            <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition">
                <i class="bi bi-plus-lg"></i> New Campaign
            </a>
        </div>
    </div>

    <!-- 4 Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                <i class="bi bi-broadcast text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Campaigns</div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total_campaigns']) }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                <i class="bi bi-send-check text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Emails Sent</div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['total_sent']) }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-200">
                <i class="bi bi-envelope-open text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Opens</div>
                <div class="text-2xl font-black text-sky-600">{{ number_format($stats['total_opened']) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    {{ $stats['total_sent'] > 0 ? round(($stats['total_opened'] / $stats['total_sent']) * 100, 1) : 0 }}% avg open
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 border border-amber-200">
                <i class="bi bi-cursor text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Clicks</div>
                <div class="text-2xl font-black text-amber-500">{{ number_format($stats['total_clicked']) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    {{ $stats['total_opened'] > 0 ? round(($stats['total_clicked'] / $stats['total_opened']) * 100, 1) : 0 }}% click-to-open
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="inline-flex p-1 bg-white rounded-2xl border border-slate-200/80 shadow-2xs gap-1">
            <a class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ !request('status') ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('admin.campaigns.index') }}">
                All
            </a>
            <a class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ request('status') === 'sent' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('admin.campaigns.index', ['status' => 'sent']) }}">
                Sent
            </a>
            <a class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ request('status') === 'draft' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('admin.campaigns.index', ['status' => 'draft']) }}">
                Drafts
            </a>
            <a class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ request('status') === 'sending' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('admin.campaigns.index', ['status' => 'sending']) }}">
                Sending
            </a>
            <a class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ request('status') === 'scheduled' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('admin.campaigns.index', ['status' => 'scheduled']) }}">
                Scheduled
            </a>
        </div>
    </div>

    <!-- Campaigns List Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Campaign & Subject</th>
                        <th class="py-3 px-4">Target Audience</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4" style="min-width: 140px;">Open Rate</th>
                        <th class="py-3 px-4" style="min-width: 140px;">Click Rate</th>
                        <th class="py-3 px-4 text-center">Sent / Dispatched</th>
                        <th class="py-3 px-4 text-right pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div>
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="font-black text-slate-900 text-xs hover:text-primary transition">
                                    {{ $campaign->title }}
                                </a>
                                <div class="text-slate-400 text-xs truncate max-w-xs mt-0.5">
                                    <strong class="text-slate-600">Subject:</strong> {{ $campaign->subject }}
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($campaign->target_type === 'all')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    <i class="bi bi-people-fill text-[11px]"></i> All Active Subscribers
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    <i class="bi bi-diagram-3-fill text-[11px]"></i> {{ $campaign->group ? $campaign->group->name : 'Audience Segment' }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($campaign->status === 'sent')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="bi bi-check2-all text-[11px]"></i> Completed
                                </span>
                            @elseif($campaign->status === 'sending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                    <i class="bi bi-arrow-repeat spin text-[11px]"></i> Sending...
                                </span>
                            @elseif($campaign->status === 'scheduled')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200" title="{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y h:i A') : '' }}">
                                    <i class="bi bi-clock-history text-[11px]"></i> Scheduled
                                </span>
                            @elseif($campaign->status === 'draft')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="bi bi-pencil-square text-[11px]"></i> Draft
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize">
                                    {{ $campaign->status }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-sky-500 h-1.5 rounded-full" style="width: {{ $campaign->open_rate }}%;"></div>
                                </div>
                                <span class="font-bold text-xs text-slate-800">{{ $campaign->open_rate }}%</span>
                            </div>
                            <div class="text-slate-400 text-[11px] mt-0.5">
                                {{ number_format($campaign->opened_count) }} / {{ number_format($campaign->sent_count) }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $campaign->click_rate }}%;"></div>
                                </div>
                                <span class="font-bold text-xs text-slate-800">{{ $campaign->click_rate }}%</span>
                            </div>
                            <div class="text-slate-400 text-[11px] mt-0.5">
                                {{ number_format($campaign->clicked_count) }} clicks
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center text-xs text-slate-500">
                            @if($campaign->sent_at)
                                <div class="font-medium text-slate-800">{{ $campaign->sent_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $campaign->sent_at->format('h:i A') }}</div>
                            @else
                                <span class="text-slate-400 italic">Not dispatched</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="View Analytics & Recipients">
                                    <i class="bi bi-graph-up text-xs"></i>
                                </a>
                                @if($campaign->status === 'draft')
                                <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="inline send-campaign-form">
                                    @csrf
                                    <button type="button" class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center bg-white transition shadow-2xs btn-send-campaign cursor-pointer" data-title="{{ $campaign->title }}" title="Send Now">
                                        <i class="bi bi-send text-xs"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="inline delete-campaign-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-campaign cursor-pointer" data-title="{{ $campaign->title }}" title="Delete Campaign">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400 text-xs">
                            <i class="bi bi-megaphone text-4xl block mb-2 opacity-50"></i>
                            <h3 class="font-bold text-slate-700 mb-1">No Campaigns Found</h3>
                            <p class="text-slate-400 mb-4">Launch your first marketing campaign to engage with your subscribers.</p>
                            <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition">
                                <i class="bi bi-plus-lg"></i> Create Campaign
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
        <div class="p-4 border-t border-slate-100 bg-white">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-send-campaign').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const title = this.dataset.title;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Dispatch Campaign Now?',
                    text: `Are you ready to send "${title}" to its target audience? Dispatches are processed in batches.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Send Now!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Send "${title}" now?`)) {
                    form.submit();
                }
            }
        });
    });

    document.querySelectorAll('.btn-delete-campaign').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const title = this.dataset.title;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Campaign?',
                    text: `Are you sure you want to delete "${title}"? All campaign recipient logs and click statistics will be removed.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Delete "${title}"?`)) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
