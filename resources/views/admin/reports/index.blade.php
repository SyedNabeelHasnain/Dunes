@extends('layouts.admin')

@section('page_title', 'Enterprise Business Intelligence & Reporting Hub')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition flex items-center gap-1">
        <i class="bi bi-house-door-fill text-xs"></i> <span>Dashboard</span>
    </a>
    <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
    <span class="text-slate-800 font-semibold">Reports & BI Hub</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Enterprise Reporting &amp; BI Hub</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Live Analytics
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Power BI & Crystal Reports grade business intelligence: multi-channel attribution, financial reconciliation, draft recovery funnels, and dynamic custom report builder.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-printer text-slate-500"></i> Print / PDF
            </a>
            <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-file-earmark-arrow-down-fill text-emerald-200"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- 4 High-Impact BI Executive Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Confirmed Revenue -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Confirmed Revenue</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-currency-dollar text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">AED {{ number_format($biKpis['revenue'], 2) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Collected: <strong class="text-emerald-700">AED {{ number_format($biKpis['collected_revenue'], 2) }}</strong></span>
                <span>AOV: <strong class="text-slate-800">AED {{ number_format($biKpis['avg_order_value']) }}</strong></span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- 2. Confirmed vs Total Bookings -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Booking Volume</span>
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-calendar2-check-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($biKpis['confirmed_bookings']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Total Received: <strong class="text-slate-800">{{ number_format($biKpis['total_bookings']) }}</strong></span>
                <span>Active Ratio: <strong class="text-primary">{{ $biKpis['total_bookings'] > 0 ? round(($biKpis['confirmed_bookings'] / $biKpis['total_bookings']) * 100) : 0 }}%</strong></span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-primary"></div>
        </div>

        <!-- 3. Recoverable Cart Abandonment -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Recoverable Drafts</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-cart-x-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-700">AED {{ number_format($biKpis['recoverable_drafts_val'], 2) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Drafts: <strong class="text-amber-800">{{ number_format($biKpis['drafts_count']) }}</strong></span>
                <span>Recovered: <strong class="text-emerald-700">{{ number_format($biKpis['converted_drafts_count']) }}</strong></span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-amber-500"></div>
        </div>

        <!-- 4. Omnichannel Inquiries & Leads -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Inbound Leads Hub</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="bi bi-chat-dots-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($biKpis['total_inquiries']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>WhatsApp: <strong class="text-emerald-700">{{ number_format($biKpis['whatsapp_leads']) }}</strong></span>
                <span>Web Forms: <strong class="text-indigo-700">{{ number_format($biKpis['contact_inquiries']) }}</strong></span>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-indigo-500"></div>
        </div>
    </div>

    <!-- Power BI Visual Analytics: Acquisition Channel Attribution & Revenue Share -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- 1. Revenue Share by Acquisition Channel -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Acquisition Channel Revenue Share
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Where your highest-value tourists & bookings originate</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-6 h-56 relative flex items-center justify-center">
                    <canvas id="channelShareChart"></canvas>
                </div>
                <div class="md:col-span-6 space-y-2 max-h-56 overflow-y-auto pr-1">
                    @forelse($channelAttribution as $ch => $info)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                        <div class="flex items-center gap-2">
                            @include('partials.admin.channel-badge', ['channel' => $ch])
                        </div>
                        <div class="text-right">
                            <span class="font-black text-slate-900">AED {{ number_format($info['revenue']) }}</span>
                            <span class="text-[10px] text-slate-400 block font-semibold">{{ $info['share'] }}% ({{ $info['bookings'] }} bks)</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 italic p-3 text-center">No channel revenue data in selected range.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 2. Tour Package Performance Matrix -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-trophy-fill text-amber-500"></i> Top Revenue Tour Packages
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Commercial volume and average ticket size</p>
                </div>
            </div>
            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                @forelse(array_slice($tourPerformance, 0, 5) as $tp)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <div class="truncate max-w-[200px]">
                        <span class="font-extrabold text-slate-800 block truncate" title="{{ $tp['name'] }}">{{ $tp['name'] }}</span>
                        <span class="text-[10px] text-slate-400">{{ $tp['bookings'] }} bookings | {{ $tp['guests'] }} passengers</span>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-black text-primary block">AED {{ number_format($tp['revenue']) }}</span>
                        <span class="text-[10px] text-slate-400">AOV: AED {{ number_format($tp['aov']) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-xs text-slate-400 italic p-3 text-center">No tour bookings recorded in selected range.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Dynamic Custom Report Builder (Crystal Reports / Power BI style) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3 mb-5">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-sliders text-primary"></i> Dynamic Custom Report Builder
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Filter across any dataset, select specific date intervals, and audit real-time records.</p>
            </div>
            <!-- Dataset Selector Pills -->
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold">
                <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['dataset' => 'bookings'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $dataset === 'bookings' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="bi bi-calendar2-check-fill me-1 text-primary"></i> Bookings
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['dataset' => 'drafts'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $dataset === 'drafts' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="bi bi-cart-x-fill me-1 text-amber-500"></i> Drafts
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['dataset' => 'whatsapp_leads'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $dataset === 'whatsapp_leads' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="bi bi-whatsapp me-1 text-emerald-600"></i> WhatsApp Leads
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['dataset' => 'inquiries'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $dataset === 'inquiries' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="bi bi-envelope-paper me-1 text-indigo-600"></i> Inquiries
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['dataset' => 'tours'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $dataset === 'tours' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="bi bi-trophy me-1 text-amber-600"></i> Tours
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
            <input type="hidden" name="dataset" value="{{ $dataset }}">

            <!-- Quick Date Preset Buttons -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Time Horizon Presets</label>
                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $presets = [
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'last_7_days' => 'Last 7 Days',
                            'this_month' => 'This Month',
                            'last_month' => 'Last Month',
                            'this_quarter' => 'This Quarter',
                            'year_to_date' => 'Year to Date',
                            'all_time' => 'All Time',
                        ];
                    @endphp
                    @foreach($presets as $key => $lbl)
                        <button type="submit" name="date_preset" value="{{ $key }}"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition border cursor-pointer {{ $datePreset === $key ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Detailed Parameter Filters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end pt-2">
                <!-- Channel Filter -->
                <div class="lg:col-span-3">
                    <label for="repChannel" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Acquisition Channel</label>
                    <select name="traffic_channel" id="repChannel" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-hidden">
                        <option value="">All Traffic Channels</option>
                        @foreach($channelsList as $ch)
                            <option value="{{ $ch }}" {{ request('traffic_channel') === $ch ? 'selected' : '' }}>{{ $ch }}</option>
                        @endforeach
                    </select>
                </div>

                @if($dataset === 'bookings')
                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <label for="repStatus" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Booking Status</label>
                    <select name="status" id="repStatus" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-hidden">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div class="lg:col-span-2">
                    <label for="repPayment" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Payment Status</label>
                    <select name="payment_status" id="repPayment" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-hidden">
                        <option value="">All Payments</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                @endif

                @if(in_array($dataset, ['bookings', 'drafts']))
                <!-- Tour Package -->
                <div class="lg:col-span-3">
                    <label for="repTour" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Package</label>
                    <select name="tour_id" id="repTour" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-hidden">
                        <option value="">All Tour Packages</option>
                        @foreach($availableTours as $t)
                            <option value="{{ $t->id }}" {{ request('tour_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Custom From/To Dates -->
                <div class="lg:col-span-2">
                    <label for="repFromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                    <input type="date" name="from_date" id="repFromDate" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ $resolvedFrom }}">
                </div>

                <div class="lg:col-span-2">
                    <label for="repToDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">To Date</label>
                    <input type="date" name="to_date" id="repToDate" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ $resolvedTo }}">
                </div>

                <!-- Submit / Reset -->
                <div class="lg:col-span-2 flex items-center gap-1.5">
                    <button type="submit" class="flex-1 py-2 px-3.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-1 cursor-pointer">
                        <i class="bi bi-funnel-fill"></i> Run Report
                    </button>
                    <a href="{{ route('admin.reports.index', ['dataset' => $dataset]) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>

        <!-- Dynamic Results Summary Strip -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="font-bold text-slate-800">
                    <i class="bi bi-database text-primary me-1"></i> Dataset: <span class="text-primary font-black">{{ $reportData['entity'] }}</span>
                </span>
                <span class="text-slate-500">
                    Records: <strong class="text-slate-900 font-mono">{{ number_format($reportData['summary']['count']) }}</strong>
                </span>
                @if(isset($reportData['summary']['total_value']) && $reportData['summary']['total_value'] > 0)
                <span class="text-slate-500">
                    Total Value: <strong class="text-emerald-700 font-mono">AED {{ number_format($reportData['summary']['total_value'], 2) }}</strong>
                </span>
                @endif
                @if(isset($reportData['summary']['avg_order_value']) && $reportData['summary']['avg_order_value'] > 0)
                <span class="text-slate-500">
                    AOV: <strong class="text-slate-800 font-mono">AED {{ number_format($reportData['summary']['avg_order_value'], 2) }}</strong>
                </span>
                @endif
                @if(isset($reportData['summary']['total_guests']) && $reportData['summary']['total_guests'] > 0)
                <span class="text-slate-500">
                    Passengers: <strong class="text-slate-800 font-mono">{{ number_format($reportData['summary']['total_guests']) }}</strong>
                </span>
                @endif
            </div>
            <div>
                <span class="text-[11px] text-slate-400">
                    Period: {{ $resolvedFrom ?: 'Beginning' }} to {{ $resolvedTo ?: 'Present' }}
                </span>
            </div>
        </div>

        <!-- Dynamic Data Table -->
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse text-xs text-slate-700 datatable" id="biReportTable">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        @foreach($reportData['columns'] as $key => $colLabel)
                            <th class="py-3 px-3.5">{{ $colLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reportData['rows'] as $r)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        @foreach($reportData['columns'] as $colKey => $colLabel)
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                @if($colKey === 'traffic_channel')
                                    @include('partials.admin.channel-badge', ['channel' => $r[$colKey] ?? 'Direct'])
                                @elseif($colKey === 'reference')
                                    <span class="font-mono font-bold text-slate-900">{{ $r[$colKey] }}</span>
                                @elseif(str_contains($colKey, 'total') || str_contains($colKey, 'paid') || str_contains($colKey, 'revenue'))
                                    <span class="font-bold font-mono text-slate-900">{{ $r[$colKey] }}</span>
                                @else
                                    {{ $r[$colKey] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($reportData['columns']) }}" class="py-12 text-center text-slate-400 italic">
                            No records matched your selected criteria and date horizon.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Channel Revenue Share Chart
    const channelCanvas = document.getElementById('channelShareChart');
    if (channelCanvas) {
        const labels = {!! json_encode(array_keys($channelAttribution)) !!};
        const revenues = {!! json_encode(array_values(array_map(fn($c) => $c['revenue'], $channelAttribution))) !!};
        
        const palette = [
            '#F59E0B', '#3B82F6', '#10B981', '#06B6D4', '#EC4899', '#8B5CF6', '#6366F1', '#64748B', '#0F172A'
        ];

        new Chart(channelCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [{
                    data: revenues.length && revenues.some(v => v > 0) ? revenues : [1],
                    backgroundColor: palette.slice(0, Math.max(1, labels.length)),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': AED ' + Number(ctx.raw).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
