@extends('layouts.admin')

@section('page_title', 'WhatsApp Leads & Analytics')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-whatsapp text-emerald-500"></i> WhatsApp Leads Hub & Analytics
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Track real-time chat click leads, customer phone numbers, tour preferences, and visitor telemetry.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.whatsapp.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-emerald-200 hover:bg-emerald-50 text-emerald-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-gear-fill text-primary"></i> Settings
            </a>
        </div>
    </div>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total WhatsApp Leads</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                    <i class="bi bi-whatsapp text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">All-time chat click inquiries</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Today's Leads</span>
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i class="bi bi-calendar2-day-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['today'] ?? 0) }}</div>
                <div class="text-xs font-bold text-emerald-600 mt-1 flex items-center gap-1">
                    <i class="bi bi-lightning-fill"></i> Active today
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">This Month</span>
                <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-200">
                    <i class="bi bi-graph-up text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['this_month'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">Current month acquisition</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mobile Traffic %</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200">
                    <i class="bi bi-phone-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $stats['mobile_pct'] ?? 0 }}%</div>
                <div class="text-xs text-slate-400 mt-1">Mobile vs desktop users</div>
            </div>
        </div>
    </div>

    <!-- 2 Interactive Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-emerald-500"></i> 14-Day WhatsApp Leads Trend
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily volume of customer WhatsApp inquiries</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="leadsTrendChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Tour Interest
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Top inquired experiences</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="tourBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <form method="GET" action="{{ route('admin.whatsapp.leads') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label for="leadSearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Leads</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" id="leadSearch" class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-primary transition" placeholder="Name, Phone, Tour..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="lg:col-span-3">
                <label for="tourFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Package</label>
                <select name="tour_name" id="tourFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Tours</option>
                    @foreach($availableTours as $tName)
                        <option value="{{ $tName }}" {{ request('tour_name') === $tName ? 'selected' : '' }}>{{ $tName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="deviceFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Device Type</label>
                <select name="device_type" id="deviceFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Devices</option>
                    <option value="mobile" {{ request('device_type') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="desktop" {{ request('device_type') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="tablet" {{ request('device_type') === 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="fromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                <input type="date" name="from_date" id="fromDate" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ request('from_date') }}">
            </div>
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('admin.whatsapp.leads') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- WhatsApp Leads Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="whatsappLeadsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 no-sort no-export no-colvis w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 whatsapp-select-all" title="Select All">
                        </th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Interest Context</th>
                        <th class="py-3 px-4">Message Snippet</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Device</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 no-export">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 whatsapp-row-select" value="{{ $lead->id }}">
                        </td>
                        <td class="py-3 px-4" data-order="{{ \Carbon\Carbon::parse($lead->created_at)->timestamp }}">
                            <div class="text-xs font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('M j, Y') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('g:ia') }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $lead->name ?: 'Visitor' }}</div>
                            <div class="text-emerald-600 font-mono font-bold text-xs mt-0.5">
                                {{ $lead->phone }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                {{ $lead->tour_name ?: 'General Inquiry' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-slate-500 text-xs truncate max-w-xs" title="{{ $lead->message_text }}">
                                {{ Str::limit($lead->message_text, 65) }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                <i class="bi bi-geo-alt-fill text-rose-500"></i> {{ $lead->city ?: 'Unknown' }}, {{ $lead->country ?: '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 capitalize">
                                {{ $lead->device_type ?: 'Desktop' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs view-lead-btn cursor-pointer" 
                                        title="View Full Lead Details"
                                        data-id="{{ $lead->id }}"
                                        data-name="{{ $lead->name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-context="{{ $lead->tour_name ?: 'General Inquiry' }}"
                                        data-url="{{ $lead->page_url }}"
                                        data-msg="{{ $lead->message_text }}"
                                        data-ip="{{ $lead->client_ip ?? $lead->ip_address ?? 'Not Available' }}"
                                        data-location="{{ ($lead->city ?? 'Unknown') . ', ' . ($lead->country ?? '') }}"
                                        data-device="{{ ucfirst($lead->device_type ?? '-') }} ({{ $lead->os_name ?? '-' }} / {{ $lead->browser_name ?? '-' }})">
                                    <i class="bi bi-search text-xs"></i>
                                </button>
                                <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-lead cursor-pointer" 
                                        title="Permanently Delete WhatsApp Lead & All Footprints"
                                        onclick="promptPermanentDeleteLead({{ $lead->id }}, '{{ addslashes($lead->name ?: 'Visitor') }}', '{{ addslashes($lead->phone ?: '') }}')">
                                    <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400 text-xs">
                            <i class="bi bi-whatsapp text-4xl block mb-2 opacity-50"></i>
                            No WhatsApp leads found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Batch Bulk Action Toolbar -->
    <div id="whatsappBulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/90 backdrop-blur-md text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-4 transition-all duration-300 border border-slate-700">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500 text-white" id="whatsappSelectedCount">0</span>
            <span class="text-xs font-semibold text-white">leads selected</span>
        </div>
        <div class="w-px h-5 bg-slate-700"></div>
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition cursor-pointer" id="whatsappBulkDelete">
            <i class="bi bi-trash3"></i> Delete Selected
        </button>
        <button type="button" class="text-slate-400 hover:text-white transition cursor-pointer text-xs" id="whatsappBulkClear" title="Deselect all">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

<!-- Modal: View Details (Alpine.js) -->
<div x-data="{ open: false }" @open-lead-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-16 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                        <i class="bi bi-whatsapp text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">WhatsApp Lead Telemetry & Details</h3>
                        <div class="text-[11px] text-slate-400">Captured click-to-chat inquiry footprint</div>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Client Details -->
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <h4 class="text-xs font-black uppercase text-emerald-600 tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-person-fill"></i> Customer Identity
                    </h4>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Customer Name</div>
                        <div class="text-sm font-black text-slate-900" id="modalCustomerName">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Phone Number</div>
                        <div class="text-xs font-mono font-bold text-emerald-600" id="modalCustomerPhone">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Interest Context</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-primary text-white mt-0.5" id="modalTourName">-</span>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Source Page</div>
                        <a href="#" target="_blank" rel="noopener" class="text-xs text-primary hover:underline truncate block" id="modalPageUrl">-</a>
                    </div>
                </div>

                <!-- Telemetry -->
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <h4 class="text-xs font-black uppercase text-primary tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-geo-alt-fill"></i> Visitor Telemetry
                    </h4>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Client IP Address</div>
                        <div class="text-xs font-mono font-bold text-slate-800" id="modalClientIp">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimated Location</div>
                        <div class="text-xs font-bold text-slate-800" id="modalLocation">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Device Environment</div>
                        <div class="text-xs font-medium text-slate-700" id="modalDevice">-</div>
                    </div>
                </div>

                <!-- Message text -->
                <div class="sm:col-span-2 p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-1.5">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Prefilled Customer Message</div>
                    <p class="text-xs text-slate-800 p-3 bg-white border border-slate-200 rounded-xl leading-relaxed whitespace-pre-wrap" id="modalMessageText">-</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer" id="modalDeleteLeadBtn">
                    <i class="bi bi-trash3"></i> Delete Lead
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Close</button>
                    <a href="#" id="modalDirectChatBtn" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                        <i class="bi bi-whatsapp"></i> Open Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 14-Day Acquisition Trend Chart
    const trendCtx = document.getElementById('leadsTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'WhatsApp Leads',
                    data: trendData.map(d => d.count),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
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

    // Tour Breakdown Doughnut Chart
    const tourCtx = document.getElementById('tourBreakdownChart');
    if (tourCtx) {
        const tourData = @json($tourBreakdown);
        new Chart(tourCtx, {
            type: 'doughnut',
            data: {
                labels: tourData.map(t => t.tour_label.length > 20 ? t.tour_label.substring(0, 20) + '...' : t.tour_label),
                datasets: [{
                    data: tourData.map(t => t.count),
                    backgroundColor: ['#F58F43', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'],
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

    // View Details Modal Handler
    $('.view-lead-btn').on('click', function() {
        const btn = $(this);
        const name = btn.data('name') || 'Visitor';
        const phone = btn.data('phone') || 'No Phone';
        const context = btn.data('context') || 'General Inquiry';
        const url = btn.data('url') || '#';
        const msg = btn.data('msg') || 'No message text provided.';
        const ip = btn.data('ip') || '-';
        const loc = btn.data('location') || 'Unknown Location';
        const device = btn.data('device') || '-';

        $('#modalCustomerName').text(name);
        $('#modalCustomerPhone').text(phone);
        $('#modalTourName').text(context);
        $('#modalPageUrl').attr('href', url).text(url);
        $('#modalClientIp').text(ip);
        $('#modalLocation').text(loc);
        $('#modalDevice').text(device);
        $('#modalMessageText').text(msg);

        if (phone && phone !== 'No Phone') {
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            const chatMsg = encodeURIComponent('Hi ' + name + '! Thanks for reaching out to Dunes Discovery Tourism regarding ' + context + '. How can we assist you?');
            $('#modalDirectChatBtn').attr('href', `https://wa.me/${cleanPhone}?text=${chatMsg}`).show();
        } else {
            $('#modalDirectChatBtn').hide();
        }

        const id = btn.data('id');
        $('#modalDeleteLeadBtn').off('click').on('click', function() {
            window.dispatchEvent(new CustomEvent('close-lead-modal'));
            promptPermanentDeleteLead(id, name, phone);
        });

        window.dispatchEvent(new CustomEvent('open-lead-modal'));
    });

    // WhatsApp Batch Bulk Selection & Processing
    const $waBulkBar = $('#whatsappBulkBar');
    const $waSelectAll = $('.whatsapp-select-all');
    const $waCountBadge = $('#whatsappSelectedCount');

    function updateWaBulkBar() {
        const checkedBoxes = $('.whatsapp-row-select:checked');
        const count = checkedBoxes.length;
        $waCountBadge.text(count);
        if (count > 0) {
            $waBulkBar.removeClass('hidden');
        } else {
            $waBulkBar.addClass('hidden');
        }
    }

    $waSelectAll.on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.whatsapp-row-select').prop('checked', isChecked);
        updateWaBulkBar();
    });

    $(document).on('change', '.whatsapp-row-select', function() {
        const total = $('.whatsapp-row-select').length;
        const checked = $('.whatsapp-row-select:checked').length;
        $waSelectAll.prop('checked', total > 0 && total === checked);
        updateWaBulkBar();
    });

    $('#whatsappBulkClear').on('click', function() {
        $('.whatsapp-row-select, .whatsapp-select-all').prop('checked', false);
        updateWaBulkBar();
    });

    $('#whatsappBulkDelete').on('click', function(e) {
        e.preventDefault();
        const selectedIds = $('.whatsapp-row-select:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        // Step 1: Bulk Caution Dialog
        Swal.fire({
            title: 'CAUTION: Permanent Bulk Deletion',
            html: `
                <div class="text-left text-xs text-slate-600 leading-relaxed">
                    <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                        <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                        <strong>IRREVERSIBLE BULK ACTION:</strong> You are about to permanently purge <strong class="text-slate-900">${selectedIds.length}</strong> selected WhatsApp lead inquiry record(s) from the database.
                    </div>
                    <p class="mb-2 text-slate-800">This will permanently delete all associated customer messages, client IP telemetry, and request log analytics. No orphaned footprints will remain.</p>
                    <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final confirmation?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
            cancelButtonText: 'Cancel (Keep Leads)',
            focusCancel: true
        }).then((step1Result) => {
            if (!step1Result.isConfirmed) return;

            // Step 2: Final Safeguard Double Confirmation
            Swal.fire({
                title: 'Confirm Bulk Deletion',
                html: `
                    <div class="text-left text-xs">
                        <p class="text-slate-800 mb-2">To confirm permanent deletion of <strong>${selectedIds.length}</strong> WhatsApp leads and all analytics footprints, type <strong>DELETE</strong> in capital letters below:</p>
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
                confirmButtonText: '<i class="bi bi-trash3-fill mr-1"></i> PURGE ALL SELECTED',
                cancelButtonText: 'Abort',
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: (inputValue) => {
                    if (!inputValue || inputValue.trim() !== 'DELETE') {
                        Swal.showValidationMessage('Verification failed! You must type "DELETE" exactly.');
                        return false;
                    }
                    return $.ajax({
                        url: '{{ route("admin.whatsapp.bulk") }}',
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
                        text: step2Result.value.message || 'Selected WhatsApp leads permanently deleted.',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        });
    });
});

/**
 * 2-Step Cautionary Double Confirmation for Permanent Single WhatsApp Lead Deletion
 */
window.promptPermanentDeleteLead = function(id, name, phone) {
    const verifyTarget = (phone && phone !== 'No Phone') ? phone.trim() : name.trim();

    // Step 1: Caution Dialog
    Swal.fire({
        title: 'CAUTION: Permanent Lead Deletion',
        html: `
            <div class="text-left text-xs text-slate-600 leading-relaxed">
                <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-semibold">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate WhatsApp lead inquiry <strong class="text-slate-900">#${id} (${name})</strong> from the database.
                </div>
                <div class="p-3 mb-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="font-bold text-slate-700 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                    <ul class="list-disc pl-4 text-slate-500 text-xs space-y-1">
                        <li>WhatsApp Lead Record & Contact Information (${phone || 'No Phone'})</li>
                        <li>Prefilled inquiry message & campaign source URL</li>
                        <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                        <li>Client IP & Geolocation Audit Trail</li>
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
        cancelButtonText: 'Cancel (Keep Lead)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the verification value below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 font-mono text-sm font-bold">
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
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill mr-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim() !== verifyTarget) {
                    Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/whatsapp-leads/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete WhatsApp lead.');
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
                    text: step2Result.value.message || 'WhatsApp lead and all analytics footprints permanently deleted.',
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
