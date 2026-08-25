@extends('emails.layout')

@section('title', 'New WhatsApp Lead')

@section('content')
    <h2>New WhatsApp Lead</h2>
    
    <p>A user has initiated a WhatsApp conversation from the website.</p>
    
    <ul>
        <li><strong>Name:</strong> {{ $name }}</li>
        <li><strong>Phone:</strong> {{ $phone }}</li>
        <li><strong>Tour:</strong> {{ $tourName }}</li>
        <li><strong>Page URL:</strong> <a href="{{ $pageUrl }}">{{ $pageUrl }}</a></li>
    </ul>
    
    <p><strong>Initial Message:</strong></p>
    <blockquote class="blockquote-green">
        {{ $messageText }}
    </blockquote>
@endsection
