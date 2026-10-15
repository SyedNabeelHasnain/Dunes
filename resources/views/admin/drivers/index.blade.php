@extends('layouts.admin')

@section('page_title', 'Safari Drivers & Field Captains')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i> Safari Drivers & Field Captains
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Manage certified DTCM desert safari captains, vehicle assignments, and dispatch schedules.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.operations.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-truck-flatbed text-primary"></i>
                <span>Operations Hub</span>
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-truck"></i>
                <span>Vehicle Fleet</span>
            </a>
            <a href="{{ route('admin.drivers.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add Safari Captain</span>
            </a>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Captains</span>
                <span class="w-8 h-8 rounded-xl bg-orange-50 text-primary flex items-center justify-center text-sm"><i class="bi bi-people-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</h3>
            <span class="text-[11px] text-slate-400">Registered RTA licensed drivers</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Active On Duty</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="bi bi-check-circle-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-emerald-600">{{ $stats['active'] }}</h3>
            <span class="text-[11px] text-slate-400">Available for tour dispatch</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">On Leave</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="bi bi-calendar-x-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-amber-600">{{ $stats['on_leave'] }}</h3>
            <span class="text-[11px] text-slate-400">Temporarily off rotation</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Inactive</span>
                <span class="w-8 h-8 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center text-sm"><i class="bi bi-slash-circle-fill"></i></span>
            </div>
            <h3 class="text-2xl font-black text-slate-600">{{ $stats['inactive'] }}</h3>
            <span class="text-[11px] text-slate-400">Archived or pending renewal</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.drivers.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <i class="bi bi-search absolute start-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search driver name, phone, license..." class="w-full ps-9 pe-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 transition outline-none">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:border-primary transition" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active On Duty</option>
                    <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                @if(request('search') || request('status'))
                    <a href="{{ route('admin.drivers.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Drivers Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-3.5 px-4">Safari Captain</th>
                        <th class="py-3.5 px-4">RTA License</th>
                        <th class="py-3.5 px-4">Contact & WhatsApp</th>
                        <th class="py-3.5 px-4">Assigned Vehicle</th>
                        <th class="py-3.5 px-4">Languages</th>
                        <th class="py-3.5 px-4 text-center">Rating</th>
                        <th class="py-3.5 px-4 text-center">Trips</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($drivers as $d)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $d->phone);
                        $todayToursCount = $d->bookings->count();
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 border border-orange-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($d->photo)
                                        <img src="{{ asset($d->photo) }}" alt="{{ $d->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-person-fill text-primary text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.drivers.show', $d->id) }}" class="font-extrabold text-slate-900 hover:text-primary transition text-sm flex items-center gap-1">
                                        {{ $d->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-400">
                                        Today: <strong class="text-slate-700">{{ $todayToursCount }} Pickup{{ $todayToursCount === 1 ? '' : 's' }}</strong>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                            {{ $d->license_number ?: 'Pending' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-800 font-mono">{{ $d->phone }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline">
                                    <i class="bi bi-whatsapp"></i> Chat
                                </a>
                                @if($d->email)
                                    <span class="text-slate-300">•</span>
                                    <a href="mailto:{{ $d->email }}" class="text-[11px] text-slate-500 hover:underline truncate max-w-[120px]">{{ $d->email }}</a>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($d->defaultVehicle)
                                <div class="font-bold text-slate-800">{{ $d->defaultVehicle->model_name }}</div>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $d->defaultVehicle->short_plate }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">None Assigned</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-[11px] text-slate-600">{{ $d->languages_list }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="bi bi-star-fill text-amber-500 text-[10px]"></i> {{ number_format($d->rating, 2) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-extrabold text-slate-800">
                            {{ number_format($d->total_trips) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <form method="POST" action="{{ route('admin.drivers.toggle-status', $d->id) }}">
                                @csrf
                                <button type="submit" class="cursor-pointer transition group" title="Click to toggle status">
                                    @if($d->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @elseif($d->status === 'on_leave')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 group-hover:bg-amber-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> On Leave
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
                                <a href="{{ route('admin.drivers.run-sheet', $d->id) }}" class="p-1.5 text-slate-400 hover:text-primary transition rounded-lg hover:bg-slate-100" title="Today's Run Sheet">
                                    <i class="bi bi-card-checklist text-sm"></i>
                                </a>
                                <a href="{{ route('admin.drivers.show', $d->id) }}" class="p-1.5 text-slate-400 hover:text-primary transition rounded-lg hover:bg-slate-100" title="View Dossier">
                                    <i class="bi bi-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.drivers.edit', $d->id) }}" class="p-1.5 text-slate-400 hover:text-sky-600 transition rounded-lg hover:bg-slate-100" title="Edit">
                                    <i class="bi bi-pencil text-sm"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.drivers.destroy', $d->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to remove this driver record?');">
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
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <i class="bi bi-person-badge text-3xl mb-2 block"></i>
                            <p class="font-bold text-sm text-slate-700">No safari drivers found</p>
                            <p class="text-xs text-slate-400 mt-1">Get started by registering your first safari driver.</p>
                            <a href="{{ route('admin.drivers.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold">Add Safari Captain</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($drivers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $drivers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
