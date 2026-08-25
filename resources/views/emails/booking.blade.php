@extends('emails.layout')

@section('title', 'Booking Update - Dunes Discovery Tourism')

@section('content')
    <p>Dear {{ $booking->name }},</p>
    
    @if($type === 'booking_cash')
        <p>Thank you for your booking. Please find your booking details below:</p>
        <ul>
            <li><strong>Reference:</strong> {{ $booking->reference }}</li>
            <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'Tour' }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</li>
            <li><strong>Total Amount:</strong> AED {{ number_format($booking->total_amount, 2) }}</li>
            <li><strong>Payment Method:</strong> Cash on pickup</li>
        </ul>
        <p>Your payment will be collected in cash at the time of pickup.</p>
        
    @elseif($type === 'booking_advance')
        <p>Thank you for your advance payment. Your booking details are as follows:</p>
        <ul>
            <li><strong>Reference:</strong> {{ $booking->reference }}</li>
            <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'Tour' }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</li>
            <li><strong>Total Amount:</strong> AED {{ number_format($booking->total_amount, 2) }}</li>
            <li><strong>Advance Paid:</strong> AED {{ number_format($booking->payment_amount, 2) }}</li>
            <li><strong>Balance Due:</strong> AED {{ number_format($booking->total_amount - $booking->payment_amount, 2) }}</li>
        </ul>
        <p>The balance amount will be collected at the time of pickup.</p>
        
    @elseif($type === 'booking_full')
        <p>Thank you for your full payment. Your booking is confirmed.</p>
        <ul>
            <li><strong>Reference:</strong> {{ $booking->reference }}</li>
            <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'Tour' }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</li>
            <li><strong>Amount Paid:</strong> AED {{ number_format($booking->payment_amount, 2) }}</li>
            <li><strong>Status:</strong> Confirmed</li>
        </ul>
        
    @elseif($type === 'booking_confirmed')
        <p>Your booking has been successfully confirmed.</p>
        <ul>
            <li><strong>Reference:</strong> {{ $booking->reference }}</li>
            <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'Tour' }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</li>
            <li><strong>Pickup Location:</strong> {{ $booking->pickup_location }}</li>
            <li><strong>Total Amount:</strong> AED {{ number_format($booking->total_amount, 2) }}</li>
            @if($booking->total_amount > $booking->payment_amount)
            <li><strong>Balance Due:</strong> AED {{ number_format($booking->total_amount - $booking->payment_amount, 2) }}</li>
            @endif
        </ul>
        
    @elseif($type === 'booking_cancelled')
        <p>We are writing to inform you that your booking has been cancelled.</p>
        <ul>
            <li><strong>Reference:</strong> {{ $booking->reference }}</li>
            <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'Tour' }}</li>
        </ul>
        <p>If you have any questions or wish to rebook, please contact us.</p>
    @endif

    @if($type !== 'booking_cancelled')
        <p><strong>Dune Discovery will contact to confirm the pickup time.</strong></p>
    @endif
    
    <p>Thank you for choosing Dunes Discovery Tourism!</p>
@endsection
