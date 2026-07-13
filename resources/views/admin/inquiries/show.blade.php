@extends('layouts.admin')

@section('page_title', 'Inquiry Details')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.inquiries.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
            <i class="bi bi-chevron-left me-1"></i> Back to List
        </a>
    </div>
    
    <div class="d-flex gap-2">
        @if($inquiry->status !== 'replied')
            <form action="{{ route('admin.inquiries.status', $inquiry->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="replied">
                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold">Mark as Replied</button>
            </form>
        @endif
        @if($inquiry->status === 'new')
            <form action="{{ route('admin.inquiries.status', $inquiry->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="read">
                <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-bold">Mark as Read</button>
            </form>
        @endif
        
        <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold" onclick="return confirm('Delete this inquiry?')">Delete Inquiry</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column (Inquiry Message & Customer Details) -->
    <div class="col-lg-7">
        <div class="card card-modern border shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="text-success fw-800 text-uppercase small mb-4">Inquiry Content</h6>
            
            <div class="mb-3">
                <label class="text-muted small fw-bold">Customer Name</label>
                <div class="fw-bold fs-5 text-dark">{{ $inquiry->name }}</div>
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-bold">Contact details</label>
                <div class="fw-medium text-dark"><i class="bi bi-envelope me-2"></i>{{ $inquiry->email }}</div>
                @if($inquiry->phone)
                    <div class="fw-medium text-dark mt-1"><i class="bi bi-telephone me-2"></i>{{ $inquiry->phone }}</div>
                @endif
            </div>

            <div class="mb-3 border-top pt-3">
                <label class="text-muted small fw-bold">Subject</label>
                <div class="fw-bold text-dark fs-6">{{ $inquiry->subject }}</div>
            </div>

            <div class="mb-0">
                <label class="text-muted small fw-bold">Message Details</label>
                <div class="p-3 bg-light rounded-3 small text-dark border mt-1" style="white-space: pre-wrap; line-height: 1.6;">{{ $inquiry->message }}</div>
            </div>
        </div>
    </div>

    <!-- Right Column (Technical Analytics Attribution) -->
    <div class="col-lg-5">
        <div class="card card-modern border shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="text-primary fw-800 text-uppercase small mb-4">Technical Attribution</h6>
            
            @if($log)
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="text-muted small fw-bold d-block">Visitor Location</label>
                    <span class="fw-bold text-dark">{{ $log->city ?: 'Unknown' }}, {{ $log->country ?: '' }}</span>
                </div>
                
                <div class="col-sm-6">
                    <label class="text-muted small fw-bold d-block">Device Info</label>
                    <span class="fw-bold text-dark text-capitalize">{{ $log->device_type }} ({{ $log->os_name }})</span>
                </div>

                <div class="col-12 border-top pt-2">
                    <label class="text-muted small fw-bold d-block">Source / Attribution</label>
                    <span class="fw-bold text-primary">{{ $log->utm_source ?: 'Direct Traffic / Organic' }}</span>
                    @if($log->referrer_url)
                        <div class="text-muted x-small text-truncate mt-1">{{ $log->referrer_url }}</div>
                    @endif
                </div>

                <div class="col-12 border-top pt-2">
                    <label class="text-muted small fw-bold d-block">Browser Details</label>
                    <span class="small text-dark">{{ $log->browser_name }} on {{ $log->os_name }}</span>
                    <div class="text-muted x-small text-truncate mt-1">{{ $log->user_agent }}</div>
                </div>

                <div class="col-12 border-top pt-2">
                    <label class="text-muted small fw-bold d-block">IP Address</label>
                    <span class="fw-medium small text-dark">{{ $log->client_ip }}</span>
                </div>
            </div>
            @else
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-geo-alt fs-1 d-block mb-3 text-muted"></i>
                <p class="mb-0 text-muted">No detailed analytics metadata available for this inquiry.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
