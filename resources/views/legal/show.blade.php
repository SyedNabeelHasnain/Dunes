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
    "name": "{{ $page->title }}",
    "item": "{{ request()->url() }}"
  }]
}
</script>

<section class="page-header py-3 bg-dark text-white position-relative overflow-hidden" style="margin-top: -var(--header-h);">
    <div class="container position-relative z-1 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
        <div>
            <h1 class="display-4 fw-bold text-white mb-2">{{ $page->title }}</h1>
            @if($page->subtitle)
            <p class="lead text-white text-opacity-75">{{ $page->subtitle }}</p>
            @endif
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border border-primary border-opacity-25 shadow-sm rounded-4 overflow-hidden mb-5 border-top-0 bg-white" style="border-top: 5px solid var(--bs-primary) !important;">
                    <div class="card-body p-4 p-lg-5">

                        @if($page->description)
                        <div class="mb-5 lead text-dark" style="line-height: 1.7;">
                            {!! nl2br(e($page->description)) !!}
                        </div>
                        @endif

                        @if($page->sections->count() > 0)
                            <div class="legal-content">
                                @foreach($page->sections as $index => $section)
                                    <div class="mb-5" id="section-{{ $section->id }}">
                                        <h3 class="h4 fw-800 text-dark mb-3 border-start border-4 border-primary ps-3">
                                            {{ $section->heading }}
                                        </h3>

                                        @if($section->subheading)
                                            <h5 class="h6 fw-bold text-secondary mb-3 ms-4">{{ $section->subheading }}</h5>
                                        @endif

                                        @if($section->items->count() > 0)
                                            <ul class="list-unstyled mb-0 d-grid gap-3 ms-1">
                                                @foreach($section->items as $item)
                                                    <li class="d-flex gap-3">
                                                        <i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i>
                                                        <span class="text-secondary">{{ $item->content }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-5 border-primary opacity-25">
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <p class="text-muted">Content is being updated.</p>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="mt-5 p-5 text-center bg-light rounded-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-4" style="width: 80px; height: 80px;">
                        <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Still Have Questions?</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">Our dedicated team is ready to help you with any inquiries or custom tour requests.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',\App\Models\Setting::where('setting_key', 'site_whatsapp')->value('setting_value') ?? '971502456056') }}?text={{ urlencode('Hi! I have a question about your ' . $page->title . '.') }}" class="btn btn-whatsapp-animated btn-lg rounded-pill px-4 d-flex align-items-center justify-content-center gap-2" target="_blank" rel="noopener">
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
