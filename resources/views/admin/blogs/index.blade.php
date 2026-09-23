@extends('layouts.admin')

@section('page_title', 'Blog Posts')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manage Blog Articles</h1>
            <p class="text-xs text-slate-500 mt-0.5">Publish articles, desert safari travel guides, news, and optimize SEO rankings.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-2xs transition">
                <i class="bi bi-tags-fill text-primary"></i> Blog Categories
            </a>
            <a href="{{ route('admin.blogs.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                <i class="bi bi-plus-lg"></i> Write New Post
            </a>
        </div>
    </div>

    <!-- Blogs Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700 datatable" id="blogsTable">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Article Title</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Read Time</th>
                        <th class="py-3 px-4">Published Date</th>
                        <th class="py-3 px-4 text-right no-sort pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($posts as $p)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                @if($p->featured_image)
                                    <img src="{{ asset('images/blog/' . $p->featured_image) }}" alt="{{ $p->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0" onerror="this.src='{{ asset('images/desert-safari-poster.avif') }}'">
                                @else
                                    <img src="{{ asset('images/desert-safari-poster.avif') }}" alt="Default post thumbnail" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0">
                                @endif
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $p->title }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $p->excerpt }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $p->category ? $p->category->name : 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $badgeColor = match($p->status) {
                                    'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize cursor-pointer hover:scale-105 transition-transform ajax-toggle-status {{ $badgeColor }}" data-url="{{ route('admin.blogs.toggle-status', $p->id) }}" title="Click to toggle status">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="text-xs font-semibold text-slate-700 flex items-center justify-center gap-1">
                                <i class="bi bi-clock text-primary"></i> {{ $p->read_time ?: '5' }} min
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs text-slate-500">
                                {{ $p->published_at ? \Carbon\Carbon::parse($p->published_at)->format('M j, Y') : 'Draft / Unpublished' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right pe-4">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.blogs.edit', $p->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="Edit Post">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </a>
                                <a href="{{ route('blog.show', $p->slug) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-xl border border-sky-200 text-sky-600 hover:bg-sky-50 flex items-center justify-center bg-white transition shadow-2xs" title="Preview Post">
                                    <i class="bi bi-box-arrow-up-right text-xs"></i>
                                </a>
                                <form action="{{ route('admin.blogs.destroy', $p->id) }}" method="POST" class="inline delete-form" data-confirm="Are you sure you want to delete this blog post?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs" title="Delete Post">
                                        <i class="bi bi-trash3-fill text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">No blog articles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
