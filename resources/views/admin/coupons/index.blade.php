@extends('layouts.admin')

@section('page_title', 'Coupons & Promo Codes')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Coupons & Promo Codes Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Create promotional codes, set discount limits, track customer redemptions, and drive tour conversions.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.coupons.export') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-file-earmark-arrow-down text-emerald-600 text-sm"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                <i class="bi bi-plus-lg"></i>
                <span>Create Promo Code</span>
            </a>
        </div>
    </div>

    <!-- Unified Section Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-primary text-white shadow-xs">
            <i class="bi bi-ticket-perforated"></i> All Promo Codes
        </a>
        <a href="{{ route('admin.coupons.popup-settings') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition">
            <i class="bi bi-megaphone text-amber-500"></i> Campaign Triggers & Banners (25% & Concierge)
        </a>
    </div>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Promos</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-ticket-perforated-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total_active'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Live and redeemable codes</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Redemptions</span>
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-person-check-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-primary">{{ number_format($stats['total_redemptions'] ?? 0) }}</div>
            <span class="text-[11px] font-bold text-primary mt-1 flex items-center gap-1">
                <i class="bi bi-graph-up-arrow"></i> Guest checkouts
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Discounts Granted</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-piggy-bank-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">AED {{ number_format($stats['total_discount_given'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Total guest savings</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Promo Revenue</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="bi bi-cash-stack text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">AED {{ number_format($stats['total_promo_revenue'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Bookings generated via promos</span>
        </div>
    </div>

    <!-- Table Card & Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-5">
        <!-- Filter Controls -->
        <form method="GET" action="{{ route('admin.coupons.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-5 sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Promo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                        <input type="text" name="search" class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Code, title, description..." value="{{ $search }}">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Discount Type</label>
                    <select name="type" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="percentage" {{ $type === 'percentage' ? 'selected' : '' }}>Percentage (% OFF)</option>
                        <option value="fixed" {{ $type === 'fixed' ? 'selected' : '' }}>Flat Amount (AED)</option>
                        <option value="per_person" {{ $type === 'per_person' ? 'selected' : '' }}>Per Person (AED)</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs">
                        Filter
                    </button>
                    @if($search || $type || $status)
                        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-2xs transition" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700" id="couponsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Promo Code</th>
                        <th class="py-3 px-4">Promotion Name</th>
                        <th class="py-3 px-4">Discount</th>
                        <th class="py-3 px-4">Rules & Scope</th>
                        <th class="py-3 px-4 text-center">Redemptions</th>
                        <th class="py-3 px-4 text-center">Validity Window</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($coupons as $coupon)
                    @php
                        $now = now();
                        $isExpired = $coupon->valid_until && $now->gt($coupon->valid_until);
                        $isScheduled = $coupon->valid_from && $now->lt($coupon->valid_from);
                        $limitReached = $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit;
                        $pctUsed = $coupon->usage_limit ? round(($coupon->used_count / $coupon->usage_limit) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Code with 1-click copy -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs tracking-wider">
                                    {{ $coupon->code }}
                                </span>
                                <button type="button" class="w-7 h-7 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-500 flex items-center justify-center bg-white transition copy-code-btn cursor-pointer" data-code="{{ $coupon->code }}" title="Copy Code">
                                    <i class="bi bi-clipboard text-xs"></i>
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @if($coupon->is_featured)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="bi bi-star-fill text-amber-500"></i> Featured
                                    </span>
                                @endif
                                @if($coupon->code === 'MATCH5')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="bi bi-compass"></i> Concierge
                                    </span>
                                @elseif($coupon->code === 'DUNESWELCOME' || $coupon->code === 'FIRST25')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">
                                        <i class="bi bi-gift-fill"></i> 25% Welcome
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Name & Applicability -->
                        <td class="py-3.5 px-4">
                            <div class="text-xs font-bold text-slate-900">{{ $coupon->name }}</div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @if($coupon->tour)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i class="bi bi-compass me-1"></i>{{ Str::limit($coupon->tour->name, 25) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="bi bi-globe me-1"></i>Sitewide
                                    </span>
                                @endif
                                @if($coupon->tier)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                                        {{ $coupon->tier->display_name }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Discount Value & Type -->
                        <td class="py-3.5 px-4">
                            @if($coupon->discount_type === 'percentage')
                                <div class="text-sm font-black text-emerald-600">{{ (float)$coupon->discount_value }}% OFF</div>
                                @if($coupon->max_discount)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Cap: AED {{ number_format($coupon->max_discount) }}</div>
                                @endif
                            @elseif($coupon->discount_type === 'per_person')
                                <div class="text-sm font-black text-primary">AED {{ number_format($coupon->discount_value) }} <span class="text-[10px] text-slate-400 font-normal">/ guest</span></div>
                                @if($coupon->max_discount)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Cap: AED {{ number_format($coupon->max_discount) }}</div>
                                @endif
                            @else
                                <div class="text-sm font-black text-slate-900">AED {{ number_format($coupon->discount_value) }} <span class="text-[10px] text-slate-400 font-normal">Flat</span></div>
                            @endif
                        </td>

                        <!-- Rules -->
                        <td class="py-3.5 px-4 text-xs text-slate-600">
                            <div class="space-y-0.5">
                                @if($coupon->min_spend > 0)
                                    <div class="flex items-center gap-1"><i class="bi bi-cart text-slate-400"></i> Min: <strong>AED {{ number_format($coupon->min_spend) }}</strong></div>
                                @endif
                                @if($coupon->min_guests > 1)
                                    <div class="flex items-center gap-1"><i class="bi bi-people text-slate-400"></i> Min: <strong>{{ $coupon->min_guests }}+</strong> guests</div>
                                @endif
                                @if($coupon->first_time_only)
                                    <div class="text-rose-600 font-bold flex items-center gap-1"><i class="bi bi-person-plus"></i> First-time only</div>
                                @endif
                                @if(!$coupon->min_spend && $coupon->min_guests <= 1 && !$coupon->first_time_only)
                                    <span class="text-slate-400 italic">No restrictions</span>
                                @endif
                            </div>
                        </td>

                        <!-- Usage Progress & Redemptions -->
                        <td class="py-3.5 px-4 text-center">
                            <button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition view-usages-btn cursor-pointer" data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}">
                                <i class="bi bi-eye text-primary"></i> {{ $coupon->used_count }} {{ $coupon->usage_limit ? "/ {$coupon->usage_limit}" : '' }}
                            </button>
                            @if($coupon->usage_limit)
                                <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-1.5 mx-auto overflow-hidden">
                                    <div class="h-full rounded-full {{ $limitReached ? 'bg-rose-500' : 'bg-primary' }}" style="width: {{ min(100, $pctUsed) }}%"></div>
                                </div>
                            @endif
                        </td>

                        <!-- Validity Window -->
                        <td class="py-3.5 px-4 text-center">
                            @if($isExpired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="bi bi-clock-history me-1"></i> Expired
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $coupon->valid_until->format('M j, Y') }}</div>
                            @elseif($isScheduled)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    <i class="bi bi-calendar-event me-1"></i> Starts {{ $coupon->valid_from->format('M j') }}
                                </span>
                            @elseif($coupon->valid_until)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="bi bi-check-circle me-1"></i> Until {{ $coupon->valid_until->format('M j') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">No Expiry</span>
                            @endif
                        </td>

                        <!-- Status Switch -->
                        <td class="py-3.5 px-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer status-toggle-switch" data-id="{{ $coupon->id }}" {{ $coupon->status === 'active' && !$isExpired && !$limitReached ? 'checked' : '' }} {{ $isExpired || $limitReached ? 'disabled' : '' }}>
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 peer-disabled:opacity-40"></div>
                            </label>
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-3.5 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="Edit Promo">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </a>
                                
                                <div class="relative">
                                    <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 bg-white transition shadow-2xs cursor-pointer" title="More Actions">
                                        <i class="bi bi-three-dots-vertical text-xs"></i>
                                    </button>

                                    <div x-show="menuOpen" @click.away="menuOpen = false" x-transition x-cloak class="absolute right-0 mt-1.5 w-48 bg-white rounded-2xl border border-slate-200 shadow-xl py-1.5 z-20 text-left">
                                        <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-primary transition">
                                            <i class="bi bi-pencil text-primary"></i> Edit Promo
                                        </a>
                                        <form action="{{ route('admin.coupons.duplicate', $coupon->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-sky-600 transition text-left cursor-pointer">
                                                <i class="bi bi-copy text-sky-600"></i> Duplicate Promo
                                            </button>
                                        </form>
                                        <button type="button" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition text-left view-usages-btn cursor-pointer" data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}">
                                            <i class="bi bi-receipt text-emerald-600"></i> View Redemptions ({{ $coupon->used_count }})
                                        </button>
                                        <div class="my-1 border-t border-slate-100"></div>
                                        <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="delete-coupon-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition text-left delete-coupon-btn cursor-pointer" data-code="{{ $coupon->code }}">
                                                <i class="bi bi-trash"></i> Archive Promo
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="text-slate-300 mb-2"><i class="bi bi-ticket-perforated text-4xl"></i></div>
                            <h3 class="text-base font-bold text-slate-900">No Promo Codes Found</h3>
                            <p class="text-xs text-slate-400 mb-3">Create high-converting coupons to boost bookings and reward returning travelers.</p>
                            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                                <i class="bi bi-plus-lg"></i> Create First Promo
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Redemptions Audit Modal (Alpine.js) -->
<div x-data="{ open: false }" @open-usages-modal.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-900" id="modalCouponTitle">Coupon Redemptions</h3>
                    <p class="text-xs text-slate-400" id="modalCouponSubtitle">Customer usage audit trail</p>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div id="modalCouponBody" class="max-h-96 overflow-y-auto">
                <div class="text-center py-6">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                    <div class="mt-2 text-xs text-slate-500 font-bold">Loading redemption logs...</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 1-Click Code Copy (Delegated for DataTables)
    $(document).on('click', '.copy-code-btn', function() {
        const btn = this;
        const code = $(this).data('code');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                const $icon = $(btn).find('i');
                $icon.attr('class', 'bi bi-check-lg text-emerald-600 text-xs');
                setTimeout(() => { $icon.attr('class', 'bi bi-clipboard text-xs'); }, 1500);
            });
        }
    });

    // AJAX Toggle Status Switch (Delegated for DataTables)
    $(document).on('change', '.status-toggle-switch', function() {
        const toggle = this;
        const couponId = $(this).data('id');
        const originalState = !toggle.checked;

        fetch(`/admin/coupons/${couponId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                toggle.checked = originalState;
                Swal.fire('Error', data.message || 'Failed to update status.', 'error');
            }
        })
        .catch(() => {
            toggle.checked = originalState;
            Swal.fire('Error', 'Network error occurred.', 'error');
        });
    });

    // Usages Audit Modal
    $(document).on('click', '.view-usages-btn', function() {
        const couponId = $(this).data('id');
        const code = $(this).data('code');
        document.getElementById('modalCouponTitle').innerText = `Redemptions for ${code}`;
        document.getElementById('modalCouponBody').innerHTML = `
            <div class="text-center py-6">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <div class="mt-2 text-xs text-slate-500 font-bold">Loading redemption logs...</div>
            </div>
        `;
        window.dispatchEvent(new CustomEvent('open-usages-modal'));

        fetch(`/admin/coupons/${couponId}/usages`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.usages.length > 0) {
                    let html = `
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-3">Date</th>
                                        <th class="py-2.5 px-3">Customer</th>
                                        <th class="py-2.5 px-3">Booking Ref</th>
                                        <th class="py-2.5 px-3 text-right">Discount</th>
                                        <th class="py-2.5 px-3 text-right">Order Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                    `;
                    data.usages.forEach(u => {
                        const dateStr = new Date(u.used_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        html += `
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2.5 px-3 text-slate-500">${dateStr}</td>
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-slate-900">${u.customer_name || 'Guest'}</div>
                                    <div class="text-[11px] text-slate-400">${u.customer_email}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">${u.booking_reference || ('#' + u.booking_id)}</span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-emerald-600">-AED ${parseFloat(u.discount_amount).toFixed(2)}</td>
                                <td class="py-2.5 px-3 text-right font-black text-slate-900">AED ${parseFloat(u.order_final_total).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                    document.getElementById('modalCouponBody').innerHTML = html;
                } else {
                    document.getElementById('modalCouponBody').innerHTML = `
                        <div class="text-center py-6 text-slate-400">
                            <i class="bi bi-inbox text-3xl mb-2 block opacity-50"></i>
                            <div class="font-bold text-slate-700 text-xs">No redemptions yet</div>
                            <small class="text-slate-400">This promo code has not been redeemed by any customers so far.</small>
                        </div>
                    `;
                }
            })
            .catch(() => {
                document.getElementById('modalCouponBody').innerHTML = `<div class="p-4 rounded-xl bg-rose-50 text-rose-700 text-xs">Failed to load redemptions log.</div>`;
            });
    });

    // Delete Confirmation (Delegated for DataTables)
    $(document).on('click', '.delete-coupon-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('.delete-coupon-form')[0];
        const code = $(this).data('code');

        Swal.fire({
            title: `Archive Promo ${code}?`,
            text: "This promo code will be deactivated and archived from the active promotions list.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Archive It'
        }).then((result) => {
            if (result.isConfirmed && form) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
