@extends('layouts.app')

@section('page_title', 'Manage Email Subscription | ' . ($settings['site_name'] ?? 'Dunes Discovery Tourism'))
@section('meta_description', 'Manage your newsletter preferences and unsubscribe options.')

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 sm:py-24 min-h-[75vh] flex items-center">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden w-full">
        <!-- Card Header -->
        <div class="bg-slate-900 text-white p-6 sm:p-8 text-center">
            <div class="inline-flex items-center justify-center bg-primary/20 text-primary rounded-full w-14 h-14 mb-3 text-2xl">
                <i class="bi bi-envelope-slash-fill"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white mb-1">Email Subscription Preferences</h1>
            <p class="text-white/60 text-xs sm:text-sm mb-0">{{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }}</p>
        </div>

        <!-- Card Body -->
        <div class="p-6 sm:p-8">
            @if(isset($message) && !$subscriber)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-3xl text-amber-500 block mb-2"></i>
                    <p class="font-bold text-amber-900 text-sm mb-1">{{ $message }}</p>
                    <span class="text-amber-700 text-xs">If you need assistance managing your email, please <a href="{{ route('contact') }}" class="underline font-semibold hover:text-amber-950">contact support</a>.</span>
                </div>
            @elseif(!empty($completed))
                <div class="text-center py-2">
                    <div class="inline-flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-full w-14 h-14 mb-3 text-2xl">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-2">You Have Been Unsubscribed</h2>
                    <p class="text-slate-500 text-sm mb-5 leading-relaxed">
                        You will no longer receive marketing promotions, travel tips, or newsletters from {{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }} at <strong class="text-slate-700">{{ $subscriber->email }}</strong>.
                    </p>
                    
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-600 text-xs mb-6 text-left leading-relaxed">
                        <i class="bi bi-info-circle text-primary me-1"></i> <strong>Note:</strong> You will still receive essential transactional notifications regarding any confirmed bookings or customer service inquiries.
                    </div>

                    <a href="{{ route('home') }}" class="btn-desert-animated font-bold rounded-full px-6 py-3 text-white text-sm shadow-md inline-flex items-center gap-2">
                        <i class="bi bi-house-door-fill"></i> Return to Homepage
                    </a>
                </div>
            @elseif(!empty($alreadyUnsubscribed))
                <div class="text-center py-2">
                    <div class="inline-flex items-center justify-center bg-slate-100 text-slate-500 rounded-full w-14 h-14 mb-3 text-2xl">
                        <i class="bi bi-bell-slash"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-2">Already Unsubscribed</h2>
                    <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                        The email address <strong class="text-slate-700">{{ $subscriber->email }}</strong> is already unsubscribed from our active marketing lists.
                    </p>
                    <a href="{{ route('home') }}" class="border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold rounded-full px-6 py-3 text-sm transition-colors inline-flex items-center gap-2">
                        <i class="bi bi-house-door-fill"></i> Return to Homepage
                    </a>
                </div>
            @else
                <p class="text-slate-600 text-sm mb-5 leading-relaxed">
                    We are sorry to see you go! Are you sure you want to unsubscribe <strong class="text-slate-800">{{ $subscriber->email }}</strong> from {{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }} newsletters and exclusive deals?
                </p>

                <form action="{{ url('/unsubscribe/' . $token) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-slate-700 font-bold text-xs uppercase tracking-wider mb-2">Optional: Why are you unsubscribing?</label>
                        <select name="reason" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors">
                            <option value="I receive emails too frequently">I receive emails too frequently</option>
                            <option value="Content is no longer relevant to me">Content is no longer relevant to me</option>
                            <option value="I never signed up for this newsletter">I never signed up for this newsletter</option>
                            <option value="I booked elsewhere / trip completed">I booked elsewhere / trip completed</option>
                            <option value="Other reason">Other reason</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <input type="text" name="other_reason" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors" placeholder="Tell us how we can improve (optional)..." maxlength="200">
                    </div>

                    <div class="space-y-2.5">
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-full py-3.5 text-sm shadow-md transition-colors cursor-pointer inline-flex items-center justify-center gap-2">
                            <i class="bi bi-x-circle"></i> Confirm Unsubscribe
                        </button>
                        <a href="{{ route('home') }}" class="block w-full border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold rounded-full py-3 text-xs sm:text-sm text-center transition-colors">
                            Nevermind, Keep Me Subscribed
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
