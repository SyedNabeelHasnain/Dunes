@extends('layouts.admin')

@section('page_title', 'Automated Visitor Journey Simulator & E2E QA')

@section('content')
<div class="space-y-6 mb-8" x-data="{
    frequency: @js(old('frequency', $settings['frequency'])),
    mailMode: @js(old('mail_mode', $settings['mail_mode'])),
    runMailMode: @js($settings['mail_mode'])
}">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs font-bold text-rose-800">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Hero Header & One-Click Quick Run -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-cyan-600 to-blue-700 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Visitor Journey Simulator &amp; E2E QA</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $settings['enabled'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ $settings['enabled'] ? 'Auto-Schedule Active' : 'Manual Mode Only' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Executes 100% real visitor operations across Booking Checkout, WhatsApp Leads, and Contact Inquiries using clearly identifiable dummy test identities (<code class="font-mono">Dunes Test Traveler</code>, <code class="font-mono">Dunes Test Lead</code>, <code class="font-mono">Dunes Test Visitor</code>) and fires real live notifications.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.qa-simulator.run') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="flows[]" value="booking">
                <input type="hidden" name="flows[]" value="whatsapp">
                <input type="hidden" name="flows[]" value="contact">
                <input type="hidden" name="locale" value="{{ $settings['locale'] }}">
                <input type="hidden" name="device_profile" value="{{ $settings['device_profile'] }}">
                <input type="hidden" name="mail_mode" value="live_smtp">
                <input type="hidden" name="recipient_email" value="{{ $defaultTestEmail }}">
                <input type="hidden" name="include_bot_shield_check" value="1">
                <input type="hidden" name="include_coupon_check" value="1">
                <input type="hidden" name="auto_cleanup" value="0">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold transition shadow-sm cursor-pointer">
                    <i class="bi bi-play-circle-fill"></i> Run All 3 Flows Live (Real Records &amp; Alerts)
                </button>
            </form>
        </div>
    </div>

    @if(($smtpHealth['status'] ?? '') === 'unhealthy' && !empty($smtpHealth['diagnosis']))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3.5 text-xs text-rose-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <div class="font-extrabold flex items-center gap-1.5">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                    Live SMTP Health Warning: {{ $smtpHealth['diagnosis']['title'] ?? 'Outbound Mail Failure Detected' }}
                </div>
                <div class="text-[11px] text-rose-800 mt-0.5">{{ $smtpHealth['diagnosis']['raw_error'] ?? '' }}</div>
            </div>
            <a href="{{ route('admin.settings.mail') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-bold text-[11px]">
                Fix Mail / Failover SMTP &rarr;
            </a>
        </div>
    @endif

    <!-- KPI & Per-Flow Health Strip -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">30-Day Pass Rate</div>
            <div class="text-2xl font-black text-slate-900 mt-1">
                {{ $passRate30d !== null ? $passRate30d.'%' : '—' }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">{{ $passedRecent }}/{{ $totalRecent }} runs passed</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Next Scheduled Run</div>
            <div class="text-sm font-black text-slate-900 mt-1.5">
                @if($nextScheduledAt)
                    {{ $nextScheduledAt->format('M d, H:i') }} <span class="text-[10px] font-bold text-cyan-700">GST</span>
                @else
                    Disabled
                @endif
            </div>
            <div class="text-[11px] text-slate-500 mt-1 font-mono">{{ $settings['enabled'] ? $cronExpression : 'Enable below to automate' }}</div>
        </div>

        @foreach(['booking' => ['label' => 'Booking Funnel', 'icon' => 'bi-calendar2-check-fill text-amber-500'], 'whatsapp' => ['label' => 'WhatsApp Lead', 'icon' => 'bi-whatsapp text-emerald-500'], 'contact' => ['label' => 'Contact + Shield', 'icon' => 'bi-envelope-check-fill text-sky-500']] as $fKey => $fMeta)
            @php
                $fs = $flowStatuses[$fKey] ?? ['status' => 'never'];
                $badgeCls = $fs['status'] === 'passed'
                    ? 'bg-emerald-100 text-emerald-800'
                    : ($fs['status'] === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600');
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                        <i class="bi {{ $fMeta['icon'] }}"></i> {{ $fMeta['label'] }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $badgeCls }}">
                        {{ $fs['status'] }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 mt-2">
                    @if(!empty($fs['run_id']))
                        <a href="{{ route('admin.qa-simulator.show', $fs['run_id']) }}" class="font-bold text-primary hover:underline">Run #{{ $fs['run_id'] }}</a>
                        · {{ $fs['steps_passed'] }}/{{ $fs['steps_total'] }} steps · {{ $fs['duration_ms'] }} ms
                    @else
                        No runs executed yet
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Custom Manual Simulator + Automated Schedule Config -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Custom Manual E2E Simulator Runner -->
        <div class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Custom Manual E2E Simulation</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Choose flows, device, locale, mail handling &amp; cleanup behavior.</p>
                </div>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">Isolated Sandbox</span>
            </div>

            <form action="{{ route('admin.qa-simulator.run') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Flows to Simulate</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['booking' => 'Booking', 'whatsapp' => 'WhatsApp', 'contact' => 'Contact Us'] as $val => $lbl)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="flows[]" value="{{ $val }}" checked class="rounded border-slate-300 text-primary">
                                <span class="font-bold text-slate-800">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sim_locale" class="block font-bold text-slate-700 mb-1">Storefront Locale</label>
                        <select id="sim_locale" name="locale" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}" {{ $settings['locale'] === $lang->code ? 'selected' : '' }}>
                                    {{ strtoupper($lang->code) }} — {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sim_device" class="block font-bold text-slate-700 mb-1">Visitor Device Profile</label>
                        <select id="sim_device" name="device_profile" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="desktop" {{ $settings['device_profile'] === 'desktop' ? 'selected' : '' }}>Desktop (Chrome)</option>
                            <option value="mobile" {{ $settings['device_profile'] === 'mobile' ? 'selected' : '' }}>Mobile (iPhone Safari)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="sim_tour" class="block font-bold text-slate-700 mb-1">Target Tour (Booking Flow)</label>
                    <select id="sim_tour" name="tour_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        <option value="">Auto-Select Active Flagship Tour</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}" {{ (int) $settings['tour_id'] === (int) $tour->id ? 'selected' : '' }}>
                                {{ $tour->name }} (/{{ $tour->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sim_mail_mode" class="block font-bold text-slate-700 mb-1">Execution &amp; Notification Mode</label>
                    <select id="sim_mail_mode" name="mail_mode" x-model="runMailMode" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        <option value="live_smtp">Real Live Mode — Creates Real Records &amp; Triggers Live SMTP + Webhooks (Default)</option>
                        <option value="intercept">Safe Dry-Run / Intercept — No Real SMTP or Webhooks Sent</option>
                    </select>
                </div>

                <div x-show="runMailMode === 'live_smtp'" x-cloak>
                    <label for="sim_recipient" class="block font-bold text-slate-700 mb-1">Dummy Customer Test Email (Receives Customer OTP, Booking Voucher &amp; Ack)</label>
                    <input id="sim_recipient" type="email" name="recipient_email" value="{{ $settings['recipient_email'] ?: $defaultTestEmail }}" placeholder="{{ $defaultTestEmail }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>

                <div class="space-y-2 rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="include_bot_shield_check" value="1" {{ $settings['include_bot_shield_check'] ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Probe Anti-Spam Bot Shield (verify bot rejection without IP ban)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="include_coupon_check" value="1" {{ $settings['include_coupon_check'] ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Verify Promo Coupon API Validation Endpoint</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_cleanup" value="1" {{ $settings['auto_cleanup'] ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Auto-delete dummy records right after run (leave unchecked to inspect Booking, WhatsApp Lead &amp; Contact Inquiry in CMS)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition cursor-pointer">
                    <i class="bi bi-play-fill"></i> Launch Custom E2E Run
                </button>
            </form>
        </div>

        <!-- Right: Automated Schedule & Alerting Settings -->
        <div class="lg:col-span-7 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Automated Scheduled Testing (Dubai Time · GST)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Configure daily, weekly, interval, or custom cron execution inside Laravel's existing scheduler.</p>
            </div>

            <form action="{{ route('admin.qa-simulator.settings.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
                    <div>
                        <span class="font-extrabold text-slate-900 block">Enable Automated Scheduled Runs</span>
                        <span class="text-[11px] text-slate-500">Runs automatically via <code class="font-mono">qa:run-synthetic-tests --scheduled</code></span>
                    </div>
                    <input type="checkbox" name="enabled" value="1" {{ old('enabled', $settings['enabled']) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-primary">
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="sched_freq" class="block font-bold text-slate-700 mb-1">Cadence / Frequency</label>
                        <select id="sched_freq" name="frequency" x-model="frequency" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="hourly">Hourly</option>
                            <option value="every_6_hours">Every 6 Hours</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="custom_cron">Custom 5-Field Cron</option>
                        </select>
                    </div>

                    <div x-show="frequency !== 'custom_cron'">
                        <label for="sched_time" class="block font-bold text-slate-700 mb-1">Run Time (Dubai HH:MM)</label>
                        <input id="sched_time" type="time" name="time" value="{{ old('time', $settings['time']) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>

                    <div x-show="frequency === 'weekly'" x-cloak>
                        <label for="sched_weekday" class="block font-bold text-slate-700 mb-1">Day of Week</label>
                        <select id="sched_weekday" name="weekday" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            @foreach([0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'] as $dNum => $dName)
                                <option value="{{ $dNum }}" {{ (int) old('weekday', $settings['weekday']) === $dNum ? 'selected' : '' }}>{{ $dName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="frequency === 'custom_cron'" x-cloak class="sm:col-span-2">
                        <label for="sched_custom_cron" class="block font-bold text-slate-700 mb-1">Custom Cron Expression (GST)</label>
                        <input id="sched_custom_cron" type="text" name="custom_cron" value="{{ old('custom_cron', $settings['custom_cron']) }}" placeholder="0 6 * * *" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Scheduled Flows</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['booking' => 'Booking', 'whatsapp' => 'WhatsApp', 'contact' => 'Contact Us'] as $val => $lbl)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="flows[]" value="{{ $val }}" {{ in_array($val, (array) old('flows', $settings['flows']), true) ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                                <span class="font-bold text-slate-800">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="sched_locale" class="block font-bold text-slate-700 mb-1">Default Locale</label>
                        <select id="sched_locale" name="locale" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}" {{ old('locale', $settings['locale']) === $lang->code ? 'selected' : '' }}>
                                    {{ strtoupper($lang->code) }} — {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sched_device" class="block font-bold text-slate-700 mb-1">Default Device</label>
                        <select id="sched_device" name="device_profile" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="desktop" {{ old('device_profile', $settings['device_profile']) === 'desktop' ? 'selected' : '' }}>Desktop</option>
                            <option value="mobile" {{ old('device_profile', $settings['device_profile']) === 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                    <div>
                        <label for="sched_retention" class="block font-bold text-slate-700 mb-1">Run Log Retention (Days)</label>
                        <input id="sched_retention" type="number" min="7" max="365" name="retention_days" value="{{ old('retention_days', $settings['retention_days']) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="sched_mail_mode" class="block font-bold text-slate-700 mb-1">Scheduled Execution Mode</label>
                        <select id="sched_mail_mode" name="mail_mode" x-model="mailMode" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="live_smtp">Real Live Mode — Real Records &amp; Live SMTP + Webhooks</option>
                            <option value="intercept">Safe Dry-Run / Intercept</option>
                        </select>
                    </div>
                    <div>
                        <label for="sched_tour" class="block font-bold text-slate-700 mb-1">Scheduled Target Tour</label>
                        <select id="sched_tour" name="tour_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="">Auto-Select Active Flagship Tour</option>
                            @foreach($tours as $tour)
                                <option value="{{ $tour->id }}" {{ (int) old('tour_id', $settings['tour_id']) === (int) $tour->id ? 'selected' : '' }}>
                                    {{ $tour->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div x-show="mailMode === 'live_smtp'" x-cloak>
                    <label for="sched_recipient" class="block font-bold text-slate-700 mb-1">Dummy Customer Test Email (Receives Customer OTP, Booking Voucher &amp; Ack)</label>
                    <input id="sched_recipient" type="email" name="recipient_email" value="{{ old('recipient_email', $settings['recipient_email'] ?: $defaultTestEmail) }}" placeholder="{{ $defaultTestEmail }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>

                <div>
                    <label for="sched_alerts" class="block font-bold text-slate-700 mb-1">Failure Alert Recipients (Comma-separated)</label>
                    <input id="sched_alerts" type="text" name="alert_recipients" value="{{ old('alert_recipients', $settings['alert_recipients']) }}" placeholder="Defaults to Primary Admin Email" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="include_bot_shield_check" value="1" {{ old('include_bot_shield_check', $settings['include_bot_shield_check']) ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Include Bot Shield Probe</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="include_coupon_check" value="1" {{ old('include_coupon_check', $settings['include_coupon_check']) ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Include Coupon Validation</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_cleanup" value="1" {{ old('auto_cleanup', $settings['auto_cleanup']) ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Auto-Cleanup Test Artifacts</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="alert_on_failure" value="1" {{ old('alert_on_failure', $settings['alert_on_failure']) ? 'checked' : '' }} class="rounded border-slate-300 text-primary">
                        <span class="font-semibold text-slate-700">Email &amp; Webhook Alert on Failure</span>
                    </label>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold transition shadow-xs cursor-pointer">
                        <i class="bi bi-save2"></i> Save Schedule &amp; QA Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Run History Table -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Synthetic E2E Test Run History</h2>
                <p class="text-xs text-slate-500">Full audit trail of manual, CLI, and scheduled visitor simulations.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($uncleanedRunsCount > 0)
                    <form action="{{ route('admin.qa-simulator.cleanup-all') }}" method="POST" class="inline" onsubmit="return confirm('Delete all kept dummy test records across {{ $uncleanedRunsCount }} run(s)? Real customer data will never be touched.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold cursor-pointer">
                            <i class="bi bi-stars"></i> Clean Up All Dummy Records ({{ $uncleanedRunsCount }})
                        </button>
                    </form>
                @endif
                <form method="GET" action="{{ route('admin.qa-simulator.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs">
                        <option value="">All Statuses</option>
                        @foreach(['passed', 'partial', 'failed'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    <select name="trigger_type" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs">
                        <option value="">All Triggers</option>
                        @foreach(['manual', 'scheduled', 'cli'] as $tr)
                            <option value="{{ $tr }}" {{ request('trigger_type') === $tr ? 'selected' : '' }}>{{ ucfirst($tr) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold">Filter</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-4 py-3 text-left">Run</th>
                        <th class="px-4 py-3 text-left">Trigger &amp; Context</th>
                        <th class="px-4 py-3 text-left">Flows</th>
                        <th class="px-4 py-3 text-left">Result</th>
                        <th class="px-4 py-3 text-left">Cleanup</th>
                        <th class="px-4 py-3 text-left">Duration</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($runs as $run)
                        @php
                            $stBadge = match($run->status) {
                                'passed' => 'bg-emerald-100 text-emerald-800',
                                'partial' => 'bg-amber-100 text-amber-800',
                                'running' => 'bg-sky-100 text-sky-800',
                                default => 'bg-rose-100 text-rose-800',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-extrabold text-slate-900">
                                <a href="{{ route('admin.qa-simulator.show', $run->id) }}" class="hover:text-primary">#{{ $run->id }}</a>
                                <div class="text-[11px] font-normal text-slate-500">{{ $run->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold uppercase text-[10px]">{{ $run->trigger_type }}</span>
                                <span class="ml-1 font-semibold text-slate-700">{{ strtoupper($run->locale) }} · {{ ucfirst($run->device_profile) }}</span>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $run->triggeredByUser ? 'By '.$run->triggeredByUser->name : 'System / Cron' }} · {{ $run->mail_mode }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach((array) $run->flows_requested as $fl)
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] uppercase">{{ $fl }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $stBadge }}">{{ $run->status }}</span>
                                <div class="text-[11px] text-slate-600 mt-1">{{ $run->passed_steps }}/{{ $run->total_steps }} steps</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($run->cleanup_completed)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">Cleaned</span>
                                @elseif($run->hasUncleanedArtifacts())
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">Artifacts Kept</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-700">{{ number_format($run->duration_ms) }} ms</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.qa-simulator.show', $run->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold">
                                    Inspect
                                </a>
                                @if($run->hasUncleanedArtifacts())
                                    <form action="{{ route('admin.qa-simulator.cleanup', $run->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold cursor-pointer">
                                            Clean
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                No synthetic E2E test runs recorded yet. Click <strong>Run All 3 Flows Now</strong> above to simulate a complete visitor journey.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $runs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
