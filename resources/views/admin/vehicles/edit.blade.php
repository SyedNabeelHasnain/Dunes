@extends('layouts.admin')

@section('page_title', 'Edit Vehicle: ' . $vehicle->display_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-pencil-square text-primary"></i> Edit Fleet Vehicle
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Update specifications, driver assignment, DTCM permits, and maintenance status.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-eye"></i> View Dossier
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
        <strong class="font-bold block mb-1">Please fix the following errors:</strong>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.vehicles.update', $vehicle->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Vehicle Specs -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                1. Vehicle Specifications & Registration
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Model & Trim <span class="text-rose-500">*</span></label>
                    <input type="text" name="model_name" value="{{ old('model_name', $vehicle->model_name) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Model Year <span class="text-rose-500">*</span></label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" min="2015" max="{{ date('Y') + 1 }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Plate Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Plate Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="plate_code" value="{{ old('plate_code', $vehicle->plate_code) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 font-mono uppercase focus:bg-white focus:border-primary outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Emirate / City <span class="text-rose-500">*</span></label>
                        <input type="text" name="plate_city" value="{{ old('plate_city', $vehicle->plate_city) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Passenger Capacity (Seats) <span class="text-rose-500">*</span></label>
                    <input type="number" name="passenger_capacity" value="{{ old('passenger_capacity', $vehicle->passenger_capacity) }}" min="1" max="50" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-primary focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Exterior Color <span class="text-rose-500">*</span></label>
                    <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>
        </div>

        <!-- Operational Status & Assigned Driver -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                2. Operational Assignment & Status
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fleet Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="active" {{ old('status', $vehicle->status) === 'active' ? 'selected' : '' }}>Active - Ready for Tours</option>
                        <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>In Maintenance / Workshop Service</option>
                        <option value="inactive" {{ old('status', $vehicle->status) === 'inactive' ? 'selected' : '' }}>Inactive / Decommissioned</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Primary Safari Captain</label>
                    <select name="default_driver_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="">-- No Primary Driver (Shared Pool) --</option>
                        @foreach($drivers as $drv)
                            <option value="{{ $drv->id }}" {{ old('default_driver_id', $vehicle->default_driver_id) == $drv->id ? 'selected' : '' }}>
                                {{ $drv->name }} ({{ $drv->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Safety & Desert Equipment</label>
                @php $vehFeatures = $vehicle->features ?: []; @endphp
                <div class="flex flex-wrap gap-3">
                    @foreach(['4x4 Desert Spec', 'Roll Cage Certified', 'Dual Air Conditioning', 'Garmin GPS Satellite Navigation', 'First Aid Trauma Kit', 'High-Output Tyre Deflators', 'Sand Recovery Trax', 'Wi-Fi Hotspot'] as $feat)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="features[]" value="{{ $feat }}" {{ in_array($feat, old('features', $vehFeatures)) ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                        <span>{{ $feat }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Compliance & Regulatory Permits -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                3. DTCM Permits & Compliance
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">DTCM Safari Permit #</label>
                    <input type="text" name="dtcm_permit_number" value="{{ old('dtcm_permit_number', $vehicle->dtcm_permit_number) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mulkiya (Registration) Expiry</label>
                    <input type="date" name="mulkiya_expiry_date" value="{{ old('mulkiya_expiry_date', $vehicle->mulkiya_expiry_date?->format('Y-m-d')) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Commercial Insurance Expiry</label>
                    <input type="date" name="insurance_expiry_date" value="{{ old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d')) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Fleet Maintenance Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">{{ old('notes', $vehicle->notes) }}</textarea>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.vehicles.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="bi bi-check-circle-fill me-1"></i> Update Vehicle
            </button>
        </div>
    </form>
</div>
@endsection
