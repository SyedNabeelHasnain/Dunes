@extends('layouts.admin')

@section('page_title', 'Edit Post: ' . $post->title)

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.blogs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all">
            <i class="bi bi-chevron-left text-xs"></i> Back to List
        </a>
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 text-sky-600 bg-sky-50/50 hover:bg-sky-50 text-xs font-bold shadow-2xs transition">
            <i class="bi bi-box-arrow-up-right"></i> Preview Article
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h1 class="text-base font-black text-slate-900">Modify Blog Post Details</h1>
            <span class="text-xs font-mono text-slate-400">ID: #{{ $post->id }}</span>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.blogs.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column (Core Content) -->
                    <div class="lg:col-span-8 space-y-6">
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-file-text text-base"></i> Article Content
                            </h2>
                            
                            <div>
                                <label for="post_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Article Title *</label>
                                <input type="text" name="title" id="post_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" value="{{ old('title', $post->title) }}" required>
                            </div>

                            <div>
                                <label for="category_id" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Blog Category *</label>
                                <select name="category_id" id="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="excerpt" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Short Excerpt (Summary) *</label>
                                <textarea name="excerpt" id="excerpt" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" required>{{ old('excerpt', $post->excerpt) }}</textarea>
                            </div>

                            <div>
                                <label for="content" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Main Article Content *</label>
                                <textarea name="content" id="content" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-800 wysiwyg-editor focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-hidden" rows="12" required>{{ old('content', $post->content) }}</textarea>
                            </div>
                        </div>

                        <!-- Author Details -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-person-badge text-base"></i> Author Profile
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="author_name" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Author Name</label>
                                    <input type="text" name="author_name" id="author_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('author_name', $post->author_name) }}" placeholder="Dunes Discovery">
                                </div>
                                <div>
                                    <label for="author_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Author Job Title</label>
                                    <input type="text" name="author_title" id="author_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('author_title', $post->author_title) }}" placeholder="Dubai Tourism Expert">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="author_bio" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Author Short Biography</label>
                                    <textarea name="author_bio" id="author_bio" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" placeholder="Biographical details shown at post footer">{{ old('author_bio', $post->author_bio) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- AI Summary -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-3">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-robot text-base"></i> AI Quick Summary (Optional)
                            </h2>
                            <textarea name="ai_summary" id="ai_summary" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" placeholder="A brief bulleted AI-generated summary shown in an highlight box at the top of the post">{{ old('ai_summary', $post->ai_summary) }}</textarea>
                        </div>
                    </div>

                    <!-- Right Column (Publish settings, tags, SEO, images) -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-sliders text-base"></i> Publish Settings
                            </h2>
                            
                            <div>
                                <label for="status" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Publish Status</label>
                                <select name="status" id="status" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary outline-hidden" required>
                                    <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="scheduled" {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                </select>
                            </div>

                            <div>
                                <label for="published_at" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Publish Date / Time</label>
                                <input type="datetime-local" name="published_at" id="published_at" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                            </div>

                            <div>
                                <label for="read_time" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Est. Read Time (Minutes)</label>
                                <input type="number" name="read_time" id="read_time" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('read_time', $post->read_time) }}" required>
                            </div>

                            <div>
                                <label for="schema_type" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">SEO Schema Type</label>
                                <select name="schema_type" id="schema_type" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden">
                                    <option value="BlogPosting" {{ old('schema_type', $post->schema_type) === 'BlogPosting' ? 'selected' : '' }}>BlogPosting (Default)</option>
                                    <option value="Article" {{ old('schema_type', $post->schema_type) === 'Article' ? 'selected' : '' }}>Article</option>
                                    <option value="NewsArticle" {{ old('schema_type', $post->schema_type) === 'NewsArticle' ? 'selected' : '' }}>NewsArticle</option>
                                    <option value="TravelAdvisory" {{ old('schema_type', $post->schema_type) === 'TravelAdvisory' ? 'selected' : '' }}>TravelAdvisory</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-3">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-tags text-base"></i> Tags
                            </h2>
                            <div>
                                <label for="tags_input" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Article Tags (Comma separated)</label>
                                <input type="text" name="tags[]" id="tags_input" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('tags') ? (is_array(old('tags')) ? implode(', ', old('tags')) : old('tags')) : implode(', ', $post->tags->pluck('name')->toArray()) }}" placeholder="dubai, safari, adventure">
                                <p class="text-[11px] text-slate-400 mt-1">Press comma to separate tags.</p>
                            </div>
                        </div>

                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-search text-base"></i> SEO & Metadata
                            </h2>
                            <div>
                                <label for="meta_title" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('meta_title', $post->meta_title) }}">
                            </div>
                            <div>
                                <label for="meta_desc" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Description</label>
                                <textarea name="meta_desc" id="meta_desc" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" rows="3">{{ old('meta_desc', $post->meta_desc) }}</textarea>
                            </div>
                            <div>
                                <label for="meta_keywords" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('meta_keywords', $post->meta_keywords) }}">
                            </div>
                            <div>
                                <label for="canonical_url" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Canonical URL Override</label>
                                <input type="url" name="canonical_url" id="canonical_url" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ old('canonical_url', $post->canonical_url) }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 space-y-4">
                            <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                <i class="bi bi-images text-base"></i> Media files
                            </h2>
                            <div>
                                <label for="featured_image" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Featured Image</label>
                                @if($post->featured_image)
                                    <div class="mb-2"><img src="{{ asset('images/blog/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-24 rounded-xl object-cover border border-slate-200"></div>
                                @endif
                                <input type="file" name="featured_image" id="featured_image" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Max size 4MB. PNG, JPG, WEBP.</p>
                            </div>
                            <div>
                                <label for="featured_image_caption" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Image Caption (Optional)</label>
                                <input type="text" name="featured_image_caption" id="featured_image_caption" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-primary outline-hidden" value="{{ $post->featured_image_caption }}" placeholder="e.g. Dubai Red Dunes Safari">
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-12 flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all">
                            <i class="bi bi-save"></i> Update Blog Post
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
