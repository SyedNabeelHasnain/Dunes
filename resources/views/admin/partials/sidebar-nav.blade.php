@php
    $isMobile = $isMobile ?? false;
    $isBookingsGroup = request()->routeIs('admin.bookings.*') || request()->routeIs('admin.pricing.*');
    $isOperationsGroup = request()->routeIs('admin.operations.*') || request()->routeIs('admin.drivers.*') || request()->routeIs('admin.vehicles.*');
    $isCatalogGroup = request()->routeIs('admin.tours.*') || request()->routeIs('admin.addons.*') || request()->routeIs('admin.tiers.*');
    $isCommunicationsGroup = request()->routeIs('admin.inquiries.*') || request()->routeIs('admin.whatsapp.*') || request()->routeIs('admin.reviews.*');
    $isMarketingGroup = request()->routeIs('admin.coupons.*') || request()->routeIs('admin.subscribers.*') || request()->routeIs('admin.subscriber-groups.*') || request()->routeIs('admin.email-templates.*') || request()->routeIs('admin.campaigns.*');
    $isContentGroup = request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-categories.*') || request()->routeIs('admin.wiki.*') || request()->routeIs('admin.pages.*') || request()->routeIs('admin.menus.*') || request()->routeIs('admin.faqs.*') || request()->routeIs('admin.legal.*');
    $isSettingsGroup = request()->routeIs('admin.settings.*');
@endphp

<div x-data="{
    openGroups: {
        bookings: {{ $isBookingsGroup ? 'true' : 'false' }},
        operations: {{ $isOperationsGroup ? 'true' : 'false' }},
        catalog: {{ $isCatalogGroup ? 'true' : 'false' }},
        communications: {{ $isCommunicationsGroup ? 'true' : 'false' }},
        marketing: {{ $isMarketingGroup ? 'true' : 'false' }},
        content: {{ $isContentGroup ? 'true' : 'false' }},
        settings: {{ $isSettingsGroup ? 'true' : 'false' }},
    },
    toggle(group) {
        @if(!$isMobile)
            if ($store.admin.sidebarCollapsed) {
                $store.admin.sidebarCollapsed = false;
                document.documentElement.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarState', 'expanded');
                this.openGroups[group] = true;
                return;
            }
        @endif
        this.openGroups[group] = !this.openGroups[group];
    }
}" class="space-y-1">

    <!-- 1. STANDALONE DASHBOARD & ANALYTICS -->
    <a href="{{ route('admin.dashboard') }}" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
       @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Dashboard' : ''" @endif>
        <i class="bi bi-grid-1x2-fill text-base shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
        <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap">Dashboard</span>
    </a>

    <a href="{{ route('admin.analytics.index') }}" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.analytics*') ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
       @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Analytics & BI' : ''" @endif>
        <i class="bi bi-graph-up-arrow text-base text-cyan-400 shrink-0"></i>
        <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap">Analytics & BI</span>
    </a>

    <!-- 2. BOOKINGS & SALES GROUP (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('bookings')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isBookingsGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Bookings & Sales' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-calendar2-check-fill text-base text-amber-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Bookings & Sales</span>
            </div>
            <div @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="flex items-center gap-1.5 shrink-0">
                @if(($sidebarCounts['draft_bookings'] ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30" title="Incomplete checkout drafts">
                        {{ $sidebarCounts['draft_bookings'] }}
                    </span>
                @endif
                @if(($sidebarCounts['pending_bookings'] ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30" title="Pending bookings">
                        {{ $sidebarCounts['pending_bookings'] }}
                    </span>
                @endif
                <i class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.bookings ? 'rotate-90 text-amber-400' : ''"></i>
            </div>
        </button>

        <!-- Bookings Sub-Tree with connecting tree guideline -->
        <div x-show="openGroups.bookings @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <!-- All Bookings -->
            <a href="{{ route('admin.bookings.index') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.bookings.index') || request()->routeIs('admin.bookings.show') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.bookings.index') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                    <span>All Reservations</span>
                </div>
                @if(($sidebarCounts['pending_bookings'] ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                        {{ $sidebarCounts['pending_bookings'] }}
                    </span>
                @endif
            </a>

            <!-- Manual New Booking -->
            <a href="{{ route('admin.bookings.create') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.bookings.create') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.bookings.create') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                    <span>Manual Booking</span>
                </div>
                <i class="bi bi-plus text-slate-400 text-sm"></i>
            </a>

            <!-- Incomplete Drafts Hub -->
            <a href="{{ route('admin.bookings.drafts') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.bookings.drafts*') ? 'bg-amber-500/20 text-amber-300 font-semibold ring-1 ring-amber-500/40' : 'text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Incomplete Drafts Hub</span>
                </div>
                @if(($sidebarCounts['draft_bookings'] ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-amber-500/30 text-amber-300 border border-amber-500/40">
                        {{ $sidebarCounts['draft_bookings'] }}
                    </span>
                @endif
            </a>

            <!-- Pricing Matrix -->
            <a href="{{ route('admin.pricing.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.pricing*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.pricing*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Pricing Matrix</span>
            </a>
        </div>
    </div>

    <!-- 3. DAILY OPERATIONS & FLEET (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('operations')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isOperationsGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Operations & Fleet' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-truck-flatbed text-base text-sky-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Operations & Fleet</span>
            </div>
            <i @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.operations ? 'rotate-90 text-sky-400' : ''"></i>
        </button>

        <div x-show="openGroups.operations @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <a href="{{ route('admin.operations.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.operations.index') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.operations.index') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Operations Dispatch Hub</span>
            </a>

            <a href="{{ route('admin.drivers.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.drivers*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.drivers*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Safari Drivers Roster</span>
            </a>

            <a href="{{ route('admin.vehicles.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.vehicles*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.vehicles*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Vehicle Fleet</span>
            </a>

            <a href="{{ route('admin.operations.reports') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.operations.reports*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.operations.reports*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Driver Settlement Reports</span>
            </a>
        </div>
    </div>

    <!-- 4. TOUR CATALOG & INVENTORY (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('catalog')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isCatalogGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Tour Inventory' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-compass-fill text-base text-emerald-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Tour Inventory</span>
            </div>
            <i @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.catalog ? 'rotate-90 text-emerald-400' : ''"></i>
        </button>

        <div x-show="openGroups.catalog @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <a href="{{ route('admin.tours.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.tours.index') || request()->routeIs('admin.tours.edit') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.tours.index') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>All Safari Tours</span>
            </a>

            <a href="{{ route('admin.tours.create') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.tours.create') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.tours.create') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                    <span>Add New Tour</span>
                </div>
                <i class="bi bi-plus text-slate-400 text-sm"></i>
            </a>

            <a href="{{ route('admin.addons.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.addons*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.addons*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Tour Add-ons</span>
            </a>

            <a href="{{ route('admin.tiers.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.tiers*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.tiers*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Pricing Tiers</span>
            </a>
        </div>
    </div>

    <!-- 5. CUSTOMER COMMUNICATIONS & LEADS (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('communications')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isCommunicationsGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Communications & Leads' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-chat-left-dots-fill text-base text-teal-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Leads & Messages</span>
            </div>
            <div @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="flex items-center gap-1.5 shrink-0">
                @php
                    $unreadCommCount = ($sidebarCounts['new_inquiries'] ?? 0) + ($sidebarCounts['unread_whatsapp'] ?? 0);
                @endphp
                @if($unreadCommCount > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white animate-pulse">
                        {{ $unreadCommCount }}
                    </span>
                @endif
                <i class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.communications ? 'rotate-90 text-teal-400' : ''"></i>
            </div>
        </button>

        <div x-show="openGroups.communications @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <!-- Contact Inquiries -->
            <a href="{{ route('admin.inquiries.index') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.inquiries*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.inquiries*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                    <span>Contact Inquiries</span>
                </div>
                @if(($sidebarCounts['new_inquiries'] ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        {{ $sidebarCounts['new_inquiries'] }}
                    </span>
                @endif
            </a>

            <!-- WhatsApp Leads -->
            <a href="{{ route('admin.whatsapp.leads') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.whatsapp.leads') ? 'bg-emerald-500/20 text-emerald-300 font-semibold ring-1 ring-emerald-500/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.whatsapp.leads') ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                    <span>WhatsApp Leads</span>
                </div>
                @if(($sidebarCounts['unread_whatsapp'] ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 animate-pulse">
                        {{ $sidebarCounts['unread_whatsapp'] }}
                    </span>
                @endif
            </a>

            <!-- Customer Reviews -->
            <a href="{{ route('admin.reviews.index') }}" 
               class="relative flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.reviews*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.reviews*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                    <span>Customer Reviews</span>
                </div>
                @if(($sidebarCounts['pending_reviews'] ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        {{ $sidebarCounts['pending_reviews'] }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <!-- 6. MARKETING & PROMOTIONS (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('marketing')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isMarketingGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Marketing & Promos' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-megaphone-fill text-base text-amber-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Marketing & Promos</span>
            </div>
            <i @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.marketing ? 'rotate-90 text-amber-400' : ''"></i>
        </button>

        <div x-show="openGroups.marketing @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <a href="{{ route('admin.coupons.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.coupons.index') || request()->routeIs('admin.coupons.create') || request()->routeIs('admin.coupons.edit') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.coupons.index') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Coupons & Promos</span>
            </a>

            <a href="{{ route('admin.coupons.popup-settings') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.coupons.popup-settings*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.coupons.popup-settings*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Popups & Social Proof</span>
            </a>

            <a href="{{ route('admin.subscribers.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.subscribers*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.subscribers*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Subscribers List</span>
            </a>

            <a href="{{ route('admin.subscriber-groups.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.subscriber-groups*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.subscriber-groups*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Subscriber Groups</span>
            </a>

            <a href="{{ route('admin.email-templates.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.email-templates*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.email-templates*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Email Templates</span>
            </a>

            <a href="{{ route('admin.campaigns.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.campaigns*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.campaigns*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Campaigns & Blasts</span>
            </a>
        </div>
    </div>

    <!-- 7. CONTENT & KNOWLEDGE PORTAL (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('content')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isContentGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Content & Knowledge' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-journal-richtext text-base text-rose-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Content & Knowledge</span>
            </div>
            <i @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.content ? 'rotate-90 text-rose-400' : ''"></i>
        </button>

        <div x-show="openGroups.content @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <a href="{{ route('admin.blogs.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.blogs*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.blogs*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Blog Articles</span>
            </a>

            <a href="{{ route('admin.blog-categories.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.blog-categories*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.blog-categories*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Blog Categories</span>
            </a>

            <a href="{{ route('admin.wiki.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.wiki.index') || request()->routeIs('admin.wiki.create') || request()->routeIs('admin.wiki.edit') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.wiki.index') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Wiki Articles</span>
            </a>

            <a href="{{ route('admin.wiki.pillars') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.wiki.pillars*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.wiki.pillars*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Wiki Pillars</span>
            </a>

            <a href="{{ route('admin.wiki.glossary') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.wiki.glossary*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.wiki.glossary*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Wiki Glossary</span>
            </a>

            <a href="{{ route('admin.pages.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.pages*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.pages*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Pages & Content</span>
            </a>

            <a href="{{ route('admin.menus.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.menus*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.menus*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Navigation Menus</span>
            </a>

            <a href="{{ route('admin.faqs.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.faqs*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.faqs*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>FAQs Knowledgebase</span>
            </a>

            <a href="{{ route('admin.legal.index') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.legal*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.legal*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Legal Policies</span>
            </a>
        </div>
    </div>

    <!-- 8. PORTAL SETTINGS & INTEGRATIONS (Collapsible Tree) -->
    <div class="pt-2">
        <button type="button" 
                @click="toggle('settings')" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition text-left cursor-pointer {{ $isSettingsGroup ? 'bg-slate-900/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-900/70 hover:text-white' }} @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif"
                @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Portal Settings' : ''" @endif>
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-gear-wide-connected text-base text-cyan-400 shrink-0"></i>
                <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap text-sm">Portal Settings</span>
            </div>
            <i @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="bi bi-chevron-right text-[11px] text-slate-500 transition-transform duration-200" :class="openGroups.settings ? 'rotate-90 text-cyan-400' : ''"></i>
        </button>

        <div x-show="openGroups.settings @if(!$isMobile) && !$store.admin.sidebarCollapsed @endif" 
             x-collapse 
             class="ps-3.5 ms-4 border-s-2 border-slate-800/80 space-y-1 mt-1">
            
            <a href="{{ route('admin.settings.general') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.general*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.general*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>General Identity</span>
            </a>

            <a href="{{ route('admin.settings.seo') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.seo*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.seo*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>SEO & Metadata</span>
            </a>

            <a href="{{ route('admin.settings.mail') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.mail*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.mail*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>SMTP Mailer</span>
            </a>

            <a href="{{ route('admin.settings.google') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.google*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.google*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Google Integrations</span>
            </a>

            <a href="{{ route('admin.settings.gttd') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.gttd*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.gttd*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Google Things To Do</span>
            </a>

            <a href="{{ route('admin.settings.meta') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.meta*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.meta*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Meta / Facebook</span>
            </a>

            <a href="{{ route('admin.whatsapp.settings') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.whatsapp.settings*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.whatsapp.settings*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>WhatsApp Setup</span>
            </a>

            <a href="{{ route('admin.settings.currency') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.currency*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.currency*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Currency & Rates</span>
            </a>

            <a href="{{ route('admin.settings.languages') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Languages & Locales</span>
            </a>

            <a href="{{ route('admin.settings.translations') }}" 
               class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.settings.translations*') ? 'bg-primary/20 text-primary-light font-semibold ring-1 ring-primary/40' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.settings.translations*') ? 'bg-primary' : 'bg-slate-600' }}"></span>
                <span>Translation APIs</span>
            </a>
        </div>
    </div>

    <!-- 9. EXTERNAL QUICK LINKS -->
    <div class="pt-3">
        <div @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="pb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">External</div>
        <a href="{{ url('/rate-card') }}" target="_blank" 
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif" 
           @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Live Rate Card' : ''" @endif>
            <i class="bi bi-file-earmark-pdf-fill text-amber-400 text-sm shrink-0"></i>
            <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap">Live Rate Card</span>
        </a>
        <a href="{{ url('/') }}" target="_blank" 
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition @if(!$isMobile) :class=\"$store.admin.sidebarCollapsed ? 'justify-center px-0' : ''\" @endif" 
           @if(!$isMobile) :title="$store.admin.sidebarCollapsed ? 'Visit Live Website' : ''" @endif>
            <i class="bi bi-box-arrow-up-right text-cyan-400 text-sm shrink-0"></i>
            <span @if(!$isMobile) x-show="!$store.admin.sidebarCollapsed" @endif class="whitespace-nowrap">View Website</span>
        </a>
    </div>
</div>
