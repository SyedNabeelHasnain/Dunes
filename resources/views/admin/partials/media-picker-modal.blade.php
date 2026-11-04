{{-- Universal Media Picker Modal (WordPress / Strapi-Style Master Media Manager) --}}
<div x-data="universalMediaPickerModal()"
     x-init="init()"
     @open-media-picker.window="openModal($event.detail)"
     @keydown.escape.window="if(isOpen) closeModal()"
     x-cloak>

    <!-- Modal Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-6"
         @click.self="closeModal()">

        <!-- Modal Dialog Box -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 w-full max-w-6xl h-[90vh] max-h-[850px] flex flex-col overflow-hidden text-slate-800 font-sans z-50">

            <!-- Modal Header -->
            <div class="px-5 py-3.5 border-b border-slate-200/80 bg-slate-50/90 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center text-lg shadow-xs">
                        <i class="bi bi-images"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Dunes Discovery Media Hub</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Browse, link, or upload images &amp; videos sitewide</p>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center bg-slate-200/70 p-1 rounded-xl text-xs font-bold text-slate-600">
                    <button type="button" 
                            @click="activeTab = 'browse'" 
                            :class="activeTab === 'browse' ? 'bg-white text-primary shadow-xs font-extrabold' : 'hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-grid-fill"></i>
                        <span>Browse Media</span>
                    </button>
                    <button type="button" 
                            @click="activeTab = 'upload'" 
                            :class="activeTab === 'upload' ? 'bg-white text-primary shadow-xs font-extrabold' : 'hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Upload New</span>
                    </button>
                </div>

                <!-- Close Modal Button -->
                <button type="button" 
                        @click="closeModal()" 
                        class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 flex items-center justify-center transition cursor-pointer"
                        title="Close Media Library (Esc)">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <!-- Tab 1: Browse Media Library -->
            <div x-show="activeTab === 'browse'" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <!-- Toolbar: Search & Filter Bar -->
                <div class="px-5 py-3 border-b border-slate-200/80 bg-white flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="searchQuery" 
                               @input.debounce.300ms="fetchAssets(1)" 
                               placeholder="Search media by filename, title..." 
                               class="w-full pl-8 pr-7 py-1.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none">
                        <button type="button" 
                                x-show="searchQuery" 
                                @click="searchQuery = ''; fetchAssets(1)" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Filter Dropdowns & Pills -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Media Type Filter -->
                        <select x-model="filterType" @change="fetchAssets(1)" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none cursor-pointer">
                            <option value="all">All Media</option>
                            <option value="image">🖼️ Images Only</option>
                            <option value="video">🎥 Videos Only</option>
                        </select>

                        <!-- Category Filter -->
                        <select x-model="filterCategory" @change="fetchAssets(1)" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none cursor-pointer">
                            <option value="all">All Categories</option>
                            <option value="tours">Tours &amp; Experiences</option>
                            <option value="blogs">Blog Articles</option>
                            <option value="reviews">Video Reviews &amp; Reels</option>
                            <option value="banners">Hero &amp; Promo Banners</option>
                            <option value="site">Site Assets</option>
                            <option value="general">General</option>
                        </select>

                        <!-- Refresh Button -->
                        <button type="button" @click="fetchAssets(currentPage)" class="p-1.5 rounded-xl border border-slate-200 text-slate-500 hover:text-primary hover:bg-slate-50 transition" title="Refresh">
                            <i class="bi bi-arrow-clockwise text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Main Content Split (Grid on Left, Inspector on Right) -->
                <div class="flex-1 flex min-h-0 overflow-hidden">
                    <!-- Media Grid Area -->
                    <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50 sidebar-scroll relative">
                        <!-- Loading Indicator -->
                        <div x-show="isLoading" class="absolute inset-0 bg-white/70 backdrop-blur-2xs z-20 flex items-center justify-center">
                            <div class="flex flex-col items-center gap-2 text-slate-500 text-xs font-semibold">
                                <div class="w-7 h-7 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>
                                <span>Loading Media Library...</span>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!isLoading && assets.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mb-3">
                                <i class="bi bi-folder2-open"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800">No media found</h4>
                            <p class="text-xs text-slate-500 max-w-xs mt-1 mb-4">No assets match your search or filter. Try a different keyword or upload a file.</p>
                            <button type="button" @click="activeTab = 'upload'" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload to Library
                            </button>
                        </div>

                        <!-- Grid -->
                        <div x-show="assets.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            <template x-for="item in assets" :key="item.id">
                                <div class="group relative aspect-square rounded-xl border-2 bg-white overflow-hidden shadow-2xs hover:shadow-md transition cursor-pointer select-none"
                                     :class="isItemSelected(item) ? 'border-primary ring-2 ring-primary/30' : 'border-slate-200 hover:border-slate-300'"
                                     @click="handleItemClick(item, $event)"
                                     @dblclick="confirmSelection()">

                                    <!-- Selected Checkmark Overlay -->
                                    <div x-show="isItemSelected(item)" class="absolute top-1.5 left-1.5 w-6 h-6 rounded-lg bg-primary text-white flex items-center justify-center text-xs font-extrabold shadow-md z-20">
                                        <template x-if="isMultiple">
                                            <span x-text="getSelectionIndex(item)"></span>
                                        </template>
                                        <template x-if="!isMultiple">
                                            <i class="bi bi-check-lg"></i>
                                        </template>
                                    </div>

                                    <!-- Top Right Extension Badge -->
                                    <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase text-white backdrop-blur-xs z-10"
                                         :class="item.is_video ? 'bg-amber-600' : 'bg-slate-900/70'">
                                        <span x-text="item.extension"></span>
                                        <template x-if="item.format_variants && Object.keys(item.format_variants).length > 0">
                                            <span class="text-amber-300 font-normal ms-0.5" x-text="'+' + Object.keys(item.format_variants).length"></span>
                                        </template>
                                    </div>

                                    <!-- Thumbnail -->
                                    <template x-if="item.is_image">
                                        <img :src="item.clean_public_url" 
                                             :alt="item.alt_text || item.filename" 
                                             loading="lazy" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f8fafc\'><rect width=\'100\' height=\'100\' fill=\'%23f1f5f9\'/><path d=\'M30 40a10 10 0 1 0 0-20 10 10 0 0 0 0 20zm-15 45h70L65 55 50 70 40 60 15 85z\' fill=\'%23cbd5e1\'/></svg>'; this.onerror=null;">
                                    </template>
                                    <template x-if="item.is_video">
                                        <div class="w-full h-full relative bg-slate-900 flex items-center justify-center overflow-hidden">
                                            <video :src="item.clean_public_url + '#t=0.001'" 
                                                   :poster="item.poster_url"
                                                   preload="metadata"
                                                   class="w-full h-full object-cover">
                                            </video>
                                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-md">
                                                    <i class="bi bi-play-fill text-base translate-x-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Bottom Info Overlay -->
                                    <div class="absolute bottom-0 inset-x-0 p-1.5 bg-gradient-to-t from-slate-950/80 via-slate-950/40 to-transparent text-white text-[10px] truncate z-10">
                                        <div class="truncate font-semibold text-white/90" x-text="item.filename"></div>
                                        <div class="flex items-center justify-between text-[9px] text-white/70">
                                            <span x-text="item.formatted_size"></span>
                                            <span x-show="item.usage_count > 0" class="text-emerald-400 font-bold" x-text="item.usage_count + ' in use'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Pagination Footer -->
                        <div x-show="pagination.total > 0" class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-500">
                            <div>
                                Showing <span class="font-bold text-slate-700" x-text="assets.length"></span> of <span class="font-bold text-slate-700" x-text="pagination.total"></span> assets
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        :disabled="currentPage <= 1" 
                                        @click="fetchAssets(currentPage - 1)" 
                                        class="px-3 py-1 rounded-lg border border-slate-200 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-white transition cursor-pointer">
                                    Previous
                                </button>
                                <span class="px-2 font-bold text-slate-700" x-text="'Page ' + currentPage + ' of ' + pagination.last_page"></span>
                                <button type="button" 
                                        :disabled="currentPage >= pagination.last_page" 
                                        @click="fetchAssets(currentPage + 1)" 
                                        class="px-3 py-1 rounded-lg border border-slate-200 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-white transition cursor-pointer">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Inspector Sidebar -->
                    <div class="w-72 sm:w-80 border-l border-slate-200/80 bg-white flex flex-col justify-between overflow-y-auto sidebar-scroll shrink-0 p-4">
                        <!-- Single Selected Item Inspector -->
                        <template x-if="selectedAsset">
                            <div class="space-y-4">
                                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Attachment Details</h4>

                                <!-- Large Preview -->
                                <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-900 aspect-video relative flex items-center justify-center">
                                    <template x-if="selectedAsset.is_image">
                                        <img :src="selectedAsset.clean_public_url" 
                                             class="w-full h-full object-contain"
                                             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23f8fafc\'><rect width=\'100\' height=\'100\' fill=\'%23f1f5f9\'/><path d=\'M30 40a10 10 0 1 0 0-20 10 10 0 0 0 0 20zm-15 45h70L65 55 50 70 40 60 15 85z\' fill=\'%23cbd5e1\'/></svg>'; this.onerror=null;">
                                    </template>
                                    <template x-if="selectedAsset.is_video">
                                        <video :src="selectedAsset.clean_public_url" 
                                               controls 
                                               playsinline 
                                               class="w-full h-full object-contain">
                                        </video>
                                    </template>
                                </div>

                                <!-- File Specs -->
                                <div class="space-y-1.5 text-xs">
                                    <div class="font-bold text-slate-900 break-all" x-text="selectedAsset.filename"></div>
                                    <div class="text-[11px] text-slate-400 font-mono break-all" x-text="selectedAsset.file_path"></div>
                                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] text-slate-500">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 font-bold" x-text="selectedAsset.extension"></span>
                                        <template x-if="selectedAsset.format_variants && Object.keys(selectedAsset.format_variants).length > 0">
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-[10px]" x-text="'+ ' + Object.keys(selectedAsset.format_variants).join(', ').toUpperCase() + ' fallback'"></span>
                                        </template>
                                        <span x-text="selectedAsset.formatted_size"></span>
                                        <span x-show="selectedAsset.dimensions" x-text="selectedAsset.dimensions"></span>
                                    </div>
                                    <div class="pt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold"
                                              :class="selectedAsset.usage_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="selectedAsset.usage_count > 0 ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span x-text="selectedAsset.usage_count > 0 ? selectedAsset.usage_count + ' Linked Usage(s)' : 'Not yet linked'"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Editable Meta Fields (Title, Alt Text) -->
                                <div class="space-y-2.5 pt-2 border-t border-slate-100 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Title</label>
                                        <input type="text" 
                                               x-model="selectedAsset.title" 
                                               placeholder="Media title..."
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Alt Text (Accessibility &amp; SEO)</label>
                                        <input type="text" 
                                               x-model="selectedAsset.alt_text" 
                                               placeholder="Alt description for search engines..."
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                    </div>
                                    <button type="button" 
                                            @click="saveAssetMetadata()" 
                                            class="w-full py-1.5 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                                        <span x-text="isSavingMeta ? 'Saving...' : 'Save Meta Changes'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Multi-select state inspector -->
                        <template x-if="!selectedAsset && selectedAssets.length > 0">
                            <div class="space-y-3">
                                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Multiple Selection</h4>
                                <div class="p-3 rounded-xl bg-primary/10 border border-primary/20 text-xs text-primary font-bold flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-base"></i>
                                    <span x-text="selectedAssets.length + ' item(s) selected'"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5 max-h-60 overflow-y-auto p-1">
                                    <template x-for="item in selectedAssets" :key="item.id">
                                        <div class="aspect-square rounded-lg overflow-hidden border border-slate-200 relative">
                                            <img :src="item.clean_public_url" class="w-full h-full object-cover">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Empty state when nothing selected -->
                        <template x-if="!selectedAsset && selectedAssets.length === 0">
                            <div class="h-full flex flex-col items-center justify-center text-center p-4 text-slate-400">
                                <i class="bi bi-hand-index-thumb text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-medium">Click on any image or video to view specs and select it.</p>
                            </div>
                        </template>

                        <!-- Bottom Primary Action -->
                        <div class="pt-4 border-t border-slate-200/80 mt-4 space-y-2">
                            <button type="button" 
                                    :disabled="!hasSelection" 
                                    @click="confirmSelection()" 
                                    class="w-full py-2.5 rounded-xl bg-primary text-white text-xs font-extrabold shadow-md hover:bg-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="bi bi-check-lg text-sm"></i>
                                <span x-text="confirmButtonText"></span>
                            </button>
                            <button type="button" 
                                    @click="closeModal()" 
                                    class="w-full py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:text-slate-900 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Upload New Media Dropzone -->
            <div x-show="activeTab === 'upload'" class="flex-1 flex flex-col items-center justify-center p-8 bg-slate-50/50 overflow-y-auto">
                <div class="w-full max-w-xl bg-white p-8 rounded-2xl border-2 border-dashed border-slate-300 hover:border-primary transition text-center shadow-xs relative"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)"
                     :class="isDragging ? 'border-primary bg-primary/5' : ''">

                    <!-- Cloud Upload Icon -->
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-3xl mx-auto mb-4 shadow-2xs">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>

                    <h4 class="text-base font-extrabold text-slate-900">Drag &amp; drop files here to upload</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">
                        Supports images (AVIF, WebP, JPG, PNG, SVG) and videos (MP4, WebM, MOV) up to 50MB. Files are automatically indexed and available sitewide.
                    </p>

                    <!-- Category target selector -->
                    <div class="max-w-xs mx-auto mb-5 text-left">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Target Category for Uploaded Media</label>
                        <select x-model="uploadCategory" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none">
                            <option value="tours">Tours &amp; Experiences</option>
                            <option value="blogs">Blog &amp; Articles</option>
                            <option value="reviews">Customer Reviews &amp; Reels</option>
                            <option value="banners">Banners &amp; Promos</option>
                            <option value="site">Site Assets</option>
                            <option value="general" selected>General</option>
                        </select>
                    </div>

                    <!-- Hidden native file input -->
                    <input type="file" 
                           x-ref="fileInput" 
                           @change="handleFilesSelected($event)" 
                           :multiple="isMultiple" 
                           accept="image/*,video/*" 
                           class="hidden">

                    <button type="button" 
                            @click="$refs.fileInput.click()" 
                            class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md hover:bg-primary-dark transition cursor-pointer">
                        <i class="bi bi-folder-plus me-1.5"></i> Select Files from Device
                    </button>

                    <!-- Upload Progress -->
                    <div x-show="isUploading" class="mt-6 text-left">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                            <span x-text="uploadStatusMessage"></span>
                            <span x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-primary transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function universalMediaPickerModal() {
    return {
        isOpen: false,
        activeTab: 'browse',
        filterType: 'all',
        filterCategory: 'all',
        searchQuery: '',
        currentPage: 1,
        pagination: { current_page: 1, last_page: 1, total: 0 },
        assets: [],
        isLoading: false,
        isMultiple: false,
        selectedAsset: null,
        selectedAssets: [],
        callback: null,
        isDragging: false,
        isUploading: false,
        uploadProgress: 0,
        uploadCategory: 'general',
        uploadStatusMessage: 'Uploading...',
        isSavingMeta: false,

        init() {
            // Register global helper on window
            window.openMediaPicker = (options = {}) => {
                this.openModal(options);
            };
            window.DunesMediaPicker = {
                open: (options = {}) => this.openModal(options),
                close: () => this.closeModal(),
            };
        },

        openModal(options = {}) {
            this.callback = typeof options.onSelect === 'function' ? options.onSelect : null;
            this.isMultiple = Boolean(options.multiple);
            this.filterType = options.type || 'all';
            this.filterCategory = options.category || 'all';
            this.selectedAsset = null;
            this.selectedAssets = [];
            this.activeTab = 'browse';
            this.isOpen = true;

            // Pre-select if value passed
            this.preselectedIdentifier = options.selected || null;

            this.fetchAssets(1);
        },

        closeModal() {
            this.isOpen = false;
            this.callback = null;
        },

        async fetchAssets(page = 1) {
            this.isLoading = true;
            this.currentPage = page;
            const params = new URLSearchParams({
                page: page,
                type: this.filterType,
                category: this.filterCategory,
                search: this.searchQuery,
            });

            try {
                const res = await fetch(`{{ route('admin.media.picker-items') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.assets = data.assets;
                    this.pagination = data.pagination;

                    // Re-apply preselection if matching
                    if (this.preselectedIdentifier && !this.selectedAsset) {
                        const matched = this.assets.find(a => 
                            a.file_path === this.preselectedIdentifier || 
                            a.clean_public_url === this.preselectedIdentifier || 
                            a.filename === this.preselectedIdentifier
                        );
                        if (matched) {
                            this.selectAsset(matched);
                        }
                    }
                }
            } catch (err) {
                console.error('Media picker fetch error:', err);
            } finally {
                this.isLoading = false;
            }
        },

        handleItemClick(item, event) {
            if (this.isMultiple) {
                const idx = this.selectedAssets.findIndex(a => a.id === item.id);
                if (idx > -1) {
                    this.selectedAssets.splice(idx, 1);
                } else {
                    this.selectedAssets.push(item);
                }
                this.selectedAsset = this.selectedAssets[this.selectedAssets.length - 1] || null;
            } else {
                this.selectAsset(item);
            }
        },

        selectAsset(item) {
            this.selectedAsset = item;
            this.selectedAssets = [item];
        },

        isItemSelected(item) {
            return this.selectedAssets.some(a => a.id === item.id);
        },

        getSelectionIndex(item) {
            const idx = this.selectedAssets.findIndex(a => a.id === item.id);
            return idx > -1 ? (idx + 1) : '';
        },

        get hasSelection() {
            return this.isMultiple ? this.selectedAssets.length > 0 : Boolean(this.selectedAsset);
        },

        get confirmButtonText() {
            if (this.isMultiple) {
                return `Insert ${this.selectedAssets.length} Selected Media`;
            }
            return 'Insert Selected Media';
        },

        confirmSelection() {
            if (!this.hasSelection) return;

            if (typeof this.callback === 'function') {
                if (this.isMultiple) {
                    this.callback(this.selectedAssets);
                } else {
                    this.callback(this.selectedAsset);
                }
            }
            this.closeModal();
        },

        async saveAssetMetadata() {
            if (!this.selectedAsset) return;
            this.isSavingMeta = true;
            try {
                const res = await fetch(`/admin/media/${this.selectedAsset.id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.selectedAsset.title,
                        alt_text: this.selectedAsset.alt_text
                    })
                });
                const data = await res.json();
                if (data.success) {
                    // Update in local list
                    const found = this.assets.find(a => a.id === this.selectedAsset.id);
                    if (found) {
                        found.title = this.selectedAsset.title;
                        found.alt_text = this.selectedAsset.alt_text;
                    }
                }
            } catch (err) {
                console.error('Save meta error:', err);
            } finally {
                this.isSavingMeta = false;
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer?.files;
            if (files && files.length > 0) {
                this.uploadFiles(files);
            }
        },

        handleFilesSelected(event) {
            const files = event.target?.files;
            if (files && files.length > 0) {
                this.uploadFiles(files);
            }
        },

        async uploadFiles(fileList) {
            if (!fileList || fileList.length === 0) return;

            this.isUploading = true;
            this.uploadProgress = 10;
            this.uploadStatusMessage = `Uploading ${fileList.length} file(s)...`;

            const formData = new FormData();
            for (let i = 0; i < fileList.length; i++) {
                formData.append('files[]', fileList[i]);
            }
            formData.append('category', this.uploadCategory);

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('admin.media.upload') }}', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 90);
                    }
                };

                xhr.onload = async () => {
                    this.uploadProgress = 100;
                    this.isUploading = false;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp.success && resp.uploaded && resp.uploaded.length > 0) {
                            // Switch to browse tab & refresh
                            this.activeTab = 'browse';
                            await this.fetchAssets(1);
                            // Auto select the newly uploaded asset
                            const firstUploaded = resp.uploaded[0];
                            const match = this.assets.find(a => a.id === firstUploaded.id);
                            if (match) {
                                this.selectAsset(match);
                            }
                        }
                    } else {
                        alert('Upload failed: ' + xhr.statusText);
                    }
                };

                xhr.onerror = () => {
                    this.isUploading = false;
                    alert('Upload connection error.');
                };

                xhr.send(formData);
            } catch (err) {
                this.isUploading = false;
                console.error('Upload error:', err);
            }
        }
    };
}
</script>
