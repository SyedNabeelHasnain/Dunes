@extends('layouts.admin')

@section('page_title', 'Simulator Run #'.$run->id.' Waterfall')

@section('content')
<div class="space-y-6 mb-8">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">{{ session('error') }}</div>
    @endif

    @php
        $statusBadge = match($run->status) {
            'passed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'partial' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    @endphp

    <!-- Run Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.qa-simulator.index') }}" class="text-xs font-bold text-slate-500 hover:text-primary">&larr; All Simulator Runs</a>
                <span class="text-slate-300">/</span>
                <h1 class="text-xl font-extrabold text-slate-900">Run #{{ $run->id }} Waterfall</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase border {{ $statusBadge }}">
                    {{ $run->status }}
                </span>
                @if($run->alert_sent)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Alert Sent</span>
                @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">{{ $run->summary }}</p>
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 mt-2">
                <span><strong>Trigger:</strong> {{ ucfirst($run->trigger_type) }}</span>
                <span><strong>Locale:</strong> {{ strtoupper($run->locale) }}</span>
                <span><strong>Device:</strong> {{ ucfirst($run->device_profile) }}</span>
                <span><strong>Mail Mode:</strong> {{ $run->mail_mode }}</span>
                <span><strong>Total Latency:</strong> {{ number_format($run->duration_ms) }} ms</span>
                <span><strong>Cleanup:</strong> {{ $run->cleanup_completed ? 'Purged Cleanly' : ($run->hasUncleanedArtifacts() ? 'Artifacts Retained' : 'N/A') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.qa-simulator.export', $run->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold">
                <i class="bi bi-download"></i> Export JSON
            </a>
            @if($run->hasUncleanedArtifacts())
                <form action="{{ route('admin.qa-simulator.cleanup', $run->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold cursor-pointer">
                        <i class="bi bi-stars"></i> Cleanup Artifacts Now
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.qa-simulator.destroy', $run->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this simulator run and any remaining artifacts?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-bold cursor-pointer">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Live Dummy Test Records Created by This Run -->
    @if($createdBookings->isNotEmpty() || $createdWhatsapps->isNotEmpty() || $createdContacts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-xs space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-database-check text-emerald-600"></i> Live Dummy Test Records Persisted in CMS
                    </h2>
                    <p class="text-[11px] text-slate-500">These real database records use clearly identifiable Dummy/Test names so you can inspect them directly in Bookings, WhatsApp Leads, and Contact Inquiries.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                @foreach($createdBookings as $b)
                    <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3.5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-amber-900">Booking #{{ $b->reference }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">{{ $b->status }}</span>
                        </div>
                        <div class="text-slate-700 font-semibold">{{ $b->name }} · {{ $b->email }}</div>
                        <div class="text-slate-600">Total: {{ $b->currency }} {{ number_format((float) $b->total, 2) }}</div>
                        <a href="{{ route('admin.bookings.show', $b->id) }}" class="inline-flex items-center gap-1 font-bold text-primary hover:underline pt-1">
                            Open Test Booking &rarr;
                        </a>
                    </div>
                @endforeach

                @foreach($createdWhatsapps as $wa)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3.5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-emerald-900">WhatsApp #{{ $wa->ref_code }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Lead #{{ $wa->id }}</span>
                        </div>
                        <div class="text-slate-700 font-semibold">{{ $wa->name }} · {{ $wa->phone }}</div>
                        <div class="text-slate-600 truncate">{{ $wa->tour_name }}</div>
                        <a href="{{ route('admin.whatsapp.leads') }}" class="inline-flex items-center gap-1 font-bold text-primary hover:underline pt-1">
                            Open WhatsApp Leads &rarr;
                        </a>
                    </div>
                @endforeach

                @foreach($createdContacts as $c)
                    <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-3.5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sky-900">Contact #{{ $c->id }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-sky-100 text-sky-800">{{ $c->status }}</span>
                        </div>
                        <div class="text-slate-700 font-semibold">{{ $c->name }} · {{ $c->email }}</div>
                        <div class="text-slate-600 truncate">{{ $c->subject }}</div>
                        <a href="{{ route('admin.inquiries.show', $c->id) }}" class="inline-flex items-center gap-1 font-bold text-primary hover:underline pt-1">
                            Open Contact Inquiry &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($run->cleanup_completed)
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-xs text-slate-600 flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600"></i>
            All synthetic dummy records for Run #{{ $run->id }} have been cleanly purged from the database and voucher storage.
        </div>
    @endif

    <!-- Step-by-Step Waterfall Grouped by Flow -->
    @foreach((array) $run->flows_requested as $flow)
        @php
            $steps = $stepsByFlow->get($flow, collect());
            $flowFailed = $steps->contains('status', 'failed');
            $flowTitle = match($flow) {
                'booking' => 'Booking Checkout End-to-End Funnel',
                'whatsapp' => 'WhatsApp Lead Capture & Gateway Funnel',
                'contact' => 'Contact Us Form, PoW, OTP & Anti-Spam Funnel',
                default => ucfirst($flow),
            };
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full {{ $flowFailed ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                    <h2 class="text-sm font-extrabold text-slate-900">{{ $flowTitle }}</h2>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $flowFailed ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $steps->where('status', 'passed')->count() }}/{{ $steps->count() }} Steps Passed
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold">
                        <tr>
                            <th class="px-4 py-3 text-left w-12">#</th>
                            <th class="px-4 py-3 text-left">Step</th>
                            <th class="px-4 py-3 text-left">Endpoint</th>
                            <th class="px-4 py-3 text-left">HTTP</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Latency</th>
                            <th class="px-4 py-3 text-left">Assertion / Diagnostics</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($steps as $step)
                            @php
                                $stepCls = match($step->status) {
                                    'passed' => 'bg-emerald-100 text-emerald-800',
                                    'skipped' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-rose-100 text-rose-800',
                                };
                            @endphp
                            <tr class="{{ $step->status === 'failed' ? 'bg-rose-50/40' : '' }}">
                                <td class="px-4 py-3 font-mono text-slate-500">{{ $step->step_order }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $step->title }}</td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-600">
                                    @if($step->http_method)
                                        <span class="font-bold text-slate-800">{{ $step->http_method }}</span>
                                    @endif
                                    {{ $step->endpoint ?? '—' }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold">{{ $step->http_status ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $stepCls }}">{{ $step->status }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700">{{ $step->duration_ms }} ms</td>
                                <td class="px-4 py-3 space-y-1">
                                    @if($step->assertion_summary)
                                        <div class="text-slate-700">{{ $step->assertion_summary }}</div>
                                    @endif
                                    @if($step->error_message)
                                        <div class="rounded-lg bg-rose-100/80 border border-rose-200 px-2.5 py-1.5 font-mono text-[11px] text-rose-900">
                                            {{ $step->error_message }}
                                        </div>
                                    @endif
                                    @if(!empty($step->metadata['emails_delivered']))
                                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 text-[11px] text-emerald-900">
                                            <strong>Live Emails Delivered ({{ count($step->metadata['emails_delivered']) }}):</strong>
                                            @foreach($step->metadata['emails_delivered'] as $em)
                                                <div>
                                                    • To: <span class="font-mono">{{ implode(', ', (array) ($em['to'] ?? [])) }}</span> — {{ $em['subject'] ?? '' }}
                                                    @if(!empty($em['attachments']))
                                                        <span class="ml-1 px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">+{{ count($em['attachments']) }} attachment(s)</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($step->metadata['emails_rendered']))
                                        <div class="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1.5 text-[11px] text-slate-600">
                                            <strong>Rendered Mailables ({{ count($step->metadata['emails_rendered']) }}):</strong>
                                            @foreach($step->metadata['emails_rendered'] as $em)
                                                <div>• To: <span class="font-mono">{{ implode(', ', (array) ($em['to'] ?? [])) }}</span> — {{ $em['subject'] ?? '' }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($step->metadata['smtp_diagnosis']))
                                        <div class="rounded-lg bg-rose-50 border border-rose-200 px-2.5 py-1.5 text-[11px] text-rose-900">
                                            <strong>SMTP Diagnosis:</strong> {{ $step->metadata['smtp_diagnosis'] }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-6 text-center text-slate-500">No steps recorded for this flow.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
