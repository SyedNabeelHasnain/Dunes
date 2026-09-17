@extends('layouts.app')

@push('styles')
<style>
.review-page {
    background: linear-gradient(180deg, #0F172A 0%, #1E293B 100%);
    min-height: 85vh;
    padding: 120px 0 80px;
    color: #F8FAFC;
}
.review-card {
    background: rgba(30, 41, 59, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(16px);
}
.star-rating-box {
    display: flex;
    justify-content: center;
    gap: 12px;
    font-size: 2.4rem;
    cursor: pointer;
}
.star-rating-box .bi-star-fill,
.star-rating-box .bi-star {
    transition: transform 0.15s ease, color 0.15s ease;
    color: #475569;
}
.star-rating-box .bi-star-fill.active {
    color: #F59E0B;
    transform: scale(1.1);
}
.photo-dropzone {
    border: 2px dashed rgba(246, 144, 68, 0.4);
    border-radius: 16px;
    padding: 28px 20px;
    text-align: center;
    background: rgba(15, 23, 42, 0.6);
    cursor: pointer;
    transition: all 0.2s ease;
}
.photo-dropzone:hover {
    border-color: #F69044;
    background: rgba(15, 23, 42, 0.9);
}
.photo-preview-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.photo-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.photo-preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    cursor: pointer;
}
.google-boost-card {
    background: linear-gradient(135deg, rgba(66, 133, 244, 0.12) 0%, rgba(52, 168, 83, 0.12) 100%);
    border: 1px solid rgba(66, 133, 244, 0.3);
    border-radius: 18px;
    padding: 24px;
}
</style>
@endpush

@section('content')
<div class="review-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">

                @if(session('review_submitted'))
                    <!-- ── SUCCESS THANK-YOU SCREEN ───────────────────────────── -->
                    <div class="review-card p-4 p-md-5 text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 76px; height: 76px; background: rgba(34, 197, 94, 0.15); color: #22C55E;">
                            <i class="bi bi-check-circle-fill display-5"></i>
                        </div>

                        <h2 class="h3 fw-bold text-white mb-2">Thank You, {{ $booking->name }}!</h2>
                        <p class="text-white-50 mb-4" style="line-height: 1.6;">
                            Your verified review and safari photos have been received. We are thrilled you chose Dunes Discovery Tourism for your Dubai desert adventure!
                        </p>

                        @if(session('submitted_rating') >= 4 && !empty($googleReviewUrl))
                            <div class="google-boost-card text-start mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ asset('images/Google-G.avif') }}" alt="Google" style="width: 24px; height: 24px; object-fit: contain;">
                                    <h5 class="fw-bold text-white mb-0">Help Fellow Travelers on Google!</h5>
                                </div>
                                <p class="text-white-50 small mb-3">
                                    Your 5-star review helps tourists from around the world choose safe, DTCM-licensed desert operators. Would you take 10 seconds to share your feedback on Google Maps?
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ $googleReviewUrl }}" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow">
                                        <i class="bi bi-google me-1.5"></i> Post on Google Reviews
                                    </a>
                                    <a href="{{ route('home') }}" class="btn btn-outline-light rounded-pill px-4 py-2.5">
                                        Back to Home
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold">
                                Return to Homepage
                            </a>
                        @endif
                    </div>
                @else
                    <!-- ── SUBMISSION FORM ────────────────────────────────────── -->
                    <div class="review-card p-4 p-md-5">

                        <!-- Tour Verified Badge Header -->
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-white border-opacity-10 flex-wrap gap-2">
                            <div>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 small fw-bold mb-1">
                                    <i class="bi bi-patch-check-fill me-1"></i> Verified Guest Experience
                                </span>
                                <h4 class="h5 fw-bold text-white mb-0">{{ $booking->tour->name ?? $booking->tour_name ?? 'Dubai Desert Safari' }}</h4>
                            </div>
                            <div class="text-md-end">
                                <small class="text-white-50 d-block" style="font-size: 0.75rem;">BOOKING REF</small>
                                <span class="badge bg-dark border border-secondary text-warning font-monospace">{{ $booking->reference }}</span>
                            </div>
                        </div>

                        <form action="{{ route('review.submit', $booking->reference) }}" method="POST" enctype="multipart/form-data" id="reviewSubmitForm">
                            @csrf

                            <!-- Rating Picker -->
                            <div class="text-center mb-4">
                                <label class="form-label text-white-50 small text-uppercase fw-bold mb-2">How was your overall experience?</label>
                                <div class="star-rating-box" id="starRatingBox">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star-fill {{ $i <= $score ? 'active' : '' }}" data-val="{{ $i }}"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="{{ $score }}">
                                <div class="text-warning small fw-bold mt-2" id="ratingLabel">
                                    {{ $score == 5 ? '5 Stars - Outstanding Desert Safari!' : ($score == 4 ? '4 Stars - Very Good Experience' : 'Tell us how we can improve') }}
                                </div>
                            </div>

                            @if(!$booking->id)
                            <!-- Guest Name -->
                            <div class="mb-3">
                                <label for="guestName" class="form-label text-white small fw-bold">Your Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" id="guestName" required class="form-control bg-dark text-white border-secondary rounded-3 py-2.5 shadow-none" placeholder="e.g., Sarah Jenkins" value="{{ old('guest_name') }}">
                            </div>
                            @endif

                            <!-- Review Title -->
                            <div class="mb-3">
                                <label for="reviewTitle" class="form-label text-white small fw-bold">Headline / Short Title</label>
                                <input type="text" name="review_title" id="reviewTitle" class="form-control bg-dark text-white border-secondary rounded-3 py-2.5 shadow-none" placeholder="e.g., Unforgettable sunset & high dune bashing!" value="{{ old('review_title') }}">
                            </div>

                            <!-- Review Text -->
                            <div class="mb-4">
                                <label for="reviewText" class="form-label text-white small fw-bold">Your Review & Story <span class="text-danger">*</span></label>
                                <textarea name="review_text" id="reviewText" rows="4" required class="form-control bg-dark text-white border-secondary rounded-3 p-3 shadow-none" placeholder="Tell future guests about your safari captain, the red dunes drive, live shows, and food...">{{ old('review_text') }}</textarea>
                                <div class="form-text text-white-50 small">Minimum 10 characters. Authentic guest feedback helps travelers make confident plans.</div>
                            </div>

                            <!-- Photo Upload Dropzone -->
                            <div class="mb-4">
                                <label class="form-label text-white small fw-bold">Upload Safari Photos <span class="text-white-50 fw-normal">(Optional, up to 4 photos)</span></label>
                                <div class="photo-dropzone" id="photoDropzone" onclick="document.getElementById('photoInput').click()">
                                    <i class="bi bi-camera-fill text-warning fs-2 d-block mb-1"></i>
                                    <span class="text-white fw-bold small d-block">Click or Drop Photos Here</span>
                                    <small class="text-white-50" style="font-size: 0.75rem;">JPG, PNG, WEBP up to 5MB each (Dune photos, buggy action, sunset selfies)</small>
                                    <input type="file" name="photos[]" id="photoInput" class="d-none" multiple accept="image/jpeg,image/png,image/webp,image/avif">
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-3" id="photoPreviewArea"></div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-desert-animated btn-lg w-100 rounded-pill py-3 fw-bold shadow">
                                <i class="bi bi-send-fill me-2"></i> Submit My Review & Photos
                            </button>
                        </form>

                        <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-center">
                            <small class="text-white-50" style="font-size: 0.75rem;">
                                <i class="bi bi-shield-lock-fill text-success me-1"></i> Licensed by Dubai Economy & Tourism (DET License #1430583) • 100% Verified Review System
                            </small>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const starBox = document.getElementById('starRatingBox');
    const ratingInput = document.getElementById('ratingInput');
    const ratingLabel = document.getElementById('ratingLabel');

    const labels = {
        1: '1 Star - Disappointing, please tell us how to improve',
        2: '2 Stars - Below expectations',
        3: '3 Stars - Average safari experience',
        4: '4 Stars - Very good adventure & service',
        5: '5 Stars - Outstanding! Highly recommended'
    };

    if (starBox && ratingInput) {
        const stars = starBox.querySelectorAll('i');

        function setRating(val) {
            ratingInput.value = val;
            stars.forEach((s, idx) => {
                if (idx < val) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
            if (ratingLabel) {
                ratingLabel.textContent = labels[val] || (val + ' Stars');
            }
        }

        stars.forEach(s => {
            s.addEventListener('click', function () {
                const val = parseInt(this.dataset.val);
                setRating(val);
            });
            s.addEventListener('mouseenter', function () {
                const val = parseInt(this.dataset.val);
                stars.forEach((star, idx) => {
                    if (idx < val) {
                        star.style.color = '#F59E0B';
                    } else {
                        star.style.color = '#475569';
                    }
                });
            });
        });

        starBox.addEventListener('mouseleave', function () {
            const current = parseInt(ratingInput.value) || 5;
            stars.forEach((star, idx) => {
                star.style.color = '';
                if (idx < current) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        });
    }

    // Photo Preview Logic
    const photoInput = document.getElementById('photoInput');
    const previewArea = document.getElementById('photoPreviewArea');

    if (photoInput && previewArea) {
        photoInput.addEventListener('change', function () {
            previewArea.innerHTML = '';
            const files = Array.from(this.files).slice(0, 4);

            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'photo-preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Safari photo preview">
                            <div class="photo-preview-remove" title="Remove">&times;</div>
                        `;
                        div.querySelector('.photo-preview-remove').addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            div.remove();
                        });
                        previewArea.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }
});
</script>
@endpush
