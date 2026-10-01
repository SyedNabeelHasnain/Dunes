@extends('layouts.admin')

@section('page_title', 'Languages & Locales')

@section('content')
<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editLang: { id: '', code: '', name: '', native_name: '', direction: 'ltr', flag: '', sort_order: 10, is_active: 1 },
    openEdit(lang) {
        this.editLang = { ...lang };
        this.editModalOpen = true;
    }
}">
    <!-- Header Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-white flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full border border-slate-200 bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="bi bi-translate"></i>
                </div>
                <div>
                    <h5 class="text-base font-extrabold text-slate-900 leading-tight">Languages & Dynamic Locales</h5>
                    <div class="text-xs text-slate-500 mt-0.5">Manage international portal languages, LTR/RTL layouts, and automated locale lifecycle generation.</div>
                </div>
            </div>
            <div>
                <button type="button" @click="addModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    <i class="bi bi-plus-lg"></i>
                    <span>Add New Language</span>
                </button>
            </div>
        </div>

        <!-- System Architecture Notice -->
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-3 text-xs text-slate-600">
            <i class="bi bi-shield-check text-emerald-600 text-sm"></i>
            <div>
                <span class="font-bold text-slate-800">English (en)</span> is the immutable root default language. Adding any language automatically builds its complete locale ecosystem (<code class="bg-slate-200/70 text-slate-700 px-1.5 py-0.5 rounded text-[11px]">lang/{code}</code>). Removing a language permanently purges its locale files.
            </div>
        </div>

        <!-- Languages Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Language</th>
                        <th class="px-6 py-3.5">Locale Code</th>
                        <th class="px-6 py-3.5">Direction</th>
                        <th class="px-6 py-3.5">Sort Order</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($languages as $lang)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-base font-bold shrink-0">
                                        @if($lang->flag)
                                            <span>{{ $lang->flag }}</span>
                                        @elseif($lang->code === 'en')
                                            <span>🇬🇧</span>
                                        @elseif($lang->code === 'ar')
                                            <span>🇦🇪</span>
                                        @else
                                            <i class="bi bi-globe text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                            <span>{{ $lang->name }}</span>
                                            @if($lang->is_default)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Default Root</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-medium font-arabic">{{ $lang->native_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200 text-xs">{{ $lang->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($lang->isRtl())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="bi bi-text-right"></i> RTL (Right to Left)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="bi bi-text-left"></i> LTR (Left to Right)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-700">
                                {{ $lang->sort_order }}
                            </td>
                            <td class="px-6 py-4">
                                @if($lang->code === 'en')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100/70 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Always Active
                                    </span>
                                @else
                                    <form action="{{ route('admin.settings.languages.toggle-status', $lang->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold cursor-pointer transition {{ $lang->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $lang->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $lang->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-end">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($lang) }})" class="p-2 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100 transition cursor-pointer" title="Edit Language Details">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    @if($lang->code === 'en' || $lang->is_default)
                                        <span class="p-2 rounded-lg text-slate-300 cursor-not-allowed" title="English is the default root language and cannot be deleted">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                    @else
                                        <form action="{{ route('admin.settings.languages.destroy', $lang->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete [{{ $lang->name }}]? This will permanently purge all its locale files and translations.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete Language and Purge Locale">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No languages found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Language Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="addModalOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h6 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-plus-circle text-primary"></i> Add New Language & Locale
                </h6>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.settings.languages.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Locale Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" placeholder="e.g. ru, fr, de, it" maxlength="10" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden uppercase font-mono">
                        <span class="text-[11px] text-slate-400">ISO 639-1 two-letter lowercase code (e.g. ru for Russian).</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">English Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" placeholder="e.g. Russian" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Native Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="native_name" placeholder="e.g. Русский" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Direction <span class="text-rose-500">*</span></label>
                            <select name="direction" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                                <option value="ltr">LTR (Left to Right)</option>
                                <option value="rtl">RTL (Right to Left)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Flag Emoji / Code</label>
                            <input type="text" name="flag" placeholder="e.g. 🇷🇺" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                        <input type="number" name="sort_order" value="10" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition shadow-xs cursor-pointer">Build Complete Locale</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Language Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h6 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Language: <span class="font-mono text-primary" x-text="editLang.code"></span>
                </h6>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form :action="'{{ url('admin/settings/languages') }}/' + editLang.id" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">English Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editLang.name" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Native Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="native_name" x-model="editLang.native_name" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Direction <span class="text-rose-500">*</span></label>
                            <select name="direction" x-model="editLang.direction" :disabled="editLang.code === 'en'" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden disabled:bg-slate-100 disabled:text-slate-400">
                                <option value="ltr">LTR (Left to Right)</option>
                                <option value="rtl">RTL (Right to Left)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Flag Emoji / Code</label>
                            <input type="text" name="flag" x-model="editLang.flag" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                        <input type="number" name="sort_order" x-model="editLang.sort_order" min="0" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition shadow-xs cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
