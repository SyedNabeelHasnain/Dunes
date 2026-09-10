@if(isset($tour) && $tour->tiers && $tour->tiers->count() > 1)
<div class="card border-0 bg-white rounded-4 p-4 p-md-5 mb-5 shadow-sm border border-light" id="packageMatrixSection">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="badge bg-soft-primary text-primary px-3 py-1.5 rounded-pill fw-bold mb-2">
                <i class="bi bi-layers me-1"></i>Side-by-Side Comparison
            </div>
            <h2 class="h3 fw-bold text-dark mb-1">Package Tier Feature Breakdown</h2>
            <p class="text-muted small mb-0">Compare what is included in each package tier to pick the ideal safari experience for your party.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                <i class="bi bi-shield-check text-success me-1"></i>Instant Confirmation
            </span>
        </div>
    </div>

    <div class="table-responsive rounded-4 border overflow-hidden">
        <table class="table table-hover align-middle mb-0 text-center" style="font-size: 14px;">
            <thead class="bg-light">
                <tr>
                    <th class="text-start py-3 px-4 text-muted fw-bold text-uppercase small" style="width: 28%; min-width: 180px;">Feature / Inclusion</th>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <th class="py-3 px-3 {{ $tier->is_popular ? 'bg-soft-primary text-primary border-primary' : 'text-dark' }}" style="min-width: 140px;">
                        @if($tier->is_popular)
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small mb-1" style="font-size: 10px;">MOST POPULAR</span>
                        @endif
                        <div class="fw-bold fs-6">{{ $tier->name }}</div>
                        <div class="fs-5 fw-800 text-primary mt-1" data-aed="{{ $tier->pivot?->price ?? 0 }}">AED {{ number_format($tier->pivot?->price ?? 0) }}</div>
                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">per person</small>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-compass text-primary me-2"></i>Dune Bashing & Sandboarding
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1">35-45 Min (Red Dunes)</span>
                        @elseif(stripos($tier->name, 'premium') !== false)
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1">25-30 Min (High Dunes)</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1">15-20 Min</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-house-door text-primary me-2"></i>Camp Seating & Service Style
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="fw-bold text-primary"><i class="bi bi-star-fill text-warning me-1"></i>AC VIP Majlis & Table Waiter</span>
                        @elseif(stripos($tier->name, 'premium') !== false)
                            <span class="text-dark fw-semibold">Reserved Table Seating</span>
                        @else
                            <span class="text-muted">Standard Bedouin Carpet Seating</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-fire text-primary me-2"></i>Live Desert Entertainment
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3 text-success">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <small class="d-block text-muted" style="font-size: 11px;">Tanoura, Fire & Belly Dance</small>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-egg-fried text-primary me-2"></i>BBQ Buffet Dinner & Refreshments
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        @if(stripos($tier->name, 'vip') !== false)
                            <span class="fw-bold text-dark">Served at Table + VIP Buffet</span>
                        @else
                            <span class="text-dark">Deluxe Open Buffet (Veg & Non-Veg)</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-truck text-primary me-2"></i>Pickup & Drop-off Vehicle
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        @if(stripos($tier->name, 'private') !== false || stripos($tier->name, 'vip') !== false)
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Private 4x4 / Doorstep</span>
                        @else
                            <span class="text-muted">Shared 4x4 Land Cruiser</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>Quad Biking / Dune Buggy
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        @if(stripos($tier->name, 'quad') !== false || stripos($tier->name, 'buggy') !== false)
                            <span class="badge bg-success text-white fw-bold px-2.5 py-1">INCLUDED</span>
                        @else
                            <span class="text-muted small">Available as Add-on</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-start py-3 px-4 fw-semibold text-dark">
                        <i class="bi bi-heart text-primary me-2"></i>Camel Ride & Henna Painting
                    </td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3 text-success">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </td>
                    @endforeach
                </tr>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td class="text-start py-3 px-4 fw-bold text-muted small">Select Package:</td>
                    @foreach($tour->tiers->sortBy('priority') as $tier)
                    <td class="py-3 px-3">
                        <button type="button" class="btn {{ $tier->is_popular ? 'btn-desert-animated' : 'btn-outline-primary' }} btn-sm rounded-pill px-3 py-2 fw-bold w-100 shadow-sm" data-action="open-booking" data-tour="{{ $tour->id }}" data-tier="{{ $tier->id }}">
                            Select {{ $tier->name }}
                        </button>
                    </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif