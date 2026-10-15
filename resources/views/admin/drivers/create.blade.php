@extends('layouts.admin')

@section('page_title', 'Register Safari Captain')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-person-plus-fill text-primary"></i> Register Safari Captain
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Add a certified desert safari driver with RTA license credentials and vehicle assignment.</p>
        </div>
        <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            <i class="bi bi-arrow-left"></i> Back to Drivers
        </a>
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

    <form method="POST" action="{{ route('admin.drivers.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Primary Profile Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                1. Captain Identity & Credentials
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Captain Tariq Al-Baloushi" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">RTA / DTCM Safari License #</label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="e.g. RTA-SAF-10492" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile / WhatsApp Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. +971 50 245 6056" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">Used for 1-click WhatsApp run sheet dispatch and guest communication.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address (Optional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="captain@dunesdiscoverytourism.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Captain Photo / Avatar</label>
                    <input type="file" name="photo" accept="image/*" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer">
                    <span class="text-[10px] text-slate-400 mt-1 block">Displayed on guest digital tickets & vouchers for verification.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Default Assigned Vehicle</label>
                    <select name="default_vehicle_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="">-- No Default Vehicle (Assign on dispatch) --</option>
                        @foreach($vehicles as $veh)
                            <option value="{{ $veh->id }}" {{ old('default_vehicle_id') == $veh->id ? 'selected' : '' }}>
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Current Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active - On Duty (Available for dispatch)</option>
                        <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave / Rest Day</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Initial Rating (1.00 - 5.00)</label>
                    <input type="number" step="0.01" min="1" max="5" name="rating" value="{{ old('rating', '5.00') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Spoken Languages</label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['English', 'Arabic', 'Urdu', 'Hindi', 'Russian', 'Spanish', 'Italian', 'German', 'French'] as $lang)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="languages[]" value="{{ $lang }}" {{ in_array($lang, old('languages', ['English', 'Arabic'])) ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
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
                    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">UAE Visa Expiry Date</label>
                    <input type="date" name="visa_expiry_date" value="{{ old('visa_expiry_date') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="Contact name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Phone Number</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="+971..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 font-mono focus:bg-white focus:border-primary outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Internal Operational Notes</label>
                <textarea name="notes" rows="3" placeholder="Special skills, preferred desert sectors, performance remarks..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:bg-white focus:border-primary outline-none transition">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.drivers.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="bi bi-check-circle-fill me-1"></i> Register Safari Captain
            </button>
        </div>
    </form>
</div>
@endsection
