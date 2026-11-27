@extends('layouts.admin')

@section('page_title', 'Blog Categories')

@section('content')
<div class="space-y-6" x-data>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Blog Categories</h1>
            <p class="text-xs text-slate-500 mt-0.5">Organize and structure travel articles and destination guides.</p>
        </div>
        <button type="button" @click="$dispatch('open-add-category')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
            <i class="bi bi-plus-lg"></i> Add New Category
        </button>
    </div>

    <!-- Categories Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="categoriesTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Priority</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $cat->name }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <code class="text-xs font-mono text-primary bg-primary/5 px-2 py-0.5 rounded-md border border-primary/15">{{ $cat->slug }}</code>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-slate-500 text-xs">{{ $cat->description ?: '-' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-600 text-xs">
                            {{ $cat->priority }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($cat->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 capitalize">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 capitalize">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" 
                                        class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs edit-cat-btn cursor-pointer" 
                                        title="Edit Category"
                                        data-id="{{ $cat->id }}"
                                        data-name="{{ $cat->name }}"
                                        data-desc="{{ $cat->description }}"
                                        data-priority="{{ $cat->priority }}"
                                        data-status="{{ $cat->status }}"
                                        data-action="{{ route('admin.blog-categories.update', $cat->id) }}">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </button>
                                <form action="{{ route('admin.blog-categories.destroy', $cat->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this category?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs cursor-pointer" title="Delete Category">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400 text-xs">No categories defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!-- Blog Tags Manager -->
    @if(isset($tags))
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4" x-data="{
        editTagOpen: false,
        editTag: { id: null, name: '' },
        openTagEdit(t) { this.editTag = { ...t }; this.editTagOpen = true; }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-tags-fill text-primary"></i> Blog Tags Manager
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Rename or clean up blog tags across all published articles.</p>
            </div>
            <form action="{{ route('admin.blog-tags.store') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="text" name="name" required placeholder="New tag name..." class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold cursor-pointer">+ Add Tag</button>
            </form>
        </div>

        <div class="flex flex-wrap gap-2">
            @forelse($tags as $tag)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                <span class="font-bold text-slate-800">#{{ $tag->name }}</span>
                <span class="text-[10px] font-mono text-slate-400">({{ $tag->posts_count ?? 0 }})</span>
                <button type="button" @click='openTagEdit(@json(['id' => $tag->id, 'name' => $tag->name]))' class="text-slate-400 hover:text-primary cursor-pointer" title="Rename Tag">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <form action="{{ route('admin.blog-tags.destroy', $tag->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete tag &quot;{{ addslashes($tag->name) }}&quot;?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-rose-600 cursor-pointer" title="Delete Tag">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            </div>
            @empty
            <span class="text-xs text-slate-400 italic">No blog tags created yet.</span>
            @endforelse
        </div>

        <div x-show="editTagOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div @click.outside="editTagOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900">Rename Blog Tag</h3>
                    <button type="button" @click="editTagOpen = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="bi bi-x-lg"></i></button>
                </div>
                <form :action="`{{ url('/admin/blog-tags') }}/${editTag.id}`" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tag Name *</label>
                        <input type="text" name="name" x-model="editTag.name" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editTagOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold cursor-pointer">Save Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal: Create Category (Alpine.js) -->
<div x-data="{ open: false }" @open-add-category.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-folder-plus text-primary"></i> Add Blog Category
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('admin.blog-categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="c_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Category Name *</label>
                    <input type="text" name="name" id="c_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required placeholder="e.g. Travel Guides">
                </div>
                <div>
                    <label for="c_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <input type="text" name="description" id="c_desc" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" placeholder="Short description of category content">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c_priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority *</label>
                        <input type="number" name="priority" id="c_priority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" value="99" required>
                    </div>
                    <div>
                        <label for="c_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status *</label>
                        <select name="status" id="c_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Category (Alpine.js) -->
<div x-data="{ open: false }" @open-edit-category.window="open = true" x-show="open" x-cloak class="relative z-50">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Blog Category
                </h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="editCategoryForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="e_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Category Name *</label>
                    <input type="text" name="name" id="e_name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                </div>
                <div>
                    <label for="e_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Description</label>
                    <input type="text" name="description" id="e_desc" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="e_priority" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Priority *</label>
                        <input type="number" name="priority" id="e_priority" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden focus:border-primary" required>
                    </div>
                    <div>
                        <label for="e_status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Status *</label>
                        <select name="status" id="e_status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-800 outline-hidden bg-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.edit-cat-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const desc = $(this).data('desc');
        const priority = $(this).data('priority');
        const status = $(this).data('status');
        const action = $(this).data('action') || `/admin/blog-categories/${id}`;

        $('#e_name').val(name);
        $('#e_desc').val(desc);
        $('#e_priority').val(priority);
        $('#e_status').val(status);

        // Update form action
        $('#editCategoryForm').attr('action', action);

        // Open Alpine modal
        window.dispatchEvent(new CustomEvent('open-edit-category'));
    });
});
</script>
@endpush
@endsection
