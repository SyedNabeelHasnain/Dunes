@extends('layouts.admin')

@section('page_title', 'Subscribers & Email Marketing Lists')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i> Email Subscribers & Audience Lists
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage newsletter subscribers, segment attributions, and marketing lists.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="$dispatch('open-import-modal')" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition cursor-pointer">
                <i class="bi bi-file-earmark-arrow-up text-slate-500"></i> Import CSV
            </button>
            <a href="{{ route('admin.subscribers.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-emerald-200 hover:bg-emerald-50 text-emerald-700 font-bold text-xs shadow-2xs transition">
                <i class="bi bi-file-earmark-excel"></i> Export CSV
            </a>
            <button type="button" @click="$dispatch('open-create-modal')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                <i class="bi bi-plus-lg"></i> Add Subscriber
            </button>
        </div>
    </div>

    <!-- 4 Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                <i class="bi bi-envelope-at text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Audience</div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                <i class="bi bi-check2-circle text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Subscribed</div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['subscribed']) }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                <i class="bi bi-bell-slash text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unsubscribed</div>
                <div class="text-2xl font-black text-slate-600">{{ number_format($stats['unsubscribed']) }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 border border-rose-200">
                <i class="bi bi-exclamation-triangle text-xl"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bounced</div>
                <div class="text-2xl font-black text-rose-600">{{ number_format($stats['bounced']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <form action="{{ route('admin.subscribers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            <div class="lg:col-span-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-primary transition" placeholder="Search email, name, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="lg:col-span-2">
                <select name="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Statuses</option>
                    <option value="subscribed" {{ request('status') === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                    <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    <option value="bounced" {{ request('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                </select>
            </div>
            <div class="lg:col-span-3">
                <select name="group_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Groups / Segments</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }} ({{ $g->subscribers_count }})</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="source" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Sources</option>
                    <option value="footer" {{ request('source') === 'footer' ? 'selected' : '' }}>Footer Form</option>
                    <option value="booking_modal" {{ request('source') === 'booking_modal' ? 'selected' : '' }}>Booking Checkout</option>
                    <option value="welcome_modal" {{ request('source') === 'welcome_modal' ? 'selected' : '' }}>Welcome Popup</option>
                    <option value="admin_import" {{ request('source') === 'admin_import' ? 'selected' : '' }}>Admin / Import</option>
                </select>
            </div>
            <div class="lg:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer" title="Apply Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if(request()->hasAny(['search', 'status', 'group_id', 'source']))
                    <a href="{{ route('admin.subscribers.index') }}" class="inline-flex items-center justify-center px-2.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar Form & Table -->
    <form action="{{ route('admin.subscribers.bulk') }}" method="POST" id="bulkForm">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="bg-slate-50/80 border-b border-slate-200/80 py-3 px-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" id="selectAll">
                        <span class="text-xs font-bold text-slate-600">Select All</span>
                    </label>
                    <div id="bulkControls" class="hidden flex items-center gap-2">
                        <select name="action" id="bulkActionSelect" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="">Bulk Actions...</option>
                            <option value="assign_group">Assign to Group</option>
                            <option value="subscribe">Mark Subscribed</option>
                            <option value="unsubscribe">Mark Unsubscribed</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <select name="target_group_id" id="bulkGroupSelect" class="hidden rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="">Choose Target Group...</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-xs transition cursor-pointer" id="bulkApplyBtn">Apply</button>
                    </div>
                </div>
                <div class="text-xs text-slate-500">
                    Showing {{ $subscribers->firstItem() ?? 0 }}–{{ $subscribers->lastItem() ?? 0 }} of {{ $subscribers->total() }} subscribers
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm text-slate-700">
                    <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-10"></th>
                            <th class="py-3 px-4">Subscriber Details</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Assigned Groups</th>
                            <th class="py-3 px-4">Attribution</th>
                            <th class="py-3 px-4">Date Subscribed</th>
                            <th class="py-3 px-4 text-right pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($subscribers as $s)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 row-select" type="checkbox" name="subscriber_ids[]" value="{{ $s->id }}">
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                        {{ strtoupper(substr($s->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $s->email }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $s->full_name }} @if($s->phone) &bull; {{ $s->phone }} @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($s->status === 'subscribed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-pointer" onclick="toggleStatus({{ $s->id }})">
                                        <i class="bi bi-check-circle-fill text-[11px]"></i> Subscribed
                                    </span>
                                @elseif($s->status === 'unsubscribed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 cursor-pointer" onclick="toggleStatus({{ $s->id }})">
                                        <i class="bi bi-dash-circle-fill text-[11px]"></i> Unsubscribed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="bi bi-x-circle-fill text-[11px]"></i> {{ ucfirst($s->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($s->groups as $grp)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $grp->name }}</span>
                                    @empty
                                        <span class="text-slate-400 text-xs italic">No Group</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ ucfirst(str_replace('_', ' ', $s->source)) }}</span>
                                @if($s->country)
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <i class="bi bi-geo-alt"></i> {{ $s->city ? $s->city . ', ' : '' }}{{ $s->country }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-xs font-medium text-slate-800">{{ $s->subscribed_at ? $s->subscribed_at->format('M j, Y') : $s->created_at->format('M j, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $s->created_at->format('H:i') }}</div>
                            </td>
                            <td class="py-3 px-4 text-right pe-4">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs cursor-pointer" onclick="editSubscriber({{ json_encode($s) }}, {{ json_encode($s->groups->pluck('id')) }})" title="Edit Details">
                                        <i class="bi bi-pencil text-xs"></i>
                                    </button>
                                    <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" onclick="confirmDelete({{ $s->id }}, '{{ $s->email }}')" title="Delete">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 text-xs">
                                <i class="bi bi-people text-4xl block mb-2 opacity-50"></i>
                                <p class="font-bold text-slate-700 mb-1">No subscribers found</p>
                                <span class="text-slate-400">Try adjusting your search criteria or add new subscribers above.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-white">
                {{ $subscribers->links() }}
            </div>
            @endif
        </div>
    </form>
</div>

<!-- Modal: Add Subscriber (Alpine.js) -->
<div x-data="{ open: false }" @open-create-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-person-plus-fill text-primary"></i> Add New Subscriber
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.subscribers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Address *</label>
                    <input type="email" name="email" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required placeholder="user@example.com">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">First Name</label>
                        <input type="text" name="first_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="John">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Last Name</label>
                        <input type="text" name="last_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Doe">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Phone Number (Optional)</label>
                    <input type="text" name="phone" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="+971 50 000 0000">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Subscription Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                        <option value="subscribed" selected>Subscribed (Active)</option>
                        <option value="unsubscribed">Unsubscribed</option>
                        <option value="bounced">Bounced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Assign to Groups / Segments</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $g)
                        <label class="inline-flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer text-xs font-medium text-slate-700">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="groups[]" value="{{ $g->id }}" {{ $g->slug === 'general-newsletter' ? 'checked' : '' }}>
                            <span>{{ $g->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Subscriber (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Subscriber
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Address *</label>
                    <input type="email" name="email" id="editEmail" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">First Name</label>
                        <input type="text" name="first_name" id="editFirstName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Last Name</label>
                        <input type="text" name="last_name" id="editLastName" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" id="editPhone" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                    <select name="status" id="editStatus" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                        <option value="subscribed">Subscribed (Active)</option>
                        <option value="unsubscribed">Unsubscribed</option>
                        <option value="bounced">Bounced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-2">Assign to Groups</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $g)
                        <label class="inline-flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer text-xs font-medium text-slate-700">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 edit-grp-check" type="checkbox" name="groups[]" value="{{ $g->id }}" id="editGrp_{{ $g->id }}">
                            <span>{{ $g->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Update Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Import CSV (Alpine.js) -->
<div x-data="{ open: false }" @open-import-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-up text-primary"></i> Import Subscribers CSV
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.subscribers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-500 leading-relaxed">
                    Upload a CSV file containing contacts. The file must include a header row with at least an <code class="font-mono text-primary bg-primary/5 px-1 py-0.5 rounded">email</code> column (optional: <code>first_name</code>, <code>last_name</code>, <code>phone</code>).
                </p>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Select CSV File *</label>
                    <input type="file" name="csv_file" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-slate-50/50" accept=".csv,text/csv" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Assign to Group (Optional)</label>
                    <select name="group_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                        <option value="">Do not assign to specific group</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500 leading-relaxed">
                    <strong class="text-slate-700">Duplicate handling:</strong> Existing contacts with matching emails will have their details updated without duplicating records.
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const rowSelects = document.querySelectorAll('.row-select');
    const bulkControls = document.getElementById('bulkControls');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const bulkGroupSelect = document.getElementById('bulkGroupSelect');

    function updateBulkState() {
        const anyChecked = Array.from(rowSelects).some(cb => cb.checked);
        if (bulkControls) {
            bulkControls.classList.toggle('hidden', !anyChecked);
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowSelects.forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        });
    }

    rowSelects.forEach(cb => cb.addEventListener('change', updateBulkState));

    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            if (bulkGroupSelect) {
                bulkGroupSelect.classList.toggle('hidden', this.value !== 'assign_group');
            }
        });
    }

    const bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            if (!bulkActionSelect.value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Action Required', text: 'Please select a bulk action to perform.' });
                return;
            }
            if (bulkActionSelect.value === 'delete') {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Selected Subscribers?',
                    text: 'This will soft-delete all checked subscriber records.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete them'
                }).then((res) => {
                    if (res.isConfirmed) bulkForm.submit();
                });
            }
        });
    }
});

function editSubscriber(sub, groupIds) {
    document.getElementById('editForm').action = `/admin/subscribers/${sub.id}`;
    document.getElementById('editEmail').value = sub.email;
    document.getElementById('editFirstName').value = sub.first_name || '';
    document.getElementById('editLastName').value = sub.last_name || '';
    document.getElementById('editPhone').value = sub.phone || '';
    document.getElementById('editStatus').value = sub.status;

    document.querySelectorAll('.edit-grp-check').forEach(cb => {
        cb.checked = groupIds.includes(parseInt(cb.value));
    });

    window.dispatchEvent(new CustomEvent('open-edit-modal'));
}

function confirmDelete(id, email) {
    Swal.fire({
        title: 'Delete Subscriber?',
        html: `Are you sure you want to delete <strong>${email}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/subscribers/${id}`;
            form.submit();
        }
    });
}

function toggleStatus(id) {
    fetch(`/admin/subscribers/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    }).then(res => res.json()).then(data => {
        if (data.success) {
            location.reload();
        }
    }).catch(err => {
        console.error(err);
    });
}
</script>
@endpush
@endsection
