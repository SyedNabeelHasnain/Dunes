@extends('layouts.admin')

@section('page_title', 'Edit Safari Captain: ' . $driver->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-pencil-square text-primary"></i> Edit Safari Captain
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Update driver profile, vehicle assignment, status, and credentials.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.drivers.show', $driver->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i class="bi bi-eye"></i> View Dossier
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
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

    <form method="POST" action="{{ route('admin.drivers.update', $driver->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Primary Profile Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                1. Captain Identity & Credentials
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $driver->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">RTA / DTCM Safari License #</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile / WhatsApp Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $driver->phone) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $driver->email) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Update Photo</label>
                    <div class="flex items-center gap-3 mb-2">
                        @if($driver->photo)
                            <img src="{{ asset($driver->photo) }}" alt="{{ $driver->name }}" class="w-12 h-12 rounded-full object-cover border border-slate-200">
                        @endif
                        <input type="file" name="photo" accept="image/*" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Default Assigned Vehicle</label>
                    <select name="default_vehicle_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="">-- No Default Vehicle --</option>
                        @foreach($vehicles as $veh)
                            <option value="{{ $veh->id }}" {{ old('default_vehicle_id', $driver->default_vehicle_id) == $veh->id ? 'selected' : '' }}>
                                {{ $veh->display_name }} (Capacity: {{ $veh->passenger_capacity }} guests)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Operations & Languages Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                2. Operational Status & Languages
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="active" {{ old('status', $driver->status) === 'active' ? 'selected' : '' }}>Active - On Duty</option>
                        <option value="on_leave" {{ old('status', $driver->status) === 'on_leave' ? 'selected' : '' }}>On Leave / Rest Day</option>
                        <option value="inactive" {{ old('status', $driver->status) === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Guest Rating (1.00 - 5.00)</label>
                    <input type="number" step="0.01" min="1" max="5" name="rating" value="{{ old('rating', $driver->rating) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Spoken Languages</label>
                @php $driverLangs = $driver->languages ?: ['English', 'Arabic']; @endphp
                <div class="flex flex-wrap gap-3">
                    @foreach(['English', 'Arabic', 'Urdu', 'Hindi', 'Russian', 'Spanish', 'Italian', 'German', 'French'] as $lang)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="languages[]" value="{{ $lang }}" {{ in_array($lang, old('languages', $driverLangs)) ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                        <span>{{ $lang }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Compliance & Emergency Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                3. Compliance & Emergency Contacts
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">License Expiry Date</label>
                    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date', $driver->license_expiry_date?->format('Y-m-d')) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">UAE Visa Expiry Date</label>
                    <input type="date" name="visa_expiry_date" value="{{ old('visa_expiry_date', $driver->visa_expiry_date?->format('Y-m-d')) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Phone Number</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Internal Operational Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">{{ old('notes', $driver->notes) }}</textarea>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.drivers.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="bi bi-check-circle-fill me-1"></i> Update Safari Captain
            </button>
        </div>
    </form>
</div>
@endsection
