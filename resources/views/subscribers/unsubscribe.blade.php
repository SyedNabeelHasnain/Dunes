@extends('layouts.app')

@section('page_title', 'Manage Email Subscription | ' . ($settings['site_name'] ?? 'Dunes Discovery Tourism'))
@section('meta_description', 'Manage your newsletter preferences and unsubscribe options.')

@section('content')
<div class="container py-5 my-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header border-0 bg-dark text-white p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-20 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-envelope-slash-fill fs-3"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Email Subscription Preferences</h1>
                    <p class="text-white-50 small mb-0">{{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }}</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if(isset($message) && !$subscriber)
                        <div class="alert alert-warning border-0 rounded-3 text-center mb-0">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2 text-warning"></i>
                            <p class="fw-bold mb-1">{{ $message }}</p>
                            <small class="text-muted">If you need assistance managing your email, please <a href="{{ route('contact') }}" class="text-decoration-underline text-dark">contact support</a>.</small>
                        </div>
                    @elseif(!empty($completed))
                        <div class="text-center py-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-check-lg fs-3"></i>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-2">You Have Been Unsubscribed</h2>
                            <p class="text-muted small mb-4">You will no longer receive marketing promotions, travel tips, or newsletters from {{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }} at <strong>{{ $subscriber->email }}</strong>.</p>
                            
                            <div class="p-3 bg-light rounded-3 text-muted extra-small mb-4 text-start" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle me-1 text-primary"></i> Note: You will still receive essential transactional notifications regarding any confirmed bookings or customer service inquiries.
                            </div>

                            <a href="{{ route('home') }}" class="btn btn-desert-animated btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm">
                                Return to Homepage
                            </a>
                        </div>
                    @elseif(!empty($alreadyUnsubscribed))
                        <div class="text-center py-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-circle mb-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-bell-slash fs-3"></i>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-2">Already Unsubscribed</h2>
                            <p class="text-muted small mb-4">The email address <strong>{{ $subscriber->email }}</strong> is already unsubscribed from our active marketing lists.</p>
                            <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm rounded-pill px-4 py-2 fw-bold">
                                Return to Homepage
                            </a>
                        </div>
                    @else
                        <p class="text-muted small mb-4">
                            We are sorry to see you go! Are you sure you want to unsubscribe <strong>{{ $subscriber->email }}</strong> from {{ $settings['site_name'] ?? 'Dunes Discovery Tourism' }} newsletters and exclusive deals?
                        </p>

                        <form action="{{ url('/unsubscribe/' . $token) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">Optional: Why are you unsubscribing?</label>
                                <select name="reason" class="form-select rounded-3 small">
                                    <option value="I receive emails too frequently">I receive emails too frequently</option>
                                    <option value="Content is no longer relevant to me">Content is no longer relevant to me</option>
                                    <option value="I never signed up for this newsletter">I never signed up for this newsletter</option>
                                    <option value="I booked elsewhere / trip completed">I booked elsewhere / trip completed</option>
                                    <option value="Other reason">Other reason</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <input type="text" name="other_reason" class="form-control rounded-3 small" placeholder="Tell us how we can improve (optional)..." maxlength="200">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger rounded-pill py-2.5 fw-bold shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i> Confirm Unsubscribe
                                </button>
                                <a href="{{ route('home') }}" class="btn btn-light rounded-pill py-2.5 text-muted small">
                                    Nevermind, Keep Me Subscribed
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
