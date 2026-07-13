<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin CMS | Dunes Discovery Tourism</title>
    
    <!-- CSS Stylesheets -->
    <link href="{{ asset('assets/vendor/bootstrap/5.3.2/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="preload" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}"></noscript>
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ \App\Models\Setting::where('setting_key', 'cache_version')->value('setting_value') ?? '1' }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .loader-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.8); z-index: 9999; display: none;
            justify-content: center; align-items: center; flex-direction: column;
        }
        .spinner {
            width: 50px; height: 50px; border: 5px solid #f3f3f3;
            border-top: 5px solid var(--bs-primary); border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .popover-primary {
            --bs-popover-border-color: var(--bs-primary);
            --bs-popover-header-bg: var(--bs-primary);
            --bs-popover-header-color: var(--bs-white);
            --bs-popover-body-padding-x: 0;
            --bs-popover-body-padding-y: 0;
        }
        .popover-primary .popover-header {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .popover-body-content {
            max-height: 250px;
            overflow-y: auto;
            min-width: 250px;
        }
    </style>
</head>
<body>

    <!-- Loader Processing Overlay -->
    <div class="loader-overlay" id="appLoader">
        <div class="spinner mb-3"></div>
        <div class="fw-bold text-primary">Processing...</div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('show'); this.classList.remove('show')"></div>

    <!-- Sidebar navigation -->
    <aside class="admin-sidebar shadow-lg" id="sidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <div class="brand-text">
                <h4 class="text-white fw-800 mb-0">DUNES<span class="text-primary">CMS</span></h4>
            </div>
            <button class="btn btn-link text-white p-0 d-none d-lg-block" id="sidebarToggleDesktop">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>
        <div class="sidebar-scroll">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="sidebar-link {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-check-fill"></i> <span>Bookings</span>
            </a>
            <a href="{{ route('admin.inquiries.index') }}" class="sidebar-link {{ request()->routeIs('admin.inquiries*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper-fill"></i> <span>Inquiries</span>
            </a>
            <a href="{{ route('admin.whatsapp.leads') }}" class="sidebar-link {{ request()->routeIs('admin.whatsapp.leads') ? 'active' : '' }}">
                <i class="bi bi-whatsapp"></i> <span>WhatsApp Leads</span>
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="sidebar-link {{ request()->routeIs('admin.whatsapp.settings') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i> <span>WhatsApp Settings</span>
            </a>
            <a href="{{ route('admin.tours.index') }}" class="sidebar-link {{ request()->routeIs('admin.tours*') ? 'active' : '' }}">
                <i class="bi bi-map-fill"></i> <span>Tours</span>
            </a>
            <a href="{{ route('admin.tiers.index') }}" class="sidebar-link {{ request()->routeIs('admin.tiers*') ? 'active' : '' }}">
                <i class="bi bi-layers-fill"></i> <span>Tiers</span>
            </a>
            <a href="{{ route('admin.addons.index') }}" class="sidebar-link {{ request()->routeIs('admin.addons*') ? 'active' : '' }}">
                <i class="bi bi-plus-square-fill"></i> <span>Addons</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="sidebar-link {{ request()->routeIs('admin.pricing*') ? 'active' : '' }}">
                <i class="bi bi-tag-fill"></i> <span>Pricing Manager</span>
            </a>
            <a href="{{ route('admin.faqs.index') }}" class="sidebar-link {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}">
                <i class="bi bi-patch-question-fill"></i> <span>FAQs</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                <i class="bi bi-star-fill"></i> <span>Reviews</span>
            </a>

            <div class="sidebar-header small fw-bold text-uppercase text-white opacity-50 px-3 mt-3 mb-2">Content</div>
            <a href="{{ route('admin.blogs.index') }}" class="sidebar-link {{ request()->routeIs('admin.blogs*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> <span>Blog</span>
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.blog-categories*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> <span>Blog Categories</span>
            </a>

            <div class="sidebar-header small fw-bold text-uppercase text-white opacity-50 px-3 mt-3 mb-2">System</div>
            <a href="{{ route('admin.settings.google') }}" class="sidebar-link {{ request()->routeIs('admin.settings.google*') ? 'active' : '' }}">
                <i class="bi bi-google"></i> <span>Google Integration</span>
            </a>
            <a href="{{ route('admin.settings.meta') }}" class="sidebar-link {{ request()->routeIs('admin.settings.meta*') ? 'active' : '' }}">
                <i class="bi bi-facebook"></i> <span>Meta Integration</span>
            </a>
            <a href="{{ route('home') }}" target="_blank" class="sidebar-link">
                <i class="bi bi-globe2"></i> <span>Live Website</span>
            </a>
            <a href="{{ route('admin.clear-cache') }}" class="sidebar-link text-warning">
                <i class="bi bi-arrow-clockwise"></i> <span>Clear Cache</span>
            </a>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="#" class="sidebar-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-power"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main-content">
        <nav class="top-navbar d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white shadow-sm d-lg-none rounded-3 border-0" onclick="document.getElementById('sidebar').classList.toggle('show'); document.getElementById('sidebarOverlay').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h1 class="h5 fw-800 mb-0 text-capitalize">@yield('page_title', 'Dashboard')</h1>
            </div>

            <!-- Active Online Visitors Widget -->
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-white shadow-sm border-0 fw-bold text-primary d-flex align-items-center gap-2"
                        id="activeVisitorsWidget"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-custom-class="popover-primary"
                        data-bs-html="true"
                        data-bs-trigger="focus"
                        data-bs-placement="bottom"
                        title="Active Visitors (5m)"
                        data-bs-content="<div class='popover-body-content p-3'><small class='text-muted'>Loading visitors...</small></div>">
                    <span class="position-relative d-flex">
                        <i class="bi bi-people-fill fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                            <span class="visually-hidden">Online</span>
                        </span>
                    </span>
                    <span id="activeVisitorsCount">0 Online</span>
                </button>
            </div>
        </nav>

        <!-- Page Yielded Content -->
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/vendor/bootstrap/5.3.2/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Toggle Sidebar collapsed state on desktop
        $('#sidebarToggleDesktop').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
            $('.admin-main-content').toggleClass('collapsed');
            localStorage.setItem('sidebarState', $('#sidebar').hasClass('collapsed') ? 'collapsed' : 'expanded');
        });

        if (localStorage.getItem('sidebarState') === 'collapsed' && $(window).width() >= 992) {
            $('#sidebar').addClass('collapsed');
            $('.admin-main-content').addClass('collapsed');
        }

        // Initialize DataTable
        $('.table:not(.no-datatable)').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search records...",
                paginate: {
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>'
                }
            },
            dom: "<'row mb-3 mt-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });

        // Hide sidebar on clicking outside (mobile)
        $(document).on('click', function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('#sidebar, #sidebarToggleDesktop, .btn-white').length) {
                    $('#sidebar').removeClass('show');
                    $('#sidebarOverlay').removeClass('show');
                }
            }
        });

        // Active visitors popover and polling
        const widget = document.getElementById('activeVisitorsWidget');
        const countSpan = document.getElementById('activeVisitorsCount');
        let popoverInstance = new bootstrap.Popover(widget);

        function updateActiveVisitors() {
            fetch("{{ route('admin.active-visitors') }}")
                .then(res => res.json())
                .then(data => {
                    countSpan.textContent = `${data.count} Online`;
                    
                    let html = '<div class="popover-body-content p-3" style="max-height:220px; overflow-y:auto; font-size:12px; min-width:260px;">';
                    if (data.visitors && data.visitors.length > 0) {
                        data.visitors.forEach(v => {
                            const date = new Date(v.request_timestamp);
                            const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            html += `
                                <div class="mb-3 border-bottom pb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>${v.client_ip}</strong>
                                        <span class="text-muted">${timeStr}</span>
                                    </div>
                                    <div class="text-primary text-truncate">${v.city || 'Unknown'}, ${v.country || ''}</div>
                                    <div class="text-muted text-truncate">${v.request_uri}</div>
                                    <div class="small opacity-75">${v.device_type} (${v.browser_name} / ${v.os_name})</div>
                                </div>
                            `;
                        });
                    } else {
                        html += '<div class="text-center text-muted py-2">No active human visitors in last 5m</div>';
                    }
                    html += '</div>';

                    // Update Popover content dynamically
                    widget.setAttribute('data-bs-content', html);
                    
                    // Re-init popover to update content
                    popoverInstance.dispose();
                    popoverInstance = new bootstrap.Popover(widget);
                })
                .catch(err => console.error("Failed to fetch active visitors", err));
        }

        // Run immediately and poll every 15s
        updateActiveVisitors();
        setInterval(updateActiveVisitors, 15000);
    });

    // Global Loader Controls
    const loader = document.getElementById('appLoader');
    function showLoader() { loader.style.display = 'flex'; }
    function hideLoader() { loader.style.display = 'none'; }
    </script>

    @stack('scripts')
</body>
</html>
