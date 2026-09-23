@extends('layouts.app')

@section('content')
<section class="py-16 min-h-[75vh] flex items-center bg-slate-50">
    <div class="max-w-xl mx-auto px-4 w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 mb-2">Payment Cancelled</h2>
            <p class="text-slate-500 text-sm sm:text-base leading-relaxed mb-4">
                Your payment was not completed. You can retry your reservation or contact our concierge team for immediate assistance.
            </p>
            @if($booking)
                <div class="p-4 bg-slate-50 rounded-2xl mb-6 border border-slate-200 text-left">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider">Reference</div>
                    <div class="font-mono font-extrabold text-slate-900 text-base sm:text-lg">#{{ $booking->reference }}</div>
                </div>
            @endif
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('tours.index') }}" class="btn-desert-animated font-bold rounded-full px-6 py-3 text-white text-sm shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="bi bi-compass-fill"></i> Browse Tours
                </a>
                <a href="{{ route('contact') }}" class="border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold rounded-full px-6 py-3 text-sm transition-colors inline-flex items-center justify-center gap-2">
                    <i class="bi bi-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
