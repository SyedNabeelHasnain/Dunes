@extends('layouts.app')

@section('content')
@push('preloads')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "BreadcrumbList",
      "@@id": "{{ route('faq') }}#breadcrumb",
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
          "name": "FAQ",
          "item": "{{ route('faq') }}"
        }
      ]
    }
    @if(isset($faqs) && $faqs->count() > 0)
    ,
    {
      "@@type": "FAQPage",
      "@@id": "{{ route('faq') }}#faqpage",
      "mainEntity": [
        @foreach($faqs as $index => $f)
        {
          "@@type": "Question",
          "name": {!! json_encode($f->question) !!},
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": {!! json_encode($f->answer) !!}
          }
        }{{ $index < $faqs->count() - 1 ? ',' : '' }}
        @endforeach
      ]
    }
    @endif
  ]
}
</script>
@endpush

<!-- Page Header Section -->
<section class="page-header py-4 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 15% 20%, rgba(246, 144, 68, 0.15) 0%, transparent 60%);"></div>
    <div class="container position-relative z-1 pt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">FAQ</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge glass rounded-pill px-3 py-1.5 mb-2">
                    <i class="bi bi-patch-question-fill text-primary me-1"></i>Help Center & Direct Answers
                </span>
                <h1 class="display-4 fw-bold text-white mb-2">Frequently Asked Questions</h1>
                <p class="lead text-white text-opacity-75 mb-0">Clear, direct answers about bookings, timings, halal food, safety, and tour inclusions.</p>
            </div>
            <div class="d-none d-lg-block text-end">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold fs-6">
                    <i class="bi bi-clock-history me-1"></i>24/7 Support Available
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

<section class="section py-5 bg-white">
    <div class="container">
        <!-- Live Instant Search Bar for FAQs -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card border-0 bg-light rounded-4 p-3 shadow-sm">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="faqSearchInput" class="form-control rounded-pill ps-5 py-3 bg-white border-0 shadow-none" placeholder="Search questions (e.g., cancel, clothing, quad, pickup, timing)..." oninput="handleFaqSearch(this.value)">
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion accordion-flush custom-accordion" id="faqAccordion">
                    @foreach($faqs as $i => $f)
                    <div class="accordion-item faq-item mb-3 border-animated rounded-4 shadow-sm overflow-hidden bg-white" data-question="{{ strtolower($f->question) }}" data-answer="{{ strtolower($f->answer) }}">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                                <i class="bi bi-question-circle-fill me-3 text-primary"></i>
                                {{ $f->question }}
                            </button>
                        </h2>
                        <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body py-4 px-4 text-muted leading-relaxed">
                                {{ $f->answer }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="noFaqResults" class="text-center py-5 d-none">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-search fs-2 text-muted"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-2">No matching questions found</h3>
                    <p class="text-muted small mb-3">Can't find what you're looking for? Reach out directly via WhatsApp for instant answers.</p>
                </div>

                <div class="mt-5 p-5 text-center bg-light rounded-5 border">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-3">Still Have Questions?</h2>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">Our dedicated team is ready 24/7 to help you with any inquiries or custom tour arrangements.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056') }}?text={{ urlencode('Hi! I have a question about your tours.') }}" class="btn btn-whatsapp-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            WhatsApp 24/7
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-desert-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-envelope"></i>
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
function handleFaqSearch(query) {
    const q = (query || '').trim().toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    let visibleCount = 0;

    items.forEach(item => {
        const question = item.dataset.question || '';
        const answer = item.dataset.answer || '';
        if (!q || question.includes(q) || answer.includes(q)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noFaqResults');
    if (noResults) {
        noResults.classList.toggle('d-none', visibleCount > 0);
    }
}
</script>
@endpush
@endsection
