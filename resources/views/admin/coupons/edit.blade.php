@extends('layouts.admin')

@section('page_title', 'Edit Promo Code #' . $coupon->code)

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div>
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all mb-2">
            <i class="bi bi-chevron-left text-xs"></i> Back to Coupons
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Promotion: <span class="text-primary font-mono">{{ $coupon->code }}</span></h1>
                <p class="text-xs text-slate-500 mt-0.5">Update promotional parameters, discount limits, and validity dates.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border capitalize {{ $coupon->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                {{ $coupon->status }}
            </span>
        </div>
    </div>

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Main Column -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Basic Details Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-ticket-perforated text-base"></i> 1. Promotion Identity
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Promo Code <span class="text-rose-600">*</span></label>
                            <input type="text" name="code" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-black uppercase font-mono tracking-wider text-slate-900 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden @error('code') border-rose-300 @enderror" value="{{ old('code', $coupon->code) }}" required>
                            @error('code')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Promotion Name <span class="text-rose-600">*</span></label>
                            <input type="text" name="name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-bold text-slate-900 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden @error('name') border-rose-300 @enderror" value="{{ old('name', $coupon->name) }}" required>
                            @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Internal Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden">{{ old('description', $coupon->description) }}</textarea>
                    </div>
                </div>

                <!-- Discount Calculation Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-percent text-base"></i> 2. Discount Structure
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Discount Type <span class="text-rose-600">*</span></label>
                            <select name="discount_type" id="discountTypeSelect" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white" required>
                                <option value="percentage" {{ old('discount_type', $coupon->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (% OFF)</option>
                                <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>Flat Amount (AED OFF)</option>
                                <option value="per_person" {{ old('discount_type', $coupon->discount_type) === 'per_person' ? 'selected' : '' }}>Per Guest (AED / person)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5" id="discountValueLabel">Discount Value <span class="text-rose-600">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-black text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('discount_value', $coupon->discount_value) }}" required>
                            @error('discount_value')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Max Discount Cap (AED)</label>
                            <input type="number" step="0.01" min="0" name="max_discount" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="No Cap" value="{{ old('max_discount', $coupon->max_discount) }}">
                        </div>
                    </div>

                    <div class="rounded-xl bg-primary/5 border border-primary/20 p-3.5 flex items-start gap-2.5 text-xs text-slate-700" id="discountTypeHint">
                        <i class="bi bi-info-circle-fill text-primary text-sm shrink-0 mt-0.5"></i>
                        <span id="discountTypeHintText">Configure how savings are calculated for guests.</span>
                    </div>
                </div>

                <!-- Applicability & Constraints Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-crosshair text-base"></i> 3. Targeting & Applicability
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Applicable Tour</label>
                            <select name="tour_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                                <option value="">Sitewide (All Tours)</option>
                                @foreach($tours as $t)
                                    <option value="{{ $t->id }}" {{ old('tour_id', $coupon->tour_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Applicable Package / Tier</label>
                            <select name="tier_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                                <option value="">All Packages / Tiers</option>
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}" {{ old('tier_id', $coupon->tier_id) == $tier->id ? 'selected' : '' }}>{{ $tier->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Minimum Spend (AED)</label>
                            <input type="number" step="0.01" min="0" name="min_spend" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('min_spend', $coupon->min_spend) }}">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Minimum Guest Count</label>
                            <input type="number" min="1" max="50" name="min_guests" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('min_guests', $coupon->min_guests) }}">
                        </div>
                    </div>
                </div>

                <!-- Date Constraints Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-calendar3 text-base"></i> 4. Schedule & Date Windows
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Booking Valid From</label>
                            <input type="datetime-local" name="valid_from" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('valid_from', $coupon->valid_from ? $coupon->valid_from->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Booking Valid Until (Expiry)</label>
                            <input type="datetime-local" name="valid_until" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('valid_until', $coupon->valid_until ? $coupon->valid_until->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Travel Date From</label>
                            <input type="date" name="tour_date_from" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('tour_date_from', $coupon->tour_date_from ? $coupon->tour_date_from->format('Y-m-d') : '') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Travel Date To</label>
                            <input type="date" name="tour_date_to" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('tour_date_to', $coupon->tour_date_to ? $coupon->tour_date_to->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side Column -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Usage Stats & Status Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-shield-lock text-base"></i> 5. Usage Limits & Status
                    </h2>

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Total Redemptions</div>
                        <div class="text-3xl font-black text-primary">{{ $coupon->used_count }}</div>
                        @if($coupon->usage_limit)
                            <div class="text-[11px] text-slate-400 mt-1">Cap: {{ $coupon->usage_limit }} ({{ round(($coupon->used_count / $coupon->usage_limit) * 100) }}% Used)</div>
                        @else
                            <div class="text-[11px] text-slate-400 mt-1">Unlimited Redemptions</div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                            <option value="active" {{ old('status', $coupon->status) === 'active' ? 'selected' : '' }}>Active (Live)</option>
                            <option value="inactive" {{ old('status', $coupon->status) === 'inactive' ? 'selected' : '' }}>Inactive (Paused)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Total Usage Limit</label>
                        <input type="number" min="1" name="usage_limit" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Unlimited" value="{{ old('usage_limit', $coupon->usage_limit) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Limit Per Customer Email</label>
                        <input type="number" min="1" name="usage_limit_per_user" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}">
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="first_time_only" id="firstTimeSwitch" value="1" {{ old('first_time_only', $coupon->first_time_only) ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-800">First-Time Guests Only</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_featured" id="featuredSwitch" value="1" {{ old('is_featured', $coupon->is_featured) ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-800">Featured Promotion</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all">
                        <i class="bi bi-check-lg"></i> Update Promo Code
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" class="w-full inline-flex items-center justify-center py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('discountTypeSelect');
    const hintText = document.getElementById('discountTypeHintText');
    const valueLabel = document.getElementById('discountValueLabel');

    function updateDiscountUI() {
        const val = typeSelect.value;
        if (val === 'percentage') {
            valueLabel.innerText = 'Discount Percentage (% OFF) *';
            hintText.innerText = 'Applies a percentage discount across the total cart. Example: 15% off AED 400 = AED 60 savings.';
        } else if (val === 'per_person') {
            valueLabel.innerText = 'Discount Amount Per Guest (AED) *';
            hintText.innerText = 'Applies a fixed discount for each guest/adult. Example: AED 25/person for 4 adults = AED 100 savings.';
        } else {
            valueLabel.innerText = 'Flat Discount Amount (AED) *';
            hintText.innerText = 'Deducts a flat AED amount from the final cart total. Example: AED 50 flat off AED 350 = AED 300 payable.';
        }
    }

    typeSelect.addEventListener('change', updateDiscountUI);
    updateDiscountUI();
});
</script>
@endpush
@endsection
