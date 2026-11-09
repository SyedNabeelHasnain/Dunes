@extends('layouts.admin')

@section('page_title', 'Customer Video Reviews & Video SEO')

@section('content')
<div class="space-y-6" x-data="{
    editModalOpen: false,
    addModalOpen: false,
    previewModalOpen: false,
    previewVideoUrl: '',
    previewVideoTitle: '',
    editingItem: null,

    // AI Analysis States
    isAnalyzingAdd: false,
    analysisStageAdd: '',
    analysisSuccessAdd: false,
    analysisErrorAdd: '',

    isAnalyzingEdit: false,
    analysisStageEdit: '',
    analysisSuccessEdit: false,
    analysisErrorEdit: '',

    addForm: {
        title: '',
        reviewer_name: '',
        reviewer_country: '',
        reviewer_country_code: '',
        category: 'red_dunes',
        tour_slug: '',
        rating: '5.0',
        duration: 30,
        transcript: '',
        caption: '',
        location: 'Lahbab Red Dunes, Dubai, UAE',
        description: '',
        keywords: '',
        existing_filename: '',
        existing_poster: '',
        poster_preview_url: '',
        placement_home: true,
        placement_gallery: true,
        placement_tour: true,
        is_active: true
    },

    resetAddForm() {
        this.addForm = {
            title: '',
            reviewer_name: '',
            reviewer_country: '',
            reviewer_country_code: '',
            category: 'red_dunes',
            tour_slug: '',
            rating: '5.0',
            duration: 30,
            transcript: '',
            caption: '',
            location: 'Lahbab Red Dunes, Dubai, UAE',
            description: '',
            keywords: '',
            existing_filename: '',
            existing_poster: '',
            poster_preview_url: '',
            placement_home: true,
            placement_gallery: true,
            placement_tour: true,
            is_active: true
        };
        this.isAnalyzingAdd = false;
        this.analysisStageAdd = '';
        this.analysisSuccessAdd = false;
        this.analysisErrorAdd = '';
        const addFileInput = document.getElementById('add_video_file');
        if (addFileInput) addFileInput.value = '';
    },

    openEdit(item) {
        this.editingItem = JSON.parse(JSON.stringify(item));
        this.isAnalyzingEdit = false;
        this.analysisStageEdit = '';
        this.analysisSuccessEdit = false;
        this.analysisErrorEdit = '';
        this.editModalOpen = true;
    },

    openPreview(url, title) {
        this.previewVideoUrl = url;
        this.previewVideoTitle = title;
        this.previewModalOpen = true;
    },

    closePreview() {
        this.previewModalOpen = false;
        this.previewVideoUrl = '';
    },

    async analyzeVideo(context = 'add') {
        const isAdd = context === 'add';
        const formEl = isAdd 
            ? document.getElementById('addVideoReviewForm') 
            : document.getElementById('editVideoReviewForm');
        
        if (!formEl) return;

        const formData = new FormData(formEl);
        
        // Ensure existing_filename is populated from state if hidden input was not updated
        const stateFilename = isAdd ? this.addForm.existing_filename : (this.editingItem?.filename || '');
        if (!formData.get('existing_filename') && stateFilename) {
            formData.set('existing_filename', stateFilename);
        }

        const videoFile = formData.get('video_file');
        const hasFile = videoFile instanceof File && videoFile.size > 0;
        const hasExisting = Boolean(formData.get('existing_filename'));

        if (!hasFile && !hasExisting) {
            const msg = 'Please choose a video from the library or select a direct video file first.';
            if (isAdd) {
                this.analysisErrorAdd = msg;
            } else {
                this.analysisErrorEdit = msg;
            }
            return;
        }

        if (isAdd) {
            this.isAnalyzingAdd = true;
            this.analysisSuccessAdd = false;
            this.analysisErrorAdd = '';
            this.analysisStageAdd = 'Inspecting video stream & duration...';
        } else {
            this.isAnalyzingEdit = true;
            this.analysisSuccessEdit = false;
            this.analysisErrorEdit = '';
            this.analysisStageEdit = 'Inspecting video stream & duration...';
        }

        const stages = [
            'Inspecting video stream & duration...',
            'Extracting HD WebP & JPG poster frames...',
            'Extracting audio & transcribing speech verbatim...',
            'Translating speech & inferring traveler details...',
            'Matching active tour & generating Video SEO tags...'
        ];
        let stageStep = 0;
        const stageTimer = setInterval(() => {
            stageStep++;
            if (stageStep < stages.length) {
                if (isAdd) {
                    this.analysisStageAdd = stages[stageStep];
                } else {
                    this.analysisStageEdit = stages[stageStep];
                }
            }
        }, 1400);

        try {
            formData.set('_token', '{{ csrf_token() }}');

            const response = await fetch('{{ route('admin.video-reviews.analyze') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            clearInterval(stageTimer);
            const res = await response.json();

            if (res.success && res.data) {
                const d = res.data;
                if (isAdd) {
                    this.addForm.title = d.title || '';
                    this.addForm.reviewer_name = d.reviewer_name || '';
                    this.addForm.reviewer_country = d.reviewer_country || '';
                    this.addForm.reviewer_country_code = d.reviewer_country_code || '';
                    this.addForm.category = d.category || 'red_dunes';
                    this.addForm.tour_slug = d.tour_slug || '';
                    this.addForm.rating = d.rating || 5.0;
                    this.addForm.duration = d.duration || 30;
                    this.addForm.transcript = d.transcript || '';
                    this.addForm.caption = d.caption || '';
                    this.addForm.location = d.location || 'Lahbab Red Dunes, Dubai, UAE';
                    this.addForm.description = d.description || '';
                    this.addForm.keywords = d.keywords || '';
                    this.addForm.existing_filename = d.video_filename || '';
                    this.addForm.existing_poster = d.poster_filename || '';
                    this.addForm.poster_preview_url = d.poster_url || '';
                    this.analysisSuccessAdd = true;
                    this.analysisStageAdd = '✨ Video analyzed! All 14 fields auto-filled.';

                    // Also sync DOM inputs directly
                    ['title', 'reviewer_name', 'reviewer_country', 'reviewer_country_code', 'category', 'tour_slug', 'rating', 'duration', 'transcript', 'caption', 'location', 'description', 'keywords'].forEach(field => {
                        const el = formEl.querySelector(`[name="${field}"]`);
                        if (el && d[field] !== undefined) {
                            el.value = d[field];
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                    const posterEl = formEl.querySelector('[name="existing_poster"]');
                    if (posterEl && d.poster_filename) {
                        posterEl.value = d.poster_filename;
                        posterEl.dispatchEvent(new Event('input', { bubbles: true }));
                        posterEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else {
                    this.editingItem.title = d.title || this.editingItem.title;
                    this.editingItem.reviewer_name = d.reviewer_name || this.editingItem.reviewer_name;
                    this.editingItem.reviewer_country = d.reviewer_country || this.editingItem.reviewer_country;
                    this.editingItem.reviewer_country_code = d.reviewer_country_code || this.editingItem.reviewer_country_code;
                    this.editingItem.category = d.category || this.editingItem.category;
                    this.editingItem.tour_slug = d.tour_slug || this.editingItem.tour_slug;
                    this.editingItem.rating = d.rating || this.editingItem.rating;
                    this.editingItem.duration = d.duration || this.editingItem.duration;
                    this.editingItem.transcript = d.transcript || this.editingItem.transcript;
                    this.editingItem.caption = d.caption || this.editingItem.caption;
                    this.editingItem.location = d.location || this.editingItem.location;
                    this.editingItem.description = d.description || this.editingItem.description;
                    this.editingItem.keywords = d.keywords ? d.keywords.split(',').map(s => s.trim()) : this.editingItem.keywords;
                    if (d.poster_filename) {
                        this.editingItem.poster_filename = d.poster_filename;
                    }
                    this.analysisSuccessEdit = true;
                    this.analysisStageEdit = '✨ Video analyzed! Fields refreshed from AI.';
                }
            } else {
                const err = res.message || 'Video analysis failed. Please verify the file.';
                if (isAdd) {
                    this.analysisErrorAdd = err;
                } else {
                    this.analysisErrorEdit = err;
                }
            }
        } catch (e) {
            clearInterval(stageTimer);
            const err = 'Failed to analyze video: ' + (e.message || 'Network error');
            if (isAdd) {
                this.analysisErrorAdd = err;
            } else {
                this.analysisErrorEdit = err;
            }
        } finally {
            if (isAdd) {
                this.isAnalyzingAdd = false;
            } else {
                this.isAnalyzingEdit = false;
            }
        }
    },

    async togglePlacement(id, placement, event) {
        const btn = event.currentTarget;
        const currentActive = btn.dataset.active === '1';
        btn.disabled = true;
        try {
            const res = await fetch(`/admin/video-reviews/${id}/toggle-placement`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ placement: placement })
            });
            const data = await res.json();
            if (data.success) {
                btn.dataset.active = data.value ? '1' : '0';
                if (data.value) {
                    btn.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-primary text-white transition-all shadow-2xs';
                } else {
                    btn.className = 'px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all';
                }
            }
        } catch (e) {
            alert('Failed to update placement.');
        } finally {
            btn.disabled = false;
        }
    },

    async toggleStatus(id, event) {
        const btn = event.currentTarget;
        btn.disabled = true;
        try {
            const res = await fetch(`/admin/video-reviews/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            }
        } catch (e) {
            alert('Failed to toggle status.');
            btn.disabled = false;
        }
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                    <i class="bi bi-camera-reels-fill text-xs"></i> Video Intelligence & SEO
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="bi bi-broadcast text-xs"></i> Google Video Sitemap Active
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Video Reviews Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage real customer video testimonials, optimize VideoObject Schema, transcripts for AISEO & SERPs, and toggle multi-surface website placements.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('sitemap.videos') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs hover:shadow-xs transition-all">
                <i class="bi bi-diagram-3-fill text-amber-500"></i> Video XML Sitemap
            </a>
            <a href="{{ localized_route('gallery.index', ['category' => 'videos']) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs hover:shadow-xs transition-all">
                <i class="bi bi-box-arrow-up-right text-primary"></i> View on Gallery
            </a>
            <button type="button" @click="resetAddForm(); addModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                <i class="bi bi-plus-lg"></i> Add Video Review
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="text-emerald-600 hover:text-emerald-900" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- 5 Performance Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
        <!-- Card 1: Total Videos -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Videos</span>
                <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <i class="bi bi-camera-video-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Verified review clips</div>
        </div>

        <!-- Card 2: Active on Site -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Live</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check-circle-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['active'] }}</div>
            <div class="text-[11px] text-emerald-600 font-bold mt-0.5">100% In Feed</div>
        </div>

        <!-- Card 3: Homepage Featured -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Homepage Reels</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-stars text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['home'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Homepage placement</div>
        </div>

        <!-- Card 4: Tour Page Linked -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tour Page Placements</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <i class="bi bi-compass-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['tour'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Linked to tours</div>
        </div>

        <!-- Card 5: Total Playtime -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Duration</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="bi bi-stopwatch-fill text-sm"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['total_duration'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">High retention video</div>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.video-reviews.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex-1 flex flex-wrap items-center gap-2.5">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[220px]">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Search title, traveler, country, transcript..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>

                <!-- Category Filter -->
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="all">All Categories</option>
                    @foreach($categories as $catKey => $cat)
                        @if($catKey !== 'all' && $catKey !== 'videos')
                            <option value="{{ $catKey }}" {{ $categoryFilter === $catKey ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                        @endif
                    @endforeach
                </select>

                <!-- Placement Filter -->
                <select name="placement" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="all" {{ $placementFilter === 'all' ? 'selected' : '' }}>All Placements</option>
                    <option value="home" {{ $placementFilter === 'home' ? 'selected' : '' }}>Homepage Placements</option>
                    <option value="gallery" {{ $placementFilter === 'gallery' ? 'selected' : '' }}>Gallery Placements</option>
                    <option value="tour" {{ $placementFilter === 'tour' ? 'selected' : '' }}>Tour Page Placements</option>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($search !== '' || $categoryFilter !== 'all' || $placementFilter !== 'all')
                    <a href="{{ route('admin.video-reviews.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-all">
                        Reset
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-all">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Videos Table / Grid View -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Video & Preview</th>
                        <th class="py-3 px-4">Title & Spoken Transcript</th>
                        <th class="py-3 px-4">Traveler & Location</th>
                        <th class="py-3 px-4">Assigned Tour</th>
                        <th class="py-3 px-4">Placements</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($filtered as $video)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Video Thumbnail & Preview -->
                            <td class="py-3.5 px-4 w-44">
                                <div class="relative w-36 aspect-16/9 rounded-xl overflow-hidden bg-slate-900 shadow-2xs group cursor-pointer border border-slate-200"
                                     @click="openPreview('{{ asset('videos/reviews/'.$video['filename']) }}', '{{ addslashes($video['title']) }}')">
                                    <img src="{{ asset('images/video-posters/'.$video['poster_filename']) }}" 
                                         alt="{{ $video['title'] }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-slate-950/30 flex items-center justify-center group-hover:bg-slate-950/15 transition-colors">
                                        <span class="w-8 h-8 rounded-full bg-primary/95 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                                            <i class="bi bi-play-fill text-lg ml-0.5"></i>
                                        </span>
                                    </div>
                                    <span class="absolute bottom-1 right-1 bg-slate-950/85 text-white text-[10px] font-mono font-bold px-1.5 py-0.2 rounded">
                                        {{ sprintf('%02d:%02d', floor(($video['duration'] ?? 30) / 60), ($video['duration'] ?? 30) % 60) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Title & Transcript -->
                            <td class="py-3.5 px-4 max-w-sm">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1 mb-1">{{ $video['title'] }}</div>
                                <div class="text-[11px] text-slate-500 italic line-clamp-2 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <span class="text-primary font-bold mr-1">&ldquo;</span>{{ $video['transcript'] ?: $video['caption'] }}<span class="text-primary font-bold ml-1">&rdquo;</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $categories[$video['category']]['name'] ?? ucfirst($video['category']) }}
                                    </span>
                                    @if(!empty($video['keywords']))
                                        <span class="text-[10px] text-slate-400">
                                            {{ count($video['keywords']) }} SEO keywords
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Traveler & Location -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $video['reviewer_name'] }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i class="bi bi-geo-alt text-amber-500"></i>
                                    <span>{{ $video['reviewer_country'] ?? 'UAE' }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-amber-400 text-xs mt-1">
                                    @for($i = 0; $i < (int)($video['rating'] ?? 5); $i++)
                                        <i class="bi bi-star-fill"></i>
                                    @endfor
                                    <span class="text-[11px] text-slate-600 font-bold ml-1">{{ number_format($video['rating'] ?? 5.0, 1) }}</span>
                                </div>
                            </td>

                            <!-- Assigned Tour -->
                            <td class="py-3.5 px-4">
                                @if(!empty($video['tour_slug']))
                                    <a href="/{{ $video['tour_slug'] }}" target="_blank" class="font-semibold text-primary hover:underline line-clamp-1 flex items-center gap-1" title="View tour page">
                                        <span>{{ $video['tour_name'] ?: $video['tour_slug'] }}</span>
                                        <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $video['tour_slug'] }}</span>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">General Safari</span>
                                @endif
                            </td>

                            <!-- Placements -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <!-- Home -->
                                    <button type="button" 
                                            @click="togglePlacement('{{ $video['id'] }}', 'home', $event)"
                                            data-active="{{ !empty($video['placements']['home']) ? '1' : '0' }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ !empty($video['placements']['home']) ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                            title="Toggle Homepage Reels placement">
                                        Home
                                    </button>

                                    <!-- Gallery -->
                                    <button type="button" 
                                            @click="togglePlacement('{{ $video['id'] }}', 'gallery', $event)"
                                            data-active="{{ !empty($video['placements']['gallery']) ? '1' : '0' }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ !empty($video['placements']['gallery']) ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                            title="Toggle Gallery Videos section placement">
                                        Gallery
                                    </button>

                                    <!-- Tour -->
                                    <button type="button" 
                                            @click="togglePlacement('{{ $video['id'] }}', 'tour', $event)"
                                            data-active="{{ !empty($video['placements']['tour']) ? '1' : '0' }}"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ !empty($video['placements']['tour']) ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                            title="Toggle Tour detail page placement">
                                        Tour
                                    </button>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button" 
                                        @click="toggleStatus('{{ $video['id'] }}', $event)"
                                        class="px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition-all {{ !empty($video['is_active']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                    {{ !empty($video['is_active']) ? 'Active' : 'Inactive' }}
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="openEdit({{ json_encode($video) }})"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all"
                                            title="Edit Video Review & SEO">
                                        <i class="bi bi-pencil-fill text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.video-reviews.destroy', $video['id']) }}" onsubmit="return confirm('Are you sure you want to remove this video review?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-all"
                                                title="Delete Video Review">
                                            <i class="bi bi-trash-fill text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bi bi-camera-video text-3xl mb-2 block"></i>
                                <div>No video reviews found matching your criteria.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Edit Video Review Modal ─────────────────────────────────────────── -->
    <div x-show="editModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="relative bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl my-8 max-h-[90vh] overflow-y-auto border border-slate-200"
             @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Edit Customer Video Review & SEO</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Optimize Schema.org metadata, AISEO transcripts, and tour placement.</p>
                </div>
                <button type="button" @click="editModalOpen = false" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="editVideoReviewForm" x-bind:action="'/admin/video-reviews/' + (editingItem ? editingItem.id : '')" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="placements_submitted" value="1">

                <!-- AI Re-Analyze Banner in Edit Modal -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-primary/80 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md border border-slate-700/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center text-base shrink-0 border border-amber-400/30">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <span>AI Re-Analyze Video Review</span>
                            </div>
                            <div class="text-[10px] text-slate-300">Re-scan speech, translate audio, and refresh SEO metadata for this video clip.</div>
                        </div>
                    </div>

                    <button type="button" 
                            @click="analyzeVideo('edit')" 
                            :disabled="isAnalyzingEdit"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed shrink-0">
                        <template x-if="!isAnalyzingEdit">
                            <span class="flex items-center gap-1.5">
                                <i class="bi bi-lightning-charge-fill text-xs"></i>
                                <span>Re-Analyze with AI</span>
                            </span>
                        </template>
                        <template x-if="isAnalyzingEdit">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-3 w-3 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="analysisStageEdit">Analyzing...</span>
                            </span>
                        </template>
                    </button>
                </div>

                <div x-show="analysisSuccessEdit" x-cloak class="p-2.5 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-800 text-xs flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>
                    <span class="font-semibold" x-text="analysisStageEdit"></span>
                </div>

                <div x-show="analysisErrorEdit" x-cloak class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-octagon-fill text-rose-600 text-sm"></i>
                        <span x-text="analysisErrorEdit"></span>
                    </div>
                    <button type="button" @click="analysisErrorEdit = ''" class="text-rose-500 hover:text-rose-800">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Title -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Video Title (SEO & Schema Name) *</label>
                        <input type="text" name="title" x-model="editingItem.title" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-primary/20">
                    </div>

                    <!-- Reviewer Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Traveler Name(s) *</label>
                        <input type="text" name="reviewer_name" x-model="editingItem.reviewer_name" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Reviewer Country -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Traveler Country</label>
                        <input type="text" name="reviewer_country" x-model="editingItem.reviewer_country" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Activity Category *</label>
                        <select name="category" x-model="editingItem.category" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($categories as $catKey => $cat)
                                @if($catKey !== 'all' && $catKey !== 'videos')
                                    <option value="{{ $catKey }}">{{ $cat['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned Tour -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Link to Tour (Tour Page Placement)</label>
                        <select name="tour_slug" x-model="editingItem.tour_slug" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="">-- No Specific Tour (General Safari) --</option>
                            @foreach($tours as $t)
                                <option value="{{ $t->slug }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rating & Duration -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Star Rating (1.0 - 5.0) *</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" x-model="editingItem.rating" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Duration (Seconds) *</label>
                        <input type="number" min="1" max="3600" name="duration" x-model="editingItem.duration" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Verbatim Transcript (AISEO Magnet) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Spoken Video Transcript (Critical for AI Crawlers, Perplexity & Google Search)
                        </label>
                        <textarea name="transcript" x-model="editingItem.transcript" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono"></textarea>
                    </div>

                    <!-- Description & Caption -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Summary Caption</label>
                        <input type="text" name="caption" x-model="editingItem.caption" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Location</label>
                        <input type="text" name="location" x-model="editingItem.location" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Description (Schema VideoObject.description)</label>
                        <textarea name="description" x-model="editingItem.description" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                    </div>

                    <!-- Keywords -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">SEO Keywords (Comma Separated)</label>
                        <input type="text" name="keywords" :value="editingItem && editingItem.keywords ? editingItem.keywords.join(', ') : ''" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Placements Checkboxes -->
                    <div class="sm:col-span-2 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="block text-xs font-bold text-slate-800 mb-2">Display Placements Across Website:</span>
                        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-700">
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_home" value="1" :checked="editingItem && editingItem.placements && editingItem.placements.home" class="rounded text-primary focus:ring-primary">
                                <span>Homepage Reels Carousel</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_gallery" value="1" :checked="editingItem && editingItem.placements && editingItem.placements.gallery" class="rounded text-primary focus:ring-primary">
                                <span>Gallery Page (Videos Tab)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_tour" value="1" :checked="editingItem && editingItem.placements && editingItem.placements.tour" class="rounded text-primary focus:ring-primary">
                                <span>Tour Detail Page Reviews</span>
                            </label>
                        </div>
                    </div>

                    <!-- File Overwrites / Choose from Library -->
                    <div class="space-y-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <x-media-picker 
                            name="existing_filename" 
                            type="video" 
                            category="reviews" 
                            label="Video Reel File (from Library)" 
                            help="Or upload a direct file below if not yet in library." />
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Direct Video Upload (Optional MP4/WebM)</label>
                            <input type="file" name="video_file" accept="video/mp4,video/webm,video/mov" class="w-full text-xs">
                        </div>
                    </div>

                    <div class="space-y-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <x-media-picker 
                            name="existing_poster" 
                            type="image" 
                            category="reviews" 
                            label="Video Poster Image (from Library)" 
                            help="Or upload a direct image below if not yet in library." />
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Direct Poster Upload (Optional WebP/JPG)</label>
                            <input type="file" name="poster_file" accept="image/*" class="w-full text-xs">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                        <i class="bi bi-check2-circle"></i>
                        <span>Save Video Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Add New Video Review Modal ──────────────────────────────────────── -->
    <div x-show="addModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="relative bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl my-8 max-h-[90vh] overflow-y-auto border border-slate-200"
             @click.away="addModalOpen = false">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Add New Customer Video Review</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Upload a customer video and configure all Video SEO, schema, and placement targets.</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="addVideoReviewForm" action="{{ route('admin.video-reviews.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- ── Step 1: Video File & AI Intelligence Engine ──────────────────── -->
                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-primary/90 text-white shadow-lg border border-slate-700/60 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center text-xl shrink-0 border border-amber-400/30">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div>
                                <div class="text-sm font-black flex items-center gap-2 tracking-tight">
                                    <span>AI Video Review Intelligence</span>
                                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-bold">Auto-Fill</span>
                                </div>
                                <p class="text-[11px] text-slate-300 mt-0.5">Upload a video or pick one from the library, then click Analyze to auto-generate all 14 fields.</p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="analyzeVideo('add')" 
                                :disabled="isAnalyzingAdd"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-md transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed shrink-0">
                            <template x-if="!isAnalyzingAdd">
                                <span class="flex items-center gap-1.5">
                                    <i class="bi bi-lightning-charge-fill text-sm"></i>
                                    <span>⚡ Analyze Video with AI</span>
                                </span>
                            </template>
                            <template x-if="isAnalyzingAdd">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-3.5 w-3.5 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="analysisStageAdd">Analyzing...</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <!-- Video Input Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-900">
                        <div class="bg-white/95 rounded-xl p-3 border border-white/20">
                            <x-media-picker 
                                name="existing_filename" 
                                type="video" 
                                category="reviews" 
                                label="Choose Video Reel (from Library)" 
                                help="Pick any existing customer video review from library." />
                        </div>

                        <div class="bg-white/95 rounded-xl p-3 border border-white/20 flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 tracking-wider mb-1">Or Direct Upload Raw Video (.mp4/.mov)</label>
                                <p class="text-[11px] text-slate-500 mb-2">Upload raw WhatsApp or mobile video file.</p>
                            </div>
                            <input type="file" id="add_video_file" name="video_file" accept="video/mp4,video/webm,video/mov,video/m4v" class="w-full text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer">
                        </div>
                    </div>

                    <!-- Success State Notice -->
                    <div x-show="analysisSuccessAdd" x-cloak class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 text-xs flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-400 text-sm shrink-0"></i>
                            <span class="font-semibold" x-text="analysisStageAdd"></span>
                        </div>
                        <span class="text-[10px] font-mono bg-emerald-400/20 text-emerald-300 px-2 py-0.5 rounded">All Fields Populated</span>
                    </div>

                    <!-- Error State Notice -->
                    <div x-show="analysisErrorAdd" x-cloak class="p-3 rounded-xl bg-rose-500/25 border border-rose-400/40 text-rose-200 text-xs flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-rose-400 text-sm shrink-0"></i>
                            <span x-text="analysisErrorAdd"></span>
                        </div>
                        <button type="button" @click="analysisErrorAdd = ''" class="text-rose-300 hover:text-white">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Generated Poster Preview Card -->
                    <div x-show="addForm.poster_preview_url" x-cloak class="flex items-center gap-3 p-3 bg-white/10 rounded-xl border border-white/15">
                        <img :src="addForm.poster_preview_url" class="w-20 h-14 object-cover rounded-lg border border-white/20 shadow-xs shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-amber-300 flex items-center gap-1.5">
                                <i class="bi bi-image"></i>
                                <span>High-Definition Poster Generated</span>
                            </div>
                            <div class="text-[11px] text-slate-300 font-mono truncate" x-text="addForm.existing_poster"></div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Dual WebP (modern) & JPG (fallback) posters ready.</div>
                        </div>
                    </div>
                </div>

                <!-- ── Step 2: Auto-Filled Details & Video SEO ───────────────────────── -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- Title -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Video Title (Schema & Heading) *</label>
                        <input type="text" name="title" x-model="addForm.title" required placeholder="e.g. Spanish Travelers Red Dunes Safari Review - Dunes Discovery Tourism" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-primary/20">
                    </div>

                    <!-- Traveler Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Traveler Name *</label>
                        <input type="text" name="reviewer_name" x-model="addForm.reviewer_name" required placeholder="e.g. Elena & Friends" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Reviewer Country & ISO Code -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Traveler Country</label>
                            <input type="text" name="reviewer_country" x-model="addForm.reviewer_country" placeholder="e.g. Spain" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Code</label>
                            <input type="text" name="reviewer_country_code" x-model="addForm.reviewer_country_code" maxlength="4" placeholder="ES" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-mono uppercase">
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Activity Category *</label>
                        <select name="category" x-model="addForm.category" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($categories as $catKey => $cat)
                                @if($catKey !== 'all' && $catKey !== 'videos')
                                    <option value="{{ $catKey }}">{{ $cat['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned Tour -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Link to Tour (Tour Page Reviews)</label>
                        <select name="tour_slug" x-model="addForm.tour_slug" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="">-- No Specific Tour (General Safari) --</option>
                            @foreach($tours as $t)
                                <option value="{{ $t->slug }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rating & Duration -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Star Rating *</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" x-model="addForm.rating" value="5.0" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Duration (Seconds) *</label>
                        <input type="number" min="1" max="3600" name="duration" x-model="addForm.duration" value="30" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Transcript -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Spoken Customer Review Transcript (AISEO Indexing & Perplexity Search)
                        </label>
                        <textarea name="transcript" x-model="addForm.transcript" rows="3" placeholder="Enter spoken words verbatim for search engine indexing..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono"></textarea>
                    </div>

                    <!-- Caption & Location -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Summary Caption</label>
                        <input type="text" name="caption" x-model="addForm.caption" placeholder="Short quote or highlight..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Location</label>
                        <input type="text" name="location" x-model="addForm.location" value="Lahbab Red Dunes, Dubai, UAE" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Description (VideoObject Schema)</label>
                        <textarea name="description" x-model="addForm.description" rows="2" placeholder="Detailed context of the experience..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                    </div>

                    <!-- Keywords -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">SEO Keywords (Comma Separated)</label>
                        <input type="text" name="keywords" x-model="addForm.keywords" placeholder="dubai desert safari, buggy review, customer feedback" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Poster Configuration -->
                    <div class="sm:col-span-2 space-y-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <x-media-picker 
                            name="existing_poster" 
                            type="image" 
                            category="reviews" 
                            label="Video Poster Image (from Library / AI Captured)" 
                            help="Automatically generated by AI analysis, or choose a custom poster." />
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Or Upload Custom Poster (Optional WebP/JPG)</label>
                            <input type="file" name="poster_file" accept="image/*" class="w-full text-xs">
                        </div>
                    </div>

                    <!-- Placements Checkboxes -->
                    <div class="sm:col-span-2 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="block text-xs font-bold text-slate-800 mb-2">Display Placements Across Website:</span>
                        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-700">
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_home" value="1" x-model="addForm.placement_home" checked class="rounded text-primary focus:ring-primary">
                                <span>Homepage Reels Carousel</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_gallery" value="1" x-model="addForm.placement_gallery" checked class="rounded text-primary focus:ring-primary">
                                <span>Gallery Page (Videos Tab)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                <input type="checkbox" name="placement_tour" value="1" x-model="addForm.placement_tour" checked class="rounded text-primary focus:ring-primary">
                                <span>Tour Detail Page Reviews</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="addModalOpen = false" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Publish Video Review</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Video Player Preview Modal ──────────────────────────────────────── -->
    <div x-show="previewModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md"
         role="dialog" 
         aria-modal="true"
         @click="closePreview()">
        <div class="relative bg-slate-900 rounded-3xl max-w-2xl w-full p-4 overflow-hidden border border-white/10 shadow-2xl"
             @click.stop>
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-white/10 text-white">
                <h4 class="font-bold text-sm truncate" x-text="previewVideoTitle"></h4>
                <button type="button" @click="closePreview()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
            <div class="relative w-full aspect-16/9 bg-black rounded-2xl overflow-hidden flex items-center justify-center">
                <template x-if="previewVideoUrl">
                    <video :src="previewVideoUrl" controls autoplay class="w-full h-full object-contain"></video>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
