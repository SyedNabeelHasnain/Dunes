@extends('layouts.admin')

@section('page_title', 'Edit Legal Page: ' . $page->title)

@section('content')
@php
    $activeLangs = isset($languages) && $languages->count() ? $languages : \App\Models\Language::getActive();
    $defaultLangCode = $activeLangs->firstWhere('is_default', true)?->code ?? 'en';
@endphp
<div class="space-y-6" x-data="{
    pageLang: '{{ $defaultLangCode }}',
    editSectionOpen: false,
    editSection: { id: null, heading: '', heading_ar: '', subheading: '', subheading_ar: '', priority: 10 },
    editItemOpen: false,
    editItem: { id: null, content: '', content_ar: '', priority: 10 },
    openSectionEdit(sec) {
        this.editSection = { ...sec };
        this.editSectionOpen = true;
    },
    openItemEdit(item) {
        this.editItem = { ...item };
        this.editItemOpen = true;
    }
}">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.legal.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition w-fit">
            <i class="bi bi-chevron-left text-slate-400"></i> Back to Legal Pages
        </a>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-primary/10 text-primary border border-primary/20">/{{ $page->slug }}</span>
            <a href="{{ url('/' . $page->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 hover:bg-sky-50 text-sky-700 text-xs font-bold transition">
                <i class="bi bi-box-arrow-up-right"></i> Preview Live
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Page Meta Info Form (Col 5) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-file-text text-primary"></i> Page Information
                </h2>
                <div class="inline-flex flex-wrap items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    @foreach($activeLangs as $lang)
                        <button type="button" @click="pageLang = '{{ $lang->code }}'" :class="pageLang === '{{ $lang->code }}' ? 'bg-white text-primary shadow-2xs' : 'text-slate-600 hover:text-slate-900'" class="px-2 py-0.5 rounded-lg text-[10px] font-bold transition cursor-pointer">
                            {{ strtoupper($lang->code) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('admin.legal.update', $page->id) }}" method="POST" class="space-y-4">
                @csrf
                @foreach($activeLangs as $lang)
                @php
                    $lc = $lang->code;
                    $isRtl = $lang->isRtl();
                    $valTitle = old('title.'.$lc, $page->getTranslation('title', $lc, false) ?: ($lc === 'ar' ? $page->title_ar : ''));
                    $valSubtitle = old('subtitle.'.$lc, $page->getTranslation('subtitle', $lc, false) ?: ($lc === 'ar' ? $page->subtitle_ar : ''));
                    $valDesc = old('description.'.$lc, $page->getTranslation('description', $lc, false) ?: ($lc === 'ar' ? $page->description_ar : ''));
                @endphp
                <div x-show="pageLang === '{{ $lc }}'" @if($lc !== $defaultLangCode) x-cloak @endif class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $lc === 'en' ? 'bg-primary text-white' : 'bg-emerald-600 text-white' }}" style="direction: ltr;">{{ $lang->name }} ({{ strtoupper($lc) }})</span>
                    <div>
                        <label for="title_{{ $lc }}" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Page Title ({{ strtoupper($lc) }}) {{ $lc === 'en' ? '*' : '' }}</label>
                        <input type="text" name="title[{{ $lc }}]" id="title_{{ $lc }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" value="{{ $valTitle }}" {{ $lc === 'en' ? 'required' : '' }}>
                    </div>
                    <div>
                        <label for="subtitle_{{ $lc }}" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Subtitle ({{ strtoupper($lc) }})</label>
                        <input type="text" name="subtitle[{{ $lc }}]" id="subtitle_{{ $lc }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" value="{{ $valSubtitle }}">
                    </div>
                    <div>
                        <label for="description_{{ $lc }}" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Introductory Description ({{ strtoupper($lc) }})</label>
                        <textarea name="description[{{ $lc }}]" id="description_{{ $lc }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" rows="4">{{ $valDesc }}</textarea>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    <i class="bi bi-check2-circle"></i> Save Page Details
                </button>
            </form>
        </div>

        <!-- Sections & Clauses (Col 7) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-list-check text-primary"></i> Page Sections &amp; Clauses
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $page->sections->count() }} Sections
                </span>
            </div>

            <!-- Existing Sections -->
            @forelse($page->sections as $sec)
            <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    @php
                        $secHeadingEn = $sec->getTranslation('heading', 'en', true) ?? '';
                        $secHeadingAr = $sec->getTranslation('heading', 'ar', false) ?: $sec->heading_ar;
                        $secSubheadingEn = $sec->getTranslation('subheading', 'en', false) ?? '';
                        $secSubheadingAr = $sec->getTranslation('subheading', 'ar', false) ?: $sec->subheading_ar;
                        $secPayload = [
                            'id' => $sec->id,
                            'heading' => $secHeadingEn,
                            'heading_ar' => $secHeadingAr ?? '',
                            'subheading' => $secSubheadingEn,
                            'subheading_ar' => $secSubheadingAr ?? '',
                            'priority' => $sec->priority,
                        ];
                    @endphp
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Order: {{ $sec->priority }}</span>
                            <span class="text-sm font-black text-slate-900">{{ $secHeadingEn }}</span>
                        </div>
                        @if($secHeadingAr)
                            <div class="text-xs font-semibold text-primary" dir="rtl">{{ $secHeadingAr }}</div>
                        @endif
                        @if($secSubheadingEn)
                            <div class="text-xs text-slate-400">{{ $secSubheadingEn }}</div>
                        @endif
                        @if($secSubheadingAr)
                            <div class="text-xs text-slate-400" dir="rtl">{{ $secSubheadingAr }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" @click='openSectionEdit(@json($secPayload))' class="w-8 h-8 rounded-xl border border-slate-200 text-slate-600 hover:text-primary hover:border-primary flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Edit Section">
                            <i class="bi bi-pencil-square text-xs"></i>
                        </button>
                        <form action="{{ route('admin.legal.section.delete', $sec->id) }}" method="POST" class="delete-form shrink-0" data-confirm="Are you sure you want to delete this section and all its items?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Delete Section">
                                <i class="bi bi-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Clauses List -->
                <div class="border-l-2 border-primary/30 pl-3.5 space-y-2 my-2">
                    @forelse($sec->items as $item)
                    @php
                        $itemContentEn = $item->getTranslation('content', 'en', true) ?? '';
                        $itemContentAr = $item->getTranslation('content', 'ar', false) ?: $item->content_ar;
                        $itemPayload = [
                            'id' => $item->id,
                            'content' => $itemContentEn,
                            'content_ar' => $itemContentAr ?? '',
                            'priority' => $item->priority,
                        ];
                    @endphp
                    <div class="flex items-start justify-between gap-2 p-2.5 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                        <div class="text-xs text-slate-800 space-y-0.5 flex-1">
                            <div>{{ $itemContentEn }}</div>
                            @if($itemContentAr)
                                <div class="text-slate-500 text-[11px]" dir="rtl">{{ $itemContentAr }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click='openItemEdit(@json($itemPayload))' class="text-slate-400 hover:text-primary p-1 transition cursor-pointer" title="Edit Clause">
                                <i class="bi bi-pencil-square text-xs"></i>
                            </button>
                            <form action="{{ route('admin.legal.item.delete', $item->id) }}" method="POST" class="delete-form shrink-0" data-confirm="Are you sure you want to delete this clause item?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Item">
                                    <i class="bi bi-x-circle text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 py-1 italic">No clause items under this section yet.</div>
                    @endforelse
                </div>

                <!-- Add Item Form (Bilingual) -->
                <form action="{{ route('admin.legal.item.add', $sec->id) }}" method="POST" class="pt-3 border-t border-slate-200/60">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                        <div class="sm:col-span-5">
                            <input type="text" name="content" class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" placeholder="English clause text..." required>
                        </div>
                        <div class="sm:col-span-4">
                            <input type="text" name="content_ar" class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white text-right" placeholder="نص البند بالعربية..." dir="rtl">
                        </div>
                        <div class="sm:col-span-1">
                            <input type="number" name="priority" class="w-full rounded-xl border border-slate-200 px-2 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary bg-white text-center" value="99" title="Priority">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                                + Item
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-xs italic">No sections defined yet for this page.</div>
            @endforelse

            <!-- Add Section Form (Bilingual) -->
            <div class="p-4 bg-slate-50/70 border border-slate-200 rounded-2xl space-y-3">
                <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-plus-circle text-primary"></i> Add New Section Header
                </h3>
                <form action="{{ route('admin.legal.section.add', $page->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Heading (EN) *</label>
                            <input type="text" name="heading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" placeholder="Section Heading (EN)" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1 text-right">عنوان القسم (عربي)</label>
                            <input type="text" name="heading_ar" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary bg-white text-right" placeholder="عنوان القسم (عربي)" dir="rtl">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5">
                            <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1">Subheading (EN - optional)</label>
                            <input type="text" name="subheading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary bg-white" placeholder="Subheading (EN)">
                        </div>
                        <div class="sm:col-span-5">
                            <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1 text-right">العنوان الفرعي (عربي)</label>
                            <input type="text" name="subheading_ar" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 outline-hidden focus:border-primary bg-white text-right" placeholder="العنوان الفرعي (عربي)" dir="rtl">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wider mb-1 text-center">Priority *</label>
                            <input type="number" name="priority" class="w-full rounded-xl border border-slate-200 px-2 py-2 text-xs text-slate-800 outline-hidden focus:border-primary bg-white text-center" value="99" required>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-xs transition cursor-pointer">
                            + Add Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div x-show="editSectionOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="editSectionOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Edit Legal Section</h3>
                <button type="button" @click="editSectionOpen = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`{{ url('/admin/legal-pages/section') }}/${editSection.id}`" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Heading (EN) *</label>
                    <input type="text" name="heading" x-model="editSection.heading" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Heading (AR)</label>
                    <input type="text" name="heading_ar" x-model="editSection.heading_ar" dir="rtl" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-right">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Subheading (EN)</label>
                        <input type="text" name="subheading" x-model="editSection.subheading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Subheading (AR)</label>
                        <input type="text" name="subheading_ar" x-model="editSection.subheading_ar" dir="rtl" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-right">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Priority *</label>
                    <input type="number" name="priority" x-model="editSection.priority" required class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editSectionOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold cursor-pointer">Update Section</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Clause Item Modal -->
    <div x-show="editItemOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="editItemOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Edit Legal Clause Item</h3>
                <button type="button" @click="editItemOpen = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`{{ url('/admin/legal-pages/item') }}/${editItem.id}`" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clause Text (EN) *</label>
                    <textarea name="content" x-model="editItem.content" rows="3" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clause Text (AR)</label>
                    <textarea name="content_ar" x-model="editItem.content_ar" rows="3" dir="rtl" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-right"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Priority *</label>
                    <input type="number" name="priority" x-model="editItem.priority" required class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editItemOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold cursor-pointer">Update Clause</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
