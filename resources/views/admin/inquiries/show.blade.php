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
        
        <button type="button" 
                class="btn btn-danger rounded-pill px-4 py-2 fw-bold" 
                id="btnDeleteInquiryShow"
                data-id="{{ $inquiry->id }}"
                data-name="{{ $inquiry->name }}"
                data-email="{{ $inquiry->email }}"
                data-subject="{{ $inquiry->subject }}">
            <i class="bi bi-trash3-fill me-1"></i> Delete Inquiry
        </button>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnDeleteInquiryShow').on('click', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'Customer';
        const email = $(this).data('email') || '';
        const subject = $(this).data('subject') || '';
        const verifyTarget = (email && email.trim()) ? email.trim() : (name ? name.trim() : 'CONFIRM');

        // Step 1: Caution Dialog
        Swal.fire({
            title: '⚠️ CAUTION: Permanent Inquiry Deletion',
            html: `
                <div class="text-start small text-secondary">
                    <div class="alert alert-danger py-2 px-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate contact inquiry <strong class="text-dark">#${id} (${name})</strong> from the database.
                    </div>
                    <div class="card bg-light border-0 p-2.5 mb-3">
                        <div class="fw-bold text-dark mb-1 small text-uppercase" style="font-size: 11px;">The following records will be permanently purged:</div>
                        <ul class="mb-0 ps-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                            <li>Contact Inquiry Record (${email || 'No Email'})</li>
                            <li>Customer message content & subject ("${subject || 'Inquiry'}")</li>
                            <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                            <li>Client IP & Geolocation Audit Trail</li>
                        </ul>
                    </div>
                    <p class="mb-0 text-muted">Are you sure you want to proceed to the final verification?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
            cancelButtonText: 'Cancel (Keep Inquiry)',
            focusCancel: true
        }).then((step1Result) => {
            if (!step1Result.isConfirmed) return;

            // Step 2: Final Safeguard Double Confirmation
            Swal.fire({
                title: '🔒 Double Confirmation Required',
                html: `
                    <div class="text-start small">
                        <p class="text-dark mb-2">To prevent accidental deletion, please type the customer email below to authorize permanent destruction:</p>
                        <div class="text-center my-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-6 font-monospace py-2 px-3">
                                ${verifyTarget}
                            </span>
                        </div>
                    </div>
                `,
                input: 'text',
                inputPlaceholder: `Type ${verifyTarget} to confirm`,
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off',
                    autocomplete: 'off',
                    style: 'text-align: center; font-family: monospace; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px;'
                },
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#b02a37',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> PERMANENTLY PURGE EVERYTHING',
                cancelButtonText: 'Abort',
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: (inputValue) => {
                    if (!inputValue || inputValue.trim().toLowerCase() !== verifyTarget.toLowerCase()) {
                        Swal.showValidationMessage(`Verification mismatch! You must type exactly "${verifyTarget}" to authorize deletion.`);
                        return false;
                    }
                    return $.ajax({
                        url: `/admin/inquiries/${id}`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        headers: { 'Accept': 'application/json' }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Failed to delete inquiry.');
                        }
                        return response;
                    }).catch(error => {
                        const msg = error.responseJSON ? error.responseJSON.message : error.message;
                        Swal.showValidationMessage(`Purge Failed: ${msg}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((step2Result) => {
                if (step2Result.isConfirmed && step2Result.value) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Purged!',
                        text: step2Result.value.message || 'Inquiry and all analytics footprints permanently deleted.',
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('admin.inquiries.index') }}";
                    });
                }
            });
        });
    });
});
</script>
@endpush
