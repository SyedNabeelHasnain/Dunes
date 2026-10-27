@extends('layouts.admin')

@section('page_title', 'Backlink Tracking & Off-Page SEO')

@section('content')
<div x-data="backlinkManager()" class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Backlink Tracking & Off-Page SEO</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitor high-DA inbound links, verify link health, attribute referral traffic, and track generated tour booking revenue.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.backlinks.syndication') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all">
                <i class="bi bi-broadcast text-amber-500 text-sm"></i>
                <span>Syndication API Hub</span>
            </a>
            <button type="button" @click="verifyAllLinks()" :disabled="isVerifyingAll" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition-all disabled:opacity-50">
                <i class="bi bi-arrow-repeat text-blue-500 text-sm" :class="{ 'animate-spin': isVerifyingAll }"></i>
                <span x-text="isVerifyingAll ? 'Verifying Links...' : 'Verify Link Health'"></span>
            </button>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all">
                <i class="bi bi-plus-lg"></i>
                <span>Add Tracked Link</span>
            </button>
        </div>
    </div>

    <!-- Alert / Flash Feedback -->
    <template x-if="toastMessage">
        <div class="p-4 rounded-xl text-xs font-semibold flex items-center justify-between transition-all"
             :class="toastType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
            <div class="flex items-center gap-2">
                <i class="bi" :class="toastType === 'success' ? 'bi-check-circle-fill text-emerald-600' : 'bi-exclamation-triangle-fill text-rose-600'"></i>
                <span x-text="toastMessage"></span>
            </div>
            <button @click="toastMessage = ''" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </template>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Total Links -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Backlinks</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="bi bi-link-45deg text-base"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalCount) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Indexed off-page directory & guest links</span>
        </div>

        <!-- Active Dofollow -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active DoFollow</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-shield-check text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($activeDofollowCount) }}</div>
            <span class="text-[11px] font-bold text-emerald-700 mt-1 flex items-center gap-1">
                <i class="bi bi-arrow-up-right"></i> {{ $totalCount > 0 ? round(($activeDofollowCount / $totalCount) * 100) : 0 }}% of total portfolio (PageRank Equity)
            </span>
        </div>

        <!-- Referral Traffic -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Referral Visits</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="bi bi-people-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-blue-600">{{ number_format($totalReferrals) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Direct clicks via UTM & HTTP referrers</span>
        </div>

        <!-- Attributed Revenue -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Attributed Bookings</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-cash-stack text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600">AED {{ number_format($totalAttributedRevenue, 2) }}</div>
            <span class="text-[11px] font-bold text-amber-700 mt-1 flex items-center gap-1">
                <i class="bi bi-check-circle"></i> Direct booking revenue from referrals
            </span>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.backlinks.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active (Confirmed Live)</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Crawl</option>
                    <option value="lost" {{ $status === 'lost' ? 'selected' : '' }}>Lost (Not Found)</option>
                    <option value="broken" {{ $status === 'broken' ? 'selected' : '' }}>Broken / HTTP Error</option>
                </select>

                <!-- Rel Type Filter -->
                <select name="rel_type" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Link Attributes</option>
                    <option value="dofollow" {{ $relType === 'dofollow' ? 'selected' : '' }}>DoFollow (SEO Equity)</option>
                    <option value="nofollow" {{ $relType === 'nofollow' ? 'selected' : '' }}>NoFollow</option>
                    <option value="ugc" {{ $relType === 'ugc' ? 'selected' : '' }}>UGC (User Generated)</option>
                    <option value="sponsored" {{ $relType === 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                </select>

                <!-- Platform Filter -->
                @if($platforms->isNotEmpty())
                    <select name="platform" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">All Platforms</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" {{ $platform === $p ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $p)) }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <!-- Search Field -->
            <div class="flex items-center gap-2">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search domain, URL, anchor..." class="w-full pl-9 pr-4 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <i class="bi bi-search absolute left-3 top-2 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                @if($status || $relType || $platform || $search)
                    <a href="{{ route('admin.backlinks.index') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Backlinks Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Referring Source Domain / Page</th>
                        <th class="py-3 px-4">Target Landing Page & Anchor</th>
                        <th class="py-3 px-3 text-center">DA</th>
                        <th class="py-3 px-3 text-center">Type</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Referrals & Rev</th>
                        <th class="py-3 px-4 text-center">Health Check</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($backlinks as $link)
                        <tr class="hover:bg-slate-50/70 transition-colors" id="backlink-row-{{ $link->id }}">
                            <!-- Source Domain / Page -->
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5 truncate">
                                    <span>{{ $link->source_domain ?: 'External Link' }}</span>
                                    <a href="{{ $link->source_url }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary transition" title="Open source page in new tab">
                                        <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-400 truncate mt-0.5" title="{{ $link->source_url }}">
                                    {{ Str::limit($link->source_url, 45) }}
                                </div>
                                @if($link->partner_name)
                                    <div class="text-[10px] text-amber-600 font-semibold mt-0.5">
                                        <i class="bi bi-person text-[9px]"></i> {{ $link->partner_name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Target Page & Anchor -->
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-semibold text-slate-800 flex items-center gap-1 truncate">
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-[10px] text-slate-600 font-mono">
                                        {{ parse_url($link->target_url, PHP_URL_PATH) ?: '/' }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                    <span class="text-slate-400">Anchor:</span>
                                    <span class="font-medium text-slate-700 italic">"{{ $link->anchor_text ?: 'Raw URL / Image' }}"</span>
                                </div>
                            </td>

                            <!-- Domain Authority -->
                            <td class="py-3 px-3 text-center">
                                @if($link->domain_authority)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-black {{ $link->domain_authority >= 50 ? 'bg-emerald-100 text-emerald-800' : ($link->domain_authority >= 30 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $link->domain_authority }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>

                            <!-- Rel Type & Platform -->
                            <td class="py-3 px-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $link->rel_type === 'dofollow' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $link->rel_type }}
                                </span>
                                @if($link->platform)
                                    <div class="text-[9px] text-slate-400 uppercase tracking-wider mt-0.5">
                                        {{ str_replace('_', ' ', $link->platform) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3 px-3 text-center" id="status-badge-{{ $link->id }}">
                                @if($link->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @elseif($link->status === 'lost')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Lost
                                    </span>
                                @elseif($link->status === 'broken')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Broken
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Referrals & Revenue -->
                            <td class="py-3 px-4 text-right">
                                <div class="font-bold text-slate-900">{{ number_format($link->referral_visits) }} visits</div>
                                <div class="text-[11px] font-semibold text-emerald-600">
                                    AED {{ number_format($link->attributed_revenue_aed, 2) }}
                                </div>
                            </td>

                            <!-- Health Check & Last Crawled -->
                            <td class="py-3 px-4 text-center">
                                <div class="text-[10px] text-slate-400" id="last-crawled-{{ $link->id }}">
                                    {{ $link->last_crawled_at ? $link->last_crawled_at->diffForHumans() : 'Never checked' }}
                                </div>
                                <button type="button" @click="verifySingleLink({{ $link->id }})" class="mt-1 text-[10px] font-bold text-blue-600 hover:text-blue-800 underline flex items-center justify-center gap-1 mx-auto">
                                    <i class="bi bi-arrow-repeat text-[9px]" :id="'verify-spinner-' + {{ $link->id }}"></i> Check Now
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEditModal({{ json_encode($link) }})" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Edit Link Details">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.backlinks.destroy', $link->id) }}" onsubmit="return confirm('Are you sure you want to stop tracking this backlink?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                            <i class="bi bi-trash3 text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="bi bi-link-45deg text-xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No Backlinks Tracked Yet</h3>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Start tracking your off-page citations, guest posts, travel portal listings, and syndicated blogger links.</p>
                                <button type="button" @click="openCreateModal()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary-dark transition">
                                    <i class="bi bi-plus-lg"></i> Add First Backlink
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($backlinks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $backlinks->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="closeModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-xl overflow-hidden p-6 z-10">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-base font-black text-slate-900" x-text="isEditMode ? 'Edit Tracked Backlink' : 'Add New Tracked Backlink'"></h3>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">

                    <!-- Source URL -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Referring Source URL (Where link is published) <span class="text-rose-500">*</span>
                        </label>
                        <input type="url" name="source_url" x-model="formData.source_url" required placeholder="https://external-travel-blog.com/top-dubai-safaris" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <!-- Target Landing Page -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Target Dunes URL (Our page being linked) <span class="text-rose-500">*</span>
                        </label>
                        <input type="url" name="target_url" x-model="formData.target_url" required placeholder="https://dunesdiscoverytourism.com/evening-desert-safari-dubai" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Anchor Text -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Anchor Text</label>
                            <input type="text" name="anchor_text" x-model="formData.anchor_text" placeholder="e.g. Best Evening Desert Safari" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>

                        <!-- Link Rel Type -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Rel Attribute <span class="text-rose-500">*</span></label>
                            <select name="rel_type" x-model="formData.rel_type" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                <option value="dofollow">DoFollow (Passes SEO Equity)</option>
                                <option value="nofollow">NoFollow</option>
                                <option value="ugc">UGC (User Generated Content)</option>
                                <option value="sponsored">Sponsored / Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Platform Type -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Platform Type</label>
                            <select name="platform" x-model="formData.platform" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                <option value="travel_directory">Travel Directory</option>
                                <option value="guest_post">Guest Article</option>
                                <option value="blog_mention">Blogger Review</option>
                                <option value="partner_portal">Partner Portal</option>
                                <option value="forum">Forum / Community</option>
                                <option value="government_citation">Gov / Tourism Board</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Initial Status -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="formData.status" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="lost">Lost</option>
                                <option value="broken">Broken</option>
                            </select>
                        </div>

                        <!-- DA -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Domain Authority (DA)</label>
                            <input type="number" min="0" max="100" name="domain_authority" x-model="formData.domain_authority" placeholder="1-100" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Partner / Contact Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Partner / Webmaster</label>
                            <input type="text" name="partner_name" x-model="formData.partner_name" placeholder="John Doe / Editor" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>

                        <!-- Campaign Tag -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campaign Tag</label>
                            <input type="text" name="campaign" x-model="formData.campaign" placeholder="e.g. q4_offpage_boost" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Internal Notes</label>
                        <textarea name="notes" x-model="formData.notes" rows="2" placeholder="Key contact info, placement details, or expiration agreement..." class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                    </div>

                    <!-- Immediate live crawl checkbox -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="live_check" name="live_check" value="1" x-model="formData.live_check" class="rounded border-slate-300 text-primary focus:ring-primary">
                        <label for="live_check" class="text-xs font-medium text-slate-700">Run live crawl & verify backlink health immediately upon saving</label>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark shadow-xs transition" x-text="isEditMode ? 'Update Backlink' : 'Save & Track Link'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function backlinkManager() {
    return {
        isModalOpen: false,
        isEditMode: false,
        isVerifyingAll: false,
        toastMessage: '',
        toastType: 'success',
        formAction: '{{ route('admin.backlinks.store') }}',
        formData: {
            source_url: '',
            target_url: '{{ url('/evening-desert-safari-dubai') }}',
            anchor_text: '',
            rel_type: 'dofollow',
            status: 'active',
            platform: 'travel_directory',
            domain_authority: '',
            partner_name: '',
            campaign: 'q4_offpage_boost',
            notes: '',
            live_check: true,
        },
        openCreateModal() {
            this.isEditMode = false;
            this.formAction = '{{ route('admin.backlinks.store') }}';
            this.formData = {
                source_url: '',
                target_url: '{{ url('/evening-desert-safari-dubai') }}',
                anchor_text: '',
                rel_type: 'dofollow',
                status: 'active',
                platform: 'travel_directory',
                domain_authority: '',
                partner_name: '',
                campaign: 'q4_offpage_boost',
                notes: '',
                live_check: true,
            };
            this.isModalOpen = true;
        },
        openEditModal(link) {
            this.isEditMode = true;
            this.formAction = '{{ url('/admin/backlinks') }}/' + link.id;
            this.formData = {
                source_url: link.source_url || '',
                target_url: link.target_url || '',
                anchor_text: link.anchor_text || '',
                rel_type: link.rel_type || 'dofollow',
                status: link.status || 'active',
                platform: link.platform || 'travel_directory',
                domain_authority: link.domain_authority || '',
                partner_name: link.partner_name || '',
                campaign: link.campaign || '',
                notes: link.notes || '',
                live_check: false,
            };
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
        async verifySingleLink(id) {
            const spinner = document.getElementById('verify-spinner-' + id);
            if (spinner) spinner.classList.add('animate-spin');

            try {
                const response = await fetch('{{ url('/admin/backlinks') }}/' + id + '/verify', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();

                if (data.success) {
                    this.toastType = 'success';
                    this.toastMessage = 'Link verified: HTTP ' + data.http_status + ' (' + data.status + ')';
                    
                    const lastCrawledEl = document.getElementById('last-crawled-' + id);
                    if (lastCrawledEl) lastCrawledEl.textContent = 'Just now';

                    const statusBadgeEl = document.getElementById('status-badge-' + id);
                    if (statusBadgeEl) {
                        if (data.status === 'active') {
                            statusBadgeEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active</span>';
                        } else if (data.status === 'lost') {
                            statusBadgeEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Lost</span>';
                        } else {
                            statusBadgeEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> ' + data.status + '</span>';
                        }
                    }
                } else {
                    this.toastType = 'error';
                    this.toastMessage = 'Verification failed.';
                }
            } catch (err) {
                this.toastType = 'error';
                this.toastMessage = 'Error connecting to server for verification.';
            } finally {
                if (spinner) spinner.classList.remove('animate-spin');
            }
        },
        async verifyAllLinks() {
            this.isVerifyingAll = true;
            try {
                const response = await fetch('{{ route('admin.backlinks.verify-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.toastType = 'success';
                    this.toastMessage = data.message;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.toastType = 'error';
                    this.toastMessage = 'Batch verification failed.';
                }
            } catch (err) {
                this.toastType = 'error';
                this.toastMessage = 'Network error during verification.';
            } finally {
                this.isVerifyingAll = false;
            }
        }
    };
}
</script>
@endsection
