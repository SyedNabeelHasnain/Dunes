@extends('layouts.admin')

@section('page_title', 'Executive Dashboard')

@section('content')
<!-- Top Executive Header with Real-Time Database Sync & Live Refresh Trigger -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h2>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="live-pulse-dot"></span> Live Real-Time Database Sync
            </span>
        </div>
        <p class="text-xs md:text-sm text-slate-500 mb-0">Direct live counts from database transactions. Zero caching, instant precision.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:inline" id="kpiSyncStatus">Live as of {{ now()->format('g:i:s A') }}</span>
        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-800 transition cursor-pointer" id="btnRefreshDashboardKpis" title="Recount all metrics live directly from database">
            <i class="bi bi-arrow-repeat text-primary text-sm" id="kpiSyncIcon"></i>
            <span>Refresh Metrics</span>
        </button>
    </div>
</div>

<!-- 6 Core Business Performance KPI Cards Row -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 lg:gap-4 mb-6">
    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-lg mb-2">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Bookings</div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 kpi-number" id="kpiTotalBookings">{{ number_format($stats['total']) }}</h3>
        </div>
        <span class="text-[11px] text-slate-500">Active reservations</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-2">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Confirmed & Done</div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 kpi-number" id="kpiConfirmedBookings">{{ number_format($stats['confirmed_and_completed'] ?? ($stats['confirmed'] + ($stats['completed'] ?? 0))) }}</h3>
        </div>
        <span class="text-[11px] text-emerald-600 font-bold"><span id="kpiConfirmedCount">{{ $stats['confirmed'] }}</span> Confirmed, <span id="kpiCompletedCount">{{ $stats['completed'] ?? 0 }}</span> Done</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-2">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pending Bookings</div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 kpi-number" id="kpiPendingBookings">{{ number_format($stats['pending']) }}</h3>
        </div>
        <span class="text-[11px] text-amber-600 font-bold"><i class="bi bi-hourglass-split me-1"></i>Awaiting review</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg mb-2">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Collected Revenue</div>
            <h4 class="text-lg lg:text-xl font-black text-primary mb-1 kpi-number" id="kpiRevenue">AED {{ number_format($stats['revenue']) }}</h4>
        </div>
        <span class="text-[11px] text-slate-500">Verified payments</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg mb-2">
                <i class="bi bi-bag-check-fill"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Avg Order Value</div>
            <h4 class="text-lg lg:text-xl font-black text-slate-900 mb-1 kpi-number" id="kpiAov">AED {{ number_format($stats['aov'] ?? 0) }}</h4>
        </div>
        <span class="text-[11px] text-slate-500">Per paid booking</span>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-2">
                <i class="bi bi-percent"></i>
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">30d Conversion</div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1 kpi-number" id="kpiConversion">{{ $stats['conversion_rate'] ?? 0 }}%</h3>
        </div>
        <span class="text-[11px] text-slate-500">Visitors to bookings</span>
    </div>
</div>

<!-- Real-Time Customer Communications & Leads Hub Summary -->
<div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-2">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-envelope-exclamation-fill"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Action Needed Inquiries</div>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-lg font-black text-rose-600 kpi-number" id="kpiNewInquiries">{{ number_format($stats['new_inquiries'] ?? 0) }}</span>
                    <a href="{{ route('admin.inquiries.index') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">View Inquiries <i class="bi bi-chevron-right text-[10px]"></i></a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3 sm:pt-0 sm:px-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-whatsapp"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">WhatsApp Leads Captured</div>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-lg font-black text-emerald-600 kpi-number" id="kpiWhatsappLeads">{{ number_format($stats['whatsapp_leads'] ?? 0) }}</span>
                    <a href="{{ route('admin.whatsapp.leads') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">View Leads <i class="bi bi-chevron-right text-[10px]"></i></a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3 sm:pt-0 sm:px-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-inbox-fill"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Contact Messages</div>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-lg font-black text-slate-900 kpi-number" id="kpiTotalInquiries">{{ number_format($stats['total_inquiries'] ?? 0) }}</span>
                    <span class="text-[11px] text-slate-400">Website submissions</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3 sm:pt-0 sm:px-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="bi bi-file-earmark-diff-fill"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Incomplete Drafts</div>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-lg font-black text-slate-500 kpi-number" id="kpiDrafts">{{ number_format($stats['drafts'] ?? 0) }}</span>
                    <span class="text-[11px] text-slate-400">Unfinished checkouts</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Grossing Tours & Quick Action Row -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-8 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
            <div>
                <h6 class="font-extrabold text-slate-900 text-sm md:text-base flex items-center gap-2 mb-0.5">
                    <i class="bi bi-trophy-fill text-amber-500"></i> Top Performing Tours
                </h6>
                <span class="text-xs text-slate-500">Top grossing experiences by revenue & booking volume</span>
            </div>
            <a href="{{ route('admin.tours.index') }}" class="px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-700 transition">
                Manage Inventory
            </a>
        </div>
        <div class="h-60">
            <canvas id="topToursChart"></canvas>
        </div>
    </div>

    <!-- Quick Payment Link Card -->
    <div class="lg:col-span-4 bg-gradient-to-br from-primary to-orange-600 text-white rounded-2xl shadow-xs overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-white/15 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white text-primary flex items-center justify-center font-bold text-lg">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div>
                <h6 class="font-extrabold text-white text-sm mb-0">Instant Payment Link</h6>
                <div class="text-[11px] text-white/80">Generate checkout link on the fly</div>
            </div>
        </div>
        <div class="p-5 flex-1 flex flex-col justify-between">
            <form id="quickPaymentForm" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" class="w-full bg-white/95 text-slate-900 text-xs font-semibold px-3 py-2 rounded-xl border-0 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-white outline-none" placeholder="Customer Full Name" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input type="email" name="email" class="w-full bg-white/95 text-slate-900 text-xs font-semibold px-3 py-2 rounded-xl border-0 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-white outline-none" placeholder="Email" required>
                    </div>
                    <div>
                        <input type="tel" name="phone" class="w-full bg-white/95 text-slate-900 text-xs font-semibold px-3 py-2 rounded-xl border-0 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-white outline-none" placeholder="Phone (+971...)" required>
                    </div>
                </div>
                <div>
                    <input type="text" name="description" class="w-full bg-white/95 text-slate-900 text-xs font-semibold px-3 py-2 rounded-xl border-0 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-white outline-none" placeholder="Activity / Service Description" required>
                </div>
                <div class="flex rounded-xl overflow-hidden bg-white/95">
                    <span class="inline-flex items-center px-3 bg-white/80 text-xs font-bold text-slate-800 border-r border-slate-200">AED</span>
                    <input type="number" name="amount" step="0.01" class="w-full bg-transparent text-slate-900 text-xs font-semibold px-3 py-2 border-0 placeholder:text-slate-400 focus:ring-0 outline-none" placeholder="0.00" required>
                </div>
                <button type="submit" class="w-full py-2.5 px-4 bg-slate-950 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Generate & Share Link</span> <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Email Marketing & Audience Performance Card -->
<div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shrink-0">
                <i class="bi bi-megaphone-fill"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h6 class="font-extrabold text-slate-900 text-sm md:text-base mb-0">Email Marketing & Audience Hub</h6>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Live</span>
                </div>
                <div class="text-xs text-slate-500">Manage subscriber lists, automated campaigns, and deliverability performance.</div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-send-fill"></i> Create Campaign
            </a>
            <a href="{{ route('admin.subscribers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-700 transition">
                <i class="bi bi-people-fill text-sky-500"></i> Audience Lists
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-700 transition">
                <i class="bi bi-palette-fill text-rose-500"></i> Templates
            </a>
            <a href="{{ route('admin.settings.mail') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-500 transition" title="Mailer Settings">
                <i class="bi bi-gear-fill"></i> SMTP
            </a>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-100">
        <div class="p-3 rounded-xl bg-slate-50 flex items-center gap-3">
            <i class="bi bi-people text-primary text-2xl"></i>
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Subscribers</div>
                <div class="text-base font-black text-slate-900">{{ number_format($stats['subscribers_count'] ?? 0) }}</div>
            </div>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 flex items-center gap-3">
            <i class="bi bi-broadcast text-amber-500 text-2xl"></i>
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Campaigns</div>
                <div class="text-base font-black text-slate-900">{{ number_format($stats['campaigns_count'] ?? 0) }}</div>
            </div>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 flex items-center gap-3">
            <i class="bi bi-check2-circle text-emerald-600 text-2xl"></i>
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dispatched Broadcasts</div>
                <div class="text-base font-black text-emerald-600">{{ number_format($stats['campaigns_sent'] ?? 0) }}</div>
            </div>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Campaign Delivery</div>
                <div class="text-xs text-emerald-600 font-bold flex items-center gap-1"><i class="bi bi-shield-check"></i> Ready & Armed</div>
            </div>
            <a href="{{ route('admin.campaigns.index') }}" class="px-2 py-1 rounded-full bg-white border border-slate-200 text-[10px] font-bold text-primary hover:bg-slate-50 transition">
                View <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <div>
            <h6 class="font-extrabold text-slate-900 text-sm md:text-base flex items-center gap-2 mb-0.5">
                <i class="bi bi-activity text-primary"></i> Recent Booking Inquiries & Reservations
            </h6>
            <span class="text-xs text-slate-500">Latest reservations processed by the system</span>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
            View All Bookings
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table datatable" id="recentBookingsTable">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="py-3 px-4">Booking Ref</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Tour / Activity</th>
                    <th class="py-3 px-4">Total</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right no-sort">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($recentBookings as $b)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3.5 px-4">
                        <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-black text-slate-900 hover:text-primary transition">#{{ $b->reference }}</a>
                        <div class="text-[11px] text-slate-400">{{ $b->created_at ? $b->created_at->format('M j, g:ia') : '' }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-900">{{ $b->name }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ $b->phone }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-800">{{ $b->tour_name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $b->tour_date ? $b->tour_date->format('M j, Y') : 'Open Date' }}</div>
                    </td>
                    <td class="py-3.5 px-4 font-black text-primary">AED {{ number_format($b->total) }}</td>
                    <td class="py-3.5 px-4 text-center">
                        @php
                            $badgeClass = [
                                'pending' => 'bg-amber-100 text-amber-800',
                                'confirmed' => 'bg-emerald-100 text-emerald-800',
                                'completed' => 'bg-sky-100 text-sky-800',
                                'cancelled' => 'bg-rose-100 text-rose-800'
                            ][$b->status] ?? 'bg-slate-100 text-slate-800';
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold capitalize {{ $badgeClass }}">{{ $b->status }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="w-8 h-8 rounded-full border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-500 transition" title="View Details">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @php
                                $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                $waMsg = 'Hi ' . $b->name . '! This is Dunes Discovery regarding your booking #' . $b->reference;
                            @endphp
                            <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-emerald-200 hover:bg-emerald-50 text-emerald-600 flex items-center justify-center transition" title="WhatsApp Customer">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">No recent bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Top Tours Revenue Breakdown Chart
    const topToursCtx = document.getElementById('topToursChart');
    if (topToursCtx) {
        const topToursData = @json($topTours);
        const labels = topToursData.map(t => t.tour_name ? t.tour_name.substring(0, 24) + '...' : 'Tour');
        const revenues = topToursData.map(t => t.revenue || 0);

        new Chart(topToursCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (AED)',
                    data: revenues,
                    backgroundColor: '#F69044',
                    borderRadius: 8,
                    barPercentage: 0.55
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'AED ' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    }

    // Quick Payment Form AJAX Handler
    $('#quickPaymentForm').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('admin.quick-payment') }}",
            type: "POST",
            data: formData,
            success: function(data) {
                hideLoader();
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Link Created!',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-clipboard me-1"></i> Copy Link',
                        cancelButtonText: '<i class="bi bi-whatsapp me-1"></i> WhatsApp Customer',
                        confirmButtonColor: '#F69044',
                        cancelButtonColor: '#25D366'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            navigator.clipboard.writeText(data.payment.link).then(() => {
                                Swal.fire('Copied!', 'Payment link copied to clipboard.', 'success');
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            const waUrl = `https://wa.me/${data.payment.phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Hi ' + data.payment.name + ', here is your payment link from Dunes Discovery Tourism: ' + data.payment.link)}`;
                            window.open(waUrl, '_blank');
                        }
                    });
                    $('#quickPaymentForm')[0].reset();
                }
            },
            error: function(xhr) {
                hideLoader();
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: msg,
                    confirmButtonColor: '#F69044'
                });
            }
        });
    });

    // Real-Time KPI Refresh with Shimmer Placeholders
    $('#btnRefreshDashboardKpis').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $('#kpiSyncIcon');
        const $status = $('#kpiSyncStatus');

        $btn.prop('disabled', true);
        $icon.addClass('kpi-sync-spin');
        $status.html('<span class="text-primary font-bold"><i class="bi bi-hourglass-split me-1"></i>Counting live from database...</span>');

        // Render shimmer loaders as placeholders on all counter cards
        $('.kpi-number').each(function() {
            $(this).data('cached-html', $(this).html());
            $(this).html('<span class="counter-shimmer"></span>');
        });

        $.ajax({
            url: "{{ route('admin.api.kpis') }}",
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
                    const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                    $('#kpiTotalBookings').text(Number(s.total).toLocaleString());
                    $('#kpiConfirmedBookings').text(Number(s.confirmed_and_completed || (s.confirmed + s.completed)).toLocaleString());
                    $('#kpiConfirmedCount').text(Number(s.confirmed).toLocaleString());
                    $('#kpiCompletedCount').text(Number(s.completed).toLocaleString());
                    $('#kpiPendingBookings').text(Number(s.pending).toLocaleString());
                    $('#kpiRevenue').text('AED ' + Number(s.revenue).toLocaleString());
                    $('#kpiAov').text('AED ' + Number(s.aov).toLocaleString());
                    $('#kpiConversion').text(s.conversion_rate + '%');

                    $('#kpiNewInquiries').text(Number(s.new_inquiries).toLocaleString());
                    $('#kpiWhatsappLeads').text(Number(s.whatsapp_leads).toLocaleString());
                    $('#kpiTotalInquiries').text(Number(s.total_inquiries).toLocaleString());
                    $('#kpiDrafts').text(Number(s.drafts).toLocaleString());

                    $status.html(`Live as of <span class="font-bold text-emerald-600">${nowTime}</span>`);

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'All Metrics Recounted Live!'
                    });
                } else {
                    $('.kpi-number').each(function() {
                        $(this).html($(this).data('cached-html'));
                    });
                    $status.text('Recount failed.');
                }
            },
            error: function() {
                $icon.removeClass('kpi-sync-spin');
                $btn.prop('disabled', false);
                $('.kpi-number').each(function() {
                    $(this).html($(this).data('cached-html'));
                });
                $status.html('<span class="text-rose-600">Sync error</span>');
            }
        });
    });
});
</script>
@endpush
@endsection
