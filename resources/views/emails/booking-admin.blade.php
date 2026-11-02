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
@endphp

@section('title', '🚨 Operational Booking Alert: #' . $booking->reference . ' - Dunes Discovery Tourism')

@section('content')

    <!-- Priority Admin Badge -->
    <div style="margin-bottom: 18px;">
        <span style="display: inline-block; background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 5px 12px; border-radius: 20px;">
            🚨 Fleet & Dispatch Operational Alert
        </span>
        <span style="display: inline-block; background-color: #f1f5f9; color: #475569; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; margin-left: 4px;">
            Ref: #{{ $booking->reference }}
        </span>
    </div>

    <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">
        New Booking Confirmed
    </h1>

    <p style="font-size: 13px; color: #475569; margin: 0 0 20px 0; line-height: 1.5;">
        A new safari reservation has been placed and confirmed on the Dunes Discovery Tourism platform. Fleet dispatch and reservations teams have been notified.
    </p>

    <!-- Guest Manifest & Contact Table -->
    <div style="margin-bottom: 22px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px;">
            Lead Guest & Contact Information
        </div>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff;">
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; width: 34%;">
                    Lead Guest Name
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 700;">
                    {{ $booking->name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Customer Phone
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                    <a href="tel:{{ $booking->phone }}" style="color: #2563eb; font-weight: 700; text-decoration: none;">{{ $booking->phone ?? 'N/A' }}</a>
                    @if(!empty($booking->phone))
                        &bull; <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->phone) }}" target="_blank" style="color: #16a34a; font-size: 11px; font-weight: 700; text-decoration: underline;">Open WhatsApp</a>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Customer Email
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">
                    <a href="mailto:{{ $booking->email }}" style="color: #2563eb; text-decoration: none;">{{ $booking->email ?? 'N/A' }}</a>
                </td>
            </tr>
            @if(!empty($booking->traffic_channel))
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Acquisition Source
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #059669; font-size: 12px; font-weight: 700;">
                    🎯 {{ $booking->traffic_channel }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Tour Itinerary Table -->
    <div style="margin-bottom: 22px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px;">
            Tour Itinerary & Dispatch Details
        </div>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff;">
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; width: 34%;">
                    Tour Experience
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 700;">
                    {{ $booking->tour_name ?: ($booking->tour?->name ?? 'Dubai Desert Safari') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Experience Tier
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    {{ $booking->tier_name ?: ($booking->tier?->display_name ?? 'Standard Package') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Tour Date
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 700;">
                    📅 {{ $tourDateDisplay }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Pickup Location
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    📍 {{ $booking->pickup_location ?: 'To Be Confirmed' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Passengers
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    👥 {{ (int)($booking->adults ?? 1) }} Adult(s)
                    @if(!empty($booking->children) && $booking->children > 0) &bull; {{ $booking->children }} Child(ren) @endif
                    @if(!empty($booking->infants) && $booking->infants > 0) &bull; {{ $booking->infants }} Infant(s) @endif
                </td>
            </tr>
            @if($booking->addons && $booking->addons->count() > 0)
            <tr>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Add-ons
                </td>
                <td style="padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">
                    ✨ {{ $booking->addons->pluck('addon_name')->implode(', ') }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Financial Breakdown Table -->
    <div style="margin-bottom: 24px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px;">
            Financials & Status
        </div>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #f8fafc;">
            <tr>
                <td style="padding: 8px 12px; color: #475569; font-size: 12px;">Total Booking:</td>
                <td style="padding: 8px 12px; text-align: right; color: #0f172a; font-size: 12px; font-weight: 700;">AED {{ number_format($totalAmount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 12px; color: #16a34a; font-size: 12px; font-weight: 700;">Amount Paid:</td>
                <td style="padding: 8px 12px; text-align: right; color: #16a34a; font-size: 12px; font-weight: 800;">AED {{ number_format($paidAmount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 9px 12px; color: #0f172a; font-size: 13px; font-weight: 800; border-top: 1px solid #cbd5e1; background-color: #ffffff;">Balance Due:</td>
                <td style="padding: 9px 12px; text-align: right; font-size: 13px; font-weight: 800; border-top: 1px solid #cbd5e1; background-color: #ffffff;">
                    @if($booking->payment_status === 'paid' || $balanceDue <= 0.001)
                        <span style="color: #16a34a;">AED 0.00 (PAID IN FULL)</span>
                    @else
                        <span style="color: #dc2626;">AED {{ number_format($balanceDue, 2) }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 12px; font-size: 11px; color: #64748b; background-color: #ffffff; border-top: 1px solid #f1f5f9;">Method / Status:</td>
                <td style="padding: 7px 12px; font-size: 11px; text-align: right; background-color: #ffffff; border-top: 1px solid #f1f5f9;">
                    <strong>{{ ucfirst($booking->payment_method ?: 'Card / Cash') }}</strong> &bull; <span style="text-transform: uppercase;">{{ $booking->payment_status }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Admin Actions -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 24px 0 16px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/admin/bookings') }}" target="_blank" style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 13px; letter-spacing: 0.5px;">
                    Open Booking in Admin CMS &rarr;
                </a>
            </td>
        </tr>
    </table>

@endsection
