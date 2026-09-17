@extends('layouts.admin')

@section('page_title', 'Email Marketing Campaigns')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-800 text-dark mb-1">
                <i class="bi bi-megaphone-fill text-primary me-2"></i>Email Marketing Campaigns
            </h4>
            <div class="text-muted small">Broadcast newsletters, flash promotions, and booking alerts with live engagement tracking.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-palette"></i> Templates Gallery
            </a>
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> Subscribers
            </a>
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> New Campaign
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Total Campaigns</div>
                        <h4 class="fw-800 text-dark mb-0">{{ number_format($stats['total_campaigns']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                        <i class="bi bi-send-check"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Emails Sent</div>
                        <h4 class="fw-800 text-success mb-0">{{ number_format($stats['total_sent']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-4">
                        <i class="bi bi-envelope-open"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Total Opens</div>
                        <h4 class="fw-800 text-info mb-0">{{ number_format($stats['total_opened']) }}</h4>
                        <span class="text-muted extra-small">
                            {{ $stats['total_sent'] > 0 ? round(($stats['total_opened'] / $stats['total_sent']) * 100, 1) : 0 }}% avg open
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-4">
                        <i class="bi bi-cursor"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small text-uppercase fw-bold">Total Clicks</div>
                        <h4 class="fw-800 text-warning mb-0">{{ number_format($stats['total_clicked']) }}</h4>
                        <span class="text-muted extra-small">
                            {{ $stats['total_opened'] > 0 ? round(($stats['total_clicked'] / $stats['total_opened']) * 100, 1) : 0 }}% click-to-open
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs & Filter -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <ul class="nav nav-pills bg-white p-1 rounded-pill shadow-sm border" id="campaignStatusTabs">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-bold {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.campaigns.index') }}">
                    All
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-bold {{ request('status') === 'sent' ? 'active' : '' }}" href="{{ route('admin.campaigns.index', ['status' => 'sent']) }}">
                    Sent
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-bold {{ request('status') === 'draft' ? 'active' : '' }}" href="{{ route('admin.campaigns.index', ['status' => 'draft']) }}">
                    Drafts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-bold {{ request('status') === 'sending' ? 'active' : '' }}" href="{{ route('admin.campaigns.index', ['status' => 'sending']) }}">
                    Sending
                </a>
            </li>
        </ul>
    </div>

    <!-- Campaigns List Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-muted extra-small text-uppercase fw-800">Campaign & Subject</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Target Audience</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Status</th>
                        <th class="text-muted extra-small text-uppercase fw-800" style="min-width: 140px;">Open Rate</th>
                        <th class="text-muted extra-small text-uppercase fw-800" style="min-width: 140px;">Click Rate</th>
                        <th class="text-center text-muted extra-small text-uppercase fw-800">Sent / Dispatched</th>
                        <th class="text-end pe-4 text-muted extra-small text-uppercase fw-800">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                    <tr>
                        <td class="ps-4 py-3">
                            <div>
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="fw-800 text-dark text-decoration-none hover-primary">
                                    {{ $campaign->title }}
                                </a>
                                <div class="text-muted small text-truncate" style="max-width: 320px;">
                                    <strong>Subject:</strong> {{ $campaign->subject }}
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($campaign->target_type === 'all')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-people-fill me-1"></i> All Active Subscribers
                                </span>
                            @else
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-diagram-3-fill me-1"></i> {{ $campaign->group ? $campaign->group->name : 'Audience Segment' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($campaign->status === 'sent')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-check2-all me-1"></i> Completed
                                </span>
                            @elseif($campaign->status === 'sending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-arrow-repeat spin me-1"></i> Sending...
                                </span>
                            @elseif($campaign->status === 'draft')
                                <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-pencil-square me-1"></i> Draft
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-bold">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $campaign->open_rate }}%;" aria-valuenow="{{ $campaign->open_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fw-bold extra-small text-dark">{{ $campaign->open_rate }}%</span>
                            </div>
                            <div class="text-muted extra-small">
                                {{ number_format($campaign->opened_count) }} / {{ number_format($campaign->sent_count) }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $campaign->click_rate }}%;" aria-valuenow="{{ $campaign->click_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fw-bold extra-small text-dark">{{ $campaign->click_rate }}%</span>
                            </div>
                            <div class="text-muted extra-small">
                                {{ number_format($campaign->clicked_count) }} clicks
                            </div>
                        </td>
                        <td class="text-center text-muted small">
                            @if($campaign->sent_at)
                                <div>{{ $campaign->sent_at->format('M d, Y') }}</div>
                                <div class="extra-small text-muted">{{ $campaign->sent_at->format('h:i A') }}</div>
                            @else
                                <span class="text-muted fst-italic">Not dispatched</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" title="View Analytics & Recipients">
                                    <i class="bi bi-graph-up"></i>
                                </a>
                                @if($campaign->status === 'draft')
                                <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="d-inline send-campaign-form">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 btn-send-campaign" data-title="{{ $campaign->title }}" title="Send Now">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline delete-campaign-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 btn-delete-campaign" data-title="{{ $campaign->title }}" title="Delete Campaign">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-megaphone text-muted display-4 mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Campaigns Found</h6>
                            <p class="text-muted small mb-3">Launch your first marketing campaign to engage with your subscribers.</p>
                            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Create Campaign
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
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
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
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
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
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
