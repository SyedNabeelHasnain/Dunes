@extends('layouts.admin')

@section('page_title', 'Inquiry Details')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.inquiries.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-2xs hover:bg-slate-50 transition-all mb-2">
                <i class="bi bi-chevron-left text-xs"></i> Back to List
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Inquiry #{{ $inquiry->id }}</h1>
                @php
                    $statusBadge = match($inquiry->status) {
                        'new' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'read' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'replied' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $statusBadge }}">
                    {{ $inquiry->status === 'new' ? 'Action Needed' : ($inquiry->status === 'read' ? 'In Review' : 'Replied') }}
                </span>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            @if($inquiry->status !== 'replied')
                <form action="{{ route('admin.inquiries.status', $inquiry->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="replied">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-check-lg"></i> Mark as Replied
                    </button>
                </form>
            @endif
            @if($inquiry->status === 'new')
                <form action="{{ route('admin.inquiries.status', $inquiry->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="read">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition-all">
                        <i class="bi bi-eye"></i> Mark as Read
                    </button>
                </form>
            @endif
            
            <button type="button" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold shadow-2xs transition-all" 
                    id="btnDeleteInquiryShow"
                    data-id="{{ $inquiry->id }}"
                    data-name="{{ $inquiry->name }}"
                    data-email="{{ $inquiry->email }}"
                    data-subject="{{ $inquiry->subject }}">
                <i class="bi bi-trash3-fill"></i> Delete Inquiry
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Inquiry Message & Customer Details -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div class="space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-chat-left-text text-base"></i> Inquiry Content
                    </h2>
                    <span class="text-xs text-slate-400">Received {{ $inquiry->created_at ? $inquiry->created_at->format('M j, Y - H:i') : '' }}</span>
                </div>
                
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer Name</div>
                    <div class="text-lg font-black text-slate-900">{{ $inquiry->name }}</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Address</div>
                        <a href="mailto:{{ $inquiry->email }}" class="text-sm font-semibold text-slate-800 hover:text-primary transition flex items-center gap-1.5">
                            <i class="bi bi-envelope text-slate-400"></i> {{ $inquiry->email }}
                        </a>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Phone Number</div>
                        @if($inquiry->phone)
                            <div class="flex items-center gap-2">
                                <a href="tel:{{ $inquiry->phone }}" class="text-sm font-semibold text-slate-800 hover:text-primary transition flex items-center gap-1.5">
                                    <i class="bi bi-telephone text-slate-400"></i> {{ $inquiry->phone }}
                                </a>
                                @php
                                    $waInq = preg_replace('/[^0-9]/', '', $inquiry->phone);
                                @endphp
                                <a href="https://wa.me/{{ $waInq }}" target="_blank" rel="noopener" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold" title="Open WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        @else
                            <span class="text-sm text-slate-400 italic">Not provided</span>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Subject</div>
                    <div class="text-base font-bold text-slate-900">{{ $inquiry->subject }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Message Body</div>
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                        {{ $inquiry->message }}
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-6 flex items-center justify-between text-xs text-slate-400">
                <span>Inquiry ID: #{{ $inquiry->id }}</span>
                <a href="mailto:{{ $inquiry->email }}?subject=Re: {{ urlencode($inquiry->subject) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                    <i class="bi bi-reply-fill"></i> Reply by Email
                </a>
            </div>
        </div>

        <!-- Technical Attribution -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h2 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="bi bi-cpu text-base"></i> Technical Attribution
                    </h2>
                    <span class="text-xs text-slate-400">Visitor Telemetry</span>
                </div>
                
                @if($log)
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Visitor Location</div>
                            <span class="text-sm font-bold text-slate-900">{{ $log->city ?: 'Unknown' }}, {{ $log->country ?: '' }}</span>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Device Info</div>
                            <span class="text-sm font-bold text-slate-900 capitalize">{{ $log->device_type }} ({{ $log->os_name }})</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Source / Attribution</div>
                        <span class="text-sm font-bold text-primary">{{ $log->utm_source ?: 'Direct Traffic / Organic' }}</span>
                        @if($log->referrer_url)
                            <div class="text-xs text-slate-400 truncate mt-1" title="{{ $log->referrer_url }}">{{ $log->referrer_url }}</div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Browser Details</div>
                        <span class="text-sm text-slate-800">{{ $log->browser_name }} on {{ $log->os_name }}</span>
                        <div class="text-[11px] text-slate-400 truncate mt-1" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">IP Address</div>
                        <span class="font-mono text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 px-2 py-1 rounded-md">{{ $log->client_ip }}</span>
                    </div>
                </div>
                @else
                <div class="text-center py-12 text-slate-400">
                    <i class="bi bi-geo-alt text-3xl block mb-2 opacity-40"></i>
                    <p class="text-xs">No detailed analytics metadata available for this inquiry.</p>
                </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 mt-6 text-xs text-slate-400 flex items-center justify-between">
                <span>Created: {{ $inquiry->created_at ? $inquiry->created_at->diffForHumans() : 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

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
            title: 'CAUTION: Permanent Inquiry Deletion',
            html: `
                <div class="text-left text-xs text-slate-600">
                    <div class="p-3 mb-3 border border-rose-200 bg-rose-50 text-rose-700 font-semibold rounded-xl">
                        <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                        <strong>IRREVERSIBLE ACTION:</strong> You are about to permanently eradicate contact inquiry <strong class="text-slate-900">#${id} (${name})</strong> from the database.
                    </div>
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3">
                        <div class="font-bold text-slate-800 mb-1 text-[11px] uppercase">The following records will be permanently purged:</div>
                        <ul class="list-disc pl-4 text-slate-500 space-y-0.5 text-[11px]">
                            <li>Contact Inquiry Record (${email || 'No Email'})</li>
                            <li>Customer message content & subject ("${subject || 'Inquiry'}")</li>
                            <li>Visitor Analytics, Telemetry & Request Logs (${id})</li>
                            <li>Client IP & Geolocation Audit Trail</li>
                        </ul>
                    </div>
                    <p class="mb-0 text-slate-500">Are you sure you want to proceed to the final verification?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Proceed to Final Confirmation <i class="bi bi-arrow-right ms-1"></i>',
            cancelButtonText: 'Cancel (Keep Inquiry)',
            focusCancel: true
        }).then((step1Result) => {
            if (!step1Result.isConfirmed) return;

            // Step 2: Final Safeguard Double Confirmation
            Swal.fire({
                title: 'Double Confirmation Required',
                html: `
                    <div class="text-left text-xs">
                        <p class="text-slate-800 mb-2">To prevent accidental deletion, please type the customer email below to authorize permanent destruction:</p>
                        <div class="text-center my-3">
                            <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-mono font-bold text-sm">
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
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#64748b',
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
@endsection
