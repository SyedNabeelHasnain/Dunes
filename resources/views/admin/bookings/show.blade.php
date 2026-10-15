@extends('layouts.admin')

@section('page_title', 'Booking #' . $booking->reference)

@section('content')
<div class="space-y-6">
    <!-- Top Bar Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all mb-2">
                <i class="bi bi-chevron-left text-xs"></i> Back to List
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Booking #{{ $booking->reference }}</h1>
                @php
                    $statusBadge = match($booking->status) {
                        'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $statusBadge }}">
                    {{ $booking->status }}
                </span>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.bookings.ticket', $booking->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-primary/30 text-primary bg-primary/5 hover:bg-primary/10 text-xs font-bold shadow-2xs transition-all" title="Download Official A4 PDF Voucher">
                <i class="bi bi-file-earmark-pdf"></i> E-Ticket PDF
            </a>
            <a href="{{ route('booking.voucher', $booking->reference) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all" title="Open Digital Boarding Pass">
                <i class="bi bi-box-arrow-up-right"></i> View Voucher
            </a>
            @if($booking->status === 'pending')
                <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="confirmed">
                    <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                    <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-check-lg"></i> Confirm Booking
                    </button>
                </form>
                <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="inline" id="cancelBookingForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                    <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-all" onclick="confirmCancelBooking()">
                        <i class="bi bi-x-lg"></i> Cancel Booking
                    </button>
                </form>
            @endif
            @if($booking->status === 'confirmed')
                <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                    <input type="hidden" name="balance_due" value="{{ $booking->balance_due }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-check2-circle"></i> Mark Completed
                    </button>
                </form>
            @endif
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold shadow-2xs transition-all" onclick="promptPermanentDeleteShow('{{ $booking->id }}', '{{ $booking->reference }}', '{{ addslashes($booking->name) }}')" title="Permanently Delete Booking & All Related Records">
                <i class="bi bi-trash3"></i> Delete Booking
            </button>
        </div>
    </div>

    <!-- Booking Overview Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Details -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-person-circle text-base"></i> Customer Details
                    </h2>
                    <span class="text-xs font-mono text-slate-400">ID: #{{ $booking->id }}</span>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Full Name</div>
                        <div class="text-lg font-black text-slate-900">{{ $booking->name }}</div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Address</div>
                            <a href="mailto:{{ $booking->email }}" class="text-sm font-semibold text-slate-800 hover:text-primary transition flex items-center gap-1.5">
                                <i class="bi bi-envelope text-slate-400"></i> {{ $booking->email }}
                            </a>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Phone Number</div>
                            <div class="flex items-center gap-2">
                                <a href="tel:{{ $booking->phone }}" class="text-sm font-semibold text-slate-800 hover:text-primary transition flex items-center gap-1.5">
                                    <i class="bi bi-telephone text-slate-400"></i> {{ $booking->phone }}
                                </a>
                                @php
                                    $waPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold" title="Open WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pickup Location</div>
                        <div class="text-sm font-medium text-slate-800 bg-slate-50 border border-slate-100 rounded-xl p-3 flex items-start gap-2">
                            <i class="bi bi-geo-alt text-primary mt-0.5 shrink-0"></i>
                            <span>{{ $booking->pickup_location ?: 'Not specified' }}</span>
                        </div>
                    </div>
                    @if($booking->special_requests)
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Special Notes / Requests</div>
                        <div class="text-xs font-medium text-slate-700 bg-amber-50/60 border border-amber-200/60 rounded-xl p-3 leading-relaxed">
                            <i class="bi bi-chat-quote text-amber-500 me-1"></i> {{ $booking->special_requests }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 mt-6 text-xs text-slate-400 flex items-center justify-between">
                <span>Booked: {{ $booking->created_at?->format('M j, Y - H:i') }}</span>
                <span>IP: {{ $booking->ip_address ?? 'Local' }}</span>
            </div>
        </div>
        
        <!-- Reservation Info -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-calendar2-check text-base"></i> Reservation Info
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                        {{ $booking->tier_name }}
                    </span>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tour / Activity</div>
                        <div class="text-lg font-black text-slate-900">{{ $booking->tour_name }}</div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tour Date</div>
                            <div class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                                <i class="bi bi-calendar-event text-primary"></i>
                                {{ $booking->tour_date ? $booking->tour_date->format('l, M j, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Guests</div>
                            <div class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                                <i class="bi bi-people text-primary"></i>
                                {{ $booking->adults }} Adults, {{ $booking->children }} Children
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Financial Breakdown</div>
                        @if($booking->coupon_code && $booking->discount_amount > 0)
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>Original Subtotal</span>
                                <span class="line-through">AED {{ number_format($booking->original_total) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold text-emerald-700 bg-emerald-50/80 px-2.5 py-1.5 rounded-lg border border-emerald-200/60">
                                <span class="flex items-center gap-1 font-mono">
                                    <i class="bi bi-ticket-perforated-fill"></i> {{ $booking->coupon_code }}
                                </span>
                                <span>-AED {{ number_format($booking->discount_amount) }}</span>
                            </div>
                        @endif
                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-sm font-bold text-slate-700">Total Price</span>
                            <span class="text-2xl font-black text-primary">AED {{ number_format($booking->total) }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200/60 text-center">
                            <div class="bg-white p-2 rounded-lg border border-slate-100">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Method</div>
                                <div class="text-xs font-black text-slate-800 capitalize">{{ $booking->payment_method ?: 'Online' }}</div>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-slate-100">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Paid</div>
                                <div class="text-xs font-black text-emerald-600">
                                    AED {{ number_format($booking->payment_status === 'paid' ? ($booking->payment_amount ?: $booking->total) : ($booking->payment_status === 'partial' ? $booking->payment_amount : 0)) }}
                                </div>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-slate-100">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Balance Due</div>
                                <div class="text-xs font-black {{ $booking->balance_due > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                    AED {{ number_format($booking->payment_status === 'paid' ? 0 : ($booking->payment_status === 'partial' ? $booking->balance_due : $booking->total)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($booking->addons && $booking->addons->count() > 0)
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase text-slate-400">Booked Addons</span>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                +AED {{ number_format($booking->addons_total ?? $booking->addons->sum('price'), 2) }}
                            </span>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($booking->addons as $addon)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 px-3 py-2 rounded-xl text-xs">
                                    <span class="font-bold text-slate-800 flex items-center gap-2">
                                        <i class="bi bi-plus-circle text-emerald-500"></i> {{ $addon->addon_name }}
                                    </span>
                                    <span class="font-black text-primary bg-white border border-slate-200 px-2 py-0.5 rounded-lg shadow-2xs">
                                        +AED {{ number_format($addon->price, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 mt-6 text-xs text-slate-400 flex items-center justify-between">
                <span>Payment Status: <strong class="capitalize text-slate-700">{{ $booking->payment_status }}</strong></span>
                <span>Currency: AED</span>
            </div>
        </div>
    </div>

    <!-- Logistics & Safari Captain Dispatch Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="{ assignModalOpen: false }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3 mb-5">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="bi bi-truck-flatbed text-base"></i> Logistics & Safari Captain Dispatch
                </h2>
                <p class="text-xs text-slate-500 mb-0">Driver assignment, vehicle allocation, real-time pickup status, and guest dispatch broadcast.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="assignModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 bg-slate-50 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span>{{ $booking->driver_id ? 'Change Assignment' : 'Assign Captain' }}</span>
                </button>
            </div>
        </div>

        @php
            $driverObj = $booking->driver;
            $vehicleObj = $booking->vehicle ?: $driverObj?->defaultVehicle;
            $driverName = $driverObj?->name ?: ($booking->assigned_driver_name ?: null);
            $driverPhone = $driverObj?->phone ?: null;
            $plateNum = $vehicleObj?->short_plate ?: ($booking->assigned_vehicle_plate ?: null);
            $pickupTime = $booking->pickup_time_slot ?: ($booking->pickup_time ?: '3:00 PM - 3:30 PM');
            $pickupStatus = $booking->pickup_status ?: 'scheduled';
            
            $statusBadgeClass = match($pickupStatus) {
                'en_route' => 'bg-sky-50 text-sky-700 border-sky-200',
                'arrived' => 'bg-amber-50 text-amber-700 border-amber-200',
                'picked_up' => 'bg-orange-50 text-primary border-orange-200',
                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'no_show' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };

            $waVal = preg_replace('/[^0-9]/', '', $booking->phone);
            $dispatchCaptain = $driverName ?: 'Our Dunes Discovery Safari Captain';
            $dispatchPlate = $plateNum ? "in vehicle {$plateNum}" : 'in our licensed 4x4 Land Cruiser';
            $dispatchMsg = "Hi {$booking->name}! Your Dunes Discovery safari captain {$dispatchCaptain} {$dispatchPlate} will pick you up at {$booking->pickup_location} around {$pickupTime} on " . ($booking->tour_date ? $booking->tour_date->format('M j') : 'your safari date') . ". See you in the dunes!";
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Assigned Captain -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Safari Captain</div>
                @if($driverObj || $driverName)
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-9 h-9 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($driverName, 0, 2)) }}
                        </span>
                        <div>
                            <div class="font-extrabold text-sm text-slate-900">{{ $driverName }}</div>
                            @if($driverObj)
                                <div class="text-[11px] text-amber-600 font-bold flex items-center gap-1">
                                    <i class="bi bi-star-fill text-[10px]"></i> {{ number_format($driverObj->rating, 1) }} &bull; DTCM Certified
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($driverPhone)
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <i class="bi bi-telephone text-slate-400"></i>
                        <a href="tel:{{ $driverPhone }}" class="hover:underline">{{ $driverPhone }}</a>
                        @php $cleanDrvPhone = preg_replace('/[^0-9]/', '', $driverPhone); @endphp
                        <a href="https://wa.me/{{ $cleanDrvPhone }}" target="_blank" rel="noopener" class="text-emerald-600 hover:text-emerald-700">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-2.5">
                        <i class="bi bi-exclamation-triangle me-1"></i> No Captain Assigned Yet
                    </div>
                @endif
            </div>

            <!-- 4x4 Fleet Vehicle -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">4x4 Vehicle</div>
                @if($vehicleObj || $plateNum)
                    <div class="font-extrabold text-sm text-slate-900 mb-1 flex items-center gap-1.5">
                        <i class="bi bi-truck text-primary"></i>
                        {{ $vehicleObj?->model_name ?: 'Toyota Land Cruiser' }}
                    </div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-xs font-bold text-slate-800 mb-1">
                        Plate: {{ $plateNum ?: $vehicleObj?->short_plate }}
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Capacity: {{ $vehicleObj?->passenger_capacity ?: 6 }} Passengers
                    </div>
                @else
                    <div class="text-xs text-slate-500 bg-slate-100 rounded-lg p-2.5">
                        Vehicle will auto-allocate with Captain
                    </div>
                @endif
            </div>

            <!-- Schedule & Status -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Schedule & Status</div>
                <div class="text-xs text-slate-600 mb-1.5">
                    Pickup Window: <strong class="text-slate-900">{{ $pickupTime }}</strong>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $statusBadgeClass }}">
                        {{ str_replace('_', ' ', $pickupStatus) }}
                    </span>
                </div>
                @if($booking->driver_assigned_at)
                    <div class="text-[10px] text-slate-400">
                        Assigned: {{ $booking->driver_assigned_at->format('M j, H:i') }}
                    </div>
                @endif
            </div>

            <!-- Quick Dispatch Actions -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col justify-between">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Broadcast Actions</div>
                <div class="space-y-1.5">
                    @if($waVal)
                    <a href="https://wa.me/{{ $waVal }}?text={{ urlencode($dispatchMsg) }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition" title="Broadcast Pickup Notice to Guest">
                        <i class="bi bi-whatsapp"></i> Broadcast to Guest
                    </a>
                    @endif
                    @if($driverObj)
                    <a href="{{ route('driver.run-sheet', $driverObj->access_token) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-2xs transition">
                        <i class="bi bi-phone"></i> Driver Run Sheet
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if($booking->driver_notes)
        <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
            <strong class="text-slate-900 font-bold">Dispatcher Notes:</strong> {{ $booking->driver_notes }}
        </div>
        @endif

        <!-- Inline Assignment Modal -->
        <div x-show="assignModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             style="display: none;">
            <div @click.outside="assignModalOpen = false"
                 class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden text-left">
                <form action="{{ route('admin.operations.assign-driver', $booking->id) }}" method="POST">
                    @csrf
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h5 class="text-sm font-extrabold text-slate-900 mb-0 flex items-center gap-2">
                            <i class="bi bi-truck-flatbed text-primary"></i> Assign Captain & Fleet Vehicle
                        </h5>
                        <button type="button" @click="assignModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase tracking-wider mb-1.5">Safari Captain</label>
                            <select name="driver_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                                <option value="">-- No Captain (Clear) --</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" {{ (int)$booking->driver_id === (int)$d->id ? 'selected' : '' }}>
                                        Captain {{ $d->name }} ({{ $d->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase tracking-wider mb-1.5">4x4 Fleet Vehicle</label>
                            <select name="vehicle_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                                <option value="">-- Captain Default Vehicle --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" {{ (int)$booking->vehicle_id === (int)$v->id ? 'selected' : '' }}>
                                        {{ $v->display_name }} ({{ $v->passenger_capacity }} seats)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase tracking-wider mb-1.5">Pickup Time Window</label>
                            <input type="text" name="pickup_time_slot" value="{{ $pickupTime }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary outline-none transition" placeholder="e.g. 3:00 PM - 3:30 PM">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-600 uppercase tracking-wider mb-1.5">Pickup Status</label>
                                <select name="pickup_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:border-primary outline-none transition">
                                    <option value="scheduled" {{ $pickupStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="en_route" {{ $pickupStatus === 'en_route' ? 'selected' : '' }}>En Route</option>
                                    <option value="arrived" {{ $pickupStatus === 'arrived' ? 'selected' : '' }}>Arrived</option>
                                    <option value="picked_up" {{ $pickupStatus === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                    <option value="completed" {{ $pickupStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="no_show" {{ $pickupStatus === 'no_show' ? 'selected' : '' }}>No Show</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 uppercase tracking-wider mb-1.5">Driver Notes</label>
                                <input type="text" name="driver_notes" value="{{ $booking->driver_notes }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-primary outline-none transition" placeholder="e.g. Call concierge on arrival">
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-extrabold shadow-xs transition cursor-pointer">Save Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Settings Update Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <h2 class="text-xs font-black uppercase tracking-wider text-primary mb-5 flex items-center gap-2">
            <i class="bi bi-pencil-square text-base"></i> Edit Booking Details
        </h2>
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="edit_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Customer Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('name', $booking->name) }}">
                </div>
                <div>
                    <label for="edit_email" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Customer Email</label>
                    <input type="email" name="email" id="edit_email" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('email', $booking->email) }}">
                </div>
                <div>
                    <label for="edit_phone" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Phone / WhatsApp</label>
                    <input type="text" name="phone" id="edit_phone" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('phone', $booking->phone) }}">
                </div>

                @if(isset($tours) && $tours->isNotEmpty())
                <div>
                    <label for="edit_tour_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Tour Experience</label>
                    <select name="tour_id" id="edit_tour_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        @foreach($tours as $t)
                            <option value="{{ $t->id }}" {{ (int) $booking->tour_id === (int) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(isset($tiers) && $tiers->isNotEmpty())
                <div>
                    <label for="edit_tier_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Package Tier</label>
                    <select name="tier_id" id="edit_tier_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        @foreach($tiers as $tierOpt)
                            <option value="{{ $tierOpt->id }}" {{ (int) $booking->tier_id === (int) $tierOpt->id ? 'selected' : '' }}>{{ $tierOpt->display_name ?: $tierOpt->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label for="edit_tour_date" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Tour Date</label>
                    <input type="date" name="tour_date" id="edit_tour_date" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $booking->tour_date ? $booking->tour_date->format('Y-m-d') : '' }}">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label for="edit_adults" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Adults</label>
                        <input type="number" min="1" max="500" name="adults" id="edit_adults" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->adults ?? 1 }}">
                    </div>
                    <div>
                        <label for="edit_children" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Children</label>
                        <input type="number" min="0" max="500" name="children" id="edit_children" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->children ?? 0 }}">
                    </div>
                    <div>
                        <label for="edit_infants" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Infants</label>
                        <input type="number" min="0" max="500" name="infants" id="edit_infants" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->infants ?? 0 }}">
                    </div>
                </div>

                <div>
                    <label for="edit_payment_method" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Payment Method</label>
                    <select name="payment_method" id="edit_payment_method" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="cash" {{ $booking->payment_method === 'cash' ? 'selected' : '' }}>Cash on Pickup</option>
                        <option value="card_pos" {{ $booking->payment_method === 'card_pos' ? 'selected' : '' }}>Card / POS Terminal</option>
                        <option value="bank_transfer" {{ $booking->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="full" {{ $booking->payment_method === 'full' ? 'selected' : '' }}>Online Full Payment (Ziina)</option>
                        <option value="advance" {{ $booking->payment_method === 'advance' ? 'selected' : '' }}>25% Advance Deposit</option>
                        <option value="complimentary" {{ $booking->payment_method === 'complimentary' ? 'selected' : '' }}>Complimentary / B2B</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Booking Status</label>
                    <select name="status" id="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        @if($booking->status === 'draft')
                            <option value="draft" selected>Draft (Abandoned Checkout)</option>
                        @endif
                        <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label for="payment_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Payment Status</label>
                    <select name="payment_status" id="payment_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ $booking->payment_status === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $booking->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ $booking->payment_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label for="edit_total" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Total (AED)</label>
                        <input type="number" name="total" id="edit_total" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->total }}">
                    </div>
                    <div>
                        <label for="edit_payment_amount" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Paid (AED)</label>
                        <input type="number" name="payment_amount" id="edit_payment_amount" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->payment_amount }}">
                    </div>
                    <div>
                        <label for="balance_due" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Balance (AED)</label>
                        <input type="number" name="balance_due" id="balance_due" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-primary outline-hidden" value="{{ $booking->balance_due }}">
                    </div>
                </div>
                <div class="md:col-span-1">
                    <label for="pickup_location" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Pickup Location</label>
                    <input type="text" name="pickup_location" id="pickup_location" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $booking->pickup_location }}">
                </div>
                <div class="md:col-span-2">
                    <label for="special_requests" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Special Requests</label>
                    <textarea name="special_requests" id="special_requests" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden">{{ $booking->special_requests }}</textarea>
                </div>
                <div class="md:col-span-1">
                    <label for="driver_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Safari Captain</label>
                    <select name="driver_id" id="driver_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">-- No Captain Assigned --</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ (int) $booking->driver_id === (int) $d->id ? 'selected' : '' }}>Captain {{ $d->name }} ({{ $d->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="vehicle_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">4x4 Vehicle</label>
                    <select name="vehicle_id" id="vehicle_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">-- Auto Default Vehicle --</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ (int) $booking->vehicle_id === (int) $v->id ? 'selected' : '' }}>{{ $v->display_name }} ({{ $v->passenger_capacity }} seats)</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="pickup_time_slot" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Pickup Time Slot</label>
                    <input type="text" name="pickup_time_slot" id="pickup_time_slot" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $booking->pickup_time_slot ?: $booking->pickup_time }}" placeholder="e.g. 3:00 PM - 3:30 PM">
                </div>
                <div class="md:col-span-1">
                    <label for="pickup_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Pickup Dispatch Status</label>
                    <select name="pickup_status" id="pickup_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="scheduled" {{ $booking->pickup_status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="en_route" {{ $booking->pickup_status === 'en_route' ? 'selected' : '' }}>En Route</option>
                        <option value="arrived" {{ $booking->pickup_status === 'arrived' ? 'selected' : '' }}>Arrived</option>
                        <option value="picked_up" {{ $booking->pickup_status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="completed" {{ $booking->pickup_status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="no_show" {{ $booking->pickup_status === 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="driver_notes" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Driver / Dispatcher Notes</label>
                    <input type="text" name="driver_notes" id="driver_notes" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $booking->driver_notes }}" placeholder="e.g. Guest requested front passenger seat, baby booster provided">
                </div>
                <div class="md:col-span-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all cursor-pointer">
                        <i class="bi bi-save"></i> Update Booking Details
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Link Management Panel -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
        <div>
            <h2 class="text-xs font-black uppercase tracking-wider text-primary mb-1 flex items-center gap-2">
                <i class="bi bi-credit-card-2-front text-base"></i> Payment Links Management
            </h2>
            <p class="text-xs text-slate-500">Create and resend secure Ziina payment links directly to the customer.</p>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700" id="paymentLinksTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Notes</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Link</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @if($booking->ziina_payment_intent_id && $booking->payments->where('payment_intent_id', $booking->ziina_payment_intent_id)->count() === 0)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $booking->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 font-bold text-slate-900">AED {{ number_format($booking->payment_amount) }}</td>
                        <td class="px-4 py-3"><span class="text-slate-400 text-xs italic">Initial Booking Link</span></td>
                        <td class="px-4 py-3">
                            @php
                                $disp = ($booking->ziina_status === 'requires_payment_instrument' || $booking->ziina_status === 'pending') ? 'Pending' : ucfirst($booking->ziina_status);
                                $badgeClass = ($disp === 'Pending') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">{{ $disp }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ $booking->ziina_redirect_url }}" target="_blank" class="text-xs text-primary hover:underline max-w-[160px] truncate inline-block font-mono" title="{{ $booking->ziina_redirect_url }}">{{ $booking->ziina_redirect_url }}</a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($booking->ziina_redirect_url && $booking->status !== 'completed' && $booking->ziina_status !== 'paid' && $booking->ziina_status !== 'completed')
                            <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200">
                                @php
                                    $waPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                                    $waText = "Hello {$booking->name}, please use this link to complete your payment for booking #{$booking->reference}: {$booking->ziina_redirect_url}";
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition" title="Resend via WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <button type="button" class="px-2.5 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold transition resend-email-btn" data-link="{{ $booking->ziina_redirect_url }}" data-amount="{{ $booking->payment_amount }}" title="Resend via Email">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                    
                    @forelse($booking->payments as $p)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 font-bold text-slate-900">AED {{ number_format($p->amount) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-700">{{ $p->notes ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $dispP = ($p->status === 'requires_payment_instrument' || $p->status === 'pending') ? 'Pending' : ucfirst($p->status);
                                $badgeClassP = ($dispP === 'Pending') ? 'bg-amber-50 text-amber-700 border-amber-200' : ($p->status === 'paid' || $p->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200');
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $badgeClassP }}">{{ $dispP }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ $p->payment_url }}" target="_blank" class="text-xs text-primary hover:underline max-w-[160px] truncate inline-block font-mono" title="{{ $p->payment_url }}">{{ $p->payment_url }}</a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($p->payment_url && $booking->status !== 'completed' && $p->status !== 'paid' && $p->status !== 'completed')
                            <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200">
                                @php
                                    $waPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                                    $waText = "Hello {$booking->name}, please use this link to complete your payment for booking #{$booking->reference}: {$p->payment_url}";
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition" title="Resend via WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <button type="button" class="px-2.5 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold transition resend-email-btn" data-link="{{ $p->payment_url }}" data-amount="{{ $p->amount }}" title="Resend via Email">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                        @if(!$booking->ziina_payment_intent_id)
                        <tr>
                            <td colspan="6" class="text-center py-6 text-xs text-slate-400">No custom payment links created.</td>
                        </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($booking->status !== 'completed')
        <!-- Form to generate payment link -->
        <div class="bg-slate-50/80 rounded-xl border border-slate-200/80 p-5">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-4">Generate New Payment Link</h3>
            <form id="createPaymentLinkForm" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="sm:col-span-4">
                    <label for="payment_amount" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Amount (AED)</label>
                    <input type="number" name="amount" id="payment_amount" step="0.01" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ $booking->balance_due > 0 ? $booking->balance_due : '' }}" required placeholder="0.00">
                </div>
                <div class="sm:col-span-4">
                    <label for="payment_notes" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Notes (Optional)</label>
                    <input type="text" name="notes" id="payment_notes" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="e.g. Balance Payment">
                </div>
                <div class="sm:col-span-4 flex items-center gap-2">
                    <button type="submit" name="send_method" value="none" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-link-45deg"></i> Create
                    </button>
                    <button type="submit" name="send_method" value="whatsapp" class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all" title="Create & Send WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </button>
                    <button type="submit" name="send_method" value="email" class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all" title="Create & Send Email">
                        <i class="bi bi-envelope"></i>
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Create custom payment link
    $('#createPaymentLinkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Determine button clicked
        const submitter = e.originalEvent.submitter;
        const sendMethod = submitter ? submitter.value : 'none';
        
        showLoader();
        
        const data = {
            _token: "{{ csrf_token() }}",
            amount: $('#payment_amount').val(),
            notes: $('#payment_notes').val(),
            send_method: sendMethod
        };
        
        $.ajax({
            url: "{{ route('admin.bookings.payment-link', $booking->id) }}",
            type: "POST",
            data: data,
            success: function(res) {
                hideLoader();
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Link Created',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if(res.redirect_url) {
                            window.open(res.redirect_url, '_blank');
                        }
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                hideLoader();
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to create payment link.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Resend payment link email
    $('.resend-email-btn').on('click', function(e) {
        e.preventDefault();
        const link = $(this).data('link');
        const amount = $(this).data('amount');
        
        showLoader();
        
        $.ajax({
            url: "{{ route('admin.bookings.resend-payment', $booking->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                link: link,
                amount: amount
            },
            success: function(res) {
                hideLoader();
                if(res.success) {
                    Swal.fire('Success', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function(xhr) {
                hideLoader();
                Swal.fire('Error', 'Failed to resend payment email.', 'error');
            }
        });
    });
});

function confirmCancelBooking() {
    Swal.fire({
        title: 'Cancel Booking #{{ $booking->reference }}?',
        text: 'Are you sure you want to cancel this booking? This will notify the customer and release reserved inventory.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel booking'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancelBookingForm').submit();
        }
    });
}

function promptPermanentDeleteShow(id, reference, name) {
    // Step 1: Caution Dialog
    Swal.fire({
        title: 'CAUTION: Permanent Booking Deletion',
        html: `
            <div class="text-left text-xs text-slate-600">
                <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate Booking <span class="font-mono font-bold">#${reference}</span> from the database.
                </div>
                <p class="mb-2 text-slate-800"><strong>Customer:</strong> ${name || 'N/A'}</p>
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                    <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                    <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                        <li>Primary Booking Record & Guest Information</li>
                        <li>All Booked Add-ons, Buggies & Extra Selections</li>
                        <li>Complete Payment History & Gateway Transaction Records</li>
                        <li>Visitor Analytics, Telemetry & Request Logs</li>
                        <li>Guest Reviews submitted for this booking</li>
                        <li>Coupon usage quota will be released back to pool</li>
                    </ul>
                </div>
                <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
        cancelButtonText: 'Cancel (Keep Booking)',
        focusCancel: true
    }).then((step1Result) => {
        if (!step1Result.isConfirmed) return;

        // Step 2: Final Safeguard Double Confirmation
        Swal.fire({
            title: 'Double Confirmation Required',
            html: `
                <div class="text-left text-xs">
                    <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the booking reference below to authorize permanent destruction:</p>
                    <div class="text-center my-3">
                        <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-sm">
                            ${reference}
                        </span>
                    </div>
                </div>
            `,
            input: 'text',
            inputPlaceholder: `Type ${reference} to confirm`,
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                autocomplete: 'off',
                style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
            },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#991b1b',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
            cancelButtonText: 'Abort',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: (inputValue) => {
                if (!inputValue || inputValue.trim() !== reference) {
                    Swal.showValidationMessage(`Reference mismatch! You must type exactly "${reference}" to authorize deletion.`);
                    return false;
                }
                return $.ajax({
                    url: `/admin/bookings/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    headers: { 'Accept': 'application/json' }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete booking.');
                    }
                    return response;
                }).catch(error => {
                    const msg = error.responseJSON ? error.responseJSON.message : error.message;
                    Swal.showValidationMessage(`Purge Failed: ${msg}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((step2Result) => {
            if (step2Result.isConfirmed && step2Result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Purged!',
                    text: step2Result.value.message || `Booking #${reference} was permanently deleted.`,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('admin.bookings.index') }}";
                });
            }
        });
    });
}
</script>
@endpush
@endsection
