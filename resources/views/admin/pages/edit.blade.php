@extends('layouts.admin')

@section('page_title', 'Edit Page Content: ' . $page->name)

@php
    $sectionShortcuts = [
        'videos' => [
            'route' => route('admin.video-reviews.index'),
            'label' => 'Manage Video Reviews',
            'icon' => 'bi-camera-reels-fill',
            'badge_color' => 'bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-200',
            'desc' => 'Manage guest customer video reels, YouTube embeds, approval status, and front-end placement.'
        ],
        'video_reels' => [
            'route' => route('admin.video-reviews.index'),
            'label' => 'Manage Video Reviews',
            'icon' => 'bi-camera-reels-fill',
            'badge_color' => 'bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-200',
            'desc' => 'Manage guest customer video reels, YouTube embeds, approval status, and front-end placement.'
        ],
        'reviews' => [
            'route' => route('admin.reviews.index'),
            'label' => 'Manage Guest Reviews',
            'icon' => 'bi-star-fill',
            'badge_color' => 'bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-200',
            'desc' => 'Moderate 5-star customer testimonials, Google & TripAdvisor sync, and review photos.'
        ],
        'moments' => [
            'route' => route('admin.media.index'),
            'label' => 'Manage Media & Moments',
            'icon' => 'bi-images',
            'badge_color' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200',
            'desc' => 'Upload, replace, and organize photography, desert safari moments, and gallery media.'
        ],
        'featured_tours' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Tours & Packages',
            'icon' => 'bi-compass-fill',
            'badge_color' => 'bg-orange-50 text-orange-700 hover:bg-orange-100 border-orange-200',
            'desc' => 'Edit pricing, itineraries, inclusions, availability, and featured flags on all tours.'
        ],
        'silos' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Adventure Categories',
            'icon' => 'bi-grid-fill',
            'badge_color' => 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200',
            'desc' => 'Organize adventure categories (Morning, Evening, Overnight, Buggies, Dhow Cruises).'
        ],
        'concierge' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Concierge Tours',
            'icon' => 'bi-chat-dots-fill',
            'badge_color' => 'bg-purple-50 text-purple-700 hover:bg-purple-100 border-purple-200',
            'desc' => 'Configure VIP custom safari concierge options and direct booking packages.'
        ],
        'why_choose_us' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Tour Highlights',
            'icon' => 'bi-check-circle-fill',
            'badge_color' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200',
            'desc' => 'Edit value propositions, direct-operator guarantees, and booking perks.'
        ],
        'why_book' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Tour Highlights',
            'icon' => 'bi-check-circle-fill',
            'badge_color' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200',
            'desc' => 'Edit value propositions, direct-operator guarantees, and booking perks.'
        ],
        'faqs' => [
            'route' => route('admin.faqs.index'),
            'label' => 'Manage FAQs Hub',
            'icon' => 'bi-question-circle-fill',
            'badge_color' => 'bg-teal-50 text-teal-700 hover:bg-teal-100 border-teal-200',
            'desc' => 'Add, reorder, or edit questions and answers for desert safaris and city tours.'
        ],
        'trust_bar' => [
            'route' => route('admin.settings.general'),
            'label' => 'Manage Trust & Badges',
            'icon' => 'bi-shield-check',
            'badge_color' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200',
            'desc' => 'Configure DTCM licensing, cancellation policy, halal certification, and operator badges.'
        ],
        'desert_cta' => [
            'route' => route('admin.bookings.index'),
            'label' => 'Manage Bookings Funnel',
            'icon' => 'bi-calendar-check-fill',
            'badge_color' => 'bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-200',
            'desc' => 'View incoming guest bookings, reservation statuses, passenger details, and vouchers.'
        ],
        'channels' => [
            'route' => route('admin.whatsapp.index'),
            'label' => 'Manage WhatsApp Concierge',
            'icon' => 'bi-whatsapp',
            'badge_color' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200',
            'desc' => 'Monitor real-time WhatsApp conversation leads, agent assignments, and phone routing.'
        ],
        'office_info' => [
            'route' => route('admin.settings.general'),
            'label' => 'Edit Office & Contact Info',
            'icon' => 'bi-geo-alt-fill',
            'badge_color' => 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100 border-cyan-200',
            'desc' => 'Update physical office address, phone numbers, email support, and operating hours.'
        ],
        'support_banner' => [
            'route' => route('admin.inquiries.index'),
            'label' => 'Manage Inquiries Desk',
            'icon' => 'bi-envelope-paper-fill',
            'badge_color' => 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200',
            'desc' => 'Respond to customer contact messages, group custom quotes, and urgent requests.'
        ],
        'happy_guests' => [
            'route' => route('admin.operations.index'),
            'label' => 'Manage Fleet & Operations',
            'icon' => 'bi-truck',
            'badge_color' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200',
            'desc' => 'Track Land Cruiser fleet, 1000cc buggies, drivers, guides, and desert logistics.'
        ],
        'story' => [
            'route' => route('admin.settings.general'),
            'label' => 'Manage Brand Identity',
            'icon' => 'bi-building-fill',
            'badge_color' => 'bg-slate-100 text-slate-800 hover:bg-slate-200 border-slate-300',
            'desc' => 'Update company history, mission, brand description, and licensing credentials.'
        ],
        'values' => [
            'route' => route('admin.settings.general'),
            'label' => 'Manage Core Values',
            'icon' => 'bi-award-fill',
            'badge_color' => 'bg-slate-100 text-slate-800 hover:bg-slate-200 border-slate-300',
            'desc' => 'Configure core company values, safety standards, and customer promises.'
        ],
        'categories' => [
            'route' => route('admin.tours.index'),
            'label' => 'Manage Tour Categories',
            'icon' => 'bi-collection-fill',
            'badge_color' => 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200',
            'desc' => 'Manage tour category silos and catalog filtering.'
        ],
        'community' => [
            'route' => route('admin.media.index'),
            'label' => 'Manage Community Media',
            'icon' => 'bi-instagram',
            'badge_color' => 'bg-pink-50 text-pink-700 hover:bg-pink-100 border-pink-200',
            'desc' => 'Curate traveler social moments, tag #DunesDiscovery, and Instagram media.'
        ],
    ];
@endphp

@section('content')
<div class="space-y-6" x-data="{ addSectionModal: false, activeLangTab: 'en' }">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition w-fit">
            <i class="bi bi-chevron-left text-slate-400"></i> Back to Pages CMS
        </a>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-primary/10 text-primary border border-primary/20">/{{ $page->slug }}</span>
            <a href="{{ url('/' . ($page->slug === 'home' ? '' : $page->slug)) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 hover:bg-sky-50 text-sky-700 text-xs font-bold transition">
                <i class="bi bi-box-arrow-up-right"></i> Preview Live
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Page Meta Info Form (Col 5) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i> Page Details
                </h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">5-Language CMS</span>
            </div>

            <!-- Language Switcher Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl overflow-x-auto">
                @foreach($languages as $lang)
                <button type="button" @click="activeLangTab = '{{ $lang->code }}'" :class="activeLangTab === '{{ $lang->code }}' ? 'bg-white text-slate-900 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-700 font-semibold'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1 shrink-0">
                    <span>{{ $lang->flag }}</span>
                    <span>{{ strtoupper($lang->code) }}</span>
                </button>
                @endforeach
            </div>

            <form action="{{ route('admin.pages.update', $page->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Internal Page Name *</label>
                    <input type="text" name="name" id="name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" value="{{ old('name', $page->name) }}" required>
                </div>

                @foreach($languages as $lang)
                @php
                    $c = $lang->code;
                    $titleTranslations = $page->getTranslations('title');
                    $subtitleTranslations = $page->getTranslations('subtitle');
                    $metaTitleTranslations = $page->getTranslations('meta_title');
                    $metaDescTranslations = $page->getTranslations('meta_description');
                @endphp
                <div x-show="activeLangTab === '{{ $c }}'" class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3" dir="{{ $lang->direction }}">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                            <span>{{ $lang->flag }}</span> {{ $lang->name }} ({{ strtoupper($c) }})
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Page Title ({{ strtoupper($c) }}) *</label>
                        <input type="text" name="title[{{ $c }}]" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" value="{{ old('title.'.$c, $titleTranslations[$c] ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Subtitle / Hero Tagline ({{ strtoupper($c) }})</label>
                        <textarea name="subtitle[{{ $c }}]" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white">{{ old('subtitle.'.$c, $subtitleTranslations[$c] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">SEO Meta Title ({{ strtoupper($c) }})</label>
                        <input type="text" name="meta_title[{{ $c }}]" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" value="{{ old('meta_title.'.$c, $metaTitleTranslations[$c] ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">SEO Meta Description ({{ strtoupper($c) }})</label>
                        <textarea name="meta_description[{{ $c }}]" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white">{{ old('meta_description.'.$c, $metaDescTranslations[$c] ?? '') }}</textarea>
                    </div>
                </div>
                @endforeach

                <div>
                    <label for="status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Publish Status</label>
                    <select name="status" id="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white">
                        <option value="published" {{ $page->status === 'published' ? 'selected' : '' }}>Published (Active on Site)</option>
                        <option value="draft" {{ $page->status === 'draft' ? 'selected' : '' }}>Draft (Admin Only)</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    Save Page Information
                </button>
            </form>
        </div>

        <!-- Page Sections List & CRUD (Col 7) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-stack text-primary"></i> Page Sections & Content Blocks
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Edit live headings, descriptions, and dynamic widgets for this page.</p>
                </div>
                <button type="button" @click="addSectionModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    <i class="bi bi-plus-lg"></i> Add Section
                </button>
            </div>

            <!-- Sections Accordion List -->
            <div class="space-y-4">
                @forelse($page->sections as $sec)
                @php
                    $secTitleTrans = $sec->getTranslations('title');
                    $secSubTrans = $sec->getTranslations('subtitle');
                    $secBodyTrans = $sec->getTranslations('body');
                @endphp
                <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition" x-data="{ expanded: false, editMode: false, isActive: {{ $sec->is_active ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-200 text-slate-700 shrink-0">#{{ $sec->section_key }}</span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 truncate">{{ $sec->name }}</h4>
                                <p class="text-[11px] text-slate-500 truncate">{{ $sec->title ?: 'No heading' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
                            @if(isset($sectionShortcuts[$sec->section_key]))
                                @php $sc = $sectionShortcuts[$sec->section_key]; @endphp
                                <a href="{{ $sc['route'] }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border {{ $sc['badge_color'] }} text-xs font-bold transition shadow-2xs hover:shadow-sm" 
                                   title="{{ $sc['desc'] }}">
                                    <i class="bi {{ $sc['icon'] }}"></i>
                                    <span>{{ $sc['label'] }}</span>
                                    <i class="bi bi-box-arrow-up-right text-[10px] opacity-75"></i>
                                </a>
                            @endif
                            <button type="button"
                                @click="fetch('{{ route('admin.pages.sections.toggle-status', ['pageId' => $page->id, 'sectionId' => $sec->id]) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => r.json()).then(d => { if(d.success) isActive = d.is_active; })"
                                :class="isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
                                class="px-2 py-1 rounded-lg border text-[10px] font-bold transition cursor-pointer"
                                x-text="isActive ? 'Active' : 'Hidden'">
                            </button>
                            <button type="button" @click="editMode = !editMode" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form action="{{ route('admin.pages.sections.delete', ['pageId' => $page->id, 'sectionId' => $sec->id]) }}" method="POST" onsubmit="return confirm('Delete this section permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Delete Section">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Inline Section Editor Form -->
                    <div x-show="editMode" class="mt-4 pt-4 border-t border-slate-200" style="display: none;">
                        @if(isset($sectionShortcuts[$sec->section_key]))
                            @php $sc = $sectionShortcuts[$sec->section_key]; @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-amber-200/80 text-xs mb-3">
                                <div class="flex items-center gap-2.5 text-slate-800">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                                        <i class="bi {{ $sc['icon'] }} text-base"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $sc['label'] }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $sc['desc'] }}</div>
                                    </div>
                                </div>
                                <a href="{{ $sc['route'] }}" 
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition shadow-xs shrink-0 text-xs">
                                    <span>Open {{ $sc['label'] }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        @endif

                        <form action="{{ route('admin.pages.sections.update', ['pageId' => $page->id, 'sectionId' => $sec->id]) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Section Display Name</label>
                                    <input type="text" name="name" value="{{ $sec->name }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs bg-white text-slate-800" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Display Order</label>
                                    <input type="number" name="order" value="{{ $sec->order }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs bg-white text-slate-800">
                                </div>
                            </div>

                            <!-- Multilingual Title, Subtitle & Body for this Section -->
                            <div class="space-y-3 bg-white p-3 rounded-xl border border-slate-200">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Translations Across Active Languages</span>
                                @foreach($languages as $lang)
                                @php $c = $lang->code; @endphp
                                <div class="p-2.5 bg-slate-50 rounded-lg space-y-2 border border-slate-100">
                                    <div class="flex items-center gap-1 text-[11px] font-bold text-slate-700">
                                        <span>{{ $lang->flag }}</span>
                                        <span>{{ $lang->name }} ({{ strtoupper($c) }})</span>
                                    </div>
                                    <input type="text" name="title[{{ $c }}]" placeholder="Heading in {{ $lang->name }}" value="{{ $secTitleTrans[$c] ?? '' }}" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs bg-white text-slate-800" dir="{{ $lang->direction }}">
                                    <textarea name="subtitle[{{ $c }}]" rows="2" placeholder="Subtitle / text in {{ $lang->name }}" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs bg-white text-slate-800" dir="{{ $lang->direction }}">{{ $secSubTrans[$c] ?? '' }}</textarea>
                                    <textarea name="body[{{ $c }}]" rows="2" placeholder="Body content (optional) in {{ $lang->name }}" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs bg-white text-slate-800" dir="{{ $lang->direction }}">{{ $secBodyTrans[$c] ?? '' }}</textarea>
                                </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="editMode = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition">Update Section</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    No sections added yet for this page. Click "Add Section" above to add one.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div x-show="addSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.outside="addSectionModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">Add New Page Section</h3>
                <button type="button" @click="addSectionModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            <form action="{{ route('admin.pages.sections.add', $page->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Section Key (slug) *</label>
                    <input type="text" name="section_key" placeholder="e.g. promo_banner, special_features" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Section Display Name *</label>
                    <input type="text" name="name" placeholder="e.g. Special Features Grid" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Default Title (English)</label>
                    <input type="text" name="title" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Default Subtitle (English)</label>
                    <textarea name="subtitle" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addSectionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">Create Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
