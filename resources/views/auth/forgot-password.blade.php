<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Reset Password</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Enter your registered email address and we'll send you a secure password reset link.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-envelope-fill text-sm"></i>
                </div>
                <input id="email" class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@dunesdiscoverytourism.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center gap-1">
                <i class="bi bi-arrow-left"></i> {{ __('Back to Sign In') }}
            </a>
            <x-primary-button>
                {{ __('Send Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
