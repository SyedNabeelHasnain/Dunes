<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Set New Password</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Please enter your new administrator credentials below.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-envelope-fill text-sm"></i>
                </div>
                <input id="email" class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password with Show/Hide toggle -->
        <div x-data="{ show: false }">
            <x-input-label for="password" :value="__('New Password')" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-lock-fill text-sm"></i>
                </div>
                <input id="password" :type="show ? 'text' : 'password'" class="w-full h-11 pl-10 pr-10 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150" name="password" required autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                    <i :class="show ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div x-data="{ show: false }">
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-lock-fill text-sm"></i>
                </div>
                <input id="password_confirmation" :type="show ? 'text' : 'password'" class="w-full h-11 pl-10 pr-10 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                    <i :class="show ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2 flex items-center justify-end">
            <x-primary-button class="w-full">
                {{ __('Reset Password & Sign In') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
