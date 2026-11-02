@php
    $channel = trim((string) ($channel ?? 'Direct'));
    $cLower = strtolower($channel);
@endphp

@if(str_contains($cLower, 'google ads'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs" title="Google Ads / Paid Search">
        <i class="bi bi-google text-amber-600"></i> Google Ads
    </span>
@elseif(str_contains($cLower, 'meta ads') || str_contains($cLower, 'facebook ads') || str_contains($cLower, 'instagram ads'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-300 shadow-2xs" title="Meta / Facebook / Instagram Ads">
        <i class="bi bi-meta text-blue-600"></i> Meta Ads
    </span>
@elseif(str_contains($cLower, 'tiktok'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white shadow-2xs" title="TikTok Campaign">
        <i class="bi bi-tiktok text-rose-400"></i> TikTok Ads
    </span>
@elseif(str_contains($cLower, 'bing'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-900 border border-cyan-300 shadow-2xs" title="Microsoft / Bing Ads">
        <i class="bi bi-microsoft text-cyan-700"></i> Bing Ads
    </span>
@elseif(str_contains($cLower, 'social'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200" title="Organic Social Media">
        <i class="bi bi-share-fill text-pink-600"></i> Social Organic
    </span>
@elseif(str_contains($cLower, 'organic'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200" title="Organic Search Engine">
        <i class="bi bi-search text-emerald-600"></i> Organic Search
    </span>
@elseif(str_contains($cLower, 'email'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200" title="Email Marketing / Newsletter">
        <i class="bi bi-envelope-fill text-indigo-600"></i> Email
    </span>
@elseif(str_contains($cLower, 'referral'))
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200" title="External Referral Website">
        <i class="bi bi-link-45deg text-purple-600"></i> Referral
    </span>
@else
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200" title="Direct Visitor / Bookmark">
        <i class="bi bi-globe2 text-slate-500"></i> Direct
    </span>
@endif
