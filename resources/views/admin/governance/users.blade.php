@extends('layouts.admin')

@section('page_title', 'Staff Roles & Access Governance (RBAC)')

@section('content')
<div class="space-y-6 mb-8">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3.5 text-xs font-bold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h5 class="text-base font-extrabold text-slate-900">CMS Staff Accounts & Role Assignments</h5>
                    <p class="text-xs text-slate-500 mt-0.5">Enforce least-privilege separation of duties across editorial, SEO, engineering, and audit roles.</p>
                </div>
                <a href="{{ route('admin.governance.index') }}" class="text-xs font-bold text-primary hover:underline">Back to Governance Hub</a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($users as $staff)
                    <div class="p-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-extrabold text-slate-900">{{ $staff->name }}</span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ ($staff->is_active ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ($staff->is_active ?? true) ? 'Active' : 'Deactivated' }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-700">{{ $staff->roleLabel() }}</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $staff->email }} · Last login: {{ $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'Never' }}
                                </div>
                            </div>

                            @if ($staff->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.governance.users.toggle', $staff) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                        {{ ($staff->is_active ?? true) ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.governance.users.update-role', $staff) }}" class="bg-slate-50/70 rounded-xl border border-slate-200 p-3.5 space-y-3">
                            @csrf
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="text-xs font-bold text-slate-700">Primary Role</label>
                                <select name="role" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800">
                                    @foreach ($roles as $roleKey => $roleMeta)
                                        <option value="{{ $roleKey }}" @selected($staff->normalizedRole() === $roleKey)>{{ $roleMeta['label'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="ml-auto px-3.5 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:opacity-90">Save Role & Permissions</button>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 pt-2 border-t border-slate-200/80">
                                @foreach ($permissions as $permKey => $permLabel)
                                    <label class="flex items-start gap-2 text-[11px] text-slate-700">
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" @checked(in_array($permKey, $staff->permissions ?? [], true)) class="mt-0.5 rounded border-slate-300 text-primary">
                                        <span><strong>{{ $permKey }}</strong></span>
                                    </label>
                                @endforeach
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-5">
                <h6 class="text-sm font-extrabold text-slate-900 mb-3">Create Staff Account</h6>
                <form method="POST" action="{{ route('admin.governance.users.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="name" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Temporary Password (min 8 chars)</label>
                        <input type="password" name="password" required minlength="8" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Role</label>
                        <select name="role" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            @foreach ($roles as $roleKey => $roleMeta)
                                <option value="{{ $roleKey }}">{{ $roleMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:opacity-90">Create Staff Account</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs p-5 space-y-3">
                <h6 class="text-sm font-extrabold text-slate-900">Role Permission Matrix</h6>
                @foreach ($roles as $roleKey => $roleMeta)
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-extrabold text-slate-800">{{ $roleMeta['label'] }}</div>
                        <p class="text-[11px] text-slate-600 mt-0.5">{{ $roleMeta['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
