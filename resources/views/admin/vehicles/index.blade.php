@extends('layouts.admin')

@section('page_title', 'Vehicle Fleet & 4x4 Inventory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-truck text-primary"></i> Safari Vehicle Fleet
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Manage Desert 4x4 Land Cruisers, VIP Patrols, DTCM inspection permits, and maintenance schedules.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.operations.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-truck-flatbed text-primary"></i>
                <span>Operations Hub</span>
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-person-badge"></i>
                <span>Drivers & Captains</span>
            </a>
            <a href="{{ route('admin.vehicles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add Fleet Vehicle</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Fleet</span>
                <span class="w-8 h-8 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-sm"><i class="bi bi-truck"></i></span>
            </div>
            <h3 class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</h3>
            <span class="text-[11px] text-slate-400">Total licensed vehicles</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Active On Duty</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="bi bi-check-circle-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-emerald-600">{{ $stats['active'] }}</h3>
            <span class="text-[11px] text-slate-400">Available for tour departures</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Maintenance</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="bi bi-tools"></i></span>
            </div>
            <h3 class="text-2xl font-black text-amber-600">{{ $stats['maintenance'] }}</h3>
            <span class="text-[11px] text-slate-400">Under service or inspection</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Capacity</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm"><i class="bi bi-people-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-sky-600">{{ $stats['total_seats'] }} <span class="text-xs font-normal text-slate-400">Seats</span></h3>
            <span class="text-[11px] text-slate-400">Active passenger seats</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.vehicles.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <i class="bi bi-search absolute start-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search model, plate, DTCM permit..." class="w-full ps-9 pe-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 transition outline-none">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:border-primary transition" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active On Duty</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>In Maintenance</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                @if(request('search') || request('status'))
                    <a href="{{ route('admin.vehicles.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-3.5 px-4">Vehicle Model</th>
                        <th class="py-3.5 px-4">Plate Number</th>
                        <th class="py-3.5 px-4 text-center">Capacity</th>
                        <th class="py-3.5 px-4">DTCM Permit</th>
                        <th class="py-3.5 px-4">Assigned Captain</th>
                        <th class="py-3.5 px-4">Registration Expiry</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vehicles as $v)
                    @php $todayPickups = $v->bookings->count(); @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="bi bi-truck text-primary"></i>
                                <a href="{{ route('admin.vehicles.show', $v->id) }}" class="hover:text-primary transition">
                                    {{ $v->model_name }}
                                </a>
                            </div>
                            <span class="text-[11px] text-slate-400">{{ $v->year }} • {{ $v->color }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-900 border border-slate-300 shadow-2xs">
                                <span class="text-[10px] uppercase text-slate-500 font-sans">{{ $v->plate_city }}</span>
                                <strong class="text-primary">{{ $v->plate_code }}</strong>
                                <span>{{ $v->plate_number }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-sky-50 text-sky-700 border border-sky-200">
                                <i class="bi bi-people-fill text-[11px]"></i> {{ $v->passenger_capacity }} Seats
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600 font-semibold">
                            {{ $v->dtcm_permit_number ?: 'Pending' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($v->defaultDriver)
                                <a href="{{ route('admin.drivers.show', $v->defaultDriver->id) }}" class="font-bold text-slate-800 hover:text-primary transition flex items-center gap-1.5">
                                    <i class="bi bi-person-fill text-primary"></i>
                                    <span>{{ $v->defaultDriver->name }}</span>
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $v->defaultDriver->phone }}</span>
                            @else
                                <span class="text-slate-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($v->mulkiya_expiry_date)
                                <div class="font-semibold text-slate-700">{{ $v->mulkiya_expiry_date->format('M j, Y') }}</div>
                                @if($v->mulkiya_expiry_date->isPast())
                                    <span class="text-[10px] font-bold text-rose-600 block">EXPIRED</span>
                                @elseif($v->mulkiya_expiry_date->diffInDays(now()) < 30)
                                    <span class="text-[10px] font-bold text-amber-600 block">Expires soon</span>
                                @endif
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <form method="POST" action="{{ route('admin.vehicles.toggle-status', $v->id) }}">
                                @csrf
                                <button type="submit" class="cursor-pointer transition group" title="Click to toggle status">
                                    @if($v->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @elseif($v->status === 'maintenance')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 group-hover:bg-amber-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Maintenance
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 group-hover:bg-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.vehicles.show', $v->id) }}" class="p-1.5 text-slate-400 hover:text-primary transition rounded-lg hover:bg-slate-100" title="View Dossier">
                                    <i class="bi bi-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.vehicles.edit', $v->id) }}" class="p-1.5 text-slate-400 hover:text-sky-600 transition rounded-lg hover:bg-slate-100" title="Edit">
                                    <i class="bi bi-pencil text-sm"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.vehicles.destroy', $v->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to retire this vehicle from active fleet?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition rounded-lg hover:bg-slate-100 cursor-pointer" title="Delete">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="bi bi-truck text-3xl mb-2 block"></i>
                            <p class="font-bold text-sm text-slate-700">No fleet vehicles found</p>
                            <p class="text-xs text-slate-400 mt-1">Add your 4x4 Land Cruisers, VIP Patrols, and tour buses.</p>
                            <a href="{{ route('admin.vehicles.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold">Add Fleet Vehicle</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
