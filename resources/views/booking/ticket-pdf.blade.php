<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Voucher - {{ $booking->reference }}</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #F58F43;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 10px;
            color: #F58F43;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .ref-badge {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 14px;
            text-align: right;
        }
        .ref-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .ref-code {
            font-size: 16px;
            font-weight: bold;
            color: #F58F43;
            font-family: 'Courier New', Courier, monospace;
        }
        .status-pill {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 12px;
            margin-top: 4px;
        }
        .status-confirmed { background-color: #dcfce7; color: #15803d; }
        .status-pending { background-color: #fef3c7; color: #b45309; }
        .status-completed { background-color: #e0f2fe; color: #0369a1; }
        .status-cancelled { background-color: #fee2e2; color: #b91c1c; }

        .section-heading {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            margin-top: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .label-cell {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            width: 25%;
        }
        .val-cell {
            color: #0f172a;
            font-size: 11px;
            font-weight: 600;
        }

        .price-box {
            background-color: #fafaf9;
            border: 1px solid #e7e5e4;
            border-radius: 6px;
            padding: 10px;
            margin-top: 6px;
        }
        .price-table {
            width: 100%;
            border-collapse: collapse;
        }
        .price-table td {
            padding: 4px 6px;
        }
        .price-total {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px !important;
        }
        .due-highlight {
            font-size: 13px;
            font-weight: bold;
            color: #dc2626;
        }

        .guidelines-box {
            background-color: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 8px 12px;
            border-radius: 0 4px 4px 0;
            margin-top: 14px;
            font-size: 10px;
            color: #92400e;
        }
        .guidelines-box ul {
            margin: 4px 0 0 16px;
            padding: 0;
        }
        .guidelines-box li {
            margin-bottom: 3px;
        }

        .footer-table {
            width: 100%;
            border-top: 1px solid #e2e8f0;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Header / Brand -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <div class="brand-title">DUNES DISCOVERY TOURISM</div>
                <div class="brand-sub">Official Safari & Activity Reservation Voucher</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">Licensed Tourism Operator | Government of Dubai, DTCM Certified</div>
            </td>
            <td style="text-align: right; vertical-align: middle; width: 40%;">
                <div class="ref-badge">
                    <div class="ref-title">Booking Reference</div>
                    <div class="ref-code">#{{ $booking->reference }}</div>
                    <div>
                        <span class="status-pill status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                        <span class="status-pill" style="background-color: #f1f5f9; color: #475569;">Payment: {{ ucfirst($booking->payment_status) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Content 2-Column Split -->
    <table style="width: 100%;" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Left Column: Guest & Tour Info -->
            <td style="width: 60%; vertical-align: top; padding-right: 15px;">
                
                <div class="section-heading">Lead Passenger & Experience</div>
                <table class="data-table">
                    <tr>
                        <td class="label-cell">Guest Name:</td>
                        <td class="val-cell">{{ $booking->name }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Contact Phone:</td>
                        <td class="val-cell">{{ $booking->phone }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Email Address:</td>
                        <td class="val-cell">{{ $booking->email }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Tour / Activity:</td>
                        <td class="val-cell" style="color: #F58F43; font-weight: bold; font-size: 12px;">{{ $booking->tour_name }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Selected Tier:</td>
                        <td class="val-cell">
                            {{ $booking->tier_name ?: ($booking->tier ? $booking->tier->display_name : 'Standard Experience') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">Party Size:</td>
                        <td class="val-cell">
                            {{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}
                            @if($booking->children > 0), {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}@endif
                            @if($booking->infants > 0), {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}@endif
                        </td>
                    </tr>
                    @if($booking->addons && $booking->addons->count() > 0)
                    <tr>
                        <td class="label-cell">Included Add-ons:</td>
                        <td class="val-cell">
                            {{ $booking->addons->pluck('addon_name')->implode(', ') }}
                        </td>
                    </tr>
                    @endif
                </table>

                <div class="section-heading">Schedule & Pickup Instructions</div>
                <table class="data-table">
                    <tr>
                        <td class="label-cell">Tour Date:</td>
                        <td class="val-cell">{{ $booking->tour_date ? $booking->tour_date->format('l, F j, Y') : 'Date on Request' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Pickup Time:</td>
                        <td class="val-cell">{{ $booking->pickup_time ?: 'Between 2:30 PM - 3:15 PM' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Pickup Location:</td>
                        <td class="val-cell">{{ $booking->pickup_location ?: 'Hotel Lobby / Residence in Dubai / Sharjah' }}</td>
                    </tr>
                    @if($booking->notes)
                    <tr>
                        <td class="label-cell">Special Requests:</td>
                        <td class="val-cell" style="font-style: italic; color: #475569;">{{ $booking->notes }}</td>
                    </tr>
                    @endif
                </table>

            </td>

            <!-- Right Column: Verification QR & Financial Summary -->
            <td style="width: 40%; vertical-align: top;">
                
                <!-- QR Code Verification Card -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; margin-bottom: 12px;">
                    <div style="font-size: 10px; font-weight: bold; color: #475569; text-transform: uppercase; margin-bottom: 6px;">E-Ticket Digital Verification</div>
                    @if(!empty($qrCodeUrl))
                        <img src="{{ $qrCodeUrl }}" width="110" height="110" style="display: block; margin: 0 auto;" alt="Verification QR">
                    @else
                        <div style="width: 100px; height: 100px; border: 2px dashed #cbd5e1; margin: 0 auto; line-height: 100px; color: #94a3b8; font-size: 10px;">QR CODE</div>
                    @endif
                    <div style="font-size: 9px; color: #64748b; margin-top: 6px;">Scan with smartphone camera to verify live booking status with driver/host</div>
                </div>

                <!-- Payment Breakdown -->
                <div class="price-box">
                    <div style="font-size: 11px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-bottom: 6px;">Payment Breakdown</div>
                    <table class="price-table">
                        @if($booking->coupon_code && (float)$booking->discount_amount > 0)
                        <tr>
                            <td style="color: #64748b;">Package Total:</td>
                            <td style="text-align: right; text-decoration: line-through; color: #94a3b8;">AED {{ number_format($booking->original_total ?: ($booking->total + $booking->discount_amount), 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #16a34a;">Promo ({{ $booking->coupon_code }}):</td>
                            <td style="text-align: right; color: #16a34a; font-weight: bold;">- AED {{ number_format($booking->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Net Total:</td>
                            <td style="text-align: right; font-weight: bold;">AED {{ number_format($booking->total, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td style="color: #64748b;">Total Price:</td>
                            <td style="text-align: right; font-weight: bold;">AED {{ number_format($booking->total, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: #64748b;">Amount Paid:</td>
                            <td style="text-align: right; font-weight: bold; color: #16a34a;">AED {{ number_format($booking->payment_amount ?: ($booking->payment_status === 'paid' ? $booking->total : 0), 2) }}</td>
                        </tr>
                        <tr class="price-total">
                            <td>Remaining Due:</td>
                            <td style="text-align: right;" class="due-highlight">
                                @if($booking->payment_status === 'paid')
                                    <span style="color: #16a34a;">AED 0.00 (PAID IN FULL)</span>
                                @else
                                    AED {{ number_format($booking->balance_due ?: ($booking->total - ($booking->payment_amount ?: 0)), 2) }}
                                @endif
                            </td>
                        </tr>
                    </table>
                    <div style="font-size: 9px; color: #64748b; margin-top: 6px; text-align: center;">
                        Method: <strong>{{ ucfirst($booking->payment_method ?: 'Cash on Pickup / Online') }}</strong>
                    </div>
                </div>

            </td>
        </tr>
    </table>

    <!-- Important Guidelines & Policies -->
    <div class="guidelines-box">
        <strong>IMPORTANT GUEST INFORMATION & TOUR GUIDELINES:</strong>
        <ul>
            <li><strong>Driver Contact:</strong> Your licensed safari captain will contact you via WhatsApp/Call 30–45 minutes prior to pickup to confirm your exact arrival time.</li>
            <li><strong>Identification:</strong> Please carry a digital or printed copy of this voucher along with a valid photo ID (Passport / Emirates ID).</li>
            <li><strong>Recommended Attire:</strong> Comfortable casual clothing and closed-toe footwear. Light jackets or shawls are recommended for desert evenings during winter months (Nov–Mar).</li>
            <li><strong>Safety Advisory:</strong> Dune bashing is not recommended for expectant mothers, guests with severe back/neck conditions, or infants under 3 years old (gentle scenic transfer available upon advance request).</li>
            <li><strong>Cancellation / Changes:</strong> Free cancellation up to 24 hours prior to tour departure time. Contact our concierge hotline below for immediate itinerary adjustments.</li>
        </ul>
    </div>

    <!-- Footer Support Contacts -->
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <strong>DUNES DISCOVERY TOURISM LLC</strong><br>
                Dubai, United Arab Emirates | Reg. Tourism License #892341<br>
                Web: <span style="color: #F58F43;">dunesdiscoverytourism.com</span> | Email: info@dunesdiscoverytourism.com
            </td>
            <td style="text-align: right; vertical-align: top;">
                <strong>24/7 Concierge Hotline & WhatsApp Support</strong><br>
                <span style="font-size: 11px; font-weight: bold; color: #0f172a;">+971 50 123 4567</span><br>
                Emergency Dispatch: Available 24 Hours Daily
            </td>
        </tr>
    </table>

</body>
</html>
