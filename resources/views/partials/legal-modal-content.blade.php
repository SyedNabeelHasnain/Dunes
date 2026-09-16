<div class="legal-modal-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom">
        <div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small fw-bold">Official Policy</span>
            <h4 class="mb-0 mt-1 text-dark fw-bold" id="modalLegalTitle">{{ $page->title }}</h4>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary active py-1 px-2" id="modalBtnEn" onclick="toggleModalLang('en')">EN</button>
            <button type="button" class="btn btn-outline-primary py-1 px-2" id="modalBtnAr" onclick="toggleModalLang('ar')">عربي</button>
        </div>
    </div>

    <!-- English View -->
    <div id="modalContentEn">
        @if($page->description)
            <div class="p-3 bg-light rounded-3 mb-3 text-secondary small border-start border-3 border-primary">
                {!! nl2br(e($page->description)) !!}
            </div>
        @endif

        @foreach ($sections as $section)
            <div class="mb-3">
                <h6 class="fw-bold text-dark mb-1">{{ $section->heading }}</h6>
                @if($section->subheading)
                    <div class="text-muted small mb-2">{{ $section->subheading }}</div>
                @endif

                @if($section->items->count() > 0)
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        @foreach($section->items as $item)
                            <li class="d-flex gap-2 text-secondary small">
                                <i class="bi bi-check2 text-primary flex-shrink-0 mt-1"></i>
                                <span>{{ $item->content }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Arabic View -->
    <div id="modalContentAr" class="d-none text-end" dir="rtl">
        @if($page->description_ar || $page->description)
            <div class="p-3 bg-light rounded-3 mb-3 text-secondary small border-end border-3 border-primary">
                {!! nl2br(e($page->description_ar ?: $page->description)) !!}
            </div>
        @endif

        @foreach ($sections as $section)
            <div class="mb-3">
                <h6 class="fw-bold text-dark mb-1">{{ $section->heading_ar ?: $section->heading }}</h6>
                @if($section->subheading_ar || $section->subheading)
                    <div class="text-muted small mb-2">{{ $section->subheading_ar ?: $section->subheading }}</div>
                @endif

                @if($section->items->count() > 0)
                    <ul class="list-unstyled mb-0 d-grid gap-2 pe-0">
                        @foreach($section->items as $item)
                            <li class="d-flex gap-2 text-secondary small">
                                <i class="bi bi-check2 text-primary flex-shrink-0 mt-1"></i>
                                <span>{{ $item->content_ar ?: $item->content }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pt-3 mt-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ url('/' . $page->slug) }}" target="_blank" class="small text-primary fw-bold text-decoration-none d-flex align-items-center gap-1">
            <span>View Full Document</span>
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <span class="text-muted small">Dunes Discovery Tourism LLC &bull; Dubai</span>
    </div>
</div>

<script>
function toggleModalLang(lang) {
    const enDiv = document.getElementById('modalContentEn');
    const arDiv = document.getElementById('modalContentAr');
    const btnEn = document.getElementById('modalBtnEn');
    const btnAr = document.getElementById('modalBtnAr');
    const title = document.getElementById('modalLegalTitle');

    if (lang === 'ar') {
        enDiv.classList.add('d-none');
        arDiv.classList.remove('d-none');
        btnEn.classList.remove('active');
        btnAr.classList.add('active');
        title.textContent = "{{ $page->title_ar ?: $page->title }}";
    } else {
        arDiv.classList.add('d-none');
        enDiv.classList.remove('d-none');
        btnAr.classList.remove('active');
        btnEn.classList.add('active');
        title.textContent = "{{ $page->title }}";
    }
}
</script>
