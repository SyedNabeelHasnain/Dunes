@extends('layouts.admin')

@section('page_title', 'Inquiries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">Contact Inquiries</h2>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="inquiriesTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $c)
                    <tr>
                        <td class="ps-4">
                            <div class="small fw-medium text-muted">
                                {{ $c->created_at->format('M j, g:ia') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $c->name }}</div>
                            <div class="text-muted small">{{ $c->email }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $c->subject }}</div>
                        </td>
                        <td>
                            @php
                                $badgeColor = [
                                    'new' => 'danger',
                                    'read' => 'warning',
                                    'replied' => 'success'
                                ][$c->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $c->status }}</span>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.inquiries.show', $c->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="View Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <form action="{{ route('admin.inquiries.status', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="replied">
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Mark Replied" {{ $c->status === 'replied' ? 'disabled' : '' }}>
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.inquiries.destroy', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete" onclick="return confirm('Delete this inquiry?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No contact inquiries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $inquiries->links() }}
</div>
@endsection
