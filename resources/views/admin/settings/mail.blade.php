@extends('layouts.admin')

@section('page_title', 'SMTP & Mailer Settings')

@section('content')
<div>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h4 class="text-lg font-extrabold text-slate-900 flex items-center gap-2 leading-tight">
                <i class="bi bi-envelope-gear-fill text-primary"></i> SMTP & Mailer Configuration
            </h4>
            <div class="text-xs text-slate-500 mt-0.5">Configure dynamic email delivery, custom SMTP credentials, sender identity, and campaign throttle limits.</div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition" onclick="document.getElementById('mailSettingsForm').submit();">
                <i class="bi bi-check-lg"></i> Save Mail Settings
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs mb-6 shadow-xs">
        <i class="bi bi-check-circle-fill text-emerald-600 text-base shrink-0"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Main Settings Form Column -->
        <div class="xl:col-span-8">
            <form id="mailSettingsForm" action="{{ route('admin.settings.mail.update') }}" method="POST">
                @csrf

                <!-- SMTP Server Credentials -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-server text-primary"></i> Mail Server Connection
                        </h5>
                        <span class="rounded-full border border-primary/20 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold text-primary">
                            Runtime Dynamic
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Mail Driver <span class="text-rose-500">*</span></label>
                            <select name="smtp_driver" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_driver') border-rose-500 @enderror" required>
                                <option value="smtp" {{ ($settings['smtp_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (Recommended for Production)</option>
                                <option value="sendmail" {{ ($settings['smtp_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail (Local Server MTA)</option>
                                <option value="log" {{ ($settings['smtp_driver'] ?? '') === 'log' ? 'selected' : '' }}>Log Driver (Testing / Development)</option>
                            </select>
                            @error('smtp_driver')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">SMTP Host</label>
                            <input type="text" name="smtp_host" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_host') border-rose-500 @enderror" placeholder="smtp.hostinger.com or smtp.gmail.com" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}">
                            @error('smtp_host')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Port</label>
                            <select name="smtp_port" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_port') border-rose-500 @enderror">
                                <option value="465" {{ ($settings['smtp_port'] ?? '465') == '465' ? 'selected' : '' }}>465 (SSL / SMTPS)</option>
                                <option value="587" {{ ($settings['smtp_port'] ?? '') == '587' ? 'selected' : '' }}>587 (TLS / STARTTLS)</option>
                                <option value="25" {{ ($settings['smtp_port'] ?? '') == '25' ? 'selected' : '' }}>25 (Standard Unencrypted)</option>
                                <option value="2525" {{ ($settings['smtp_port'] ?? '') == '2525' ? 'selected' : '' }}>2525 (Alternative TLS)</option>
                            </select>
                            @error('smtp_port')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Encryption</label>
                            <select name="smtp_encryption" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_encryption') border-rose-500 @enderror">
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="none" {{ ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                            @error('smtp_encryption')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">SMTP Username / Email</label>
                            <input type="text" name="smtp_username" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_username') border-rose-500 @enderror" placeholder="info@domain.com" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}">
                            @error('smtp_username')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-12">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">SMTP Password</label>
                            <div class="relative flex rounded-xl shadow-2xs">
                                <input type="password" name="smtp_password" id="smtpPassword" class="w-full rounded-l-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="•••••••••••••• (Leave blank to keep existing)">
                                <button type="button" class="inline-flex items-center px-4 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" onclick="togglePasswordVisibility('smtpPassword')">
                                    <i class="bi bi-eye" id="smtpPasswordEye"></i>
                                </button>
                            </div>
                            <div class="mt-1 text-[11px] text-slate-500">Passwords are securely stored. Leave blank if you don't wish to change the current password.</div>
                        </div>
                    </div>
                </div>

                <!-- Sender Branding & Header Info -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <h5 class="text-sm font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="bi bi-person-badge-fill text-primary"></i> Sender Identity & Branding
                    </h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">From Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="smtp_from_address" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_from_address') border-rose-500 @enderror" placeholder="noreply@domain.com" value="{{ old('smtp_from_address', $settings['smtp_from_address'] ?? config('mail.from.address')) }}" required>
                            @error('smtp_from_address')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">From Display Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="smtp_from_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_from_name') border-rose-500 @enderror" placeholder="e.g. Dunes Discovery Tourism" value="{{ old('smtp_from_name', $settings['smtp_from_name'] ?? $settings['site_name'] ?? config('mail.from.name')) }}" required>
                            @error('smtp_from_name')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Reply-To Address</label>
                            <input type="email" name="smtp_reply_to" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary @error('smtp_reply_to') border-rose-500 @enderror" placeholder="support@domain.com" value="{{ old('smtp_reply_to', $settings['smtp_reply_to'] ?? '') }}">
                            @error('smtp_reply_to')
                                <div class="mt-1 text-[11px] text-rose-500">{{ $message }}</div>
                            @enderror
                            <div class="mt-1 text-[11px] text-slate-500">Subscribers will see this address when hitting "Reply" in their email client.</div>
                        </div>
                    </div>
                </div>

                <!-- Throttle & Newsletter Engine Settings -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <h5 class="text-sm font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="bi bi-speedometer2 text-primary"></i> Broadcast Throttle & Deliverability
                    </h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200/80 bg-slate-50/70">
                                <div>
                                    <label class="text-xs font-extrabold text-slate-900 block" for="newsletterEnabled">
                                        Enable Public Newsletter Subscription Block
                                    </label>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Display newsletter subscription form above footer across the portal.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                                    <input type="checkbox" name="newsletter_enabled" value="1" id="newsletterEnabled" {{ ($settings['newsletter_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Size (Emails per Chunk)</label>
                            <input type="number" name="newsletter_batch_size" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" min="5" max="250" value="{{ old('newsletter_batch_size', $settings['newsletter_batch_size'] ?? '50') }}" required>
                            <div class="mt-1 text-[11px] text-slate-500">Recommended: 25 - 50. Keeps batches within shared hosting burst limits.</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Batch Throttle Delay (Seconds)</label>
                            <input type="number" name="newsletter_batch_delay" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" min="0" max="10" value="{{ old('newsletter_batch_delay', $settings['newsletter_batch_delay'] ?? '1') }}" required>
                            <div class="mt-1 text-[11px] text-slate-500">Pause between chunk dispatches to prevent SMTP rate-limit bans.</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Live SMTP Connection Tester -->
        <div class="xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sticky top-24">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <div>
                        <h6 class="text-xs font-extrabold text-slate-900 leading-tight">Live SMTP Tester</h6>
                        <span class="text-[11px] text-slate-500">Verify host, port, credentials & socket connectivity</span>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    Send a live diagnostic test email to verify that your mail server credentials authenticate and can transmit messages cleanly.
                </p>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Diagnostic Recipient Email:</label>
                    <input type="email" id="testConnectionEmail" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="yourname@domain.com" value="{{ auth()->user()->email ?? '' }}">
                </div>

                <button type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition" id="btnTestConnection" onclick="runLiveSmtpTest()">
                    <i class="bi bi-send-check"></i> Test SMTP Connection
                </button>

                <div id="smtpTestResult" class="mt-4 hidden"></div>

                <hr class="my-5 border-slate-100">

                <h6 class="text-[11px] font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                    <i class="bi bi-shield-check text-emerald-600"></i> Recommended Best Practices
                </h6>
                <ul class="text-[11px] text-slate-500 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Ensure your sending domain has active <strong class="text-slate-700">SPF</strong> and <strong class="text-slate-700">DKIM</strong> DNS records.</li>
                    <li>Port <strong class="text-slate-700">465 (SSL)</strong> is typically preferred for Hostinger, cPanel, and Titan mail.</li>
                    <li>Always send a test email before initiating large promotional campaigns.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePasswordVisibility(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + 'Eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('bi-eye');
        eye.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('bi-eye-slash');
        eye.classList.add('bi-eye');
    }
}

async function runLiveSmtpTest() {
    const emailInput = document.getElementById('testConnectionEmail');
    const resultBox = document.getElementById('smtpTestResult');
    const btn = document.getElementById('btnTestConnection');

    const email = emailInput.value.trim();
    if (!email) {
        resultBox.className = 'flex items-center gap-2 p-3 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs';
        resultBox.textContent = 'Please enter an email address to receive the test.';
        resultBox.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-2"><i class="bi bi-arrow-repeat"></i></span> Testing Connection...';
    resultBox.classList.add('hidden');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const response = await fetch('{{ route("admin.settings.mail.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ test_email: email })
        });

        const data = await response.json();
        if (data.success) {
            resultBox.className = 'p-3.5 mt-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs shadow-xs';
            resultBox.innerHTML = `<strong><i class="bi bi-check-circle-fill mr-1 text-emerald-600"></i> Success!</strong> ${data.message}`;
        } else {
            resultBox.className = 'p-3.5 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs shadow-xs';
            resultBox.innerHTML = `<strong><i class="bi bi-exclamation-triangle-fill mr-1 text-rose-600"></i> Connection Failed:</strong><br>${data.message}`;
        }
        resultBox.classList.remove('hidden');
    } catch (err) {
        resultBox.className = 'p-3.5 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs shadow-xs';
        resultBox.innerHTML = `<strong><i class="bi bi-x-circle-fill mr-1 text-rose-600"></i> Error:</strong> Could not connect to testing endpoint.`;
        resultBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-check"></i> Test SMTP Connection';
    }
}
</script>
@endpush
@endsection
