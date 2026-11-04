@props([
    'name',
    'value' => null,
    'label' => null,
    'help' => null,
    'type' => 'image',
    'category' => 'all',
    'multiple' => false,
    'form' => null,
    'required' => false,
])

@php
    $initialValue = old($name, $value);
    $isMultiple = (bool) $multiple;
    if ($isMultiple) {
        $initialArray = is_array($initialValue) ? $initialValue : (is_string($initialValue) ? json_decode($initialValue, true) ?: [] : []);
        $normalizedInitial = array_values(array_filter($initialArray));
    } else {
        $normalizedInitial = is_string($initialValue) ? trim($initialValue) : '';
    }
@endphp

<div class="media-picker-component space-y-1.5"
     x-data="mediaPickerComponent({
         name: @js($name),
         initial: @js($normalizedInitial),
         isMultiple: @js($isMultiple),
         mediaType: @js($type),
         targetCategory: @js($category)
     })">

    @if($label)
        <label class="block text-xs font-bold uppercase text-slate-600 tracking-wider">
            {{ $label }}
            @if($required) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    {{-- SINGLE SELECT MODE --}}
    <template x-if="!isMultiple">
        <div class="space-y-2">
            <!-- Selected Media State -->
            <div x-show="currentValue" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/90 shadow-2xs">
                <div class="w-16 h-16 rounded-lg bg-slate-200 overflow-hidden relative shrink-0 border border-slate-300 flex items-center justify-center">
                    <template x-if="mediaType === 'video'">
                        <div class="w-full h-full bg-slate-900 flex items-center justify-center text-amber-400">
                            <i class="bi bi-play-circle-fill text-2xl"></i>
                        </div>
                    </template>
                    <template x-if="mediaType !== 'video'">
                        <img :src="resolvePreviewUrl(currentValue)" 
                             class="w-full h-full object-cover" 
                             onerror="this.src='/images/media-placeholder.svg'; this.onerror=null;">
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 truncate" x-text="extractFilename(currentValue)"></div>
                    <div class="text-[11px] text-slate-400 font-mono truncate" x-text="currentValue"></div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                            <i class="bi bi-link-45deg"></i> Linked from Media Library
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" 
                            @click="openPicker()" 
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition flex items-center gap-1 cursor-pointer">
                        <i class="bi bi-arrow-repeat text-primary"></i> Change
                    </button>
                    <button type="button" 
                            @click="clear()" 
                            class="p-1.5 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-xs shadow-2xs transition cursor-pointer" 
                            title="Remove Selection">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Empty State (No Media Selected) -->
            <div x-show="!currentValue" 
                 class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 hover:bg-slate-50 hover:border-primary/50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 text-xl shadow-2xs">
                        <i class="bi {{ $type === 'video' ? 'bi-camera-video' : 'bi-image' }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-700">No {{ $type === 'video' ? 'video' : 'media' }} selected</div>
                        <div class="text-[11px] text-slate-400">Choose from existing media library or upload a new file</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="openPicker()" 
                            class="px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary-dark transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-images"></i> Choose from Library
                    </button>
                </div>
            </div>

            <!-- Hidden Input for Form Submission -->
            <input type="hidden" 
                   name="{{ $name }}" 
                   :value="currentValue" 
                   @if($form) form="{{ $form }}" @endif>
        </div>
    </template>

    {{-- MULTIPLE / GALLERY SELECT MODE --}}
    <template x-if="isMultiple">
        <div class="space-y-3">
            <!-- Selected Gallery Grid -->
            <div x-show="items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                <template x-for="(item, index) in items" :key="index">
                    <div class="group relative aspect-square rounded-xl border border-slate-200 overflow-hidden bg-slate-100 shadow-2xs">
                        <img :src="resolvePreviewUrl(item)" 
                             class="w-full h-full object-cover"
                             onerror="this.src='/images/media-placeholder.svg'; this.onerror=null;">

                        <!-- Index Badge -->
                        <div class="absolute top-1 left-1 w-5 h-5 rounded-md bg-slate-900/80 text-white text-[10px] font-bold flex items-center justify-center backdrop-blur-xs"
                             x-text="index + 1">
                        </div>

                        <!-- Hover Action Bar -->
                        <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-1.5">
                            <!-- Move Left -->
                            <button type="button" 
                                    x-show="index > 0" 
                                    @click="moveItem(index, -1)" 
                                    class="p-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-800 text-xs shadow-xs transition" 
                                    title="Move Earlier">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <!-- Move Right -->
                            <button type="button" 
                                    x-show="index < items.length - 1" 
                                    @click="moveItem(index, 1)" 
                                    class="p-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-800 text-xs shadow-xs transition" 
                                    title="Move Later">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                            <!-- Remove Item -->
                            <button type="button" 
                                    @click="removeItem(index)" 
                                    class="p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs shadow-xs transition" 
                                    title="Remove from Gallery">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Hidden Input for Form Submission -->
                        <input type="hidden" 
                               name="{{ $name }}[]" 
                               :value="item" 
                               @if($form) form="{{ $form }}" @endif>
                    </div>
                </template>
            </div>

            <!-- Action Cluster -->
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="items.length > 0 ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                    <span class="text-xs font-bold text-slate-700" x-text="items.length + ' image(s) in gallery'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="items.length > 0" 
                            @click="items = []" 
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 text-xs font-semibold hover:bg-white transition cursor-pointer">
                        Clear All
                    </button>
                    <button type="button" 
                            @click="openPicker()" 
                            class="px-3.5 py-1.5 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary-dark transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-plus-circle-fill"></i> Add from Media Library
                    </button>
                </div>
            </div>
        </div>
    </template>

    @if($help)
        <p class="text-[11px] text-slate-400 leading-normal">{{ $help }}</p>
    @endif
</div>

<script>
if (typeof window.mediaPickerComponent !== 'function') {
    window.mediaPickerComponent = function(config) {
        return {
            name: config.name,
            currentValue: config.isMultiple ? '' : (config.initial || ''),
            items: config.isMultiple ? (Array.isArray(config.initial) ? config.initial : []) : [],
            isMultiple: config.isMultiple,
            mediaType: config.mediaType || 'image',
            targetCategory: config.targetCategory || 'all',

            openPicker() {
                if (typeof window.openMediaPicker !== 'function') {
                    console.error('Universal Media Picker Modal is not loaded.');
                    return;
                }

                window.openMediaPicker({
                    type: this.mediaType,
                    category: this.targetCategory,
                    multiple: this.isMultiple,
                    selected: this.isMultiple ? this.items : this.currentValue,
                    onSelect: (selected) => {
                        if (this.isMultiple) {
                            const newPaths = Array.isArray(selected) ? selected.map(s => s.file_path || s.public_url || s) : [];
                            // Append new unique paths
                            newPaths.forEach(p => {
                                const clean = String(p).replace(/^\//, '');
                                if (!this.items.includes(clean)) {
                                    this.items.push(clean);
                                }
                            });
                        } else {
                            const path = selected?.file_path || selected?.public_url || '';
                            this.currentValue = String(path).replace(/^\//, '');
                        }
                    }
                });
            },

            clear() {
                this.currentValue = '';
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            moveItem(index, direction) {
                const target = index + direction;
                if (target < 0 || target >= this.items.length) return;
                const temp = this.items[index];
                this.items[index] = this.items[target];
                this.items[target] = temp;
            },

            extractFilename(path) {
                if (!path) return '';
                const parts = path.split('/');
                return parts[parts.length - 1] || path;
            },

            resolvePreviewUrl(path) {
                if (!path) return '/images/media-placeholder.svg';
                if (path.startsWith('http://') || path.startsWith('https://')) return path;
                const clean = path.replace(/^\//, '');
                // Check if starts with storage or images
                if (clean.startsWith('storage/')) return '/' + clean;
                return '/' + clean;
            }
        };
    };
}
</script>
