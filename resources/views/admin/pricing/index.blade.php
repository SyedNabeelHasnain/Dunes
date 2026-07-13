@extends('layouts.admin')

@section('page_title', 'Pricing Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Pricing Matrix Manager</h2>
</div>

<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <h5 class="fw-800 mb-0 text-dark">Bulk Pricing Matrix</h5>
        <div class="text-muted small">Update pricing across all active tours and packages in one place.</div>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.pricing.update') }}" method="POST">
            @csrf

            <div class="table-responsive mb-4">
                <table class="table align-middle table-bordered text-center no-datatable" style="min-width: 900px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-3" style="width: 250px;">Tour / Activity Name</th>
                            @foreach($tiers as $tier)
                                <th>
                                    <div class="fw-bold text-dark">{{ $tier->display_name }}</div>
                                    <span class="badge bg-light text-muted border rounded-pill small">{{ $tier->slug }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tours as $tour)
                        <tr>
                            <td class="text-start ps-3">
                                <div class="fw-bold text-dark">{{ $tour->name }}</div>
                                <div class="text-muted small">{{ $tour->category ? $tour->category->name : '' }}</div>
                            </td>
                            @foreach($tiers as $tier)
                                @php
                                    $pivot = $tour->tiers->firstWhere('id', $tier->id)->pivot ?? null;
                                @endphp
                                <td style="background-color: {{ $pivot ? '#f8fff8' : '#fffcfc' }};">
                                    <div class="d-flex flex-column gap-2 p-1">
                                        <!-- Active Price -->
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted small" style="font-size: 0.7rem; width: 45px;">AED</span>
                                            <input type="number" name="pricing[{{ $tour->id }}][{{ $tier->id }}][price]" step="0.01" class="form-control text-center fw-bold text-dark" value="{{ $pivot ? $pivot->price : '' }}" placeholder="Price">
                                        </div>
                                        
                                        <!-- Old/Strike Price -->
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted small" style="font-size: 0.7rem; width: 45px;">Original</span>
                                            <input type="number" name="pricing[{{ $tour->id }}][{{ $tier->id }}][old_price]" step="0.01" class="form-control text-center" value="{{ $pivot ? $pivot->old_price : '' }}" placeholder="Old">
                                        </div>

                                        <!-- Price Type -->
                                        <select name="pricing[{{ $tour->id }}][{{ $tier->id }}][price_type]" class="form-select form-select-sm" style="font-size: 0.75rem;">
                                            <option value="per person" {{ $pivot && $pivot->price_type === 'per person' ? 'selected' : '' }}>Per Person</option>
                                            <option value="per group" {{ $pivot && $pivot->price_type === 'per group' ? 'selected' : '' }}>Per Group</option>
                                            <option value="per car" {{ $pivot && $pivot->price_type === 'per car' ? 'selected' : '' }}>Per Car</option>
                                        </select>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info border-0 rounded-4 p-3 d-flex gap-3 mb-4">
                <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                <div class="small">
                    <strong>Note:</strong> Pricing columns with empty price values will not be updated or attached. To deactivate a tier package for a tour, please manage that directly from the individual tour's edit view.
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Update Inventory Pricing Matrix</button>
            </div>
        </form>
    </div>
</div>
@endsection
