<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Voucher #{{ $booking->reference }} - Dunes Discovery Tourism</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .voucher-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
            .voucher-header {
                background: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .voucher-header * {
                color: #ffffff !important;
            }
            .badge-ref {
                border: 1px solid #ffffff !important;
                color: #f59e0b !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6 lg:p-8 pb-12">

    <!-- Top Action Bar (Web Only) -->
    <div class="no-print max-w-4xl mx-auto flex justify-between items-center flex-wrap gap-3 mb-6">
        <a href="{{ url('/') }}" class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm font-bold rounded-full px-4 py-2 shadow-xs inline-flex items-center gap-1.5 transition-colors">
            <i class="bi bi-arrow-left text-primary"></i> Return to Site
        </a>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.print()" class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-full px-4 py-2 shadow-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('booking.ticket.pdf', $booking->reference) }}" class="btn-desert-animated text-xs sm:text-sm font-bold rounded-full px-5 py-2 text-white shadow-xs inline-flex items-center gap-1.5">
                <i class="bi bi-file-earmark-arrow-down"></i> Download PDF Ticket
            </a>
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                $waMsg = 'Hello Dunes Discovery! Regarding my booking #' . $booking->reference . ' for ' . $booking->tour_name;
            @endphp
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-full px-4 py-2 shadow-xs inline-flex items-center gap-1.5 transition-colors">
                <i class="bi bi-whatsapp"></i> Concierge
            </a>
        </div>
    </div>

    <!-- Main Boarding Pass / Voucher Card -->
    <div class="voucher-card max-w-4xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        
        <!-- Luxury Header -->
        <div class="voucher-header bg-slate-900 text-white p-6 sm:p-8 relative">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-wider mb-1">DUNES DISCOVERY TOURISM</h1>
                    <p class="text-amber-400 text-xs uppercase font-bold tracking-wider mb-0.5">Official Safari & Experience Voucher</p>
                    <span class="text-white/60 text-[11px]">Licensed Dubai Tourism Operator &bull; DTCM Certified</span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-white/60 block text-[10px] font-bold uppercase tracking-wider mb-1">Booking Reference</span>
                    <span class="badge-ref inline-block bg-white/10 border border-white/20 text-amber-400 font-mono text-base sm:text-lg font-extrabold px-3.5 py-1 rounded-xl tracking-wider">
                        #{{ $booking->reference }}
                    </span>
                    <div class="mt-2 flex sm:justify-end gap-1.5 flex-wrap">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $booking->status === 'confirmed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($booking->status === 'pending' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30') }}">
                            {{ $booking->status }}
                        </span>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/90 text-slate-900">
                            {{ $booking->payment_status === 'paid' ? 'Paid in Full' : 'Payment: ' . ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
            <!-- Accent Bottom Bar -->
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-primary via-amber-400 to-primary"></div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Row 1: Experience & Lead Passenger -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Selected Experience (Col 7) -->
                <div class="md:col-span-7 bg-slate-50 rounded-2xl p-5 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block mb-1">Selected Experience</span>
                        <div class="text-lg font-bold text-primary mb-2.5 leading-snug">{{ $booking->tour_name }}</div>
                        <div class="flex gap-2 flex-wrap items-center mb-3">
                            <span class="bg-amber-500/15 text-primary border border-primary/25 text-xs font-bold px-3 py-1 rounded-full">
                                {{ $booking->tier_name ?: ($booking->tier ? $booking->tier->display_name : 'Standard Experience') }}
                            </span>
                            @if($booking->addons && $booking->addons->count() > 0)
                                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full">
                                    +{{ $booking->addons->count() }} Add-on{{ $booking->addons->count() > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1 text-xs text-slate-600 pt-2 border-t border-slate-200">
                        <div><i class="bi bi-people-fill text-slate-800 me-1.5"></i><strong>Guests:</strong> {{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}@if($booking->children > 0), {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}@endif @if($booking->infants > 0), {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}@endif</div>
                        @if($booking->addons && $booking->addons->count() > 0)
                        <div><i class="bi bi-puzzle-fill text-slate-800 me-1.5"></i><strong>Addons:</strong> {{ $booking->addons->pluck('addon_name')->implode(', ') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Lead Passenger (Col 5) -->
                <div class="md:col-span-5 bg-slate-50 rounded-2xl p-5 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block mb-1">Lead Passenger</span>
                        <div class="text-base font-bold text-slate-900 mb-1.5">{{ $booking->name }}</div>
                        <div class="text-xs text-slate-600 font-mono mb-1"><i class="bi bi-telephone-fill text-emerald-600 me-1.5"></i>{{ $booking->phone }}</div>
                        <div class="text-xs text-slate-600 mb-2"><i class="bi bi-envelope-fill text-primary me-1.5"></i>{{ $booking->email }}</div>
                    </div>
                    <div class="pt-2 border-t border-slate-200 text-xs text-slate-500">
                        <span class="bg-slate-200/70 text-slate-700 px-2.5 py-0.5 rounded-full text-[11px] font-medium">Booked on {{ $booking->created_at ? $booking->created_at->format('M j, Y') : 'Recent' }}</span>
                    </div>
                </div>
            </div>

            <!-- Row 2: Schedule & Financials & QR -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Schedule -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block">Schedule & Pickup</span>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Tour Date</span>
                        <strong class="text-slate-900 text-sm block">{{ $booking->tour_date ? $booking->tour_date->format('l, F j, Y') : 'Open Date' }}</strong>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Pickup Window</span>
                        <strong class="text-slate-900 text-sm block">{{ $booking->pickup_time ?: '2:30 PM - 3:15 PM' }}</strong>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Location</span>
                        <span class="text-slate-700 text-xs font-semibold block leading-tight">{{ $booking->pickup_location ?: 'Hotel / Residence in Dubai' }}</span>
                    </div>
                </div>

                <!-- Financials -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-2 text-xs">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block mb-1">Financial Summary</span>
                    @if($booking->coupon_code && (float)$booking->discount_amount > 0)
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Original Total:</span>
                        <span class="line-through">AED {{ number_format($booking->original_total ?: ($booking->total + $booking->discount_amount), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-emerald-600 font-bold">
                        <span>Promo ({{ $booking->coupon_code }}):</span>
                        <span>-AED {{ number_format($booking->discount_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center font-bold text-slate-800">
                        <span>Net Package:</span>
                        <span>AED {{ number_format($booking->total, 2) }}</span>
                    </div>
                    @else
                    <div class="flex justify-between items-center font-bold text-slate-800">
                        <span>Total Package:</span>
                        <span>AED {{ number_format($booking->total, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center text-emerald-600 font-semibold">
                        <span>Amount Paid:</span>
                        <span>AED {{ number_format($booking->payment_amount ?: ($booking->payment_status === 'paid' ? $booking->total : 0), 2) }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                        <span class="font-bold text-rose-600">Balance Due:</span>
                        <span class="font-black text-sm text-rose-600">
                            @if($booking->payment_status === 'paid')
                                <span class="text-emerald-600">AED 0.00</span>
                            @else
                                AED {{ number_format($booking->balance_due ?: ($booking->total - ($booking->payment_amount ?: 0)), 2) }}
                            @endif
                        </span>
                    </div>
                    <div class="text-slate-500 text-[11px] text-center pt-2 border-t border-slate-200">
                        Method: <strong class="text-slate-700 capitalize">{{ $booking->payment_method ?: 'Cash on Pickup / Online' }}</strong>
                    </div>
                </div>

                <!-- Digital QR -->
                <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl p-5 text-center flex flex-col items-center justify-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block mb-2">Digital E-Ticket QR</span>
                    @if(!empty($qrCodeUrl))
                        <img src="{{ $qrCodeUrl }}" width="115" height="115" class="rounded-xl shadow-xs bg-white p-1.5" alt="Verification QR">
                    @else
                        <div class="w-24 h-24 border border-slate-300 rounded-xl bg-white text-slate-500 text-xs flex items-center justify-center font-mono">QR CODE</div>
                    @endif
                    <span class="text-slate-500 text-[10px] mt-2 leading-tight">Scan with driver to verify reservation instantly</span>
                </div>
            </div>

            @if($booking->notes)
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
                <span class="font-bold uppercase tracking-wider text-slate-500 block mb-1 text-[10px]">Special Guest Requests</span>
                <p class="text-slate-800 mb-0 leading-relaxed">{{ $booking->notes }}</p>
            </div>
            @endif

            <!-- Guidelines -->
            <div class="bg-amber-50/80 border-l-4 border-amber-500 rounded-r-2xl p-4 text-xs text-amber-900 space-y-1.5 leading-relaxed">
                <h6 class="font-bold uppercase tracking-wider text-amber-950 text-[11px] flex items-center gap-1.5 mb-1.5">
                    <i class="bi bi-info-circle-fill text-amber-500"></i> Important Safari Information & Advisory
                </h6>
                <ul class="list-disc pl-4 space-y-1 text-[11px] text-amber-900/90">
                    <li><strong>Pickup Notice:</strong> Your licensed safari captain will call or WhatsApp 30 to 45 minutes prior to pickup to confirm your exact vehicle arrival time.</li>
                    <li><strong>Identification:</strong> Please present this voucher (digital or printed) together with valid photo ID upon boarding.</li>
                    <li><strong>Clothing:</strong> Casual comfortable wear and sports footwear recommended. Light jackets are advisable for desert evenings in winter.</li>
                    <li><strong>Advisory:</strong> Dune bashing is not suitable for pregnant women or guests with back/neck conditions. Gentle scenic desert transfer is provided on request.</li>
                </ul>
            </div>

            <!-- Footer Contact -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pt-4 border-t border-slate-200 text-slate-500 text-[11px]">
                <div>
                    <strong class="text-slate-800">DUNES DISCOVERY TOURISM LLC</strong> &bull; Dubai, United Arab Emirates<br>
                    License #{{ $settings['company_license_number'] ?? $settings['site_det_license'] ?? '1430583' }} &bull; Web: dunesdiscoverytourism.com
                </div>
                <div class="sm:text-right">
                    <strong>24/7 Concierge Hotline:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings['site_phone'] ?? '+971502456056') }}" class="font-bold text-slate-800 hover:text-primary">{{ $settings['site_phone'] ?? '+971 50 245 6056' }}</a><br>
                    Support: {{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}
                </div>
            </div>
        </div>
    </div>

</body>
</html>
