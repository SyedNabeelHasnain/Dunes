<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dunes Discovery Tourism') }} - Portal Authentication</title>

        <!-- Fonts & Icons Suite -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">

        <!-- Scripts & Styles -->
        @php
            $hasVite = file_exists(public_path('build/manifest.json'));
        @endphp
        @if($hasVite)
            @vite(['resources/css/app.css', 'resources/js/admin.js'])
        @else
            <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
        @endif

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-amber-50/25 to-slate-100 text-slate-800 min-h-screen flex flex-col justify-center items-center py-10 px-4 sm:px-6 relative overflow-x-hidden selection:bg-[#F27405] selection:text-white">
        <!-- Ambient Decorative Blur Accents -->
        <div class="absolute -top-32 -left-32 w-80 h-80 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 rounded-full bg-[#F27405]/10 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Brand Logo -->
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="inline-block transition-transform duration-200 hover:scale-105" title="Return to Dunes Discovery Tourism Homepage">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Dunes Discovery Tourism') }}" class="h-16 sm:h-20 w-auto object-contain mx-auto drop-shadow-xs">
                </a>
            </div>

            <!-- Card Shell -->
            <div class="w-full bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(15,23,42,0.08)] border border-slate-200/90 p-6 sm:p-8 relative overflow-hidden backdrop-blur-xs">
                <!-- Top Brand Accent Bar -->
                <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-500 via-[#F27405] to-orange-600"></div>

                {{ $slot }}
            </div>

            <!-- Footer Details -->
            <div class="mt-6 text-center text-xs text-slate-400 space-y-1">
                <div>&copy; {{ date('Y') }} Dunes Discovery Tourism L.L.C. All rights reserved.</div>
                <div class="text-[11px] text-slate-400/80">Authorized Administrative Personnel Only</div>
            </div>
        </div>
    </body>
</html>
