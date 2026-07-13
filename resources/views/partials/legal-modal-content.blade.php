<div class="legal-modal-content">
    <h4 class="mb-4 text-dark fw-bold">{{ $page->title }}</h4>
    @if($page->description)
        <p class="mb-4 text-secondary">{!! nl2br(e($page->description)) !!}</p>
    @endif

    @foreach ($sections as $section)
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2">{{ $section->heading }}</h5>
            @if($section->subheading)
                <h6 class="text-muted fw-bold small mb-2">{{ $section->subheading }}</h6>
            @endif

            @if($section->items->count() > 0)
                <ul class="list-unstyled mb-0 d-grid gap-2">
                    @foreach($section->items as $item)
                        <li class="d-flex gap-2 text-secondary small">
                            <i class="bi bi-dot fs-4 lh-1 text-primary mt-n1"></i>
                            <span>{{ $item->content }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</div>
