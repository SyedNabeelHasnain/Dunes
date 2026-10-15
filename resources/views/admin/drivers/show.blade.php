@extends('layouts.admin')

@section('page_title', 'Captain Dossier: ' . $driver->name)

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-person-vcard text-primary"></i> Safari Captain Dossier
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Detailed operational record, assigned vehicle, guest ratings, and dispatch history.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.drivers.run-sheet', $driver->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                <i class="bi bi-card-checklist text-amber-400"></i>
                <span>Today's Run Sheet</span>
            </a>
            <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition">
                <i class="bi bi-pencil"></i>
                <span>Edit Profile</span>
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-arrow-left"></i> Drivers List
            </a>
        </div>
    </div>

    <!-- Header Dossier Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-orange-100 border-2 border-orange-200 overflow-hidden flex-shrink-0 flex items-center justify-center shadow-xs">
                    @if($driver->photo)
                        <img src="{{ asset($driver->photo) }}" alt="{{ $driver->name }}" class="w-full h-full object-cover">
                    @else
                        <i class="bi bi-person-fill text-primary text-4xl"></i>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl md:text-2xl font-black text-slate-900">{{ $driver->name }}</h1>
                        @if($driver->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active On Duty
                            </span>
                        @elseif($driver->status === 'on_leave')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> On Leave
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                Inactive
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="bi bi-star-fill text-amber-500"></i> {{ number_format($driver->rating, 2) }} Rating
                        </span>
                    </div>

                    <div class="flex items-center gap-4 text-xs text-slate-500 mt-2 flex-wrap">
                        <span><i class="bi bi-credit-card-2-front text-slate-400"></i> License: <strong class="font-mono text-slate-700">{{ $driver->license_number ?: 'N/A' }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-telephone text-slate-400"></i> <strong class="font-mono text-slate-700">{{ $driver->phone }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-translate text-slate-400"></i> Languages: <strong class="text-slate-700">{{ $driver->languages_list }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Quick Communication & Mobile Run Sheet Link -->
            @php $cleanPhone = preg_replace('/[^0-9]/', '', $driver->phone); @endphp
            <div class="flex flex-col sm:flex-row md:flex-col items-start sm:items-center md:items-end gap-2 w-full md:w-auto pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                <div class="flex items-center gap-2">
                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp
                    </a>
                    <a href="tel:{{ $driver->phone }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        <i class="bi bi-telephone-fill"></i> Direct Call
                    </a>
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-1">
                    <i class="bi bi-phone"></i>
                    <span>Driver Mobile Portal Link:</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ route('driver.run-sheet', $driver->access_token) }}'); alert('Mobile run-sheet link copied to clipboard!');" class="text-primary hover:underline font-bold cursor-pointer">
                        Copy Run Sheet URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Today's Pickups</span>
            <h3 class="text-2xl font-black text-primary">{{ $todayBookings->count() }}</h3>
            <span class="text-[11px] text-slate-400">Scheduled for today</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Lifetime Completed</span>
            <h3 class="text-2xl font-black text-slate-900">{{ number_format($lifetimeStats['total_bookings']) }}</h3>
            <span class="text-[11px] text-slate-400">Tour bookings completed</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Guests</span>
            <h3 class="text-2xl font-black text-emerald-600">{{ number_format($lifetimeStats['total_guests']) }}</h3>
            <span class="text-[11px] text-slate-400">Passengers transported</span>
        </div>

        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Assigned Vehicle</span>
            <h3 class="text-base font-black text-slate-900 truncate">
                {{ $driver->defaultVehicle?->short_plate ?: 'None' }}
            </h3>
            <span class="text-[11px] text-slate-400 truncate block">
                {{ $driver->defaultVehicle?->model_name ?: 'Assign in operations' }}
            </span>
        </div>
    </div>

    <!-- Today's Schedule Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Today's Assigned Pickups ({{ now()->format('M j, Y') }})
            </h3>
            <a href="{{ route('admin.operations.index', ['date' => now()->format('Y-m-d')]) }}" class="text-xs font-bold text-primary hover:underline">
                View in Logistics Hub &rarr;
            </a>
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
                        <th class="py-3 px-4">Cash to Collect</th>
                        <th class="py-3 px-4 text-center">Pickup Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayBookings as $b)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-black text-slate-900">#{{ $b->reference }}</span>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $b->pickup_time ?: '3:00 PM' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $b->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $b->phone }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 flex items-center gap-1"><i class="bi bi-building text-primary"></i> {{ $b->pickup_location }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $b->tour_name }}</div>
                            <span class="text-[10px] text-primary font-bold">{{ $b->tier_name }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="font-black text-slate-900">{{ (int)$b->adults + (int)$b->children }}</div>
                            <span class="text-[10px] text-slate-400">{{ $b->adults }}A / {{ $b->children }}C</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($b->payment_status === 'paid')
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-bold text-xs"><i class="bi bi-check-circle-fill"></i> Paid Online</span>
                            @else
                                <span class="font-bold text-rose-600 font-mono text-xs">AED {{ number_format($b->balance_due, 2) }}</span>
                                <span class="text-[10px] text-slate-400 block font-semibold">Collect Cash</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold capitalize
                                {{ $b->pickup_status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                  ($b->pickup_status === 'picked_up' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                                  ($b->pickup_status === 'en_route' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200')) }}">
                                {{ str_replace('_', ' ', $b->pickup_status ?: 'scheduled') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            No pickups assigned to {{ $driver->name }} for today.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
