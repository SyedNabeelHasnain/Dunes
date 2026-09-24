@extends('layouts.app')

@php
    $pageTitle = 'Page Not Found (404) | Dunes Discovery Tourism';
    $pageDesc = 'The page you are looking for does not exist or has been moved. Explore our Dubai desert safari tours or return home.';
    $pageRobots = 'noindex, follow';
@endphp

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4" style="background: linear-gradient(135deg, #FFF9F5 0%, #FFF0E6 100%);">
    <div class="max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-10 text-center">
        <div class="mb-4 text-primary">
            <i class="bi bi-compass-fill text-6xl inline-block" style="filter: drop-shadow(0 10px 15px rgba(246, 144, 68, 0.25));"></i>
        </div>
        <h1 class="text-5xl sm:text-6xl font-black text-slate-900 tracking-tight mb-2">404</h1>
        <h2 class="text-lg sm:text-xl font-bold text-slate-600 mb-3">Lost in the Dunes?</h2>
        <p class="text-slate-500 text-sm leading-relaxed mb-6">
            The page or tour experience you are searching for might have been relocated, renamed, or is temporarily unavailable.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-desert-animated px-5 py-3 rounded-full font-bold text-white text-sm shadow-md inline-flex items-center justify-center gap-2">
                <i class="bi bi-house-door-fill"></i> Return to Homepage
            </a>
            <a href="{{ route('tours.index') }}" class="border-2 border-primary text-primary hover:bg-primary hover:text-white px-5 py-3 rounded-full font-bold text-sm transition-colors inline-flex items-center justify-center gap-2">
                <i class="bi bi-grid-fill"></i> Explore Desert Tours
            </a>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-200 text-slate-500 text-xs">
            Need immediate assistance? <a href="https://wa.me/971502456056?text=Hi%20Dunes%20Team%2C%20I%20need%20help%20finding%20a%20tour" target="_blank" rel="noopener" class="text-primary font-bold hover:underline inline-flex items-center gap-1"><i class="bi bi-whatsapp"></i> Chat on WhatsApp</a>
        </div>
    </div>
</div>
@endsection
