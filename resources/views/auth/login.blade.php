<x-guest-layout>
    <!-- Header -->
    <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Admin CMS Portal</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Sign in with your administrator username or email</p>
    </div>

    <!-- Session Status Alert -->
    @if (session('status'))
        <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-semibold flex items-center gap-2.5">
            <i class="bi bi-info-circle-fill text-amber-500 text-sm shrink-0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Validation Error Alert -->
    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-semibold flex items-start gap-2.5">
            <i class="bi bi-exclamation-triangle-fill text-rose-500 text-sm shrink-0 mt-0.5"></i>
            <div class="space-y-0.5">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email or Username -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                {{ __('Email or Username') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-person-fill text-sm"></i>
                </div>
                <input id="email" 
                       type="text" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username" 
                       placeholder="admin or email address"
                       class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                    <i class="bi bi-x-circle text-[11px]"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password with Alpine Show/Hide Toggle -->
        <div x-data="{ showPassword: false }">
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                {{ __('Password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-lock-fill text-sm"></i>
                </div>
                <input id="password" 
                       :type="showPassword ? 'text' : 'password'" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       placeholder="••••••••"
                       class="w-full h-11 pl-10 pr-10 rounded-xl border border-slate-300 bg-slate-50/50 text-slate-900 text-sm font-medium placeholder:text-slate-400 focus:bg-white focus:border-[#F27405] focus:ring-4 focus:ring-[#F27405]/15 focus:outline-none outline-none transition-all duration-150">
                <button type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer" 
                        :title="showPassword ? 'Hide password' : 'Show password'">
                    <i :class="showPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'" class="text-sm"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                    <i class="bi bi-x-circle text-[11px]"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" 
                       type="checkbox" 
                       name="remember" 
                       class="w-4 h-4 rounded border-slate-300 text-[#F27405] focus:ring-[#F27405]/20 focus:ring-offset-0 cursor-pointer">
                <span class="text-slate-600 font-semibold">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-slate-500 hover:text-[#F27405] transition font-semibold">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full h-11 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#F27405] to-[#f59e0b] hover:from-[#d96504] hover:to-[#d97706] active:scale-[0.99] text-white text-sm font-bold shadow-md hover:shadow-lg transition-all duration-150 cursor-pointer">
                <i class="bi bi-box-arrow-in-right text-base"></i>
                <span>{{ __('Sign In to Admin CMS') }}</span>
            </button>
        </div>

        <!-- SSL / Security Guarantee -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] font-medium text-slate-400">
            <i class="bi bi-shield-lock-fill text-emerald-500"></i>
            <span>256-Bit Encrypted Administrative Session</span>
        </div>
    </form>
</x-guest-layout>
