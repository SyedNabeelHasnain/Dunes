@extends('layouts.admin')

@section('page_title', 'Customer Reviews & Ratings')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Reviews & Ratings Hub</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer testimonials, moderate Google/TripAdvisor reviews, and showcase ratings on the website.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Prominent Google Reviews Sync Button -->
            <button type="button" 
                    id="syncGoogleReviewsBtn" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-blue-200/80 bg-blue-50/90 hover:bg-blue-100 text-blue-700 font-bold text-xs shadow-2xs hover:shadow-xs transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                    title="Pull latest live customer reviews from Google Places API">
                <i class="bi bi-arrow-repeat text-sm sync-icon transition-transform"></i>
                <span class="btn-text">Sync Google Reviews</span>
                <span class="inline-flex items-center text-[10px] font-medium bg-blue-200/60 text-blue-800 px-1.5 py-0.5 rounded-md last-synced-badge" title="Last sync timestamp">
                    {{ !empty($lastSyncedAt) ? \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() : 'Never' }}
                </span>
            </button>

            <button type="button" @click="$dispatch('open-add-review')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                <i class="bi bi-plus-lg"></i> Log New Review
            </button>
        </div>
    </div>

    <!-- 4 Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Reviews</span>
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i class="bi bi-chat-heart-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">All logged customer feedback</div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Average Rating</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200">
                    <i class="bi bi-star-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">★ {{ number_format($stats['avg_rating'] ?? 5.0, 1) }}</div>
                <div class="text-xs font-bold text-emerald-600 mt-1">{{ $stats['five_star_pct'] ?? 100 }}% 5-Star Reviews</div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Live on Site</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                    <i class="bi bi-check-circle-fill text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['approved'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">Approved testimonials</div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Moderation</span>
                <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center border border-rose-200">
                    <i class="bi bi-clock-history text-lg"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl font-black text-rose-600">{{ number_format($stats['pending'] ?? 0) }}</div>
                <div class="text-xs text-slate-400 mt-1">Awaiting approval</div>
            </div>
        </div>
    </div>

    <!-- 2 Interactive Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-amber-500"></i> Star Rating Distribution
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Customer satisfaction across star ratings</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="ratingDistributionChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Review Sources
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Google vs TripAdvisor vs Direct</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="reviewSourceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
                <label for="reviewSearch" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Search Reviews</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" id="reviewSearch" class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden focus:border-primary transition" placeholder="Reviewer, Title, Content..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="lg:col-span-3">
                <label for="ratingFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Rating</label>
                <select name="rating" id="ratingFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Ratings</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars ★★★★★</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars ★★★★☆</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars ★★★☆☆</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars ★★☆☆☆</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star ★☆☆☆☆</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="statusFilter" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                <select name="status" id="statusFilter" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden bg-white focus:border-primary">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Live)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="lg:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Reviews Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="reviewsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Reviewer</th>
                        <th class="py-3 px-4">Review Content</th>
                        <th class="py-3 px-4">Source</th>
                        <th class="py-3 px-4 text-center">Rating</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reviews as $r)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                @if(!empty($r->reviewer_avatar_url))
                                    <img src="{{ $r->reviewer_avatar_url }}" alt="{{ $r->reviewer_name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0" loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-xs truncate">
                                        @if(!empty($r->reviewer_profile_url))
                                            <a href="{{ $r->reviewer_profile_url }}" target="_blank" rel="noopener" class="hover:text-primary transition underline-offset-2 hover:underline">
                                                {{ $r->reviewer_name }}
                                            </a>
                                        @else
                                            {{ $r->reviewer_name }}
                                        @endif
                                    </div>
                                    @if(!empty($r->reviewer_reviews_count))
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $r->reviewer_reviews_count }} reviews</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                @if($r->is_featured)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="bi bi-star-fill text-amber-500"></i> Featured
                                    </span>
                                @endif
                                @if($r->is_local_guide)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200" title="Google Local Guide">
                                        <i class="bi bi-patch-check-fill text-orange-500"></i> Local Guide
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if(!empty($r->review_title))
                                <div class="font-bold text-slate-800 text-xs mb-0.5">{{ $r->review_title }}</div>
                            @endif
                            <div class="text-slate-600 text-xs line-clamp-3">{{ $r->review_text }}</div>
                            @if(!empty($r->photos) && is_array($r->photos))
                                <div class="flex items-center gap-1.5 mt-2">
                                    @foreach($r->photos as $p)
                                        <a href="{{ asset($p) }}" target="_blank" rel="noopener" title="View customer photo">
                                            <img src="{{ asset($p) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shadow-2xs hover:scale-105 transition-transform">
                                        </a>
                                    @endforeach
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ count($r->photos) }} photo(s)</span>
                                </div>
                            @endif
                            @if(!empty($r->owner_response_text))
                                <div class="mt-2 p-2 rounded-xl bg-blue-50/60 border border-blue-100 text-[11px] text-blue-900">
                                    <div class="font-bold flex items-center gap-1 text-[10px] text-blue-700 mb-0.5 uppercase tracking-wide">
                                        <i class="bi bi-reply-fill"></i> Response from owner
                                        @if(!empty($r->owner_response_date))
                                            <span class="text-slate-400 font-normal">({{ \Carbon\Carbon::parse($r->owner_response_date)->format('M j, Y') }})</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-600 line-clamp-2">{{ $r->owner_response_text }}</p>
                                </div>
                            @endif
                            @if(!empty($r->visited_in) || !empty($r->likes_count))
                                <div class="flex items-center gap-3 mt-1.5 text-[10px] text-slate-400">
                                    @if(!empty($r->visited_in))
                                        <span><i class="bi bi-geo-alt"></i> Visited: {{ $r->visited_in }}</span>
                                    @endif
                                    @if(!empty($r->likes_count) && $r->likes_count > 0)
                                        <span><i class="bi bi-hand-thumbs-up"></i> {{ $r->likes_count }} like(s)</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4" data-order="{{ $r->source }}">
                            @php
                                $src = strtolower($r->source ?? '');
                            @endphp
                            @if($src === 'google')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/90 shadow-2xs">
                                    <i class="bi bi-google text-blue-600"></i> Google
                                </span>
                            @elseif($src === 'tripadvisor')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/90 shadow-2xs">
                                    <i class="bi bi-compass-fill text-emerald-600"></i> TripAdvisor
                                </span>
                            @elseif($src === 'direct_ugc' || $src === 'direct')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200/90 shadow-2xs">
                                    <i class="bi bi-patch-check-fill text-purple-600"></i> Direct UGC
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/90 shadow-2xs capitalize">
                                    <i class="bi bi-pencil-square text-slate-500"></i> {{ $r->source ?: 'Manual' }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center" data-order="{{ $r->rating }}">
                            <div class="text-amber-400 flex items-center justify-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $r->rating ? '-fill' : '' }} text-xs"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center" data-order="{{ $r->status }}">
                            @php
                                $badgeStyle = [
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200'
                                ][$r->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize badge-interactive ajax-toggle-status {{ $badgeStyle }} cursor-pointer" data-url="{{ route('admin.reviews.toggle-status', $r->id) }}" title="Click to toggle status">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4" data-order="{{ $r->published_date ? \Carbon\Carbon::parse($r->published_date)->timestamp : 0 }}">
                            <div class="text-xs font-medium text-slate-600">
                                {{ $r->published_date ? \Carbon\Carbon::parse($r->published_date)->format('M j, Y') : '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" 
                                        class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-review-btn cursor-pointer" 
                                        title="Edit Review"
                                        data-id="{{ $r->id }}"
                                        data-name="{{ $r->reviewer_name }}"
                                        data-title="{{ $r->review_title }}"
                                        data-text="{{ $r->review_text }}"
                                        data-source="{{ $r->source }}"
                                        data-rating="{{ $r->rating }}"
                                        data-status="{{ $r->status }}"
                                        data-featured="{{ $r->is_featured ? '1' : '0' }}"
                                        data-date="{{ $r->published_date }}"
                                        data-action="{{ route('admin.reviews.update', $r->id) }}">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </button>
                                <form action="{{ route('admin.reviews.destroy', $r->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this review?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Delete Review">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400 text-xs">
                            <i class="bi bi-chat-square-dots text-3xl block mb-2 opacity-50"></i>
                            No customer reviews match your filter parameters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Review (Alpine.js) -->
<div x-data="{ open: false }" @open-add-review.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-chat-square-quote text-primary"></i> Log New Review
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.reviews.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="c_reviewer_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Reviewer Name *</label>
                    <input type="text" name="reviewer_name" id="c_reviewer_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required placeholder="e.g. John Doe">
                </div>
                <div>
                    <label for="c_review_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Review Title</label>
                    <input type="text" name="review_title" id="c_review_title" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="e.g. Unforgettable Experience!">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c_source" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Source</label>
                        <select name="source" id="c_source" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="manual">Manual</option>
                            <option value="google">Google Reviews</option>
                            <option value="tripadvisor">TripAdvisor</option>
                        </select>
                    </div>
                    <div>
                        <label for="c_rating" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Rating (1-5)</label>
                        <select name="rating" id="c_rating" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="5">5 Stars ★★★★★</option>
                            <option value="4">4 Stars ★★★★☆</option>
                            <option value="3">3 Stars ★★★☆☆</option>
                            <option value="2">2 Stars ★★☆☆☆</option>
                            <option value="1">1 Star ★☆☆☆☆</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="c_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label for="c_published_date" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Published Date</label>
                        <input type="date" name="published_date" id="c_published_date" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div>
                    <label for="c_review_text" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Review Text *</label>
                    <textarea name="review_text" id="c_review_text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" rows="4" required placeholder="Paste the customer review details here..."></textarea>
                </div>
                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_featured" id="c_is_featured" value="1" checked>
                    <span class="text-xs font-bold text-slate-800">Show on Home Page Marquee</span>
                </label>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Log Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Review (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-review.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Modify Customer Review
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="editReviewForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="e_reviewer_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Reviewer Name *</label>
                    <input type="text" name="reviewer_name" id="e_reviewer_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div>
                    <label for="e_review_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Review Title</label>
                    <input type="text" name="review_title" id="e_review_title" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="e_source" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Source</label>
                        <select name="source" id="e_source" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="manual">Manual</option>
                            <option value="google">Google Reviews</option>
                            <option value="tripadvisor">TripAdvisor</option>
                        </select>
                    </div>
                    <div>
                        <label for="e_rating" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Rating (1-5)</label>
                        <select name="rating" id="e_rating" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="5">5 Stars ★★★★★</option>
                            <option value="4">4 Stars ★★★★☆</option>
                            <option value="3">3 Stars ★★★☆☆</option>
                            <option value="2">2 Stars ★★☆☆☆</option>
                            <option value="1">1 Star ★☆☆☆☆</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="e_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status</label>
                        <select name="status" id="e_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label for="e_published_date" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Published Date</label>
                        <input type="date" name="published_date" id="e_published_date" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary">
                    </div>
                </div>
                <div>
                    <label for="e_review_text" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Review Text *</label>
                    <textarea name="review_text" id="e_review_text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" rows="4" required></textarea>
                </div>
                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <input class="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4" type="checkbox" name="is_featured" id="e_is_featured" value="1">
                    <span class="text-xs font-bold text-slate-800">Show on Home Page Marquee</span>
                </label>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Update Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingDistributionChart');
    if (ratingCtx) {
        const ratingData = @json($ratingDistribution);
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ['5 Stars ★', '4 Stars ★', '3 Stars ★', '2 Stars ★', '1 Star ★'],
                datasets: [{
                    label: 'Reviews',
                    data: [ratingData[5] || 0, ratingData[4] || 0, ratingData[3] || 0, ratingData[2] || 0, ratingData[1] || 0],
                    backgroundColor: ['#F58F43', '#fbbf24', '#fcd34d', '#9ca3af', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Review Source Breakdown Doughnut Chart
    const sourceCtx = document.getElementById('reviewSourceChart');
    if (sourceCtx) {
        const sourceData = @json($sourceBreakdown);
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: sourceData.map(s => (s.source || 'Direct').toUpperCase()),
                datasets: [{
                    data: sourceData.map(s => s.count),
                    backgroundColor: ['#4285F4', '#00af87', '#F58F43', '#8b5cf6'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    // Edit Review Modal handler
    $('.edit-review-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const title = $(this).data('title');
        const text = $(this).data('text');
        const source = $(this).data('source');
        const rating = $(this).data('rating');
        const status = $(this).data('status');
        const featured = $(this).data('featured');
        const date = $(this).data('date');
        const action = $(this).data('action') || `/admin/reviews/${id}`;

        $('#e_reviewer_name').val(name);
        $('#e_review_title').val(title);
        $('#e_review_text').val(text);
        $('#e_source').val(source);
        $('#e_rating').val(rating);
        $('#e_status').val(status);
        $('#e_published_date').val(date);
        
        $('#e_is_featured').prop('checked', featured == '1');

        // Set action url
        $('#editReviewForm').attr('action', action);

        // Show Alpine modal
        window.dispatchEvent(new CustomEvent('open-edit-review'));
    });

    // Automated Google Review Sync AJAX handler with SweetAlert2 feedback
    $('#syncGoogleReviewsBtn').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $btn.find('.sync-icon');
        const $text = $btn.find('.btn-text');
        const originalText = $text.text();

        $btn.prop('disabled', true);
        $icon.addClass('animate-spin');
        $text.text('Syncing with Google...');

        $.ajax({
            url: "{{ route('admin.reviews.sync-google') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: {
                force: 0
            },
            success: function(res) {
                const added = res.added ?? 0;
                const updated = res.updated ?? 0;
                const total = res.total ?? 0;
                const rating = res.place_rating ?? '5.0';
                const ratingsCount = res.user_ratings_total ?? 329;
                const msg = res.message || 'Synchronization complete.';

                Swal.fire({
                    icon: 'success',
                    title: 'Google Reviews Synced!',
                    html: `
                        <div class="text-left text-xs space-y-3 py-1">
                            <div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200">
                                <strong>${msg}</strong>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-slate-700">
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Google Profile Rating</span>
                                    <span class="text-base font-black text-amber-500">★ ${rating}</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Google Total Reviews</span>
                                    <span class="text-base font-black text-blue-600">${ratingsCount}+</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Stored in Database</span>
                                    <span class="text-base font-black text-slate-900">${total}</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Batch Updated</span>
                                    <span class="text-base font-black text-emerald-600">${updated} updated (+${added} new)</span>
                                </div>
                            </div>

                            <div class="p-2.5 bg-blue-50/70 text-blue-800 rounded-lg border border-blue-200/60 text-[11px] leading-relaxed">
                                <i class="bi bi-info-circle-fill text-blue-600 me-1"></i>
                                <strong>How Google Sync Works:</strong> Google Places API delivers real-time sync batches (up to 5 newest reviews per pull). The system automatically saves every newly published review to your database catalog (${total} saved) so no reviews are ever lost.
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#F58F43',
                    confirmButtonText: 'Refresh Table',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                if (xhr.status === 429) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Rate Limit Cooldown',
                        text: res.message || 'Google reviews were synchronized recently. Please wait a minute before requesting another sync.',
                        confirmButtonColor: '#F58F43'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sync Failed',
                        text: res.message || 'Unable to sync reviews from Google. Please verify your Google API key and Place ID settings.',
                        confirmButtonColor: '#F58F43'
                    });
                }
            },
            complete: function() {
                $btn.prop('disabled', false);
                $icon.removeClass('animate-spin');
                $text.text(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection
