@extends('layouts.admin')

@section('page_title', 'Legal Pages & Policy Manager')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Legal & Policy Pages</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage Terms & Conditions, Privacy Policy content, and legal disclosures.</p>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($pages as $page)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-primary/10 text-primary border border-primary/20">/{{ $page->slug }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ $page->sections_count }} Sections</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 mb-1.5">{{ $page->title }}</h2>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">{{ $page->subtitle ?: 'Standard site legal agreement disclosure.' }}</p>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                <a href="{{ url('/' . $page->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                    <i class="bi bi-box-arrow-up-right text-slate-400"></i> Preview Live
                </a>
                <a href="{{ route('admin.legal.edit', $page->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition">
                    <i class="bi bi-pencil-square"></i> Edit Content
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
