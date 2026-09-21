<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Voucher #{{ $booking->reference }} - Dunes Discovery Tourism</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/5.3.2/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #F58F43;
            --primary-dark: #e07628;
            --dark: #0f172a;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            padding-bottom: 40px;
        }
        .voucher-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            max-width: 860px;
            margin: 0 auto;
        }
        .voucher-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 30px;
            position: relative;
        }
        .voucher-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F58F43 0%, #fb923c 100%);
        }
        .badge-ref {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #F58F43;
            font-family: monospace;
            font-size: 1.15rem;
            padding: 8px 16px;
            border-radius: 12px;
            letter-spacing: 1px;
        }
        .voucher-body {
            padding: 32px;
        }
        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .section-val {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
        }
        .qr-card {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }
        .guidelines-card {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            font-size: 0.85rem;
            color: #92400e;
        }
        .action-bar {
            max-width: 860px;
            margin: 20px auto;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .action-bar, .no-print {
                display: none !important;
            }
            .voucher-card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
            .voucher-header {
                background: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Web Only) -->
    <div class="action-bar no-print d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 px-3">
        <a href="{{ url('/') }}" class="btn btn-white border rounded-pill shadow-sm px-3 fw-bold text-dark">
            <i class="bi bi-arrow-left me-1 text-primary"></i> Return to Site
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-light border rounded-pill shadow-sm px-3 fw-bold text-dark">
                <i class="bi bi-printer me-1 text-secondary"></i> Print
            </button>
            <a href="{{ route('booking.ticket.pdf', $booking->reference) }}" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold text-white">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Download PDF Ticket
            </a>
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $booking->phone);
                $waMsg = 'Hello Dunes Discovery! Regarding my booking #' . $booking->reference . ' for ' . $booking->tour_name;
            @endphp
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['site_whatsapp'] ?? '971502456056') }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-pill shadow-sm px-3 fw-bold">
                <i class="bi bi-whatsapp me-1"></i> Concierge
            </a>
        </div>
    </div>

    <!-- Main Boarding Pass / Voucher Card -->
    <div class="voucher-card mt-3">
        <!-- Luxury Header -->
        <div class="voucher-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-800 text-white mb-1">DUNES DISCOVERY TOURISM</h1>
                    <p class="small text-warning text-uppercase fw-bold mb-0">Official Safari & Experience Voucher</p>
                    <span class="small text-white-50" style="font-size: 0.75rem;">Licensed Dubai Tourism Operator | DTCM Certified</span>
                </div>
                <div class="text-md-end">
                    <span class="d-block small text-white-50 mb-1">BOOKING REFERENCE</span>
                    <span class="badge-ref fw-bold">#{{ $booking->reference }}</span>
                    <div class="mt-2">
                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning text-dark' : 'info') }} rounded-pill px-3 py-1 text-uppercase fw-bold" style="font-size: 0.72rem;">
                            {{ $booking->status }}
                        </span>
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 text-uppercase fw-bold ms-1" style="font-size: 0.72rem;">
                            {{ $booking->payment_status === 'paid' ? 'Paid in Full' : 'Payment: ' . ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="voucher-body">
            <!-- Row 1: Experience & Lead Passenger -->
            <div class="row g-4 mb-4">
                <div class="col-md-7">
                    <div class="p-3 bg-light rounded-4 border h-100">
                        <div class="section-label">Selected Experience</div>
                        <div class="section-val text-primary fs-5 mb-2">{{ $booking->tour_name }}</div>
                        <div class="d-flex gap-2 flex-wrap align-items-center mb-3">
                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 fw-bold">
                                {{ $booking->tier_name ?: ($booking->tier ? $booking->tier->display_name : 'Standard Experience') }}
                            </span>
                            @if($booking->addons && $booking->addons->count() > 0)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold">
                                    +{{ $booking->addons->count() }} Add-on{{ $booking->addons->count() > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="bi bi-people-fill text-dark me-2"></i><strong>Guests:</strong> {{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}
                            @if($booking->children > 0), {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}@endif
                            @if($booking->infants > 0), {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}@endif
                        </div>
                        @if($booking->addons && $booking->addons->count() > 0)
                        <div class="small text-muted">
                            <i class="bi bi-puzzle-fill text-dark me-2"></i><strong>Addons:</strong> {{ $booking->addons->pluck('addon_name')->implode(', ') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="p-3 bg-light rounded-4 border h-100">
                        <div class="section-label">Lead Passenger</div>
                        <div class="section-val mb-1">{{ $booking->name }}</div>
                        <div class="small text-muted font-monospace mb-1"><i class="bi bi-telephone-fill text-success me-2"></i>{{ $booking->phone }}</div>
                        <div class="small text-muted mb-2"><i class="bi bi-envelope-fill text-primary me-2"></i>{{ $booking->email }}</div>
                        <div class="small text-muted border-top pt-2 mt-2">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">Booked on {{ $booking->created_at ? $booking->created_at->format('M j, Y') : 'Recent' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Schedule & Financials & QR -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-4 border h-100 shadow-sm">
                        <div class="section-label">Schedule & Pickup</div>
                        <div class="mb-3">
                            <span class="small text-muted d-block">Tour Date</span>
                            <strong class="text-dark fs-6">{{ $booking->tour_date ? $booking->tour_date->format('l, F j, Y') : 'Open Date' }}</strong>
                        </div>
                        <div class="mb-3">
                            <span class="small text-muted d-block">Pickup Window</span>
                            <strong class="text-dark">{{ $booking->pickup_time ?: '2:30 PM - 3:15 PM' }}</strong>
                        </div>
                        <div>
                            <span class="small text-muted d-block">Location</span>
                            <span class="small text-dark fw-semibold">{{ $booking->pickup_location ?: 'Hotel / Residence in Dubai' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-4 border h-100 shadow-sm">
                        <div class="section-label">Financial Reconciliation</div>
                        @if($booking->coupon_code && (float)$booking->discount_amount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Original Total:</span>
                            <span class="text-muted text-decoration-line-through small">AED {{ number_format($booking->original_total ?: ($booking->total + $booking->discount_amount), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-success fw-bold">Promo ({{ $booking->coupon_code }}):</span>
                            <span class="fw-bold text-success">-AED {{ number_format($booking->discount_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Net Package:</span>
                            <span class="fw-bold text-dark">AED {{ number_format($booking->total, 2) }}</span>
                        </div>
                        @else
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Total Package:</span>
                            <span class="fw-bold text-dark">AED {{ number_format($booking->total, 2) }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Amount Paid:</span>
                            <span class="fw-bold text-success">AED {{ number_format($booking->payment_amount ?: ($booking->payment_status === 'paid' ? $booking->total : 0), 2) }}</span>
                        </div>
                        <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-danger">Balance Due:</span>
                            <span class="fw-800 text-danger fs-6">
                                @if($booking->payment_status === 'paid')
                                    <span class="text-success">AED 0.00</span>
                                @else
                                    AED {{ number_format($booking->balance_due ?: ($booking->total - ($booking->payment_amount ?: 0)), 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="small text-muted text-center mt-3 pt-2 border-top" style="font-size: 0.75rem;">
                            Method: <strong>{{ ucfirst($booking->payment_method ?: 'Cash on Pickup / Online') }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="qr-card h-100 d-flex flex-column align-items-center justify-content-center">
                        <span class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem;">Digital E-Ticket QR</span>
                        @if(!empty($qrCodeUrl))
                            <img src="{{ $qrCodeUrl }}" width="115" height="115" class="rounded-3 shadow-sm bg-white p-1" alt="Verification QR">
                        @else
                            <div style="width: 105px; height: 105px; line-height: 105px;" class="border rounded-3 bg-white text-muted small">QR CODE</div>
                        @endif
                        <span class="small text-muted text-center mt-2" style="font-size: 0.72rem;">Scan with driver to verify reservation instantly</span>
                    </div>
                </div>
            </div>

            @if($booking->notes)
            <div class="alert alert-light border rounded-3 p-3 mb-4">
                <span class="small fw-bold text-muted text-uppercase d-block mb-1">Special Guest Requests</span>
                <span class="small text-dark">{{ $booking->notes }}</span>
            </div>
            @endif

            <!-- Guidelines -->
            <div class="guidelines-card mb-4">
                <h6 class="fw-bold text-uppercase mb-2" style="font-size: 0.8rem;"><i class="bi bi-info-circle-fill me-1"></i> Important Safari Information & Advisory</h6>
                <ul class="mb-0 ps-3">
                    <li class="mb-1"><strong>Pickup Notice:</strong> Your licensed safari captain will call or WhatsApp 30 to 45 minutes prior to pickup to confirm your exact vehicle arrival time.</li>
                    <li class="mb-1"><strong>Identification:</strong> Please present this voucher (digital or printed) together with valid photo ID upon boarding.</li>
                    <li class="mb-1"><strong>Clothing:</strong> Casual comfortable wear and sports footwear recommended. Light jackets are advisable for desert evenings in winter.</li>
                    <li><strong>Advisory:</strong> Dune bashing is not suitable for pregnant women or guests with back/neck conditions. Gentle scenic desert transfer is provided on request.</li>
                </ul>
            </div>

            <!-- Footer Contact -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 border-top pt-3 text-muted small" style="font-size: 0.78rem;">
                <div>
                    <strong>DUNES DISCOVERY TOURISM LLC</strong> | Dubai, United Arab Emirates<br>
                    License #{{ $settings['company_license_number'] ?? $settings['site_det_license'] ?? '1430583' }} | Web: dunesdiscoverytourism.com
                </div>
                <div class="text-md-end">
                    <strong>24/7 Concierge Hotline:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings['site_phone'] ?? '+971502456056') }}" class="text-decoration-none fw-bold text-dark">{{ $settings['site_phone'] ?? '+971 50 245 6056' }}</a><br>
                    Support: {{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}
                </div>
            </div>
        </div>
    </div>

</body>
</html>
