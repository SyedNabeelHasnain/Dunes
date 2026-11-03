<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Access Restricted | Dunes Discovery Tourism</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #0b1120;
            color: #f8fafc;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .luxury-gradient-bg {
            background: radial-gradient(circle at 50% 15%, rgba(245, 143, 67, 0.12) 0%, rgba(11, 17, 32, 0) 70%),
                        radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.08) 0%, rgba(11, 17, 32, 0) 60%);
        }
    </style>
</head>
<body class="luxury-gradient-bg antialiased selection:bg-amber-500 selection:text-white">

    <!-- Top Executive Header -->
    <header class="w-full border-b border-slate-800/80 bg-slate-950/60 backdrop-blur-md py-4 px-6 sm:px-10">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-white.png') }}" alt="Dunes Discovery Tourism" class="h-9 w-auto object-contain" onerror="this.style.display='none'">
                <div class="border-s border-slate-700/80 ps-3 hidden sm:block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Security & Access Governance</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                    <i class="bi bi-shield-lock-fill text-amber-500"></i> DET License #1430583
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow flex items-center justify-center px-4 py-12 sm:px-6">
        <div class="w-full max-w-2xl">
            
            <!-- Notice Card -->
            <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 sm:p-10 shadow-2xl backdrop-blur-xl relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Status Badge & Icon -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                        <i class="bi bi-shield-x text-2xl"></i>
                    </div>
                    <div>
                        <div class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30 mb-1">
                            HTTP 403 Forbidden
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Access Temporarily Restricted</h1>
                    </div>
                </div>

                <!-- Explanation -->
                <p class="text-sm text-slate-300 leading-relaxed mb-6">
                    Your network IP address <strong class="font-mono text-amber-400 bg-slate-950/80 px-2 py-0.5 rounded border border-slate-800">{{ $clientIp ?? request()->ip() }}</strong> has been marked as restricted by the automated security governance firewall of <span class="font-semibold text-white">Dunes Discovery Tourism LLC</span>.
                </p>

                <!-- Unblock Appeal Section -->
                <div class="rounded-2xl border border-slate-800/90 bg-slate-950/60 p-5 sm:p-6 mb-6">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-200 mb-2 flex items-center gap-2">
                        <i class="bi bi-person-check text-amber-500"></i> Request Immediate Access Review
                    </h2>
                    <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                        If you are an international traveler, corporate partner, or guest planning an excursion and believe this restriction is an error, please submit this verification request. Our operations desk will review and restore access promptly.
                    </p>

                    <form id="unblockAppealForm" onsubmit="submitUnblockAppeal(event)" class="space-y-4">
                        <input type="hidden" name="ip_address" value="{{ $clientIp ?? request()->ip() }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required class="w-full rounded-xl border border-slate-700 bg-slate-900/90 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-hidden transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="e.g. Alexander Wright">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Email Address <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" required class="w-full rounded-xl border border-slate-700 bg-slate-900/90 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-hidden transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="guest@example.com">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">WhatsApp / Phone Number</label>
                            <input type="tel" name="phone" class="w-full rounded-xl border border-slate-700 bg-slate-900/90 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-hidden transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="+971 50 123 4567">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Explanation / Travel Inquiry <span class="text-rose-500">*</span></label>
                            <textarea name="reason" rows="3" required class="w-full rounded-xl border border-slate-700 bg-slate-900/90 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-hidden transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Please mention the tour or service you are trying to view or book..."></textarea>
                        </div>

                        <button type="submit" id="btnSubmitAppeal" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-amber-600/20 hover:bg-amber-500 transition cursor-pointer">
                            <i class="bi bi-send-fill"></i> Submit Unblock Appeal to Operations Desk
                        </button>
                    </form>

                    <div id="appealFeedback" class="mt-4 hidden"></div>
                </div>

                <!-- Direct Fast-Track Contacts -->
                <div class="border-t border-slate-800 pt-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400">
                    <div>
                        <div class="text-slate-300 font-bold">Need instant booking confirmation?</div>
                        <div class="text-[11px] text-slate-500">Contact our 24/7 Dubai reservation desk directly:</div>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $cleanWa = '971502456056';
                            $waText = urlencode("Hello Dunes Discovery Team, my IP is " . ($clientIp ?? request()->ip()) . " and I was blocked while trying to visit the website. Please assist me with booking.");
                        @endphp
                        <a href="https://wa.me/{{ $cleanWa }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                            <i class="bi bi-whatsapp"></i> WhatsApp Desk
                        </a>
                        <a href="mailto:info@dunesdiscoverytourism.com?subject={{ urlencode('Unblock Request - IP ' . ($clientIp ?? request()->ip())) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition border border-slate-700">
                            <i class="bi bi-envelope"></i> Email Support
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/80 bg-slate-950/60 py-4 px-6 text-center text-[11px] text-slate-500">
        <p>&copy; {{ date('Y') }} Dunes Discovery Tourism LLC. All Rights Reserved. Licensed by Government of Dubai DET #1430583.</p>
    </footer>

    <script>
    async function submitUnblockAppeal(e) {
        e.preventDefault();
        const form = document.getElementById('unblockAppealForm');
        const btn = document.getElementById('btnSubmitAppeal');
        const feedback = document.getElementById('appealFeedback');

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-2"><i class="bi bi-arrow-repeat"></i></span> Transmitting Review Request...';
        feedback.classList.add('hidden');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route("security.unblock-request") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (result.success) {
                form.classList.add('hidden');
                feedback.className = 'p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200 text-xs shadow-md leading-relaxed';
                feedback.innerHTML = '<div class="flex items-start gap-2.5"><i class="bi bi-check-circle-fill text-emerald-400 text-base shrink-0 mt-0.5"></i><div><strong class="font-bold text-white text-sm block mb-1">Review Request Submitted Successfully!</strong>' + result.message + '</div></div>';
            } else {
                feedback.className = 'p-3.5 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-200 text-xs shadow-md';
                feedback.innerHTML = '<div class="flex items-start gap-2"><i class="bi bi-exclamation-triangle-fill text-rose-400 shrink-0 mt-0.5"></i><div>' + (result.message || 'Failed to submit unblock request.') + '</div></div>';
            }
            feedback.classList.remove('hidden');
        } catch (err) {
            feedback.className = 'p-3.5 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-200 text-xs shadow-md';
            feedback.innerHTML = '<div class="flex items-start gap-2"><i class="bi bi-x-circle-fill text-rose-400 shrink-0 mt-0.5"></i><div>An unexpected connection error occurred. Please reach out via WhatsApp or email directly.</div></div>';
            feedback.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Unblock Appeal to Operations Desk';
        }
    }
    </script>
</body>
</html>
