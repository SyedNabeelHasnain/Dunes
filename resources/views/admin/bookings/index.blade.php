@extends('layouts.admin')

@section('page_title', 'Bookings & Orders')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight mb-1">Bookings & Reservations</h2>
        <p class="text-xs md:text-sm text-slate-500 mb-0">Manage customer tour bookings, payment reconciliations, and instant WhatsApp support.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition cursor-pointer" id="btnRefreshBookingsStats" title="Recount metrics live directly from database">
            <i class="bi bi-arrow-repeat text-primary text-sm" id="bookingsSyncIcon"></i>
            <span>Refresh Counts</span>
        </button>
        <a href="{{ route('admin.bookings.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-emerald-50 border border-emerald-300 text-emerald-700 shadow-xs text-xs font-bold transition">
            <i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i> Export CSV
        </a>
    </div>
</div>

<!-- 4 Booking Summary Metric Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Bookings</span>
            <span class="w-8 h-8 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-sm"><i class="bi bi-calendar-check-fill"></i></span>
        </div>
        <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 booking-kpi-val" id="bookingStatTotal">{{ number_format($stats['total'] ?? 0) }}</h3>
        <span class="text-[11px] text-slate-400">All active reservations</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Confirmed / Paid</span>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="bi bi-check-circle-fill"></i></span>
        </div>
        <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 booking-kpi-val" id="bookingStatConfirmed">{{ number_format($stats['confirmed'] ?? 0) }}</h3>
        <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1"><i class="bi bi-shield-check"></i> Active & Confirmed</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Bookings</span>
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="bi bi-clock-history"></i></span>
        </div>
        <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 booking-kpi-val" id="bookingStatPending">{{ number_format($stats['pending'] ?? 0) }}</h3>
        <span class="text-[11px] text-amber-600 font-bold flex items-center gap-1"><i class="bi bi-hourglass-split"></i> Awaiting confirmation</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Collected Revenue</span>
            <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm"><i class="bi bi-cash-stack"></i></span>
        </div>
        <h3 class="text-lg lg:text-xl font-black text-primary mb-1 booking-kpi-val" id="bookingStatRevenue">AED {{ number_format($stats['revenue'] ?? 0) }}</h3>
        <span class="text-[11px] text-slate-400">AOV: <strong id="bookingStatAov" class="text-slate-700">AED {{ number_format($stats['aov'] ?? 0) }}</strong></span>
    </div>
</div>

<!-- 2 Interactive Analytics Charts -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-8 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h6 class="font-extrabold text-slate-900 text-sm md:text-base flex items-center gap-2 mb-0.5">
                    <i class="bi bi-graph-up-arrow text-primary"></i> 14-Day Bookings & Revenue Trend
                </h6>
                <span class="text-xs text-slate-500">Daily volume of reservations and collected payments</span>
            </div>
        </div>
        <div class="h-56">
            <canvas id="bookingsTrendChart"></canvas>
        </div>
    </div>

    <div class="lg:col-span-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h6 class="font-extrabold text-slate-900 text-sm md:text-base flex items-center gap-2 mb-0.5">
                    <i class="bi bi-pie-chart-fill text-emerald-600"></i> Status Distribution
                </h6>
                <span class="text-xs text-slate-500">Confirmed vs pending vs cancelled</span>
            </div>
        </div>
        <div class="h-56 relative">
            <canvas id="bookingStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Search & Filters Toolbar -->
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
    <form method="GET" action="{{ route('admin.bookings.index') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label for="searchQuery" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Keyword Search</label>
                <div class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden px-3 py-2 shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20">
                    <i class="bi bi-search text-slate-400 me-2 text-xs"></i>
                    <input type="text" name="search" id="searchQuery" class="w-full bg-transparent border-0 outline-none text-xs font-medium text-slate-800 placeholder:text-slate-400 p-0" placeholder="Ref, name, phone, email..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label for="statusFilter" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Booking Status</label>
                <select name="status" id="statusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-2xs">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Abandoned Checkouts (Drafts)</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="paymentFilter" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Payment Status</label>
                <select name="payment_status" id="paymentFilter" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-2xs">
                    <option value="">All Payments</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial (Advance)</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="fromDate" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tour From</label>
                <input type="date" name="from_date" id="fromDate" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-2xs" value="{{ request('from_date') }}">
            </div>

            <div class="lg:col-span-2">
                <label for="toDate" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tour To</label>
                <input type="date" name="to_date" id="toDate" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-2xs" value="{{ request('to_date') }}">
            </div>

            <div class="lg:col-span-1 flex gap-1.5">
                <button type="submit" class="flex-1 py-2 px-3 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center cursor-pointer" title="Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Bookings Table Card -->
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table datatable" id="bookingsTable">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="py-3 px-4 no-sort no-export w-9">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary bulk-select-all cursor-pointer" title="Select All">
                    </th>
                    <th class="py-3 px-4">Ref & Time</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Tour / Activity</th>
                    <th class="py-3 px-4">Tour Date</th>
                    <th class="py-3 px-4">Total & Paid</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right no-sort">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($bookings as $b)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3.5 px-4 no-export">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary bulk-row-select cursor-pointer" value="{{ $b->id }}">
                    </td>
                    <td class="py-3.5 px-4" data-order="{{ $b->created_at ? $b->created_at->timestamp : 0 }}">
                        <div class="font-black text-slate-900">#{{ $b->reference }}</div>
                        <div class="text-[11px] text-slate-400">{{ $b->created_at ? $b->created_at->format('M j, Y g:ia') : '' }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ $b->phone }}</div>
                        <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $b->email }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-800">{{ $b->tour_name }}</div>
                        <div class="flex items-center gap-1.5 flex-wrap mt-1">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-50 text-primary border border-orange-200">
                                {{ $b->tier_name ?: ($b->tier ? $b->tier->display_name : 'Standard') }}
                            </span>
                            @if($b->addons && $b->addons->count() > 0)
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="{{ $b->addons->pluck('addon_name')->implode(', ') }}">
                                    +{{ $b->addons->count() }} Addon{{ $b->addons->count() > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-0.5">({{ $b->adults }} Adults, {{ $b->children }} Child)</span>
                    </td>
                    <td class="py-3.5 px-4" data-order="{{ $b->tour_date ? $b->tour_date->timestamp : 0 }}">
                        <div class="font-bold text-slate-800">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : 'Open Date' }}</div>
                        @if($b->pickup_time)
                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5"><i class="bi bi-clock"></i> {{ $b->pickup_time }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4" data-order="{{ (float)$b->total }}">
                        <div class="font-black text-primary text-sm">AED {{ number_format($b->total) }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            @if($b->payment_status === 'paid')
                                Paid: <strong class="text-emerald-600">AED {{ number_format($b->payment_amount ?: $b->total) }}</strong> | Due: AED 0
                            @elseif($b->payment_status === 'partial')
                                Paid: <strong class="text-amber-600">AED {{ number_format($b->payment_amount) }}</strong> | Due: AED {{ number_format($b->balance_due) }}
                            @else
                                Paid: <strong class="text-slate-500">AED 0</strong> | Due: AED {{ number_format($b->total) }}
                            @endif
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-center" data-order="{{ $b->status }}">
                        @php
                            $badgeClass = [
                                'pending' => 'bg-amber-100 text-amber-800',
                                'confirmed' => 'bg-emerald-100 text-emerald-800',
                                'completed' => 'bg-sky-100 text-sky-800',
                                'cancelled' => 'bg-rose-100 text-rose-800',
                                'draft' => 'bg-slate-100 text-slate-600 border border-slate-300'
                            ][$b->status] ?? 'bg-slate-100 text-slate-700';
                        @endphp
                        @if($b->status === 'draft')
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">Abandoned Lead</span>
                        @else
                            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold capitalize {{ $badgeClass }}">{{ $b->status }}</span>
                        @endif
                        <div class="text-[10px] text-slate-400 capitalize mt-1">
                            Pay: <span class="font-bold text-slate-700">{{ $b->payment_status }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            @if($b->status === 'draft')
                                @php
                                    $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                    $waMsg = 'Hi ' . $b->name . '! We noticed you started reserving the ' . $b->tour_name . ' on Dunes Discovery. Your spot is held for ' . ($b->tour_date ? $b->tour_date->format('M j') : 'your chosen date') . '. Would you like help completing your reservation with code FIRST25 (25% OFF)?';
                                @endphp
                                <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-amber-400 hover:bg-amber-500 text-slate-900 text-xs font-bold shadow-xs transition" title="Send WhatsApp Recovery">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>Recover</span>
                                </a>
                            @else
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="w-8 h-8 rounded-full border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-500 transition" title="View Booking Details">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('admin.bookings.ticket', $b->id) }}" class="w-8 h-8 rounded-full border border-rose-200 hover:bg-rose-50 text-rose-600 flex items-center justify-center transition" title="Download E-Ticket PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                @php
                                    $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                    $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                                @endphp
                                <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-emerald-200 hover:bg-emerald-50 text-emerald-600 flex items-center justify-center transition" title="WhatsApp Customer">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            @endif
                            <button type="button" class="w-8 h-8 rounded-full border border-slate-200 hover:border-rose-400 hover:text-rose-600 text-slate-400 flex items-center justify-center transition cursor-pointer" title="Permanently Delete Booking & All Records" onclick="promptPermanentDelete({{ $b->id }}, '{{ $b->reference }}', '{{ addslashes($b->name) }}')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        <i class="bi bi-calendar-x text-4xl block mb-2 opacity-50"></i>
                        No bookings match your current filter parameters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Floating Batch Bulk Action Toolbar -->
<div id="bookingBulkBar" class="bulk-action-bar">
    <div class="flex items-center gap-2">
        <span class="px-2 py-0.5 bg-primary text-white rounded-full text-xs font-bold"><span id="bookingSelectedCount">0</span></span>
        <span class="font-semibold text-xs text-white">selected</span>
    </div>
    <div class="w-px h-5 bg-slate-700"></div>
    <div class="flex items-center gap-1.5 flex-wrap">
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition booking-bulk-action" data-action="status_confirmed">
            <i class="bi bi-check-circle text-emerald-400 me-1"></i> Confirmed
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition booking-bulk-action" data-action="status_completed">
            <i class="bi bi-check2-all text-sky-400 me-1"></i> Completed
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition booking-bulk-action" data-action="status_pending">
            <i class="bi bi-clock-history text-amber-400 me-1"></i> Pending
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition booking-bulk-action" data-action="status_cancelled">
            <i class="bi bi-x-circle text-rose-400 me-1"></i> Cancelled
        </button>
        <button type="button" class="px-3 py-1 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition booking-bulk-action" data-action="delete">
            <i class="bi bi-trash3 me-1"></i> Delete
        </button>
    </div>
    <button type="button" class="text-slate-400 hover:text-white ms-1 cursor-pointer" id="bookingBulkClear" title="Deselect all">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Bookings & Revenue Trend Chart
    const trendCtx = document.getElementById('bookingsTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Bookings Count',
                        data: trendData.map(d => d.count),
                        borderColor: '#F69044',
                        backgroundColor: 'rgba(246, 144, 68, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (AED)',
                        data: trendData.map(d => d.revenue),
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        pointBackgroundColor: '#10b981',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: '#f1f5f9' } },
                    y: { type: 'linear', position: 'left', beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { display: false } }
                }
            }
        });
    }

    // Status Distribution Doughnut Chart
    const statusCtx = document.getElementById('bookingStatusChart');
    if (statusCtx) {
        const statusData = @json($statusDistribution);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [statusData.confirmed || 0, statusData.pending || 0, statusData.completed || 0, statusData.cancelled || 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#ef4444'],
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

    // Batch Bulk Selection & Processing
    const $bulkBar = $('#bookingBulkBar');
    const $selectAll = $('.bulk-select-all');
    const $countBadge = $('#bookingSelectedCount');

    function updateBulkBar() {
        const checkedBoxes = $('.bulk-row-select:checked');
        const count = checkedBoxes.length;
        $countBadge.text(count);
        if (count > 0) {
            $bulkBar.addClass('show');
        } else {
            $bulkBar.removeClass('show');
        }
    }

    $selectAll.on('change', function() {
        $('.bulk-row-select').prop('checked', $(this).is(':checked'));
        updateBulkBar();
    });

    $(document).on('change', '.bulk-row-select', function() {
        const total = $('.bulk-row-select').length;
        const checked = $('.bulk-row-select:checked').length;
        $selectAll.prop('checked', total > 0 && total === checked);
        updateBulkBar();
    });

    $('#bookingBulkClear').on('click', function() {
        $('.bulk-row-select, .bulk-select-all').prop('checked', false);
        updateBulkBar();
    });

    $('.booking-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.bulk-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const isDelete = action === 'delete';

        if (isDelete) {
            Swal.fire({
                title: 'CAUTION: Permanent Bulk Deletion',
                html: `
                    <div class="text-left text-xs text-slate-600">
                        <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>IRREVERSIBLE BULK ACTION:</strong> You are about to permanently eradicate <strong class="text-slate-900">${selectedIds.length}</strong> selected booking(s) from the database.
                        </div>
                        <p class="mb-2 text-slate-800">This will permanently delete all associated payment history, booked addons, analytics logs, and guest reviews for all selected records.</p>
                        <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final confirmation?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
                cancelButtonText: 'Cancel (Keep Bookings)',
                focusCancel: true
            }).then((step1Result) => {
                if (!step1Result.isConfirmed) return;

                Swal.fire({
                    title: 'Confirm Bulk Deletion',
                    html: `
                        <div class="text-left text-xs">
                            <p class="text-slate-800 mb-2">To confirm permanent deletion of <strong>${selectedIds.length}</strong> bookings and all related records, type <strong>DELETE</strong> in capital letters below:</p>
                        </div>
                    `,
                    input: 'text',
                    inputPlaceholder: 'DELETE',
                    inputAttributes: {
                        autocapitalize: 'characters',
                        style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;'
                    },
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PURGE ALL SELECTED',
                    cancelButtonText: 'Abort',
                    focusCancel: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (inputValue) => {
                        if (!inputValue || inputValue.trim() !== 'DELETE') {
                            Swal.showValidationMessage('Verification failed! You must type "DELETE" exactly.');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.bookings.bulk") }}',
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
                            text: step2Result.value.message || 'Selected bookings permanently deleted.',
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

        const actionLabel = 'update status to ' + action.replace('status_', '');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${actionLabel} for ${selectedIds.length} selected booking(s).`,
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
                    url: '{{ route("admin.bookings.bulk") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds,
                        action: action
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message || 'Bookings updated successfully.',
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

    // Real-Time Bookings KPI Refresh with Shimmer Placeholders
    $('#btnRefreshBookingsStats').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $('#bookingsSyncIcon');

        $btn.prop('disabled', true);
        $icon.addClass('kpi-sync-spin');

        $('.booking-kpi-val').each(function() {
            $(this).data('cached-val', $(this).html());
            $(this).html('<span class="counter-shimmer"></span>');
        });

        $.ajax({
            url: "{{ route('admin.api.bookings.stats') }}",
            type: "GET",
            dataType: "json",
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            success: function(res) {
                $icon.removeClass('kpi-sync-spin');
                $btn.prop('disabled', false);

                if (res && res.success && res.stats) {
                    const s = res.stats;
                    $('#bookingStatTotal').text(Number(s.total).toLocaleString());
                    $('#bookingStatConfirmed').text(Number(s.confirmed).toLocaleString());
                    $('#bookingStatPending').text(Number(s.pending).toLocaleString());
                    $('#bookingStatRevenue').text('AED ' + Number(s.revenue).toLocaleString());
                    $('#bookingStatAov').text('AED ' + Number(s.aov).toLocaleString());

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Bookings Metrics Recounted Live!'
                    });
                } else {
                    $('.booking-kpi-val').each(function() {
                        $(this).html($(this).data('cached-val'));
                    });
                }
            },
            error: function() {
                $icon.removeClass('kpi-sync-spin');
                $btn.prop('disabled', false);
                $('.booking-kpi-val').each(function() {
                    $(this).html($(this).data('cached-val'));
                });
            }
        });
    });
});

window.promptPermanentDelete = function(id, reference, name) {
    Swal.fire({
        title: 'CAUTION: Permanent Booking Deletion',
        html: `
            <div class="text-left text-xs text-slate-600">
                <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate Booking <span class="font-mono font-bold">#${reference}</span> from the database.
                </div>
                <p class="mb-2 text-slate-800"><strong>Customer:</strong> ${name || 'N/A'}</p>
                <div class="p-3 mb-3 rounded-xl bg-slate-100 border border-slate-200/80">
                    <div class="font-bold text-slate-900 mb-1 text-[11px] uppercase tracking-wider">The following records will be permanently purged:</div>
                    <ul class="space-y-1 text-slate-500 text-[11px] list-disc ps-4">
                        <li>Primary Booking Record & Guest Information</li>
                        <li>All Booked Add-ons, Buggies & Extra Selections</li>
                        <li>Complete Payment History & Gateway Transaction Records</li>
                        <li>Visitor Analytics, Telemetry & Request Logs</li>
                        <li>Guest Reviews submitted for this booking</li>
                        <li>Coupon usage quota will be released back to pool</li>
                    </ul>
                </div>
                <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Booking)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the booking reference below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1 rounded-full text-base font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            ${reference}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${reference} to confirm`,
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                autocomplete: 'off',
                style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
            },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim() !== reference) {
                    Swal.showValidationMessage(`Reference mismatch! You must type exactly "${reference}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/bookings/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete booking.');
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
                    text: step2Result.value.message || `Booking #${reference} was permanently deleted.`,
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
