@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [{
    "@type": "ListItem",
    "position": 1,
    "name": "Home",
    "item": "{{ route('home') }}"
  },{
    "@type": "ListItem",
    "position": 2,
    "name": "FAQ",
    "item": "{{ route('faq') }}"
  }]
}
</script>

<section class="page-header py-3 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('images/abu-dhabi-city-tour-hero.jpg') }}') center/cover;"></div>
    <div class="container position-relative z-1 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">FAQ</li>
            </ol>
        </nav>
        <div>
            <h1 class="display-4 fw-bold text-white mb-2">Frequently Asked Questions</h1>
            <p class="lead text-white text-opacity-75">Find answers to common questions about our tours</p>
        </div>
    </div>
</section>

<section class="section py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion accordion-flush custom-accordion" id="faqAccordion">
                    @foreach($faqs as $i => $f)
                    <div class="accordion-item mb-3 border-animated rounded-4 shadow-sm overflow-hidden bg-white">
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

                <div class="mt-5 p-5 text-center bg-light rounded-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Still Have Questions?</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">Our dedicated team is ready to help you with any inquiries or custom tour requests.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056') }}?text={{ urlencode('Hi! I have a question about your tours.') }}" class="btn btn-whatsapp-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            WhatsApp Us
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
@endsection
