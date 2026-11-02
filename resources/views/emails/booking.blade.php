@extends('emails.layout')

@php
    $tourDateDisplay = 'Date on Request';
    if (!empty($booking->tour_date)) {
        if ($booking->tour_date instanceof \DateTimeInterface) {
            $tourDateDisplay = $booking->tour_date->format('l, F j, Y');
        } else {
            try {
                $tourDateDisplay = \Carbon\Carbon::parse($booking->tour_date)->format('l, F j, Y');
            } catch (\Throwable $e) {
                $tourDateDisplay = (string) $booking->tour_date;
            }
        }
    }

    $totalAmount = (float) ($booking->total ?? 0);
    $discountAmount = (float) ($booking->discount_amount ?? 0);
    $originalTotal = (float) ($booking->original_total ?? ($totalAmount + $discountAmount));
    $paidAmount = (float) ($booking->payment_amount ?? ($booking->payment_status === 'paid' ? $totalAmount : 0));
    $balanceDue = (float) ($booking->balance_due ?? max(0, $totalAmount - $paidAmount));
    $isPaidInFull = ($booking->payment_status === 'paid' || $balanceDue <= 0.001);
@endphp

@section('title', 'Booking Reservation #' . $booking->reference . ' - Dunes Discovery Tourism LLC')

@section('content')

    <!-- Status Badge -->
    <div style="margin-bottom: 20px;">
        @if($type === 'booking_cancelled')
            <span style="display: inline-block; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 14px; border-radius: 20px;">
                ✕ Reservation Cancelled
            </span>
        @elseif($isPaidInFull)
            <span style="display: inline-block; background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 14px; border-radius: 20px;">
                ✓ Confirmed &bull; Paid in Full
            </span>
        @elseif($type === 'booking_advance' || $paidAmount > 0)
            <span style="display: inline-block; background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 14px; border-radius: 20px;">
                ✓ Confirmed &bull; Deposit Received
            </span>
        @else
            <span style="display: inline-block; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 14px; border-radius: 20px;">
                ✓ Confirmed &bull; Cash on Pickup
            </span>
        @endif
    </div>

    <!-- Personal Headline & Greeting -->
    <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.3px;">
        @if($type === 'booking_cancelled')
            Booking Cancelled - Ref: #{{ $booking->reference }}
        @else
            Your Desert Adventure is Confirmed!
        @endif
    </h1>

    <p style="font-size: 14px; color: #475569; margin: 0 0 24px 0; line-height: 1.6;">
        Dear <strong>{{ $booking->name ?? 'Valued Guest' }}</strong>,<br>
        @if($type === 'booking_cancelled')
            We are writing to inform you that your safari reservation <strong>#{{ $booking->reference }}</strong> has been officially cancelled. If you believe this was in error or wish to reschedule, our 24/7 concierge desk is here to help.
        @else
            Thank you for choosing <strong>Dunes Discovery Tourism LLC</strong>. Your reservation has been registered with our operations fleet and your safari seats are guaranteed. Please review your itinerary, pickup location, and digital boarding pass voucher below.
        @endif
    </p>

    <!-- Experience & Itinerary Structured Table -->
    <div style="margin-bottom: 24px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px;">
            Experience Summary & Itinerary
        </div>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff;">
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; width: 34%;">
                    Booking Reference
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #F58F43; font-size: 14px; font-weight: 800; font-family: 'Courier New', Courier, monospace;">
                    #{{ $booking->reference }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Tour / Activity
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 700;">
                    {{ $booking->tour_name ?: ($booking->tour?->name ?? 'Dubai Desert Safari Tour') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Selected Package
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    {{ $booking->tier_name ?: ($booking->tier?->display_name ?? 'Standard Safari Experience') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Tour Date
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 700;">
                    📅 {{ $tourDateDisplay }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Estimated Pickup
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    ⏰ {{ $booking->pickup_time ?: 'Between 2:45 PM – 3:30 PM (Confirmed on tour day)' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Pickup Location
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    📍 {{ $booking->pickup_location ?: 'Hotel Lobby / Residence in Dubai or Sharjah' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Guest Manifest
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    👥 {{ (int)($booking->adults ?? 1) }} Adult{{ ($booking->adults ?? 1) > 1 ? 's' : '' }}
                    @if(!empty($booking->children) && $booking->children > 0)
                        &bull; {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}
                    @endif
                    @if(!empty($booking->infants) && $booking->infants > 0)
                        &bull; {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}
                    @endif
                </td>
            </tr>
            @if($booking->addons && $booking->addons->count() > 0)
            <tr>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Included Add-ons
                </td>
                <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    ✨ {{ $booking->addons->pluck('addon_name')->implode(', ') }}
                </td>
            </tr>
            @endif
            @if(!empty($booking->notes))
            <tr>
                <td style="padding: 10px 14px; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Special Notes
                </td>
                <td style="padding: 10px 14px; color: #475569; font-size: 12px; font-style: italic;">
                    {{ $booking->notes }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if($type !== 'booking_cancelled')
    <!-- Financial Breakdown Structured Table -->
    <div style="margin-bottom: 26px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px;">
            Payment & Financial Breakdown
        </div>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #f8fafc;">
            @if(!empty($booking->coupon_code) && $discountAmount > 0)
            <tr>
                <td style="padding: 9px 14px; color: #64748b; font-size: 12px;">Standard Package Rate:</td>
                <td style="padding: 9px 14px; text-align: right; text-decoration: line-through; color: #94a3b8; font-size: 12px;">
                    AED {{ number_format($originalTotal, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 14px; color: #16a34a; font-size: 12px; font-weight: 600;">Promo Discount ({{ $booking->coupon_code }}):</td>
                <td style="padding: 9px 14px; text-align: right; color: #16a34a; font-weight: 700; font-size: 12px;">
                    - AED {{ number_format($discountAmount, 2) }}
                </td>
            </tr>
            @endif
            <tr>
                <td style="padding: 9px 14px; color: #334155; font-size: 13px; font-weight: 600;">Total Booking Amount:</td>
                <td style="padding: 9px 14px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 700;">
                    AED {{ number_format($totalAmount, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 14px; color: #16a34a; font-size: 13px; font-weight: 700; border-top: 1px dashed #cbd5e1;">Amount Received:</td>
                <td style="padding: 9px 14px; text-align: right; color: #16a34a; font-size: 13px; font-weight: 800; border-top: 1px dashed #cbd5e1;">
                    AED {{ number_format($paidAmount, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 11px 14px; color: #0f172a; font-size: 14px; font-weight: 800; border-top: 1px solid #cbd5e1; background-color: #ffffff;">
                    Remaining Balance Due:
                </td>
                <td style="padding: 11px 14px; text-align: right; font-size: 14px; font-weight: 800; border-top: 1px solid #cbd5e1; background-color: #ffffff;">
                    @if($isPaidInFull)
                        <span style="color: #16a34a;">AED 0.00 (PAID IN FULL)</span>
                    @else
                        <span style="color: #dc2626;">AED {{ number_format($balanceDue, 2) }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 7px 14px; font-size: 11px; color: #64748b; text-align: right; background-color: #ffffff; border-top: 1px solid #f1f5f9;">
                    Payment Method: <strong>{{ ucfirst($booking->payment_method ?: 'Credit Card / Cash on Pickup') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Prominent Primary CTA Button -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 30px 0 24px 0;">
        <tr>
            <td align="center">
                <a href="{{ route('booking.voucher', $booking->reference) }}" target="_blank" style="display: inline-block; background-color: #F58F43; color: #ffffff; text-decoration: none; padding: 15px 36px; border-radius: 8px; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.6px; box-shadow: 0 4px 14px rgba(245, 143, 67, 0.4); text-align: center;">
                    View Digital Boarding Pass & Live Voucher &rarr;
                </a>
            </td>
        </tr>
    </table>

    <!-- Official PDF Ticket Voucher Attachment Highlight Box -->
    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #F58F43; border-radius: 8px; padding: 16px 18px; margin-bottom: 24px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="vertical-align: top; width: 34px; font-size: 24px; line-height: 1;">
                    📄
                </td>
                <td style="vertical-align: top; padding-left: 10px;">
                    <div style="font-size: 13px; font-weight: 800; color: #92400e; margin-bottom: 4px;">
                        Official A4 Ticket Voucher Attached (Printable PDF)
                    </div>
                    <div style="font-size: 12px; color: #78350f; line-height: 1.55;">
                        Your official boarding voucher with check-in QR code is attached to this email as a PDF document. You can save it directly to your smartphone (Apple Wallet / Files) or print it out. No cellular data or roaming required upon landing in Dubai!
                    </div>
                    <div style="margin-top: 8px;">
                        <a href="{{ route('booking.ticket.pdf', $booking->reference) }}" target="_blank" style="color: #b45309; font-size: 11px; font-weight: 700; text-decoration: underline;">
                            Download PDF Ticket Voucher Direct Link &rarr;
                        </a>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Safari Captain Contact & Pickup Guidelines -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
        <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #0f172a; margin-bottom: 10px;">
            Important Guest Briefing & Safari Guidelines
        </div>
        <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.65;">
            <li style="margin-bottom: 6px;">
                <strong>Safari Captain WhatsApp Dispatch:</strong> Your assigned licensed Safari Captain will contact you via WhatsApp / Call <strong>30 to 45 minutes prior to pickup</strong> to re-confirm vehicle plate number and arrival time.
            </li>
            <li style="margin-bottom: 6px;">
                <strong>Photo Identification:</strong> Please ensure each adult guest carries a valid Passport or Emirates ID copy.
            </li>
            <li style="margin-bottom: 6px;">
                <strong>Desert Attire:</strong> Comfortable casual clothing and closed-toe footwear are recommended. During winter months (November to March), light jackets or shawls are recommended for cool desert evenings.
            </li>
            <li>
                <strong>24/7 Concierge Hotline:</strong> Need to change pickup hotel or update guest count? Contact our team anytime: <a href="https://wa.me/971502456056?text={{ urlencode('Hello Dunes Discovery, inquiry regarding booking #' . $booking->reference) }}" target="_blank" style="color: #22c55e; font-weight: bold; text-decoration: underline;">Chat on WhatsApp (+971 50 245 6056)</a>.
            </li>
        </ul>
    </div>
    @endif

    <div style="text-align: center; font-size: 13px; color: #64748b; margin-top: 10px;">
        Thank you for choosing <strong>Dunes Discovery Tourism LLC</strong> &mdash; We look forward to hosting you on an unforgettable Arabian adventure!
    </div>

@endsection
