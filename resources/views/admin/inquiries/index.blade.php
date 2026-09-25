@extends('layouts.admin')

@section('page_title', 'Inquiries & Messages')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Inquiries Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer inquiries from website contact forms, track response times, and reply quickly.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inquiries.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-file-earmark-arrow-down text-emerald-600 text-sm"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Inquiries</span>
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-envelope-paper-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">All contact submissions</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Action Needed</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-exclamation-circle-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600">{{ number_format($stats['new'] ?? 0) }}</div>
            <span class="text-[11px] font-bold text-rose-600 mt-1 flex items-center gap-1">
                <i class="bi bi-bell-fill"></i> Unread inquiries
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In Review</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-eye-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['read'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Viewed / in progress</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Resolution Rate</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check2-circle text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $stats['response_rate'] ?? 0 }}%</div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $stats['replied'] ?? 0 }} replied to customers</span>
        </div>
    </div>

    <!-- 2 Interactive Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i> 14-Day Inquiries Acquisition Trend
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily volume of incoming contact requests</p>
                </div>
            </div>
            <div class="h-56 relative w-full">
                <canvas id="inquiriesTrendChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-amber-500"></i> Status Breakdown
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Resolution pipeline</p>
                </div>
            </div>
            <div class="h-56 relative w-full">
                <canvas id="statusBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.inquiries.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-5 sm:col-span-2">
                    <label for="inquirySearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Messages</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                        <input type="text" name="search" id="inquirySearch" class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Name, Email, Subject, Message..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label for="statusFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                    <select name="status" id="statusFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Action Needed (New)</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>In Review (Read)</option>
                        <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Resolved (Replied)</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="fromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                    <input type="date" name="from_date" id="fromDate" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ request('from_date') }}">
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all" title="Apply Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('admin.inquiries.index') }}" class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Inquiries Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="inquiriesTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 no-sort no-export no-colvis w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary inquiries-select-all" title="Select All">
                        </th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($inquiries as $c)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 no-export">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary inquiries-row-select" value="{{ $c->id }}">
                        </td>
                        <td class="py-3 px-4" data-order="{{ $c->created_at ? $c->created_at->timestamp : 0 }}">
                            <div class="text-xs font-bold text-slate-900">
                                {{ $c->created_at ? $c->created_at->format('M j, Y') : '' }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $c->created_at ? $c->created_at->format('g:ia') : '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-bold text-slate-900">{{ $c->name }}</div>
                            <a href="mailto:{{ $c->email }}" class="text-[11px] text-slate-400 hover:text-primary transition">{{ $c->email }}</a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-semibold text-slate-800 line-clamp-1">{{ $c->subject }}</div>
                            <div class="text-[11px] text-slate-400 line-clamp-1">{{ Str::limit($c->message, 60) }}</div>
                        </td>
                        <td class="py-3 px-4 text-center" data-order="{{ $c->status }}">
                            @php
                                $badgeClass = match($c->status) {
                                    'new' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'read' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'replied' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $badgeClass }}">
                                {{ $c->status === 'new' ? 'Action Needed' : ($c->status === 'read' ? 'In Review' : 'Replied') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.inquiries.show', $c->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="View Detail">
                                    <i class="bi bi-eye-fill text-xs"></i>
                                </a>

                                <form action="{{ route('admin.inquiries.status', $c->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="replied">
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center bg-white transition shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed" title="Mark Replied" {{ $c->status === 'replied' ? 'disabled' : '' }}>
                                        <i class="bi bi-check-lg text-xs"></i>
                                    </button>
                                </form>

                                <button type="button" 
                                        class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-inquiry" 
                                        title="Permanently Delete Inquiry"
                                        data-id="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-email="{{ $c->email }}"
                                        data-subject="{{ $c->subject }}">
                                    <i class="bi bi-trash3-fill text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            <i class="bi bi-envelope-x text-3xl block mb-2 opacity-50"></i>
                            No contact inquiries match your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Batch Bulk Action Toolbar -->
<div id="inquiriesBulkBar" class="bulk-action-bar">
    <div class="flex items-center gap-2">
        <span class="px-2 py-0.5 bg-primary text-white rounded-full text-xs font-bold"><span id="inquiriesSelectedCount">0</span></span>
        <span class="font-semibold text-xs text-white">selected</span>
    </div>
    <div class="w-px h-5 bg-slate-700"></div>
    <div class="flex items-center gap-1.5 flex-wrap">
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_read">
            <i class="bi bi-eye text-amber-400 me-1"></i> Mark Read
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_replied">
            <i class="bi bi-check-circle text-emerald-400 me-1"></i> Mark Replied
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition inquiries-bulk-action" data-action="status_new">
            <i class="bi bi-bell text-rose-400 me-1"></i> Mark New
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition inquiries-bulk-action" data-action="delete">
            <i class="bi bi-trash3 me-1"></i> Delete
        </button>
    </div>
    <button type="button" class="text-slate-400 hover:text-white ms-1 cursor-pointer" id="inquiriesBulkClear" title="Deselect all">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Inquiries Acquisition Trend Chart
    const trendCtx = document.getElementById('inquiriesTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Inquiries',
                    data: trendData.map(d => d.count),
                    borderColor: '#F69044',
                    backgroundColor: 'rgba(246, 144, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#F69044',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Status Breakdown Doughnut Chart
    const statusCtx = document.getElementById('statusBreakdownChart');
    if (statusCtx) {
        const statusData = @json($statusBreakdown);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Action Needed (New)', 'In Review (Read)', 'Resolved (Replied)'],
                datasets: [{
                    data: [statusData.new || 0, statusData.read || 0, statusData.replied || 0],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    // Inquiries Batch Bulk Selection & Processing
    const $inqBulkBar = $('#inquiriesBulkBar');
    const $inqSelectAll = $('.inquiries-select-all');
    const $inqCountBadge = $('#inquiriesSelectedCount');

    function updateInqBulkBar() {
        const checkedBoxes = $('.inquiries-row-select:checked');
        const count = checkedBoxes.length;
        $inqCountBadge.text(count);
        if (count > 0) {
            $inqBulkBar.addClass('show');
        } else {
            $inqBulkBar.removeClass('show');
        }
    }

    $inqSelectAll.on('change', function() {
        $('.inquiries-row-select').prop('checked', $(this).is(':checked'));
        updateInqBulkBar();
    });

    $(document).on('change', '.inquiries-row-select', function() {
        const total = $('.inquiries-row-select').length;
        const checked = $('.inquiries-row-select:checked').length;
        $inqSelectAll.prop('checked', total > 0 && total === checked);
        updateInqBulkBar();
    });

    $('#inquiriesBulkClear').on('click', function() {
        $('.inquiries-row-select, .inquiries-select-all').prop('checked', false);
        updateInqBulkBar();
    });

    // Single inquiry delete button trigger
    $(document).on('click', '.btn-delete-inquiry', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'Customer';
        const email = $(this).data('email') || '';
        const subject = $(this).data('subject') || '';
        promptPermanentDeleteInquiry(id, name, email, subject);
    });

    $('.inquiries-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.inquiries-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const isDelete = action === 'delete';

        if (isDelete) {
            // Step 1: Caution Dialog for Bulk Inquiries Purge
            Swal.fire({
                title: 'CAUTION: Permanent Bulk Inquiries Purge',
                html: `
                    <div class="text-left text-xs text-slate-600">
                        <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                            <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                            <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate <strong class="text-slate-900">${selectedIds.length} contact inquiries</strong> from the database.
                        </div>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                            <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently erased:</div>
                            <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                                <li>All ${selectedIds.length} customer messages, inquiries & sender details</li>
                                <li>All associated visitor telemetry, IP tracking & request logs</li>
                                <li>Complete attribution, UTM tags & analytics footprints</li>
                            </ul>
                        </div>
                        <p class="mb-0 text-slate-500">Are you sure you want to proceed to final verification?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Proceed to Verification <i class="bi bi-arrow-right ms-1"></i>',
                cancelButtonText: 'Cancel (Keep Inquiries)',
                focusCancel: true
            }).then((step1) => {
                if (!step1.isConfirmed) return;

                // Step 2: Final Safeguard Bulk Purge Double Confirmation
                Swal.fire({
                    title: 'Double Confirmation: Bulk Purge',
                    html: `
                        <div class="text-left text-xs">
                            <p class="text-slate-800 mb-2">To permanently purge all <strong>${selectedIds.length}</strong> selected inquiry record(s) and their analytics footprints, type <span class="inline-block px-2 py-0.5 bg-rose-600 text-white font-mono rounded">DELETE</span> below:</p>
                        </div>
                    `,
                    input: 'text',
                    inputPlaceholder: 'Type DELETE to confirm',
                    inputAttributes: {
                        autocapitalize: 'characters',
                        style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                    },
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#991b1b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PURGE ALL SELECTED RECORDS',
                    cancelButtonText: 'Abort',
                    focusCancel: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (inputValue) => {
                        if (inputValue !== 'DELETE') {
                            Swal.showValidationMessage('Verification mismatch! You must type exactly "DELETE" in capital letters to authorize bulk deletion.');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.inquiries.bulk") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selectedIds,
                                action: 'delete'
                            },
                            headers: { 'Accept': 'application/json' }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message || 'Failed to process bulk action.');
                            }
                            return response;
                        }).catch(error => {
                            const msg = error.responseJSON ? error.responseJSON.message : error.message;
                            Swal.showValidationMessage(`Bulk Purge Failed: ${msg}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((step2Result) => {
                    if (step2Result.isConfirmed && step2Result.value) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Purged!',
                            text: step2Result.value.message || 'Selected inquiries permanently deleted.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            });
            return;
        }

        const actionLabel = 'mark as ' + action.replace('status_', '');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${actionLabel} for ${selectedIds.length} selected inquiry message(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F69044',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                $.ajax({
                    url: '{{ route("admin.inquiries.bulk") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds,
                        action: action
                    },
                    success: function(res) {
                        hideLoader();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'Inquiries updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        hideLoader();
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing bulk action.';
                        Swal.fire('Failed', msg, 'error');
                    }
                });
            }
        });
    });
});

/**
 * 2-Step Cautionary Double Confirmation for Permanent Single Contact Inquiry Deletion
 */
window.promptPermanentDeleteInquiry = function(id, name, email, subject) {
    const verifyTarget = (email && email.trim()) ? email.trim() : (name ? name.trim() : 'CONFIRM');

    // Step 1: Caution Dialog
    Swal.fire({
        title: 'CAUTION: Permanent Inquiry Deletion',
        html: `
            <div class="text-left text-xs text-slate-600">
                <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate contact inquiry <strong class="text-slate-900">#${id} (${name})</strong> from the database.
                </div>
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                    <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                    <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                        <li>Contact Inquiry Record (${email || 'No Email'})</li>
                        <li>Customer message content & subject ("${subject || 'Inquiry'}")</li>
                        <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                        <li>Client IP & Geolocation Audit Trail</li>
                    </ul>
                </div>
                <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Inquiry)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the customer email below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-sm">
                            ${verifyTarget}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${verifyTarget} to confirm`,
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                autocomplete: 'off',
                style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
            },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#991b1b',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim().toLowerCase() !== verifyTarget.toLowerCase()) {
                    Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/inquiries/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete inquiry.');
                    }
                    return response;
                }).catch(error => {
                    const msg = error.responseJSON ? error.responseJSON.message : error.message;
                    Swal.showValidationMessage(`Purge Failed: ${msg}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((step2Result) => {
            if (step2Result.isConfirmed && step2Result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Purged!',
                    text: step2Result.value.message || 'Inquiry and all analytics footprints permanently deleted.',
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });
};
</script>
@endpush
@endsection
