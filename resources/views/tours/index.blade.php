@extends('layouts.app')

@section('content')
<!-- Page Header Section -->
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 15% 20%, rgba(246, 144, 68, 0.15) 0%, transparent 60%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Tours</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge glass rounded-pill px-3 py-1.5">
                        <i class="bi bi-star-fill text-warning me-1"></i>Rated 4.9/5 by 2,847+ Travelers
                    </span>
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1.5 text-white">
                        <i class="bi bi-patch-check-fill me-1"></i>DTCM Licensed Operator
                    </span>
                </div>
                <h1 class="display-4 fw-bold text-white mb-2">
                    @if(request('q'))
                        Search: "{{ request('q') }}"
                    @elseif($selectedCategorySlug)
                        {{ ucwords(str_replace('-', ' ', $selectedCategorySlug)) }}
                    @else
                        Explore Dubai Safari Tours & Experiences
                    @endif
                </h1>
                <p class="lead text-white text-opacity-75 mb-0">Discover top-rated desert adventures, high-power dune buggies, skyline dhow cruises & city tours.</p>
            </div>
            <div class="d-none d-lg-block text-end">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold fs-6">
                    <i class="bi bi-shield-check me-1"></i>Best Price Guarantee
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Regulatory E-E-A-T & Trust Bar -->
<section class="bg-light py-3 border-bottom shadow-sm">
    <div class="container">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-patch-check-fill text-primary fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">DTCM Licensed Operator</div>
                        <small class="text-muted" style="font-size: 11px;">Dubai Tourism Authority</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-repeat text-success fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Free Cancellation</div>
                        <small class="text-muted" style="font-size: 11px;">Full refund 24h prior</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-award-fill text-warning fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">100% Halal Food</div>
                        <small class="text-muted" style="font-size: 11px;">Veg, Non-Veg & Jain</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                    <div class="text-start">
                        <div class="fw-bold text-dark small lh-1">Instant Confirmation</div>
                        <small class="text-muted" style="font-size: 11px;">Card / Cash on Pickup</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        <!-- Interactive Search & Category Filter Controls -->
        <div class="card border-0 bg-light rounded-4 p-3 p-md-4 mb-5 shadow-sm">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="tourSearchInput" class="form-control rounded-pill ps-5 py-2.5 bg-white border-0 shadow-none" placeholder="Search safaris, buggies, cruises..." oninput="handleTourSearch(this.value)">
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="d-flex gap-2 overflow-auto pb-1" style="min-width: max-content;">
                        <button onclick="filterTours('')" data-category="" class="btn filter-btn {{ !$selectedCategorySlug ? 'btn-desert-animated-dark' : 'btn-white border' }} rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 transition-all small">
                            <i class="bi bi-grid-fill"></i> All ({{ $tours->count() }})
                        </button>
                        @foreach($categories as $cat)
                            @php
                                $catCount = $tours->where('category_id', $cat->id)->count();
                                $iconMap = [
                                    'desert-safari' => 'bi-sun-fill',
                                    'city-tour' => 'bi-building-fill',
                                    'water-activity' => 'bi-water',
                                    'day-trip' => 'bi-map-fill'
                                ];
                                $icon = $iconMap[$cat->slug] ?? 'bi-compass-fill';
                            @endphp
                            <button onclick="filterTours('{{ $cat->slug }}')" data-category="{{ $cat->slug }}" class="btn filter-btn {{ $selectedCategorySlug === $cat->slug ? 'btn-desert-animated-dark' : 'btn-white border' }} rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 transition-all small">
                                <i class="bi {{ $icon }}"></i> {{ $cat->name }} ({{ $catCount }})
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if($tours->count() > 0)
        <div class="row g-4" id="tours-grid">
            @foreach($tours as $t)
                @php
                    $minPrice = $t->tiers->min('pivot.price') ?? 0;
                    $tourCat = $categories->firstWhere('id', $t->category_id);
                    $tourCatSlug = $tourCat ? $tourCat->slug : '';
                @endphp
                <div class="col-12 col-md-6 col-lg-4 tour-item" data-category="{{ $tourCatSlug }}" data-name="{{ strtolower($t->name) }}">
                    <article class="card card-modern h-100 border-0 shadow-sm transition-all hover-shadow-md rounded-4 overflow-hidden bg-white">
                        <a href="{{ route('tours.show', $t->slug) }}" class="text-decoration-none text-dark d-flex flex-column h-100">
                            <div class="card-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 16/10;">
                                <img src="{{ asset('images/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $t->thumb_image)) }}" class="card-img-top w-100 h-100" alt="{{ $t->name }} Dubai" loading="lazy" style="object-fit: cover;">
                                @if($t->is_bestseller)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill shadow-sm">
                                    <i class="bi bi-fire me-1"></i>Best Seller
                                </span>
                                @endif
                                <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
                                    <span class="badge glass text-white fw-semibold">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $tourCat ? $tourCat->name : 'Tours' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1 text-primary"></i>{{ $t->duration }}
                                    </div>
                                    <div class="text-warning small fw-bold">
                                        <i class="bi bi-star-fill me-1"></i>{{ $t->rating ?: '4.9' }}
                                    </div>
                                </div>
                                <h2 class="h5 fw-bold mb-2 line-clamp-2 text-dark">{{ $t->name }}</h2>
                                
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>4x4 Pickup
                                    </span>
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>Halal Live BBQ
                                    </span>
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="bi bi-check2 text-success me-1"></i>Free Cancel 24h
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; font-weight: 700;">Starting from</small>
                                        <span class="h5 fw-bold text-primary mb-0">AED {{ number_format($minPrice) }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn-circle-whatsapp fab-whatsapp" data-tour-name="{{ $t->name }}" aria-label="Book {{ $t->name }} via WhatsApp">
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

        <div id="no-tours-message" class="text-center py-5" style="display: none;">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                <i class="bi bi-search fs-1 text-muted"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">No Tours Found</h2>
            <p class="text-muted mb-4">We couldn't find any tours matching your criteria.</p>
            <button onclick="resetFilters()" class="btn btn-desert-animated-dark rounded-pill px-5 py-3">View All Tours</button>
        </div>
        @else
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                <i class="bi bi-search fs-1 text-muted"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">No Tours Found</h2>
            <p class="text-muted mb-4">We couldn't find any tours matching your search query. Try exploring all our amazing experiences!</p>
            <a href="{{ route('tours.index') }}" class="btn btn-desert-animated-dark rounded-pill px-5 py-3">View All Tours</a>
        </div>
        @endif
    </div>
</section>

<!-- GEO & AI Direct-Answer Catalog Buyer's Guide -->
<section class="section py-5 bg-light border-top">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Dubai Tour Selection <span class="text-primary">Guide & FAQ</span></h2>
            <p class="text-muted lead mx-auto" style="max-width: 650px;">Expert tips to help you choose the best desert safari or city adventure for your group.</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 p-4 border-0 rounded-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-sun-fill text-warning fs-4"></i>
                        <h3 class="h6 fw-bold text-dark mb-0">Best for First-Timers</h3>
                    </div>
                    <p class="text-muted small mb-0">The <strong>Evening Desert Safari</strong> offers the complete Dubai experience: dune bashing in Lahbab Red Dunes, camel ride, sandboarding, 5-star live BBQ dinner, and live shows.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 p-4 border-0 rounded-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-speedometer2 text-primary fs-4"></i>
                        <h3 class="h6 fw-bold text-dark mb-0">Best for Thrill-Seekers</h3>
                    </div>
                    <p class="text-muted small mb-0">Choose our <strong>1000cc Dune Buggy (Can-Am / Polaris)</strong> or <strong>Quad Biking ATV tours</strong> for self-drive high-speed excitement across open dunes with full safety gear included.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 p-4 border-0 rounded-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-star-fill text-warning fs-4"></i>
                        <h3 class="h6 fw-bold text-dark mb-0">Best for Luxury & VIPs</h3>
                    </div>
                    <p class="text-muted small mb-0">Book the <strong>VIP Chalet Desert Safari</strong> or <strong>Marina Catamaran Dinner Cruise</strong> featuring private air-conditioned seating, dedicated waiter service, and gourmet cuisine.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
let currentCategory = '';
let currentSearch = '';

function applyTourFilters() {
    const items = document.querySelectorAll('.tour-item');
    let hasVisible = false;

    items.forEach(item => {
        const itemCat = (item.dataset.category || '').toLowerCase();
        const itemName = (item.dataset.name || '').toLowerCase();

        const matchesCat = !currentCategory || itemCat === currentCategory.toLowerCase();
        const matchesSearch = !currentSearch || itemName.includes(currentSearch.toLowerCase());

        if (matchesCat && matchesSearch) {
            item.style.display = 'block';
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('no-tours-message');
    if (noResults) {
        noResults.style.display = hasVisible ? 'none' : 'block';
    }
}

function filterTours(category) {
    currentCategory = category;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('btn-white', 'border');
            btn.classList.add('btn-desert-animated-dark');
        } else {
            btn.classList.remove('btn-desert-animated-dark');
            btn.classList.add('btn-white', 'border');
        }
    });

    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.history.pushState({}, '', url);

    applyTourFilters();
}

function handleTourSearch(val) {
    currentSearch = (val || '').trim();
    applyTourFilters();
}

function resetFilters() {
    const input = document.getElementById('tourSearchInput');
    if (input) input.value = '';
    currentSearch = '';
    filterTours('');
}

window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const cat = urlParams.get('category') || '';
    filterTours(cat);
});
</script>
@push('preloads')
<!-- Schema.org 2026 CollectionPage, ItemList & BreadcrumbList -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "CollectionPage",
      "@@id": "{{ route('tours.index') }}#webpage",
      "url": "{{ route('tours.index') }}",
      "name": "Dubai Desert Safari Tours & City Experiences | Dunes Discovery",
      "description": "Browse and book the best Dubai desert safari tours, dune buggy rentals, quad biking, and dhow cruise dinners with Dunes Discovery Tourism.",
      "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ route('home') }}"
          },
          {
            "@@type": "ListItem",
            "position": 2,
            "name": "Tours",
            "item": "{{ route('tours.index') }}"
          }
        ]
      },
      "mainEntity": {
        "@@type": "ItemList",
        "numberOfItems": {{ $tours->count() }},
        "itemListElement": [
          @foreach($tours as $idx => $t)
          {
            "@@type": "ListItem",
            "position": {{ $idx + 1 }},
            "name": {!! json_encode($t->name) !!},
            "url": "{{ route('tours.show', $t->slug) }}"
          }{{ $idx < $tours->count() - 1 ? ',' : '' }}
          @endforeach
        ]
      }
    }
  ]
}
</script>
@endpush
@endsection
