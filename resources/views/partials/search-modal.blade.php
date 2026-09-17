<!-- Global Interactive Search Modal & Intent Trigger (Phase A) -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="globalSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small">
                        <i class="bi bi-search me-1"></i>Search Dubai Safaris
                    </span>
                    <span class="text-muted small">DET Licensed Operator #1430583</span>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('tours.search') }}" method="GET" id="globalSearchForm">
                    <div class="position-relative mb-4">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 fs-5 text-primary"></i>
                        <input type="text" name="q" id="globalSearchModalInput" class="form-control form-control-lg rounded-pill ps-5 pe-5 py-3 border-2 border-primary border-opacity-25 shadow-sm" placeholder="Search Dubai desert safaris, 1000cc buggies, VIP dinner, private 4x4..." autocomplete="off" required>
                        <button type="submit" class="btn btn-desert-animated rounded-pill position-absolute top-50 end-0 translate-middle-y me-2 px-3 py-1.5 fw-semibold small">
                            Search
                        </button>
                    </div>
                </form>

                <div class="trending-searches-wrapper">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="text-uppercase fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="bi bi-fire text-danger me-1"></i>Popular Searches
                        </small>
                        <small class="text-muted" style="font-size: 11px;">Press <kbd class="border rounded px-1.5 py-0.5 bg-light text-muted">/</kbd> anytime</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('tours.search', ['q' => 'Evening Desert Safari']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-sunset text-warning"></i> Evening Desert Safari
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Can-Am Dune Buggy']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-speedometer2 text-danger"></i> Can-Am Dune Buggy
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Quad Biking Lahbab']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-compass text-primary"></i> Quad Biking Lahbab
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'VIP Luxury Desert Safari']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-gem text-warning"></i> VIP Luxury Table
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Morning Desert Safari']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-sunrise text-info"></i> Morning Safari
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Dubai Marina Dhow Cruise']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-water text-primary"></i> Marina Dhow Cruise
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Abu Dhabi City Tour']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-building text-secondary"></i> Abu Dhabi City Tour
                        </a>
                        <a href="{{ route('tours.search', ['q' => 'Private Land Cruiser']) }}" class="badge bg-light text-dark border rounded-pill px-3 py-2 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow-sm transition-all">
                            <i class="bi bi-car-front-fill text-success"></i> Private Land Cruiser
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2.5 px-4 justify-content-between">
                <span class="small text-muted" style="font-size: 11px;">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>100% Free 24h Cancellation on all tours
                </span>
                <a href="{{ route('tours.index') }}" class="small fw-semibold text-primary text-decoration-none" style="font-size: 12px;">
                    Browse full tour catalog <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Keyboard shortcut '/' or Ctrl+K / Cmd+K to open search modal
    document.addEventListener('keydown', function(e) {
        if ((e.key === '/' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) && 
            !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            var searchModalEl = document.getElementById('globalSearchModal');
            if (searchModalEl && window.bootstrap) {
                var modal = bootstrap.Modal.getInstance(searchModalEl) || new bootstrap.Modal(searchModalEl);
                modal.show();
            }
        }
    });

    var searchModalEl = document.getElementById('globalSearchModal');
    if (searchModalEl) {
        searchModalEl.addEventListener('shown.bs.modal', function () {
            var inp = document.getElementById('globalSearchModalInput');
            if (inp) inp.focus();
        });
    }
});
</script>
