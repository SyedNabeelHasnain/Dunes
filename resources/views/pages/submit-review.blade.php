@extends('layouts.app')

@section('content')
<div class="py-16 sm:py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 min-h-[85vh] text-slate-100 flex items-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 w-full">

        @if(session('review_submitted'))
            <!-- ── SUCCESS THANK-YOU SCREEN ───────────────────────────── -->
            <div class="bg-slate-900/90 border border-white/10 rounded-3xl p-6 sm:p-10 text-center shadow-2xl backdrop-blur-xl">
                <div class="w-18 h-18 rounded-full bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-4xl mb-4 mx-auto">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">Thank You, {{ $booking->name }}!</h2>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                    Your verified review and safari photos have been received. We are thrilled you chose Dunes Discovery Tourism for your Dubai desert adventure!
                </p>

                @if(session('submitted_rating') >= 4 && !empty($googleReviewUrl))
                    <div class="bg-gradient-to-br from-blue-500/10 to-emerald-500/10 border border-blue-500/30 rounded-2xl p-6 text-left mb-6">
                        <div class="flex items-center gap-2.5 mb-2">
                            <img src="{{ asset('images/Google-G.avif') }}" alt="Google" class="w-6 h-6 object-contain">
                            <h3 class="font-bold text-white text-base mb-0">Help Fellow Travelers on Google!</h3>
                        </div>
                        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-4">
                            Your 5-star review helps tourists from around the world choose safe, DTCM-licensed desert operators. Would you take 10 seconds to share your feedback on Google Maps?
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $googleReviewUrl }}" target="_blank" rel="noopener" class="btn-desert-animated text-xs sm:text-sm font-bold rounded-full px-5 py-2.5 text-white shadow-md inline-flex items-center gap-2">
                                <i class="bi bi-google"></i> Post on Google Reviews
                            </a>
                            <a href="{{ route('home') }}" class="border border-white/20 hover:bg-white/10 text-white text-xs sm:text-sm font-bold rounded-full px-5 py-2.5 transition-colors inline-flex items-center">
                                Back to Home
                            </a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('home') }}" class="btn-desert-animated text-sm sm:text-base font-bold rounded-full px-8 py-3.5 text-white inline-flex items-center gap-2 shadow-lg">
                        <i class="bi bi-house-door-fill"></i> Return to Homepage
                    </a>
                @endif
            </div>
        @else
            <!-- ── SUBMISSION FORM ────────────────────────────────────── -->
            <div class="bg-slate-900/95 border border-white/10 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">

                <!-- Tour Verified Badge Header -->
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-white/10 flex-wrap gap-3">
                    <div>
                        <span class="inline-flex items-center gap-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 rounded-full px-3 py-1 text-xs font-bold mb-1.5">
                            <i class="bi bi-patch-check-fill"></i> Verified Guest Experience
                        </span>
                        <h2 class="text-lg sm:text-xl font-bold text-white mb-0">{{ $booking->tour->name ?? $booking->tour_name ?? 'Dubai Desert Safari' }}</h2>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-white/40 block text-[10px] font-bold uppercase tracking-wider">Booking Ref</span>
                        <span class="bg-slate-950 border border-slate-700 text-amber-400 font-mono text-xs px-2.5 py-1 rounded-lg inline-block">#{{ $booking->reference }}</span>
                    </div>
                </div>

                <form action="{{ route('review.submit', $booking->reference) }}" method="POST" enctype="multipart/form-data" id="reviewSubmitForm">
                    @csrf

                    <!-- Rating Picker -->
                    <div class="text-center mb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2.5">How was your overall experience?</label>
                        <div class="flex justify-center items-center gap-3 text-3xl sm:text-4xl cursor-pointer" id="starRatingBox">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star-fill text-slate-700 hover:scale-110 transition-all {{ $i <= $score ? 'text-amber-400' : '' }}" data-val="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="{{ $score }}">
                        <div class="text-amber-400 text-xs sm:text-sm font-bold mt-2.5" id="ratingLabel">
                            {{ $score == 5 ? '5 Stars - Outstanding Desert Safari!' : ($score == 4 ? '4 Stars - Very Good Experience' : 'Tell us how we can improve') }}
                        </div>
                    </div>

                    @if(!$booking->id)
                    <!-- Guest Name -->
                    <div class="mb-4">
                        <label for="guestName" class="block text-white text-xs sm:text-sm font-bold mb-1.5">Your Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="guest_name" id="guestName" required class="w-full bg-slate-950 border border-slate-700 focus:border-primary text-white rounded-xl px-4 py-3 text-sm focus:outline-none transition-colors" placeholder="e.g., Sarah Jenkins" value="{{ old('guest_name') }}">
                    </div>
                    @endif

                    <!-- Review Title -->
                    <div class="mb-4">
                        <label for="reviewTitle" class="block text-white text-xs sm:text-sm font-bold mb-1.5">Headline / Short Title</label>
                        <input type="text" name="review_title" id="reviewTitle" class="w-full bg-slate-950 border border-slate-700 focus:border-primary text-white rounded-xl px-4 py-3 text-sm focus:outline-none transition-colors" placeholder="e.g., Unforgettable sunset & high dune bashing!" value="{{ old('review_title') }}">
                    </div>

                    <!-- Review Text -->
                    <div class="mb-6">
                        <label for="reviewText" class="block text-white text-xs sm:text-sm font-bold mb-1.5">Your Review & Story <span class="text-rose-500">*</span></label>
                        <textarea name="review_text" id="reviewText" rows="4" required class="w-full bg-slate-950 border border-slate-700 focus:border-primary text-white rounded-xl p-4 text-sm focus:outline-none transition-colors leading-relaxed" placeholder="Tell future guests about your safari captain, the red dunes drive, live shows, and food...">{{ old('review_text') }}</textarea>
                        <span class="text-slate-400 text-[11px] block mt-1">Minimum 10 characters. Authentic guest feedback helps travelers make confident plans.</span>
                    </div>

                    <!-- Photo Upload Dropzone -->
                    <div class="mb-8">
                        <label class="block text-white text-xs sm:text-sm font-bold mb-2">Upload Safari Photos <span class="text-slate-400 font-normal">(Optional, up to 4 photos)</span></label>
                        <div class="border-2 border-dashed border-amber-500/40 hover:border-primary rounded-2xl p-6 text-center bg-slate-950/60 hover:bg-slate-950/90 cursor-pointer transition-all" id="photoDropzone" onclick="document.getElementById('photoInput').click()">
                            <i class="bi bi-camera-fill text-amber-400 text-3xl block mb-2"></i>
                            <span class="text-white font-bold text-xs sm:text-sm block">Click or Drop Photos Here</span>
                            <small class="text-slate-400 text-[11px] block mt-0.5">JPG, PNG, WEBP up to 5MB each (Dune photos, buggy action, sunset selfies)</small>
                            <input type="file" name="photos[]" id="photoInput" class="hidden" multiple accept="image/jpeg,image/png,image/webp,image/avif">
                        </div>
                        <div class="flex flex-wrap gap-2.5 mt-3" id="photoPreviewArea"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-desert-animated w-full font-bold rounded-full py-4 text-white text-sm sm:text-base shadow-xl inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i class="bi bi-send-fill"></i> Submit My Review & Photos
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-white/10 text-center">
                    <span class="text-slate-400 text-xs inline-flex items-center gap-1.5">
                        <i class="bi bi-shield-lock-fill text-emerald-400"></i> Licensed by Dubai Economy & Tourism (DET License #1430583) &bull; 100% Verified Review System
                    </span>
                </div>
            </div>
        @endif

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
                    s.classList.add('text-amber-400');
                    s.classList.remove('text-slate-700');
                } else {
                    s.classList.remove('text-amber-400');
                    s.classList.add('text-slate-700');
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
                        star.classList.add('text-amber-400');
                        star.classList.remove('text-slate-700');
                    } else {
                        star.classList.remove('text-amber-400');
                        star.classList.add('text-slate-700');
                    }
                });
            });
        });

        starBox.addEventListener('mouseleave', function () {
            const current = parseInt(ratingInput.value) || 5;
            stars.forEach((star, idx) => {
                if (idx < current) {
                    star.classList.add('text-amber-400');
                    star.classList.remove('text-slate-700');
                } else {
                    star.classList.remove('text-amber-400');
                    star.classList.add('text-slate-700');
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

            files.forEach((file) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'relative w-20 h-20 rounded-xl overflow-hidden border border-white/20 shadow-sm';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Safari photo preview" class="w-full h-full object-cover">
                            <button type="button" class="absolute top-1 right-1 bg-black/75 hover:bg-black text-white rounded-full w-5 h-5 flex items-center justify-center text-xs cursor-pointer" title="Remove">&times;</button>
                        `;
                        div.querySelector('button').addEventListener('click', function (ev) {
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
