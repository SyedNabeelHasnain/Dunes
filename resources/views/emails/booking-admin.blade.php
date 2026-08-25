@extends('emails.layout')

@section('title', 'New Booking Received')

@section('content')
    <h2>New Booking Received</h2>
    
    <p>A new booking has been placed on the website. Here are the details:</p>
    
    <ul>
        <li><strong>Reference:</strong> {{ $booking->reference }}</li>
        <li><strong>Customer Name:</strong> {{ $booking->name }}</li>
        <li><strong>Email:</strong> {{ $booking->email }}</li>
        <li><strong>Phone:</strong> {{ $booking->phone }}</li>
        <li><strong>Tour:</strong> {{ $booking->tour->title ?? 'N/A' }}</li>
        <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</li>
        <li><strong>Total Amount:</strong> AED {{ number_format($booking->total_amount, 2) }}</li>
        <li><strong>Payment Method:</strong> {{ ucfirst($booking->payment_method) }}</li>
        <li><strong>Payment Status:</strong> {{ ucfirst($booking->payment_status) }}</li>
    </ul>
    
    <p>Please log in to the admin panel to view full details and manage this booking.</p>
@endsection
