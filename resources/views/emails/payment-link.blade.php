@extends('emails.layout')

@section('title', 'Payment Link - Dunes Discovery Tourism')

@section('content')
    <p>Dear {{ $booking->name }},</p>
    
    <p>A payment link has been generated for your booking (Reference: {{ $booking->reference }}).</p>
    
    <p><strong>Amount to Pay:</strong> AED {{ number_format($amount, 2) }}</p>
    
    @if(!empty($notes))
        <p><strong>Notes:</strong> {{ $notes }}</p>
    @endif
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $paymentUrl }}" class="btn">Pay Now</a>
    </div>
    
    <p>If the button above does not work, you can copy and paste the following link into your browser:</p>
    <p><a href="{{ $paymentUrl }}">{{ $paymentUrl }}</a></p>
    
    <p>Thank you for choosing Dunes Discovery Tourism!</p>
@endsection
