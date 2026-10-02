@extends('layouts.admin')

@section('page_title', 'Edit Page Content: ' . $page->name)

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
                @endphp
                <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition" x-data="{ expanded: false, editMode: false }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-200 text-slate-700">#{{ $sec->section_key }}</span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 truncate">{{ $sec->name }}</h4>
                                <p class="text-[11px] text-slate-500 truncate">{{ $sec->title ?: 'No heading' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
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

                            <!-- Multilingual Title & Subtitle for this Section -->
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
