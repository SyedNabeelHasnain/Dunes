@extends('layouts.admin')

@section('page_title', 'WhatsApp Integration Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="bg-slate-50/80 border-b border-slate-200/80 p-5 px-6">
            <h1 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="bi bi-whatsapp text-emerald-500"></i> WhatsApp Integration Settings
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure global settings for WhatsApp clicks and redirection.</p>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.whatsapp.settings.update') }}" method="POST" class="space-y-5">
                @csrf

                <!-- WhatsApp Phone Number -->
                <div>
                    <label for="site_whatsapp" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">WhatsApp Target Number *</label>
                    <input type="text" name="site_whatsapp" id="site_whatsapp" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $settings['site_whatsapp'] ?? '971502456056' }}" placeholder="971502456056" required>
                    <p class="text-[11px] text-slate-400 mt-1">The phone number where customer WhatsApp queries are sent. Must include country code and exclude leading '+' or zeros.</p>
                </div>

                <!-- Default Country Code -->
                <div>
                    <label for="whatsapp_default_country" class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-1.5">Default Country Code (e.g. 971) *</label>
                    <input type="text" name="whatsapp_default_country" id="whatsapp_default_country" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 outline-hidden focus:border-primary" value="{{ $settings['whatsapp_default_country'] ?? '971' }}" placeholder="971" required>
                    <p class="text-[11px] text-slate-400 mt-1">Used to format phone numbers when users do not provide a country prefix.</p>
                </div>

                <!-- WhatsApp Lead Form Field Toggle -->
                <div class="p-5 bg-slate-50/70 border border-slate-200/80 rounded-2xl space-y-3">
                    <div>
                        <h2 class="text-xs font-black uppercase text-slate-700 tracking-wider">WhatsApp Lead Generation Form</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Control if visitors must provide their details before contacting you on WhatsApp.</p>
                    </div>
                    <div class="flex items-center gap-6 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input class="rounded-full border-slate-300 text-primary focus:ring-primary w-4 h-4" type="radio" name="whatsapp_form_enabled" id="waFormYes" value="1" {{ ($settings['whatsapp_form_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-800">Yes (Enabled)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input class="rounded-full border-slate-300 text-primary focus:ring-primary w-4 h-4" type="radio" name="whatsapp_form_enabled" id="waFormNo" value="0" {{ ($settings['whatsapp_form_enabled'] ?? '1') === '0' ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-800">No (Disabled)</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400">If set to "No", visitors will be redirected to WhatsApp instantly. Clicks are logged with default name "WhatsApp Visitor" and no phone detail.</p>
                </div>

                <div class="p-4 bg-sky-50 rounded-2xl border border-sky-200 flex items-start gap-3">
                    <i class="bi bi-info-circle-fill text-sky-500 text-base shrink-0 mt-0.5"></i>
                    <div class="text-xs text-sky-800 leading-relaxed">
                        <strong class="font-bold">Redirection mechanism:</strong><br>
                        Redirection uses standard WhatsApp API links (<code>wa.me</code>). This opens the WhatsApp application natively on mobile devices or WhatsApp Web on desktop browsers with a pre-filled, context-aware message.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
