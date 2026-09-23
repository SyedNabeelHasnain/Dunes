@extends('layouts.admin')

@section('page_title', 'Admin Account & Security Settings')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Admin Profile & Security</h1>
        <p class="text-xs text-slate-500 mt-0.5">Update your administrator account name, registered email, and security password.</p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Profile Info Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="bi bi-person-badge text-primary"></i> Account Credentials
            </h2>
            
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Administrator Name *</label>
                    <input type="text" name="name" id="name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('name') border-rose-300 ring-rose-200 @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Address *</label>
                    <input type="email" name="email" id="email" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('email') border-rose-300 ring-rose-200 @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Update Account Info
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Change Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="bi bi-key-fill text-amber-500"></i> Change Security Password
            </h2>

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Current Password *</label>
                    <input type="password" name="current_password" id="current_password" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('current_password', 'updatePassword') border-rose-300 ring-rose-200 @enderror" required>
                    @error('current_password', 'updatePassword')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">New Password *</label>
                    <input type="password" name="password" id="password" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary @error('password', 'updatePassword') border-rose-300 ring-rose-200 @enderror" required>
                    @error('password', 'updatePassword')
                        <div class="text-xs text-rose-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Update Security Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
