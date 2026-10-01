@extends('layouts.admin')

@section('page_title', 'Translation Services APIs')

@section('content')
<div x-data="{
    activeService: '{{ $activeService ?? 'none' }}',
    testTesting: false,
    testResult: null,
    testDriver(driver) {
        this.testTesting = true;
        this.testResult = null;
        
        let payload = { driver: driver };
        if (driver === 'deepl') {
            payload.auth_key = document.getElementById('deepl_auth_key').value;
            payload.endpoint_type = document.getElementById('deepl_endpoint_type').value;
        } else if (driver === 'google') {
            payload.api_key = document.getElementById('google_translate_api_key').value;
        }

        fetch('{{ route('admin.settings.translations.test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            this.testTesting = false;
            this.testResult = data;
        })
        .catch(err => {
            this.testTesting = false;
            this.testResult = { success: false, message: 'Request error: ' + err.message };
        });
    }
}">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-6">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 bg-white flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">Translation Services & AI API Integrations</h5>
                <div class="text-xs text-slate-500 mt-0.5">Configure automated machine translation APIs to instantly autofill Arabic and international content from English.</div>
            </div>
        </div>

        <!-- Strict Single Service Banner -->
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-3 text-xs text-slate-600">
            <i class="bi bi-info-circle-fill text-primary text-sm"></i>
            <div>
                <span class="font-bold text-slate-800">Single Active Service Rule:</span> Only one translation provider can be active at any given time. The selected active engine will be utilized across all admin content editing bars.
            </div>
        </div>

        <!-- Test Result Banner (Dynamic) -->
        <div x-show="testResult !== null" x-cloak class="px-6 py-3 transition-all" :class="testResult && testResult.success ? 'bg-emerald-50 border-b border-emerald-100 text-emerald-800' : 'bg-rose-50 border-b border-rose-100 text-rose-800'">
            <div class="flex items-center justify-between text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <i class="bi" :class="testResult && testResult.success ? 'bi-check-circle-fill text-emerald-600' : 'bi-exclamation-triangle-fill text-rose-600'"></i>
                    <span x-text="testResult ? testResult.message : ''"></span>
                </div>
                <button type="button" @click="testResult = null" class="text-xs opacity-70 hover:opacity-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('admin.settings.translations.update') }}" method="POST" class="p-6">
            @csrf

            <!-- Provider Selection Radios -->
            <div class="mb-8">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                    Select Active Translation Engine <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- None -->
                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition select-none" :class="activeService === 'none' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 bg-white hover:border-slate-300'">
                        <input type="radio" name="translation_active_service" value="none" x-model="activeService" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-2">
                                <i class="bi bi-slash-circle text-slate-400"></i> None (Manual Only)
                            </span>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center transition" :class="activeService === 'none' ? 'border-primary bg-primary text-white' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="activeService === 'none'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Admin authors manually type or paste translations into language tabs without automated API calls.</p>
                    </label>

                    <!-- DeepL -->
                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition select-none" :class="activeService === 'deepl' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 bg-white hover:border-slate-300'">
                        <input type="radio" name="translation_active_service" value="deepl" x-model="activeService" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-amber-500"></i> DeepL API
                            </span>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center transition" :class="activeService === 'deepl' ? 'border-primary bg-primary text-white' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="activeService === 'deepl'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Industry-leading neural MT with flawless HTML tag preservation and authentic phrasing.</p>
                    </label>

                    <!-- Google Cloud Translation -->
                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition select-none" :class="activeService === 'google' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 bg-white hover:border-slate-300'">
                        <input type="radio" name="translation_active_service" value="google" x-model="activeService" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black text-slate-900 flex items-center gap-2">
                                <i class="bi bi-google text-blue-500"></i> Google Cloud Translation
                            </span>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center transition" :class="activeService === 'google' ? 'border-primary bg-primary text-white' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="activeService === 'google'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">High-volume, low-latency machine translation supporting 100+ languages globally.</p>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- DeepL API Settings Card -->
                <div class="p-5 rounded-2xl border transition" :class="activeService === 'deepl' ? 'bg-amber-50/20 border-amber-300/80 shadow-xs' : 'bg-slate-50/50 border-slate-200 opacity-90'">
                    <div class="flex items-center justify-between mb-4">
                        <h6 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <i class="bi bi-key-fill text-amber-500"></i> DeepL API Credentials
                        </h6>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="activeService === 'deepl' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                            <span x-text="activeService === 'deepl' ? 'Active Engine' : 'Inactive'"></span>
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="deepl_auth_key" class="block text-xs font-bold text-slate-700">DeepL Authentication Key</label>
                                @if(!empty($settings['deepl_auth_key']))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                        <i class="bi bi-shield-check"></i> Key Settled & Saved
                                    </span>
                                @else
                                    <span class="text-[11px] font-medium text-slate-400">Not configured yet</span>
                                @endif
                            </div>

                            <div x-data="{ showKey: false }" class="relative">
                                <input :type="showKey ? 'text' : 'password'" 
                                       name="deepl_auth_key" 
                                       id="deepl_auth_key" 
                                       value="{{ $settings['deepl_auth_key'] ?? '' }}" 
                                       autocomplete="new-password"
                                       placeholder="e.g. 12345678-abcd-1234-efgh-123456789012:fx" 
                                       class="w-full rounded-xl border border-slate-200 bg-white ps-3.5 pe-10 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden font-mono">
                                <button type="button" 
                                        @click="showKey = !showKey" 
                                        class="absolute inset-y-0 end-0 pe-3 flex items-center text-slate-400 hover:text-slate-700 cursor-pointer transition"
                                        title="Click to Reveal / Hide Key">
                                    <i class="bi text-sm" :class="showKey ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500">
                                <span>Keys ending with <code class="bg-slate-100 px-1 py-0.5 rounded font-mono text-slate-700">:fx</code> use the Free API tier automatically.</span>
                                @if(!empty($settings['deepl_auth_key']))
                                    <span class="text-slate-400 font-mono text-[10px]">
                                        Saved: {{ Str::mask($settings['deepl_auth_key'], '*', 4, -4) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label for="deepl_endpoint_type" class="block text-xs font-bold text-slate-700 mb-1">Account API Plan</label>
                            <select name="deepl_endpoint_type" id="deepl_endpoint_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                                <option value="free" {{ ($settings['deepl_endpoint_type'] ?? 'free') === 'free' ? 'selected' : '' }}>DeepL API Free (api-free.deepl.com)</option>
                                <option value="pro" {{ ($settings['deepl_endpoint_type'] ?? 'free') === 'pro' ? 'selected' : '' }}>DeepL API Pro (api.deepl.com)</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="button" @click="testDriver('deepl')" :disabled="testTesting" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition shadow-xs cursor-pointer disabled:opacity-50">
                                <i class="bi bi-broadcast" :class="{ 'animate-pulse text-amber-500': testTesting }"></i>
                                <span>Test DeepL Connection</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Google Cloud Translation Settings Card -->
                <div class="p-5 rounded-2xl border transition" :class="activeService === 'google' ? 'bg-blue-50/20 border-blue-300/80 shadow-xs' : 'bg-slate-50/50 border-slate-200 opacity-90'">
                    <div class="flex items-center justify-between mb-4">
                        <h6 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <i class="bi bi-google text-blue-500"></i> Google Cloud Translation Credentials
                        </h6>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="activeService === 'google' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                            <span x-text="activeService === 'google' ? 'Active Engine' : 'Inactive'"></span>
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="google_translate_api_key" class="block text-xs font-bold text-slate-700">Google Cloud API Key</label>
                                @if(!empty($settings['google_translate_api_key']))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                        <i class="bi bi-shield-check"></i> Key Settled & Saved
                                    </span>
                                @else
                                    <span class="text-[11px] font-medium text-slate-400">Not configured yet</span>
                                @endif
                            </div>

                            <div x-data="{ showKey: false }" class="relative">
                                <input :type="showKey ? 'text' : 'password'" 
                                       name="google_translate_api_key" 
                                       id="google_translate_api_key" 
                                       value="{{ $settings['google_translate_api_key'] ?? '' }}" 
                                       autocomplete="new-password"
                                       placeholder="e.g. AIzaSy..." 
                                       class="w-full rounded-xl border border-slate-200 bg-white ps-3.5 pe-10 py-2.5 text-xs text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary outline-hidden font-mono">
                                <button type="button" 
                                        @click="showKey = !showKey" 
                                        class="absolute inset-y-0 end-0 pe-3 flex items-center text-slate-400 hover:text-slate-700 cursor-pointer transition"
                                        title="Click to Reveal / Hide Key">
                                    <i class="bi text-sm" :class="showKey ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500">
                                <span>Google Cloud Console > Credentials > API Key (with Cloud Translation API enabled).</span>
                                @if(!empty($settings['google_translate_api_key']))
                                    <span class="text-slate-400 font-mono text-[10px]">
                                        Saved: {{ Str::mask($settings['google_translate_api_key'], '*', 4, -4) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="button" @click="testDriver('google')" :disabled="testTesting" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition shadow-xs cursor-pointer disabled:opacity-50">
                                <i class="bi bi-broadcast" :class="{ 'animate-pulse text-blue-500': testTesting }"></i>
                                <span>Test Google Connection</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    <i class="bi bi-check2-circle me-1.5"></i> Save Translation Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
