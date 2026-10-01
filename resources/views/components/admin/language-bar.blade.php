@props([
    'languages' => null,
    'requiredFields' => [],
    'htmlFields' => [],
])

@php
    $langs = $languages ?? \App\Models\Language::getActive();
    $translationManager = app(\App\Services\Translation\TranslationManager::class);
    $activeProviderName = $translationManager->isConfigured() ? $translationManager->driver()->getName() : null;
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs mb-6"
     x-data="{
         isTranslating: false,
         translateMessage: '',
         translateSuccess: false,
         autoTranslate(targetLocale) {
             if (targetLocale === 'en') return;
             
             this.isTranslating = true;
             this.translateMessage = 'Translating content from English via {{ $activeProviderName ?? 'Translation API' }}...';
             this.translateSuccess = false;

             // Collect source values from English fields
             let texts = {};
             let requiredKeys = {{ json_encode($requiredFields) }};
             let htmlKeys = {{ json_encode($htmlFields) }};

             // Search for English inputs, textareas, and Quill editors
             let enInputs = document.querySelectorAll('[data-locale=\'en\']');
             enInputs.forEach(el => {
                 let fieldName = el.getAttribute('data-field');
                 if (!fieldName) return;

                 // Check if Quill editor attached
                 if (el.classList.contains('ql-editor') || el.closest('.ql-container')) {
                     texts[fieldName] = el.innerHTML;
                 } else {
                     texts[fieldName] = el.value || '';
                 }
             });

             if (Object.keys(texts).length === 0) {
                 this.isTranslating = false;
                 this.translateMessage = 'No English content found to translate.';
                 return;
             }

             fetch('{{ route('admin.api.translate') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     texts: texts,
                     target_locale: targetLocale,
                     source_locale: 'en',
                     html_fields: htmlKeys
                 })
             })
             .then(res => res.json())
             .then(data => {
                 this.isTranslating = false;
                 if (data.success && data.translations) {
                     this.translateSuccess = true;
                     this.translateMessage = 'Content successfully translated from English!';

                     // Populate target language inputs
                     for (let fieldName in data.translations) {
                         let translatedVal = data.translations[fieldName];
                         let targetEl = document.querySelector('[data-locale=\'' + targetLocale + '\'][data-field=\'' + fieldName + '\']');
                         if (targetEl) {
                             if (targetEl.tagName === 'INPUT' || targetEl.tagName === 'TEXTAREA') {
                                 targetEl.value = translatedVal;
                                 targetEl.dispatchEvent(new Event('input', { bubbles: true }));
                                 targetEl.dispatchEvent(new Event('change', { bubbles: true }));
                             }
                             // Handle Quill WYSIWYG editor if present
                             if (window.quillEditors && window.quillEditors[targetLocale + '_' + fieldName]) {
                                 window.quillEditors[targetLocale + '_' + fieldName].root.innerHTML = translatedVal;
                             }
                         }
                     }

                     // Trigger input event to update completion status
                     window.dispatchEvent(new CustomEvent('language-content-updated', { detail: { locale: targetLocale } }));

                     setTimeout(() => { this.translateMessage = ''; }, 5000);
                 } else {
                     this.translateSuccess = false;
                     this.translateMessage = data.message || 'Translation failed. Please check your API settings.';
                 }
             })
             .catch(err => {
                 this.isTranslating = false;
                 this.translateSuccess = false;
                 this.translateMessage = 'Translation request failed: ' + err.message;
             });
         }
     }">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Language Switcher Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 me-2 shrink-0">
                <i class="bi bi-translate text-primary"></i> Content Locale:
            </span>

            @foreach($langs as $lang)
                <button type="button"
                        @click="activeLocale = '{{ $lang->code }}'"
                        class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer shrink-0"
                        :class="activeLocale === '{{ $lang->code }}' 
                            ? 'bg-primary text-white shadow-sm ring-2 ring-primary/30' 
                            : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'">
                    
                    <span class="text-sm">
                        @if($lang->flag)
                            {{ $lang->flag }}
                        @elseif($lang->code === 'en')
                            🇬🇧
                        @elseif($lang->code === 'ar')
                            🇦🇪
                        @else
                            🌐
                        @endif
                    </span>

                    <span>{{ $lang->name }} ({{ strtoupper($lang->code) }})</span>

                    @if($lang->isRtl())
                        <span class="px-1.5 py-0.5 rounded text-[9px] uppercase tracking-wider font-extrabold"
                              :class="activeLocale === '{{ $lang->code }}' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                            RTL
                        </span>
                    @endif

                    <!-- Completion status indicator -->
                    <span class="w-2 h-2 rounded-full"
                          :class="isLocaleComplete('{{ $lang->code }}') ? 'bg-emerald-400' : 'bg-amber-400'"
                          :title="isLocaleComplete('{{ $lang->code }}') ? 'All required fields completed' : 'Missing required fields'">
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Auto-Translate Action Button -->
        <div class="flex items-center gap-3 shrink-0">
            <template x-if="activeLocale !== 'en'">
                <div>
                    @if($activeProviderName)
                        <button type="button"
                                @click="autoTranslate(activeLocale)"
                                :disabled="isTranslating"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-xs cursor-pointer disabled:opacity-50">
                            <i class="bi" :class="isTranslating ? 'bi-arrow-repeat animate-spin' : 'bi-magic'"></i>
                            <span x-text="isTranslating ? 'Translating...' : 'Auto-Translate with {{ $activeProviderName }}'"></span>
                        </button>
                    @else
                        <a href="{{ route('admin.settings.translations') }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 hover:text-primary text-[11px] font-semibold transition">
                            <i class="bi bi-gear-fill text-slate-400"></i>
                            <span>Configure Translation API</span>
                        </a>
                    @endif
                </div>
            </template>

            <!-- Language Guide Tooltip -->
            <div class="text-[11px] text-slate-400 hidden xl:flex items-center gap-1.5">
                <i class="bi bi-info-circle"></i>
                <span>Editing <strong class="text-slate-700" x-text="activeLocale.toUpperCase()"></strong> version. All languages required before publish.</span>
            </div>
        </div>
    </div>

    <!-- Live Toast Status Message -->
    <div x-show="translateMessage !== ''"
         x-cloak
         x-transition
         class="mt-3 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between"
         :class="translateSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
        <div class="flex items-center gap-2">
            <i class="bi" :class="translateSuccess ? 'bi-check-circle-fill text-emerald-600' : 'bi-exclamation-circle-fill text-rose-600'"></i>
            <span x-text="translateMessage"></span>
        </div>
        <button type="button" @click="translateMessage = ''" class="opacity-70 hover:opacity-100 cursor-pointer">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>
