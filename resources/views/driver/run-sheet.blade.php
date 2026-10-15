<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Driver Run Sheet - {{ $driver->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="https://dunesdiscoverytourism.com/assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased pb-20">

    <!-- Sticky Header -->
    <header class="sticky top-0 z-40 bg-slate-950/90 backdrop-blur-md border-b border-slate-800 px-4 py-3 shadow-lg">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-500/20 border border-orange-500/40 flex items-center justify-center text-primary font-bold">
                    <i class="bi bi-person-badge-fill text-lg"></i>
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-white leading-tight">{{ $driver->name }}</h1>
                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 font-mono">
                        <i class="bi bi-truck text-primary"></i>
                        <span>{{ $driver->defaultVehicle?->short_plate ?: 'Assigned 4x4' }}</span>
                    </div>
                </div>
            </div>

            <!-- Date Switcher -->
            <form method="GET" action="{{ route('driver.run-sheet', $token) }}">
                <input type="date" name="date" value="{{ $selectedDate }}" class="px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 text-xs font-bold text-white outline-none focus:border-primary" onchange="this.form.submit()">
            </form>
        </div>
    </header>

    <!-- Main Mobile Container -->
    <main class="max-w-md mx-auto px-4 pt-4 space-y-4">
        <!-- Date & Summary Pill -->
        <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-between shadow-sm">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Manifest Date</span>
                <strong class="text-xs font-black text-white">{{ $targetDate->format('l, M j, Y') }}</strong>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Today's Load</span>
                <strong class="text-xs font-black text-primary">{{ count($bookings) }} Stops • {{ $totalGuests }} Guests</strong>
            </div>
        </div>

        @if($totalCashToCollect > 0)
        <!-- Cash Collection Alert Bar -->
        <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-amber-400 font-bold">
                <i class="bi bi-cash-stack text-base"></i>
                <span>Cash to Collect Today:</span>
            </div>
            <strong class="text-amber-400 font-mono font-black text-sm">AED {{ number_format($totalCashToCollect, 2) }}</strong>
        </div>
        @endif

        @if (session('success'))
        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Pickups List -->
        <div class="space-y-3">
            @forelse($bookings as $idx => $b)
            @php
                $cleanGuestPhone = preg_replace('/[^0-9]/', '', $b->phone);
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($b->pickup_location);
                $isPaid = ($b->payment_status === 'paid');
            @endphp
            <div class="p-4 rounded-2xl bg-slate-800 border {{ $b->pickup_status === 'completed' ? 'border-emerald-500/40 opacity-75' : ($b->pickup_status === 'picked_up' ? 'border-sky-500/50' : 'border-slate-700') }} shadow-md space-y-3 transition">
                <!-- Top Row: Sequence #, Time, Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-primary/20 text-primary font-black text-xs flex items-center justify-center">
                            {{ $idx + 1 }}
                        </span>
                        <span class="text-xs font-black text-white">#{{ $b->reference }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold bg-orange-500/20 text-orange-400 border border-orange-500/30">
                            <i class="bi bi-clock"></i> {{ $b->pickup_time ?: '3:00 PM' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold capitalize
                            {{ $b->pickup_status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' :
                              ($b->pickup_status === 'picked_up' ? 'bg-sky-500/20 text-sky-400' :
                              ($b->pickup_status === 'en_route' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-700 text-slate-300')) }}">
                            {{ str_replace('_', ' ', $b->pickup_status ?: 'scheduled') }}
                        </span>
                    </div>
                </div>

                <!-- Guest & Hotel Info -->
                <div>
                    <h3 class="text-sm font-black text-white">{{ $b->name }}</h3>
                    <div class="text-xs text-slate-300 font-semibold mt-0.5 flex items-start gap-1.5">
                        <i class="bi bi-geo-alt-fill text-primary mt-0.5 flex-shrink-0"></i>
                        <span>{{ $b->pickup_location }}</span>
                    </div>
                </div>

                <!-- Tour & Passengers -->
                <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/60 text-[11px] space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Tour:</span>
                        <strong class="text-white">{{ $b->tour_name }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Guests:</span>
                        <strong class="text-primary">{{ (int)$b->adults + (int)$b->children }} Persons ({{ $b->adults }} Adults, {{ $b->children }} Children)</strong>
                    </div>
                    @if($b->addons && $b->addons->count() > 0)
                    <div class="flex items-center justify-between pt-1 border-t border-slate-800">
                        <span class="text-slate-400">Add-ons:</span>
                        <strong class="text-emerald-400">{{ $b->addons->pluck('addon_name')->implode(', ') }}</strong>
                    </div>
                    @endif
                </div>

                <!-- Payment Status -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-slate-400 font-medium">Payment:</span>
                    @if($isPaid)
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-400">
                            <i class="bi bi-check-circle-fill"></i> Paid Online
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 font-black text-rose-400 font-mono">
                            <i class="bi bi-cash"></i> Collect Cash: AED {{ number_format($b->balance_due, 2) }}
                        </span>
                    @endif
                </div>

                <!-- Action Buttons: Map, Call, WhatsApp -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <a href="{{ $mapsUrl }}" target="_blank" class="py-2.5 px-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="bi bi-map-fill text-primary"></i> Map
                    </a>
                    <a href="tel:{{ $b->phone }}" class="py-2.5 px-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="bi bi-telephone-fill text-sky-400"></i> Call
                    </a>
                    <a href="https://wa.me/{{ $cleanGuestPhone }}" target="_blank" class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="bi bi-whatsapp"></i> Chat
                    </a>
                </div>

                <!-- Pickup Status Update Buttons -->
                <div class="pt-2 border-t border-slate-700/60 flex items-center gap-2">
                    <form method="POST" action="{{ route('driver.update-status', [$token, $b->id]) }}" class="flex-1">
                        @csrf
                        @if($b->pickup_status === 'scheduled')
                            <input type="hidden" name="status" value="en_route">
                            <button type="submit" class="w-full py-2 px-3 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 border border-amber-500/30 font-bold text-xs flex items-center justify-center gap-1.5 transition">
                                <i class="bi bi-car-front"></i> Mark En Route
                            </button>
                        @elseif($b->pickup_status === 'en_route')
                            <input type="hidden" name="status" value="arrived">
                            <button type="submit" class="w-full py-2 px-3 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-400 border border-sky-500/30 font-bold text-xs flex items-center justify-center gap-1.5 transition">
                                <i class="bi bi-geo-alt-fill"></i> Mark Arrived at Hotel
                            </button>
                        @elseif($b->pickup_status === 'arrived')
                            <input type="hidden" name="status" value="picked_up">
                            <button type="submit" class="w-full py-2 px-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-xs">
                                <i class="bi bi-check-circle-fill"></i> Mark Guests Onboard
                            </button>
                        @elseif($b->pickup_status === 'picked_up')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-xs">
                                <i class="bi bi-flag-fill"></i> Mark Completed
                            </button>
                        @else
                            <div class="text-center text-[11px] text-emerald-400 font-bold py-1 w-full flex items-center justify-center gap-1">
                                <i class="bi bi-check-all text-sm"></i> Trip Completed
                            </div>
                        @endif
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-500 bg-slate-800/60 rounded-2xl border border-slate-700/60">
                <i class="bi bi-calendar2-check text-4xl mb-2 text-slate-600 block"></i>
                <p class="font-bold text-sm text-slate-300">No pickups scheduled for today</p>
                <p class="text-xs text-slate-500 mt-1">Enjoy your rest day or check tomorrow's schedule above.</p>
            </div>
            @endforelse
        </div>

        <!-- Help Hotline -->
        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Dunes Operations Dispatch</span>
            <div class="text-xs font-bold text-slate-200">
                Emergency Hotline: <a href="tel:+971502456056" class="text-primary font-mono">+971 50 245 6056</a>
            </div>
        </div>
    </main>

</body>
</html>
