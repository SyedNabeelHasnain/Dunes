@extends('layouts.app')

@section('content')
<!-- Modern Hero Section -->
<section class="hero-modern position-relative d-flex align-items-center justify-content-center overflow-hidden" style="min-height: 90vh;">
    <video class="hero-video position-absolute top-0 start-0 w-100 h-100" autoplay loop muted playsinline style="object-fit: cover; z-index: -1;">
        <source src="{{ asset('images/desert-safar-dubai-tour-short-dune-discovery-tourism.mp4') }}" type="video/mp4">
    </video>
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.8) 100%); z-index: -1;"></div>

    <div class="container position-relative z-1 text-center text-white" style="margin-top:-5vh;">
        <div class="badge rounded-pill glass px-3 py-2 mb-4">
            <i class="bi bi-star-fill text-warning me-2"></i>
            <span class="fw-semibold">Rated 4.9/5 by 2,847+ Travelers</span>
        </div>
        <h1 class="display-3 fw-bold mb-3 text-white">Discover the Magic of <span class="text-gradient-primary">Dubai Desert</span></h1>
        <p class="lead mb-5 mx-auto opacity-90" style="max-width: 700px;">Experience thrilling dune bashing, magical sunsets, authentic BBQ dinner, and unforgettable entertainment under the stars.</p>

        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center align-items-center mb-5">
            <a href="#" class="btn btn-desert-animated btn-lg rounded-pill px-5 py-3 shadow-primary fw-bold" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="bi bi-calendar-check me-2"></i>Book Now
            </a>
            <a href="#" class="btn btn-desert-animated-dark btn-lg rounded-pill px-5 py-3 fw-bold d-inline-flex align-items-center gap-2" data-action="open-booking" data-tour="1" data-tier="1">
                <span class="fw-bold me-2 text-white">Starting from</span>
                <span class="fs-4 fw-bold text-primary">AED 79</span>
            </a>
        </div>

        <div class="container mb-4">
            <div class="row justify-content-center">
                <div class="col-lg-9 text-center">
                    <p class="h5 fw-bold text-white mb-2">Dubai Desert Safari with Luxury Land Cruiser Pick & Drop</p>
                    <p class="text-white-50 mb-0 px-2 px-md-5">Enjoy a premium desert safari experience with chauffeur-driven Luxury Land Cruiser hotel pickup and drop-off. Comfortable seating, professional drivers, and hassle-free transfers included with every booking.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center opacity-75">
            <div class="col-4 col-md-auto">
                <div class="h3 fw-bold mb-0 text-white">10K+</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Happy Guests</small>
            </div>
            <div class="col-4 col-md-auto border-start border-end border-white border-opacity-25 px-md-4">
                <div class="h3 fw-bold mb-0 text-white">4.9/5</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Top Rated</small>
            </div>
            <div class="col-4 col-md-auto">
                <div class="h3 fw-bold mb-0 text-white">24/7</div>
                <small class="text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 1px;">Support</small>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<div class="stats-bar bg-dark py-4 shadow-lg position-relative z-2" style="margin-top: -50px; border-radius: 24px 24px 0 0;">
    <div class="container">
        <div class="row g-0 text-center stats-grid">
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-trophy text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">#1</div>
                <small class="text-white text-opacity-50 small">Desert Safari</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-shield-check text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">100%</div>
                <small class="text-white text-opacity-50 small">Secure Pay</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-clock-history text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Fast</div>
                <small class="text-white text-opacity-50 small">Booking</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-truck text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">25+</div>
                <small class="text-white text-opacity-50 small">Vehicles</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-geo-alt text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Local</div>
                <small class="text-white text-opacity-50 small">Expert Guides</small>
            </div>
            <div class="col-4 col-lg-2 stats-item">
                <i class="bi bi-star text-primary fs-3 mb-2 d-block"></i>
                <div class="h4 fw-bold text-white mb-0">Best</div>
                <small class="text-white text-opacity-50 small">Price Promise</small>
            </div>
        </div>
    </div>
</div>

<!-- Popular Tours Section -->
<section class="section py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Popular <span class="text-primary">Tours</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 600px;">Handpicked experiences for unforgettable memories in the heart of Dubai.</p>
        </div>

        <div class="row g-4 mb-5">
            @foreach($bestsellers as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $category = $categories->firstWhere('id', $t->category_id);
                @endphp
                <div class="col-12 col-md-6 col-lg-3">
                    <article class="card card-modern h-100 border-0 shadow-sm">
                        <a href="{{ route('tours.show', $t->slug) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
                            <div class="card-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 16/10;">
                                <img src="{{ asset('images/' . $t->thumb_image) }}" class="card-img-top w-100 h-100" alt="{{ $t->name }}" loading="lazy" style="object-fit: cover;">
                                @if($t->is_bestseller)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                    <i class="bi bi-fire me-1"></i>Best Seller
                                </span>
                                @endif
                                <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
                                    <span class="badge glass text-white fw-semibold">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $category ? $category->name : 'Tours' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1"></i>{{ $t->duration }}
                                    </div>
                                    <div class="text-warning small">
                                        <i class="bi bi-star-fill me-1"></i>{{ $t->rating }}
                                    </div>
                                </div>
                                <h3 class="h5 fw-bold mb-3 line-clamp-2">{{ $t->name }}</h3>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Starting from</small>
                                        <span class="h5 fw-bold text-primary mb-0">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="{{ $t->name }}">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                        <div class="btn-circle-desert d-flex align-items-center justify-content-center">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('tours.index') }}" class="btn btn-desert-animated-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                View All Tours <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Guest Reviews Marquee Section -->
<section class="reviews-section bg-soft-primary py-5 overflow-hidden">
    <div class="container mb-5 py-lg-4">
        <div class="text-center">
            <h2 class="display-5 fw-bold mb-3">What Our <span class="text-primary">Guests Say</span></h2>
            <p class="text-muted lead">Real reviews from real travelers around the world.</p>
        </div>
    </div>

    @php
        $googleReviews = $reviews->where('source', 'google');
        $tripReviews = $reviews->where('source', 'tripadvisor');
    @endphp

    <div class="reviews-marquee mb-4">
        <div class="reviews-track d-flex gap-4">
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($googleReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>

    <div class="reviews-marquee reverse">
        <div class="reviews-track d-flex gap-4">
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
            @foreach($tripReviews as $r)
                {!! renderReviewCardMarkup($r) !!}
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Why <span class="text-primary">Choose Us</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 600px;">Trusted by thousands of travelers worldwide for premium desert experiences.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Best Price Guarantee</h3>
                    <p class="text-muted mb-0">We match any competitor price. No hidden fees, what you see is what you pay.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Instant Confirmation</h3>
                    <p class="text-muted mb-0">Receive immediate booking confirmation via email and WhatsApp.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-modern h-100 p-4 p-lg-5 text-center border-0 shadow-sm">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Free Cancellation</h3>
                    <p class="text-muted mb-0">Cancel up to 24 hours before for a full refund. Flexibility guaranteed.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs Section -->
<section class="section py-5 bg-light">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Common <span class="text-primary">Questions</span></h2>
            <p class="text-muted lead">Everything you need to know about our desert safari tours.</p>
        </div>

        @php
            $generalFaqIds = \App\Models\FaqAssignment::where('entity_type', 'general')->pluck('faq_id');
            $faqs = \App\Models\Faq::whereIn('id', $generalFaqIds)->where('status', 'active')->orderBy('priority', 'asc')->limit(6)->get();
        @endphp

        <div class="accordion accordion-flush mx-auto" id="faqAccordion" style="max-width: 800px;">
            @foreach($faqs as $index => $f)
            <div class="accordion-item border-animated bg-white mb-3 rounded-4 border-0 shadow-sm">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed rounded-4 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $index }}">
                        {{ $f->question }}
                    </button>
                </h3>
                <div id="faq-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        {{ $f->answer }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5 pb-3">
            <a href="{{ route('faq') }}" class="btn btn-desert-animated rounded-pill px-5 py-3 d-inline-flex align-items-center gap-2">
                View All FAQs <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA booking banner -->
<section class="cta-section py-5 py-lg-6 position-relative text-white">
    <div class="container position-relative z-1 text-center py-4">
        <h2 class="display-4 fw-bold mb-3 text-white">Ready for Your Desert Adventure?</h2>
        <p class="lead mb-4 opacity-90 mx-auto" style="max-width: 620px;">Book now and create memories that last a lifetime. Free cancellation up to 24 hours before.</p>
        <div class="pt-2">
            <a href="#" class="btn btn-cta-white btn-lg rounded-pill px-5 py-3.5 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="bi bi-calendar-check-fill me-2 text-primary"></i>Book Your Tour Now
            </a>
        </div>
    </div>
</section>

@endsection

@php
function renderReviewCardMarkup($r) {
    $stars = '';
    for($i = 0; $i < 5; $i++) {
        $stars .= $i < floor($r->rating) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>';
    }

    $sourceIcon = ($r->source == 'google') ? '<i class="bi bi-google text-primary"></i>' : '<i class="bi-star-fill text-success"></i>';
    $url = !empty($r->review_url) ? $r->review_url : '#';
    $avatar = !empty($r->reviewer_avatar_url) ? $r->reviewer_avatar_url : 'https://ui-avatars.com/api/?name='.urlencode($r->reviewer_name);
    $fallbackAvatar = 'https://ui-avatars.com/api/?name='.urlencode($r->reviewer_name).'&background=random';

    return '
    <div class="review-card h-100 d-flex flex-column text-start">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($r->reviewer_name) . '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src=\'' . $fallbackAvatar . '\'">
                <div>
                    <div class="fw-bold text-dark small">' . htmlspecialchars($r->reviewer_name) . '</div>
                    <div class="text-muted extra-small" style="font-size: 0.75rem;">' . ($r->published_date ? $r->published_date->format('M Y') : '') . '</div>
                </div>
            </div>
            <div class="d-flex gap-1 small">' . $stars . '</div>
        </div>
        ' . ($r->review_title ? '<h6 class="fw-bold mb-2 text-dark line-clamp-1">' . htmlspecialchars($r->review_title) . '</h6>' : '') . '
        <p class="text-dark small mb-3 flex-grow-1 line-clamp-3" style="font-size: 0.9rem;">"' . htmlspecialchars($r->review_text) . '"</p>
        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
            <span class="badge bg-light text-dark rounded-pill px-2 py-1 small fw-normal">' . $sourceIcon . ' ' . ucfirst($r->source) . '</span>
            <a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 0.8rem;">View</a>
        </div>
    </div>';
}
@endphp
