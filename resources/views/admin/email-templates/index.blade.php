@extends('layouts.admin')

@section('page_title', 'Email Templates Gallery')

@section('content')
<div class="space-y-6" x-data="{ openPreview: false, previewDevice: 'desktop', previewTitle: '', previewUrl: '' }">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi bi-envelope-paper-heart-fill text-primary"></i> Email Templates Gallery
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Design and customize mobile-responsive HTML email templates with dynamic personalization tags.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-primary/30 hover:bg-primary/5 text-primary font-bold text-xs shadow-2xs transition">
                <i class="bi bi-send-plus"></i> New Campaign
            </a>
            <a href="{{ route('admin.email-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-xs transition">
                <i class="bi bi-plus-lg"></i> Create Template
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between overflow-hidden">
            <div class="bg-slate-50/80 border-b border-slate-200/80 p-3.5 px-5 flex items-center justify-between gap-2">
                <div>
                    @if($template->is_system)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            <i class="bi bi-shield-check text-[11px]"></i> System Default
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="bi bi-palette text-[11px]"></i> Custom Layout
                        </span>
                    @endif
                </div>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <i class="bi bi-megaphone"></i> {{ $template->campaigns_count }} campaign(s)
                </span>
            </div>

            <div class="p-5 flex-1">
                <h2 class="font-black text-slate-900 text-sm truncate mb-1" title="{{ $template->name }}">{{ $template->name }}</h2>
                <div class="text-xs text-slate-500 truncate mb-3">
                    <strong class="text-slate-700">Subject:</strong> {{ $template->subject }}
                </div>
                
                @if($template->preview_text)
                <div class="p-2.5 px-3 bg-slate-50 rounded-xl text-slate-500 text-xs mb-3 truncate border border-slate-200">
                    <i class="bi bi-eye text-primary mr-1"></i> {{ $template->preview_text }}
                </div>
                @endif

                <!-- Visual Mini Thumbnail Frame -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 relative mb-4 h-40">
                    <iframe src="{{ route('admin.email-templates.preview', $template->id) }}" class="w-full h-full border-0 pointer-events-none" style="transform: scale(0.65); transform-origin: top left; width: 154%; height: 154%; pointer-events: none;" loading="lazy"></iframe>
                    <div class="absolute inset-0 bg-transparent cursor-pointer" @click="previewTitle = '{{ addslashes($template->name) }}'; previewUrl = '{{ route('admin.email-templates.preview', $template->id) }}'; openPreview = true;"></div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span><i class="bi bi-clock-history mr-1"></i> Updated {{ $template->updated_at->diffForHumans() }}</span>
                    <span class="font-mono text-slate-500">#{{ $template->slug }}</span>
                </div>
            </div>

            <div class="border-t border-slate-100 p-3 px-5 flex items-center justify-between gap-2 bg-slate-50/40">
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition cursor-pointer" @click="previewTitle = '{{ addslashes($template->name) }}'; previewUrl = '{{ route('admin.email-templates.preview', $template->id) }}'; openPreview = true;">
                    <i class="bi bi-eye text-slate-500"></i> Preview
                </button>
                <div class="flex items-center gap-1">
                    <a href="{{ route('admin.campaigns.create', ['template_id' => $template->id]) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="Start Campaign with this template">
                        <i class="bi bi-send-plus text-xs"></i>
                    </a>
                    <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="w-8 h-8 rounded-xl border border-slate-200 hover:border-primary hover:text-primary flex items-center justify-center text-slate-600 bg-white transition shadow-2xs" title="Edit Template">
                        <i class="bi bi-pencil text-xs"></i>
                    </a>
                    @if(!$template->is_system)
                    <form action="{{ route('admin.email-templates.destroy', $template->id) }}" method="POST" class="inline delete-template-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="w-8 h-8 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 flex items-center justify-center bg-white transition shadow-2xs btn-delete-template cursor-pointer" data-name="{{ $template->name }}" title="Delete Template">
                            <i class="bi bi-trash text-xs"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-xs">
                <i class="bi bi-envelope-paper text-4xl text-slate-300 block mb-3"></i>
                <h3 class="text-base font-bold text-slate-800 mb-1">No Email Templates Found</h3>
                <p class="text-xs text-slate-400 mb-5">Start by creating your first responsive email marketing template.</p>
                <div>
                    <a href="{{ route('admin.email-templates.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                        <i class="bi bi-plus-lg"></i> Create Template
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Template Live Preview Modal (Alpine.js) -->
    <div x-show="openPreview" x-cloak class="relative z-50">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openPreview = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-10 flex items-center justify-center">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-5xl w-full overflow-hidden flex flex-col" @click.stop>
                <div class="bg-slate-900 text-white p-4 px-6 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-envelope-check text-primary text-xl"></i>
                        <div>
                            <h3 class="text-sm font-black text-white" x-text="'Preview: ' + previewTitle">Template Preview</h3>
                            <span class="text-[11px] text-slate-400">Live rendered with dynamic tags populated</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center bg-slate-800 rounded-xl p-1 border border-slate-700 text-xs">
                            <button type="button" @click="previewDevice = 'desktop'" :class="previewDevice === 'desktop' ? 'bg-primary text-white' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg font-bold transition cursor-pointer">
                                <i class="bi bi-display mr-1"></i> Desktop
                            </button>
                            <button type="button" @click="previewDevice = 'mobile'" :class="previewDevice === 'mobile' ? 'bg-primary text-white' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg font-bold transition cursor-pointer">
                                <i class="bi bi-phone mr-1"></i> Mobile
                            </button>
                        </div>
                        <button type="button" @click="openPreview = false" class="text-slate-400 hover:text-white cursor-pointer"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <div class="p-4 sm:p-6 bg-slate-100 flex justify-center items-center" style="min-height: 540px;">
                    <div class="w-full transition-all duration-300" :style="previewDevice === 'mobile' ? 'max-width: 420px;' : 'max-width: 100%;'">
                        <iframe :src="previewUrl" class="w-full rounded-2xl shadow-md border border-slate-200 bg-white" style="height: 650px;" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-template').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.dataset.name;
            const form = this.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Template?',
                    text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Are you sure you want to delete "${name}"?`)) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
