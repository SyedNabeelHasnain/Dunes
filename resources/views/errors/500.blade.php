@extends('layouts.app')

@php
    $pageTitle = 'Server Error (500) | Dunes Discovery Tourism';
    $pageDesc = 'Something unexpected occurred on our server. Please try again or contact our support team.';
@endphp

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background: linear-gradient(135deg, #FFF9F5 0%, #FFF0E6 100%);">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4 p-md-5 bg-white bg-opacity-75 backdrop-blur">
                    <div class="mb-4 text-warning">
                        <i class="bi bi-exclamation-triangle-fill display-1" style="font-size: 5rem; text-shadow: 0 10px 20px rgba(255, 150, 0, 0.2);"></i>
                    </div>
                    <h1 class="display-4 fw-extrabold text-dark mb-2">500</h1>
                    <h2 class="h4 fw-bold text-secondary mb-3">Temporary Server Hitch</h2>
                    <p class="text-muted mb-4 lead" style="font-size: 1rem;">
                        Our system encountered a brief hiccup while processing your request. Please refresh the page or try again in a few moments.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="javascript:location.reload()" class="btn btn-desert-animated px-4 py-3 rounded-pill fw-bold border-0 shadow-sm">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reload Page
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary px-4 py-3 rounded-pill fw-bold border-2">
                            <i class="bi bi-house-door-fill me-2"></i>Return Home
                        </a>
                    </div>
                    <div class="mt-4 pt-3 border-top text-muted small">
                        If the issue persists, feel free to contact us via <a href="https://wa.me/971502456056?text=Hi%20Dunes%20Team%2C%20I%20encountered%20a%20500%20server%20error" target="_blank" class="text-primary fw-bold text-decoration-none"><i class="bi bi-whatsapp me-1"></i>WhatsApp Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
