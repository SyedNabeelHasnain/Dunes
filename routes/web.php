<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxGatewayController;

// ── Front-Facing Pages ────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
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
    Route::post('/tours/{id}/itinerary', [AdminTourController::class, 'addItinerary'])->name('tours.itinerary.add');
    Route::post('/itinerary/{id}/update', [AdminTourController::class, 'updateItinerary'])->name('tours.itinerary.update');
    Route::post('/itinerary/{id}/delete', [AdminTourController::class, 'deleteItinerary'])->name('tours.itinerary.delete');
    Route::post('/content-items/create', [AdminTourController::class, 'addContentItem'])->name('tours.content-items.create');
    Route::post('/tours/{id}/content', [AdminTourController::class, 'setTourContent'])->name('tours.content.set');
    Route::post('/categories/create', [AdminTourController::class, 'addCategory'])->name('categories.create');
    Route::post('/categories/rename', [AdminTourController::class, 'renameCategory'])->name('categories.rename');
    Route::get('/tiers', [AdminTourController::class, 'tiers'])->name('tiers.index');
    Route::get('/addons', [AdminTourController::class, 'addons'])->name('addons.index');
    Route::get('/pricing', [AdminTourController::class, 'pricing'])->name('pricing.index');
    Route::post('/pricing/update', [AdminTourController::class, 'updatePricing'])->name('pricing.update');
    
    // Bookings & WhatsApp Leads
    Route::resource('bookings', AdminBookingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('/bookings/{id}/payment-link', [AdminBookingController::class, 'createPaymentLink'])->name('bookings.payment-link');
    Route::post('/bookings/{id}/resend-payment', [AdminBookingController::class, 'resendPaymentEmail'])->name('bookings.resend-payment');
    Route::get('/whatsapp-leads', [AdminWhatsappController::class, 'index'])->name('whatsapp.leads');
    Route::get('/whatsapp-settings', [AdminWhatsappController::class, 'settings'])->name('whatsapp.settings');
    Route::post('/whatsapp-settings/update', [AdminWhatsappController::class, 'updateSettings'])->name('whatsapp.settings.update');
    
    // FAQs, Reviews, and Inquiries
    Route::resource('faqs', AdminFaqController::class)->except(['create', 'show', 'edit']);
    Route::resource('reviews', AdminReviewController::class)->except(['create', 'show', 'edit']);
    Route::get('/inquiries', [AdminDashboardController::class, 'inquiries'])->name('inquiries.index');
    Route::get('/inquiries/{id}', [AdminDashboardController::class, 'viewInquiry'])->name('inquiries.show');
    Route::post('/inquiries/{id}/status', [AdminDashboardController::class, 'updateInquiryStatus'])->name('inquiries.status');
    Route::delete('/inquiries/{id}', [AdminDashboardController::class, 'deleteInquiry'])->name('inquiries.destroy');
    
    // Blog CMS
    Route::resource('blogs', AdminBlogController::class)->except(['show']);
    Route::resource('blog-categories', AdminBlogCategoryController::class)->except(['create', 'show', 'edit']);

    // Legal Pages Manager
    Route::get('/legal', [AdminLegalController::class, 'index'])->name('legal.index');
    Route::get('/legal/{id}/edit', [AdminLegalController::class, 'edit'])->name('legal.edit');
    Route::post('/legal/{id}/update', [AdminLegalController::class, 'update'])->name('legal.update');
    Route::post('/legal/{id}/section/add', [AdminLegalController::class, 'addSection'])->name('legal.section.add');
    Route::post('/legal/section/{sectionId}/item/add', [AdminLegalController::class, 'addItem'])->name('legal.item.add');
    Route::delete('/legal/section/{id}', [AdminLegalController::class, 'deleteSection'])->name('legal.section.delete');
    Route::delete('/legal/item/{id}', [AdminLegalController::class, 'deleteItem'])->name('legal.item.delete');

    // Integrations Settings (Google, Meta, Cache)
    Route::get('/settings/google', [AdminSettingController::class, 'google'])->name('settings.google');
    Route::get('/settings/meta', [AdminSettingController::class, 'meta'])->name('settings.meta');
    Route::post('/settings/update', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::get('/clear-cache', [AdminSettingController::class, 'clearCache'])->name('clear-cache');
});

// ── Profile routes (Breeze default) ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ── Legacy AJAX Gateway Route (Throttled for Security) ───────────────────────
Route::post('/ajax.php', [AjaxGatewayController::class, 'handle'])->middleware('throttle:30,1');

// ── Dynamic XML Sitemap & Image Sitemap ──────────────────────────────────────
Route::get('/sitemap.xml', function() {
    $tours = \App\Models\Tour::where('status', 'active')->select('slug', 'name', 'hero_image', 'updated_at')->get();
    $blogs = \App\Models\BlogPost::where('status', 'published')->select('slug', 'title', 'featured_image', 'updated_at')->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    $staticPages = ['', '/about', '/contact', '/faq', '/tours', '/blog', '/terms-condition', '/privacy-policy'];
    foreach ($staticPages as $page) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . url($page) . '</loc>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>' . ($page === '' ? '1.0' : '0.8') . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    foreach ($tours as $tour) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . url('/' . $tour->slug) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . ($tour->updated_at ? $tour->updated_at->toAtomString() : now()->toAtomString()) . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>0.9</priority>' . "\n";
        if (!empty($tour->hero_image)) {
            $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
            $xml .= '    <image:image>' . "\n";
            $xml .= '      <image:loc>' . asset('images/blog/' . $imgFile) . '</image:loc>' . "\n";
            $xml .= '      <image:title>' . htmlspecialchars($tour->name) . '</image:title>' . "\n";
            $xml .= '    </image:image>' . "\n";
        }
        $xml .= '  </url>' . "\n";
    }

    foreach ($blogs as $blog) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . url('/blog/' . $blog->slug) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . ($blog->updated_at ? $blog->updated_at->toAtomString() : now()->toAtomString()) . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.7</priority>' . "\n";
        if (!empty($blog->featured_image)) {
            $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
            $xml .= '    <image:image>' . "\n";
            $xml .= '      <image:loc>' . asset('images/blog/' . $imgFile) . '</image:loc>' . "\n";
            $xml .= '      <image:title>' . htmlspecialchars($blog->title) . '</image:title>' . "\n";
            $xml .= '    </image:image>' . "\n";
        }
        $xml .= '  </url>' . "\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'text/xml']);
})->name('sitemap');

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
    return response("# Dunes Discovery Tourism\nhttps://dunesdiscoverytourism.com\n- Licensed Dubai Tour Operator\n- 1200+ 5-Star Reviews\n- Instant Booking: https://wa.me/971502456056", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
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
    return response("# Dunes Discovery Tourism Full Specifications\nhttps://dunesdiscoverytourism.com\n- Full Tour Catalog & Pricing Tiers available at https://dunesdiscoverytourism.com/tours", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
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

// ── Auth Routes (Login, Register, Password Reset) ────────────────────────────
require __DIR__.'/auth.php';

// ── Root-level Dynamic Tour Slugs (Fallback Route) ───────────────────────────
Route::get('/{slug}', [TourController::class, 'show'])->name('tours.show');
