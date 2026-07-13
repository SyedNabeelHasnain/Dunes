@extends('layouts.app')

@section('content')
<section class="section py-5" style="margin-top: 5vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
                    <h2 class="fw-800 mb-2 text-danger">Payment Cancelled</h2>
                    <p class="text-muted">Your payment was not completed. You can retry booking or contact our team for assistance.</p>
                    @if($booking)
                        <div class="p-3 bg-light rounded-4 mt-3 border">
                            <div class="text-muted small fw-bold">Reference</div>
                            <div class="fw-800 text-dark">#{{ $booking->reference }}</div>
                        </div>
                    @endif
                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('tours.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Browse Tours</a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
