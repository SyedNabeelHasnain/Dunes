@extends('layouts.app')

@push('schema')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Organization',
      '@id' => url('/') . '#organization',
      'name' => 'Dunes Discovery Tourism LLC',
      'alternateName' => 'Dunes Discovery Dubai',
      'url' => url('/'),
      'logo' => asset('images/logo.png'),
      'description' => $pageDesc,
      'telephone' => '+971502456056',
      'email' => 'info@dunesdiscoverytourism.com',
      'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Dubai',
        'addressCountry' => 'AE',
      ],
      'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => '5.0',
        'reviewCount' => '329',
        'bestRating' => '5.0',
        'worstRating' => '1.0',
      ],
    ],
    [
      '@type' => 'BreadcrumbList',
      '@id' => $canonical . '#breadcrumb',
      'itemListElement' => [
        [
          '@type' => 'ListItem',
          'position' => 1,
          'name' => __('ui.nav.home') ?? 'Home',
          'item' => localized_route('home'),
        ],
        [
          '@type' => 'ListItem',
          'position' => 2,
          'name' => 'Press Kit & Brand Assets',
          'item' => $canonical,
        ],
      ],
    ],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<!-- Hero Header Section -->
<section class="hero-subpage bg-slate-950 text-white relative overflow-hidden" style="padding-top: calc(var(--header-h, 72px) + 2.5rem);">
    <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_30%_25%,rgba(246,144,68,0.25)_0%,transparent_65%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-12 sm:pb-16">
        <nav aria-label="{{ __('ui.common.breadcrumb') ?? 'Breadcrumb' }}">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-white/70 mb-4">
                <li><a href="{{ localized_route('home') }}" class="hover:text-white transition-colors">{{ __('ui.nav.home') ?? 'Home' }}</a></li>
                <li><span class="text-white/40">/</span></li>
                <li class="text-white font-semibold" aria-current="page">Press Kit &amp; Media Resources</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="glass-dark text-white rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-newspaper text-amber-400"></i> Official Media Resources
                    </span>
                    <span class="bg-emerald-600/90 rounded-full px-3.5 py-1 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-patch-check-fill text-emerald-200"></i> DET Permit #{{ $facts['license_number'] }}
                    </span>
                    <span class="bg-primary/20 text-primary border border-primary/40 rounded-full px-3.5 py-1 text-xs font-semibold inline-flex items-center gap-1.5">
                        <i class="bi bi-star-fill text-amber-400"></i> 5.0 Star Verified Rating
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-3">
                    Press Kit &amp; Brand Assets
                </h1>
                <p class="text-sm sm:text-base text-white/80 leading-relaxed max-w-2xl">
                    Official company resources, high-resolution logos, brand identity guidelines, corporate fact sheet, and embeddable trust badges for travel journalists, partners, and media publications.
                </p>
            </div>

            <!-- Quick Jump Actions -->
            <div class="flex flex-wrap gap-2.5 shrink-0">
                <a href="#brand-assets" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all inline-flex items-center gap-2 border border-white/15">
                    <i class="bi bi-palette-fill text-amber-400"></i> Brand Assets
                </a>
                <a href="#fact-sheet" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all inline-flex items-center gap-2 border border-white/15">
                    <i class="bi bi-file-earmark-text-fill text-blue-400"></i> Fact Sheet
                </a>
                <a href="#partner-badges" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-[#e07b32] text-white text-xs font-bold transition-all inline-flex items-center gap-2 shadow-lg">
                    <i class="bi bi-shield-check"></i> Embed Partner Badges
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Main Container -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16">

    <!-- 1. Corporate Fact Sheet -->
    <section id="fact-sheet" class="scroll-mt-24">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-primary">Company Overview</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Official Fact Sheet</h2>
            </div>
            <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full font-medium">Updated for {{ $currentYear }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-building"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Legal Corporate Entity</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">{{ $facts['company_name'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Established {{ $facts['established_year'] }} in Dubai, UAE</div>
            </div>

            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Government Licensing</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">DET License #{{ $facts['license_number'] }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $facts['license_authority'] }}</div>
            </div>

            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Fleet &amp; Equipment</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">{{ $facts['fleet_size'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Roll-cage certified, full guest passenger insurance</div>
            </div>

            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Desert Operating Base</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">Lahbab Red Dunes</div>
                <div class="text-xs text-slate-500 mt-1">{{ $facts['primary_terrain'] }}</div>
            </div>

            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Verified Reputation</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">{{ $facts['rating_google'] }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $facts['rating_tripadvisor'] }} &bull; {{ $facts['total_reviews'] }}</div>
            </div>

            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-lg mb-3">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Media Contact &amp; Inquiries</div>
                <div class="text-base font-extrabold text-slate-900 mt-1">{{ $facts['contact_press'] }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $facts['phone'] }} (24/7 Desk)</div>
            </div>
        </div>
    </section>

    <!-- 2. Embeddable Partner Trust Badges -->
    <section id="partner-badges" class="scroll-mt-24" x-data="{ copiedId: null, copyEmbed(id, code) { navigator.clipboard.writeText(code).then(() => { this.copiedId = id; setTimeout(() => { this.copiedId = null; }, 2500); }); } }">
        <div class="mb-8">
            <span class="text-xs font-black uppercase tracking-wider text-primary">Web Embeds &amp; Badges</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Embeddable Partner &amp; Review Badges</h2>
            <p class="text-sm text-slate-600 mt-2 max-w-2xl">
                Hotels, travel concierges, bloggers, and booking partners can embed our official live badges on their websites. Copy and paste the snippet below directly into your HTML.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($badges as $badge)
            <div class="p-6 rounded-3xl border border-slate-200 bg-white shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Badge Preview Box -->
                    <div class="p-6 rounded-2xl bg-slate-100/70 border border-slate-200/80 flex items-center justify-center mb-5 min-h-[120px]">
                        <img src="{{ $badge['image'] }}" alt="{{ $badge['name'] }}" class="max-w-full h-auto drop-shadow-sm select-none" width="280" height="80">
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 mb-1">{{ $badge['name'] }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $badge['description'] }}</p>

                    <!-- Embed Code Area -->
                    <div class="relative bg-slate-950 rounded-xl p-3 mb-4">
                        <pre class="text-[11px] font-mono text-emerald-400 overflow-x-auto whitespace-pre-wrap break-all select-all">{{ $badge['embed_code'] }}</pre>
                    </div>
                </div>

                <!-- 1-Click Copy Button -->
                <button type="button" 
                        @click="copyEmbed('{{ $badge['id'] }}', '{{ addslashes($badge['embed_code']) }}')"
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                        :class="copiedId === '{{ $badge['id'] }}' ? 'bg-emerald-600 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white'">
                    <i :class="copiedId === '{{ $badge['id'] }}' ? 'bi bi-check2-all text-base' : 'bi bi-clipboard text-sm'"></i>
                    <span x-text="copiedId === '{{ $badge['id'] }}' ? 'Embed Code Copied!' : 'Copy HTML Embed Code'"></span>
                </button>
            </div>
            @endforeach
        </div>
    </section>

    <!-- 3. Brand Assets & Color Identity -->
    <section id="brand-assets" class="scroll-mt-24">
        <div class="mb-8">
            <span class="text-xs font-black uppercase tracking-wider text-primary">Visual Identity</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Official Brand Assets &amp; Palette</h2>
            <p class="text-sm text-slate-600 mt-2 max-w-2xl">
                Consistent typography and color tokens help ensure Dunes Discovery Tourism is presented accurately in external media and news features.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Logo Files -->
            <div class="p-6 rounded-3xl border border-slate-200 bg-white shadow-xs">
                <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="bi bi-image text-primary"></i> Official Logo Packages
                </h3>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-950 flex items-center justify-between gap-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Logo" class="h-10 w-auto object-contain" width="160" height="40">
                        <a href="{{ asset('images/logo.png') }}" download class="px-3.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all inline-flex items-center gap-1.5 shrink-0">
                            <i class="bi bi-download"></i> PNG (Hi-Res)
                        </a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Dunes Discovery Logo" class="h-10 w-auto object-contain" width="160" height="40">
                        <a href="{{ asset('images/favicon.png') }}" download class="px-3.5 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition-all inline-flex items-center gap-1.5 shrink-0">
                            <i class="bi bi-download"></i> App Icon / Favicon
                        </a>
                    </div>
                </div>

                <div class="mt-4 text-xs text-slate-500 leading-relaxed">
                    <strong>Usage Guideline:</strong> Please maintain clear space around the logo and avoid distorting, recoloring, or altering the emblem proportions.
                </div>
            </div>

            <!-- Brand Color Palette -->
            <div class="p-6 rounded-3xl border border-slate-200 bg-white shadow-xs" x-data="{ copiedColor: null, copyHex(hex) { navigator.clipboard.writeText(hex).then(() => { this.copiedColor = hex; setTimeout(() => { this.copiedColor = null; }, 2000); }); } }">
                <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="bi bi-palette text-primary"></i> Primary Color Palette
                </h3>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="copyHex('#F69044')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition-all cursor-pointer group">
                        <div class="h-10 rounded-xl mb-2.5 shadow-inner" style="background-color: #F69044;"></div>
                        <div class="text-xs font-bold text-slate-900">Desert Amber</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex justify-between items-center">
                            <span>#F69044</span>
                            <span class="text-[10px] text-primary group-hover:underline" x-text="copiedColor === '#F69044' ? 'Copied!' : 'Copy'"></span>
                        </div>
                    </button>

                    <button type="button" @click="copyHex('#020617')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition-all cursor-pointer group">
                        <div class="h-10 rounded-xl mb-2.5 shadow-inner" style="background-color: #020617;"></div>
                        <div class="text-xs font-bold text-slate-900">Midnight Sand</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex justify-between items-center">
                            <span>#020617</span>
                            <span class="text-[10px] text-primary group-hover:underline" x-text="copiedColor === '#020617' ? 'Copied!' : 'Copy'"></span>
                        </div>
                    </button>

                    <button type="button" @click="copyHex('#F59E0B')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition-all cursor-pointer group">
                        <div class="h-10 rounded-xl mb-2.5 shadow-inner" style="background-color: #F59E0B;"></div>
                        <div class="text-xs font-bold text-slate-900">Sahara Gold</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex justify-between items-center">
                            <span>#F59E0B</span>
                            <span class="text-[10px] text-primary group-hover:underline" x-text="copiedColor === '#F59E0B' ? 'Copied!' : 'Copy'"></span>
                        </div>
                    </button>

                    <button type="button" @click="copyHex('#10B981')" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition-all cursor-pointer group">
                        <div class="h-10 rounded-xl mb-2.5 shadow-inner" style="background-color: #10B981;"></div>
                        <div class="text-xs font-bold text-slate-900">Oasis Emerald</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex justify-between items-center">
                            <span>#10B981</span>
                            <span class="text-[10px] text-primary group-hover:underline" x-text="copiedColor === '#10B981' ? 'Copied!' : 'Copy'"></span>
                        </div>
                    </button>
                </div>

                <div class="mt-4 text-xs text-slate-500 leading-relaxed">
                    Click any swatch to instantly copy the hexadecimal color code to your clipboard.
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Editorial Media Downloads -->
    <section id="media-gallery" class="scroll-mt-24">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-primary">Press Photography</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">High-Resolution Editorial Media</h2>
            </div>
            <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full font-medium">Free Editorial Use with Attribution</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($editorialImages as $img)
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs flex flex-col justify-between group">
                <div class="relative aspect-4/3 bg-slate-100 overflow-hidden">
                    <img src="{{ asset('images/' . $img['filename']) }}" alt="{{ $img['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    <span class="absolute top-2.5 start-2.5 rounded-full bg-slate-950/80 backdrop-blur-xs px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                        {{ $img['category'] }}
                    </span>
                </div>
                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2 mb-1">{{ $img['title'] }}</h4>
                        <div class="text-[10px] text-slate-500 mb-3">{{ $img['credit'] }}</div>
                    </div>
                    <a href="{{ asset('images/' . $img['filename']) }}" download class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5">
                        <i class="bi bi-download"></i> Download Media
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- 5. Media & Partnership Contacts -->
    <section class="p-8 sm:p-10 rounded-3xl bg-slate-950 text-white relative overflow-hidden">
        <div class="absolute inset-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_20%,rgba(246,144,68,0.2)_0%,transparent_60%)] pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl">
            <span class="rounded-full px-3.5 py-1 text-xs font-bold bg-primary/20 border border-primary/40 text-primary inline-flex items-center gap-1.5 mb-3">
                <i class="bi bi-chat-heart-fill"></i> Media Partnerships &amp; PR
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
                Need an Interview, Quote, or Custom Visual Assets?
            </h2>
            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                Our operations team and desert safari specialists are available for travel journalism inquiries, TV documentary filming permits in Lahbab, and influencer partnerships.
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <a href="mailto:{{ $facts['contact_press'] }}" class="btn-desert-animated text-xs font-bold rounded-xl px-5 py-3 text-white inline-flex items-center gap-2 shadow-lg">
                    <i class="bi bi-envelope-fill"></i> Contact PR Team ({{ $facts['contact_press'] }})
                </a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $facts['phone']) }}?text={{ urlencode('Hello Dunes Discovery Media Team, I am reaching out regarding a press / editorial feature.') }}" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-emerald-600/90 hover:bg-emerald-600 text-white text-xs font-bold inline-flex items-center gap-2 transition-all shadow-md">
                    <i class="bi bi-whatsapp"></i> WhatsApp Press Desk
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
