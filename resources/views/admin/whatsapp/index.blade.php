@extends('layouts.admin')

@section('page_title', 'WhatsApp Leads')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-800 text-dark mb-0">WhatsApp Click Leads</h2>
    <a href="{{ route('admin.whatsapp.settings') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
        <i class="bi bi-gear-fill me-1 text-primary"></i> WhatsApp Settings
    </a>
</div>

<div class="card card-modern border shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="whatsappLeadsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Customer</th>
                        <th>Interest Context</th>
                        <th>Message Snippet</th>
                        <th>Location</th>
                        <th>Device</th>
                        <th class="pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="ps-4">
                            <div class="small fw-medium text-muted">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('M j, g:ia') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $lead->name }}</div>
                            <div class="text-success small fw-bold" style="font-size: 0.75rem;">
                                {{ $lead->phone }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border rounded-pill fw-bold small">
                                {{ $lead->tour_name ?: 'General Inquiry' }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ Str::limit($lead->message_text, 70) }}
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">
                                {{ $lead->city ?: 'Unknown' }}, {{ $lead->country ?: '' }}
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted text-capitalize">
                                {{ $lead->device_type ?: '-' }} ({{ $lead->os_name ?: '-' }})
                            </div>
                        </td>
                        <td class="pe-4">
                            <button class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center view-lead-btn" 
                                    style="width: 32px; height: 32px;" 
                                    title="View Full Lead Details"
                                    data-name="{{ $lead->name }}"
                                    data-phone="{{ $lead->phone }}"
                                    data-context="{{ $lead->tour_name ?: 'General Inquiry' }}"
                                    data-url="{{ $lead->page_url }}"
                                    data-msg="{{ $lead->message_text }}"
                                    data-ip="{{ $lead->ip_address }}"
                                    data-location="{{ ($lead->city ?? 'Unknown') . ', ' . ($lead->country ?? '') }}"
                                    data-device="{{ ucfirst($lead->device_type ?? '-') }} ({{ $lead->os_name ?? '-' }} / {{ $lead->browser_name ?? '-' }})">
                                <i class="bi bi-search"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No WhatsApp leads found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $leads->links() }}
</div>

<!-- View Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" aria-labelledby="leadDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0 bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-dark" id="leadDetailsModalLabel">WhatsApp Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Client Details -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100 border">
                            <h6 class="text-success fw-800 text-uppercase small mb-3">Lead Information</h6>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Customer Name</label>
                                <span class="fw-bold text-dark fs-5" id="modalName"></span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold d-block">Phone Number</label>
                                <a href="" id="modalWhatsAppLink" target="_blank" class="fw-bold text-success text-decoration-none fs-5 d-flex align-items-center gap-2">
                                    <span id="modalPhone"></span> <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                            <div class="mb-0">
                                <label class="text-muted small fw-bold d-block">Inquiry Context</label>
                                <span class="fw-semibold text-dark" id="modalContext"></span>
                                <div class="mt-1">
                                    <a href="" id="modalSourceUrl" target="_blank" class="small text-primary text-decoration-none">
                                        View Source Page <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tech attribution -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100 border">
                            <h6 class="text-primary fw-800 text-uppercase small mb-3">Technical Attribution</h6>
                            <div class="mb-2">
                                <label class="text-muted small fw-bold d-block">Visitor Location</label>
                                <span class="fw-bold text-dark" id="modalLocation"></span>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small fw-bold d-block">Device & OS</label>
                                <span class="fw-bold text-dark" id="modalDevice"></span>
                            </div>
                            <div class="mb-0">
                                <label class="text-muted small fw-bold d-block">IP Address</label>
                                <span class="fw-medium small text-dark" id="modalIp"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Full Message text -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="text-muted small fw-bold d-block mb-2">Lead Message</label>
                            <div class="p-3 bg-white border rounded-3 small text-dark" id="modalMessage" style="white-space: pre-wrap; line-height: 1.6;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-lead-btn').on('click', function() {
        const name = $(this).data('name');
        const phone = $(this).data('phone');
        const context = $(this).data('context');
        const url = $(this).data('url');
        const msg = $(this).data('msg');
        const ip = $(this).data('ip');
        const loc = $(this).data('location');
        const dev = $(this).data('device');

        $('#modalName').text(name);
        $('#modalPhone').text(phone);
        $('#modalContext').text(context);
        $('#modalLocation').text(loc);
        $('#modalDevice').text(dev);
        $('#modalIp').text(ip);
        $('#modalMessage').text(msg);
        
        // Setup links
        $('#modalSourceUrl').attr('href', url);
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        $('#modalWhatsAppLink').attr('href', `https://wa.me/${cleanPhone}`);

        // Open modal
        const myModal = new bootstrap.Modal(document.getElementById('leadDetailsModal'));
        myModal.show();
    });
});
</script>
@endpush
@endsection
