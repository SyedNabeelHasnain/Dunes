@extends('layouts.admin')

@section('page_title', 'Inquiries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">Customer Contact Inquiries</h2>
        <p class="text-muted small mb-0">Manage customer messages, questions, and replies from the website contact forms.</p>
    </div>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4 p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="inquiriesTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $c)
                    <tr>
                        <td class="ps-4">
                            <div class="small fw-bold text-dark">
                                {{ $c->created_at ? $c->created_at->format('M j, Y') : '' }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ $c->created_at ? $c->created_at->format('g:ia') : '' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $c->name }}</div>
                            <div class="text-muted small">{{ $c->email }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $c->subject }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $badgeColor = [
                                    'new' => 'danger',
                                    'read' => 'warning',
                                    'replied' => 'success'
                                ][$c->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-capitalize px-3 py-1 rounded-pill">{{ $c->status }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.inquiries.show', $c->id) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="View Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <form action="{{ route('admin.inquiries.status', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="replied">
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Mark Replied" {{ $c->status === 'replied' ? 'disabled' : '' }}>
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.inquiries.destroy', $c->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete" onclick="return confirm('Delete this inquiry?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No contact inquiries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
