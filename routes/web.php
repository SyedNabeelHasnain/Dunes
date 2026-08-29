<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RateCardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTourController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminBlogCategoryController;
use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminWhatsappController;
use App\Http\Controllers\Admin\AdminLegalController;
use App\Http\Controllers\Admin\AdminCouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxGatewayController;

// ── Front-Facing Pages ────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/rate-card', [RateCardController::class, 'index'])->name('rate-card');
Route::get('/pricing-guide', [RateCardController::class, 'index'])->name('pricing-guide');
Route::get('/terms-condition', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
Route::redirect('/dashboard', '/admin')->name('dashboard');

Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/thankyou', [BookingController::class, 'thankyou'])->name('booking.thankyou');
Route::get('/payment-cancel', [BookingController::class, 'paymentCancel'])->name('booking.cancel');

// ── Admin CMS Panel (Guarded by auth) ──────────────────────────────────────────
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.alias');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics.index');
    Route::get('/active-visitors', [AdminDashboardController::class, 'activeVisitors'])->name('active-visitors');
    Route::post('/quick-payment', [AdminDashboardController::class, 'createQuickPayment'])->name('quick-payment');
    
    // Tours, Tiers, Addons, and Pricing
    Route::resource('tours', AdminTourController::class)->except(['show']);
    Route::post('/tours/{id}/toggle-status', [AdminTourController::class, 'toggleStatus'])->name('tours.toggle-status');
    Route::post('/tours/{id}/itinerary', [AdminTourController::class, 'addItinerary'])->name('tours.itinerary.add');
    Route::post('/itinerary/{id}/update', [AdminTourController::class, 'updateItinerary'])->name('tours.itinerary.update');
    Route::post('/itinerary/{id}/delete', [AdminTourController::class, 'deleteItinerary'])->name('tours.itinerary.delete');
    Route::post('/content-items/create', [AdminTourController::class, 'addContentItem'])->name('tours.content-items.create');
    Route::post('/tours/{id}/content', [AdminTourController::class, 'setTourContent'])->name('tours.content.set');
    Route::post('/categories/create', [AdminTourController::class, 'addCategory'])->name('categories.create');
    Route::post('/categories/rename', [AdminTourController::class, 'renameCategory'])->name('categories.rename');
    Route::get('/tiers', [AdminTourController::class, 'tiers'])->name('tiers.index');
    Route::post('/tiers', [AdminTourController::class, 'storeTier'])->name('tiers.store');
    Route::post('/tiers/{id}/update', [AdminTourController::class, 'updateTier'])->name('tiers.update');
    Route::delete('/tiers/{id}', [AdminTourController::class, 'deleteTier'])->name('tiers.destroy');

    Route::get('/addons', [AdminTourController::class, 'addons'])->name('addons.index');
    Route::post('/addons', [AdminTourController::class, 'storeAddon'])->name('addons.store');
    Route::post('/addons/{id}/update', [AdminTourController::class, 'updateAddon'])->name('addons.update');
    Route::delete('/addons/{id}', [AdminTourController::class, 'deleteAddon'])->name('addons.destroy');

    Route::get('/pricing', [AdminTourController::class, 'pricing'])->name('pricing.index');
    Route::post('/pricing/update', [AdminTourController::class, 'updatePricing'])->name('pricing.update');
    
    // Bookings & WhatsApp Leads
    Route::get('/bookings/export/csv', [AdminBookingController::class, 'exportCsv'])->name('bookings.export');
    Route::resource('bookings', AdminBookingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('/bookings/{id}/payment-link', [AdminBookingController::class, 'createPaymentLink'])->name('bookings.payment-link');
    Route::post('/bookings/{id}/resend-payment', [AdminBookingController::class, 'resendPaymentEmail'])->name('bookings.resend-payment');
    Route::get('/whatsapp-leads/export/csv', [AdminWhatsappController::class, 'exportCsv'])->name('whatsapp.export');
    Route::get('/whatsapp-leads', [AdminWhatsappController::class, 'index'])->name('whatsapp.leads');
    Route::get('/whatsapp', [AdminWhatsappController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/leads', [AdminWhatsappController::class, 'index'])->name('whatsapp.leads.alias');
    Route::get('/whatsapp-settings', [AdminWhatsappController::class, 'settings'])->name('whatsapp.settings');
    Route::post('/whatsapp-settings/update', [AdminWhatsappController::class, 'updateSettings'])->name('whatsapp.settings.update');
    
    // FAQs, Reviews, and Inquiries
    Route::resource('faqs', AdminFaqController::class)->except(['create', 'show', 'edit']);
    Route::post('/faqs/{id}/toggle-status', [AdminFaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
    Route::resource('reviews', AdminReviewController::class)->except(['create', 'show', 'edit']);
    Route::post('/reviews/{id}/toggle-status', [AdminReviewController::class, 'toggleStatus'])->name('reviews.toggle-status');
    Route::get('/inquiries/export/csv', [AdminDashboardController::class, 'exportInquiriesCsv'])->name('inquiries.export');
    Route::get('/inquiries', [AdminDashboardController::class, 'inquiries'])->name('inquiries.index');
    Route::get('/inquiries/{id}', [AdminDashboardController::class, 'viewInquiry'])->name('inquiries.show');
    Route::post('/inquiries/{id}/status', [AdminDashboardController::class, 'updateInquiryStatus'])->name('inquiries.status');
    Route::delete('/inquiries/{id}', [AdminDashboardController::class, 'deleteInquiry'])->name('inquiries.destroy');
    
    // Blog CMS
    Route::resource('blogs', AdminBlogController::class)->except(['show']);
    Route::post('/blogs/{id}/toggle-status', [AdminBlogController::class, 'toggleStatus'])->name('blogs.toggle-status');
    Route::resource('blog-categories', AdminBlogCategoryController::class)->except(['create', 'show', 'edit']);

    // Coupons & Promo Codes
    Route::get('/coupons/popup-settings', [AdminCouponController::class, 'popupSettings'])->name('coupons.popup-settings');
    Route::post('/coupons/popup-settings', [AdminCouponController::class, 'updatePopupSettings'])->name('coupons.popup-settings.update');
    Route::get('/coupons/export/csv', [AdminCouponController::class, 'exportCsv'])->name('coupons.export');
    Route::post('/coupons/{id}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    Route::post('/coupons/{id}/duplicate', [AdminCouponController::class, 'duplicate'])->name('coupons.duplicate');
    Route::get('/coupons/{id}/usages', [AdminCouponController::class, 'usages'])->name('coupons.usages');
    Route::resource('coupons', AdminCouponController::class);

    // Legal Pages Manager
    Route::get('/legal-pages', [AdminLegalController::class, 'index'])->name('legal.index');
    Route::get('/legal', function() { return redirect()->route('admin.legal.index'); })->name('legal.alias');
    Route::get('/legal-pages/{id}/edit', [AdminLegalController::class, 'edit'])->name('legal.edit');
    Route::post('/legal-pages/{id}/update', [AdminLegalController::class, 'update'])->name('legal.update');
    Route::post('/legal-pages/{id}/section/add', [AdminLegalController::class, 'addSection'])->name('legal.section.add');
    Route::post('/legal-pages/section/{sectionId}/item/add', [AdminLegalController::class, 'addItem'])->name('legal.item.add');
    Route::delete('/legal-pages/section/{id}', [AdminLegalController::class, 'deleteSection'])->name('legal.section.delete');
    Route::delete('/legal-pages/item/{id}', [AdminLegalController::class, 'deleteItem'])->name('legal.item.delete');

    // Admin Profile & Security
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

    // Integrations Settings (Google, Meta, Cache)
    Route::get('/settings/google', [AdminSettingController::class, 'google'])->name('settings.google');
    Route::get('/settings/meta', [AdminSettingController::class, 'meta'])->name('settings.meta');
    Route::post('/settings/update', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::match(['get', 'post'], '/clear-cache', [AdminSettingController::class, 'clearCache'])->name('clear-cache');
    Route::match(['get', 'post'], '/run-migrations', [AdminSettingController::class, 'runMigrations'])->name('run-migrations');
});

// ── Profile routes (Breeze default) ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ── Legacy AJAX Gateway Route (Throttled for Security) ───────────────────────
Route::match(['get', 'post'], '/ajax.php', [AjaxGatewayController::class, 'handle'])->middleware('throttle:60,1');

// ── Dynamic XML Sitemap & Image Sitemap ──────────────────────────────────────
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// ── AI Search Engine & LLM Markdown Endpoints (GEO Optimization) ─────────────
Route::get('/llms.txt', function () {
    $paths = [
        public_path('llms.txt'),
        base_path('../public_html/llms.txt'),
        base_path('public/llms.txt')
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            return response(file_get_contents($p), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
        }
    }
    return response("# Dunes Discovery Tourism\n\n> Licensed UAE Destination Management Company.\n\n## Core Tours\n\n- [Tour Catalog](https://dunesdiscoverytourism.com/tours): Full catalog of desert safaris, dune buggy rentals and city tours.\n- [Dune Buggy Rental](https://dunesdiscoverytourism.com/dune-buggy-rental-dubai): Self-drive 1000cc dune buggy rentals.\n- [Contact Us](https://dunesdiscoverytourism.com/contact): Direct booking and customer support.", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
});

Route::get('/llms-full.txt', function () {
    $paths = [
        public_path('llms-full.txt'),
        base_path('../public_html/llms-full.txt'),
        base_path('public/llms-full.txt')
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            return response(file_get_contents($p), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
        }
    }
    return response("# Dunes Discovery Tourism Full Specifications\n\n> Comprehensive guide for LLM search engines.\n\n## Core Links\n\n- [Tour Catalog](https://dunesdiscoverytourism.com/tours): Full pricing and tours.\n- [XML Sitemap](https://dunesdiscoverytourism.com/sitemap.xml): Full XML sitemap.", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
});

// ── Explicit High-Value Tour Routes ─────────────────────────────────────────
Route::get('/dune-buggy-rental-dubai', [TourController::class, 'showBuggy'])->name('tours.buggy');

// ── SEO 301 Permanent Redirects for Legacy / Shorthand Tour Slugs ───────────
Route::redirect('/dubai-marina-dhow-cruise', '/dhow-cruise-catamaran-cruise-dinner-dubai', 301);
Route::redirect('/ocean-empress-dhow-cruise', '/dhow-cruise-catamaran-cruise-dinner-dubai', 301);
Route::redirect('/abu-dhabi-city-tour', '/abu-dhabi-city-tour-from-dubai', 301);
Route::redirect('/quad-bike-tour-dubai', '/desert-safari-quad-biking-dubai', 301);
Route::redirect('/vip-desert-safari-dubai', '/evening-desert-safari-dubai', 301);
Route::redirect('/buggy-tour-dubai', '/dune-buggy-rental-dubai', 301);

// ── Root-level Dynamic Tour Slugs (Fallback Route) ───────────────────────────
Route::get('/{slug}', [TourController::class, 'show'])->name('tours.show');
