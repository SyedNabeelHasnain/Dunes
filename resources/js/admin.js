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
        if (typeof window.resetSpotlightPalette === 'function') {
            window.resetSpotlightPalette();
        }
        setTimeout(() => {
            const input = document.getElementById('cmdInput');
            if (input) {
                input.focus();
                input.select();
            }
        }, 80);
    },

    closeCommandPalette() {
        this.commandPaletteOpen = false;
    }
});

Alpine.start();

// =============================================================================
// 2. DUNES SPOTLIGHT COMMAND PALETTE ENGINE
// =============================================================================

function initSpotlight() {
    let activeCategory = 'all';
    let activeIndex = -1;
    let searchDebounceTimer = null;
    let abortController = null;

    const cmdInput = document.getElementById('cmdInput');
    const dynamicContainer = document.getElementById('dynamicSpotlightResults');
    const cmdSpinner = document.getElementById('cmdSpinner');
    const cmdSearchIcon = document.getElementById('cmdSearchIcon');
    const emptyState = document.getElementById('cmdEmptyState');
    const emptyTitle = document.getElementById('cmdEmptyTitle');
    const categoryPills = document.querySelectorAll('.cmd-pill');

    function getVisibleEntries() {
        return Array.from(document.querySelectorAll('.cmd-entry')).filter(el => {
            return el.offsetParent !== null && window.getComputedStyle(el).display !== 'none';
        });
    }

    function setActiveEntry(index) {
        const visible = getVisibleEntries();
        visible.forEach(el => {
            el.classList.remove('cmd-active', 'bg-primary/10', 'border-primary', 'ring-1', 'ring-primary/40');
        });

        if (visible.length === 0) {
            activeIndex = -1;
            return;
        }

        if (index < 0) index = 0;
        if (index >= visible.length) index = visible.length - 1;

        activeIndex = index;
        const current = visible[activeIndex];
        if (current) {
            current.classList.add('cmd-active', 'bg-primary/10', 'border-primary', 'ring-1', 'ring-primary/40');
            current.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function filterStaticEntries(query) {
        let visibleCount = 0;
        const normalizedQuery = (query || '').toLowerCase().trim();

        document.querySelectorAll('#staticSpotlightSections .cmd-entry').forEach(el => {
            const entryCategory = el.dataset.category || '';
            const section = el.closest('.cmd-section');
            const sectionCategory = section ? section.dataset.section : '';

            const matchesCategory = (
                activeCategory === 'all' ||
                entryCategory === activeCategory ||
                sectionCategory === activeCategory
            );

            const text = (el.textContent + ' ' + (el.dataset.keywords || '')).toLowerCase();
            const matchesQuery = !normalizedQuery || text.includes(normalizedQuery);

            if (matchesCategory && matchesQuery) {
                el.style.display = '';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });

        // Hide/show static sections based on whether they have visible items
        document.querySelectorAll('#staticSpotlightSections .cmd-section').forEach(sec => {
            const visibleChild = sec.querySelectorAll('.cmd-entry:not([style*="display: none"])');
            sec.style.display = visibleChild.length > 0 ? '' : 'none';
        });

        return visibleCount;
    }

    function checkEmptyState(query, staticCount, dynamicCount) {
        if (!emptyState) return;
        const totalVisible = staticCount + dynamicCount;
        if (totalVisible === 0) {
            emptyState.classList.remove('hidden');
            emptyState.style.display = 'block';
            if (emptyTitle) {
                emptyTitle.textContent = query ? `No results found for "${query}"` : 'No matching items';
            }
        } else {
            emptyState.classList.add('hidden');
            emptyState.style.display = 'none';
        }
    }

    function renderDynamicResults(categories) {
        if (!dynamicContainer) return 0;
        dynamicContainer.innerHTML = '';

        if (!categories || categories.length === 0) {
            return 0;
        }

        let totalDynamicItems = 0;

        categories.forEach(cat => {
            if (!cat.items || cat.items.length === 0) return;

            const sectionEl = document.createElement('div');
            sectionEl.className = 'cmd-section';
            sectionEl.dataset.section = cat.key;

            const headerEl = document.createElement('div');
            headerEl.className = 'text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between';
            headerEl.innerHTML = `
                <span class="flex items-center gap-1.5"><i class="${cat.icon || 'bi bi-database-fill text-primary'}"></i> ${cat.label}</span>
                <span class="text-[10px] text-emerald-600 font-mono bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">${cat.items.length} live records</span>
            `;
            sectionEl.appendChild(headerEl);

            const listEl = document.createElement('div');
            listEl.className = 'space-y-1.5';

            cat.items.forEach(item => {
                totalDynamicItems++;
                const itemEl = document.createElement('div');
                itemEl.className = 'cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer';
                itemEl.dataset.keywords = `${item.title} ${item.subtitle} ${item.badge || ''}`;
                itemEl.dataset.category = cat.key;

                const badgeColorClass = item.badge_color === 'emerald' ? 'bg-emerald-100 text-emerald-800'
                    : item.badge_color === 'amber' ? 'bg-amber-100 text-amber-800'
                    : item.badge_color === 'rose' ? 'bg-rose-100 text-rose-800'
                    : item.badge_color === 'sky' ? 'bg-sky-100 text-sky-800'
                    : item.badge_color === 'indigo' ? 'bg-indigo-100 text-indigo-800'
                    : 'bg-slate-100 text-slate-800';

                let previewHtml = '';
                if (item.preview_url) {
                    previewHtml = `
                        <a href="${item.preview_url}" target="_blank" class="p-1.5 text-slate-400 hover:text-cyan-600 rounded-lg hover:bg-white transition" title="Preview on live website" onclick="event.stopPropagation();">
                            <i class="bi bi-box-arrow-up-right text-xs"></i>
                        </a>
                    `;
                }

                itemEl.innerHTML = `
                    <div class="flex items-center justify-between p-2.5">
                        <a href="${item.url}" class="flex items-center gap-3 flex-1 min-w-0">
                            <i class="${item.icon || 'bi bi-dot'} text-lg shrink-0"></i>
                            <div class="truncate">
                                <div class="flex items-center gap-2">
                                    <strong class="text-xs font-bold text-slate-900 group-hover:text-primary truncate">${item.title}</strong>
                                    ${item.badge ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${badgeColorClass} shrink-0">${item.badge}</span>` : ''}
                                </div>
                                <span class="text-[11px] text-slate-500 truncate block">${item.subtitle}</span>
                            </div>
                        </a>
                        <div class="flex items-center gap-1 shrink-0 ms-2">
                            ${previewHtml}
                            <a href="${item.url}" class="p-1 text-slate-300 group-hover:text-primary transition">
                                <i class="bi bi-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                `;

                listEl.appendChild(itemEl);
            });

            sectionEl.appendChild(listEl);
            dynamicContainer.appendChild(sectionEl);
        });

        return totalDynamicItems;
    }

    async function fetchDynamicSearch(query) {
        if (!query || query.length < 2) {
            if (dynamicContainer) dynamicContainer.innerHTML = '';
            if (cmdSpinner) cmdSpinner.classList.add('hidden');
            if (cmdSearchIcon) cmdSearchIcon.classList.remove('hidden');
            return 0;
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        if (cmdSpinner) cmdSpinner.classList.remove('hidden');
        if (cmdSearchIcon) cmdSearchIcon.classList.add('hidden');

        try {
            const url = `/admin/api/spotlight?q=${encodeURIComponent(query)}&category=${encodeURIComponent(activeCategory)}`;
            const res = await fetch(url, {
                signal: abortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('Search failed');
            const data = await res.json();
            const count = renderDynamicResults(data.categories || []);
            return count;
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.debug('Spotlight fetch error:', err);
            }
            return 0;
        } finally {
            if (cmdSpinner) cmdSpinner.classList.add('hidden');
            if (cmdSearchIcon) cmdSearchIcon.classList.remove('hidden');
        }
    }

    // Input Handler with Debounce
    if (cmdInput) {
        cmdInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            const staticCount = filterStaticEntries(query);

            clearTimeout(searchDebounceTimer);
            if (query.length >= 2) {
                searchDebounceTimer = setTimeout(async () => {
                    const dynamicCount = await fetchDynamicSearch(query);
                    checkEmptyState(query, staticCount, dynamicCount);
                    setActiveEntry(0);
                }, 180);
            } else {
                if (dynamicContainer) dynamicContainer.innerHTML = '';
                checkEmptyState(query, staticCount, 0);
                setActiveEntry(0);
            }
        });

        // Keyboard Navigation (ArrowUp, ArrowDown, Enter)
        cmdInput.addEventListener('keydown', (e) => {
            const visible = getVisibleEntries();
            if (visible.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActiveEntry(activeIndex + 1 >= visible.length ? 0 : activeIndex + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActiveEntry(activeIndex - 1 < 0 ? visible.length - 1 : activeIndex - 1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < visible.length) {
                    const activeEl = visible[activeIndex];
                    const link = activeEl.querySelector('a');
                    if (link) {
                        link.click();
                    } else {
                        activeEl.click();
                    }
                }
            }
        });
    }

    // Category Tabs Switching
    categoryPills.forEach(btn => {
        btn.addEventListener('click', () => {
            activeCategory = btn.dataset.category || 'all';

            categoryPills.forEach(p => {
                p.classList.remove('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');
                p.classList.add('text-slate-600', 'font-medium');
            });
            btn.classList.remove('text-slate-600', 'font-medium');
            btn.classList.add('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');

            const query = cmdInput ? cmdInput.value.trim() : '';
            const staticCount = filterStaticEntries(query);

            if (query.length >= 2) {
                fetchDynamicSearch(query).then(dynamicCount => {
                    checkEmptyState(query, staticCount, dynamicCount);
                    setActiveEntry(0);
                });
            } else {
                checkEmptyState(query, staticCount, 0);
                setActiveEntry(0);
            }
        });
    });

    // Reset when palette opens
    window.resetSpotlightPalette = function() {
        activeCategory = 'all';
        activeIndex = -1;
        if (cmdInput) {
            cmdInput.value = '';
        }
        categoryPills.forEach((p, idx) => {
            if (idx === 0) {
                p.classList.add('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');
                p.classList.remove('text-slate-600', 'font-medium');
            } else {
                p.classList.remove('bg-primary', 'text-white', 'shadow-2xs', 'font-bold');
                p.classList.add('text-slate-600', 'font-medium');
            }
        });
        if (dynamicContainer) dynamicContainer.innerHTML = '';
        filterStaticEntries('');
        checkEmptyState('', 1, 0);
        setTimeout(() => setActiveEntry(0), 100);
    };

    // Global Shortcut Listener (Ctrl+K, Cmd+K, Escape)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const store = Alpine.store('admin');
            if (store) {
                if (store.commandPaletteOpen) {
                    store.closeCommandPalette();
                } else {
                    store.openCommandPalette();
                }
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

    // Initial pass to prepare state
    filterStaticEntries('');
}

document.addEventListener('DOMContentLoaded', () => {
    initSpotlight();
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

        // Strip native required attribute so hidden textarea never blocks form submission
        textarea.removeAttribute('required');

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
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        });

        const form = textarea.closest('form');
        if (form) {
            form.setAttribute('novalidate', 'true');
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
