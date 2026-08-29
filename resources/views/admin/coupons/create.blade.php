@extends('layouts.admin')

@section('page_title', 'Create Promo Code')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold mb-2">
        <i class="bi bi-chevron-left me-1"></i> Back to Coupons
    </a>
    <h2 class="h4 fw-800 text-dark mb-1">Create New Promotion Code</h2>
    <p class="text-muted small mb-0">Configure discount rules, date windows, usage limits, and tour applicability.</p>
</div>

<form action="{{ route('admin.coupons.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Left Main Column -->
        <div class="col-lg-8">
            
            <!-- Basic Details Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">1. Promotion Identity</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Promo Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" id="couponCode" class="form-control rounded-start-4 fw-800 text-uppercase font-monospace fs-5 @error('code') is-invalid @enderror" placeholder="e.g. SUMMER2026" value="{{ old('code') }}" required style="letter-spacing: 1.5px;">
                            <button type="button" class="btn btn-light border rounded-end-4 px-3" id="generateCodeBtn" title="Generate Random Code">
                                <i class="bi bi-magic me-1"></i> Generate
                            </button>
                        </div>
                        @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <small class="text-muted">Customers will enter this exact code at checkout.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Promotion Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-4 fw-bold @error('name') is-invalid @enderror" placeholder="e.g. Summer Desert Safari Special" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted text-uppercase">Internal Description (Optional)</label>
                    <textarea name="description" class="form-control rounded-4" rows="2" placeholder="Campaign notes, target audience, marketing channel...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Discount Calculation Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">2. Discount Structure</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Discount Type <span class="text-danger">*</span></label>
                        <select name="discount_type" id="discountTypeSelect" class="form-select rounded-4 fw-bold" required>
                            <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (% OFF)</option>
                            <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Flat Amount (AED OFF)</option>
                            <option value="per_person" {{ old('discount_type') === 'per_person' ? 'selected' : '' }}>Per Guest (AED / person)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase" id="discountValueLabel">Discount Value (% / AED) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="discount_value" class="form-control rounded-4 fw-800 fs-5 text-primary" placeholder="15" value="{{ old('discount_value') }}" required>
                        @error('discount_value')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Max Discount Cap (AED)</label>
                        <input type="number" step="0.01" min="0" name="max_discount" class="form-control rounded-4" placeholder="e.g. 100 (Optional)" value="{{ old('max_discount') }}">
                        <small class="text-muted">Maximum AED discount ceiling.</small>
                    </div>
                </div>

                <div class="alert alert-info bg-primary-subtle border-0 rounded-4 p-3 mb-0 text-dark small" id="discountTypeHint">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                    <span id="discountTypeHintText">Applies a percentage discount across the total cart. Example: 15% off AED 400 = AED 60 savings.</span>
                </div>
            </div>

            <!-- Applicability & Constraints Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">3. Targeting & Applicability</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Applicable Tour</label>
                        <select name="tour_id" class="form-select rounded-4">
                            <option value="">Sitewide (All Tours)</option>
                            @foreach($tours as $t)
                                <option value="{{ $t->id }}" {{ old('tour_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave empty to allow on all tours.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Applicable Package / Tier</label>
                        <select name="tier_id" class="form-select rounded-4">
                            <option value="">All Packages / Tiers</option>
                            @foreach($tiers as $tier)
                                <option value="{{ $tier->id }}" {{ old('tier_id') == $tier->id ? 'selected' : '' }}>{{ $tier->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Restrict to specific package (e.g. VIP only).</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Minimum Spend (AED)</label>
                        <input type="number" step="0.01" min="0" name="min_spend" class="form-control rounded-4" placeholder="0.00" value="{{ old('min_spend', 0) }}">
                        <small class="text-muted">Minimum cart subtotal required to apply.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Minimum Guest Count</label>
                        <input type="number" min="1" max="50" name="min_guests" class="form-control rounded-4" placeholder="1" value="{{ old('min_guests', 1) }}">
                        <small class="text-muted">Required guests count (useful for group/family promos).</small>
                    </div>
                </div>
            </div>

            <!-- Date Constraints Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">4. Schedule & Date Windows</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Booking Valid From</label>
                        <input type="datetime-local" name="valid_from" class="form-control rounded-4" value="{{ old('valid_from') }}">
                        <small class="text-muted">When customers can start redeeming.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Booking Valid Until (Expiry)</label>
                        <input type="datetime-local" name="valid_until" class="form-control rounded-4" value="{{ old('valid_until') }}">
                        <small class="text-muted">Cutoff time after which code expires.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tour Travel Date From</label>
                        <input type="date" name="tour_date_from" class="form-control rounded-4" value="{{ old('tour_date_from') }}">
                        <small class="text-muted">Tour must take place on or after this date.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tour Travel Date To</label>
                        <input type="date" name="tour_date_to" class="form-control rounded-4" value="{{ old('tour_date_to') }}">
                        <small class="text-muted">Tour must take place on or before this date.</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side Column -->
        <div class="col-lg-4">
            
            <!-- Status & Limits Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="text-primary fw-800 text-uppercase small mb-3">5. Usage Limits & Status</h6>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                    <select name="status" class="form-select rounded-4 fw-bold">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Live)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Draft / Paused)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Total Usage Limit</label>
                    <input type="number" min="1" name="usage_limit" class="form-control rounded-4" placeholder="Unlimited" value="{{ old('usage_limit') }}">
                    <small class="text-muted">Global maximum redemptions count.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Limit Per Customer Email</label>
                    <input type="number" min="1" name="usage_limit_per_user" class="form-control rounded-4 fw-bold" value="{{ old('usage_limit_per_user', 1) }}">
                    <small class="text-muted">Maximum times 1 email can redeem this code.</small>
                </div>

                <hr class="my-3">

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="first_time_only" id="firstTimeSwitch" value="1" {{ old('first_time_only') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="firstTimeSwitch">First-Time Guests Only</label>
                    <small class="text-muted d-block" style="font-size:0.75rem;">Restricts redemption to new customer emails.</small>
                </div>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="featuredSwitch" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="featuredSwitch">Featured Promotion</label>
                    <small class="text-muted d-block" style="font-size:0.75rem;">Can be surfaced in public marketing banners.</small>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="card card-modern bg-white border-0 shadow-sm rounded-4 p-4">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-800 fs-6 shadow-sm mb-2">
                    <i class="bi bi-check-lg me-2"></i> Save & Publish Promo
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-light rounded-pill w-100 py-2 fw-bold text-muted">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate Random Coupon Code
    document.getElementById('generateCodeBtn').addEventListener('click', function() {
        const prefixes = ['DUNES', 'DESERT', 'VIP', 'SPECIAL', 'SAFARI', 'DUBAI'];
        const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const randomNum = Math.floor(100 + Math.random() * 900);
        document.getElementById('couponCode').value = `${randomPrefix}${randomNum}`;
    });

    // Dynamic Discount Type Hints
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
