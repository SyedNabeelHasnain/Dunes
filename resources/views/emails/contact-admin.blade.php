@extends('emails.layout')

@section('title', 'New Contact Message')

@section('content')
    <h2>New Contact Form Submission</h2>
    
    <p>You have received a new message from the contact form.</p>
    
    <ul>
        <li><strong>Name:</strong> {{ $name }}</li>
        <li><strong>Email:</strong> {{ $email }}</li>
        <li><strong>Phone:</strong> {{ $phone }}</li>
        <li><strong>Subject:</strong> {{ $subject }}</li>
    </ul>
    
    <p><strong>Message:</strong></p>
    <blockquote>
        {{ $messageText }}
    </blockquote>
@endsection
