@extends('layouts.admin')

@section('page_title', 'Write New Post')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-chevron-left me-1"></i> Back to List
    </a>
</div>

<div class="card card-modern border shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom ps-4 pe-4">
        <h5 class="fw-800 mb-0 text-dark">New Blog Post Details</h5>
    </div>
    
    <div class="card-body p-4 ps-4 pe-4">
        <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <!-- Left Column (Core Content) -->
                <div class="col-lg-8">
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Article Content</h6>
                        
                        <div class="mb-3">
                            <label for="post_title" class="form-label fw-bold text-dark">Article Title</label>
                            <input type="text" name="title" id="post_title" class="form-control" placeholder="e.g. 10 Best Things to Do in Dubai Desert" required style="height: 48px; border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold text-dark">Blog Category</label>
                            <select name="category_id" id="category_id" class="form-select" required style="height: 48px; border-radius: 8px;">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label fw-bold text-dark">Short Excerpt (Summary)</label>
                            <textarea name="excerpt" id="excerpt" class="form-control" rows="2" placeholder="Brief summary displayed on listings" required style="border-radius: 8px;"></textarea>
                        </div>

                        <div class="mb-0">
                            <label for="content" class="form-label fw-bold text-dark">Main Article Content (HTML allowed)</label>
                            <textarea name="content" id="content" class="form-control" rows="12" placeholder="Write article here..." required style="border-radius: 8px; font-family: Courier, monospace;"></textarea>
                        </div>
                    </div>

                    <!-- Author Details -->
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Author Profile</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="author_name" class="form-label fw-bold text-dark">Author Name</label>
                                <input type="text" name="author_name" id="author_name" class="form-control" value="Dunes Discovery" placeholder="Dunes Discovery">
                            </div>
                            <div class="col-md-6">
                                <label for="author_title" class="form-label fw-bold text-dark">Author Job Title</label>
                                <input type="text" name="author_title" id="author_title" class="form-control" value="Dubai Tourism Expert" placeholder="Dubai Tourism Expert">
                            </div>
                            <div class="col-12">
                                <label for="author_bio" class="form-label fw-bold text-dark">Author Short Biography</label>
                                <textarea name="author_bio" id="author_bio" class="form-control" rows="2" placeholder="Biographical details shown at post footer"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- AI Summary -->
                    <div class="p-4 bg-light rounded-4 border mb-0">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">AI Quick Summary (Optional)</h6>
                        <div class="mb-0">
                            <textarea name="ai_summary" id="ai_summary" class="form-control" rows="2" placeholder="A brief bulleted AI-generated summary shown in an highlight box at the top of the post"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Publish settings, tags, SEO, images) -->
                <div class="col-lg-4">
                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Publish Settings</h6>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold text-dark">Publish Status</label>
                            <select name="status" id="status" class="form-select" required style="border-radius: 8px;">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="published_at" class="form-label fw-bold text-dark">Publish Date / Time</label>
                            <input type="datetime-local" name="published_at" id="published_at" class="form-control" style="border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label for="read_time" class="form-label fw-bold text-dark">Est. Read Time (Minutes)</label>
                            <input type="number" name="read_time" id="read_time" class="form-control" value="5" required style="border-radius: 8px;">
                        </div>

                        <div class="mb-0">
                            <label for="schema_type" class="form-label fw-bold text-dark">SEO Schema Type</label>
                            <select name="schema_type" id="schema_type" class="form-select" style="border-radius: 8px;">
                                <option value="BlogPosting">BlogPosting (Default)</option>
                                <option value="Article">Article</option>
                                <option value="NewsArticle">NewsArticle</option>
                                <option value="TravelAdvisory">TravelAdvisory</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Tags</h6>
                        <div class="mb-0">
                            <label for="tags_input" class="form-label fw-bold text-dark small">Article Tags (Comma separated)</label>
                            <input type="text" name="tags[]" id="tags_input" class="form-control" placeholder="dubai, safari, adventure">
                            <div class="form-text">Press comma to separate tags.</div>
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">SEO & Metadata</h6>
                        <div class="mb-3">
                            <label for="meta_title" class="form-label fw-bold text-dark small">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label for="meta_desc" class="form-label fw-bold text-dark small">Meta Description</label>
                            <textarea name="meta_desc" id="meta_desc" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label fw-bold text-dark small">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control form-control-sm">
                        </div>
                        <div class="mb-0">
                            <label for="canonical_url" class="form-label fw-bold text-dark small">Canonical URL Override</label>
                            <input type="url" name="canonical_url" id="canonical_url" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <h6 class="text-primary fw-800 text-uppercase small mb-4">Media files</h6>
                        <div class="mb-3">
                            <label for="featured_image" class="form-label fw-bold text-dark">Featured Image</label>
                            <input type="file" name="featured_image" id="featured_image" class="form-control">
                            <div class="form-text">Max size 4MB. PNG, JPG, WEBP.</div>
                        </div>
                        <div class="mb-0">
                            <label for="featured_image_caption" class="form-label fw-bold text-dark small">Image Caption (Optional)</label>
                            <input type="text" name="featured_image_caption" id="featured_image_caption" class="form-control form-control-sm" placeholder="e.g. Dubai Red Dunes Safari">
                        </div>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Publish Blog Post</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
