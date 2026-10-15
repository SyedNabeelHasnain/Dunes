@extends('layouts.admin')

@section('page_title', 'Driver Performance & Cash Reconciliation Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-file-earmark-bar-graph text-primary"></i> Driver Performance & Cash Reconciliation
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Audit trips completed, passenger headcounts, and cash collections by safari captain.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.operations.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-truck-flatbed text-primary"></i>
                <span>Operations Hub</span>
            </a>
            <a href="{{ route('admin.operations.reports.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <span>Export CSV Report</span>
            </a>
        </div>
    </div>

    <!-- Date Range Filter Form -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.operations.reports') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:border-primary transition">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:border-primary transition">
                </div>
                <div class="pt-4">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition cursor-pointer">
                        Apply Filter
                    </button>
                </div>
            </div>

            <!-- Quick Date Shortcuts -->
            <div class="flex items-center gap-2 pt-4 flex-wrap">
                <a href="{{ route('admin.operations.reports', ['from_date' => now()->format('Y-m-d'), 'to_date' => now()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Today
                </a>
                <a href="{{ route('admin.operations.reports', ['from_date' => now()->startOfWeek()->format('Y-m-d'), 'to_date' => now()->endOfWeek()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    This Week
                </a>
                <a href="{{ route('admin.operations.reports', ['from_date' => now()->startOfMonth()->format('Y-m-d'), 'to_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    This Month
                </a>
                <a href="{{ route('admin.operations.reports', ['from_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'to_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Last Month
                </a>
            </div>
        </form>
    </div>

    <!-- 4 Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Trips</span>
            <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['total_trips']) }}</h3>
            <span class="text-[11px] text-slate-400">Completed in selected period</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Guests</span>
            <h3 class="text-2xl font-black text-primary">{{ number_format($summary['total_guests']) }}</h3>
            <span class="text-[11px] text-slate-400">Passengers transported</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Cash on Pickup Due</span>
            <h3 class="text-2xl font-black text-rose-600 font-mono">AED {{ number_format($summary['total_cash_to_collect'], 2) }}</h3>
            <span class="text-[11px] text-slate-400">To be settled by drivers</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Active Captains</span>
            <h3 class="text-2xl font-black text-emerald-600">{{ $summary['active_drivers'] }}</h3>
            <span class="text-[11px] text-slate-400">On active duty</span>
        </div>
    </div>

    <!-- Driver Breakdown Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-3.5 px-4">Safari Captain</th>
                        <th class="py-3.5 px-4">RTA License</th>
                        <th class="py-3.5 px-4">Assigned Vehicle</th>
                        <th class="py-3.5 px-4 text-center">Trips</th>
                        <th class="py-3.5 px-4 text-center">Adults</th>
                        <th class="py-3.5 px-4 text-center">Children</th>
                        <th class="py-3.5 px-4 text-center font-black">Total Guests</th>
                        <th class="py-3.5 px-4">Cash on Pickup</th>
                        <th class="py-3.5 px-4 text-center">Rating</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reportData as $row)
                    @php $d = $row['driver']; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.drivers.show', $d->id) }}" class="font-extrabold text-slate-900 hover:text-primary transition flex items-center gap-2">
                                <i class="bi bi-person-fill text-primary"></i>
                                <span>{{ $d->name }}</span>
                            </a>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $d->phone }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                            {{ $d->license_number ?: 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">
                            {{ $d->defaultVehicle?->short_plate ?: 'Shared Pool' }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                            {{ $row['trips'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-600">
                            {{ $row['adults'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-600">
                            {{ $row['children'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-black text-primary text-sm">
                            {{ $row['total_guests'] }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold font-mono text-rose-600">AED {{ number_format($row['cash_to_collect'], 2) }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="bi bi-star-fill text-amber-500 text-[10px]"></i> {{ number_format($d->rating, 2) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('admin.drivers.run-sheet', $d->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                <i class="bi bi-eye"></i> Run Sheet
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            No trip data found for this date range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
