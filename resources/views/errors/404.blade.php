@extends('layouts.app')

@php
    $pageTitle = 'Page Not Found (404) | Dunes Discovery Tourism';
    $pageDesc = 'The page you are looking for does not exist or has been moved. Explore our Dubai desert safari tours or return home.';
@endphp

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background: linear-gradient(135deg, #FFF9F5 0%, #FFF0E6 100%);">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4 p-md-5 bg-white bg-opacity-75 backdrop-blur">
                    <div class="mb-4 text-primary">
                        <i class="bi bi-compass-fill display-1" style="font-size: 5rem; text-shadow: 0 10px 20px rgba(255, 107, 0, 0.2);"></i>
                    </div>
                    <h1 class="display-3 fw-extrabold text-dark mb-2">404</h1>
                    <h2 class="h4 fw-bold text-secondary mb-3">Lost in the Dunes?</h2>
                    <p class="text-muted mb-4 lead" style="font-size: 1rem;">
                        The page or tour experience you are searching for might have been relocated, renamed, or is temporarily unavailable.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-desert-animated px-4 py-3 rounded-pill fw-bold border-0 shadow-sm">
                            <i class="bi bi-house-door-fill me-2"></i>Return to Homepage
                        </a>
                        <a href="{{ route('tours.index') }}" class="btn btn-outline-primary px-4 py-3 rounded-pill fw-bold border-2">
                            <i class="bi bi-grid-fill me-2"></i>Explore Desert Tours
                        </a>
                    </div>
                    <div class="mt-4 pt-3 border-top text-muted small">
                        Need immediate assistance? <a href="https://wa.me/971502456056?text=Hi%20Dunes%20Team%2C%20I%20need%20help%20finding%20a%20tour" target="_blank" class="text-primary fw-bold text-decoration-none"><i class="bi bi-whatsapp me-1"></i>Chat on WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
