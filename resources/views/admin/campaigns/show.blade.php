@extends('layouts.admin')

@section('page_title', 'Campaign Analytics: ' . $campaign->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-1" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-800 text-dark mb-0">{{ $campaign->title }}</h4>
                @if($campaign->status === 'sent')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold ms-2">
                        <i class="bi bi-check2-all me-1"></i> Completed
                    </span>
                @elseif($campaign->status === 'sending')
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold ms-2">
                        <i class="bi bi-arrow-repeat spin me-1"></i> Sending...
                    </span>
                @elseif($campaign->status === 'scheduled')
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-bold ms-2">
                        <i class="bi bi-clock-history me-1"></i> Scheduled
                    </span>
                @elseif($campaign->status === 'draft')
                    <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-1 small fw-bold ms-2">
                        <i class="bi bi-pencil-square me-1"></i> Draft
                    </span>
                @endif
            </div>
            <div class="text-muted small ps-4 ms-2">
                <strong>Subject:</strong> {{ $campaign->subject }}
                @if($campaign->sent_at)
                    • Dispatched on {{ $campaign->sent_at->format('M d, Y h:i A') }}
                @elseif($campaign->scheduled_at)
                    • Scheduled for {{ $campaign->scheduled_at->format('M d, Y h:i A') }}
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-info rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                <i class="bi bi-send-check"></i> Send Test Copy
            </button>
            @if(in_array($campaign->status, ['draft', 'scheduled']))
                <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="d-inline" id="sendNowForm">
                    @csrf
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-2" id="btnSendNow">
                        <i class="bi bi-send-fill"></i> Send Broadcast Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Analytics Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Audience</div>
                <h4 class="fw-800 text-dark mb-0">{{ number_format($campaign->total_recipients ?: $campaign->sent_count) }}</h4>
                <div class="text-muted extra-small mt-1 text-truncate">
                    {{ $campaign->target_type === 'all' ? 'All Active' : ($campaign->group ? $campaign->group->name : 'Segment') }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Delivered</div>
                <h4 class="fw-800 text-primary mb-0">{{ number_format($campaign->sent_count) }}</h4>
                <div class="text-muted extra-small mt-1">
                    {{ $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100, 1) : 100 }}% delivery
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Opens (Unique)</div>
                <h4 class="fw-800 text-info mb-0">{{ number_format($campaign->opened_count) }}</h4>
                <div class="text-info extra-small fw-bold mt-1">
                    {{ $campaign->open_rate }}% open rate
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Link Clicks</div>
                <h4 class="fw-800 text-warning mb-0">{{ number_format($campaign->clicked_count) }}</h4>
                <div class="text-warning extra-small fw-bold mt-1">
                    {{ $campaign->click_rate }}% click rate
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Bounces</div>
                <h4 class="fw-800 text-danger mb-0">{{ number_format($campaign->bounced_count) }}</h4>
                <div class="text-muted extra-small mt-1">
                    {{ $campaign->bounce_rate }}% bounce rate
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Unsubscribed</div>
                <h4 class="fw-800 text-secondary mb-0">{{ number_format($campaign->unsubscribed_count) }}</h4>
                <div class="text-muted extra-small mt-1">
                    {{ $campaign->unsubscribe_rate }}% opt-out
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Rates Progress Bars -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <h6 class="fw-800 text-dark mb-3">
            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Engagement Performance Benchmarks
        </h6>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="small fw-bold text-dark"><i class="bi bi-envelope-open text-info me-1"></i> Unique Open Rate</span>
                    <span class="small fw-800 text-info">{{ $campaign->open_rate }}%</span>
                </div>
                <div class="progress rounded-pill" style="height: 10px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $campaign->open_rate }}%;" aria-valuenow="{{ $campaign->open_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-muted extra-small mt-1">Industry travel average: ~22% - 28%</div>
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="small fw-bold text-dark"><i class="bi bi-cursor text-warning me-1"></i> Click-to-Open Rate</span>
                    <span class="small fw-800 text-warning">{{ $campaign->click_rate }}%</span>
                </div>
                <div class="progress rounded-pill" style="height: 10px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $campaign->click_rate }}%;" aria-valuenow="{{ $campaign->click_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-muted extra-small mt-1">Industry travel average: ~2.5% - 4.5%</div>
            </div>
        </div>
    </div>

    <!-- Recipient Logs Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.campaigns.show', $campaign->id) }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" class="form-control rounded-pill ps-5 bg-light border-0" placeholder="Search recipient email, name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="log_status" class="form-select rounded-pill bg-light border-0">
                        <option value="">All Delivery Statuses</option>
                        <option value="sent" {{ request('log_status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="opened" {{ request('log_status') === 'opened' ? 'selected' : '' }}>Opened</option>
                        <option value="clicked" {{ request('log_status') === 'clicked' ? 'selected' : '' }}>Clicked</option>
                        <option value="bounced" {{ request('log_status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                        <option value="failed" {{ request('log_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold flex-grow-1">Filter</button>
                    @if(request()->hasAny(['search', 'log_status']))
                        <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="btn btn-light rounded-pill px-3">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Recipient Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-muted extra-small text-uppercase fw-800">Recipient</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Status</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Opened</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Clicked Links</th>
                        <th class="text-muted extra-small text-uppercase fw-800">Dispatched At</th>
                        <th class="text-end pe-4 text-muted extra-small text-uppercase fw-800">Diagnostic Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">{{ $log->subscriber ? $log->subscriber->full_name : 'Guest' }}</div>
                            <div class="text-muted small font-monospace">{{ $log->subscriber ? $log->subscriber->email : 'N/A' }}</div>
                        </td>
                        <td>
                            @if($log->status === 'clicked')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-cursor me-1"></i> Clicked
                                </span>
                            @elseif($log->status === 'opened')
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-envelope-open me-1"></i> Opened
                                </span>
                            @elseif($log->status === 'sent')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-check2 me-1"></i> Delivered
                                </span>
                            @elseif($log->status === 'bounced')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-exclamation-octagon me-1"></i> Bounced
                                </span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="bi bi-x-circle me-1"></i> Failed
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-bold">
                                    {{ ucfirst($log->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($log->opened_at)
                                <span class="text-info fw-bold small">
                                    <i class="bi bi-check2 me-1"></i> {{ $log->opened_at->format('M d, h:i A') }}
                                </span>
                                <div class="text-muted extra-small">{{ $log->opened_at->diffForHumans($log->sent_at, true) }} after send</div>
                            @else
                                <span class="text-muted extra-small fst-italic">Not opened yet</span>
                            @endif
                        </td>
                        <td>
                            @if($log->clicks && $log->clicks->count() > 0)
                                <span class="badge bg-warning rounded-pill px-2.5 py-1 text-dark fw-bold">
                                    {{ $log->clicks->count() }} click(s)
                                </span>
                                <div class="text-muted extra-small text-truncate" style="max-width: 180px;" title="{{ $log->clicks->first()->target_url ?? $log->clicks->first()->url }}">
                                    {{ $log->clicks->first()->target_url ?? $log->clicks->first()->url }}
                                </div>
                            @else
                                <span class="text-muted extra-small fst-italic">No clicks</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $log->sent_at ? $log->sent_at->format('M d, h:i:s A') : 'Pending' }}
                        </td>
                        <td class="text-end pe-4">
                            @if($log->error_message)
                                <span class="badge bg-danger-subtle text-danger small text-truncate" style="max-width: 220px;" title="{{ $log->error_message }}">
                                    <i class="bi bi-bug me-1"></i> {{ $log->error_message }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border font-monospace extra-small">
                                    #{{ substr($log->tracking_token, 0, 10) }}...
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox text-muted display-4 mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Recipient Logs Found</h6>
                            <p class="text-muted small">Logs will appear once the campaign is dispatched to your subscribers.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                        <i class="bi bi-send-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="testEmailModalLabel">Send Test Copy</h5>
                        <div class="text-muted extra-small">Dispatch a live preview of this campaign to your inbox</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Recipient Email Address:</label>
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
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
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
            resultBox.className = 'alert alert-success rounded-3 small';
            resultBox.textContent = data.message || 'Diagnostic copy sent successfully! Check your inbox.';
        } else {
            resultBox.className = 'alert alert-danger rounded-3 small';
            resultBox.textContent = data.message || 'Failed to dispatch test email. Check your SMTP settings.';
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
</script>
@endpush
@endsection
