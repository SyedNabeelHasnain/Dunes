@extends('layouts.admin')

@section('page_title', 'Daily Tour Operations & Logistics Hub')

@section('content')
<div x-data="{
    assignModalOpen: false,
    batchModalOpen: false,
    selectedBookings: [],
    allSelected: false,
    formAction: '',
    bookingId: null,
    driverId: '',
    vehicleId: '',
    pickupTime: '',
    pickupStatus: 'scheduled',
    driverNotes: '',
    driversData: @js($drivers->keyBy('id')),
    vehiclesData: @js($vehicles->keyBy('id')),

    openAssignModal(booking) {
        this.bookingId = booking.id;
        this.formAction = '/admin/operations/' + booking.id + '/assign-driver';
        this.driverId = booking.driver_id ? String(booking.driver_id) : '';
        this.vehicleId = booking.vehicle_id ? String(booking.vehicle_id) : '';
        this.pickupTime = booking.pickup_time_slot || booking.pickup_time || '';
        this.pickupStatus = booking.pickup_status || 'scheduled';
        this.driverNotes = booking.driver_notes || '';
        this.assignModalOpen = true;
    },
    closeAssignModal() {
        this.assignModalOpen = false;
    },
    onDriverChange() {
        if (this.driverId && this.driversData[this.driverId]) {
            const defaultVeh = this.driversData[this.driverId].default_vehicle_id;
            if (defaultVeh && !this.vehicleId) {
                this.vehicleId = String(defaultVeh);
            }
        }
    },
    setTimeShortcut(time) {
        this.pickupTime = time;
    },
    toggleSelectAll(event, bookingIds) {
        this.allSelected = event.target.checked;
        this.selectedBookings = this.allSelected ? [...bookingIds] : [];
    },
    toggleBooking(id) {
        const strId = String(id);
        const idx = this.selectedBookings.indexOf(strId);
        if (idx > -1) {
            this.selectedBookings.splice(idx, 1);
            this.allSelected = false;
        } else {
            this.selectedBookings.push(strId);
        }
    },
    openBatchModal() {
        if (this.selectedBookings.length === 0) return;
        this.batchModalOpen = true;
    },
    closeBatchModal() {
        this.batchModalOpen = false;
    }
}">

    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-primary/10 text-primary font-black text-sm">
                    <i class="bi bi-truck-flatbed"></i>
                </span>
                <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight mb-0">
                    Daily Tour Operations & Dispatch Hub
                </h2>
            </div>
            <p class="text-xs md:text-sm text-slate-500 mb-0">
                Live Dubai logistics clusters, 4x4 safari allocations, vehicle capacity monitoring, and driver run sheets for <span class="font-bold text-slate-800">{{ $targetDate->format('l, F j, Y') }}</span>.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Date Shortcuts -->
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

            <!-- Quick Module Links -->
            <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition" title="Manage Safari Drivers">
                <i class="bi bi-person-badge text-primary"></i>
                <span class="hidden sm:inline">Captains</span>
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition" title="Manage 4x4 Fleet">
                <i class="bi bi-truck text-amber-500"></i>
                <span class="hidden sm:inline">Fleet</span>
            </a>
            <a href="{{ route('admin.operations.reports') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition" title="Driver Reports & Cash Reconciliation">
                <i class="bi bi-cash-stack text-emerald-600"></i>
                <span class="hidden sm:inline">Reports</span>
            </a>
            <a href="{{ route('admin.operations.export', ['date' => $selectedDate]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-slate-700 transition">
                <i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i>
                <span>Manifest</span>
            </a>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-slate-900 hover:bg-slate-800 text-white shadow-xs text-xs font-bold transition cursor-pointer" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span class="hidden sm:inline">Print</span>
            </button>
        </div>
    </div>

    <!-- 4 Operational Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pickups</span>
                <span class="w-8 h-8 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-sm"><i class="bi bi-geo-alt-fill"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1">{{ number_format($stats['total_bookings']) }}</h3>
            <span class="text-[11px] text-slate-400">Scheduled for {{ $targetDate->format('M j') }}</span>
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
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">4x4 Fleet Needed</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="bi bi-truck"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-slate-900 mb-1">{{ $stats['vehicles_needed'] }} <span class="text-xs font-normal text-slate-400">4x4 Vehicles</span></h3>
            <span class="text-[11px] text-slate-400">Standard 6 seats / Land Cruiser</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Driver Allocation</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="bi bi-check2-all"></i></span>
            </div>
            <h3 class="text-xl lg:text-2xl font-black text-emerald-600 mb-1">{{ $stats['assigned_count'] }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total_bookings'] }}</span></h3>
            @php $allocPct = $stats['total_bookings'] > 0 ? round(($stats['assigned_count'] / $stats['total_bookings']) * 100) : 0; @endphp
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1">
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ $allocPct }}%"></div>
            </div>
        </div>
    </div>

    <!-- Active Captains & Vehicle Fleet Roster on Duty Today -->
    @if($drivers->count() > 0)
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 mb-0.5">
                    <i class="bi bi-shield-check text-emerald-600"></i> Safari Captains & Fleet Live Capacity
                </h4>
                <p class="text-[11px] text-slate-500 mb-0">Passenger load per vehicle for {{ $targetDate->format('M j, Y') }} and instant 1-click WhatsApp run-sheet dispatch.</p>
            </div>
            <div class="text-xs font-bold text-slate-500">
                {{ count($driverLoads) }} of {{ $drivers->count() }} Captains Assigned
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($drivers as $driver)
                @php
                    $guestLoad = $driverLoads[$driver->id] ?? 0;
                    $cap = $driver->defaultVehicle?->passenger_capacity ?: 6;
                    $pct = $cap > 0 ? round(($guestLoad / $cap) * 100) : 0;
                    $cleanPhone = preg_replace('/[^0-9]/', '', $driver->phone);
                    $runSheetUrl = route('driver.run-sheet', $driver->access_token);
                    $waRunSheetMsg = "Hi Captain {$driver->name}! Here is your Dunes Discovery safari run sheet for {$targetDate->format('M j, Y')}: {$runSheetUrl} - Safe driving!";
                @endphp
                <div class="p-3.5 rounded-xl border {{ $guestLoad > 0 ? 'border-primary/20 bg-orange-50/20' : 'border-slate-200 bg-slate-50/40' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-8 h-8 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($driver->name, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 truncate">{{ $driver->name }}</div>
                                <div class="text-[10px] text-slate-500 truncate">{{ $driver->defaultVehicle?->short_plate ?: 'No vehicle set' }}</div>
                            </div>
                        </div>

                        <!-- Load bar -->
                        <div class="space-y-1 mb-2">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-slate-500">Seat Load:</span>
                                <span class="{{ $pct >= 100 ? 'text-rose-600' : ($pct >= 70 ? 'text-amber-600' : 'text-slate-800') }}">
                                    {{ $guestLoad }} / {{ $cap }} seats
                                    @if($pct >= 100) <span class="text-[9px] bg-rose-100 text-rose-700 px-1 rounded">FULL</span> @endif
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                <div class="{{ $pct >= 100 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }} h-full rounded-full transition-all" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between gap-1 text-[11px]">
                        <a href="{{ route('admin.drivers.run-sheet', $driver->id) }}" class="font-bold text-primary hover:underline flex items-center gap-1">
                            <i class="bi bi-card-checklist"></i> Run Sheet
                        </a>
                        @if($cleanPhone)
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waRunSheetMsg) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 hover:text-emerald-700 font-bold p-1 rounded" title="Send Run Sheet via WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Floating Batch Assignment Bar -->
    <div x-show="selectedBookings.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 inset-x-4 md:inset-x-auto md:right-8 z-40 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-4"
         style="display: none;">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-black flex items-center justify-center" x-text="selectedBookings.length"></span>
            <span class="text-xs font-bold text-slate-200">Bookings selected for assignment</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="selectedBookings = []; allSelected = false;" class="text-xs text-slate-400 hover:text-white px-2 py-1">Deselect</button>
            <button type="button" @click="openBatchModal()" class="px-4 py-1.5 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-extrabold shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                <i class="bi bi-person-check-fill"></i>
                <span>Batch Assign to Captain</span>
            </button>
        </div>
    </div>

    <!-- Dubai Logistics Zone Clusters -->
    @php 
        $hasAnyBookings = false; 
        $allBookingIds = $bookings->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();
    @endphp

    @foreach($zones as $zoneName => $zoneBookings)
        @if(count($zoneBookings) > 0)
            @php $hasAnyBookings = true; @endphp
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-6 overflow-hidden">
                <div class="bg-slate-50/80 px-5 py-3.5 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary text-white shadow-2xs">
                            <i class="bi bi-geo-alt-fill"></i> {{ $zoneName }}
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ count($zoneBookings) }} Booking{{ count($zoneBookings) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-bold text-slate-500">
                        <span>Total Guests: <strong class="text-slate-800">{{ array_sum(array_map(fn($b) => (int)$b->adults + (int)$b->children, $zoneBookings)) }}</strong></span>
                        <span>Estimated 4x4: <strong class="text-primary">{{ ceil(array_sum(array_map(fn($b) => (int)$b->adults + (int)$b->children, $zoneBookings)) / 6) }}</strong></span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table no-datatable">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/40">
                                <th class="py-3 px-4 w-10 text-center">
                                    <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary cursor-pointer" @change="toggleSelectAll($event, @js(array_map('strval', array_column($zoneBookings, 'id'))))">
                                </th>
                                <th class="py-3 px-4">Ref & Time</th>
                                <th class="py-3 px-4">Guest Contact</th>
                                <th class="py-3 px-4">Hotel / Pickup Location</th>
                                <th class="py-3 px-4">Tour & Tier</th>
                                <th class="py-3 px-4 text-center">Guests</th>
                                <th class="py-3 px-4">Financials</th>
                                <th class="py-3 px-4">Assigned Captain & 4x4 Fleet</th>
                                <th class="py-3 px-4 text-center">Pickup Status</th>
                                <th class="py-3 px-4 text-right">Guest Dispatch</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($zoneBookings as $b)
                            @php
                                $waVal = preg_replace('/[^0-9]/', '', $b->phone);
                                $pickupTime = $b->pickup_time_slot ?: ($b->pickup_time ?: '3:00 PM - 3:30 PM');
                                
                                $driverObj = $b->driver;
                                $vehicleObj = $b->vehicle ?: $driverObj?->defaultVehicle;
                                
                                $driverName = $driverObj?->name ?: ($b->assigned_driver_name ?: null);
                                $driverPhone = $driverObj?->phone ?: null;
                                $plateNum = $vehicleObj?->short_plate ?: ($b->assigned_vehicle_plate ?: null);
                                
                                $pickupStatus = $b->pickup_status ?: 'scheduled';
                                
                                $statusBadgeColors = match($pickupStatus) {
                                    'en_route' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'arrived' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'picked_up' => 'bg-orange-50 text-primary border-orange-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'no_show' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };

                                $dispatchCaptain = $driverName ?: 'Our Dunes Discovery Safari Captain';
                                $dispatchPlate = $plateNum ? "in vehicle {$plateNum}" : 'in our licensed 4x4 Land Cruiser';
                                $dispatchMsg = "Hi {$b->name}! Your Dunes Discovery safari captain {$dispatchCaptain} {$dispatchPlate} will pick you up at {$b->pickup_location} around {$pickupTime} on {$targetDate->format('M j')}. See you in the dunes!";
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition" :class="selectedBookings.includes('{{ $b->id }}') ? 'bg-orange-50/30' : ''">
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox" :checked="selectedBookings.includes('{{ $b->id }}')" @click="toggleBooking('{{ $b->id }}')" class="rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-black text-slate-900 hover:text-primary transition block">#{{ $b->reference }}</a>
                                    <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i class="bi bi-clock"></i> {{ $pickupTime }}
                                    </div>
                                    <span class="inline-block mt-1 px-2 py-0.2 rounded-full text-[10px] font-semibold {{ $b->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">{{ $b->status }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $b->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $b->phone }}</div>
                                    @if($waVal)
                                    <a href="https://wa.me/{{ $waVal }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline mt-0.5">
                                        <i class="bi bi-whatsapp"></i> Chat Guest
                                    </a>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="font-bold text-slate-800 flex items-start gap-1.5">
                                        <i class="bi bi-building text-primary mt-0.5 shrink-0"></i> 
                                        <span class="line-clamp-2">{{ $b->pickup_location ?: 'Location Pending' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 line-clamp-1">{{ $b->tour_name }}</div>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-primary border border-orange-200">{{ $b->tier_name }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="font-black text-slate-900 text-sm">{{ (int)$b->adults + (int)$b->children }}</div>
                                    <span class="text-[10px] text-slate-400">{{ $b->adults }}A / {{ $b->children }}C</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">AED {{ number_format($b->total, 2) }}</div>
                                    @if($b->payment_status === 'paid')
                                        <span class="inline-block px-2 py-0.2 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                                    @else
                                        <span class="inline-block px-2 py-0.2 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Due: AED {{ number_format($b->balance_due > 0 ? $b->balance_due : $b->total, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($driverName)
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <i class="bi bi-person-badge-fill text-primary"></i> {{ $driverName }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-mono">
                                                    {{ $plateNum ?: ($vehicleObj?->model_name ?: 'Fleet 4x4') }}
                                                </div>
                                            </div>
                                            <button type="button" @click="openAssignModal(@js($b))" class="text-slate-400 hover:text-primary p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer" title="Reassign Driver">
                                                <i class="bi bi-arrow-repeat text-sm"></i>
                                            </button>
                                        </div>
                                    @else
                                        <button type="button" @click="openAssignModal(@js($b))" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-bold text-amber-800 transition cursor-pointer">
                                            <i class="bi bi-person-plus-fill"></i>
                                            <span>Assign Captain</span>
                                        </button>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.operations.update-status', $b->id) }}">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold uppercase rounded-full px-2.5 py-1 border outline-none cursor-pointer {{ $statusBadgeColors }}">
                                            <option value="scheduled" {{ $pickupStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="en_route" {{ $pickupStatus === 'en_route' ? 'selected' : '' }}>En Route</option>
                                            <option value="arrived" {{ $pickupStatus === 'arrived' ? 'selected' : '' }}>Arrived</option>
                                            <option value="picked_up" {{ $pickupStatus === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                            <option value="completed" {{ $pickupStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="no_show" {{ $pickupStatus === 'no_show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($waVal)
                                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($dispatchMsg) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition" title="Broadcast Pickup Notice to Guest">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Notice</span>
                                    </a>
                                    @endif
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
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-12 text-center mb-6">
        <i class="bi bi-calendar2-check text-4xl text-slate-300 mb-3 block"></i>
        <h5 class="font-extrabold text-slate-800 text-base mb-1">No Tour Pickups Scheduled for {{ $targetDate->format('M j, Y') }}</h5>
        <p class="text-xs text-slate-500 mb-4 max-w-md mx-auto">All scheduled bookings for this date will be grouped automatically into Dubai logistics pickup zones.</p>
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">View All Bookings</a>
    </div>
    @endif

    <!-- Single Booking Driver Assignment Modal -->
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
                    <h5 class="text-sm font-extrabold text-slate-900 mb-0 flex items-center gap-2">
                        <i class="bi bi-person-badge text-primary"></i> Assign Safari Captain & 4x4 Vehicle
                    </h5>
                    <button type="button" @click="closeAssignModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Select Safari Captain</label>
                        <select name="driver_id" x-model="driverId" @change="onDriverChange()" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">-- No Driver Assigned (Clear) --</option>
                            @foreach($drivers as $d)
                                @php $load = $driverLoads[$d->id] ?? 0; @endphp
                                <option value="{{ $d->id }}">
                                    Captain {{ $d->name }} ({{ $d->phone }}) - {{ $load }} guests today
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Select 4x4 Fleet Vehicle</label>
                        <select name="vehicle_id" x-model="vehicleId" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">-- Auto-select Captain's Default Vehicle --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->display_name }} ({{ $v->passenger_capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Pickup Time Window</label>
                            <div class="flex gap-1 text-[10px]">
                                <button type="button" @click="setTimeShortcut('2:30 PM - 3:00 PM')" class="text-primary hover:underline">2:30 PM</button>
                                <span class="text-slate-300">&bull;</span>
                                <button type="button" @click="setTimeShortcut('3:00 PM - 3:30 PM')" class="text-primary hover:underline">3:00 PM</button>
                                <span class="text-slate-300">&bull;</span>
                                <button type="button" @click="setTimeShortcut('3:30 PM - 4:00 PM')" class="text-primary hover:underline">3:30 PM</button>
                            </div>
                        </div>
                        <input type="text" name="pickup_time_slot" x-model="pickupTime" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="e.g. 3:00 PM - 3:30 PM">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Pickup Status</label>
                            <select name="pickup_status" x-model="pickupStatus" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                                <option value="scheduled">Scheduled</option>
                                <option value="en_route">En Route</option>
                                <option value="arrived">Arrived</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="completed">Completed</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Dispatcher Notes</label>
                            <input type="text" name="driver_notes" x-model="driverNotes" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary outline-none transition" placeholder="e.g. Call concierge on arrival">
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeAssignModal()" class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-extrabold shadow-xs transition cursor-pointer">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Assignment Modal -->
    <div x-show="batchModalOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        <div @click.outside="closeBatchModal()"
             x-transition:enter="transition-all ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.operations.batch-assign') }}">
                @csrf
                <template x-for="id in selectedBookings" :key="id">
                    <input type="hidden" name="booking_ids[]" :value="id">
                </template>

                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h5 class="text-sm font-extrabold text-slate-900 mb-0 flex items-center gap-2">
                            <i class="bi bi-collection-fill text-primary"></i> Batch Assign Bookings
                        </h5>
                        <p class="text-[11px] text-slate-500 mb-0">Assigning <strong class="text-slate-800" x-text="selectedBookings.length"></strong> bookings to a single safari captain.</p>
                    </div>
                    <button type="button" @click="closeBatchModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Select Safari Captain *</label>
                        <select name="driver_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">-- Choose Captain --</option>
                            @foreach($drivers as $d)
                                @php $load = $driverLoads[$d->id] ?? 0; @endphp
                                <option value="{{ $d->id }}">
                                    Captain {{ $d->name }} ({{ $d->phone }}) - Currently {{ $load }} guests
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Select 4x4 Fleet Vehicle (Optional)</label>
                        <select name="vehicle_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">-- Default Captain Vehicle --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->display_name }} ({{ $v->passenger_capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Pickup Time Slot (Optional)</label>
                        <input type="text" name="pickup_time_slot" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary outline-none transition" placeholder="e.g. 3:00 PM - 3:30 PM">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeBatchModal()" class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-extrabold shadow-xs transition cursor-pointer">Confirm Batch Assignment</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('styles')
<style>
@media print {
    aside, header, #appLoader, .btn, form, [data-section], th:first-child, td:first-child, th:last-child, td:last-child {
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