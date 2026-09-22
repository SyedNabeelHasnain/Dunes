@extends('layouts.app')

@section('title', 'Your Feedback - Dunes Discovery Tourism')

@section('content')
<section class="py-16 bg-gradient-to-b from-slate-50 to-white min-h-[75vh] flex items-center">
    <div class="max-w-xl mx-auto px-4 w-full">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10 text-center">
            
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="bi bi-chat-heart"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-900 mb-2">We value your honesty</h2>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Dear <strong class="text-slate-700">{{ $booking->name }}</strong>, our goal is to deliver exceptional 5-star desert safari adventures. Please let us know what we could have done better on your {{ $booking->tour_name }}.
            </p>

            <form action="{{ route('review.feedback', $booking->reference) }}" method="POST" class="text-left">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Your Detailed Feedback</label>
                    <textarea name="feedback" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-slate-800 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors leading-relaxed" placeholder="Tell our operations team what we can improve (driver, food, camp, timing)..." required></textarea>
                </div>

                <button type="submit" class="btn-desert-animated w-full rounded-full py-3.5 font-extrabold text-white shadow-lg text-sm sm:text-base cursor-pointer">
                    Submit Private Feedback
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                Need immediate assistance? Speak directly with our guest relations team on 
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['site_whatsapp'] ?? '971502456056') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 font-bold hover:underline inline-flex items-center gap-1">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>.
            </div>

        </div>
    </div>
</section>
@endsection