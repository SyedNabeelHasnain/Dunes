<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <h2 class="text-xl font-extrabold text-slate-900">Admin CMS Portal</h2>
        <p class="text-xs text-slate-500 mt-1">Sign in with your administrator username or email</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email or Username -->
        <div>
            <x-input-label for="email" :value="__('Email or Username')" class="text-xs font-bold text-slate-700" />
            <x-text-input id="email" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-[#F69044] focus:ring-[#F69044] text-xs py-2.5" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="admin or email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-500" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-slate-700" />
            <x-text-input id="password" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-[#F69044] focus:ring-[#F69044] text-xs py-2.5"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-500" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#F69044] shadow-xs focus:ring-[#F69044]" name="remember">
                <span class="ms-2 text-slate-600 font-medium">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-slate-500 hover:text-slate-800 transition font-medium" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#F69044] hover:bg-[#d57a32] active:bg-[#b05e20] text-white py-2.5 px-4 text-xs font-bold shadow-xs transition">
                <span>{{ __('Sign In to Admin CMS') }}</span>
            </button>
        </div>
    </form>
</x-guest-layout>
