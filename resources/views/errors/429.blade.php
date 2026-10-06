@extends('layouts.app')

@php
    $pageTitle = 'Too Many Requests (429) | Dunes Discovery Tourism';
    $pageDesc = 'Too many requests were received from your connection. Please wait a moment and try again.';
    $pageRobots = 'noindex, nofollow';
@endphp

@section('content')
<div class="page-standalone-clearance min-h-[85vh] flex items-center justify-center px-4" style="background: linear-gradient(135deg, #FFF9F5 0%, #FFF0E6 100%);">
    <div class="max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-10 text-center">
        <div class="mb-4 text-amber-500">
            <i class="bi bi-hourglass-split text-6xl inline-block" style="filter: drop-shadow(0 10px 15px rgba(246, 144, 68, 0.25));"></i>
        </div>
        <h1 class="text-5xl sm:text-6xl font-black text-slate-900 tracking-tight mb-2">429</h1>
        <h2 class="text-lg sm:text-xl font-bold text-slate-600 mb-3">Rate Limit Reached</h2>
        <p class="text-slate-500 text-sm leading-relaxed mb-6">
            {{ $message ?? 'We noticed a high volume of requests from your connection. For security and service protection, please wait 60 seconds before trying again.' }}
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-desert-animated px-5 py-3 rounded-full font-bold text-white text-sm shadow-md inline-flex items-center justify-center gap-2">
                <i class="bi bi-house-door-fill"></i> {{ __('ui.nav.home') }}
            </a>
            <a href="{{ route('login') }}" class="border-2 border-primary text-primary hover:bg-primary hover:text-white px-5 py-3 rounded-full font-bold text-sm transition-colors inline-flex items-center justify-center gap-2">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </a>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-200 text-slate-500 text-xs">
            Need urgent help? <a href="https://wa.me/971502456056?text=Hi%20Dunes%20Team%2C%20I%20am%20seeing%20a%20rate%20limit%20issue" target="_blank" rel="noopener" class="btn-whatsapp text-primary font-bold hover:underline inline-flex items-center gap-1 cursor-pointer"><i class="bi bi-whatsapp"></i> {{ __('ui.common.whatsapp_chat') }}</a>
        </div>
    </div>
</div>
@endsection
