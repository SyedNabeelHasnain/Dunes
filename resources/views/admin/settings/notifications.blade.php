@extends('layouts.admin')

@section('page_title', 'Notification & Alert Management')

@section('content')
<div>
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h4 class="text-lg font-extrabold text-slate-900 flex items-center gap-2 leading-tight">
                <i class="bi bi-bell-fill text-primary"></i> Notification & Operational Alert Controls
            </h4>
            <div class="text-xs text-slate-500 mt-0.5">Manage customer transaction emails, PDF ticket voucher attachments, multi-recipient operational routing, and real-time webhook syndication.</div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition cursor-pointer" onclick="document.getElementById('notificationSettingsForm').submit();">
                <i class="bi bi-check-lg"></i> Save All Notification Settings
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs mb-6 shadow-xs">
        <i class="bi bi-check-circle-fill text-emerald-600 text-base shrink-0"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs mb-6 shadow-xs">
        <div class="font-bold mb-1 flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i> Please correct the following errors:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Main Form Column (8 Cols) -->
        <div class="xl:col-span-8">
            <form id="notificationSettingsForm" action="{{ route('admin.settings.notifications.update') }}" method="POST">
                @csrf

                <!-- Section 1: Official Sender Identity -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <h5 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="bi bi-person-badge-fill text-primary"></i> Sender Identity & Reply-To Routing
                        </h5>
                        <span class="rounded-full border border-primary/20 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold text-primary">
                            Brand Authority
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">From Name</label>
                            <input type="text" name="notification_from_name" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Dunes Discovery Tourism LLC" value="{{ old('notification_from_name', $settings['notification_from_name'] ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Displayed sender name in email inboxes.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">From Email Address</label>
                            <input type="email" name="notification_from_email" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="reservations@dunesdiscoverytourism.com" value="{{ old('notification_from_email', $settings['notification_from_email'] ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Leave blank to use SMTP default address.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Reply-To Email Address</label>
                            <input type="email" name="notification_reply_to" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="support@dunesdiscoverytourism.com" value="{{ old('notification_reply_to', $settings['notification_reply_to'] ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Direct destination when customers click Reply.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer Booking Confirmation & PDF Voucher -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-person-check-fill"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">Customer Booking Confirmation & Offline Voucher</h5>
                                <div class="text-[11px] text-slate-500">Delivered directly to guest inbox immediately after payment/checkout.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">Guest Facing</span>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_booking_customer_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_booking_customer_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Send Booking Confirmation Email to Customer</div>
                                <div class="text-[11px] text-slate-500">Sends responsive HTML booking confirmation with itinerary, timing, pickup hotel, driver briefing, and payment breakdown.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-amber-200/80 bg-amber-50/40 hover:border-amber-300 cursor-pointer transition">
                            <input type="checkbox" name="notification_booking_customer_attach_pdf" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-amber-300 text-primary focus:ring-primary" {{ ($settings['notification_booking_customer_attach_pdf'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <i class="bi bi-file-earmark-pdf-fill text-rose-600"></i> Attach Official A4 Ticket / Voucher PDF Directly to Email (Recommended)
                                </div>
                                <div class="text-[11px] text-amber-800/80 mt-0.5">
                                    International tourists often experience roaming data delays upon landing at DXB airport. Generating and attaching the printable PDF ticket voucher in-memory guarantees offline access to their QR code and tour pass directly in their inbox.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Section 3: Operations Team Booking Alerts -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-building-fill-gear"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">Admin & Operations Team Booking Alerts</h5>
                                <div class="text-[11px] text-slate-500">Notifies reservations desk, fleet dispatch, and logistics managers.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-0.5 text-[11px] font-bold text-sky-700">Internal Operations</span>
                    </div>

                    <div class="space-y-4 mb-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_booking_admin_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_booking_admin_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Dispatch Instant Operational Booking Alert</div>
                                <div class="text-[11px] text-slate-500">Alerts management whenever a booking is created or confirmed with full passenger manifest and financials.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_booking_admin_attach_pdf" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_booking_admin_attach_pdf'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="bi bi-file-earmark-pdf-fill text-rose-500"></i> Attach Ticket Voucher PDF to Admin Email
                                </div>
                                <div class="text-[11px] text-slate-500">Enables tour dispatchers and 4x4 safari marshals to print or forward voucher copies immediately.</div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Dedicated Booking Alert Recipients <span class="text-[11px] font-normal text-slate-500">(Comma-separated emails)</span>
                        </label>
                        <input type="text" name="notification_booking_recipients" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="reservations@dunesdiscoverytourism.com, dispatch@dunesdiscoverytourism.com" value="{{ old('notification_booking_recipients', $settings['notification_booking_recipients'] ?? '') }}">
                        <p class="text-[10px] text-slate-400 mt-1">If blank, alerts fall back to Primary Admin Email (<code>{{ $settings['admin_email'] ?? 'info@dunesdiscoverytourism.com' }}</code>) and global CC/BCC.</p>
                    </div>
                </div>

                <!-- Section 4: WhatsApp High-Intent Lead Alerts -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-whatsapp"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">Instant WhatsApp Consultation & Lead Alerts</h5>
                                <div class="text-[11px] text-slate-500">Triggered whenever a visitor initiates a direct WhatsApp safari inquiry.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">High-Intent Sales</span>
                    </div>

                    <div class="space-y-4 mb-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_whatsapp_admin_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_whatsapp_admin_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Dispatch Email Alert on WhatsApp Interaction</div>
                                <div class="text-[11px] text-slate-500">Emails your sales team with customer details, acquisition traffic source (Google Ads, Meta, Direct), city/country, and a green 1-click WhatsApp Reply button.</div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            WhatsApp Lead Recipients <span class="text-[11px] font-normal text-slate-500">(Comma-separated emails)</span>
                        </label>
                        <input type="text" name="notification_whatsapp_recipients" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="sales@dunesdiscoverytourism.com, manager@dunesdiscoverytourism.com" value="{{ old('notification_whatsapp_recipients', $settings['notification_whatsapp_recipients'] ?? '') }}">
                        <p class="text-[10px] text-slate-400 mt-1">If blank, defaults to site email or primary admin email.</p>
                    </div>
                </div>

                <!-- Section 5: Contact Inquiry Notifications & Auto-Acknowledgement -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-chat-left-dots-fill"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">General Contact Inquiries & Auto-Receipt</h5>
                                <div class="text-[11px] text-slate-500">Controls incoming contact form submissions and polite automated receipts.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700">Inquiry Desk</span>
                    </div>

                    <div class="space-y-4 mb-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_contact_admin_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_contact_admin_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Dispatch Admin Alert on New Contact Inquiry</div>
                                <div class="text-[11px] text-slate-500">Alerts team with customer message, phone, subject, and marketing attribution data.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_contact_customer_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_contact_customer_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Send Instant Customer Auto-Acknowledgement Receipt</div>
                                <div class="text-[11px] text-slate-500">Sends polite automated confirmation confirming our team has received their message and will respond promptly.</div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Contact Inquiry Recipients <span class="text-[11px] font-normal text-slate-500">(Comma-separated emails)</span>
                        </label>
                        <input type="text" name="notification_contact_recipients" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="info@dunesdiscoverytourism.com, contact@dunesdiscoverytourism.com" value="{{ old('notification_contact_recipients', $settings['notification_contact_recipients'] ?? '') }}">
                        <p class="text-[10px] text-slate-400 mt-1">If blank, defaults to Primary Admin Email.</p>
                    </div>
                </div>

                <!-- Section 6: Customer Reviews & Guest Feedback Alerts -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">Customer Reviews & Post-Safari Feedback Alerts</h5>
                                <div class="text-[11px] text-slate-500">Instant notification when guests submit ratings, testimonials, or private feedback.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-700">Quality Assurance</span>
                    </div>

                    <div class="space-y-4 mb-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_review_admin_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_review_admin_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Dispatch Review & Feedback Alert to Quality Team</div>
                                <div class="text-[11px] text-slate-500">Notifies management with rating breakdown, guest comments, photos, and 1-click link to moderate review in CMS.</div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Review Alert Recipients <span class="text-[11px] font-normal text-slate-500">(Comma-separated emails)</span>
                        </label>
                        <input type="text" name="notification_review_recipients" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="quality@dunesdiscoverytourism.com, feedback@dunesdiscoverytourism.com" value="{{ old('notification_review_recipients', $settings['notification_review_recipients'] ?? '') }}">
                        <p class="text-[10px] text-slate-400 mt-1">If blank, defaults to Primary Admin Email.</p>
                    </div>
                </div>

                <!-- Section 7: Outbound Webhook Syndication -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
                                <i class="bi bi-broadcast-pin"></i>
                            </span>
                            <div>
                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">External Real-Time Webhook Syndication</h5>
                                <div class="text-[11px] text-slate-500">Stream notification payloads to Slack, Discord, Zapier, Make, or custom CRM.</div>
                            </div>
                        </div>
                        <span class="rounded-full border border-purple-200 bg-purple-50 px-2.5 py-0.5 text-[11px] font-bold text-purple-700">Webhook API</span>
                    </div>

                    <div class="space-y-4 mb-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                            <input type="checkbox" name="notification_webhook_enabled" value="1" class="mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-primary focus:ring-primary" {{ ($settings['notification_webhook_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                            <div class="text-xs">
                                <div class="font-bold text-slate-800">Enable Outbound Webhook Dispatch</div>
                                <div class="text-[11px] text-slate-500">Automatically POSTs JSON payloads to your external webhook endpoint on booking, lead, contact, and review events.</div>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Webhook Endpoint URL</label>
                            <input type="url" name="notification_webhook_url" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="https://hooks.slack.com/services/... or https://hook.eu1.make.com/..." value="{{ old('notification_webhook_url', $settings['notification_webhook_url'] ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Accepts standard HTTPS Webhook targets.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Webhook Secret (HMAC SHA-256)</label>
                            <input type="text" name="notification_webhook_secret" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary font-mono" placeholder="whsec_xxxxxxxxxxxxxxxxxxxx" value="{{ old('notification_webhook_secret', $settings['notification_webhook_secret'] ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Sent in <code>X-Dunes-Signature</code> header for verification.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition cursor-pointer">
                        <i class="bi bi-check-circle-fill"></i> Save All Notification Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Diagnostic Column (4 Cols) -->
        <div class="xl:col-span-4 space-y-6">
            <!-- Diagnostic Test Lab Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <h5 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-amber-500"></i> Live Diagnostic Dispatcher
                    </h5>
                    <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-primary">
                        1-Click Test
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Test live delivery of any transactional email template, verify recipient routing, and confirm PDF ticket voucher attachment generation in real time.
                </p>

                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Notification Event to Test:</label>
                        <select id="testNotificationEvent" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="booking_customer">Customer Booking Email (with PDF Voucher)</option>
                            <option value="booking_admin">Admin Booking Alert (with PDF Voucher)</option>
                            <option value="whatsapp_lead">WhatsApp Lead Alert (with 1-Click Reply)</option>
                            <option value="contact_inquiry">Contact Form Submission Alert</option>
                            <option value="customer_review">Customer UGC Review Alert</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Test Recipient Email:</label>
                        <input type="email" id="testNotificationRecipient" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" placeholder="admin@dunesdiscoverytourism.com" value="{{ auth()->user()->email ?? '' }}">
                    </div>
                </div>

                <button type="button" id="btnSendTestNotification" onclick="dispatchLiveNotificationTest()" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition cursor-pointer">
                    <i class="bi bi-send-fill text-primary"></i> Dispatch Live Test Notification
                </button>

                <div id="testNotificationResult" class="mt-4 hidden"></div>
            </div>

            <!-- Global Recipient Directory Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-envelope-at-fill text-primary"></i> Active Mailbox Configuration
                </h5>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Primary Admin:</span>
                        <span class="font-bold text-slate-800 font-mono text-[11px]">{{ $settings['admin_email'] ?? 'info@dunesdiscoverytourism.com' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Global CC Emails:</span>
                        <span class="text-slate-700 font-mono text-[11px] text-right truncate max-w-[170px]" title="{{ $settings['admin_email_cc'] ?? 'None' }}">
                            {{ !empty($settings['admin_email_cc']) ? $settings['admin_email_cc'] : 'None configured' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Global BCC Emails:</span>
                        <span class="text-slate-700 font-mono text-[11px] text-right truncate max-w-[170px]" title="{{ $settings['admin_email_bcc'] ?? 'None' }}">
                            {{ !empty($settings['admin_email_bcc']) ? $settings['admin_email_bcc'] : 'None configured' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Public Site Email:</span>
                        <span class="font-bold text-slate-800 font-mono text-[11px]">{{ $settings['site_email'] ?? 'info@dunesdiscoverytourism.com' }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                    <a href="{{ route('admin.settings.mail') }}" class="text-[11px] text-primary hover:underline font-bold inline-flex items-center gap-1">
                        <i class="bi bi-gear-fill"></i> Manage SMTP Server Credentials &rarr;
                    </a>
                </div>
            </div>

            <!-- Best Practice Guidelines -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                <h6 class="text-[11px] font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                    <i class="bi bi-shield-check text-emerald-600"></i> Operations Architecture
                </h6>
                <ul class="text-[11px] text-slate-600 space-y-2 list-disc list-inside leading-relaxed">
                    <li><strong class="text-slate-800">In-Memory PDF Generation:</strong> Ticket vouchers are compiled using DomPDF in RAM without writing temporary files to server disk.</li>
                    <li><strong class="text-slate-800">Multi-Recipient Delivery:</strong> Entering multiple comma-separated emails allows team members across dispatch, finance, and quality control to stay synchronized.</li>
                    <li><strong class="text-slate-800">Direct WhatsApp Linkage:</strong> Lead emails incorporate an instant <code>wa.me</code> deep link so agents can reply on WhatsApp in under 30 seconds.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function dispatchLiveNotificationTest() {
    const eventSelect = document.getElementById('testNotificationEvent');
    const recipientInput = document.getElementById('testNotificationRecipient');
    const resultBox = document.getElementById('testNotificationResult');
    const btn = document.getElementById('btnSendTestNotification');

    const event = eventSelect.value;
    const email = recipientInput.value.trim();

    if (!email) {
        resultBox.className = 'flex items-center gap-2 p-3 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs';
        resultBox.innerHTML = '<i class="bi bi-exclamation-circle-fill text-rose-600"></i> Please specify a recipient email address.';
        resultBox.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-2"><i class="bi bi-arrow-repeat"></i></span> Generating & Dispatching Test...';
    resultBox.classList.add('hidden');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const response = await fetch('{{ route("admin.settings.notifications.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                event: event,
                test_email: email
            })
        });

        const data = await response.json();
        if (data.success) {
            let pdfNotice = data.pdf_attached ? '<div class="mt-1 text-[11px] text-emerald-700 font-bold"><i class="bi bi-file-earmark-pdf-fill text-rose-600"></i> Official A4 Ticket Voucher PDF successfully generated and attached.</div>' : '';
            resultBox.className = 'p-3.5 mt-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs shadow-xs';
            resultBox.innerHTML = `<strong><i class="bi bi-check-circle-fill mr-1 text-emerald-600"></i> Success!</strong> ${data.message}${pdfNotice}`;
        } else {
            resultBox.className = 'p-3.5 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs shadow-xs';
            resultBox.innerHTML = `<strong><i class="bi bi-exclamation-triangle-fill mr-1 text-rose-600"></i> Dispatch Failed:</strong><br>${data.message || 'Failed sending notification.'}`;
        }
        resultBox.classList.remove('hidden');
    } catch (err) {
        resultBox.className = 'p-3.5 mt-3 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 text-xs shadow-xs';
        resultBox.innerHTML = `<strong><i class="bi bi-x-circle-fill mr-1 text-rose-600"></i> System Error:</strong> Unable to communicate with test endpoint.`;
        resultBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill text-primary"></i> Dispatch Live Test Notification';
    }
}
</script>
@endpush
