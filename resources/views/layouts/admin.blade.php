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
    @php
        try {
            $adminCacheVer = \Illuminate\Support\Facades\Cache::remember('cache_ver_admin', 86400, function() {
                return \App\Models\Setting::where('setting_key', 'cache_version')->value('setting_value') ?? '1';
            });
        } catch (\Throwable $e) {
            $adminCacheVer = time();
        }
    @endphp
    <link href="{{ asset('assets/css/app.css') }}?v={{ $adminCacheVer }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Global SVG & Pagination Safeguards */
        svg {
            max-width: 100%;
        }
        .pagination svg, nav svg, nav[role="navigation"] svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            max-width: 1.25rem !important;
            max-height: 1.25rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        nav[role="navigation"] {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
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

        /* DataTables Sorting & Clean Table Styling */
        table.dataTable thead th,
        table.table thead th {
            position: relative;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s ease, color 0.15s ease;
            white-space: nowrap;
        }
        table.dataTable thead th:not(.no-sort):hover,
        table.table thead th:not(.no-sort):hover {
            background-color: rgba(245, 143, 67, 0.08) !important;
            color: #F58F43 !important;
        }
        table.dataTable thead th.no-sort,
        table.table thead th.no-sort {
            cursor: default !important;
        }
        /* Prominent High-Contrast Sort Arrows */
        table.dataTable thead th.sorting::after {
            content: " \21C5" !important;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.4;
            font-size: 0.9rem;
            font-weight: 700;
        }
        table.dataTable thead th.sorting_asc::after {
            content: " \25B2" !important;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 1;
            color: #F58F43 !important;
            font-size: 0.85rem;
            font-weight: 700;
        }
        table.dataTable thead th.sorting_desc::after {
            content: " \25BC" !important;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 1;
            color: #F58F43 !important;
            font-size: 0.85rem;
            font-weight: 700;
        }
        table.dataTable thead th.no-sort::after,
        table.dataTable thead th.no-sort::before {
            display: none !important;
            content: "" !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 50rem;
            padding: 0.4rem 1.25rem;
            border: 1px solid #dee2e6;
            outline: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #F58F43;
            box-shadow: 0 0 0 0.25rem rgba(245, 143, 67, 0.25);
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 50rem;
            padding: 0.35rem 2rem 0.35rem 0.85rem;
            border: 1px solid #dee2e6;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_paginate .page-link {
            border-radius: 50rem;
            margin: 0 2px;
            font-size: 0.875rem;
            color: #0f2239;
        }
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: #F58F43;
            border-color: #F58F43;
            color: #ffffff;
        }

        /* Interactive Command Palette */
        .cmd-item {
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }
        .cmd-item:hover, .cmd-item.active {
            background-color: #f8f9fa;
            border-color: #F58F43 !important;
            transform: translateX(4px);
        }

        /* Clickable Ajax Status Badges */
        .badge-interactive {
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .badge-interactive:hover {
            transform: scale(1.06);
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
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
            <a href="{{ route('admin.analytics.index') }}" class="sidebar-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> <span>Analytics</span>
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

            <div class="sidebar-heading">Tour Management</div>
            <a href="{{ route('admin.tours.index') }}" class="sidebar-link {{ request()->routeIs('admin.tours*') ? 'active' : '' }}">
                <i class="bi bi-compass-fill"></i> <span>Tours Inventory</span>
            </a>
            <a href="{{ route('admin.addons.index') }}" class="sidebar-link {{ request()->routeIs('admin.addons*') ? 'active' : '' }}">
                <i class="bi bi-puzzle-fill"></i> <span>Tour Add-ons</span>
            </a>
            <a href="{{ route('admin.tiers.index') }}" class="sidebar-link {{ request()->routeIs('admin.tiers*') ? 'active' : '' }}">
                <i class="bi bi-layers-fill"></i> <span>Pricing Tiers</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="sidebar-link {{ request()->routeIs('admin.pricing*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> <span>Pricing Matrix</span>
            </a>

            <div class="sidebar-heading">Marketing & Content</div>
            <a href="{{ route('admin.blogs.index') }}" class="sidebar-link {{ request()->routeIs('admin.blogs*') ? 'active' : '' }}">
                <i class="bi bi-journal-richtext"></i> <span>Blog Articles</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                <i class="bi bi-star-half"></i> <span>Customer Reviews</span>
            </a>
            <a href="{{ route('admin.faqs.index') }}" class="sidebar-link {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}">
                <i class="bi bi-question-circle-fill"></i> <span>FAQs</span>
            </a>
            <a href="{{ route('admin.legal.index') }}" class="sidebar-link {{ request()->routeIs('admin.legal*') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i> <span>Legal Policies</span>
            </a>

            <div class="sidebar-heading">System & Integrations</div>
            <a href="{{ route('admin.settings.google') }}" class="sidebar-link {{ request()->routeIs('admin.settings.google*') ? 'active' : '' }}">
                <i class="bi bi-google"></i> <span>Google Integrations</span>
            </a>
            <a href="{{ route('admin.settings.meta') }}" class="sidebar-link {{ request()->routeIs('admin.settings.meta*') ? 'active' : '' }}">
                <i class="bi bi-meta"></i> <span>Meta / Facebook</span>
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="sidebar-link {{ request()->routeIs('admin.whatsapp.settings*') ? 'active' : '' }}">
                <i class="bi bi-gear-wide-connected"></i> <span>WhatsApp Setup</span>
            </a>

            <div class="sidebar-heading">External Tools</div>
            <a href="{{ url('/rate-card') }}" target="_blank" class="sidebar-link">
                <i class="bi bi-file-earmark-pdf-fill text-warning"></i> <span>Live Rate Card</span>
            </a>
            <a href="{{ url('/') }}" target="_blank" class="sidebar-link">
                <i class="bi bi-box-arrow-up-right text-info"></i> <span>Visit Website</span>
            </a>
        </div>

        <div class="sidebar-footer">
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
        <nav class="top-navbar d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white shadow-sm d-lg-none rounded-3 border-0" onclick="document.getElementById('sidebar').classList.toggle('show'); document.getElementById('sidebarOverlay').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h1 class="h5 fw-800 mb-0 text-capitalize text-dark">@yield('page_title', 'Dashboard')</h1>
            </div>

            <!-- Top Actions: Command Palette & Active Online Visitors Widget -->
            <div class="d-flex align-items-center gap-2">
                <!-- Command Palette Shortcut Button -->
                <button type="button" class="btn btn-white shadow-sm border-0 d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-muted small fw-bold" data-bs-toggle="modal" data-bs-target="#commandPaletteModal">
                    <i class="bi bi-search text-primary"></i>
                    <span class="d-none d-sm-inline">Jump to...</span>
                    <kbd class="bg-light border text-dark px-2 py-0.5 rounded shadow-none" style="font-size: 0.7rem;">Ctrl K</kbd>
                </button>

                <!-- Active Online Visitors Widget -->
                <button type="button" class="btn btn-white shadow-sm border-0 fw-bold text-primary d-flex align-items-center gap-2 rounded-pill px-3 py-2"
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

    <!-- Spotlight Command Palette Modal (Ctrl + K) -->
    <div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-labelledby="commandPaletteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 shadow-lg border-0 bg-white overflow-hidden">
                <div class="modal-header border-bottom p-3 bg-light">
                    <div class="input-group input-group-lg border-0 bg-transparent">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-primary fs-4"></i></span>
                        <input type="text" class="form-control bg-transparent border-0 shadow-none fs-5 fw-bold" id="cmdInput" placeholder="Search pages, actions, tours, or bookings... (Esc to close)" autofocus>
                    </div>
                </div>
                <div class="modal-body p-3" style="max-height: 420px; overflow-y: auto;">
                    <!-- Quick Actions -->
                    <div class="mb-3 cmd-section" data-section="actions">
                        <span class="text-uppercase text-muted fw-bold small px-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Quick Actions</span>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6 cmd-entry" data-keywords="add tour create new tour">
                                <a href="{{ route('admin.tours.create') }}" class="d-flex align-items-center p-2 rounded-3 border bg-light text-dark cmd-item">
                                    <i class="bi bi-plus-circle-fill text-primary fs-5 me-2"></i>
                                    <div><strong class="d-block small">Add New Tour</strong><span class="text-muted" style="font-size: 0.75rem;">Create a new desert safari or activity</span></div>
                                </a>
                            </div>
                            <div class="col-md-6 cmd-entry" data-keywords="add addon create add-on upgrade">
                                <a href="{{ route('admin.addons.index') }}" class="d-flex align-items-center p-2 rounded-3 border bg-light text-dark cmd-item">
                                    <i class="bi bi-puzzle-fill text-warning fs-5 me-2"></i>
                                    <div><strong class="d-block small">Manage Tour Add-ons</strong><span class="text-muted" style="font-size: 0.75rem;">Quad biking, VIP seating, buggy</span></div>
                                </a>
                            </div>
                            <div class="col-md-6 cmd-entry" data-keywords="write blog new article post">
                                <a href="{{ route('admin.blogs.create') }}" class="d-flex align-items-center p-2 rounded-3 border bg-light text-dark cmd-item">
                                    <i class="bi bi-journal-plus text-success fs-5 me-2"></i>
                                    <div><strong class="d-block small">Write Blog Post</strong><span class="text-muted" style="font-size: 0.75rem;">Publish travel guides and tips</span></div>
                                </a>
                            </div>
                            <div class="col-md-6 cmd-entry" data-keywords="export bookings csv download excel">
                                <a href="{{ route('admin.bookings.export') }}" class="d-flex align-items-center p-2 rounded-3 border bg-light text-dark cmd-item">
                                    <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5 me-2"></i>
                                    <div><strong class="d-block small">Export Bookings (CSV)</strong><span class="text-muted" style="font-size: 0.75rem;">Download customer reservations spreadsheet</span></div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Shortcuts -->
                    <div class="cmd-section" data-section="navigation">
                        <span class="text-uppercase text-muted fw-bold small px-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Navigation Shortcuts</span>
                        <div class="list-group list-group-flush mt-1">
                            <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="dashboard overview revenue kpi stats">
                                <span><i class="bi bi-grid-1x2-fill text-primary me-2"></i> Dashboard Overview</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.analytics.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="analytics traffic visitors acquisition referrers sources campaigns utm">
                                <span><i class="bi bi-graph-up-arrow text-primary me-2"></i> Traffic & Acquisition Analytics</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.bookings.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="bookings orders customers reservations payments">
                                <span><i class="bi bi-calendar2-check-fill text-primary me-2"></i> Bookings & Reservations</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.whatsapp.leads') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="whatsapp leads inquiries click chat support">
                                <span><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Click Leads</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.tours.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="tours inventory safari quad buggy glamping">
                                <span><i class="bi bi-compass-fill text-primary me-2"></i> Tours Inventory</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.pricing.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="pricing matrix rates aed tiers standard vip glamping">
                                <span><i class="bi bi-cash-stack text-info me-2"></i> Pricing Matrix</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.reviews.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="reviews ratings customer feedback stars">
                                <span><i class="bi bi-star-half text-warning me-2"></i> Customer Reviews</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.faqs.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="faqs questions answers help">
                                <span><i class="bi bi-question-circle-fill text-primary me-2"></i> Frequently Asked Questions</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                            <a href="{{ route('admin.settings.google') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-3 border-0 py-2 cmd-entry cmd-item" data-keywords="google tag ads conversion tracking analytics gtm aw-17859624049">
                                <span><i class="bi bi-google text-danger me-2"></i> Google Ads & Tag Integrations</span>
                                <span class="badge bg-light text-muted border">Nav</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-2 ps-3 pe-3 d-flex justify-content-between">
                    <span class="text-muted small"><kbd class="bg-white border text-dark px-1">↑</kbd> <kbd class="bg-white border text-dark px-1">↓</kbd> to navigate, <kbd class="bg-white border text-dark px-1">Enter</kbd> to select</span>
                    <span class="badge bg-white text-muted border">Spotlight</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/vendor/bootstrap/5.3.2/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        // Suppress DataTables alert popups in UI
        if ($.fn.dataTable) {
            $.fn.dataTable.ext.errMode = 'none';
        }

        // Initialize Universal DataTables with column sorting across all admin tables
        $('.table:not(.no-datatable), .datatable').each(function() {
            var $table = $(this);
            if ($table.find('tbody tr').length > 0 && $table.find('tbody td[colspan]').length === 0) {
                if (!$.fn.DataTable.isDataTable($table)) {
                    $table.DataTable({
                        pageLength: 25,
                        ordering: true,
                        responsive: true,
                        order: [], // Preserve natural server-side sort order
                        columnDefs: [
                            { orderable: false, targets: 'no-sort' }
                        ],
                        language: {
                            search: "",
                            searchPlaceholder: "Quick search table records...",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                previous: '<i class="bi bi-chevron-left"></i>',
                                next: '<i class="bi bi-chevron-right"></i>'
                            }
                        },
                        dom: "<'row mb-3 mt-3 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row mt-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                    });
                }
            }
        });

        // 1-Click Interactive AJAX Status Toggles for Tours, FAQs, Reviews, and Blogs
        $(document).on('click', '.ajax-toggle-status', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const toggleUrl = $btn.data('url');
            if (!toggleUrl) return;

            $btn.addClass('opacity-50');

            $.ajax({
                url: toggleUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $btn.removeClass('opacity-50');
                    if (res.success) {
                        const newStatus = res.status;
                        $btn.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        
                        // Update badge colors dynamically
                        $btn.removeClass('bg-success bg-secondary bg-warning bg-danger text-white');
                        if (newStatus === 'active' || newStatus === 'approved' || newStatus === 'published') {
                            $btn.addClass('bg-success text-white');
                        } else if (newStatus === 'pending') {
                            $btn.addClass('bg-warning text-white');
                        } else {
                            $btn.addClass('bg-secondary text-white');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: res.message || 'Status updated successfully.',
                            timer: 2000,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    $btn.removeClass('opacity-50');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update status. Please try again.',
                        timer: 2500,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            });
        });

        // Command Palette (Ctrl+K or Cmd+K)
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#commandPaletteModal').modal('show');
            }
        });

        $('#commandPaletteModal').on('shown.bs.modal', function () {
            $('#cmdInput').val('').focus();
            $('.cmd-entry').show();
        });

        $('#cmdInput').on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            if (!query) {
                $('.cmd-entry').show();
                $('.cmd-section').show();
                return;
            }

            $('.cmd-entry').each(function() {
                const text = $(this).text().toLowerCase();
                const keywords = ($(this).data('keywords') || '').toLowerCase();
                if (text.includes(query) || keywords.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('.cmd-section').each(function() {
                const visibleEntries = $(this).find('.cmd-entry:visible').length;
                $(this).toggle(visibleEntries > 0);
            });
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                confirmButtonColor: '#F58F43'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '<ul style="text-align:left; font-size:13px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
                confirmButtonColor: '#F58F43'
            });
        @endif

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
