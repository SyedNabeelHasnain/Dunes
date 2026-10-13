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

    <!-- Prevent Alpine.js modal/drawer flashing during page hydration -->
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Admin Tailwind CSS v4 & Alpine.js Enterprise Portal Engine via Vite -->
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col" x-data>

    <!-- Global Processing Loader Overlay -->
    <div id="appLoader" class="fixed inset-0 z-50 bg-white/80 backdrop-blur-xs hidden flex-col items-center justify-center" style="display: none;">
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
            <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.pages*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-file-earmark-richtext-fill text-base text-amber-400"></i> <span>Pages & Content</span>
            </a>
            <a href="{{ route('admin.menus.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.menus*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-menu-button-wide-fill text-base text-sky-400"></i> <span>Navigation & Menus</span>
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
            <a href="{{ route('admin.settings.gttd') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.gttd*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-patch-check-fill text-emerald-400 text-base"></i> <span>Google Things To Do</span>
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
            <a href="{{ route('admin.settings.languages') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-translate text-emerald-400 text-base"></i> <span>Languages & Locales</span>
            </a>
            <a href="{{ route('admin.settings.translations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.translations*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                <i class="bi bi-robot text-amber-400 text-base"></i> <span>Translation APIs</span>
            </a>
            <button type="button" class="w-full text-start flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition clear-cache-trigger">
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
            <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.pages*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Pages & Content' : ''">
                <i class="bi bi-file-earmark-richtext-fill text-base shrink-0 text-amber-400"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Pages & Content</span>
            </a>
            <a href="{{ route('admin.menus.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.menus*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Navigation & Menus' : ''">
                <i class="bi bi-menu-button-wide-fill text-base shrink-0 text-sky-400"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Navigation & Menus</span>
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
            <a href="{{ route('admin.settings.google') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.google*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Google & Local SEO' : ''">
                <i class="bi bi-google text-rose-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Google & Local SEO</span>
            </a>
            <a href="{{ route('admin.settings.gttd') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.gttd*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Google Things To Do' : ''">
                <i class="bi bi-pin-map-fill text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Google Things To Do</span>
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
            <a href="{{ route('admin.settings.languages') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Languages & Locales' : ''">
                <i class="bi bi-translate text-emerald-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Languages & Locales</span>
            </a>
            <a href="{{ route('admin.settings.translations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.translations*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Translation APIs' : ''">
                <i class="bi bi-robot text-amber-400 text-base shrink-0"></i> <span x-show="!$store.admin.sidebarCollapsed" class="whitespace-nowrap">Translation APIs</span>
            </a>
            <button type="button" class="w-full text-start flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition clear-cache-trigger" :class="$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''" :title="$store.admin.sidebarCollapsed ? 'Purge Cache' : ''">
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
                <button type="button" @click="$store.admin.openCommandPalette()" class="sm:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Search Spotlight (Ctrl K)">
                    <i class="bi bi-search text-primary text-base"></i>
                </button>
                <h1 class="text-lg md:text-xl font-extrabold text-slate-900 capitalize tracking-tight mb-0">@yield('page_title', 'Dashboard')</h1>
            </div>

            <!-- Top Actions Toolbar -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Quick New Booking Button -->
                <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-2xs transition" title="Create Manual Booking">
                    <i class="bi bi-calendar-plus-fill"></i>
                    <span class="hidden sm:inline">New Booking</span>
                </a>

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
                        <button type="button" class="w-full text-start flex items-center gap-2 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-medium transition clear-cache-trigger">
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
         class="fixed inset-0 z-50 flex items-start justify-center p-3 sm:p-4 pt-12 md:pt-20 bg-slate-950/70 backdrop-blur-xs"
         style="display: none;">
        <div @click.outside="$store.admin.closeCommandPalette()"
             x-transition:enter="transition-all ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
            
            <!-- Header Search Input -->
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
                <div class="relative flex items-center justify-center ms-1">
                    <i id="cmdSearchIcon" class="bi bi-search text-primary text-lg"></i>
                    <div id="cmdSpinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-primary border-t-transparent"></div>
                </div>
                <input type="text" id="cmdInput" placeholder="Search actions, tours, bookings, guests, pages, settings... (Esc to close)" autocomplete="off" class="w-full bg-transparent border-0 outline-none text-slate-800 text-sm sm:text-base font-semibold placeholder:text-slate-400 placeholder:font-normal focus:ring-0">
                <button type="button" @click="$store.admin.closeCommandPalette()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Quick Filter Tabs -->
            <div class="px-3.5 py-2 bg-slate-50/50 border-b border-slate-200/80 flex items-center gap-1.5 overflow-x-auto text-xs no-scrollbar shrink-0" id="cmdCategoryPills">
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-bold text-xs bg-primary text-white shadow-2xs transition shrink-0" data-category="all">All</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="actions"><i class="bi bi-lightning-charge-fill text-amber-500 me-1"></i>Quick Actions</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="navigation"><i class="bi bi-compass-fill text-primary me-1"></i>CMS Sections</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="bookings"><i class="bi bi-calendar2-check-fill text-emerald-500 me-1"></i>Bookings</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="tours"><i class="bi bi-sun-fill text-orange-500 me-1"></i>Tours</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="inquiries"><i class="bi bi-chat-dots-fill text-cyan-500 me-1"></i>Leads & Inquiries</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="marketing"><i class="bi bi-ticket-perforated-fill text-rose-500 me-1"></i>Marketing & Promos</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="content"><i class="bi bi-journal-richtext text-emerald-600 me-1"></i>Content & Blogs</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="settings"><i class="bi bi-sliders text-amber-500 me-1"></i>Settings & SEO</button>
                <button type="button" class="cmd-pill px-2.5 py-1 rounded-lg font-medium text-xs text-slate-600 hover:bg-slate-200/60 transition shrink-0" data-category="preview"><i class="bi bi-box-arrow-up-right text-sky-500 me-1"></i>Live Website</button>
            </div>

            <!-- Search Results Scrollable Body -->
            <div class="p-4 overflow-y-auto space-y-5 flex-1" id="cmdResultsBody">
                
                <!-- Dynamic Real-Time Database Search Results Container -->
                <div id="dynamicSpotlightResults" class="space-y-4"></div>

                <!-- Empty State Card -->
                <div id="cmdEmptyState" class="hidden text-center py-10 px-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="bi bi-search"></i>
                    </div>
                    <div class="font-bold text-slate-800 text-sm mb-1" id="cmdEmptyTitle">No results found</div>
                    <div class="text-xs text-slate-500 max-w-sm mx-auto" id="cmdEmptySubtitle">Try searching by booking reference (#DT-), customer name, email, tour name, coupon code, or page title.</div>
                </div>

                <!-- Static Categorized Catalog -->
                <div id="staticSpotlightSections" class="space-y-5">
                    
                    <!-- Quick Actions Section -->
                    <div class="cmd-section" data-section="actions">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="bi bi-lightning-charge-fill text-amber-500"></i> Quick Actions & Creators</span>
                            <span class="text-[10px] text-slate-400 font-mono">15 actions</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="create booking manual reservation new order walk-in phone whatsapp add reservation">
                                <a href="{{ route('admin.bookings.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-calendar-plus-fill text-primary text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Create Manual Booking</strong>
                                        <span class="text-[11px] text-slate-500">Walk-in, phone, or WhatsApp reservation</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="add tour create new tour safari package inventory">
                                <a href="{{ route('admin.tours.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-plus-circle-fill text-primary text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Add New Tour</strong>
                                        <span class="text-[11px] text-slate-500">Create a new safari, buggy, or excursion</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="add addon create add-on upgrade quad buggy vip seating">
                                <a href="{{ route('admin.addons.index') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-puzzle-fill text-amber-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Manage Tour Add-ons</strong>
                                        <span class="text-[11px] text-slate-500">Quad bikes, dune buggies, VIP upgrades</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="pricing tiers standard premium luxury vip rate card">
                                <a href="{{ route('admin.tiers.index') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-layers-fill text-sky-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Tour Pricing Tiers</strong>
                                        <span class="text-[11px] text-slate-500">Standard, Premium, Luxury, VIP tiers</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="pricing matrix season holiday weekend rate card bulk pricing">
                                <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-cash-stack text-emerald-600 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Pricing Matrix Grid</strong>
                                        <span class="text-[11px] text-slate-500">Bulk price management across inventory</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="write blog new article post desert travel guide publish">
                                <a href="{{ route('admin.blogs.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-journal-plus text-emerald-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Write Blog Post</strong>
                                        <span class="text-[11px] text-slate-500">Publish desert guides and travel insights</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="coupon promo discount voucher code create gift campaign">
                                <a href="{{ route('admin.coupons.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-ticket-perforated-fill text-rose-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Create Promo Code</strong>
                                        <span class="text-[11px] text-slate-500">Discounts and voucher campaigns</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="popup welcome offer banner exit intent sticky bar promo discount modal">
                                <a href="{{ route('admin.coupons.popup-settings') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-gift-fill text-amber-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Welcome Offer & Banner</strong>
                                        <span class="text-[11px] text-slate-500">Configure popups & sticky top banners</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="email template create notification layout design newsletter">
                                <a href="{{ route('admin.email-templates.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-envelope-plus text-rose-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Create Email Template</strong>
                                        <span class="text-[11px] text-slate-500">Design automated marketing emails</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="campaign broadcast create send email marketing newsletter blast">
                                <a href="{{ route('admin.campaigns.create') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-megaphone-fill text-amber-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Create Email Campaign</strong>
                                        <span class="text-[11px] text-slate-500">Broadcast newsletters to subscribers</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="export bookings csv download excel sheet customers report">
                                <a href="{{ route('admin.bookings.export') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-file-earmark-spreadsheet-fill text-emerald-600 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Export Bookings (CSV)</strong>
                                        <span class="text-[11px] text-slate-500">Customer spreadsheet & revenues</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="export inquiries csv messages leads excel spreadsheet">
                                <a href="{{ route('admin.inquiries.export') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-envelope-arrow-down-fill text-cyan-600 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Export Inquiries (CSV)</strong>
                                        <span class="text-[11px] text-slate-500">Download customer contact messages</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="export whatsapp leads csv chat numbers phone excel spreadsheet">
                                <a href="{{ route('admin.whatsapp.export') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-file-earmark-arrow-down-fill text-emerald-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Export WhatsApp Leads (CSV)</strong>
                                        <span class="text-[11px] text-slate-500">Export chat contacts & inquiries</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="operations dispatch manifest drivers pickup logistics daily departures">
                                <a href="{{ route('admin.operations.index') }}" class="flex items-center gap-3 p-2.5">
                                    <i class="bi bi-truck-flatbed text-amber-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Daily Operations Manifest</strong>
                                        <span class="text-[11px] text-slate-500">Assign drivers & track live departures</span>
                                    </div>
                                </a>
                            </div>
                            <div class="cmd-entry rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 transition group cursor-pointer" data-category="actions" data-keywords="clear cache purge system views routes config redis flush clean reload">
                                <a href="javascript:void(0);" class="flex items-center gap-3 p-2.5 clear-cache-trigger">
                                    <i class="bi bi-arrow-repeat text-amber-500 text-xl shrink-0"></i>
                                    <div>
                                        <strong class="block text-xs font-bold text-slate-900 group-hover:text-primary">Purge System Cache</strong>
                                        <span class="text-[11px] text-slate-500">Flush views, routes, config, and cache</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Core CMS Navigation Section -->
                    <div class="cmd-section" data-section="navigation">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="bi bi-compass-fill text-primary"></i> CMS Modules & Management</span>
                            <span class="text-[10px] text-slate-400 font-mono">16 modules</span>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.dashboard') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="dashboard overview revenue kpi stats summary realtime">
                                <span class="flex items-center gap-2"><i class="bi bi-grid-1x2-fill text-primary"></i> Dashboard Overview & KPIs</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/dashboard</span>
                            </a>
                            <a href="{{ route('admin.analytics.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="analytics traffic visitors acquisition referrers sources campaigns utm">
                                <span class="flex items-center gap-2"><i class="bi bi-graph-up-arrow text-primary"></i> Real-Time Analytics Dashboard</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/analytics</span>
                            </a>
                            <a href="{{ route('admin.bookings.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="bookings orders customers reservations payments guest tickets status">
                                <span class="flex items-center gap-2"><i class="bi bi-calendar2-check-fill text-primary"></i> Bookings & Reservations</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/bookings</span>
                            </a>
                            <a href="{{ route('admin.operations.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="operations dispatch manifest drivers pickup logistics departures">
                                <span class="flex items-center gap-2"><i class="bi bi-truck-flatbed text-amber-500"></i> Daily Operations Manifest & Drivers</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/operations</span>
                            </a>
                            <a href="{{ route('admin.inquiries.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="inquiries contact messages leads forms support questions">
                                <span class="flex items-center gap-2"><i class="bi bi-envelope-paper-fill text-amber-500"></i> Customer Inquiries</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/inquiries</span>
                            </a>
                            <a href="{{ route('admin.whatsapp.leads') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="whatsapp leads chats conversations phone numbers inquiries">
                                <span class="flex items-center gap-2"><i class="bi bi-whatsapp text-emerald-500"></i> WhatsApp Leads & Chats</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/whatsapp-leads</span>
                            </a>
                            <a href="{{ route('admin.tours.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="tours inventory catalog safari quad buggy glamping evening morning">
                                <span class="flex items-center gap-2"><i class="bi bi-compass-fill text-primary"></i> Tours Inventory</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/tours</span>
                            </a>
                            <a href="{{ route('admin.addons.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="addons extras upgrades quad buggy vip seating">
                                <span class="flex items-center gap-2"><i class="bi bi-puzzle-fill text-amber-500"></i> Tour Add-ons Management</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/addons</span>
                            </a>
                            <a href="{{ route('admin.tiers.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="tiers pricing standard vip premium luxury">
                                <span class="flex items-center gap-2"><i class="bi bi-layers-fill text-sky-500"></i> Pricing Tiers</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/tiers</span>
                            </a>
                            <a href="{{ route('admin.pricing.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="pricing matrix bulk calendar rates seasonal">
                                <span class="flex items-center gap-2"><i class="bi bi-cash-stack text-emerald-600"></i> Pricing Matrix</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/pricing</span>
                            </a>
                            <a href="{{ route('admin.blogs.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="blogs articles posts guides desert travel tips publishing">
                                <span class="flex items-center gap-2"><i class="bi bi-journal-richtext text-emerald-500"></i> Blog Articles & Posts</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/blogs</span>
                            </a>
                            <a href="{{ route('admin.blog-categories.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="blog categories topics tags taxonomy">
                                <span class="flex items-center gap-2"><i class="bi bi-folder-fill text-amber-500"></i> Blog Categories</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/blog-categories</span>
                            </a>
                            <a href="{{ route('admin.reviews.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="reviews ratings testimonials feedback google tripadvisor website">
                                <span class="flex items-center gap-2"><i class="bi bi-star-half text-amber-400"></i> Customer Reviews & Moderation</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/reviews</span>
                            </a>
                            <a href="{{ route('admin.faqs.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="faqs questions answers help accordion tour faq">
                                <span class="flex items-center gap-2"><i class="bi bi-question-circle-fill text-sky-500"></i> FAQs Management</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/faqs</span>
                            </a>
                            <a href="{{ route('admin.legal.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="legal policies terms condition privacy policy cancellation refund gdpr">
                                <span class="flex items-center gap-2"><i class="bi bi-shield-check text-indigo-500"></i> Legal Policies & Terms</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/legal-pages</span>
                            </a>
                            <a href="{{ route('admin.pages.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="pages content home about contact sections cms builder">
                                <span class="flex items-center gap-2"><i class="bi bi-file-earmark-richtext-fill text-amber-500"></i> Dynamic Pages & Content CMS</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/pages</span>
                            </a>
                            <a href="{{ route('admin.menus.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="navigation" data-keywords="menus navigation header footer links navbar hierarchy">
                                <span class="flex items-center gap-2"><i class="bi bi-menu-button-wide-fill text-sky-500"></i> Navigation Menus Manager</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/menus</span>
                            </a>
                        </div>
                    </div>

                    <!-- Marketing & Email Marketing Section -->
                    <div class="cmd-section" data-section="marketing">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="bi bi-ticket-perforated-fill text-rose-500"></i> Marketing & Audiences</span>
                            <span class="text-[10px] text-slate-400 font-mono">6 modules</span>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.coupons.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="coupons promos discount vouchers codes campaigns">
                                <span class="flex items-center gap-2"><i class="bi bi-ticket-perforated-fill text-rose-500"></i> Coupons & Promo Codes</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/coupons</span>
                            </a>
                            <a href="{{ route('admin.coupons.popup-settings') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="welcome offer popup sticky banner discount exit intent">
                                <span class="flex items-center gap-2"><i class="bi bi-gift-fill text-amber-500"></i> Welcome Offer Popup & Topbar</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/coupons/popup-settings</span>
                            </a>
                            <a href="{{ route('admin.subscribers.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="subscribers newsletter email audience marketing club vip contacts">
                                <span class="flex items-center gap-2"><i class="bi bi-people-fill text-sky-500"></i> Email Subscribers & Lists</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/subscribers</span>
                            </a>
                            <a href="{{ route('admin.subscriber-groups.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="subscriber groups segments tags audiences lists">
                                <span class="flex items-center gap-2"><i class="bi bi-diagram-3-fill text-cyan-500"></i> Subscriber Groups & Segments</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/subscriber-groups</span>
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="email templates html responsive layouts newsletters">
                                <span class="flex items-center gap-2"><i class="bi bi-envelope-paper-heart-fill text-rose-400"></i> Email Templates</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/email-templates</span>
                            </a>
                            <a href="{{ route('admin.campaigns.index') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="marketing" data-keywords="campaigns email broadcast blast marketing stats open click rates">
                                <span class="flex items-center gap-2"><i class="bi bi-megaphone-fill text-amber-500"></i> Campaigns & Broadcast Analytics</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/campaigns</span>
                            </a>
                        </div>
                    </div>

                    <!-- Portal Settings & SEO Section -->
                    <div class="cmd-section" data-section="settings">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="bi bi-sliders text-amber-500"></i> Portal Settings, SEO & Integrations</span>
                            <span class="text-[10px] text-slate-400 font-mono">13 panels</span>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.settings.general') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="settings identity logo favicon brand phone email address contact copyright">
                                <span class="flex items-center gap-2"><i class="bi bi-sliders text-amber-500"></i> General Identity & Contact</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/general</span>
                            </a>
                            <a href="{{ route('admin.settings.seo') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="seo metadata canonical robots meta title description sitemap search console google indexing indexnow">
                                <span class="flex items-center gap-2"><i class="bi bi-search text-sky-500"></i> SEO & Search Engine Indexing</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/seo</span>
                            </a>
                            <a href="{{ route('admin.settings.marketing') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="marketing promos banner settings exit intent popups tracking">
                                <span class="flex items-center gap-2"><i class="bi bi-megaphone text-rose-500"></i> Marketing & Promo Settings</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/marketing</span>
                            </a>
                            <a href="{{ route('admin.settings.mail') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="mail smtp mailer host port username password tls ssl encryption transactional">
                                <span class="flex items-center gap-2"><i class="bi bi-envelope-gear text-sky-500"></i> SMTP & Mailer Setup</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/mail</span>
                            </a>
                            <a href="{{ route('admin.settings.google') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="google tag manager gtm analytics ga4 search console place id business profile maps">
                                <span class="flex items-center gap-2"><i class="bi bi-google text-rose-500"></i> Google Integrations & Local SEO</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/google</span>
                            </a>
                            <a href="{{ route('admin.settings.gttd') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="google things to do gttd feed products attractions xml partner operator">
                                <span class="flex items-center gap-2"><i class="bi bi-patch-check-fill text-emerald-500"></i> Google Things To Do (GTTD)</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/gttd</span>
                            </a>
                            <a href="{{ route('admin.settings.meta') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="meta facebook pixel capi conversion api access token tracking ads">
                                <span class="flex items-center gap-2"><i class="bi bi-meta text-sky-500"></i> Meta / Facebook Pixel & CAPI</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/meta</span>
                            </a>
                            <a href="{{ route('admin.whatsapp.settings') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="whatsapp business number api message template float widget setup">
                                <span class="flex items-center gap-2"><i class="bi bi-gear-wide-connected text-emerald-500"></i> WhatsApp Business Setup</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/whatsapp-settings</span>
                            </a>
                            <a href="{{ route('admin.settings.currency') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="currency exchange rates aed usd eur gbp sar inr base multi currency">
                                <span class="flex items-center gap-2"><i class="bi bi-currency-exchange text-cyan-500"></i> Currency & Exchange Rates</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/currency</span>
                            </a>
                            <a href="{{ route('admin.settings.languages') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="languages locales english arabic russian spanish italian rtl translation active">
                                <span class="flex items-center gap-2"><i class="bi bi-translate text-emerald-500"></i> Multi-Language & Locales</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/languages</span>
                            </a>
                            <a href="{{ route('admin.settings.translations') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="translation api deepl google cloud automated catalog in-form auto translate">
                                <span class="flex items-center gap-2"><i class="bi bi-robot text-amber-500"></i> Automated Translation APIs</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/settings/translations</span>
                            </a>
                            <a href="{{ route('admin.profile') }}" class="cmd-entry flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="settings" data-keywords="profile security password name email admin credentials two factor">
                                <span class="flex items-center gap-2"><i class="bi bi-person-gear text-slate-500"></i> Administrator Profile & Security</span>
                                <span class="text-[10px] text-slate-400 font-mono">/admin/profile</span>
                            </a>
                        </div>
                    </div>

                    <!-- Live Public Previews & Tools Section -->
                    <div class="cmd-section" data-section="preview">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="bi bi-box-arrow-up-right text-sky-500"></i> Public Website & Feeds</span>
                            <span class="text-[10px] text-slate-400 font-mono">14 tools</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <a href="{{ url('/') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="live website frontend homepage client landing">
                                <span class="flex items-center gap-2"><i class="bi bi-house-door-fill text-primary"></i> Live Website Homepage</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/rate-card') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="rate card pricing sheet prices safari packages rates">
                                <span class="flex items-center gap-2"><i class="bi bi-file-earmark-pdf-fill text-amber-500"></i> Official Rate Card (2026)</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/dubai-desert-sunset-times') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="sunset times desert guide lahbab golden hour twilight schedule">
                                <span class="flex items-center gap-2"><i class="bi bi-sunset-fill text-orange-500"></i> Desert Sunset Times Guide</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/build-your-own-safari') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="custom safari builder build your own customizer package calculator">
                                <span class="flex items-center gap-2"><i class="bi bi-sliders2-vertical text-primary"></i> Custom Safari Builder</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/dune-buggy-rental-dubai') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="dune buggy rental can am polaris desert off road quad">
                                <span class="flex items-center gap-2"><i class="bi bi-ev-front-fill text-rose-500"></i> Dune Buggy Rental Dubai</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/gallery') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="gallery photos videos instagram reels media guests albums">
                                <span class="flex items-center gap-2"><i class="bi bi-images text-indigo-500"></i> Media & Guest Gallery</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/contact') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="contact us form location bur dubai phone map">
                                <span class="flex items-center gap-2"><i class="bi bi-geo-alt-fill text-emerald-500"></i> Contact Us Page</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/about') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="about company story license det tourism dunes discovery">
                                <span class="flex items-center gap-2"><i class="bi bi-building text-slate-600"></i> About Us Page</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/faq') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="faq questions answers help guides info">
                                <span class="flex items-center gap-2"><i class="bi bi-patch-question-fill text-sky-500"></i> Customer FAQs Page</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/press-kit') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="press kit media assets logos brand guidelines badges">
                                <span class="flex items-center gap-2"><i class="bi bi-award-fill text-amber-500"></i> Press & Media Kit</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/sitemap.xml') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="sitemap xml search engines indexing googlebot">
                                <span class="flex items-center gap-2"><i class="bi bi-diagram-2-fill text-rose-500"></i> XML Sitemap Index</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/feeds/google-things-to-do.xml') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="google things to do feed products attractions xml live feed">
                                <span class="flex items-center gap-2"><i class="bi bi-patch-check text-emerald-500"></i> Google Things To Do Feed</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                            <a href="{{ url('/llms.txt') }}" target="_blank" class="cmd-entry flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-primary hover:bg-slate-50 text-xs font-medium text-slate-700 transition" data-category="preview" data-keywords="llms txt ai knowledge base bot search agent">
                                <span class="flex items-center gap-2"><i class="bi bi-robot text-indigo-500"></i> AI Agent Knowledge (llms.txt)</span>
                                <i class="bi bi-box-arrow-up-right text-slate-400 text-xs"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono shadow-2xs">↑</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono shadow-2xs">↓</kbd> to navigate</span>
                    <span class="text-slate-300">•</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono shadow-2xs">↵</kbd> to select</span>
                    <span class="text-slate-300">•</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono shadow-2xs">Esc</kbd> to close</span>
                </div>
                <div class="flex items-center gap-2 ms-auto">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live DB Search
                    </span>
                    <span class="font-bold text-primary tracking-wide">DUNES SPOTLIGHT</span>
                </div>
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
                text: @js(session('success')),
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
                text: @js(session('status') === 'profile-updated' ? 'Profile details updated successfully!' : (session('status') === 'password-updated' ? 'Password changed successfully!' : session('status'))),
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
                text: @js(session('error')),
                confirmButtonColor: '#F69044'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: @js('<ul style="text-align:left; font-size:13px;">' . implode('', array_map(fn($e) => '<li>' . e($e) . '</li>', $errors->all())) . '</ul>'),
                confirmButtonColor: '#F69044'
            });
        @endif
    });
    </script>

    @stack('scripts')
</body>
</html>
