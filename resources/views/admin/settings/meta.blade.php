@extends('layouts.admin')

@section('page_title', 'Meta Conversion Suite')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-facebook"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">Meta / Facebook Conversion suite</h5>
                <div class="text-xs text-slate-500 mt-0.5">Configure Pixel ID and Conversions API (CAPI) events.</div>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- CAPI Status Switch -->
            <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 mb-6">
                <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3">Conversions API Tracking</h6>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="meta_active" id="meta_active" value="1" {{ ($settings['meta_active'] ?? '') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                    <label class="text-xs font-bold text-slate-900 cursor-pointer" for="meta_active">Inject browser Pixel and trigger Server Conversions API events</label>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">When enabled, server-side actions like Checkout initiated or WhatsApp clicks will submit secure telemetry directly to Meta via CAPI.</div>
            </div>

            <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
                    <i class="bi bi-gear-fill"></i> Meta API Credentials
                </h6>
                
                <div class="mb-4">
                    <label for="meta_pixel_id" class="block text-xs font-bold text-slate-700 mb-1.5">Meta Pixel ID</label>
                    <input type="text" name="meta_pixel_id" id="meta_pixel_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['meta_pixel_id'] ?? '' }}" placeholder="e.g. 123456789012345" required>
                    <div class="mt-1 text-[11px] text-slate-500">Your unique target Pixel container identity.</div>
                </div>

                <div class="mb-4">
                    <label for="meta_access_token" class="block text-xs font-bold text-slate-700 mb-1.5">System Access Token (CAPI)</label>
                    <textarea name="meta_access_token" id="meta_access_token" rows="4" placeholder="EAAG..." class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-mono text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary">{{ $settings['meta_access_token'] ?? '' }}</textarea>
                    <div class="mt-1 text-[11px] text-slate-500">Long-lived access token generated in Meta Events Manager settings tab.</div>
                </div>

                <div>
                    <label for="meta_test_event_code" class="block text-xs font-bold text-slate-700 mb-1.5">Test Event Code (Debugging Only)</label>
                    <input type="text" name="meta_test_event_code" id="meta_test_event_code" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['meta_test_event_code'] ?? '' }}" placeholder="e.g. TEST12345">
                    <div class="mt-1 text-[11px] text-amber-600 font-medium"><strong>Warning:</strong> Leave empty in production. Only fill when tracking testing in sandbox is active.</div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-check2-circle"></i> Save Meta Configurations
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
