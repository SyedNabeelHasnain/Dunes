@extends('layouts.app')

@section('content')
<section class="page-header py-3 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 15% 20%, rgba(246, 144, 68, 0.12) 0%, transparent 55%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Tours</li>
            </ol>
        </nav>
        <div>
            <h1 class="display-4 fw-bold text-white mb-2">
                @if(request('q'))
                    Search: "{{ request('q') }}"
                @elseif($selectedCategorySlug)
                    {{ ucwords(str_replace('-', ' ', $selectedCategorySlug)) }}
                @else
                    Explore Our Tours
                @endif
            </h1>
            <p class="lead text-white text-opacity-75">Discover unforgettable experiences in the heart of Dubai</p>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        <!-- Category Filter Tabs -->
        <div class="mb-5 overflow-auto">
            <div class="d-flex gap-2 p-2" style="min-width: max-content;">
                <button onclick="filterTours('')" data-category="" class="btn filter-btn {{ !$selectedCategorySlug ? 'btn-desert-animated-dark' : 'btn-outline-dark' }} rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 transition-all">
                    <i class="bi bi-grid-fill"></i> All Tours
                </button>
                @foreach($categories as $cat)
                    @php
                        $iconMap = [
                            'desert-safari' => 'bi-sun-fill',
                            'city-tour' => 'bi-building-fill',
                            'water-activity' => 'bi-water',
                            'day-trip' => 'bi-map-fill'
                        ];
                        $icon = $iconMap[$cat->slug] ?? 'bi-compass-fill';
                    @endphp
                    <button onclick="filterTours('{{ $cat->slug }}')" data-category="{{ $cat->slug }}" class="btn filter-btn {{ $selectedCategorySlug === $cat->slug ? 'btn-desert-animated-dark' : 'btn-outline-dark' }} rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 transition-all">
                        <i class="bi {{ $icon }}"></i> {{ $cat->name }}
                    </button>
                @endforeach
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
                <div class="col-12 col-md-6 col-lg-3 tour-item" data-category="{{ $tourCatSlug }}">
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
                                        <i class="bi bi-tag-fill me-1"></i>{{ $tourCat ? $tourCat->name : 'Tours' }}
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
                                <h2 class="h5 fw-bold mb-3 line-clamp-2">{{ $t->name }}</h2>
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

        <div id="no-tours-message" class="text-center py-5" style="display: none;">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                <i class="bi bi-search fs-1 text-muted"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">No Tours Found</h2>
            <p class="text-muted mb-4">We couldn't find any tours matching this category.</p>
            <button onclick="filterTours('')" class="btn btn-desert-animated-dark rounded-pill px-5 py-3">View All Tours</button>
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

@push('scripts')
<script>
function filterTours(category) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('btn-outline-dark');
            btn.classList.add('btn-desert-animated-dark');
        } else {
            btn.classList.add('btn-outline-dark');
            btn.classList.remove('btn-desert-animated-dark');
        }
    });

    const items = document.querySelectorAll('.tour-item');
    let hasVisible = false;

    items.forEach(item => {
        const itemCat = item.dataset.category;

        item.classList.remove('animate-fade-up');
        item.classList.remove('aos-animate');

        if (category === '' || itemCat === category) {
            item.style.display = 'block';
            void item.offsetWidth;
            item.classList.add('animate-fade-up');
            setTimeout(() => item.classList.add('aos-animate'), 50);
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('no-tours-message');
    if (noResults) {
        noResults.style.display = hasVisible ? 'none' : 'block';
        if (!hasVisible) noResults.classList.add('animate-fade-up');
    }

    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.history.pushState({}, '', url);
}

window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const cat = urlParams.get('category') || '';
    filterTours(cat);
});
</script>
@endpush
@endsection
