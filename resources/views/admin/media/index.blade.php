@extends('layouts.admin')

@section('page_title', 'Media Library (Images & Videos)')

@section('content')
<div x-data="mediaManager()" x-init="init()" class="space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition">Dashboard</a>
                <span>/</span>
                <span>Content &amp; Knowledge</span>
                <span>/</span>
                <span class="text-slate-800">Media Library</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-images text-rose-500"></i> Media Library &amp; Asset Manager
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Centralized management for all website images, photography, tour galleries, promotional banners, and customer video reels.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Sync Server Files Button -->
            <button type="button" 
                    @click="syncServerFiles()" 
                    :disabled="syncing"
                    class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 shadow-2xs transition disabled:opacity-50 cursor-pointer"
                    title="Scan public folders to index untracked media and refresh usage links">
                <i class="bi bi-arrow-repeat text-sm text-primary" :class="syncing ? 'animate-spin' : ''"></i>
                <span x-text="syncing ? 'Syncing...' : 'Scan & Sync Server Media'">Scan &amp; Sync Server Media</span>
            </button>

            <!-- Upload Media Button -->
            <button type="button" 
                    @click="openUploadModal()" 
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white bg-primary hover:bg-primary-dark shadow-md hover:shadow-lg transition cursor-pointer">
                <i class="bi bi-cloud-arrow-up-fill text-sm"></i>
                <span>Upload Media</span>
            </button>
        </div>
    </div>

    <!-- KPI Statistics Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Total Assets -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Total Media</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="bi bi-collection-play-fill text-sm"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">{{ number_format($kpis['total_assets']) }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-0.5">{{ $kpis['formatted_total_size'] }} storage</div>
        </div>

        <!-- Images & Photos -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Photos &amp; Images</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-image-fill text-sm"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">{{ number_format($kpis['images_count']) }}</div>
            <div class="text-[11px] font-medium text-emerald-600 mt-0.5">{{ $kpis['formatted_images_size'] }} • WebP/AVIF</div>
        </div>

        <!-- Videos & Reels -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Videos &amp; Reels</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-play-btn-fill text-sm"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">{{ number_format($kpis['videos_count']) }}</div>
            <div class="text-[11px] font-medium text-amber-600 mt-0.5">{{ $kpis['formatted_videos_size'] }} • MP4 Reels</div>
        </div>

        <!-- In-Use Assets -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>In-Use Links</span>
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="bi bi-link-45deg text-sm"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">{{ number_format($kpis['in_use_count']) }}</div>
            <div class="text-[11px] font-semibold text-sky-600 mt-0.5">{{ $kpis['in_use_pct'] }}% of library linked</div>
        </div>

        <!-- Unused / Orphans -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Unused Assets</span>
                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="bi bi-archive-fill text-sm"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">{{ number_format($kpis['unused_count']) }}</div>
            <div class="text-[11px] font-medium text-slate-400 mt-0.5">Ready for cleanup</div>
        </div>
    </div>

    <!-- Filter & Toolbar Card -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('admin.media.index') }}" id="mediaFilterForm" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <!-- Search input -->
            <div class="relative flex-1 min-w-[240px]">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search by filename, title, alt text, or path..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none">
                @if(request('search'))
                    <a href="{{ route('admin.media.index', array_merge(request()->except('search'), ['view' => $viewMode])) }}" 
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>

            <!-- Filters cluster -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Media Type Dropdown -->
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:border-slate-300 focus:ring-2 focus:ring-primary/20 outline-none transition cursor-pointer">
                    <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>All Media Types</option>
                    <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>🖼️ Images Only</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>🎥 Videos Only</option>
                </select>

                <!-- Category Dropdown -->
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:border-slate-300 focus:ring-2 focus:ring-primary/20 outline-none transition cursor-pointer">
                    @foreach($categories as $catKey => $catLabel)
                        <option value="{{ $catKey }}" {{ request('category', 'all') === $catKey ? 'selected' : '' }}>
                            {{ $catLabel }}
                        </option>
                    @endforeach
                </select>

                <!-- Usage Dropdown -->
                <select name="usage" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:border-slate-300 focus:ring-2 focus:ring-primary/20 outline-none transition cursor-pointer">
                    <option value="all" {{ request('usage', 'all') === 'all' ? 'selected' : '' }}>All Usages</option>
                    <option value="in_use" {{ request('usage') === 'in_use' ? 'selected' : '' }}>🟢 In-Use Only</option>
                    <option value="unused" {{ request('usage') === 'unused' ? 'selected' : '' }}>⚪ Unused Only</option>
                </select>

                <!-- Sort Dropdown -->
                <select name="sort" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:border-slate-300 focus:ring-2 focus:ring-primary/20 outline-none transition cursor-pointer">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="size_desc" {{ request('sort') === 'size_desc' ? 'selected' : '' }}>Size (Largest)</option>
                    <option value="size_asc" {{ request('sort') === 'size_asc' ? 'selected' : '' }}>Size (Smallest)</option>
                    <option value="used_desc" {{ request('sort') === 'used_desc' ? 'selected' : '' }}>Most Used</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Filename (A-Z)</option>
                </select>

                <!-- View Switcher (Grid vs List) -->
                <div class="flex items-center rounded-xl border border-slate-200 p-0.5 bg-slate-50">
                    <a href="{{ route('admin.media.index', array_merge(request()->query(), ['view' => 'grid'])) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'grid' ? 'bg-white text-primary shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-900' }}"
                       title="Grid View">
                        <i class="bi bi-grid-fill"></i>
                    </a>
                    <a href="{{ route('admin.media.index', array_merge(request()->query(), ['view' => 'list'])) }}" 
                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'list' ? 'bg-white text-primary shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-900' }}"
                       title="List View">
                        <i class="bi bi-list-ul"></i>
                    </a>
                </div>
            </div>
        </form>

        <!-- Bulk Actions Floating Bar (Shows when 1+ selected) -->
        <div x-show="selectedIds.length > 0" 
             x-transition 
             class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl bg-slate-900 text-white text-xs font-medium shadow-md">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-primary text-white font-bold" x-text="selectedIds.length"></span>
                <span>items selected</span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Move to Category -->
                <div class="flex items-center gap-1.5">
                    <select x-model="bulkCategory" class="px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none">
                        <option value="">Move to Category...</option>
                        <option value="tours">Tours &amp; Experiences</option>
                        <option value="blogs">Blog &amp; Articles</option>
                        <option value="reviews">Reviews &amp; Reels</option>
                        <option value="banners">Banners &amp; Promos</option>
                        <option value="site">Site Assets</option>
                        <option value="general">General</option>
                    </select>
                    <button type="button" @click="applyBulkCategory()" class="px-2.5 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-semibold transition">
                        Move
                    </button>
                </div>

                <!-- Refresh Usage -->
                <button type="button" @click="bulkRefreshUsage()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-semibold transition flex items-center gap-1.5">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Check Usages</span>
                </button>

                <!-- Bulk Delete -->
                <button type="button" @click="confirmBulkDelete()" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition flex items-center gap-1.5">
                    <i class="bi bi-trash-fill"></i>
                    <span>Delete</span>
                </button>

                <!-- Deselect All -->
                <button type="button" @click="selectedIds = []" class="p-1.5 rounded-lg text-slate-400 hover:text-white transition" title="Clear selection">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Media Library Content Area -->
    @if($assets->isEmpty())
        <!-- Empty State -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-12 text-center shadow-2xs">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-2xl shadow-xs">
                <i class="bi bi-images"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">No media assets found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">
                No files matched your search or filters. Try adjusting your query or scan existing server files into the library.
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('admin.media.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Clear Filters
                </a>
                <button type="button" @click="syncServerFiles()" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-md hover:bg-primary-dark transition">
                    <i class="bi bi-arrow-repeat me-1"></i> Scan Server Media
                </button>
            </div>
        </div>
    @elseif($viewMode === 'grid')
        <!-- GRID VIEW -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
            @foreach($assets as $asset)
                <div class="group relative rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-2xs hover:shadow-md hover:border-primary/50 transition-all flex flex-col cursor-pointer"
                     x-data="{ isPlaying: false, isPaused: false, isMuted: true }"
                     @click="openDetails({{ $asset->id }})">
                    
                    <!-- Thumbnail Container with 1:1 Aspect Ratio -->
                    <div class="relative w-full aspect-square bg-slate-900/5 overflow-hidden flex items-center justify-center">
                        @if($asset->is_image)
                            <!-- IMAGE THUMBNAIL -->
                            <img src="{{ $asset->clean_public_url }}" 
                                 alt="{{ $asset->alt_text ?: $asset->filename }}" 
                                 loading="lazy" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f8fafc\'><rect width=\'100\' height=\'100\' fill=\'%23f1f5f9\'/><path d=\'M30 40a10 10 0 1 0 0-20 10 10 0 0 0 0 20zm-15 45h70L65 55 50 70 40 60 15 85z\' fill=\'%23cbd5e1\'/></svg>'; this.onerror=null;">
                        @elseif($asset->is_video)
                            <!-- VIDEO PLAYER & PREVIEW THUMBNAIL (Browser renders first frame automatically via preload="metadata") -->
                            <video x-ref="cardVideo_{{ $asset->id }}"
                                   src="{{ $asset->clean_public_url }}#t=0.001" 
                                   @if(!empty($asset->poster_url)) poster="{{ $asset->poster_url }}" @endif
                                   :muted="isMuted"
                                   playsinline 
                                   preload="metadata"
                                   @play="isPlaying = true; isPaused = false"
                                   @pause="isPaused = true"
                                   @playing="isPlaying = true; isPaused = false"
                                   @ended="isPlaying = false; isPaused = false"
                                   class="w-full h-full object-cover bg-black"
                                   @click.stop="const v = $refs.cardVideo_{{ $asset->id }}; if(v){ if(v.paused){ v.play().then(() => { isPlaying = true; isPaused = false; }).catch(() => { isPaused = true; }); } else { v.pause(); isPaused = true; } }">
                            </video>

                            <!-- Centered Interactive Play/Pause Button Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none transition"
                                 :class="isPlaying && !isPaused ? 'opacity-0 group-hover:opacity-100 bg-slate-950/20' : 'bg-slate-950/30'">
                                <button type="button" 
                                        @click.stop="const v = $refs.cardVideo_{{ $asset->id }}; if(v){ if(v.paused || !isPlaying){ isPlaying = true; isPaused = false; v.play().catch(() => { isPaused = true; }); } else { v.pause(); isPaused = true; } }"
                                        class="pointer-events-auto w-12 h-12 rounded-full bg-primary/95 hover:bg-primary text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition cursor-pointer"
                                        :title="isPlaying && !isPaused ? 'Pause Video' : 'Play Video'">
                                    <i class="bi text-2xl" :class="isPlaying && !isPaused ? 'bi-pause-fill' : 'bi-play-fill translate-x-0.5'"></i>
                                </button>
                            </div>

                            <!-- Floating Inline Controls Bar (When Video Is Playing Inside Card) -->
                            <div x-show="isPlaying" 
                                 @click.stop
                                 class="absolute bottom-1.5 left-1.5 right-1.5 flex items-center justify-between p-1 rounded-lg bg-slate-950/85 backdrop-blur-xs text-white text-[11px] z-20 shadow-md">
                                <div class="flex items-center gap-1">
                                    <button type="button" 
                                            @click.stop="const v = $refs.cardVideo_{{ $asset->id }}; if(v){ if(v.paused){ v.play().then(() => { isPlaying = true; isPaused = false; }).catch(() => { isPaused = true; }); } else { v.pause(); isPaused = true; } }" 
                                            class="p-1 hover:text-primary transition"
                                            :title="isPaused ? 'Resume Playback' : 'Pause Playback'">
                                        <i class="bi" :class="isPaused ? 'bi-play-fill text-sm text-amber-400' : 'bi-pause-fill text-sm text-emerald-400'"></i>
                                    </button>
                                    <button type="button" 
                                            @click.stop="isMuted = !isMuted; if($refs.cardVideo_{{ $asset->id }}){ $refs.cardVideo_{{ $asset->id }}.muted = isMuted; }" 
                                            class="p-1 hover:text-primary transition"
                                            :title="isMuted ? 'Unmute Audio' : 'Mute Audio'">
                                        <i class="bi" :class="isMuted ? 'bi-volume-mute-fill text-xs text-slate-300' : 'bi-volume-up-fill text-xs text-emerald-400'"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" 
                                            @click.stop="openVideoModal('{{ $asset->clean_public_url }}', '{{ $asset->poster_url }}', '{{ addslashes($asset->filename) }}')" 
                                            class="p-1 hover:text-primary transition"
                                            title="Expand Cinema Player">
                                        <i class="bi bi-arrows-fullscreen text-xs"></i>
                                    </button>
                                    <button type="button" 
                                            @click.stop="isPlaying = false; isPaused = false; const v = $refs.cardVideo_{{ $asset->id }}; if(v){ v.pause(); v.currentTime = 0; }" 
                                            class="p-1 hover:text-rose-400 transition"
                                            title="Close Video">
                                        <i class="bi bi-x-circle-fill text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-3xl text-slate-400">
                                <i class="bi bi-file-earmark"></i>
                            </div>
                        @endif

                        <!-- Top Overlay Controls -->
                        <div class="absolute top-2 left-2 right-2 flex items-center justify-between pointer-events-none z-10" @click.stop>
                            <!-- Multi-select checkbox -->
                            <input type="checkbox" 
                                   value="{{ $asset->id }}" 
                                   x-model="selectedIds" 
                                   class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary pointer-events-auto shadow-2xs cursor-pointer">

                            <div class="flex items-center gap-1">
                                @if($asset->is_video)
                                    <button type="button" 
                                            @click="openVideoModal('{{ $asset->clean_public_url }}', '{{ $asset->poster_url }}', '{{ addslashes($asset->filename) }}')"
                                            class="pointer-events-auto p-1 rounded-md bg-slate-950/70 hover:bg-primary text-white backdrop-blur-xs transition"
                                            title="Open full cinema video player">
                                        <i class="bi bi-arrows-fullscreen text-[10px]"></i>
                                    </button>
                                @endif
                                <!-- Extension Badge -->
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase {{ $asset->is_video ? 'bg-amber-600 text-white' : 'bg-slate-950/70 text-white' }} backdrop-blur-xs"
                                      @if(!empty($asset->format_variants)) title="Available formats: {{ strtoupper($asset->extension) }}, {{ strtoupper(implode(', ', array_keys($asset->format_variants))) }}" @endif>
                                    {{ $asset->extension }}@if(!empty($asset->format_variants))<span class="text-amber-300 font-normal ms-0.5">+{{ count($asset->format_variants) }}</span>@endif
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Overlay Badges (Hidden when playing inline) -->
                        <div x-show="!isPlaying" class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-[10px] font-bold pointer-events-none z-10">
                            <!-- Usage Badge -->
                            @if($asset->usage_count > 0)
                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-600/90 text-white shadow-2xs backdrop-blur-xs">
                                    {{ $asset->usage_count }} in use
                                </span>
                            @else
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-900/60 text-slate-300 shadow-2xs backdrop-blur-xs">
                                    Unused
                                </span>
                            @endif

                            <!-- File Size Badge -->
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-950/70 text-white shadow-2xs backdrop-blur-xs">
                                {{ $asset->formatted_file_size }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-2.5 flex-1 flex flex-col justify-between gap-1.5">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 truncate" title="{{ $asset->filename }}">
                                {{ $asset->filename }}
                            </h4>
                            <p class="text-[11px] text-slate-400 truncate mt-0.5" title="{{ $asset->title ?: $asset->category }}">
                                {{ $asset->title ?: ucfirst($asset->category) }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 text-[11px]" @click.stop>
                            <span class="text-slate-400 capitalize">{{ $asset->category }}</span>
                            <div class="flex items-center gap-1">
                                @if($asset->is_video)
                                    <button type="button" 
                                            @click="openVideoModal('{{ $asset->clean_public_url }}', '{{ $asset->poster_url }}', '{{ addslashes($asset->filename) }}')" 
                                            class="p-1 rounded-md text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition cursor-pointer"
                                            title="Watch Fullscreen">
                                        <i class="bi bi-play-circle-fill"></i>
                                    </button>
                                @endif
                                <button type="button" 
                                        @click="copyUrl('{{ $asset->clean_public_url }}')" 
                                        class="p-1 rounded-md text-slate-400 hover:text-primary hover:bg-slate-50 transition cursor-pointer"
                                        title="Copy public URL">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- LIST / TABLE VIEW -->
        <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 w-10 text-center">
                                <input type="checkbox" 
                                       @change="toggleSelectAll($event)" 
                                       class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                            </th>
                            <th class="py-3 px-3 w-16">Preview</th>
                            <th class="py-3 px-3">Filename &amp; Title</th>
                            <th class="py-3 px-3">Type &amp; Ext</th>
                            <th class="py-3 px-3">Dimensions</th>
                            <th class="py-3 px-3">Size</th>
                            <th class="py-3 px-3">Category</th>
                            <th class="py-3 px-3">Usages</th>
                            <th class="py-3 px-3">Date</th>
                            <th class="py-3 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($assets as $asset)
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-2.5 px-3.5 text-center">
                                    <input type="checkbox" 
                                           value="{{ $asset->id }}" 
                                           x-model="selectedIds" 
                                           class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center shrink-0 cursor-pointer relative"
                                         @click="openDetails({{ $asset->id }})">
                                        @if($asset->is_image)
                                            <img src="{{ $asset->clean_public_url }}" 
                                                 alt="{{ $asset->filename }}" 
                                                 class="w-full h-full object-cover"
                                                 onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f8fafc\'><rect width=\'100\' height=\'100\' fill=\'%23f1f5f9\'/><path d=\'M30 40a10 10 0 1 0 0-20 10 10 0 0 0 0 20zm-15 45h70L65 55 50 70 40 60 15 85z\' fill=\'%23cbd5e1\'/></svg>'; this.onerror=null;">
                                        @elseif($asset->is_video)
                                            <video src="{{ $asset->clean_public_url }}#t=0.001" 
                                                   @if(!empty($asset->poster_url)) poster="{{ $asset->poster_url }}" @endif
                                                   preload="metadata" 
                                                   class="w-full h-full object-cover bg-black">
                                            </video>
                                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center text-white">
                                                <i class="bi bi-play-fill text-xs"></i>
                                            </div>
                                        @else
                                            <i class="bi bi-file-earmark text-slate-400 text-base"></i>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 min-w-[200px]">
                                    <div class="font-bold text-slate-900 hover:text-primary transition cursor-pointer" @click="openDetails({{ $asset->id }})">
                                        {{ $asset->filename }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">
                                        {{ $asset->title ?: '—' }}
                                    </div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $asset->is_video ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700' }}"
                                          @if(!empty($asset->format_variants)) title="Available fallback formats: {{ strtoupper(implode(', ', array_keys($asset->format_variants))) }}" @endif>
                                        {{ $asset->extension }}@if(!empty($asset->format_variants))<span class="text-slate-500 font-normal ms-0.5">+{{ count($asset->format_variants) }}</span>@endif
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">
                                    {{ $asset->dimensions ?: '—' }}
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">
                                    {{ $asset->formatted_file_size }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 capitalize">
                                        {{ $asset->category }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3">
                                    @if($asset->usage_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $asset->usage_count }} links
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-500">
                                            Unused
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $asset->created_at ? $asset->created_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($asset->is_video)
                                            <button type="button" 
                                                    @click="openVideoModal('{{ $asset->clean_public_url }}', '{{ $asset->poster_url }}', '{{ addslashes($asset->filename) }}')" 
                                                    class="p-1.5 rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition"
                                                    title="Watch video">
                                                <i class="bi bi-play-circle-fill"></i>
                                            </button>
                                        @endif
                                        <button type="button" 
                                                @click="copyUrl('{{ $asset->clean_public_url }}')" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-slate-100 transition"
                                                title="Copy public URL">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <button type="button" 
                                                @click="openDetails({{ $asset->id }})" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition"
                                                title="Inspect asset">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" 
                                                @click="confirmDeleteAsset({{ $asset->id }}, '{{ addslashes($asset->filename) }}', {{ $asset->usage_count }})" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Delete asset">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Pagination -->
    @if($assets->hasPages())
        <div class="pt-2">
            {{ $assets->links() }}
        </div>
    @endif

    <!-- SLIDE-OVER ASSET DETAIL & INSPECTOR DRAWER -->
    <div x-show="drawerOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-hidden bg-slate-950/60 backdrop-blur-xs flex justify-end"
         style="display: none;"
         @keydown.escape.window="drawerOpen = false">
        
        <div x-show="drawerOpen" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-full max-w-xl bg-white h-full shadow-2xl flex flex-col overflow-hidden"
             @click.outside="drawerOpen = false">
            
            <!-- Drawer Header -->
            <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="bi" :class="activeAsset?.media_type === 'video' ? 'bi-play-btn-fill' : 'bi-image-fill'"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 truncate" x-text="activeAsset?.filename || 'Asset Inspector'"></h3>
                        <p class="text-[11px] text-slate-400 font-mono" x-text="activeAsset?.dimensions ? activeAsset.dimensions + ' • ' + activeAsset.formatted_size : activeAsset?.formatted_size"></p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <template x-if="activeAsset?.media_type === 'video'">
                        <button type="button" 
                                @click="openVideoModal(activeAsset?.public_url, activeAsset?.poster_url, activeAsset?.filename)" 
                                class="p-2 rounded-xl text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition" 
                                title="Watch Cinema Player">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </template>
                    <button type="button" 
                            @click="copyUrl(activeAsset?.public_url)" 
                            class="p-2 rounded-xl text-slate-500 hover:text-primary hover:bg-slate-100 transition" 
                            title="Copy Public URL">
                        <i class="bi bi-clipboard"></i>
                    </button>
                    <a :href="activeAsset?.full_url" 
                       download 
                       target="_blank" 
                       class="p-2 rounded-xl text-slate-500 hover:text-primary hover:bg-slate-100 transition" 
                       title="Download file">
                        <i class="bi bi-download"></i>
                    </a>
                    <button type="button" 
                            @click="drawerOpen = false" 
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Drawer Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                
                <!-- Media Preview Canvas -->
                <div class="rounded-2xl border border-slate-200/90 bg-slate-950 overflow-hidden flex items-center justify-center p-2 min-h-[220px] max-h-[360px] relative">
                    <template x-if="activeAsset?.media_type === 'image'">
                        <img :src="activeAsset?.public_url" 
                             :alt="activeAsset?.alt_text" 
                             class="max-w-full max-h-[340px] rounded-xl object-contain shadow-sm"
                             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f8fafc\'><rect width=\'100\' height=\'100\' fill=\'%23f1f5f9\'/><path d=\'M30 40a10 10 0 1 0 0-20 10 10 0 0 0 0 20zm-15 45h70L65 55 50 70 40 60 15 85z\' fill=\'%23cbd5e1\'/></svg>'; this.onerror=null;">
                    </template>
                    <template x-if="activeAsset?.media_type === 'video'">
                        <div class="w-full flex flex-col items-center">
                            <video :src="activeAsset?.public_url" 
                                   :poster="activeAsset?.poster_url"
                                   controls 
                                   playsinline 
                                   class="max-w-full max-h-[340px] rounded-xl shadow-lg bg-black">
                            </video>
                        </div>
                    </template>
                </div>

                <!-- URL Bar with 1-Click Copy -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Public URL</label>
                    <div class="flex items-center gap-2">
                        <input type="text" 
                               readonly 
                               :value="activeAsset?.public_url" 
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-700 outline-none">
                        <button type="button" 
                                @click="copyUrl(activeAsset?.public_url)" 
                                class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shrink-0 transition flex items-center gap-1.5 cursor-pointer">
                            <i class="bi bi-clipboard"></i>
                            <span>Copy</span>
                        </button>
                    </div>
                </div>

                <!-- Specs Grid -->
                <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200/70">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Format / MIME</span>
                        <span class="font-bold text-slate-800" x-text="activeAsset?.mime_type || activeAsset?.extension"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">File Size</span>
                        <span class="font-bold text-slate-800" x-text="activeAsset?.formatted_size"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Resolution</span>
                        <span class="font-bold text-slate-800" x-text="activeAsset?.dimensions || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Uploaded At</span>
                        <span class="font-bold text-slate-800" x-text="activeAsset?.created_at"></span>
                    </div>

                    <!-- Available Format Variants -->
                    <template x-if="activeAsset?.format_variants && Object.keys(activeAsset.format_variants).length > 0">
                        <div class="col-span-2 pt-2 border-t border-slate-200/60">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase mb-1">Available Format Variants</span>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800" x-text="(activeAsset.extension || '').toUpperCase() + ' (Primary)'"></span>
                                <template x-for="(vInfo, ext) in activeAsset.format_variants" :key="ext">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700" x-text="ext.toUpperCase() + ' (Fallback)'"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Editable Metadata Form -->
                <div class="space-y-3 pt-2 border-t border-slate-200/80">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-pencil-square text-primary"></i> Metadata &amp; Image SEO
                    </h4>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Title</label>
                        <input type="text" 
                               x-model="editMeta.title" 
                               placeholder="e.g. Desert Safari Dune Bashing Sunset" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Alt Text (Crucial for Google Image SEO)</label>
                            <span class="text-[10px] text-emerald-600 font-bold" x-show="editMeta.alt_text?.length > 10">SEO Optimized</span>
                        </div>
                        <input type="text" 
                               x-model="editMeta.alt_text" 
                               placeholder="Descriptive alt text for Google rankings & accessibility..." 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                        <select x-model="editMeta.category" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="tours">Tours &amp; Experiences</option>
                            <option value="blogs">Blog &amp; Articles</option>
                            <option value="reviews">Reviews &amp; Reels</option>
                            <option value="banners">Hero &amp; Promo Banners</option>
                            <option value="site">Site Assets (Logo, OG)</option>
                            <option value="general">General</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Caption</label>
                        <textarea x-model="editMeta.caption" 
                                  rows="2" 
                                  placeholder="Optional caption..." 
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></textarea>
                    </div>

                    <button type="button" 
                            @click="saveMetadata()" 
                            :disabled="savingMeta"
                            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <i class="bi" :class="savingMeta ? 'bi-arrow-repeat animate-spin' : 'bi-check2-circle'"></i>
                        <span x-text="savingMeta ? 'Saving...' : 'Save Metadata'">Save Metadata</span>
                    </button>
                </div>

                <!-- Where is this file used? (Active Links Tracker) -->
                <div class="space-y-2 pt-2 border-t border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-diagram-3-fill text-sky-500"></i> Active Website Usages
                        </h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                              :class="activeAsset?.usage_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'" 
                              x-text="(activeAsset?.usage_count || 0) + ' references'">
                        </span>
                    </div>

                    <template x-if="activeAsset?.usage_locations && activeAsset.usage_locations.length > 0">
                        <div class="space-y-1.5">
                            <template x-for="(loc, idx) in activeAsset.usage_locations" :key="idx">
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-sky-100 text-sky-800" x-text="loc.type"></span>
                                            <strong class="text-slate-800 font-semibold" x-text="loc.name"></strong>
                                        </div>
                                        <span class="text-[11px] text-slate-500" x-text="'Field: ' + loc.field"></span>
                                    </div>
                                    <template x-if="loc.edit_url">
                                        <a :href="loc.edit_url" target="_blank" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-primary hover:border-primary text-[11px] font-bold transition flex items-center gap-1">
                                            <span>Edit</span>
                                            <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!activeAsset?.usage_locations || activeAsset.usage_locations.length === 0">
                        <div class="p-3 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-500">
                            <i class="bi bi-info-circle text-sm text-slate-400 block mb-1"></i>
                            This file is currently not referenced in any tours, blog posts, or site settings. Safe to delete or keep as archive.
                        </div>
                    </template>
                </div>

                <!-- Replace File & Danger Zone -->
                <div class="pt-2 border-t border-slate-200/80 space-y-2.5">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-shield-exclamation text-amber-500"></i> Asset Operations
                    </h4>

                    <!-- Replace File preserving URL -->
                    <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/80">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <strong class="text-xs font-bold text-amber-900 block">Replace File (Preserve URL)</strong>
                                <p class="text-[11px] text-amber-700 mt-0.5">
                                    Upload a new file to overwrite this asset on disk. All existing tours &amp; blogs will immediately reflect the new image without breaking links.
                                </p>
                            </div>
                            <label class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs cursor-pointer shrink-0 transition flex items-center gap-1">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Replace</span>
                                <input type="file" @change="replaceAssetFile($event)" class="hidden">
                            </label>
                        </div>
                    </div>

                    <!-- Delete Asset Button -->
                    <button type="button" 
                            @click="confirmDeleteAsset(activeAsset.id, activeAsset.filename, activeAsset.usage_count)" 
                            class="w-full py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="bi bi-trash-fill"></i>
                        <span>Delete Asset from Library</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- DEDICATED CINEMA VIDEO PLAYER MODAL -->
    <div x-show="videoModalOpen" 
         x-transition 
         class="fixed inset-0 z-50 overflow-hidden bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6" 
         style="display: none;"
         @keydown.escape.window="closeVideoModal()">
        
        <div class="w-full max-w-4xl bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800 flex flex-col" 
             @click.outside="closeVideoModal()">
            
            <!-- Video Modal Header -->
            <div class="px-5 py-3.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between text-white">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <h3 class="text-sm font-bold truncate text-slate-100" x-text="videoModalData.title"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="copyUrl(videoModalData.url)" 
                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition flex items-center gap-1">
                        <i class="bi bi-clipboard"></i>
                        <span>Copy URL</span>
                    </button>
                    <button type="button" 
                            @click="closeVideoModal()" 
                            class="p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        <i class="bi bi-x-lg text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Video Player Screen -->
            <div class="relative w-full aspect-video sm:aspect-[16/9] max-h-[72vh] bg-black flex items-center justify-center">
                <video x-ref="cinemaVideo"
                       :src="videoModalData.url" 
                       :poster="videoModalData.poster"
                       controls 
                       autoplay 
                       playsinline 
                       class="w-full h-full object-contain">
                </video>
            </div>
        </div>
    </div>

    <!-- DRAG-AND-DROP UPLOAD MODAL -->
    <div x-show="uploadModalOpen" 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;"
         @keydown.escape.window="uploadModalOpen = false">
        
        <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl space-y-4" @click.outside="uploadModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Upload Media Assets</h3>
                        <p class="text-xs text-slate-500">Supports AVIF, WebP, JPG, PNG, SVG images and MP4 reels</p>
                    </div>
                </div>
                <button type="button" @click="uploadModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Target Category -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Category</label>
                <select x-model="uploadCategory" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none">
                    <option value="general">General Uploads</option>
                    <option value="tours">Tours &amp; Experiences</option>
                    <option value="blogs">Blog &amp; Articles</option>
                    <option value="reviews">Reviews &amp; Reels</option>
                    <option value="banners">Hero &amp; Promo Banners</option>
                    <option value="site">Site Assets (Logo, Favicon)</option>
                </select>
            </div>

            <!-- Dropzone Area -->
            <div @dragover.prevent="dragOver = true"
                 @dragleave.prevent="dragOver = false"
                 @drop.prevent="handleDrop($event)"
                 :class="dragOver ? 'border-primary bg-primary/5' : 'border-slate-300 bg-slate-50'"
                 class="rounded-2xl border-2 border-dashed p-8 text-center transition flex flex-col items-center justify-center cursor-pointer relative"
                 @click="$refs.fileInput.click()">
                
                <input type="file" 
                       x-ref="fileInput" 
                       multiple 
                       @change="handleFilesSelected($event)" 
                       class="hidden" 
                       accept="image/*,video/mp4,video/webm,video/quicktime">

                <div class="w-12 h-12 rounded-2xl bg-white text-primary flex items-center justify-center text-2xl shadow-xs mb-3">
                    <i class="bi bi-cloud-arrow-up"></i>
                </div>
                <p class="text-xs font-bold text-slate-800">Drag and drop files here, or <span class="text-primary underline">browse</span></p>
                <p class="text-[11px] text-slate-400 mt-1">Images up to 10MB • Videos up to 50MB</p>
            </div>

            <!-- Queued Files List -->
            <template x-if="queuedFiles.length > 0">
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                        <span x-text="queuedFiles.length + ' files ready'"></span>
                        <button type="button" @click="queuedFiles = []" class="text-rose-500 hover:underline">Clear all</button>
                    </div>

                    <template x-for="(f, i) in queuedFiles" :key="i">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                            <div class="flex items-center gap-2 truncate min-w-0">
                                <i class="bi" :class="f.type.startsWith('video/') ? 'bi-file-earmark-play text-amber-500' : 'bi-file-earmark-image text-emerald-500'"></i>
                                <span class="truncate font-semibold text-slate-800" x-text="f.name"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="formatBytes(f.size)"></span>
                            </div>
                            <button type="button" @click="removeQueuedFile(i)" class="text-slate-400 hover:text-rose-500 p-1">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Modern Format Optimization Bar (When non-WebP/AVIF images are queued) -->
            <template x-if="hasConvertibleImages()">
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent border border-emerald-500/20 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 font-bold text-emerald-800">
                            <i class="bi bi-magic text-emerald-600 text-sm"></i>
                            <span>Next-Gen Image Standards Optimization</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">WebP / AVIF Available</span>
                    </div>
                    <p class="text-[11px] text-slate-600">
                        Convert to modern format on upload to ensure high site performance and Core Web Vitals standards.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer font-bold transition text-[11px]"
                               :class="selectedConvertFormat === 'webp' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="webp" x-model="selectedConvertFormat" class="hidden">
                            <i class="bi bi-check2"></i>
                            <span>WebP (Recommended)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer font-bold transition text-[11px]"
                               :class="selectedConvertFormat === 'avif' ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="avif" x-model="selectedConvertFormat" class="hidden">
                            <i class="bi bi-check2"></i>
                            <span>AVIF (Next-Gen)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer font-bold transition text-[11px]"
                               :class="selectedConvertFormat === 'original' ? 'bg-slate-700 text-white border-slate-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="original" x-model="selectedConvertFormat" class="hidden">
                            <span>Keep Original</span>
                        </label>
                        
                        <select x-show="selectedConvertFormat !== 'original'" 
                                x-model="selectedConvertQuality" 
                                class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-700 bg-white outline-none ml-auto">
                            <option value="lossless">Lossless (100% Quality)</option>
                            <option value="high">High-Fidelity (92% Quality)</option>
                        </select>
                    </div>
                </div>
            </template>

            <!-- Upload Progress Bar -->
            <template x-if="uploading">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                        <span>Uploading files...</span>
                        <span x-text="uploadProgress + '%'"></span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-primary transition-all duration-200" :style="'width: ' + uploadProgress + '%'"></div>
                    </div>
                </div>
            </template>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="uploadModalOpen = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" 
                        @click="startUploadWithCheck()" 
                        :disabled="queuedFiles.length === 0 || uploading"
                        class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-md disabled:opacity-50 flex items-center gap-1.5 cursor-pointer">
                    <i class="bi" :class="uploading ? 'bi-arrow-repeat animate-spin' : 'bi-cloud-arrow-up-fill'"></i>
                    <span x-text="uploading ? 'Uploading...' : 'Start Upload'">Start Upload</span>
                </button>
            </div>
        </div>
    </div>

    <!-- DUPLICATE MEDIA RESOLUTION MODAL -->
    <div x-show="duplicateModalOpen" 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;"
         @keydown.escape.window="duplicateModalOpen = false">
        
        <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-4 border border-amber-200" @click.outside="duplicateModalOpen = false">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-600 flex items-center justify-center text-xl shrink-0 border border-amber-500/20">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Duplicate Media Detected</h3>
                    <p class="text-xs text-slate-500" x-text="duplicateMessage || 'This exact file already exists in your Media Library.'"></p>
                </div>
            </div>

            <template x-if="duplicateData">
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-slate-200 overflow-hidden shrink-0 border border-slate-300 flex items-center justify-center">
                        <template x-if="duplicateData.media_type === 'video'">
                            <div class="w-full h-full bg-slate-900 flex items-center justify-center text-amber-400">
                                <i class="bi bi-play-circle-fill text-2xl"></i>
                            </div>
                        </template>
                        <template x-if="duplicateData.media_type !== 'video'">
                            <img :src="duplicateData.url" class="w-full h-full object-cover" onerror="this.src='/images/media-placeholder.svg';">
                        </template>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-800 truncate" x-text="duplicateData.filename"></div>
                        <div class="text-[11px] text-slate-500 font-mono truncate" x-text="duplicateData.public_url"></div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700 uppercase" x-text="duplicateData.category"></span>
                            <span class="text-[10px] text-slate-500" x-text="duplicateData.formatted_size"></span>
                            <span class="text-[10px] font-bold text-primary" x-text="'Used in: ' + (duplicateData.usage_count || 0) + ' place(s)'"></span>
                        </div>
                    </div>
                </div>
            </template>

            <p class="text-xs text-slate-600">
                Uploading duplicate copies creates redundant files and clutters your library. We strongly recommend linking to the existing media file.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="useExistingDuplicate()" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-md transition cursor-pointer">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Use Existing Media (Recommended)</span>
                </button>
                <button type="button" 
                        @click="duplicateModalOpen = false; submitUpload(true)" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    Upload Duplicate Anyway
                </button>
            </div>
        </div>
    </div>

    <!-- IMAGE OPTIMIZATION & NEXT-GEN FORMAT CONVERSION MODAL -->
    <div x-show="optimizationModalOpen" 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;"
         @keydown.escape.window="optimizationModalOpen = false">
        
        <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-4 border border-emerald-500/20" @click.outside="optimizationModalOpen = false">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center text-xl shrink-0 border border-emerald-500/20">
                    <i class="bi bi-magic"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Modern Image Standards Optimization</h3>
                    <p class="text-xs text-slate-500">We detected images that can be optimized to next-gen WebP or AVIF.</p>
                </div>
            </div>

            <!-- List of convertible images -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 max-h-40 overflow-y-auto">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pending Image(s) For Optimization</span>
                <template x-for="(file, idx) in getConvertibleFiles()" :key="idx">
                    <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-white border border-slate-100">
                        <div class="flex items-center gap-2 truncate min-w-0">
                            <i class="bi bi-file-earmark-image text-emerald-500"></i>
                            <span class="font-bold text-slate-800 truncate" x-text="file.name"></span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono shrink-0" x-text="formatBytes(file.size)"></span>
                    </div>
                </template>
            </div>

            <!-- Format & Quality Choices -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-800">Select Target Next-Gen Format:</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- WebP Option -->
                    <button type="button" 
                            @click="selectedConvertFormat = 'webp'"
                            class="p-3.5 rounded-2xl border text-left transition relative cursor-pointer hover:border-emerald-500 hover:bg-emerald-50/50"
                            :class="selectedConvertFormat === 'webp' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                WebP
                            </span>
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">Recommended</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">99.8% universal compatibility, lossless transparency, 60–75% file reduction.</p>
                    </button>

                    <!-- AVIF Option -->
                    <button type="button" 
                            @click="selectedConvertFormat = 'avif'"
                            class="p-3.5 rounded-2xl border text-left transition relative cursor-pointer hover:border-purple-500 hover:bg-purple-50/50"
                            :class="selectedConvertFormat === 'avif' ? 'border-purple-500 bg-purple-50/40 ring-2 ring-purple-500/20' : 'border-slate-200 bg-white'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-lightning-charge-fill text-purple-500 text-sm"></i>
                                AVIF
                            </span>
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-purple-100 text-purple-800">Next-Gen</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">Highest modern compression (up to 80% smaller), HDR color depth, high fidelity.</p>
                    </button>
                </div>

                <!-- Quality Preset Selector -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <span class="font-bold text-slate-700">Conversion Quality:</span>
                    <select x-model="selectedConvertQuality" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 bg-white outline-none">
                        <option value="lossless">Lossless (100% Quality - Pixel Perfect)</option>
                        <option value="high">High-Fidelity (92% Quality - Smallest Footprint)</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="skipConversionAndUpload()" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer text-center">
                    Upload Original As-Is
                </button>
                
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" 
                            @click="optimizationModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="applyConversionAndUpload(selectedConvertFormat || 'webp', selectedConvertQuality)" 
                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition cursor-pointer">
                        <i class="bi bi-magic"></i>
                        <span x-text="'Convert to ' + (selectedConvertFormat ? selectedConvertFormat.toUpperCase() : 'WEBP') + ' & Upload'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SAFE DELETE CONFIRMATION MODAL -->
    <div x-show="deleteModalOpen" 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;"
         @keydown.escape.window="deleteModalOpen = false">
        
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4" @click.outside="deleteModalOpen = false">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Confirm Asset Deletion</h3>
                    <p class="text-xs text-slate-500" x-text="deleteTarget.filename"></p>
                </div>
            </div>

            <!-- In-Use Danger Alert -->
            <template x-if="deleteTarget.usageCount > 0">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-2">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <i class="bi bi-shield-x"></i>
                        <span>Warning: Asset is actively linked!</span>
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        This file is actively referenced across <strong x-text="deleteTarget.usageCount"></strong> location(s) (tours, blogs, or site settings). Deleting it will result in broken images or 404s on the live website.
                    </p>
                    <label class="flex items-center gap-2 pt-1 font-bold text-rose-900 cursor-pointer">
                        <input type="checkbox" x-model="deleteTarget.force" class="w-4 h-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                        <span>Yes, force delete anyway</span>
                    </label>
                </div>
            </template>

            <template x-if="deleteTarget.usageCount === 0">
                <p class="text-xs text-slate-600">
                    Are you sure you want to delete this file? This file will be removed from server disk and marked deleted in the library.
                </p>
            </template>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" 
                        @click="executeDelete()" 
                        :disabled="deleteTarget.usageCount > 0 && !deleteTarget.force"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-md disabled:opacity-40 flex items-center gap-1.5 cursor-pointer">
                    <i class="bi bi-trash-fill"></i>
                    <span>Confirm Delete</span>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Component Script -->
<script>
function mediaManager() {
    return {
        selectedIds: [],
        bulkCategory: '',
        syncing: false,
        drawerOpen: false,
        activeAsset: null,
        editMeta: { title: '', alt_text: '', caption: '', category: 'general' },
        savingMeta: false,
        uploadModalOpen: false,
        dragOver: false,
        queuedFiles: [],
        uploadCategory: 'general',
        uploading: false,
        uploadProgress: 0,
        optimizationModalOpen: false,
        selectedConvertFormat: 'webp',
        selectedConvertQuality: 'lossless',
        autoConvertPromptShown: false,
        duplicateModalOpen: false,
        duplicateData: null,
        duplicateMessage: '',
        deleteModalOpen: false,
        deleteTarget: { id: null, filename: '', usageCount: 0, force: false },
        videoModalOpen: false,
        videoModalData: { url: '', poster: '', title: '' },

        init() {
            // Hotkeys or initializations if needed
        },

        openVideoModal(url, poster, title) {
            this.videoModalData = { url, poster, title };
            this.videoModalOpen = true;
            this.$nextTick(() => {
                if (this.$refs.cinemaVideo) {
                    this.$refs.cinemaVideo.currentTime = 0;
                    this.$refs.cinemaVideo.play();
                }
            });
        },

        closeVideoModal() {
            if (this.$refs.cinemaVideo) {
                this.$refs.cinemaVideo.pause();
            }
            this.videoModalOpen = false;
        },

        copyUrl(url) {
            if (!url) return;
            const full = url.startsWith('http') ? url : (window.location.origin + url);
            navigator.clipboard.writeText(full).then(() => {
                alert('Copied to clipboard:\n' + full);
            }).catch(() => {
                prompt('Copy URL:', full);
            });
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            } else {
                this.selectedIds = [];
            }
        },

        async syncServerFiles() {
            this.syncing = true;
            try {
                const res = await fetch('{{ route('admin.media.sync-existing') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Sync error: ' + (data.message || 'Unknown error'));
                }
            } catch (err) {
                alert('Failed to execute sync: ' + err.message);
            } finally {
                this.syncing = false;
            }
        },

        async openDetails(id) {
            try {
                const res = await fetch(`/admin/media/${id}/details`);
                const data = await res.json();
                if (data.success) {
                    this.activeAsset = data.asset;
                    this.editMeta = {
                        title: data.asset.title || '',
                        alt_text: data.asset.alt_text || '',
                        caption: data.asset.caption || '',
                        category: data.asset.category || 'general'
                    };
                    this.drawerOpen = true;
                }
            } catch (err) {
                alert('Could not load asset details: ' + err.message);
            }
        },

        async saveMetadata() {
            if (!this.activeAsset) return;
            this.savingMeta = true;
            try {
                const res = await fetch(`/admin/media/${this.activeAsset.id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editMeta)
                });
                const data = await res.json();
                if (data.success) {
                    this.activeAsset.title = this.editMeta.title;
                    this.activeAsset.alt_text = this.editMeta.alt_text;
                    this.activeAsset.caption = this.editMeta.caption;
                    this.activeAsset.category = this.editMeta.category;
                    alert('Metadata updated successfully!');
                } else {
                    alert('Save failed: ' + (data.message || 'Validation error'));
                }
            } catch (err) {
                alert('Network error while saving: ' + err.message);
            } finally {
                this.savingMeta = false;
            }
        },

        async replaceAssetFile(event) {
            const file = event.target.files[0];
            if (!file || !this.activeAsset) return;

            if (!confirm(`Are you sure you want to replace "${this.activeAsset.filename}" with "${file.name}"? The URL will remain identical.`)) {
                event.target.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch(`/admin/media/${this.activeAsset.id}/replace`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert('File successfully replaced!');
                    window.location.reload();
                } else {
                    alert('Replacement failed: ' + (data.message || 'Error'));
                }
            } catch (err) {
                alert('Upload replacement error: ' + err.message);
            }
        },

        openUploadModal() {
            this.queuedFiles = [];
            this.uploadProgress = 0;
            this.uploading = false;
            this.autoConvertPromptShown = false;
            this.selectedConvertFormat = 'webp';
            this.selectedConvertQuality = 'lossless';
            this.uploadModalOpen = true;
        },

        handleFilesSelected(e) {
            const files = Array.from(e.target.files);
            this.queuedFiles.push(...files);
            e.target.value = '';
        },

        handleDrop(e) {
            this.dragOver = false;
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const files = Array.from(e.dataTransfer.files);
                this.queuedFiles.push(...files);
            }
        },

        hasConvertibleImages() {
            const convertible = ['png', 'jpg', 'jpeg', 'bmp', 'gif'];
            return this.queuedFiles.some(f => {
                const ext = f.name.split('.').pop().toLowerCase();
                return convertible.includes(ext);
            });
        },

        getConvertibleFiles() {
            const convertible = ['png', 'jpg', 'jpeg', 'bmp', 'gif'];
            return this.queuedFiles.filter(f => {
                const ext = f.name.split('.').pop().toLowerCase();
                return convertible.includes(ext);
            });
        },

        startUploadWithCheck() {
            if (this.queuedFiles.length === 0) return;
            if (this.hasConvertibleImages() && !this.autoConvertPromptShown) {
                this.optimizationModalOpen = true;
                return;
            }
            this.submitUpload(false);
        },

        applyConversionAndUpload(format, quality = 'lossless') {
            this.selectedConvertFormat = format;
            this.selectedConvertQuality = quality;
            this.autoConvertPromptShown = true;
            this.optimizationModalOpen = false;
            this.submitUpload(false);
        },

        skipConversionAndUpload() {
            this.selectedConvertFormat = 'original';
            this.autoConvertPromptShown = true;
            this.optimizationModalOpen = false;
            this.submitUpload(false);
        },

        removeQueuedFile(index) {
            this.queuedFiles.splice(index, 1);
        },

        formatBytes(bytes) {
            if (bytes <= 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        useExistingDuplicate() {
            this.duplicateModalOpen = false;
            this.uploadModalOpen = false;
            this.queuedFiles = [];
            if (this.duplicateData && this.duplicateData.id) {
                this.openDetails(this.duplicateData.id);
            }
        },

        async submitUpload(force = false) {
            if (this.queuedFiles.length === 0) return;
            this.uploading = true;
            this.uploadProgress = 10;

            const formData = new FormData();
            this.queuedFiles.forEach(file => {
                formData.append('files[]', file);
            });
            formData.append('category', this.uploadCategory);
            if (force) {
                formData.append('force_upload', '1');
            }
            if (this.selectedConvertFormat) {
                formData.append('convert_format', this.selectedConvertFormat);
                formData.append('convert_quality', this.selectedConvertQuality || 'lossless');
            }

            try {
                this.uploadProgress = 50;
                const res = await fetch('{{ route('admin.media.upload') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                this.uploadProgress = 90;
                const data = await res.json();
                this.uploadProgress = 100;

                if (res.status === 409 || data.is_duplicate) {
                    this.uploading = false;
                    this.duplicateData = data.duplicate || (data.duplicates && data.duplicates[0]?.existing) || null;
                    this.duplicateMessage = data.message || 'Duplicate media detected in the library.';
                    this.duplicateModalOpen = true;
                    return;
                }

                if (data.success) {
                    alert(data.message);
                    this.uploadModalOpen = false;
                    window.location.reload();
                } else {
                    alert('Upload issues: ' + (data.message || data.errors?.join(', ')));
                }
            } catch (err) {
                alert('Upload network error: ' + err.message);
            } finally {
                this.uploading = false;
                this.autoConvertPromptShown = false;
            }
        },

        confirmDeleteAsset(id, filename, usageCount) {
            this.deleteTarget = { id, filename, usageCount, force: false };
            this.deleteModalOpen = true;
        },

        async executeDelete() {
            if (!this.deleteTarget.id) return;
            try {
                const res = await fetch(`/admin/media/${this.deleteTarget.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ force: this.deleteTarget.force })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.deleteModalOpen = false;
                    this.drawerOpen = false;
                    window.location.reload();
                } else {
                    alert('Delete blocked: ' + data.message);
                }
            } catch (err) {
                alert('Delete error: ' + err.message);
            }
        },

        async applyBulkCategory() {
            if (!this.bulkCategory || this.selectedIds.length === 0) return;
            try {
                const res = await fetch('{{ route('admin.media.bulk-action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'set_category',
                        ids: this.selectedIds,
                        category: this.bulkCategory
                    })
                });
                const data = await res.json();
                alert(data.message);
                window.location.reload();
            } catch (err) {
                alert('Bulk update error: ' + err.message);
            }
        },

        async bulkRefreshUsage() {
            if (this.selectedIds.length === 0) return;
            try {
                const res = await fetch('{{ route('admin.media.bulk-action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'refresh_usage',
                        ids: this.selectedIds
                    })
                });
                const data = await res.json();
                alert(data.message);
                window.location.reload();
            } catch (err) {
                alert('Usage refresh error: ' + err.message);
            }
        },

        async confirmBulkDelete() {
            if (this.selectedIds.length === 0) return;
            const force = confirm(`Are you sure you want to delete ${this.selectedIds.length} assets?\n\nIf any assets are in-use, click OK to force delete, or Cancel to abort.`);
            if (!force) return;

            try {
                const res = await fetch('{{ route('admin.media.bulk-action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        ids: this.selectedIds,
                        force: true
                    })
                });
                const data = await res.json();
                alert(data.message);
                window.location.reload();
            } catch (err) {
                alert('Bulk delete error: ' + err.message);
            }
        }
    };
}
</script>
@endsection
