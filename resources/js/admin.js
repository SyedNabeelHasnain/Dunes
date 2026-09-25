/**
 * Dunes Discovery Tourism - Admin CMS State & UI Architecture
 * Tailwind CSS v4 & Alpine.js Enterprise Portal Engine
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;

// =============================================================================
// 1. ALPINE.JS REACTIVE ADMIN STORE
// =============================================================================

Alpine.store('admin', {
    sidebarCollapsed: localStorage.getItem('sidebarState') === 'collapsed',
    mobileSidebarOpen: false,
    commandPaletteOpen: false,
    searchQuery: '',
    activeVisitorsCount: '0 Online',
    activeVisitorsList: [],
    visitorsLoading: false,

    init() {
        if (window.innerWidth >= 1024 && this.sidebarCollapsed) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    },

    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarState', this.sidebarCollapsed ? 'collapsed' : 'expanded');
        document.documentElement.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
    },

    toggleMobileSidebar() {
        this.mobileSidebarOpen = !this.mobileSidebarOpen;
        document.body.style.overflow = this.mobileSidebarOpen ? 'hidden' : '';
    },

    closeMobileSidebar() {
        this.mobileSidebarOpen = false;
        document.body.style.overflow = '';
    },

    openCommandPalette() {
        this.commandPaletteOpen = true;
        this.searchQuery = '';
        setTimeout(() => {
            const input = document.getElementById('cmdInput');
            if (input) input.focus();
        }, 80);
    },

    closeCommandPalette() {
        this.commandPaletteOpen = false;
    }
});

Alpine.start();

// =============================================================================
// 2. COMMAND PALETTE KEYBOARD LISTENER (Ctrl+K, Cmd+K, Escape)
// =============================================================================

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        const store = Alpine.store('admin');
        if (store.commandPaletteOpen) {
            store.closeCommandPalette();
        } else {
            store.openCommandPalette();
        }
    } else if (e.key === 'Escape') {
        const store = Alpine.store('admin');
        if (store && store.commandPaletteOpen) {
            store.closeCommandPalette();
        }
        if (store && store.mobileSidebarOpen) {
            store.closeMobileSidebar();
        }
    }
});

// Live filtering inside Command Palette
document.addEventListener('input', (e) => {
    if (e.target && e.target.id === 'cmdInput') {
        const query = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.cmd-entry').forEach((el) => {
            const text = (el.textContent + ' ' + (el.dataset.keywords || '')).toLowerCase();
            if (!query || text.includes(query)) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });
        document.querySelectorAll('.cmd-section').forEach((sec) => {
            const visibleEntries = sec.querySelectorAll('.cmd-entry:not([style*="display: none"])');
            sec.style.display = visibleEntries.length > 0 ? '' : 'none';
        });
    }
});

// =============================================================================
// 3. GLOBAL LOADER CONTROLS
// =============================================================================

window.showLoader = function() {
    const loader = document.getElementById('appLoader');
    if (loader) loader.style.display = 'flex';
};

window.hideLoader = function() {
    const loader = document.getElementById('appLoader');
    if (loader) loader.style.display = 'none';
};

// =============================================================================
// 4. ACTIVE ONLINE VISITORS POLLER
// =============================================================================

window.initVisitorsPoller = function(endpointUrl) {
    if (!endpointUrl) return;

    let lastPayload = '';

    function updateActiveVisitors() {
        if (document.hidden) return;

        fetch(endpointUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const currentPayload = JSON.stringify(data);
            if (currentPayload === lastPayload) return;
            lastPayload = currentPayload;

            const adminStore = Alpine.store('admin');
            if (adminStore) {
                adminStore.activeVisitorsCount = `${data.count || 0} Online`;
                adminStore.activeVisitorsList = data.visitors || [];
            }

            const countEl = document.getElementById('activeVisitorsCount');
            if (countEl) countEl.textContent = `${data.count || 0} Online`;
        })
        .catch(err => console.debug("Active visitors poller:", err));
    }

    updateActiveVisitors();
    setInterval(updateActiveVisitors, 15000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) updateActiveVisitors();
    });
};

// =============================================================================
// 5. CACHE PURGE AJAX HANDLER
// =============================================================================

window.initCachePurgeHandler = function(purgeUrl, csrfToken) {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.clear-cache-trigger');
        if (!trigger) return;
        e.preventDefault();

        if (window.Swal) {
            window.Swal.fire({
                title: 'Purging Caches...',
                text: 'Flushing views, routes, config, and query caches.',
                allowOutsideClick: false,
                didOpen: () => window.Swal.showLoading()
            });
        }

        fetch(purgeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _token: csrfToken })
        })
        .then(res => res.json())
        .then(res => {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Cache Purged',
                    text: res.message || 'System caches cleared successfully.',
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        })
        .catch(() => {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to purge cache. Please try again.',
                    confirmButtonColor: '#F69044'
                });
            }
        });
    });
};

// =============================================================================
// 6. UNIVERSAL SWEETALERT2 CONFIRMATION HANDLER
// =============================================================================

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form || (!form.classList.contains('delete-form') && !form.dataset.confirm)) return;
    if (form.dataset.confirmed === 'true') return;

    e.preventDefault();
    const message = form.dataset.confirm || form.querySelector('[type="submit"]')?.title || 'Are you sure you want to delete this record? This action cannot be undone.';

    if (window.Swal) {
        window.Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    } else if (confirm(message)) {
        form.dataset.confirmed = 'true';
        form.submit();
    }
});

// =============================================================================
// 7. INTERACTIVE 1-CLICK AJAX STATUS BADGE TOGGLES
// =============================================================================

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.ajax-toggle-status');
    if (!btn) return;
    e.preventDefault();

    const toggleUrl = btn.dataset.url;
    if (!toggleUrl) return;

    btn.style.opacity = '0.5';
    btn.style.pointerEvents = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(toggleUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ _token: csrfToken })
    })
    .then(res => res.json())
    .then(res => {
        btn.style.opacity = '1';
        btn.style.pointerEvents = '';

        if (res.success) {
            const newStatus = res.status;
            btn.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            // Update badge classes dynamically
            btn.className = btn.className
                .replace(/bg-(emerald|green|amber|yellow|slate|gray|rose|red)-[0-9]+/g, '')
                .replace(/text-(emerald|green|amber|yellow|slate|gray|rose|red)-[0-9]+/g, '');

            if (['active', 'approved', 'published'].includes(newStatus)) {
                btn.classList.add('bg-emerald-100', 'text-emerald-800');
            } else if (newStatus === 'pending') {
                btn.classList.add('bg-amber-100', 'text-amber-800');
            } else {
                btn.classList.add('bg-slate-100', 'text-slate-800');
            }

            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: res.message || 'Status updated successfully.',
                    timer: 2000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
        }
    })
    .catch(() => {
        btn.style.opacity = '1';
        btn.style.pointerEvents = '';
        if (window.Swal) {
            window.Swal.fire({
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

// =============================================================================
// 8. UNIVERSAL QUILL WYSIWYG AUTO-INITIALIZER
// =============================================================================

window.initQuillEditors = function() {
    if (typeof window.Quill === 'undefined') return;

    document.querySelectorAll('textarea.wysiwyg-editor').forEach((textarea) => {
        if (textarea.dataset.quillInitialized === 'true') return;
        textarea.dataset.quillInitialized = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'quill-editor-container mb-3 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden';
        textarea.parentNode.insertBefore(wrapper, textarea);
        textarea.style.display = 'none';

        const quill = new window.Quill(wrapper, {
            theme: 'snow',
            placeholder: textarea.getAttribute('placeholder') || 'Compose rich content...',
            modules: {
                toolbar: [
                    [{ 'header': [2, 3, 4, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['blockquote', 'code-block'],
                    ['link', 'clean']
                ]
            }
        });

        if (textarea.value) {
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        }

        quill.on('text-change', () => {
            textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });

        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            });
        }
    });
};

// =============================================================================
// 9. UNIVERSAL DATATABLES TAILWIND INITIALIZER
// =============================================================================

window.initTailwindDataTables = function() {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) return;

    // Suppress legacy popup alerts
    window.jQuery.fn.dataTable.ext.errMode = 'none';

    window.jQuery('.table:not(.no-datatable), .datatable').each(function() {
        const $table = window.jQuery(this);
        if ($table.find('tbody tr').length > 0 && $table.find('tbody td[colspan]').length === 0) {
            if (!window.jQuery.fn.DataTable.isDataTable($table)) {
                // If table is wrapped in a redundant single overflow container, unwrap it
                // so the DataTables wrapper controls its own scroll area and prevents
                // toolbar buttons or popover dropdowns from being clipped
                if ($table.parent().hasClass('overflow-x-auto') && $table.parent().children().length === 1) {
                    $table.unwrap();
                }

                $table.DataTable({
                    pageLength: 25,
                    ordering: true,
                    order: [],
                    autoWidth: false,
                    responsive: false,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-excel me-1 text-emerald-600"></i>Excel',
                            className: 'btn btn-sm bg-white border border-slate-200 shadow-sm rounded-full px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-primary transition',
                            exportOptions: { columns: ':visible:not(.no-export):not(.no-sort)' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="bi bi-file-earmark-pdf me-1 text-rose-600"></i>PDF',
                            className: 'btn btn-sm bg-white border border-slate-200 shadow-sm rounded-full px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-primary transition',
                            exportOptions: { columns: ':visible:not(.no-export):not(.no-sort)' },
                            orientation: 'landscape',
                            pageSize: 'A4'
                        },
                        {
                            extend: 'print',
                            text: '<i class="bi bi-printer me-1 text-slate-700"></i>Print',
                            className: 'btn btn-sm bg-white border border-slate-200 shadow-sm rounded-full px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-primary transition',
                            exportOptions: { columns: ':visible:not(.no-export):not(.no-sort)' }
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="bi bi-columns-gap me-1 text-slate-500"></i>Columns',
                            className: 'btn btn-sm bg-white border border-slate-200 shadow-sm rounded-full px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-primary transition',
                            popoverTitle: 'Toggle Visible Columns',
                            columns: ':not(.no-colvis)'
                        }
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search records...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>'
                        }
                    },
                    dom: "<'flex flex-col md:flex-row items-center justify-between gap-4 mb-4'<'flex items-center gap-2'l><'flex items-center gap-2'B><'w-full md:w-auto'f>>" +
                         "<'overflow-x-auto rounded-xl border border-slate-200'<'min-w-full'tr>>" +
                         "<'flex flex-col md:flex-row items-center justify-between gap-4 mt-4'<'text-sm text-slate-500'i><'flex items-center gap-1'p>>"
                });
            }
        }
    });
};

// Auto-run on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.initQuillEditors();
    window.initTailwindDataTables();
});
