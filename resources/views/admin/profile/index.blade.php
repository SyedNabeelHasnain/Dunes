@extends('layouts.admin')

@section('page_title', 'Admin Account & Security Settings')

@section('content')
<div class="mb-4">
    <h4 class="fw-800 text-dark mb-1">Admin Profile & Security</h4>
    <p class="text-muted small mb-0">Update your administrator account name, registered email, and security password.</p>
</div>

@if(session('status') === 'profile-updated')
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>Profile details updated successfully!
    </div>
@endif

@if(session('status') === 'password-updated')
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="bi bi-shield-check me-2"></i>Password changed successfully!
    </div>
@endif

<div class="row g-4 mb-5">
    <!-- Profile Info Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-800 text-dark mb-4"><i class="bi bi-person-badge text-primary me-2"></i>Account Credentials</h5>
            
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label fw-bold text-dark">Administrator Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required style="height: 48px; border-radius: 8px;">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label fw-bold text-dark">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required style="height: 48px; border-radius: 8px;">
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">Update Account Info</button>
            </form>
        </div>
    </div>

    <!-- Password Change Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-800 text-dark mb-4"><i class="bi bi-key-fill text-warning me-2"></i>Change Security Password</h5>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-bold text-dark">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required style="height: 48px; border-radius: 8px;">
                    @error('current_password', 'updatePassword')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold text-dark">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required style="height: 48px; border-radius: 8px;">
                    @error('password', 'updatePassword')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-bold text-dark">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required style="height: 48px; border-radius: 8px;">
                </div>

                <button type="submit" class="btn btn-dark rounded-pill px-5 py-2 fw-bold">Update Security Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
