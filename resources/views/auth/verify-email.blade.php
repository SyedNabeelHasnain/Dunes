<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Verify Your Email</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Please verify your email address by clicking on the link sent to your inbox.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
            <span>{{ __('A new verification link has been sent to your email address.') }}</span>
        </div>
    @endif

    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
