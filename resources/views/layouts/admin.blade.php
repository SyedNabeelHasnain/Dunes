<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Dashboard') | Admin CMS | Dunes Discovery Tourism</title>
    
    <!-- Vector Icons Suite & Quill WYSIWYG CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">

    <!-- Admin Tailwind CSS v4 & Alpine.js Enterprise Portal Engine via Vite -->
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col" x-data>

    <!-- Global Processing Loader Overlay -->
    <div id="appLoader" class="fixed inset-0 z-50 bg-white/80 backdrop-blur-xs hidden flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-slate-200 border-t-primary rounded-full animate-spin mb-3"></div>
        <div class="font-bold text-primary text-sm tracking-wide">Processing...</div>
    </div>

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div x-show="$store.admin.mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.admin.closeMobileSidebar()" 
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden"
         style="display: none;"></div>

    <!-- Mobile Slide-over Sidebar Drawer -->
    <aside x-show="$store.admin.mobileSidebarOpen"
           x-transition:enter="transition-transform ease-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed top-0 bottom-0 left-0 z-50 w-72 bg-slate-950 text-slate-200 shadow-2xl flex flex-col lg:hidden"
           style="display: none;">
        <!-- Brand Header -->
        <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800/80 shrink-0">
            <h4 class="text-white font-extrabold tracking-tight text-lg mb-0">DUNES<span class="text-primary">CMS</span></h4>
            <button type="button" @click="$store.admin.closeMobileSidebar()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>
        <!-- Scrollable Navigation Links -->
        <div class="flex-1 overflow-y-auto p-3 space-y-1 sidebar-scroll text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.dashboard*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.analytics*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-graph-up-arrow text-base"></i> <span>Analytics</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.bookings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-calendar2-check-fill text-base"></i> <span>Bookings</span>
            </a>
            <a href="{{ route('admin.operations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.operations*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-truck-flatbed text-amber-400 text-base"></i> <span>Daily Operations</span>
            </a>
            <a href="{{ route('admin.inquiries.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.inquiries*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-envelope-paper-fill text-base"></i> <span>Inquiries</span>
            </a>
            <a href="{{ route('admin.whatsapp.leads') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.whatsapp.leads') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-whatsapp text-emerald-400 text-base"></i> <span>WhatsApp Leads</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Tour Management</div>
            <a href="{{ route('admin.tours.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.tours*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-compass-fill text-base"></i> <span>Tours Inventory</span>
            </a>
            <a href="{{ route('admin.addons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.addons*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-puzzle-fill text-base"></i> <span>Tour Add-ons</span>
            </a>
            <a href="{{ route('admin.tiers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.tiers*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-layers-fill text-base"></i> <span>Pricing Tiers</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.pricing*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-cash-stack text-base"></i> <span>Pricing Matrix</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Marketing & Content</div>
            <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.coupons.index') || request()->routeIs('admin.coupons.create') || request()->routeIs('admin.coupons.edit') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-ticket-perforated-fill text-base"></i> <span>Coupons & Promos</span>
            </a>
            <a href="{{ route('admin.coupons.popup-settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.coupons.popup-settings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-gift-fill text-amber-400 text-base"></i> <span>Welcome Offer & Banner</span>
            </a>
            <a href="{{ route('admin.blogs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.blogs*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-journal-richtext text-base"></i> <span>Blog Articles</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.reviews*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-star-half text-base"></i> <span>Customer Reviews</span>
            </a>
            <a href="{{ route('admin.faqs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.faqs*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-question-circle-fill text-base"></i> <span>FAQs</span>
            </a>
            <a href="{{ route('admin.legal.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.legal*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-shield-check text-base"></i> <span>Legal Policies</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Email Marketing</div>
            <a href="{{ route('admin.subscribers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.subscribers*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-people-fill text-sky-400 text-base"></i> <span>Subscribers & Lists</span>
            </a>
            <a href="{{ route('admin.subscriber-groups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.subscriber-groups*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-diagram-3-fill text-cyan-400 text-base"></i> <span>Subscriber Groups</span>
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.email-templates*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-envelope-paper-heart-fill text-rose-400 text-base"></i> <span>Email Templates</span>
            </a>
            <a href="{{ route('admin.campaigns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.campaigns*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-megaphone-fill text-amber-400 text-base"></i> <span>Campaigns & Analytics</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Portal Settings & SEO</div>
            <a href="{{ route('admin.settings.general') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.general*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-sliders text-amber-400 text-base"></i> <span>General Identity</span>
            </a>
            <a href="{{ route('admin.settings.seo') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.seo*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-search text-sky-400 text-base"></i> <span>SEO & Metadata</span>
            </a>
            <a href="{{ route('admin.settings.marketing') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.marketing*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-megaphone text-rose-400 text-base"></i> <span>Marketing & Promos</span>
            </a>
            <a href="{{ route('admin.settings.mail') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.mail*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-envelope-gear text-sky-400 text-base"></i> <span>SMTP & Mailer</span>
            </a>
            <a href="{{ route('admin.settings.google') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.google*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-google text-rose-400 text-base"></i> <span>Google Integrations</span>
            </a>
            <a href="{{ route('admin.settings.meta') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.meta*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-meta text-sky-400 text-base"></i> <span>Meta / Facebook</span>
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.whatsapp.settings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-gear-wide-connected text-emerald-400 text-base"></i> <span>WhatsApp Setup</span>
            </a>
            <a href="{{ route('admin.settings.currency') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.currency*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-currency-exchange text-cyan-400 text-base"></i> <span>Currency & Rates</span>
            </a>
            <button type="button" class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition clear-cache-trigger">
                <i class="bi bi-arrow-repeat text-amber-400 text-base"></i> <span>Purge Cache</span>
            </button>

            <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">External Tools</div>
            <a href="{{ url('/rate-card') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition">
                <i class="bi bi-file-earmark-pdf-fill text-amber-400 text-base"></i> <span>Live Rate Card</span>
            </a>
            <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition">
                <i class="bi bi-box-arrow-up-right text-cyan-400 text-base"></i> <span>Visit Website</span>
            </a>
        </div>
        <!-- Mobile Sidebar Footer -->
        <div class="p-3 border-t border-slate-800 bg-slate-900/60 shrink-0 space-y-2">
            <button type="button" class="w-full flex items-center justify-center gap-2 py-2 px-3 border border-slate-700/80 hover:bg-slate-800 text-slate-300 rounded-full text-xs font-semibold transition clear-cache-trigger">
                <i class="bi bi-arrow-repeat text-amber-400"></i> <span>Purge Caches</span>
            </button>
            <a href="#" class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-full text-xs font-bold shadow-sm transition" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-power"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Desktop Collapsible Sidebar -->
    <aside :class="$store.admin.sidebarCollapsed ? 'w-20' : 'w-64'"
           class="hidden lg:flex fixed top-0 bottom-0 left-0 z-30 bg-slate-950 text-slate-200 shadow-xl transition-all duration-300 ease-in-out flex-col">
        <!-- Brand Header -->
        <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800/80 shrink-0">
            <div x-show="!$store.admin.sidebarCollapsed" class="transition-opacity duration-200">
                <h4 class="text-white font-extrabold tracking-tight text-lg mb-0 whitespace-nowrap">DUNES<span class="text-primary">CMS</span></h4>
            </div>
            <button type="button" @click="$store.admin.toggleSidebar()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-900 transition mx-auto" :title="$store.admin.sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                <i class="bi bi-list text-xl"></i>
            </button>
        </div>
        <!-- Scrollable Navigation -->
        <div class="flex-1 overflow-y-auto py-3 px-2 space-y-1 sidebar-scroll text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.dashboard*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Dashboard' : ''">
                <i class="bi bi-grid-1x2-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Dashboard</span>
            </a>
            <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.analytics*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Analytics' : ''">
                <i class="bi bi-graph-up-arrow text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Analytics</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.bookings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Bookings' : ''">
                <i class="bi bi-calendar2-check-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Bookings</span>
            </a>
            <a href="{{ route('admin.operations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.operations*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Daily Operations' : ''">
                <i class="bi bi-truck-flatbed text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Daily Operations</span>
            </a>
            <a href="{{ route('admin.inquiries.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.inquiries*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Inquiries' : ''">
                <i class="bi bi-envelope-paper-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Inquiries</span>
            </a>
            <a href="{{ route('admin.whatsapp.leads') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.whatsapp.leads') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'WhatsApp Leads' : ''">
                <i class="bi bi-whatsapp text-emerald-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">WhatsApp Leads</span>
            </a>

            <div x-show="!$store.admin.sidebarCollapsed" class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Tour Management</div>
            <a href="{{ route('admin.tours.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.tours*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Tours Inventory' : ''">
                <i class="bi bi-compass-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Tours Inventory</span>
            </a>
            <a href="{{ route('admin.addons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.addons*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Tour Add-ons' : ''">
                <i class="bi bi-puzzle-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Tour Add-ons</span>
            </a>
            <a href="{{ route('admin.tiers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.tiers*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Pricing Tiers' : ''">
                <i class="bi bi-layers-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Pricing Tiers</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.pricing*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Pricing Matrix' : ''">
                <i class="bi bi-cash-stack text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Pricing Matrix</span>
            </a>

            <div x-show="!$store.admin.sidebarCollapsed" class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Marketing & Content</div>
            <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.coupons.index') || request()->routeIs('admin.coupons.create') || request()->routeIs('admin.coupons.edit') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Coupons & Promos' : ''">
                <i class="bi bi-ticket-perforated-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Coupons & Promos</span>
            </a>
            <a href="{{ route('admin.coupons.popup-settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.coupons.popup-settings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Welcome Offer & Banner' : ''">
                <i class="bi bi-gift-fill text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Welcome Offer</span>
            </a>
            <a href="{{ route('admin.blogs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.blogs*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Blog Articles' : ''">
                <i class="bi bi-journal-richtext text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Blog Articles</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.reviews*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Customer Reviews' : ''">
                <i class="bi bi-star-half text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Customer Reviews</span>
            </a>
            <a href="{{ route('admin.faqs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.faqs*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'FAQs' : ''">
                <i class="bi bi-question-circle-fill text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">FAQs</span>
            </a>
            <a href="{{ route('admin.legal.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.legal*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Legal Policies' : ''">
                <i class="bi bi-shield-check text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Legal Policies</span>
            </a>

            <div x-show="!$store.admin.sidebarCollapsed" class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Email Marketing</div>
            <a href="{{ route('admin.subscribers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.subscribers*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Subscribers' : ''">
                <i class="bi bi-people-fill text-sky-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Subscribers</span>
            </a>
            <a href="{{ route('admin.subscriber-groups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.subscriber-groups*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Subscriber Groups' : ''">
                <i class="bi bi-diagram-3-fill text-cyan-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Subscriber Groups</span>
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.email-templates*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Email Templates' : ''">
                <i class="bi bi-envelope-paper-heart-fill text-rose-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Email Templates</span>
            </a>
            <a href="{{ route('admin.campaigns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.campaigns*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Campaigns' : ''">
                <i class="bi bi-megaphone-fill text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Campaigns</span>
            </a>

            <div x-show="!$store.admin.sidebarCollapsed" class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Portal Settings & SEO</div>
            <a href="{{ route('admin.settings.general') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.general*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'General Settings' : ''">
                <i class="bi bi-sliders text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">General Identity</span>
            </a>
            <a href="{{ route('admin.settings.seo') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.seo*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'SEO & Metadata' : ''">
                <i class="bi bi-search text-sky-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">SEO & Metadata</span>
            </a>
            <a href="{{ route('admin.settings.marketing') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.marketing*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Marketing' : ''">
                <i class="bi bi-megaphone text-rose-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Marketing</span>
            </a>
            <a href="{{ route('admin.settings.mail') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.mail*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'SMTP Mailer' : ''">
                <i class="bi bi-envelope-gear text-sky-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">SMTP & Mailer</span>
            </a>
            <a href="{{ route('admin.settings.google') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.google*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Google Integrations' : ''">
                <i class="bi bi-google text-rose-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Google Setup</span>
            </a>
            <a href="{{ route('admin.settings.meta') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.meta*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Meta / Facebook' : ''">
                <i class="bi bi-meta text-sky-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Meta / Facebook</span>
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.whatsapp.settings*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'WhatsApp Setup' : ''">
                <i class="bi bi-gear-wide-connected text-emerald-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">WhatsApp Setup</span>
            </a>
            <a href="{{ route('admin.settings.currency') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.currency*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Currency & Rates' : ''">
                <i class="bi bi-currency-exchange text-cyan-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Currency & Rates</span>
            </a>
            <button type="button" class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition clear-cache-trigger" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Purge Cache' : ''">
                <i class="bi bi-arrow-repeat text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Purge Cache</span>
            </button>

            <div x-show="!$store.admin.sidebarCollapsed" class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">External Links</div>
            <a href="{{ url('/rate-card') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Live Rate Card' : ''">
                <i class="bi bi-file-earmark-pdf-fill text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Rate Card</span>
            </a>
            <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Live Website' : ''">
                <i class="bi bi-box-arrow-up-right text-cyan-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">View Website</span>
            </a>
        </div>
        <!-- Desktop Sidebar Footer -->
        <div class="p-3 border-t border-slate-800 bg-slate-900/60 shrink-0 space-y-2">
            <button type="button" class="w-full flex items-center justify-center gap-2 py-2 px-3 border border-slate-700/80 hover:bg-slate-800 text-slate-300 rounded-full text-xs font-semibold transition clear-cache-trigger" :title="$store.admin.sidebarCollapsed ? 'Purge Cache' : ''">
                <i class="bi bi-arrow-repeat text-amber-400"></i> <span x-show="!$store.admin.sidebarCollapsed">Purge Caches</span>
            </button>
            <a href="#" class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-full text-xs font-bold shadow-sm transition" :title="$store.admin.sidebarCollapsed ? 'Sign Out' : ''" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-power"></i> <span x-show="!$store.admin.sidebarCollapsed">Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Pinned Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Main Content Area Wrapper -->
    <div :class="$store.admin.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" class="min-h-screen transition-all duration-300 ease-in-out flex flex-col flex-1">
        <!-- Top Navbar -->
        <header class="sticky top-0 z-20 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 md:px-6 py-3 shrink-0 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button type="button" @click="$store.admin.toggleMobileSidebar()" class="lg:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Toggle Navigation Menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <h1 class="text-lg md:text-xl font-extrabold text-slate-900 capitalize tracking-tight mb-0">@yield('page_title', 'Dashboard')</h1>
            </div>

            <!-- Top Actions Toolbar -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Jump to Command Palette Button -->
                <button type="button" @click="$store.admin.openCommandPalette()" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-xs font-semibold text-slate-600 shadow-2xs transition">
                    <i class="bi bi-search text-primary"></i>
                    <span>Jump to...</span>
                    <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] text-slate-500 font-mono shadow-2xs">Ctrl K</kbd>
                </button>

                <!-- Purge Cache Button -->
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-xs font-semibold text-slate-600 shadow-2xs transition clear-cache-trigger" title="Purge application views, routes, and config caches">
                    <i class="bi bi-arrow-repeat text-amber-500"></i>
                    <span class="hidden md:inline">Purge Cache</span>
                </button>

                <!-- Active Online Visitors Widget Popover -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs text-xs font-bold text-primary transition" title="Active human visitors in last 5 minutes">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span id="activeVisitorsCount" x-text="$store.admin.activeVisitorsCount">0 Online</span>
                    </button>
                    <!-- Visitors Popover Dropdown -->
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3 z-30 text-xs" style="display: none;">
                        <div class="font-bold text-slate-800 pb-2 border-b border-slate-100 flex items-center justify-between">
                            <span>Live Visitors (Last 5m)</span>
                            <span class="text-primary font-mono text-[11px]" x-text="$store.admin.activeVisitorsCount"></span>
                        </div>
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 pt-1" id="activeVisitorsList">
                            <template x-if="$store.admin.activeVisitorsList && $store.admin.activeVisitorsList.length > 0">
                                <div>
                                    <template x-for="(v, index) in $store.admin.activeVisitorsList" :key="index">
                                        <div class="py-2 space-y-0.5">
                                            <div class="flex items-center justify-between font-mono text-[11px]">
                                                <span class="font-semibold text-slate-800" x-text="v.client_ip"></span>
                                                <span class="text-slate-400" x-text="new Date(v.request_timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                            </div>
                                            <div class="text-primary truncate font-medium" x-text="(v.city || 'Unknown') + ', ' + (v.country || '')"></div>
                                            <div class="text-slate-500 truncate" x-text="v.request_uri"></div>
                                            <div class="text-[10px] text-slate-400" x-text="v.device_type + ' (' + v.browser_name + ')'"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!$store.admin.activeVisitorsList || $store.admin.activeVisitorsList.length === 0">
                                <div class="text-center text-slate-400 py-3">No active human visitors detected</div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Admin User Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex items-center gap-2 p-1 md:px-3 md:py-1.5 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition">
                        <div class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="hidden md:inline text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <i class="bi bi-chevron-down text-[10px] text-slate-400 hidden md:inline"></i>
                    </button>
                    <!-- User Dropdown Menu -->
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-30 text-xs" style="display: none;">
                        <div class="px-3 py-2 border-b border-slate-100 mb-1">
                            <div class="font-bold text-slate-900 text-sm">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@dunesdiscoverytourism.com' }}</div>
                        </div>
                        <a href="{{ route('admin.profile') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-medium transition">
                            <i class="bi bi-person-gear text-slate-400"></i> My Profile & Security
                        </a>
                        <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-medium transition">
                            <i class="bi bi-box-arrow-up-right text-cyan-500"></i> View Live Website
                        </a>
                        <button type="button" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-medium transition clear-cache-trigger">
                            <i class="bi bi-arrow-repeat text-amber-500"></i> Purge All Caches
                        </button>
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="#" class="flex items-center gap-2 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold transition" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-power"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Yielded Content -->
        <main class="flex-1 p-4 md:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <!-- Spotlight Command Palette Modal (Ctrl + K / Cmd + K) -->
    <div x-show="$store.admin.commandPaletteOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 md:pt-24 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        <div @click.outside="$store.admin.closeCommandPalette()"
             x-transition:enter="transition-all ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[80vh]">
            <!-- Header Search Input -->
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
                <i class="bi bi-search text-primary text-lg ms-2"></i>
                <input type="text" id="cmdInput" placeholder="Search actions, tours, bookings, or pages... (Esc to close)" class="w-full bg-transparent border-0 outline-none text-slate-800 text-base font-semibold placeholder:text-slate-400 placeholder:font-normal focus:ring-0">
                <button type="button" @click="$store.admin.closeCommandPalette()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- Search Results Scrollable Body -->
            <div class="p-4 overflow-y-auto space-y-4 flex-1">
                <!-- Quick Actions Section -->
                <div class="cmd-section" data-section="actions">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1">Quick Actions</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="cmd-entry" data-keywords="add tour create new tour">
                            <a href="{{ route('admin.tours.create') }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group">
                                <i class="bi bi-plus-circle-fill text-primary text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Add New Tour</strong>
                                    <span class="text-[11px] text-slate-500">Create a new safari or excursion</span>
                                </div>
                            </a>
                        </div>
                        <div class="cmd-entry" data-keywords="add addon create add-on upgrade">
                            <a href="{{ route('admin.addons.index') }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group">
                                <i class="bi bi-puzzle-fill text-amber-500 text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Manage Tour Add-ons</strong>
                                    <span class="text-[11px] text-slate-500">Quad biking, VIP seating, buggy</span>
                                </div>
                            </a>
                        </div>
                        <div class="cmd-entry" data-keywords="write blog new article post">
                            <a href="{{ route('admin.blogs.create') }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group">
                                <i class="bi bi-journal-plus text-emerald-500 text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Write Blog Post</strong>
                                    <span class="text-[11px] text-slate-500">Publish desert guides and tips</span>
                                </div>
                            </a>
                        </div>
                        <div class="cmd-entry" data-keywords="coupon promo discount voucher code create">
                            <a href="{{ route('admin.coupons.create') }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group">
                                <i class="bi bi-ticket-perforated-fill text-rose-500 text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Create Promo Code</strong>
                                    <span class="text-[11px] text-slate-500">Discounts and voucher campaigns</span>
                                </div>
                            </a>
                        </div>
                        <div class="cmd-entry" data-keywords="export bookings csv download excel">
                            <a href="{{ route('admin.bookings.export') }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group">
                                <i class="bi bi-file-earmark-spreadsheet-fill text-emerald-600 text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Export Bookings (CSV)</strong>
                                    <span class="text-[11px] text-slate-500">Download customer spreadsheet</span>
                                </div>
                            </a>
                        </div>
                        <div class="cmd-entry" data-keywords="clear cache purge system views routes config">
                            <a href="javascript:void(0);" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition clear-cache-trigger group">
                                <i class="bi bi-arrow-repeat text-amber-500 text-xl"></i>
                                <div>
                                    <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Purge System Cache</strong>
                                    <span class="text-[11px] text-slate-500">Flush views, routes, config</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation Shortcuts Section -->
                <div class="cmd-section" data-section="navigation">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1">Navigation Shortcuts</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="dashboard overview revenue kpi stats">
                            <span class="flex items-center gap-2"><i class="bi bi-grid-1x2-fill text-primary"></i> Dashboard Overview</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/dashboard</span>
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="bookings orders customers reservations payments">
                            <span class="flex items-center gap-2"><i class="bi bi-calendar2-check-fill text-primary"></i> Bookings & Reservations</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/bookings</span>
                        </a>
                        <a href="{{ route('admin.operations.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="operations dispatch manifest drivers pickup logistics">
                            <span class="flex items-center gap-2"><i class="bi bi-truck-flatbed text-amber-500"></i> Daily Operations Manifest</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/operations</span>
                        </a>
                        <a href="{{ route('admin.tours.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="tours inventory safari quad buggy glamping">
                            <span class="flex items-center gap-2"><i class="bi bi-compass-fill text-primary"></i> Tours Inventory</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/tours</span>
                        </a>
                        <a href="{{ route('admin.campaigns.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="campaigns email marketing newsletters broadcasts analytics stats clicks opens">
                            <span class="flex items-center gap-2"><i class="bi bi-megaphone-fill text-amber-500"></i> Email Campaigns</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/campaigns</span>
                        </a>
                        <a href="{{ route('admin.analytics.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="analytics traffic visitors acquisition referrers sources campaigns utm">
                            <span class="flex items-center gap-2"><i class="bi bi-graph-up-arrow text-primary"></i> Analytics Dashboard</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/analytics</span>
                        </a>
                        <a href="{{ route('admin.settings.general') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-keywords="settings identity logo contact general">
                            <span class="flex items-center gap-2"><i class="bi bi-sliders text-amber-500"></i> Portal Settings</span>
                            <span class="text-[10px] text-slate-400 font-mono">/admin/settings/general</span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex items-center justify-between">
                <span>Use <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono">Esc</kbd> to close</span>
                <span class="font-bold text-primary">DUNES SPOTLIGHT</span>
            </div>
        </div>
    </div>

    <!-- Essential Scripts & Plugins -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" crossorigin="anonymous"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize real-time online visitors poller
        window.initVisitorsPoller("{{ route('admin.active-visitors') }}");
        
        // Initialize cache purge AJAX handler
        window.initCachePurgeHandler("{{ route('admin.clear-cache') }}", "{{ csrf_token() }}");

        // Flash message toasts via SweetAlert2
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

        @if(session('status'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('status') === 'profile-updated' ? 'Profile details updated successfully!' : (session('status') === 'password-updated' ? 'Password changed successfully!' : session('status')) }}",
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
                confirmButtonColor: '#F69044'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '<ul style="text-align:left; font-size:13px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
                confirmButtonColor: '#F69044'
            });
        @endif
    });
    </script>

    @stack('scripts')
</body>
</html>
