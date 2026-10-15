@extends('layouts.admin')

@section('page_title', 'Vehicle Dossier: ' . $vehicle->display_name)

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-truck text-primary"></i> Vehicle Dossier
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Technical specifications, registration cards, assigned captain, and tour schedules.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition">
                <i class="bi bi-pencil"></i>
                <span>Edit Vehicle</span>
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-arrow-left"></i> Fleet List
            </a>
        </div>
    </div>

    <!-- Header Vehicle Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-orange-100 border border-orange-200 flex-shrink-0 flex items-center justify-center text-primary text-3xl shadow-xs">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl md:text-2xl font-black text-slate-900">{{ $vehicle->model_name }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg font-mono font-black text-sm bg-slate-100 text-slate-900 border border-slate-300 shadow-2xs">
                            <span class="text-[10px] uppercase text-slate-500 font-sans">{{ $vehicle->plate_city }}</span>
                            <strong class="text-primary">{{ $vehicle->plate_code }}</strong>
                            <span>{{ $vehicle->plate_number }}</span>
                        </span>
                        @if($vehicle->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active On Duty
                            </span>
                        @elseif($vehicle->status === 'maintenance')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> In Maintenance
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 text-xs text-slate-500 mt-2 flex-wrap">
                        <span>Year: <strong class="text-slate-700">{{ $vehicle->year }}</strong></span>
                        <span>•</span>
                        <span>Color: <strong class="text-slate-700">{{ $vehicle->color }}</strong></span>
                        <span>•</span>
                        <span>Capacity: <strong class="text-primary font-bold">{{ $vehicle->passenger_capacity }} Guests</strong></span>
                        <span>•</span>
                        <span>DTCM Permit: <strong class="font-mono text-slate-700">{{ $vehicle->dtcm_permit_number ?: 'N/A' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Assigned Driver Badge -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 w-full md:w-auto">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Primary Safari Captain</span>
                @if($vehicle->defaultDriver)
                    <div class="flex items-center gap-2">
                        <i class="bi bi-person-fill text-primary text-base"></i>
                        <div>
                            <a href="{{ route('admin.drivers.show', $vehicle->defaultDriver->id) }}" class="font-bold text-slate-900 hover:text-primary transition text-xs">
                                {{ $vehicle->defaultDriver->name }}
                            </a>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $vehicle->defaultDriver->phone }}</div>
                        </div>
                    </div>
                @else
                    <span class="text-xs text-slate-400 italic">Unassigned (Shared Pool)</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Registration & Equipment -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Mulkiya (Registration)</span>
            @if($vehicle->mulkiya_expiry_date)
                <h4 class="text-base font-black text-slate-900">{{ $vehicle->mulkiya_expiry_date->format('M j, Y') }}</h4>
                <span class="text-xs font-semibold {{ $vehicle->mulkiya_expiry_date->isPast() ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $vehicle->mulkiya_expiry_date->isPast() ? 'EXPIRED' : $vehicle->mulkiya_expiry_date->diffForHumans() }}
                </span>
            @else
                <span class="text-xs text-slate-400">Not recorded</span>
            @endif
        </div>

        <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Commercial Insurance</span>
            @if($vehicle->insurance_expiry_date)
                <h4 class="text-base font-black text-slate-900">{{ $vehicle->insurance_expiry_date->format('M j, Y') }}</h4>
                <span class="text-xs font-semibold {{ $vehicle->insurance_expiry_date->isPast() ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $vehicle->insurance_expiry_date->isPast() ? 'EXPIRED' : $vehicle->insurance_expiry_date->diffForHumans() }}
                </span>
            @else
                <span class="text-xs text-slate-400">Not recorded</span>
            @endif
        </div>

        <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Today's Pickups</span>
            <h4 class="text-base font-black text-primary">{{ $todayBookings->count() }} Pickups</h4>
            <span class="text-xs text-slate-400">Scheduled for today</span>
        </div>
    </div>

    @if($vehicle->features && count($vehicle->features) > 0)
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
        <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3">Vehicle Desert Equipment & Features</h4>
        <div class="flex flex-wrap gap-2">
            @foreach($vehicle->features as $feat)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-orange-50 text-primary border border-orange-200">
                    <i class="bi bi-shield-check"></i> {{ $feat }}
                </span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Today's Assigned Bookings -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Today's Assigned Departures ({{ now()->format('M j, Y') }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-3 px-4">Ref & Time</th>
                        <th class="py-3 px-4">Guest</th>
                        <th class="py-3 px-4">Pickup Location</th>
                        <th class="py-3 px-4">Tour & Tier</th>
                        <th class="py-3 px-4 text-center">Guests</th>
                        <th class="py-3 px-4">Assigned Captain</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayBookings as $b)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-black text-slate-900">#{{ $b->reference }}</span>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $b->pickup_time ?: '3:00 PM' }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ $b->name }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ $b->pickup_location }}</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $b->tour_name }}</div>
                            <span class="text-[10px] text-primary font-bold">{{ $b->tier_name }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-black text-slate-900">
                            {{ (int)$b->adults + (int)$b->children }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($b->driver)
                                <span class="font-bold text-slate-800">{{ $b->driver->name }}</span>
                            @else
                                <span class="text-slate-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold capitalize bg-slate-100 text-slate-600">
                                {{ str_replace('_', ' ', $b->pickup_status ?: 'scheduled') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            No departures scheduled for this vehicle today.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
