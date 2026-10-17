@extends('layouts.admin')

@section('page_title', 'Incomplete Drafts & Abandoned Checkouts')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition flex items-center gap-1">
        <i class="bi bi-house-door-fill text-xs"></i> <span>Dashboard</span>
    </a>
    <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
    <a href="{{ route('admin.bookings.index') }}" class="hover:text-primary transition">Bookings</a>
    <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
    <span class="text-slate-800 font-semibold">Incomplete Drafts</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Abandoned Checkouts Hub</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    {{ number_format($stats['total']) }} Drafts
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Monitor unsubmitted booking forms in real-time. Follow up directly with prospective guests via WhatsApp, Phone, or Email to recover high-value sales.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.bookings.drafts.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-download text-emerald-600"></i> Export CSV
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-calendar2-check-fill text-amber-400"></i> Active Bookings
            </a>
        </div>
    </div>

    <!-- 4 High-Impact KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Abandoned -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Dropped Drafts</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <span class="font-semibold text-amber-600">{{ $stats['today'] }}</span> dropped today
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-cart-x-fill"></i>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-amber-500"></div>
        </div>

        <!-- 2. Potential Recoverable Value -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recoverable Revenue</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1 font-mono">AED {{ number_format($stats['recoverable_value'], 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        Pipeline value in unsubmitted carts
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- 3. Contactable Leads -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Contactable Leads</div>
                    <div class="text-2xl font-black text-sky-600 mt-1">{{ $stats['contactable_rate'] }}%</div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        <span class="font-semibold text-slate-800">{{ number_format($stats['contactable_count']) }}</span> have verified phone / email
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-telephone-outbound-fill"></i>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-sky-500"></div>
        </div>

        <!-- 4. Today's Abandonments -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Today's New Drops</div>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($stats['today']) }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        Hot leads within the last 24 hours
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-stopwatch-fill"></i>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-rose-500"></div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.bookings.drafts') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <!-- Search Keyword -->
                <div class="lg:col-span-4 sm:col-span-2">
                    <label for="draftSearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Drafts</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-search text-xs"></i>
                        </div>
                        <input type="text" name="search" id="draftSearch" class="w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" placeholder="Guest name, phone, email, ref..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Contact Reachability Filter -->
                <div class="lg:col-span-2">
                    <label for="contactTypeFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Contact Reach</label>
                    <select name="contact_type" id="contactTypeFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">All Dropped Carts</option>
                        <option value="has_phone" {{ request('contact_type') === 'has_phone' ? 'selected' : '' }}>Has Phone Number</option>
                        <option value="has_email" {{ request('contact_type') === 'has_email' ? 'selected' : '' }}>Has Email Address</option>
                        <option value="both" {{ request('contact_type') === 'both' ? 'selected' : '' }}>Has Both Phone & Email</option>
                    </select>
                </div>

                <!-- Tour Filter -->
                <div class="lg:col-span-2">
                    <label for="tourIdFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Tour Package</label>
                    <select name="tour_id" id="tourIdFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden bg-white">
                        <option value="">All Tours</option>
                        @foreach($availableTours as $t)
                            <option value="{{ $t->id }}" {{ request('tour_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="lg:col-span-2">
                    <label for="fromDate" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">From Date</label>
                    <input type="date" name="from_date" id="fromDate" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ request('from_date') }}">
                </div>

                <!-- Actions -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('admin.bookings.drafts') }}" class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Drafts Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700" id="draftsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 drafts-select-all" title="Select All">
                        </th>
                        <th class="py-3 px-4">Draft Ref & Dropped</th>
                        <th class="py-3 px-4">Prospective Guest</th>
                        <th class="py-3 px-4">Tour & Setup</th>
                        <th class="py-3 px-4">Cart Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pe-4">1-Click Recovery Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($drafts as $d)
                    @php
                        $hasPhone = !empty($d->phone);
                        $hasRealEmail = !empty($d->email) && !str_ends_with($d->email, '@guest.local');
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$d->phone);
                        if (str_starts_with($cleanPhone, '00')) {
                            $cleanPhone = substr($cleanPhone, 2);
                        }
                        $waText = "Hello " . ($d->name && $d->name !== 'Prospective Guest' ? $d->name : 'there') . "! We noticed you were looking at the " . ($d->tour_name ?: 'Desert Safari') . " for " . ($d->tour_date ? $d->tour_date->format('M j, Y') : 'your upcoming trip') . " on Dunes Discovery Tourism. Would you like us to assist or confirm your preferred safari vehicle?";
                        $waUrl = "https://wa.me/" . ($cleanPhone ?: '971502456056') . "?text=" . rawurlencode($waText);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors bg-amber-50/30 border-l-4 border-l-amber-400" id="draft-row-{{ $d->id }}">
                        <!-- Checkbox -->
                        <td class="py-3.5 px-4">
                            <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4 draft-row-select" value="{{ $d->id }}">
                        </td>

                        <!-- Ref & Dropped Time -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $d->reference }}</span>
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" title="Incomplete checkout draft"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                <i class="bi bi-clock"></i> {{ $d->created_at ? $d->created_at->diffForHumans() : 'Recently' }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ $d->created_at ? $d->created_at->format('M j, Y - g:ia') : '' }}
                            </div>
                        </td>

                        <!-- Prospective Guest Profile -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <span>{{ $d->name ?: 'Prospective Guest' }}</span>
                                @if($hasPhone && $hasRealEmail)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800" title="Full Contact Details Available">HOT LEAD</span>
                                @endif
                            </div>
                            <!-- Phone -->
                            <div class="mt-0.5 flex items-center gap-1">
                                @if($hasPhone)
                                    <a href="tel:{{ $d->phone }}" class="text-xs font-mono font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                                        <i class="bi bi-telephone-fill text-[10px]"></i> {{ $d->phone }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">No phone captured</span>
                                @endif
                            </div>
                            <!-- Email -->
                            <div class="mt-0.5">
                                @if($hasRealEmail)
                                    <a href="mailto:{{ $d->email }}" class="text-[11px] text-slate-500 hover:text-primary transition flex items-center gap-1">
                                        <i class="bi bi-envelope-fill text-[10px]"></i> {{ $d->email }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">No email captured</span>
                                @endif
                            </div>
                        </td>

                        <!-- Tour & Setup -->
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900 text-xs line-clamp-1" title="{{ $d->tour_name }}">
                                {{ $d->tour_name }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $d->tier_name ?: 'Standard' }}
                                </span>
                                @if($d->tour_date)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="bi bi-calendar-event me-1 text-[9px]"></i> {{ $d->tour_date->format('M j, Y') }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                <i class="bi bi-people me-1"></i> {{ $d->adults }} Adults @if($d->children > 0), {{ $d->children }} Kids @endif @if($d->infants > 0), {{ $d->infants }} Infants @endif
                            </div>
                        </td>

                        <!-- Cart Value -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-bold text-sm text-slate-900 font-mono">
                                AED {{ number_format((float)$d->total, 2) }}
                            </div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">
                                {{ $d->payment_method ?: 'Cash/Card' }}
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="bi bi-hourglass-split text-[10px]"></i> Abandoned
                            </span>
                        </td>

                        <!-- 1-Click Recovery Actions Hub -->
                        <td class="py-3.5 px-4 text-right pe-4 whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <!-- 1-Click WhatsApp Direct Recovery Link -->
                                @if($hasPhone)
                                    <a href="{{ $waUrl }}" target="_blank"
                                       class="w-8 h-8 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition shadow-2xs"
                                       title="1-Click WhatsApp Lead Recovery with Pre-Filled Message">
                                        <i class="bi bi-whatsapp text-sm"></i>
                                    </a>
                                @endif

                                <!-- 1-Click Direct Phone Call -->
                                @if($hasPhone)
                                    <a href="tel:{{ $d->phone }}"
                                       class="w-8 h-8 rounded-xl border border-sky-300 bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white flex items-center justify-center transition shadow-2xs"
                                       title="Call Guest Now ({{ $d->phone }})">
                                        <i class="bi bi-telephone-fill text-xs"></i>
                                    </a>
                                @endif

                                <!-- 1-Click Send Recovery Email -->
                                @if($hasRealEmail)
                                    <button type="button"
                                            class="w-8 h-8 rounded-xl border border-purple-300 bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white flex items-center justify-center transition shadow-2xs btn-send-recovery cursor-pointer"
                                            title="Send Direct Recovery Email with Resume Link"
                                            data-id="{{ $d->id }}"
                                            data-email="{{ $d->email }}"
                                            data-ref="{{ $d->reference }}">
                                        <i class="bi bi-envelope-arrow-up-fill text-xs"></i>
                                    </button>
                                @endif

                                <!-- 1-Click Convert to Confirmed Booking -->
                                <button type="button"
                                        class="w-8 h-8 rounded-xl border border-primary/40 bg-primary/10 text-primary hover:bg-primary hover:text-white flex items-center justify-center transition shadow-2xs btn-convert-draft cursor-pointer"
                                        title="Convert to Active Booking"
                                        data-id="{{ $d->id }}"
                                        data-ref="{{ $d->reference }}">
                                    <i class="bi bi-check2-circle text-sm font-bold"></i>
                                </button>

                                <!-- Delete Draft -->
                                <button type="button"
                                        class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-draft cursor-pointer"
                                        title="Permanently Discard Draft"
                                        data-id="{{ $d->id }}"
                                        data-ref="{{ $d->reference }}">
                                    <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="bi bi-cart-check-fill text-emerald-500"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm">No Abandoned Checkouts Found</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                                Great news! All recent customers either successfully completed their checkout or no drafts match your active filter parameters.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($drafts->hasPages())
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Showing {{ $drafts->firstItem() }} to {{ $drafts->lastItem() }} of {{ $drafts->total() }} dropped drafts
            </div>
            <div>
                {{ $drafts->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Floating Bulk Action Bar -->
<div id="draftsBulkBar" class="bulk-action-bar" style="display: none;">
    <div class="flex items-center gap-2">
        <span class="px-2 py-0.5 bg-primary text-white rounded-full text-xs font-bold"><span id="draftsSelectedCount">0</span></span>
        <span class="font-semibold text-xs text-white">selected drafts</span>
    </div>
    <div class="w-px h-5 bg-slate-700"></div>
    <div class="flex items-center gap-1.5 flex-wrap">
        <button type="button" class="px-3.5 py-1.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition drafts-bulk-action" data-action="delete">
            <i class="bi bi-trash3 me-1"></i> Discard Selected
        </button>
        <button type="button" class="px-3 py-1.5 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition" id="cancelDraftsBulk">
            Cancel
        </button>
    </div>
</div>

<!-- Convert Draft Modal -->
<div id="convertDraftModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Convert to Active Booking</h3>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 close-convert-modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p class="text-xs text-slate-600">
            Converting <strong id="convertDraftRef" class="font-mono text-slate-900"></strong> will move this abandoned checkout into your live bookings pipeline with full dispatch and ticketing capabilities.
        </p>
        <div>
            <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Initial Status</label>
            <select id="convertTargetStatus" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 bg-white focus:border-primary outline-hidden">
                <option value="pending">Pending Confirmation</option>
                <option value="confirmed">Confirmed (Seat Secured)</option>
            </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition close-convert-modal">
                Cancel
            </button>
            <button type="button" id="confirmConvertBtn" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-check2"></i> Convert & View Booking
            </button>
        </div>
    </div>
</div>

<!-- Review & Send Recovery Email Modal -->
<div id="reviewRecoveryModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="bi bi-envelope-paper-heart-fill"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight">Review & Send Recovery Email</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Draft <span id="recoveryDraftRef" class="font-mono font-bold text-slate-800"></span> &bull; <span id="recoveryGuestName" class="font-semibold text-slate-700"></span>
                    </p>
                </div>
            </div>
            <button type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition close-recovery-modal">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <!-- Body / Content -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1">
            <!-- Loading State -->
            <div id="recoveryModalLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
                <span class="inline-block animate-spin w-8 h-8 border-3 border-purple-600 border-t-transparent rounded-full"></span>
                <span class="text-xs font-bold text-slate-500">Generating live email preview & cart details...</span>
            </div>

            <!-- Content Container -->
            <div id="recoveryModalBody" class="space-y-4 hidden">
                <!-- Cart Quick Summary Banner -->
                <div class="p-3 rounded-xl bg-purple-50/60 border border-purple-100 flex items-center justify-between gap-3 flex-wrap text-xs">
                    <div>
                        <div class="font-bold text-slate-900" id="recoveryTourTitle">-</div>
                        <div class="text-[11px] text-slate-500 mt-0.5" id="recoveryTourMeta">-</div>
                    </div>
                    <div class="text-end">
                        <div class="text-[10px] uppercase font-bold text-purple-700 tracking-wider">Cart Total</div>
                        <div class="font-mono font-extrabold text-sm text-slate-900" id="recoveryCartTotal">AED 0.00</div>
                    </div>
                </div>

                <!-- Recipient & Subject Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="recoveryRecipientEmail" class="block text-xs font-bold uppercase text-slate-600 tracking-wider mb-1">
                            Recipient Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="recoveryRecipientEmail" class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-hidden font-medium text-slate-800" required>
                        </div>
                    </div>
                    <div>
                        <label for="recoverySubject" class="block text-xs font-bold uppercase text-slate-600 tracking-wider mb-1">
                            Subject Line <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="recoverySubject" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-hidden font-medium text-slate-800" required>
                    </div>
                </div>

                <!-- Preview Mode Tabs -->
                <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-bold">
                    <button type="button" id="tabLivePreviewBtn" class="py-2 border-b-2 border-purple-600 text-purple-700 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-eye-fill"></i> Live Rendered Email
                    </button>
                    <button type="button" id="tabCustomMessageBtn" class="py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-pencil-square"></i> Customize Message
                    </button>
                </div>

                <!-- Tab 1: Live Rendered Iframe -->
                <div id="tabLivePreviewContainer" class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 shadow-inner">
                    <div class="bg-slate-200/70 px-3 py-1.5 text-[11px] font-mono text-slate-600 flex items-center justify-between border-b border-slate-200">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> HTML Email Preview (Responsive)</span>
                        <span class="text-[10px] text-slate-400">Direct SMTP Delivery</span>
                    </div>
                    <iframe id="recoveryEmailIframe" class="w-full h-80 border-0 bg-white" sandbox="allow-same-origin"></iframe>
                </div>

                <!-- Tab 2: Customize Message Textarea -->
                <div id="tabCustomMessageContainer" class="space-y-2 hidden">
                    <label for="recoveryCustomMessage" class="block text-xs font-bold uppercase text-slate-600 tracking-wider">
                        Personalized Introduction / Note (Optional)
                    </label>
                    <textarea id="recoveryCustomMessage" rows="5" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-hidden font-normal text-slate-800 leading-relaxed" placeholder="Write a customized message to this prospective guest, offering special assistance, answering queries, or offering an exclusive coupon..."></textarea>
                    <p class="text-[11px] text-slate-400">
                        When provided, this message will replace the standard opening greeting while retaining the full reservation summary, recovery link, and WhatsApp concierge buttons.
                    </p>
                </div>

                <!-- Direct Delivery Notice -->
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600">
                    <i class="bi bi-shield-check text-emerald-600 text-sm"></i>
                    <span>This email will be delivered immediately through your configured SMTP mail server with genuine bounce & delivery tracking.</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-2 shrink-0">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition close-recovery-modal">
                Cancel
            </button>
            <button type="button" id="confirmSendRecoveryBtn" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                <i class="bi bi-send-fill text-xs"></i> <span>Send Recovery Email Now</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 1. Bulk Select & Actions
    const selectAll = document.querySelector('.drafts-select-all');
    const rowCheckboxes = document.querySelectorAll('.draft-row-select');
    const bulkBar = document.getElementById('draftsBulkBar');
    const selectedCountSpan = document.getElementById('draftsSelectedCount');
    const cancelBulkBtn = document.getElementById('cancelDraftsBulk');

    function updateBulkBar() {
        const selected = Array.from(rowCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        if (selected.length > 0) {
            bulkBar.style.display = 'flex';
            selectedCountSpan.textContent = selected.length;
        } else {
            bulkBar.style.display = 'none';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!cb.checked && selectAll) selectAll.checked = false;
            updateBulkBar();
        });
    });

    if (cancelBulkBtn) {
        cancelBulkBtn.addEventListener('click', function() {
            if (selectAll) selectAll.checked = false;
            rowCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    const showAdminToast = (message, icon = 'success') => {
        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        } else if (window.adminToast) {
            window.adminToast(message, icon);
        } else {
            alert(message);
        }
    };

    // Bulk Delete Action
    document.querySelectorAll('.drafts-bulk-action').forEach(btn => {
        btn.addEventListener('click', async function() {
            const selected = Array.from(rowCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (!selected.length) return;

            const executeBulk = async () => {
                try {
                    const res = await fetch("{{ route('admin.bookings.drafts.bulk') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: selected,
                            action: 'delete'
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        selected.forEach(id => {
                            const row = document.getElementById(`draft-row-${id}`);
                            if (row) row.remove();
                        });
                        bulkBar.style.display = 'none';
                        showAdminToast(data.message, 'success');
                    } else {
                        if (window.Swal) {
                            window.Swal.fire({ icon: 'error', title: 'Bulk Action Failed', text: data.message || 'Operation failed.' });
                        } else {
                            alert(data.message || 'Bulk action failed.');
                        }
                    }
                } catch (err) {
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'An error occurred while executing bulk action.' });
                    } else {
                        alert('An error occurred while executing bulk action.');
                    }
                }
            };

            if (window.Swal) {
                window.Swal.fire({
                    title: 'Discard Selected Drafts?',
                    text: `Are you sure you want to permanently discard ${selected.length} selected checkout draft(s)? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: `Yes, discard ${selected.length} drafts`,
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) executeBulk();
                });
            } else {
                if (confirm(`Are you sure you want to permanently discard ${selected.length} selected draft(s)?`)) {
                    executeBulk();
                }
            }
        });
    });

    // 2. Review & Send Recovery Email Hub
    const reviewModal = document.getElementById('reviewRecoveryModal');
    const modalLoading = document.getElementById('recoveryModalLoading');
    const modalBody = document.getElementById('recoveryModalBody');
    const draftRefSpan = document.getElementById('recoveryDraftRef');
    const guestNameSpan = document.getElementById('recoveryGuestName');
    const tourTitleEl = document.getElementById('recoveryTourTitle');
    const tourMetaEl = document.getElementById('recoveryTourMeta');
    const cartTotalEl = document.getElementById('recoveryCartTotal');
    const recipientEmailInput = document.getElementById('recoveryRecipientEmail');
    const subjectInput = document.getElementById('recoverySubject');
    const customMessageInput = document.getElementById('recoveryCustomMessage');
    const emailIframe = document.getElementById('recoveryEmailIframe');
    const tabLivePreviewBtn = document.getElementById('tabLivePreviewBtn');
    const tabCustomMessageBtn = document.getElementById('tabCustomMessageBtn');
    const tabLivePreviewContainer = document.getElementById('tabLivePreviewContainer');
    const tabCustomMessageContainer = document.getElementById('tabCustomMessageContainer');
    const confirmSendBtn = document.getElementById('confirmSendRecoveryBtn');
    let activeRecoveryDraftId = null;

    const closeRecoveryModal = () => {
        if (reviewModal) reviewModal.classList.add('hidden');
        activeRecoveryDraftId = null;
    };

    document.querySelectorAll('.close-recovery-modal').forEach(btn => {
        btn.addEventListener('click', closeRecoveryModal);
    });

    if (reviewModal) {
        reviewModal.addEventListener('click', e => {
            if (e.target === reviewModal) closeRecoveryModal();
        });
    }

    if (tabLivePreviewBtn && tabCustomMessageBtn) {
        tabLivePreviewBtn.addEventListener('click', () => {
            tabLivePreviewBtn.className = 'py-2 border-b-2 border-purple-600 text-purple-700 transition flex items-center gap-1.5 cursor-pointer';
            tabCustomMessageBtn.className = 'py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer';
            tabLivePreviewContainer.classList.remove('hidden');
            tabCustomMessageContainer.classList.add('hidden');
        });

        tabCustomMessageBtn.addEventListener('click', () => {
            tabCustomMessageBtn.className = 'py-2 border-b-2 border-purple-600 text-purple-700 transition flex items-center gap-1.5 cursor-pointer';
            tabLivePreviewBtn.className = 'py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer';
            tabCustomMessageContainer.classList.remove('hidden');
            tabLivePreviewContainer.classList.add('hidden');
        });
    }

    document.querySelectorAll('.btn-send-recovery').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            activeRecoveryDraftId = id;

            if (reviewModal) reviewModal.classList.remove('hidden');
            if (modalLoading) modalLoading.classList.remove('hidden');
            if (modalBody) modalBody.classList.add('hidden');

            try {
                const res = await fetch(`/admin/bookings/drafts/${id}/preview-recovery`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data.success && data.draft) {
                    const d = data.draft;
                    if (draftRefSpan) draftRefSpan.textContent = '#' + d.reference;
                    if (guestNameSpan) guestNameSpan.textContent = d.name;
                    if (tourTitleEl) tourTitleEl.textContent = d.tour_name || 'Desert Safari Tour';
                    if (tourMetaEl) tourMetaEl.textContent = `${d.tier_name ? d.tier_name + ' • ' : ''}${d.tour_date} • ${d.email}`;
                    if (cartTotalEl) cartTotalEl.textContent = 'AED ' + d.total;

                    if (recipientEmailInput) recipientEmailInput.value = d.email || '';
                    if (subjectInput) subjectInput.value = data.subject || `Complete Your Dubai Desert Safari Reservation - Ref: #${d.reference}`;
                    if (customMessageInput) customMessageInput.value = '';

                    if (emailIframe) {
                        emailIframe.srcdoc = data.html || '';
                    }

                    if (modalLoading) modalLoading.classList.add('hidden');
                    if (modalBody) modalBody.classList.remove('hidden');
                } else {
                    closeRecoveryModal();
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'error', title: 'Preview Failed', text: data.message || 'Could not load recovery draft.' });
                    } else {
                        alert(data.message || 'Could not load recovery draft.');
                    }
                }
            } catch (err) {
                closeRecoveryModal();
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to load draft preview from server.' });
                } else {
                    alert('Failed to load draft preview from server.');
                }
            }
        });
    });

    if (confirmSendBtn) {
        confirmSendBtn.addEventListener('click', async function() {
            if (!activeRecoveryDraftId) return;

            const toEmail = recipientEmailInput?.value?.trim() || '';
            const subject = subjectInput?.value?.trim() || '';
            const customMessage = customMessageInput?.value?.trim() || '';

            if (!toEmail || !toEmail.includes('@')) {
                recipientEmailInput?.focus();
                showAdminToast('Please provide a valid recipient email address.', 'error');
                return;
            }

            if (!subject) {
                subjectInput?.focus();
                showAdminToast('Please provide an email subject line.', 'error');
                return;
            }

            const origHtml = confirmSendBtn.innerHTML;
            confirmSendBtn.disabled = true;
            confirmSendBtn.innerHTML = '<span class="inline-block animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full me-1.5"></span> Dispatching via SMTP...';

            try {
                const res = await fetch(`/admin/bookings/drafts/${activeRecoveryDraftId}/send-recovery`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        to_email: toEmail,
                        subject: subject,
                        custom_message: customMessage
                    })
                });

                const data = await res.json();
                if (data.success) {
                    const sentDraftId = activeRecoveryDraftId;
                    closeRecoveryModal();
                    showAdminToast(data.message || 'Recovery email dispatched via SMTP successfully!', 'success');

                    const draftRow = document.getElementById(`draft-row-${sentDraftId}`);
                    if (draftRow) {
                        const recBtn = draftRow.querySelector('.btn-send-recovery');
                        if (recBtn) {
                            recBtn.classList.remove('bg-purple-50', 'text-purple-600', 'border-purple-300');
                            recBtn.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                            recBtn.title = 'Recovery Email Dispatched via SMTP';
                        }
                        const profileTd = draftRow.querySelector('td:nth-child(3)');
                        if (profileTd && !profileTd.querySelector('.badge-recovery-sent')) {
                            const badge = document.createElement('div');
                            badge.className = 'badge-recovery-sent mt-1 inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-purple-100 text-purple-800';
                            badge.innerHTML = '<i class="bi bi-check-all"></i> Recovery Sent Just Now';
                            profileTd.appendChild(badge);
                        }
                    }
                } else {
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Dispatch Failed',
                            text: data.message || 'Failed to dispatch recovery email over SMTP.'
                        });
                    } else {
                        alert(data.message || 'Failed to dispatch recovery email over SMTP.');
                    }
                }
            } catch (err) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Network / Mail Server Error',
                        text: 'An error occurred while connecting to the mail server. Please verify SMTP settings.'
                    });
                } else {
                    alert('Network error while dispatching recovery email.');
                }
            } finally {
                confirmSendBtn.disabled = false;
                confirmSendBtn.innerHTML = origHtml;
            }
        });
    }

    // 3. 1-Click Convert Draft to Booking Modal
    const convertModal = document.getElementById('convertDraftModal');
    const convertDraftRef = document.getElementById('convertDraftRef');
    const confirmConvertBtn = document.getElementById('confirmConvertBtn');
    let activeConvertDraftId = null;

    document.querySelectorAll('.btn-convert-draft').forEach(btn => {
        btn.addEventListener('click', function() {
            activeConvertDraftId = this.dataset.id;
            convertDraftRef.textContent = '#' + this.dataset.ref;
            convertModal.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.close-convert-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            convertModal.classList.add('hidden');
            activeConvertDraftId = null;
        });
    });

    if (confirmConvertBtn) {
        confirmConvertBtn.addEventListener('click', async function() {
            if (!activeConvertDraftId) return;

            const targetStatus = document.getElementById('convertTargetStatus').value;
            const origHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full me-1"></span> Converting...';

            try {
                const res = await fetch(`/admin/bookings/drafts/${activeConvertDraftId}/convert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ target_status: targetStatus })
                });

                const data = await res.json();
                if (data.success) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'error', title: 'Conversion Failed', text: data.message || 'Failed to convert draft.' });
                    } else {
                        alert(data.message || 'Failed to convert draft.');
                    }
                    this.disabled = false;
                    this.innerHTML = origHtml;
                }
            } catch (err) {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Network error while converting draft.' });
                } else {
                    alert('Network error while converting draft.');
                }
                this.disabled = false;
                this.innerHTML = origHtml;
            }
        });
    }

    // 4. Single Delete Draft
    document.querySelectorAll('.btn-delete-draft').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const ref = this.dataset.ref;

            const executeDelete = async () => {
                try {
                    const res = await fetch(`/admin/bookings/drafts/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();
                    if (data.success) {
                        const row = document.getElementById(`draft-row-${id}`);
                        if (row) row.remove();
                        showAdminToast(data.message, 'success');
                    } else {
                        if (window.Swal) {
                            window.Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to discard draft.' });
                        } else {
                            alert(data.message || 'Failed to discard draft.');
                        }
                    }
                } catch (err) {
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Network error while discarding draft.' });
                    } else {
                        alert('Network error while discarding draft.');
                    }
                }
            };

            if (window.Swal) {
                window.Swal.fire({
                    title: 'Discard Draft?',
                    text: `Are you sure you want to permanently discard draft #${ref}? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, discard it',
                    cancelButtonText: 'Keep draft'
                }).then(result => {
                    if (result.isConfirmed) executeDelete();
                });
            } else {
                if (confirm(`Are you sure you want to permanently discard draft #${ref}? This action cannot be undone.`)) {
                    executeDelete();
                }
            }
        });
    });
});
</script>
@endpush
