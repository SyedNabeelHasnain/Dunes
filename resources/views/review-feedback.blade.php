@extends('layouts.app')

@section('title', 'Your Feedback - Dunes Discovery Tourism')

@section('content')
<section class="py-5" style="background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%); min-height: 75vh;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-lg rounded-5 overflow-hidden p-4 p-md-5 bg-white text-center">
                    
                    <div class="bg-warning-subtle text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                        <i class="bi bi-chat-heart"></i>
                    </div>

                    <h2 class="h3 fw-800 text-dark mb-2">We value your honesty</h2>
                    <p class="text-muted small mb-4">
                        Dear {{ $booking->name }}, our goal is to deliver exceptional 5-star desert safari adventures. Please let us know what we could have done better on your {{ $booking->tour_name }}.
                    </p>

                    <form action="{{ route('review.feedback', $booking->reference) }}" method="POST" class="text-start">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Your Detailed Feedback</label>
                            <textarea name="feedback" rows="4" class="form-control rounded-4 border p-3 shadow-none" placeholder="Tell our operations team what we can improve (driver, food, camp, timing)..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-800 text-white shadow-sm">
                            Submit Private Feedback
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            Need immediate assistance? Speak directly with our guest relations team on <a href="https://wa.me/971501234567" target="_blank" class="text-success fw-bold text-decoration-none"><i class="bi bi-whatsapp"></i> WhatsApp</a>.
                        </small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection