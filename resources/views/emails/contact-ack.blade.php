@extends('emails.layout')

@section('title', 'Thank you for contacting us')

@section('content')
    <p>Dear {{ $name }},</p>
    
    <p>Thank you for reaching out to Dunes Discovery Tourism. We have received your message and our team will get back to you as soon as possible.</p>
    
    <p>If your inquiry is urgent, please feel free to contact us directly via phone or WhatsApp.</p>
    
    <p>Best regards,<br>
    Dunes Discovery Tourism Team</p>
@endsection
