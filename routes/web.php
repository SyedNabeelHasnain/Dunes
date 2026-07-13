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
use Illuminate\Support\Facades\Route;

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
    Route::get('/active-visitors', [AdminDashboardController::class, 'activeVisitors'])->name('active-visitors');
    Route::post('/quick-payment', [AdminDashboardController::class, 'createQuickPayment'])->name('quick-payment');
    
    // Tours, Tiers, Addons, and Pricing
    Route::resource('tours', AdminTourController::class)->except(['show']);
    Route::post('/tours/{id}/itinerary', [AdminTourController::class, 'addItinerary'])->name('tours.itinerary.add');
    Route::post('/itinerary/{id}/update', [AdminTourController::class, 'updateItinerary'])->name('tours.itinerary.update');
    Route::post('/itinerary/{id}/delete', [AdminTourController::class, 'deleteItinerary'])->name('tours.itinerary.delete');
    Route::post('/content-items/create', [AdminTourController::class, 'addContentItem'])->name('tours.content-items.create');
    Route::post('/tours/{id}/content', [AdminTourController::class, 'setTourContent'])->name('tours.content.set');
    Route::post('/categories/create', [AdminTourController::class, 'addCategory'])->name('tours.categories.create');
    Route::post('/categories/rename', [AdminTourController::class, 'renameCategory'])->name('tours.categories.rename');
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

// ── Database Initialization Route (Triggered once after deployment) ─────────
Route::get('/system/init-db', function() {
    if (request()->input('key') !== 'dunes2026') {
        abort(404);
    }
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return 'Database initialized and seeded successfully!';
});

// ── Root-level Dynamic Tour Slugs (Fallback Route) ───────────────────────────
Route::get('/{slug}', [TourController::class, 'show'])->name('tours.show');
