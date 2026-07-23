@extends('layouts.admin')

@section('page_title', 'Blog Posts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Manage Blog Articles</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
            <i class="bi bi-tags-fill me-1 text-primary"></i> Blog Categories
        </a>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Write New Post
        </a>
    </div>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="blogsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Article Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Read Time</th>
                        <th>Published Date</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $p)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($p->featured_image)
                                    <img src="{{ asset('images/blog/' . $p->featured_image) }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" class="border" onerror="this.src='{{ asset('images/desert-safari-poster.avif') }}'">
                                @else
                                    <img src="{{ asset('images/desert-safari-poster.avif') }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" class="border">
                                @endif
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $p->title }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $p->excerpt }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small">
                                {{ $p->category ? $p->category->name : 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeColor = [
                                    'published' => 'success',
                                    'draft' => 'secondary',
                                    'scheduled' => 'warning'
                                ][$p->status] ?? 'info';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $p->status }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark"><i class="bi bi-clock me-1 text-primary"></i>{{ $p->read_time ?: '5' }} min</div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-muted">
                                {{ $p->published_at ? \Carbon\Carbon::parse($p->published_at)->format('M j, Y') : 'Not Published' }}
                            </div>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.blogs.edit', $p->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Post">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="{{ route('blog.show', $p->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Preview Post">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <form action="{{ route('admin.blogs.destroy', $p->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Post" onclick="return confirm('Delete this post?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No blog articles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $posts->links() }}
</div>
@endsection
