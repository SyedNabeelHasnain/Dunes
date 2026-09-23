@extends('layouts.admin')

@section('page_title', 'Daily Tour Operations & Logistics Hub')

@section('content')
<div x-data="{
    assignModalOpen: false,
    formAction: '',
    pickupTime: '',
    driverName: '',
    driverPhone: '',
    vehiclePlate: '',
    openAssignModal(id, time, notes) {
        this.formAction = '/admin/operations/' + id + '/assign-driver';
        this.pickupTime = time || '';
        const m = notes ? notes.match(/\[DISPATCH:\s*Driver:\s*([^\|]+)\s*\|\s*Phone:\s*([^\|]+)\s*\|\s*Plate:\s*([^\]]+)\]/i) : null;
        if (m) {
            this.driverName = m[1].trim();
            this.driverPhone = m[2].trim();
            this.vehiclePlate = m[3].trim();
        } else {
            this.driverName = '';
            this.driverPhone = '';
            this.vehiclePlate = '';
        }
        this.assignModalOpen = true;
    },
    closeAssignModal() {
        this.assignModalOpen = false;
    }
}">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2 mb-1">
                <i class="bi bi-truck-flatbed text-primary"></i> Daily Tour Operations & Dispatch Hub
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mb-0">Cluster hotel pickups by Dubai zones, allocate 4x4 safari vehicles, and broadcast driver details to guests.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Date Selector Shortcuts -->
            <div class="inline-flex rounded-full bg-white p-1 border border-slate-200 shadow-xs">
                <a href="{{ route('admin.operations.index', ['date' => now()->format('Y-m-d')]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $selectedDate === now()->format('Y-m-d') ? 'bg-primary text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Today ({{ now()->format('M j') }})
                </a>
                <a href="{{ route('admin.operations.index', ['date' => now()->addDay()->format('Y-m-d')]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $selectedDate === now()->addDay()->format('Y-m-d') ? 'bg-primary text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Tomorrow ({{ now()->addDay()->format('M j') }})
                </a>
            </div>

            <form method="GET" action="{{ route('admin.operations.index') }}" class="inline-flex">
                <input type="date" name="date" class="px-3 py-1.5 rounded-full bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs outline-none focus:border-primary transition" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </form>

            <a href="{{ route('admin.operations.export', ['date' => $selectedDate]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition">
                <i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i>
                <span>Export Manifest</span>
            </a>
            <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-slate-950 hover:bg-slate-900 text-white shadow-xs text-xs font-bold transition cursor-pointer" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>Print Dispatch</span>
            </button>
        </div>
    </div>

    <!-- 4 Operations Operational Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pickups</span>
                <span class="w-8 h-8 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-sm"><i class="bi bi-geo-alt-fill"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1">{{ number_format($stats['total_bookings']) }}</h3>
            <span class="text-[11px] text-slate-400">Scheduled for {{ $targetDate->format('M j, Y') }}</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Guests</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm"><i class="bi bi-people-fill"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-primary mb-1">{{ number_format($stats['total_guests']) }}</h3>
            <span class="text-[11px] text-slate-400">{{ $stats['total_adults'] }} Adults, {{ $stats['total_children'] }} Children</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">4x4 Vehicles Needed</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="bi bi-truck"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1">{{ $stats['vehicles_needed'] }} <span class="text-xs font-normal text-slate-400">Vehicles</span></h3>
            <span class="text-[11px] text-slate-400">Est. 6 guests / Land Cruiser</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Confirmed Status</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="bi bi-check-all"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-emerald-600 mb-1">{{ $stats['confirmed_count'] }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total_bookings'] }}</span></h3>
            <span class="text-[11px] text-slate-400">{{ $stats['pending_count'] }} pending confirmations</span>
        </div>
    </div>

    <!-- Zone Clusters Sections -->
    @php $hasAnyBookings = false; @endphp
    @foreach($zones as $zoneName => $zoneBookings)
        @if(count($zoneBookings) > 0)
            @php $hasAnyBookings = true; @endphp
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-6 overflow-hidden">
                <div class="bg-slate-50/80 px-5 py-3 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary text-white shadow-2xs">
                            <i class="bi bi-geo-alt"></i> {{ $zoneName }}
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ count($zoneBookings) }} Booking{{ count($zoneBookings) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="text-xs font-bold text-slate-500">
                        Total Guests: {{ array_sum(array_map(fn($b) => (int)$b->adults + (int)$b->children, $zoneBookings)) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table no-datatable">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/40">
                                <th class="py-3 px-4">Ref & Time</th>
                                <th class="py-3 px-4">Guest Contact</th>
                                <th class="py-3 px-4">Hotel / Pickup Location</th>
                                <th class="py-3 px-4">Tour & Package</th>
                                <th class="py-3 px-4 text-center">Guests</th>
                                <th class="py-3 px-4">Add-ons</th>
                                <th class="py-3 px-4">Driver & Vehicle</th>
                                <th class="py-3 px-4 text-right">Broadcast</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($zoneBookings as $b)
                            @php
                                $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                $pickupTime = $b->pickup_time ?: '3:00 PM - 3:30 PM';
                                $driverName = 'Assigned Driver';
                                $plateNum = 'Land Cruiser';
                                
                                if ($b->special_requests && preg_match('/\[DISPATCH:\s*Driver:\s*([^\|]+)\s*\|\s*Phone:\s*([^\|]+)\s*\|\s*Plate:\s*([^\]]+)\]/i', $b->special_requests, $m)) {
                                    $driverName = trim($m[1]);
                                    $plateNum = trim($m[3]);
                                }

                                $dispatchMsg = "Hi {$b->name}! Your Dunes Discovery Tourism safari captain {$driverName} in {$plateNum} will pick you up at {$b->pickup_location} around {$pickupTime} on {$targetDate->format('M j')}. See you in the dunes!";
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-slate-900">#{{ $b->reference }}</div>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">{{ $b->status }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $b->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $b->phone }}</div>
                                    <a href="https://wa.me/{{ $waVal }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline mt-0.5"><i class="bi bi-whatsapp"></i> Chat</a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5"><i class="bi bi-building text-primary"></i> {{ $b->pickup_location }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5"><i class="bi bi-clock"></i> Pickup: <strong class="text-slate-700">{{ $pickupTime }}</strong></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">{{ $b->tour_name }}</div>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-primary border border-orange-200">{{ $b->tier_name }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="font-black text-slate-900">{{ (int)$b->adults + (int)$b->children }}</div>
                                    <span class="text-[10px] text-slate-400">{{ $b->adults }}A / {{ $b->children }}C</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($b->addons && $b->addons->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($b->addons as $addon)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class="bi bi-plus-circle"></i> {{ $addon->addon_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">None</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <button type="button" @click="openAssignModal('{{ $b->id }}', '{{ addslashes($b->pickup_time ?? '') }}', '{{ addslashes($b->special_requests ?? '') }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">
                                        <i class="bi bi-person-badge text-primary"></i>
                                        <span>{{ $driverName !== 'Assigned Driver' ? $driverName : 'Assign Driver' }}</span>
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($dispatchMsg) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition" title="Send WhatsApp Pickup Notice">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Send Notice</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach

    @if(!$hasAnyBookings)
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-10 text-center mb-6">
        <i class="bi bi-calendar2-check text-4xl text-slate-300 mb-3 block"></i>
        <h5 class="font-extrabold text-slate-800 text-base mb-1">No Tour Pickups Scheduled for {{ $targetDate->format('M j, Y') }}</h5>
        <p class="text-xs text-slate-500 mb-4 max-w-md mx-auto">All scheduled bookings for this date will be grouped automatically into Dubai logistics zones.</p>
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">View All Bookings</a>
    </div>
    @endif

    <!-- Driver Assignment Alpine Modal -->
    <div x-show="assignModalOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        <div @click.outside="closeAssignModal()"
             x-transition:enter="transition-all ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <form method="POST" :action="formAction">
                @csrf
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h5 class="text-sm font-extrabold text-slate-900 mb-0">Assign Safari Captain & Vehicle</h5>
                    <button type="button" @click="closeAssignModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pickup Time Window</label>
                        <input type="text" name="pickup_time" x-model="pickupTime" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="e.g. 3:00 PM - 3:30 PM">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Driver / Safari Captain Name</label>
                        <input type="text" name="driver_name" x-model="driverName" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="e.g. Captain Rashid Khan">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Driver Contact Phone</label>
                        <input type="text" name="driver_phone" x-model="driverPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="e.g. +971 50 123 4567">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">4x4 Vehicle Plate / Model</label>
                        <input type="text" name="vehicle_plate" x-model="vehiclePlate" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="e.g. Land Cruiser (Plate A 12345)">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeAssignModal()" class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-extrabold shadow-xs transition cursor-pointer">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('styles')
<style>
@media print {
    aside, header, #appLoader, .btn, form, [data-section], th:last-child, td:last-child {
        display: none !important;
    }
    div[class*="lg:pl-"] {
        padding-left: 0 !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
        font-size: 10pt;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #ccc !important;
        padding: 6px 8px !important;
    }
}
</style>
@endpush
@endsection