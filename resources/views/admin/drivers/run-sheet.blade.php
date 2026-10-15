@extends('layouts.admin')

@section('page_title', 'Pickup Run Sheet: ' . $driver->name . ' (' . $targetDate->format('M j, Y') . ')')

@section('content')
<div class="space-y-6">
    <!-- Header with Print & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-card-checklist text-primary"></i> Captain Pickup Run Sheet
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                {{ $driver->name }} • {{ $driver->defaultVehicle?->display_name ?: 'Vehicle TBD' }} • {{ $targetDate->format('l, F j, Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('admin.drivers.run-sheet', $driver->id) }}" class="inline-flex">
                <input type="date" name="date" value="{{ $date }}" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs outline-none focus:border-primary transition" onchange="this.form.submit()">
            </form>

            @php
                $cleanDriverPhone = preg_replace('/[^0-9]/', '', $driver->phone);
                $runSheetText = "📋 *DUNES DISCOVERY TOURISM - DAILY RUN SHEET*\n";
                $runSheetText .= "👨‍✈️ Driver: {$driver->name}\n";
                $runSheetText .= "🚙 Vehicle: " . ($driver->defaultVehicle?->short_plate ?: 'Assigned Fleet') . "\n";
                $runSheetText .= "📅 Date: {$targetDate->format('M j, Y')}\n";
                $runSheetText .= "👥 Total Pickups: " . count($bookings) . " | Total Guests: {$totalGuests}\n";
                $runSheetText .= "------------------------------------\n";

                foreach($bookings as $idx => $b) {
                    $num = $idx + 1;
                    $pick = $b->pickup_time ?: '3:00 PM';
                    $gCount = (int)$b->adults + (int)$b->children;
                    $cleanGuestPhone = preg_replace('/[^0-9]/', '', $b->phone);
                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($b->pickup_location);
                    $collect = $b->payment_status === 'paid' ? "PAID ONLINE" : "COLLECT CASH: AED " . number_format($b->balance_due, 2);

                    $runSheetText .= "\n{$num}. *{$b->reference}* - ⏰ {$pick}\n";
                    $runSheetText .= "   👤 Guest: {$b->name} ({$gCount} Guests - {$b->adults}A/{$b->children}C)\n";
                    $runSheetText .= "   🏨 Hotel: {$b->pickup_location}\n";
                    $runSheetText .= "   🗺️ Map: {$mapsUrl}\n";
                    $runSheetText .= "   📞 Phone: {$b->phone}\n";
                    $runSheetText .= "   💵 {$collect}\n";
                }
                $runSheetText .= "\nSafe driving! Contact dispatch office at +971 50 245 6056 for any assistance.";
            @endphp

            <a href="https://wa.me/{{ $cleanDriverPhone }}?text={{ urlencode($runSheetText) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-whatsapp"></i>
                <span>Send to Driver's WhatsApp</span>
            </a>

            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition cursor-pointer">
                <i class="bi bi-printer"></i>
                <span>Print Run Sheet</span>
            </button>
        </div>
    </div>

    <!-- Summary Banner -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-primary text-xl font-bold">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <h4 class="font-black text-slate-900 text-sm">{{ $driver->name }}</h4>
                <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                    <span>{{ $driver->defaultVehicle?->display_name ?: 'Standard Desert 4x4' }}</span>
                    <span>•</span>
                    <span class="font-mono text-slate-700">{{ $driver->phone }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px] font-bold uppercase">Total Stops</span>
                <strong class="text-base text-slate-900 font-black">{{ count($bookings) }} Pickups</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] font-bold uppercase">Total Guests</span>
                <strong class="text-base text-primary font-black">{{ $totalGuests }} Passengers</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px] font-bold uppercase">Total Cash to Collect</span>
                <strong class="text-base text-rose-600 font-mono font-black">AED {{ number_format($totalCashToCollect, 2) }}</strong>
            </div>
        </div>
    </div>

    <!-- Pickups Sequence Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Pickup Sequence for {{ $targetDate->format('l, F j, Y') }}</span>
            <span class="text-xs text-slate-400 font-medium">Sorted by location & departure window</span>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($bookings as $idx => $b)
            @php
                $cleanGuestPhone = preg_replace('/[^0-9]/', '', $b->phone);
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($b->pickup_location);
            @endphp
            <div class="p-5 hover:bg-slate-50/80 transition flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-black text-sm flex items-center justify-center flex-shrink-0">
                        {{ $idx + 1 }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-900 text-sm">#{{ $b->reference }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-orange-50 text-primary border border-orange-200">
                                <i class="bi bi-clock"></i> {{ $b->pickup_time ?: '3:00 PM - 3:30 PM' }}
                            </span>
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold capitalize bg-slate-100 text-slate-600">
                                {{ str_replace('_', ' ', $b->pickup_status ?: 'scheduled') }}
                            </span>
                        </div>

                        <div class="font-extrabold text-slate-900 mt-1 text-sm">{{ $b->name }}</div>
                        
                        <div class="text-slate-600 mt-1 flex items-center gap-1.5 font-medium">
                            <i class="bi bi-geo-alt-fill text-primary"></i>
                            <strong>{{ $b->pickup_location }}</strong>
                            <a href="{{ $mapsUrl }}" target="_blank" class="text-primary hover:underline text-[11px] font-bold ms-1">
                                [Open Google Maps]
                            </a>
                        </div>

                        <div class="text-slate-500 mt-1 flex items-center gap-2 flex-wrap text-[11px]">
                            <span><strong class="text-slate-700">{{ $b->tour_name }}</strong> ({{ $b->tier_name }})</span>
                            <span>•</span>
                            <span class="text-primary font-bold">{{ (int)$b->adults + (int)$b->children }} Guests ({{ $b->adults }} Adults, {{ $b->children }} Children)</span>
                            @if($b->addons && $b->addons->count() > 0)
                                <span>•</span>
                                <span class="text-emerald-700 font-semibold">Addons: {{ $b->addons->pluck('addon_name')->implode(', ') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Side Actions & Payment Status -->
                <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end gap-2 w-full lg:w-auto pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                    <div>
                        @if($b->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="bi bi-check-circle-fill"></i> Paid Online
                            </span>
                        @else
                            <div class="text-right">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="bi bi-cash-stack"></i> Collect: AED {{ number_format($b->balance_due, 2) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 mt-1">
                        <a href="tel:{{ $b->phone }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            <i class="bi bi-telephone-fill"></i> Call
                        </a>
                        <a href="https://wa.me/{{ $cleanGuestPhone }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <i class="bi bi-calendar2-check text-4xl mb-2 block"></i>
                <p class="font-bold text-sm text-slate-700">No pickups assigned for this date</p>
                <p class="text-xs text-slate-400 mt-1">Choose a different date or assign bookings in the Logistics Hub.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
