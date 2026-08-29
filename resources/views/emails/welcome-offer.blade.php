@extends('emails.layout')

@section('title', 'Your 25% OFF Desert Safari Voucher')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <span style="display: inline-block; background-color: #fff4eb; color: #F58F43; font-weight: 800; font-size: 12px; text-transform: uppercase; padding: 6px 14px; border-radius: 50px; letter-spacing: 1px; margin-bottom: 10px;">
            Exclusive Welcome Gift
        </span>
        <h1 style="color: #1a1a1a; font-size: 24px; font-weight: 800; margin: 0 0 10px 0;">Unlock 25% OFF Your Dubai Desert Adventure</h1>
        <p style="color: #666; font-size: 15px; line-height: 1.5; margin: 0;">Dear {{ $name }}, here is your exclusive one-time discount voucher for your upcoming Dubai safari experience!</p>
    </div>

    <!-- Voucher Ticket Box -->
    <div style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); border-radius: 16px; padding: 25px; text-align: center; color: #ffffff; margin-bottom: 25px; border: 2px dashed #F58F43;">
        <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 2px; color: #F58F43; font-weight: 700; margin-bottom: 8px;">
            Single-Use Promo Voucher
        </div>
        <div style="font-family: monospace; font-size: 28px; font-weight: 800; letter-spacing: 3px; background: rgba(255,255,255,0.1); padding: 12px 20px; border-radius: 8px; display: inline-block; color: #ffffff; margin-bottom: 12px;">
            {{ $code }}
        </div>
        <div style="color: #d1d5db; font-size: 14px;">
            Enjoy <strong>{{ (float)$discount }}% OFF</strong> your entire reservation today.
        </div>
    </div>

    <!-- Guarantees & Features -->
    <div style="background-color: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #1a1a1a; font-size: 15px; font-weight: 700; margin: 0 0 12px 0;">Why Book With Dunes Discovery Tourism?</h3>
        <ul style="margin: 0; padding-left: 20px; color: #4b5563; font-size: 14px; line-height: 1.8;">
            <li><strong>100% Free Cancellation:</strong> Full refund up to 24 hours prior to pickup.</li>
            <li><strong>Official DTCM License:</strong> Government certified Dubai desert safari operator.</li>
            <li><strong>5-Star Gourmet Halal Dining:</strong> Live BBQ buffet (Veg, Non-Veg, Jain options).</li>
            <li><strong>Zero Hidden Fees:</strong> Pay securely online via Card/Apple Pay or Cash on Pickup.</li>
        </ul>
    </div>

    <!-- Call to Action Button -->
    <div style="text-align: center; margin-bottom: 25px;">
        <a href="{{ $bookingUrl }}" style="display: inline-block; background-color: #F58F43; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 16px; padding: 16px 36px; border-radius: 50px; box-shadow: 0 4px 15px rgba(245,143,67,0.4); text-transform: uppercase; letter-spacing: 0.5px;">
            Book My Safari with 25% OFF &rarr;
        </a>
    </div>

    <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0; line-height: 1.4;">
        * Note: This voucher is non-transferable, valid for new bookings placed today, and single-use per customer.
    </p>
@endsection
