@php
    $routeName = Route::currentRouteName() ?? '';
    $path = request()->path();

    // Check if view supplied custom breadcrumbs section
    $hasCustomSection = View::hasSection('breadcrumbs');
    $breadcrumbs = [];

    if (! $hasCustomSection) {
        // Precise map of known routes to hierarchical trail
        $routeMap = [
            // Dashboard
            'admin.dashboard' => [],

            // Analytics
            'admin.analytics.index' => [
                ['label' => 'Analytics & Intelligence'],
            ],

            // Bookings & Reservations
            'admin.bookings.index' => [
                ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                ['label' => 'All Reservations'],
            ],
            'admin.bookings.create' => [
                ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                ['label' => 'Create Manual Booking'],
            ],
            'admin.bookings.show' => [
                ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                ['label' => isset($booking) ? 'Booking #'.($booking->reference ?? $booking->id) : 'Booking Details'],
            ],
            'admin.bookings.drafts' => [
                ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                ['label' => 'Incomplete Drafts Hub'],
            ],
            'admin.pricing.index' => [
                ['label' => 'Bookings', 'url' => route('admin.bookings.index')],
                ['label' => 'Seasonal Pricing Matrix'],
            ],

            // Operations & Fleet
            'admin.operations.index' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Daily Operations Hub'],
            ],
            'admin.operations.reports' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Driver Settlement Reports'],
            ],
            'admin.drivers.index' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Safari Drivers', 'url' => route('admin.drivers.index')],
                ['label' => 'Drivers Roster'],
            ],
            'admin.drivers.create' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Safari Drivers', 'url' => route('admin.drivers.index')],
                ['label' => 'Add Driver'],
            ],
            'admin.drivers.edit' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Safari Drivers', 'url' => route('admin.drivers.index')],
                ['label' => 'Edit Driver'],
            ],
            'admin.vehicles.index' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Vehicle Fleet', 'url' => route('admin.vehicles.index')],
                ['label' => 'Fleet Inventory'],
            ],
            'admin.vehicles.create' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Vehicle Fleet', 'url' => route('admin.vehicles.index')],
                ['label' => 'Add Vehicle'],
            ],
            'admin.vehicles.edit' => [
                ['label' => 'Operations & Fleet', 'url' => route('admin.operations.index')],
                ['label' => 'Vehicle Fleet', 'url' => route('admin.vehicles.index')],
                ['label' => 'Edit Vehicle'],
            ],

            // Tour Catalog & Inventory
            'admin.tours.index' => [
                ['label' => 'Tour Catalog', 'url' => route('admin.tours.index')],
                ['label' => 'Tours Inventory'],
            ],
            'admin.tours.create' => [
                ['label' => 'Tour Catalog', 'url' => route('admin.tours.index')],
                ['label' => 'New Tour'],
            ],
            'admin.tours.edit' => [
                ['label' => 'Tour Catalog', 'url' => route('admin.tours.index')],
                ['label' => isset($tour) ? ($tour->name ?? 'Edit Tour') : 'Edit Tour'],
            ],
            'admin.addons.index' => [
                ['label' => 'Tour Catalog', 'url' => route('admin.tours.index')],
                ['label' => 'Tour Add-ons'],
            ],
            'admin.tiers.index' => [
                ['label' => 'Tour Catalog', 'url' => route('admin.tours.index')],
                ['label' => 'Pricing Tiers'],
            ],

            // Customer Communications & Leads
            'admin.inquiries.index' => [
                ['label' => 'Communications', 'url' => route('admin.inquiries.index')],
                ['label' => 'Contact Inquiries'],
            ],
            'admin.whatsapp.leads' => [
                ['label' => 'Communications', 'url' => route('admin.whatsapp.leads')],
                ['label' => 'WhatsApp Leads'],
            ],
            'admin.reviews.index' => [
                ['label' => 'Communications', 'url' => route('admin.reviews.index')],
                ['label' => 'Customer Reviews'],
            ],

            // Marketing & Promos
            'admin.coupons.index' => [
                ['label' => 'Marketing & Promos', 'url' => route('admin.coupons.index')],
                ['label' => 'Coupons & Promos'],
            ],
            'admin.coupons.create' => [
                ['label' => 'Marketing & Promos', 'url' => route('admin.coupons.index')],
                ['label' => 'New Coupon'],
            ],
            'admin.coupons.edit' => [
                ['label' => 'Marketing & Promos', 'url' => route('admin.coupons.index')],
                ['label' => 'Edit Coupon'],
            ],
            'admin.coupons.popup-settings' => [
                ['label' => 'Marketing & Promos', 'url' => route('admin.coupons.index')],
                ['label' => 'Popups & Social Proof'],
            ],
            'admin.subscribers.index' => [
                ['label' => 'Email Marketing', 'url' => route('admin.subscribers.index')],
                ['label' => 'Subscribers'],
            ],
            'admin.subscriber-groups.index' => [
                ['label' => 'Email Marketing', 'url' => route('admin.subscribers.index')],
                ['label' => 'Subscriber Groups'],
            ],
            'admin.email-templates.index' => [
                ['label' => 'Email Marketing', 'url' => route('admin.subscribers.index')],
                ['label' => 'Email Templates'],
            ],
            'admin.campaigns.index' => [
                ['label' => 'Email Marketing', 'url' => route('admin.campaigns.index')],
                ['label' => 'Campaigns & Analytics'],
            ],
            'admin.campaigns.create' => [
                ['label' => 'Email Marketing', 'url' => route('admin.campaigns.index')],
                ['label' => 'New Campaign'],
            ],
            'admin.campaigns.edit' => [
                ['label' => 'Email Marketing', 'url' => route('admin.campaigns.index')],
                ['label' => 'Edit Campaign'],
            ],
            'admin.campaigns.show' => [
                ['label' => 'Email Marketing', 'url' => route('admin.campaigns.index')],
                ['label' => 'Campaign Analytics'],
            ],

            // Content & Knowledge Portal
            'admin.blogs.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.blogs.index')],
                ['label' => 'Blog Articles'],
            ],
            'admin.blogs.create' => [
                ['label' => 'Content & Portal', 'url' => route('admin.blogs.index')],
                ['label' => 'Write Article'],
            ],
            'admin.blogs.edit' => [
                ['label' => 'Content & Portal', 'url' => route('admin.blogs.index')],
                ['label' => 'Edit Article'],
            ],
            'admin.blog-categories.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.blogs.index')],
                ['label' => 'Blog Categories'],
            ],
            'admin.wiki.index' => [
                ['label' => 'Knowledge Hub', 'url' => route('admin.wiki.index')],
                ['label' => 'Wiki Articles'],
            ],
            'admin.wiki.pillars' => [
                ['label' => 'Knowledge Hub', 'url' => route('admin.wiki.index')],
                ['label' => 'Wiki Pillars'],
            ],
            'admin.wiki.glossary' => [
                ['label' => 'Knowledge Hub', 'url' => route('admin.wiki.index')],
                ['label' => 'Wiki Glossary'],
            ],
            'admin.pages.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.pages.index')],
                ['label' => 'Pages & Content'],
            ],
            'admin.pages.create' => [
                ['label' => 'Content & Portal', 'url' => route('admin.pages.index')],
                ['label' => 'New Page'],
            ],
            'admin.pages.edit' => [
                ['label' => 'Content & Portal', 'url' => route('admin.pages.index')],
                ['label' => 'Edit Page'],
            ],
            'admin.menus.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.menus.index')],
                ['label' => 'Navigation & Menus'],
            ],
            'admin.faqs.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.faqs.index')],
                ['label' => 'FAQs'],
            ],
            'admin.legal.index' => [
                ['label' => 'Content & Portal', 'url' => route('admin.legal.index')],
                ['label' => 'Legal Policies'],
            ],

            // Portal Settings & Integrations
            'admin.settings.general' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'General Identity'],
            ],
            'admin.settings.seo' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'SEO & Metadata'],
            ],
            'admin.settings.mail' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'SMTP Mailer'],
            ],
            'admin.settings.google' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Google Integrations'],
            ],
            'admin.settings.gttd' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Google Things To Do'],
            ],
            'admin.settings.meta' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Meta / Facebook'],
            ],
            'admin.whatsapp.settings' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'WhatsApp Configuration'],
            ],
            'admin.settings.currency' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Currency & Rates'],
            ],
            'admin.settings.languages' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Languages & Locales'],
            ],
            'admin.settings.translations' => [
                ['label' => 'Settings', 'url' => route('admin.settings.general')],
                ['label' => 'Translation APIs'],
            ],
            'admin.profile' => [
                ['label' => 'Account', 'url' => route('admin.profile')],
                ['label' => 'Profile & Security'],
            ],
        ];

        if (isset($routeMap[$routeName])) {
            $breadcrumbs = $routeMap[$routeName];
        } else {
            // Dynamic Intelligent Fallback for future pages
            $segments = request()->segments();
            if (isset($segments[0]) && $segments[0] === 'admin') {
                array_shift($segments);
            }

            $currentUrl = url('/admin');
            foreach ($segments as $index => $segment) {
                $isLast = ($index === count($segments) - 1);
                $currentUrl .= '/' . $segment;

                $friendlyOverrides = [
                    'gttd' => 'Google Things To Do',
                    'seo' => 'SEO & Metadata',
                    'faq' => 'FAQs',
                    'faqs' => 'FAQs',
                    'drafts' => 'Incomplete Drafts Hub',
                    'leads' => 'WhatsApp Leads',
                    'reports' => 'Driver Reports',
                    'bi' => 'Business Intelligence',
                ];

                $label = $friendlyOverrides[strtolower($segment)] ?? ucwords(str_replace(['-', '_'], ' ', $segment));
                if (is_numeric($segment)) {
                    $label = '#' . $segment;
                }

                $breadcrumbs[] = [
                    'label' => ($isLast && View::hasSection('page_title')) ? View::getSection('page_title') : $label,
                    'url' => $isLast ? null : $currentUrl,
                ];
            }
        }
    }
@endphp

@if(!request()->routeIs('admin.dashboard') || $hasCustomSection)
<nav aria-label="Breadcrumbs" class="bg-slate-50/90 backdrop-blur-xs border-b border-slate-200/80 px-4 md:px-6 py-2.5 flex items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2.5 min-w-0 overflow-x-auto py-0.5 sidebar-scroll">
        <!-- Quick Back Button -->
        <button type="button" 
                onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('admin.dashboard') }}'; }" 
                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg shadow-2xs transition shrink-0 cursor-pointer" 
                title="Return to previous screen">
            <i class="bi bi-arrow-left text-xs text-slate-500"></i>
            <span>Back</span>
        </button>

        <span class="h-3.5 w-px bg-slate-200 shrink-0"></span>

        <!-- Dynamic Hierarchy Steps -->
        <ol class="inline-flex items-center gap-1.5 min-w-0 shrink-0 text-slate-500">
            @if($hasCustomSection)
                <li class="inline-flex items-center gap-1.5 flex-wrap">
                    @yield('breadcrumbs')
                </li>
            @else
                <!-- Root / Dashboard -->
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-primary transition font-medium">
                        <i class="bi bi-house-door-fill text-xs text-slate-400"></i>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                </li>

                @foreach($breadcrumbs as $index => $crumb)
                    <li class="inline-flex items-center gap-1.5">
                        <i class="bi bi-chevron-right text-[9px] text-slate-400 shrink-0"></i>
                        @if(!empty($crumb['url']) && !$loop->last)
                            <a href="{{ $crumb['url'] }}" class="text-slate-600 hover:text-primary transition font-medium hover:underline">
                                {{ $crumb['label'] }}
                            </a>
                        @else
                            <span class="font-bold text-slate-800 {{ $loop->last ? 'truncate max-w-[220px] sm:max-w-xs md:max-w-md' : '' }}" {{ $loop->last ? 'aria-current=page' : '' }}>
                                {{ $crumb['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            @endif
        </ol>
    </div>

    <!-- Right Side Context / Direct Action Links -->
    <div class="hidden sm:flex items-center gap-2 shrink-0 text-[11px] text-slate-400 font-medium">
        <a href="{{ url('/') }}" target="_blank" class="hover:text-primary transition inline-flex items-center gap-1" title="View live website">
            <i class="bi bi-globe2 text-slate-400"></i> <span>Live Site</span>
        </a>
    </div>
</nav>
@endif
