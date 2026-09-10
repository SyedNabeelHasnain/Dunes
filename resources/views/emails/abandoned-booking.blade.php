@extends('emails.layout')

@section('title', 'Complete Your Desert Safari Booking - Dunes Discovery Tourism')

@section('content')
    <p>Dear {{ $booking->name }},</p>
    
    <p>We noticed you started reserving your Dubai desert adventure but haven't finalized your booking yet.</p>

    <div style="background-color: #fff8f0; border-left: 4px solid #F58F43; padding: 16px 20px; border-radius: 4px; margin: 20px 0;">
        <h3 style="margin: 0 0 10px 0; color: #333333; font-size: 18px;">Reservation Summary:</h3>
        <p style="margin: 4px 0;"><strong>Booking Reference:</strong> #{{ $booking->reference }}</p>
        <p style="margin: 4px 0;"><strong>Experience:</strong> {{ $booking->tour_name ?? ($booking->tour->name ?? 'Desert Safari Tour') }}</p>
        @if($booking->tier_name)
        <p style="margin: 4px 0;"><strong>Selected Package:</strong> {{ $booking->tier_name }}</p>
        @endif
        <p style="margin: 4px 0;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->tour_date)->format('M d, Y') }}</p>
        <p style="margin: 4px 0;"><strong>Guests:</strong> {{ $booking->adults }} Adult(s) @if($booking->children > 0), {{ $booking->children }} Child(ren) @endif</p>
        <p style="margin: 4px 0; font-size: 18px; color: #F58F43;"><strong>Total Amount:</strong> AED {{ number_format($booking->total, 2) }}</p>
    </div>

    <p>We have placed a temporary courtesy hold on your spots so you don't miss out on your preferred safari date.</p>

    <div style="text-align: center; margin: 30px 0 20px 0;">
        <a href="{{ $recoveryUrl }}" style="background-color: #F58F43; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(245, 143, 67, 0.35);">
            Complete Your Reservation &rarr;
        </a>
    </div>

    <div style="text-align: center; margin-bottom: 25px;">
        <a href="{{ $whatsappUrl }}" style="background-color: #25D366; color: #ffffff; padding: 10px 24px; text-decoration: none; border-radius: 20px; font-weight: bold; font-size: 14px; display: inline-block;">
            Chat with Safari Concierge on WhatsApp
        </a>
    </div>

    <p style="font-size: 14px; color: #777777;">
        Need to adjust your pickup location, change the date, or customize your safari package? Simply reply to this email or reach us anytime on WhatsApp at +971 50 245 6056.
    </p>

    <p>Warm regards,<br>
    <strong>The Dunes Discovery Tourism Team</strong><br>
    Dubai, United Arab Emirates</p>
@endsection