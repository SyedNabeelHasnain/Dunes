@extends('layouts.admin')

@section('page_title', 'Create Manual Booking')

@section('content')
@php
    $initialTourId = old('tour_id', $prefill['tour_id'] ?: ($toursCatalog[0]['id'] ?? ''));
    $initialTierId = old('tier_id', $prefill['tier_id'] ?: '');
    $initialAdults = (int) old('adults', $prefill['adults'] ?? 2);
    $initialChildren = (int) old('children', $prefill['children'] ?? 0);
    $initialInfants = (int) old('infants', $prefill['infants'] ?? 0);
    $initialAddons = array_map('intval', (array) old('addons', []));
    $initialCouponCode = (string) old('coupon_code', '');
    $initialDiscountAmount = old('discount_amount', '');
    $initialCustomTotal = old('custom_total', '');
    $initialStatus = (string) old('status', 'confirmed');
    $initialPaymentMethod = (string) old('payment_method', 'cash');
    $initialPaymentStatus = (string) old('payment_status', 'unpaid');
    $initialPaymentAmount = old('payment_amount', '');
    $initialBalanceDue = old('balance_due', '');
@endphp

<div class="space-y-6" x-data="manualBookingCreator()">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3.5 py-1.5 shadow-2xs hover:bg-slate-50 transition-all mb-2">
                <i class="bi bi-chevron-left text-xs"></i> Back to Bookings
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-calendar-plus-fill text-primary"></i> Create Manual Booking
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Record walk-in, phone, WhatsApp, or B2B partner reservations with live package & add-on pricing calculation.</p>
        </div>
    </div>

    <form action="{{ route('admin.bookings.store') }}" method="POST" id="createManualBookingForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left 8 Columns: Customer, Tour, Guests, Add-ons, Logistics -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Customer Details Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-person-vcard-fill text-base"></i> 1. Guest & Contact Details
                        </h2>
                        <span class="text-[11px] font-semibold text-slate-400">Required for e-ticket & dispatch</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="customerName" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" name="name" id="customerName" required
                                       value="{{ old('name', $prefill['name']) }}"
                                       placeholder="e.g. John Doe"
                                       class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-sm text-slate-800 outline-hidden focus:border-primary transition">
                            </div>
                        </div>

                        <div>
                            <label for="customerEmail" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" id="customerEmail" required
                                       value="{{ old('email', $prefill['email']) }}"
                                       placeholder="guest@example.com"
                                       class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-sm text-slate-800 outline-hidden focus:border-primary transition">
                            </div>
                        </div>

                        <div>
                            <label for="customerPhone" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Phone / WhatsApp <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <input type="text" name="phone" id="customerPhone" required
                                       value="{{ old('phone', $prefill['phone']) }}"
                                       placeholder="+971 50 123 4567"
                                       class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-sm text-slate-800 outline-hidden focus:border-primary transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Tour, Package Tier, Date & Guest Counts Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-compass-fill text-base"></i> 2. Experience, Package Tier & Pax
                        </h2>
                        <span class="text-[11px] font-semibold text-slate-400" x-text="selectedTierObj ? (selectedTierObj.price_type === 'per_vehicle' ? 'Private Vehicle Pricing' : 'Per-Person Pricing') : ''"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label for="tourSelect" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Select Tour Experience <span class="text-rose-500">*</span></label>
                            <select name="tour_id" id="tourSelect" x-model.number="tourId" @change="onTourChange()" required
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-semibold text-slate-800 bg-white outline-hidden focus:border-primary transition">
                                <template x-for="tour in tours" :key="tour.id">
                                    <option :value="tour.id" x-text="tour.name + ' (Base: AED ' + Number(tour.price).toLocaleString() + ')'" :selected="Number(tour.id) === Number(tourId)"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="tierSelect" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Package Tier <span class="text-rose-500">*</span></label>
                            <select name="tier_id" id="tierSelect" x-model.number="tierId" @change="recalc()" required
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-semibold text-slate-800 bg-white outline-hidden focus:border-primary transition">
                                <template x-for="tier in availableTiers" :key="tier.id">
                                    <option :value="tier.id"
                                            x-text="tier.name + ' — AED ' + Number(tier.price).toLocaleString() + (tier.price_type === 'per_vehicle' ? ' / vehicle (up to ' + tier.capacity + ' pax)' : ' / adult')"
                                            :selected="Number(tier.id) === Number(tierId)"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="tourDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="tour_date" id="tourDate" required
                               value="{{ old('tour_date', $prefill['tour_date']) }}"
                               class="w-full sm:w-1/2 rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-semibold text-slate-800 outline-hidden focus:border-primary transition">
                    </div>

                    <!-- Pax Steppers -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="adultsInput" class="text-xs font-bold uppercase text-slate-700">Adults (12+ yrs)</label>
                                <span class="text-[11px] font-bold text-primary" x-text="selectedTierObj ? ('AED ' + Number(selectedTierObj.price).toLocaleString()) : ''"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="adults = Math.max(1, Number(adults) - 1); recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">-</button>
                                <input type="number" name="adults" id="adultsInput" min="1" max="500" x-model.number="adults" @input="recalc()" required
                                       class="w-full text-center rounded-lg border border-slate-200 py-1.5 text-sm font-black text-slate-900 bg-white">
                                <button type="button" @click="adults = Number(adults) + 1; recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">+</button>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="childrenInput" class="text-xs font-bold uppercase text-slate-700">Children (3–11 yrs)</label>
                                <span class="text-[11px] font-bold text-slate-500" x-text="childUnitPriceLabel()"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="children = Math.max(0, Number(children) - 1); recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">-</button>
                                <input type="number" name="children" id="childrenInput" min="0" max="500" x-model.number="children" @input="recalc()"
                                       class="w-full text-center rounded-lg border border-slate-200 py-1.5 text-sm font-black text-slate-900 bg-white">
                                <button type="button" @click="children = Number(children) + 1; recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">+</button>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="infantsInput" class="text-xs font-bold uppercase text-slate-700">Infants (0–2 yrs)</label>
                                <span class="text-[11px] font-bold text-emerald-600">Free</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="infants = Math.max(0, Number(infants) - 1); recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">-</button>
                                <input type="number" name="infants" id="infantsInput" min="0" max="500" x-model.number="infants" @input="recalc()"
                                       class="w-full text-center rounded-lg border border-slate-200 py-1.5 text-sm font-black text-slate-900 bg-white">
                                <button type="button" @click="infants = Number(infants) + 1; recalc()" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold flex items-center justify-center transition cursor-pointer">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Optional Experience Add-ons Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-puzzle-fill text-base"></i> 3. Optional Experience Add-ons & Upgrades
                        </h2>
                        <span class="text-xs font-bold text-slate-500" x-text="selectedAddons.length + ' selected'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="addon in availableAddons" :key="addon.id">
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border transition cursor-pointer select-none"
                                   :class="selectedAddons.includes(Number(addon.id)) ? 'border-primary bg-orange-50/40 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <input type="checkbox" name="addons[]" :value="addon.id"
                                       :checked="selectedAddons.includes(Number(addon.id))"
                                       @change="toggleAddon(Number(addon.id))"
                                       class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary w-4 h-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-900 truncate" x-text="addon.name"></span>
                                        <span class="text-xs font-black text-primary shrink-0" x-text="'+ AED ' + Number(addon.price).toLocaleString()"></span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-semibold text-slate-500 uppercase" x-text="addon.price_type === 'per_group' ? 'Flat Per Booking' : 'Per Guest (' + Math.max(1, Number(adults) + Number(children)) + 'x)'"></span>
                                        <template x-if="addon.is_tour_specific">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Tour Upgrade</span>
                                        </template>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- 4. Pickup Location & Operational Notes Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-geo-alt-fill text-base"></i> 4. Pickup Logistics & Special Requests
                        </h2>
                        <span class="text-[11px] font-semibold text-slate-400">Synced with Daily Operations Manifest</span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="pickupLocation" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Hotel / Residence Pickup Location</label>
                            <input type="text" name="pickup_location" id="pickupLocation"
                                   value="{{ old('pickup_location', $prefill['pickup_location']) }}"
                                   placeholder="e.g. Atlantis The Palm, Room 412 (or Direct Camp Meeting Point)"
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 outline-hidden focus:border-primary transition">
                        </div>

                        <div>
                            <label for="specialRequests" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Special Requests / Dietary / Dispatch Notes</label>
                            <textarea name="special_requests" id="specialRequests" rows="3"
                                      placeholder="Vegetarian BBQ, child seat needed, VIP table, B2B voucher reference, etc."
                                      class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 outline-hidden focus:border-primary transition">{{ old('special_requests', $prefill['special_requests']) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4 Columns: Pricing Summary, Discounts, Payment & Actions -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-20">

                <!-- Financial Breakdown & Discount Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-calculator-fill text-base"></i> Live Pricing Summary
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">AED</span>
                    </div>

                    <div class="space-y-2.5 text-xs border-b border-slate-100 pb-4 mb-4">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Package Base Subtotal</span>
                            <span class="font-bold text-slate-900" x-text="'AED ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                        <div class="text-[11px] text-slate-400" x-text="packageBreakdownText()"></div>

                        <div class="flex items-center justify-between text-slate-600 pt-1">
                            <span>Selected Add-ons</span>
                            <span class="font-bold text-slate-900" x-text="'+ AED ' + addonsTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>

                        <div class="flex items-center justify-between text-slate-800 font-bold pt-2 border-t border-slate-100">
                            <span>Gross Subtotal</span>
                            <span x-text="'AED ' + originalTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <!-- Promo Code / Discount Controls -->
                    <div class="space-y-3 border-b border-slate-100 pb-4 mb-4">
                        <div>
                            <label for="couponCodeSelect" class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Apply Promo Code (Optional)</label>
                            <select id="couponCodeSelect" x-model="couponCode" @change="onCouponChange()"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-800 bg-white outline-hidden focus:border-primary">
                                <option value="">— No Promo Code —</option>
                                <template x-for="c in coupons" :key="c.code">
                                    <option :value="c.code" x-text="c.code + ' (' + (c.discount_type === 'percentage' ? c.discount_value + '% OFF' : 'AED ' + c.discount_value + ' OFF') + ')'"></option>
                                </template>
                            </select>
                            <input type="hidden" name="coupon_code" :value="couponCode">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="discountAmountInput" class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Discount (AED)</label>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="discountAmountInput"
                                       x-model="manualDiscountAmount" @input="recalc()"
                                       placeholder="0.00"
                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-emerald-700 outline-hidden focus:border-primary">
                            </div>

                            <div>
                                <label for="customTotalInput" class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1" title="Leave blank to use auto-calculated total">Custom Total Override</label>
                                <input type="number" step="0.01" min="0" name="custom_total" id="customTotalInput"
                                       x-model="customTotal" @input="recalc()"
                                       placeholder="Auto"
                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-900 outline-hidden focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Final Net Total Banner -->
                    <div class="p-4 rounded-xl bg-slate-900 text-white flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Net Total Payable</div>
                            <div class="text-xl font-black text-primary mt-0.5" x-text="'AED ' + finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                        </div>
                        <div class="text-right" x-show="effectiveDiscount > 0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                                  x-text="'Saved AED ' + effectiveDiscount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                    </div>
                </div>

                <!-- Payment & Booking Status Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                            <i class="bi bi-credit-card-2-front-fill text-base"></i> Status & Payment
                        </h2>
                    </div>

                    <div>
                        <label for="bookingStatusSelect" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Booking Status <span class="text-rose-500">*</span></label>
                        <select name="status" id="bookingStatusSelect" x-model="status" required
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 bg-white outline-hidden focus:border-primary">
                            <option value="confirmed">Confirmed (Active Reservation)</option>
                            <option value="pending">Pending Confirmation</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label for="paymentMethodSelect" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Payment Method <span class="text-rose-500">*</span></label>
                        <select name="payment_method" id="paymentMethodSelect" x-model="paymentMethod" required
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 bg-white outline-hidden focus:border-primary">
                            <option value="cash">Cash on Pickup / Arrival</option>
                            <option value="card_pos">Card / POS Terminal</option>
                            <option value="bank_transfer">Bank Transfer / Wire</option>
                            <option value="full">Online Payment (Ziina / Card)</option>
                            <option value="advance">25% Advance Deposit</option>
                            <option value="complimentary">Complimentary / B2B Voucher</option>
                        </select>
                    </div>

                    <div>
                        <label for="paymentStatusSelect" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Payment Status <span class="text-rose-500">*</span></label>
                        <select name="payment_status" id="paymentStatusSelect" x-model="paymentStatus" @change="onPaymentStatusChange()" required
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 bg-white outline-hidden focus:border-primary">
                            <option value="unpaid">Unpaid (Collect Full Balance)</option>
                            <option value="partial">Partially Paid (Deposit Collected)</option>
                            <option value="paid">Fully Paid (Balance AED 0)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label for="paymentAmountInput" class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Amount Paid (AED)</label>
                            <input type="number" step="0.01" min="0" name="payment_amount" id="paymentAmountInput"
                                   x-model.number="paymentAmount" @input="onPaymentAmountInput()"
                                   :readonly="paymentStatus !== 'partial'"
                                   :class="paymentStatus !== 'partial' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white text-emerald-700'"
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold outline-hidden focus:border-primary">
                        </div>

                        <div>
                            <label for="balanceDueInput" class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Balance Due (AED)</label>
                            <input type="number" step="0.01" min="0" name="balance_due" id="balanceDueInput"
                                   x-model.number="balanceDue"
                                   readonly
                                   class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-bold text-rose-600 cursor-not-allowed outline-hidden">
                        </div>
                    </div>

                    <!-- Notifications & Payment Link Checkboxes -->
                    <div class="pt-3 border-t border-slate-100 space-y-2.5">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="send_confirmation_email" value="1" checked
                                   class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Send confirmation email to guest</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer select-none" :class="balanceDue <= 0 ? 'opacity-50' : ''">
                            <input type="checkbox" name="generate_payment_link" value="1"
                                   :disabled="balanceDue <= 0"
                                   class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Generate Ziina payment link for balance</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-black text-xs uppercase tracking-wider shadow-sm transition cursor-pointer mt-2">
                        <i class="bi bi-check2-circle text-base"></i> Create Booking Now
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function manualBookingCreator() {
    return {
        tours: @json($toursCatalog),
        coupons: @json($couponsCatalog),
        tourId: Number(@json($initialTourId)) || 0,
        tierId: Number(@json($initialTierId)) || 0,
        adults: Number(@json($initialAdults)) || 2,
        children: Number(@json($initialChildren)) || 0,
        infants: Number(@json($initialInfants)) || 0,
        selectedAddons: @json($initialAddons),
        couponCode: @json($initialCouponCode),
        manualDiscountAmount: @json($initialDiscountAmount),
        customTotal: @json($initialCustomTotal),
        status: @json($initialStatus),
        paymentMethod: @json($initialPaymentMethod),
        paymentStatus: @json($initialPaymentStatus),
        paymentAmount: Number(@json($initialPaymentAmount)) || 0,
        balanceDue: Number(@json($initialBalanceDue)) || 0,

        subtotal: 0,
        addonsTotal: 0,
        originalTotal: 0,
        effectiveDiscount: 0,
        finalTotal: 0,

        init() {
            if (!this.tourId && this.tours.length > 0) {
                this.tourId = Number(this.tours[0].id);
            }
            const tour = this.selectedTourObj;
            if (tour && tour.tiers.length > 0) {
                const hasTier = tour.tiers.some(t => Number(t.id) === Number(this.tierId));
                if (!hasTier) {
                    this.tierId = Number(tour.tiers[0].id);
                }
            }
            this.recalc();
        },

        get selectedTourObj() {
            return this.tours.find(t => Number(t.id) === Number(this.tourId)) || this.tours[0] || null;
        },

        get availableTiers() {
            return this.selectedTourObj ? this.selectedTourObj.tiers : [];
        },

        get selectedTierObj() {
            return this.availableTiers.find(t => Number(t.id) === Number(this.tierId)) || this.availableTiers[0] || null;
        },

        get availableAddons() {
            return this.selectedTourObj ? this.selectedTourObj.addons : [];
        },

        onTourChange() {
            const tiers = this.availableTiers;
            if (tiers.length > 0 && !tiers.some(t => Number(t.id) === Number(this.tierId))) {
                this.tierId = Number(tiers[0].id);
            }
            this.recalc();
        },

        toggleAddon(id) {
            const idx = this.selectedAddons.indexOf(id);
            if (idx > -1) {
                this.selectedAddons.splice(idx, 1);
            } else {
                this.selectedAddons.push(id);
            }
            this.recalc();
        },

        childUnitPriceLabel() {
            const tier = this.selectedTierObj;
            if (!tier) return '';
            if (tier.price_type === 'per_vehicle') return 'Included in vehicle';
            const cp = Number(tier.child_price) > 0 ? Number(tier.child_price) : Math.round(Number(tier.price) * 0.70);
            return 'AED ' + cp.toLocaleString();
        },

        packageBreakdownText() {
            const tier = this.selectedTierObj;
            if (!tier) return '';
            const a = Math.max(1, Number(this.adults) || 1);
            const c = Math.max(0, Number(this.children) || 0);
            if (tier.price_type === 'per_vehicle') {
                const cap = Math.max(1, Number(tier.capacity) || 6);
                const vehicles = Math.ceil((a + c) / cap);
                return vehicles + ' Vehicle(s) × AED ' + Number(tier.price).toLocaleString() + ' (up to ' + cap + ' pax/vehicle)';
            }
            const cp = Number(tier.child_price) > 0 ? Number(tier.child_price) : (Number(tier.price) * 0.70);
            let parts = [a + ' Adult(s) × AED ' + Number(tier.price).toLocaleString()];
            if (c > 0) {
                parts.push(c + ' Child(ren) × AED ' + cp.toFixed(2));
            }
            return parts.join(' + ');
        },

        onCouponChange() {
            if (!this.couponCode) {
                this.manualDiscountAmount = '';
                this.recalc();
                return;
            }
            const c = this.coupons.find(item => item.code === this.couponCode);
            if (c) {
                const guests = Math.max(1, (Number(this.adults) || 1) + (Number(this.children) || 0));
                let disc = 0;
                if (c.discount_type === 'percentage') {
                    disc = (this.originalTotal * Number(c.discount_value)) / 100;
                } else if (c.discount_type === 'per_person') {
                    disc = Number(c.discount_value) * guests;
                } else {
                    disc = Number(c.discount_value);
                }
                if (c.max_discount && disc > Number(c.max_discount)) {
                    disc = Number(c.max_discount);
                }
                disc = Math.min(this.originalTotal, Math.round(disc * 100) / 100);
                this.manualDiscountAmount = disc.toFixed(2);
            }
            this.recalc();
        },

        recalc() {
            const tier = this.selectedTierObj;
            const a = Math.max(1, Number(this.adults) || 1);
            const c = Math.max(0, Number(this.children) || 0);
            const billableGuests = Math.max(1, a + c);

            if (tier) {
                if (tier.price_type === 'per_vehicle') {
                    const cap = Math.max(1, Number(tier.capacity) || 6);
                    const vehicles = Math.ceil(billableGuests / cap);
                    this.subtotal = vehicles * Number(tier.price);
                } else {
                    const cp = Number(tier.child_price) > 0 ? Number(tier.child_price) : (Number(tier.price) * 0.70);
                    this.subtotal = (a * Number(tier.price)) + (c * cp);
                }
            } else {
                this.subtotal = 0;
            }

            let addonsSum = 0;
            const addonsCatalog = this.availableAddons;
            this.selectedAddons.forEach(id => {
                const addon = addonsCatalog.find(item => Number(item.id) === Number(id));
                if (addon) {
                    const qty = addon.price_type === 'per_group' ? 1 : billableGuests;
                    addonsSum += Number(addon.price) * qty;
                }
            });
            this.addonsTotal = Math.round(addonsSum * 100) / 100;
            this.subtotal = Math.round(this.subtotal * 100) / 100;
            this.originalTotal = Math.round((this.subtotal + this.addonsTotal) * 100) / 100;

            let disc = 0;
            if (this.manualDiscountAmount !== '' && this.manualDiscountAmount !== null && !isNaN(this.manualDiscountAmount)) {
                disc = Math.min(this.originalTotal, Math.max(0, Number(this.manualDiscountAmount)));
            }
            this.effectiveDiscount = Math.round(disc * 100) / 100;

            if (this.customTotal !== '' && this.customTotal !== null && !isNaN(this.customTotal)) {
                this.finalTotal = Math.max(0, Math.round(Number(this.customTotal) * 100) / 100);
                if (this.finalTotal < this.originalTotal && this.effectiveDiscount === 0) {
                    this.effectiveDiscount = Math.round((this.originalTotal - this.finalTotal) * 100) / 100;
                }
            } else {
                this.finalTotal = Math.max(0, Math.round((this.originalTotal - this.effectiveDiscount) * 100) / 100);
            }

            this.syncPaymentAmounts();
        },

        onPaymentStatusChange() {
            if (this.paymentStatus === 'paid') {
                this.paymentAmount = this.finalTotal;
                this.balanceDue = 0;
            } else if (this.paymentStatus === 'unpaid') {
                this.paymentAmount = 0;
                this.balanceDue = this.finalTotal;
            } else if (this.paymentStatus === 'partial') {
                if (this.paymentAmount <= 0 || this.paymentAmount >= this.finalTotal) {
                    this.paymentAmount = Math.round(this.finalTotal * 0.25 * 100) / 100;
                }
                this.balanceDue = Math.max(0, Math.round((this.finalTotal - this.paymentAmount) * 100) / 100);
            }
        },

        onPaymentAmountInput() {
            if (this.paymentStatus === 'partial') {
                const paid = Math.min(this.finalTotal, Math.max(0, Number(this.paymentAmount) || 0));
                this.balanceDue = Math.max(0, Math.round((this.finalTotal - paid) * 100) / 100);
            }
        },

        syncPaymentAmounts() {
            if (this.paymentStatus === 'paid') {
                this.paymentAmount = this.finalTotal;
                this.balanceDue = 0;
            } else if (this.paymentStatus === 'unpaid') {
                this.paymentAmount = 0;
                this.balanceDue = this.finalTotal;
            } else {
                const paid = Math.min(this.finalTotal, Math.max(0, Number(this.paymentAmount) || 0));
                this.paymentAmount = paid;
                this.balanceDue = Math.max(0, Math.round((this.finalTotal - paid) * 100) / 100);
            }
        }
    };
}
</script>
@endpush
