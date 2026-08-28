@extends('layouts.admin')

@section('page_title', 'WhatsApp Leads')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h4 fw-800 text-dark mb-1">WhatsApp Inquiries & Click Leads</h2>
        <p class="text-muted small mb-0">Live leads generated through website WhatsApp widgets with visitor telemetry.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.whatsapp.export') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export CSV
        </a>
        <a href="{{ route('admin.whatsapp.settings') }}" class="btn btn-light border rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-gear-fill me-1 text-primary"></i> Settings
        </a>
    </div>
</div>

<div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover datatable" id="whatsappLeadsTable">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Customer</th>
                        <th>Interest Context</th>
                        <th>Message Snippet</th>
                        <th>Location</th>
                        <th>Device</th>
                        <th class="pe-4 text-end no-sort">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="ps-4">
                            <div class="small fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('M j, Y') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                {{ \Carbon\Carbon::parse($lead->created_at)->format('g:ia') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $lead->name ?: 'Visitor' }}</div>
                            <div class="text-success small fw-bold font-monospace" style="font-size: 0.75rem;">
                                {{ $lead->phone }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold small">
                                {{ $lead->tour_name ?: 'General Inquiry' }}
                            </span>
                        </td>
                        <td class="text-muted small" style="max-width: 220px;">
                            {{ Str::limit($lead->message_text, 65) }}
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $lead->city ?: 'Unknown' }}, {{ $lead->country ?: '' }}
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted text-capitalize">
                                <span class="badge bg-light text-dark border">{{ $lead->device_type ?: 'Desktop' }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @if($lead->phone)
                                @php
                                    $waClean = preg_replace('/[^0-9]/', '', $lead->phone);
                                    $replyMsg = 'Hi ' . ($lead->name ?: 'there') . '! Thanks for contacting Dunes Discovery regarding ' . ($lead->tour_name ?: 'our tours') . '. How may we assist you today?';
                                @endphp
                                <a href="https://wa.me/{{ $waClean }}?text={{ urlencode($replyMsg) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Reply on WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                @endif
                                <button class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center view-lead-btn" 
                                        style="width: 34px; height: 34px;" 
                                        title="View Full Lead Details"
                                        data-name="{{ $lead->name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-context="{{ $lead->tour_name ?: 'General Inquiry' }}"
                                        data-url="{{ $lead->page_url }}"
                                        data-msg="{{ $lead->message_text }}"
                                        data-ip="{{ $lead->client_ip ?? $lead->ip_address ?? 'Not Available' }}"
                                        data-location="{{ ($lead->city ?? 'Unknown') . ', ' . ($lead->country ?? '') }}"
                                        data-device="{{ ucfirst($lead->device_type ?? '-') }} ({{ $lead->os_name ?? '-' }} / {{ $lead->browser_name ?? '-' }})">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No WhatsApp leads found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
