@extends('layouts.admin')

@section('page_title', 'SEO & Metadata Management')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-slate-200 bg-slate-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="bi bi-search"></i>
            </div>
            <div>
                <h5 class="text-base font-extrabold text-slate-900 leading-tight">SEO & Metadata Management</h5>
                <div class="text-xs text-slate-500 mt-0.5">Configure global default metadata and custom per-page SEO titles, descriptions, keywords, and OpenGraph social share cards.</div>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- Alpine Nav Tabs -->
            <div x-data="{ activeTab: 'default' }">
                <div class="flex items-center gap-1.5 p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/80 mb-6 overflow-x-auto">
                    <button type="button" @click="activeTab = 'default'" :class="activeTab === 'default' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="default-tab">
                        Global Defaults
                    </button>
                    <button type="button" @click="activeTab = 'home'" :class="activeTab === 'home' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="home-tab">
                        Homepage
                    </button>
                    <button type="button" @click="activeTab = 'tours'" :class="activeTab === 'tours' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="tours-tab">
                        Tours Catalog
                    </button>
                    <button type="button" @click="activeTab = 'blog'" :class="activeTab === 'blog' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="blog-tab">
                        Blog Catalog
                    </button>
                    <button type="button" @click="activeTab = 'about'" :class="activeTab === 'about' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="about-tab">
                        About Us
                    </button>
                    <button type="button" @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="contact-tab">
                        Contact Us
                    </button>
                    <button type="button" @click="activeTab = 'faq'" :class="activeTab === 'faq' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="faq-tab">
                        FAQ Page
                    </button>
                    <button type="button" @click="activeTab = 'rate-card'" :class="activeTab === 'rate-card' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0" id="rate-card-tab">
                        Rate Card
                    </button>
                </div>

                <div id="seoTabsContent">
                    <!-- 1. Global Defaults -->
                    <div x-show="activeTab === 'default'" id="tab-default">
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-globe"></i> Global Fallback SEO Settings
                                </h6>
                                <span class="rounded-full border border-primary/20 bg-amber-500/10 px-3 py-1 text-[11px] font-bold text-primary">Sitewide Fallback</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-4">Applied across all general pages whenever specific page meta is not provided.</p>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_default_title" class="block text-xs font-bold text-slate-700">Default Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_default_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_default_title" id="seo_default_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_default_title'] ?? '' }}" placeholder="Dunes Discovery Tourism | Premium Dubai Desert Safari Tours">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_default_description" class="block text-xs font-bold text-slate-700">Default Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_default_description">0 / 160</span>
                                </div>
                                <textarea name="seo_default_description" id="seo_default_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" placeholder="Experience Dubai's premier desert safari adventures...">{{ $settings['seo_default_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_default_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Default Meta Keywords</label>
                                    <input type="text" name="seo_default_keywords" id="seo_default_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_default_keywords'] ?? '' }}" placeholder="desert safari dubai, dunes discovery, evening safari">
                                </div>
                                <div>
                                    <label for="seo_default_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Default OpenGraph Image URL</label>
                                    <input type="text" name="seo_default_og_image" id="seo_default_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_default_og_image'] ?? '' }}" placeholder="/images/og-default.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Homepage SEO -->
                    <div x-show="activeTab === 'home'" id="tab-home" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-house"></i> Homepage SEO Settings (/)
                                </h6>
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">High Priority</span>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_home_title" class="block text-xs font-bold text-slate-700">Homepage Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_home_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_home_title" id="seo_home_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_home_title'] ?? '' }}" placeholder="Dunes Discovery Tourism | Dubai Desert Safari & Adventure Tours 2026">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_home_description" class="block text-xs font-bold text-slate-700">Homepage Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_home_description">0 / 160</span>
                                </div>
                                <textarea name="seo_home_description" id="seo_home_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_home_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_home_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Homepage Meta Keywords</label>
                                    <input type="text" name="seo_home_keywords" id="seo_home_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_home_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_home_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Homepage OpenGraph Image</label>
                                    <input type="text" name="seo_home_og_image" id="seo_home_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_home_og_image'] ?? '' }}" placeholder="/images/og-home.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Tours Catalog SEO -->
                    <div x-show="activeTab === 'tours'" id="tab-tours" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-compass"></i> Tours Catalog SEO (/tours)
                                </h6>
                                <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[11px] font-bold text-sky-700">Commercial Catalog</span>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_tours_title" class="block text-xs font-bold text-slate-700">Tours Catalog Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_tours_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_tours_title" id="seo_tours_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_tours_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_tours_description" class="block text-xs font-bold text-slate-700">Tours Catalog Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_tours_description">0 / 160</span>
                                </div>
                                <textarea name="seo_tours_description" id="seo_tours_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_tours_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_tours_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Tours Catalog Keywords</label>
                                    <input type="text" name="seo_tours_keywords" id="seo_tours_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_tours_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_tours_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Tours Catalog OG Image</label>
                                    <input type="text" name="seo_tours_og_image" id="seo_tours_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_tours_og_image'] ?? '' }}" placeholder="/images/og-tours.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Blog Catalog SEO -->
                    <div x-show="activeTab === 'blog'" id="tab-blog" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                <h6 class="text-xs font-black uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="bi bi-journal-text"></i> Blog Catalog SEO (/blog)
                                </h6>
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">E-E-A-T Content</span>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_blog_title" class="block text-xs font-bold text-slate-700">Blog Catalog Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_blog_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_blog_title" id="seo_blog_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_blog_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_blog_description" class="block text-xs font-bold text-slate-700">Blog Catalog Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_blog_description">0 / 160</span>
                                </div>
                                <textarea name="seo_blog_description" id="seo_blog_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_blog_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_blog_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Blog Catalog Keywords</label>
                                    <input type="text" name="seo_blog_keywords" id="seo_blog_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_blog_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_blog_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Blog Catalog OG Image</label>
                                    <input type="text" name="seo_blog_og_image" id="seo_blog_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_blog_og_image'] ?? '' }}" placeholder="/images/og-blog.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. About Us SEO -->
                    <div x-show="activeTab === 'about'" id="tab-about" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3 flex items-center gap-2">
                                <i class="bi bi-info-circle"></i> About Us SEO (/about)
                            </h6>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_about_title" class="block text-xs font-bold text-slate-700">About Us Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_about_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_about_title" id="seo_about_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_about_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_about_description" class="block text-xs font-bold text-slate-700">About Us Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_about_description">0 / 160</span>
                                </div>
                                <textarea name="seo_about_description" id="seo_about_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_about_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_about_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">About Us Keywords</label>
                                    <input type="text" name="seo_about_keywords" id="seo_about_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_about_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_about_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">About Us OG Image</label>
                                    <input type="text" name="seo_about_og_image" id="seo_about_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_about_og_image'] ?? '' }}" placeholder="/images/og-about.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Contact Us SEO -->
                    <div x-show="activeTab === 'contact'" id="tab-contact" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3 flex items-center gap-2">
                                <i class="bi bi-chat-dots"></i> Contact Us SEO (/contact)
                            </h6>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_contact_title" class="block text-xs font-bold text-slate-700">Contact Us Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_contact_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_contact_title" id="seo_contact_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_contact_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_contact_description" class="block text-xs font-bold text-slate-700">Contact Us Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_contact_description">0 / 160</span>
                                </div>
                                <textarea name="seo_contact_description" id="seo_contact_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_contact_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_contact_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Contact Us Keywords</label>
                                    <input type="text" name="seo_contact_keywords" id="seo_contact_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_contact_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_contact_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Contact Us OG Image</label>
                                    <input type="text" name="seo_contact_og_image" id="seo_contact_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_contact_og_image'] ?? '' }}" placeholder="/images/og-contact.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. FAQ SEO -->
                    <div x-show="activeTab === 'faq'" id="tab-faq" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3 flex items-center gap-2">
                                <i class="bi bi-question-circle"></i> FAQ SEO (/faq)
                            </h6>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_faq_title" class="block text-xs font-bold text-slate-700">FAQ Page Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_faq_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_faq_title" id="seo_faq_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_faq_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_faq_description" class="block text-xs font-bold text-slate-700">FAQ Page Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_faq_description">0 / 160</span>
                                </div>
                                <textarea name="seo_faq_description" id="seo_faq_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_faq_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_faq_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">FAQ Keywords</label>
                                    <input type="text" name="seo_faq_keywords" id="seo_faq_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_faq_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_faq_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">FAQ OG Image</label>
                                    <input type="text" name="seo_faq_og_image" id="seo_faq_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_faq_og_image'] ?? '' }}" placeholder="/images/og-faq.jpg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 8. Rate Card SEO -->
                    <div x-show="activeTab === 'rate-card'" id="tab-rate-card" x-cloak>
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                            <h6 class="text-xs font-black uppercase tracking-wider text-primary mb-3 flex items-center gap-2">
                                <i class="bi bi-tag"></i> Rate Card & Pricing Guide SEO (/rate-card)
                            </h6>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_rate_card_title" class="block text-xs font-bold text-slate-700">Rate Card Meta Title</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_rate_card_title">0 / 60</span>
                                </div>
                                <input type="text" name="seo_rate_card_title" id="seo_rate_card_title" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input" value="{{ $settings['seo_rate_card_title'] ?? '' }}">
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="seo_rate_card_description" class="block text-xs font-bold text-slate-700">Rate Card Meta Description</label>
                                    <span class="text-[11px] text-slate-400 char-count" data-target="seo_rate_card_description">0 / 160</span>
                                </div>
                                <textarea name="seo_rate_card_description" id="seo_rate_card_description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary seo-input">{{ $settings['seo_rate_card_description'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="seo_rate_card_keywords" class="block text-xs font-bold text-slate-700 mb-1.5">Rate Card Keywords</label>
                                    <input type="text" name="seo_rate_card_keywords" id="seo_rate_card_keywords" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_rate_card_keywords'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="seo_rate_card_og_image" class="block text-xs font-bold text-slate-700 mb-1.5">Rate Card OG Image</label>
                                    <input type="text" name="seo_rate_card_og_image" id="seo_rate_card_og_image" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 outline-hidden transition focus:border-primary focus:ring-1 focus:ring-primary" value="{{ $settings['seo_rate_card_og_image'] ?? '' }}" placeholder="/images/og-rate-card.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#e07b32] transition">
                    <i class="bi bi-check2-circle"></i> Save SEO Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCount(input) {
        const targetId = input.id;
        const countSpan = document.querySelector(`.char-count[data-target="${targetId}"]`);
        if (countSpan) {
            const max = targetId.includes('title') ? 60 : 160;
            const len = input.value.length;
            countSpan.textContent = `${len} / ${max}`;
            if (len > max) {
                countSpan.classList.add('text-rose-600', 'font-bold');
                countSpan.classList.remove('text-slate-400');
            } else {
                countSpan.classList.remove('text-rose-600', 'font-bold');
                countSpan.classList.add('text-slate-400');
            }
        }
    }

    document.querySelectorAll('.seo-input').forEach(function(input) {
        updateCount(input);
        input.addEventListener('input', function() {
            updateCount(this);
        });
    });
});
</script>
@endpush
@endsection
