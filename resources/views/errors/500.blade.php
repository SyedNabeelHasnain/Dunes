@extends('layouts.app')

@php
    $pageTitle = 'Server Error (500) | Dunes Discovery Tourism';
    $pageDesc = 'Something unexpected occurred on our server. Please try again or contact our support team.';
    $pageRobots = 'noindex, nofollow';
@endphp

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4" style="background: linear-gradient(135deg, #FFF9F5 0%, #FFF0E6 100%);">
    <div class="max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-10 text-center">
        <div class="mb-4 text-amber-500">
            <i class="bi bi-exclamation-triangle-fill text-6xl inline-block" style="filter: drop-shadow(0 10px 15px rgba(245, 158, 11, 0.25));"></i>
        </div>
        <h1 class="text-5xl sm:text-6xl font-black text-slate-900 tracking-tight mb-2">500</h1>
        <h2 class="text-lg sm:text-xl font-bold text-slate-600 mb-3">Temporary Server Hitch</h2>
        <p class="text-slate-500 text-sm leading-relaxed mb-6">
            Our system encountered a brief hiccup while processing your request. Please refresh the page or try again in a few moments.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-3">
            <a href="javascript:location.reload()" class="btn-desert-animated px-5 py-3 rounded-full font-bold text-white text-sm shadow-md inline-flex items-center justify-center gap-2">
                <i class="bi bi-arrow-clockwise"></i> Reload Page
            </a>
            <a href="{{ route('home') }}" class="border-2 border-primary text-primary hover:bg-primary hover:text-white px-5 py-3 rounded-full font-bold text-sm transition-colors inline-flex items-center justify-center gap-2">
                <i class="bi bi-house-door-fill"></i> Return Home
            </a>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-200 text-slate-500 text-xs">
            If the issue persists, feel free to contact us via <a href="https://wa.me/971502456056?text=Hi%20Dunes%20Team%2C%20I%20encountered%20a%20500%20server%20error" target="_blank" rel="noopener" class="text-primary font-bold hover:underline inline-flex items-center gap-1"><i class="bi bi-whatsapp"></i> WhatsApp Support</a>
        </div>
    </div>
</div>
@endsection
