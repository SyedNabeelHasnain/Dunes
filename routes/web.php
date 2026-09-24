<?php

use App\Http\Controllers\Admin\AdminBlogCategoryController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEmailCampaignController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\Admin\AdminLegalController;
use App\Http\Controllers\Admin\AdminMailSettingController;
use App\Http\Controllers\Admin\AdminOperationsController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSubscriberController;
use App\Http\Controllers\Admin\AdminSubscriberGroupController;
use App\Http\Controllers\Admin\AdminTourController;
use App\Http\Controllers\Admin\AdminWhatsappController;
use App\Http\Controllers\AjaxGatewayController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LlmsController;
use App\Http\Controllers\LocationLandingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RateCardController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\VoucherController;
use App\Http\Middleware\AdminNoCacheMiddleware;
use Illuminate\Support\Facades\Route;

// ── Front-Facing Pages ────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/rate-card', [RateCardController::class, 'index'])->name('rate-card');
Route::redirect('/pricing-guide', '/rate-card', 301);
// ── Legal, Privacy & Compliance Policies ──────────────────────────────────
Route::get('/terms-condition', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/cookie-policy', [LegalController::class, 'cookies'])->name('cookies');
Route::get('/cancellation-refund-policy', [LegalController::class, 'cancellation'])->name('cancellation');
Route::get('/payment-security-policy', [LegalController::class, 'paymentSecurity'])->name('payment.security');
Route::get('/safety-liability-waiver', [LegalController::class, 'safetyWaiver'])->name('safety.waiver');
Route::get('/ai-editorial-policy', [LegalController::class, 'aiEditorial'])->name('ai.editorial');
Route::get('/responsible-tourism-policy', [LegalController::class, 'responsibleTourism'])->name('responsible.tourism');

// Aliases & 301 redirects for legal routes
Route::redirect('/terms', '/terms-condition', 301);
Route::redirect('/terms-and-conditions', '/terms-condition', 301);
Route::redirect('/terms-conditions', '/terms-condition', 301);
Route::redirect('/privacy', '/privacy-policy', 301);
Route::redirect('/cookies', '/cookie-policy', 301);
Route::redirect('/refund-policy', '/cancellation-refund-policy', 301);
Route::redirect('/cancellation-policy', '/cancellation-refund-policy', 301);
Route::redirect('/payment-policy', '/payment-security-policy', 301);
Route::redirect('/payment-security', '/payment-security-policy', 301);
Route::redirect('/security-policy', '/payment-security-policy', 301);
Route::redirect('/waiver', '/safety-liability-waiver', 301);
Route::redirect('/safety-waiver', '/safety-liability-waiver', 301);
Route::redirect('/safety-policy', '/safety-liability-waiver', 301);
Route::redirect('/ai-policy', '/ai-editorial-policy', 301);
Route::redirect('/editorial-policy', '/ai-editorial-policy', 301);
Route::redirect('/sustainability', '/responsible-tourism-policy', 301);
Route::redirect('/responsible-tourism', '/responsible-tourism-policy', 301);

Route::redirect('/dashboard', '/admin')->name('dashboard');

Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/search', [TourController::class, 'search'])->name('tours.search');
Route::get('/build-your-own-safari', [TourController::class, 'customizer'])->name('tours.customizer');
Route::redirect('/custom-safari', '/build-your-own-safari', 301);
Route::get('/tours/{slug}', function ($slug) {
    return redirect('/'.$slug, 301);
});
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/thankyou', [BookingController::class, 'thankyou'])->name('booking.thankyou');
Route::get('/payment-cancel', [BookingController::class, 'paymentCancel'])->name('booking.cancel');
Route::post('/booking/draft', [BookingController::class, 'saveDraft'])->name('booking.draft');
Route::get('/booking/{reference}/voucher', [VoucherController::class, 'show'])->name('booking.voucher');
Route::get('/booking/{reference}/ticket-pdf', [VoucherController::class, 'downloadPdf'])->name('booking.ticket.pdf');
Route::get('/booking/{reference}/voucher-pdf', [VoucherController::class, 'downloadPdf'])->name('booking.voucher.pdf');
Route::get('/review/{ref}', [PageController::class, 'reviewRate'])->name('review.rate');
Route::post('/review/{ref}', [PageController::class, 'submitReview'])->name('review.submit');
Route::post('/review/{ref}/feedback', [PageController::class, 'submitFeedback'])->name('review.feedback');

// ── Admin CMS Panel (Guarded by auth & strict no-cache headers) ──────────────
Route::middleware(['auth', AdminNoCacheMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.alias');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics.index');
    Route::get('/active-visitors', [AdminDashboardController::class, 'activeVisitors'])->name('active-visitors');
    Route::get('/api/kpis', [AdminDashboardController::class, 'liveKpis'])->name('api.kpis');
    Route::get('/api/bookings-stats', [AdminBookingController::class, 'liveStats'])->name('api.bookings.stats');
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
    Route::post('/bookings/bulk-action', [AdminBookingController::class, 'bulkAction'])->name('bookings.bulk');
    Route::resource('bookings', AdminBookingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('/bookings/{id}/payment-link', [AdminBookingController::class, 'createPaymentLink'])->name('bookings.payment-link');
    Route::post('/bookings/{id}/resend-payment', [AdminBookingController::class, 'resendPaymentEmail'])->name('bookings.resend-payment');
    Route::get('/bookings/{id}/ticket', [AdminBookingController::class, 'downloadTicket'])->name('bookings.ticket');

    // Daily Tour Operations & Driver Dispatch Manifest
    Route::get('/operations', [AdminOperationsController::class, 'index'])->name('operations.index');
    Route::post('/operations/{id}/assign-driver', [AdminOperationsController::class, 'assignDriver'])->name('operations.assign-driver');
    Route::get('/operations/export/csv', [AdminOperationsController::class, 'exportManifest'])->name('operations.export');

    Route::get('/whatsapp-leads/export/csv', [AdminWhatsappController::class, 'exportCsv'])->name('whatsapp.export');
    Route::post('/whatsapp-leads/bulk-action', [AdminWhatsappController::class, 'bulkAction'])->name('whatsapp.bulk');
    Route::delete('/whatsapp-leads/{id}', [AdminWhatsappController::class, 'destroy'])->name('whatsapp.destroy');
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
    Route::post('/inquiries/bulk-action', [AdminDashboardController::class, 'bulkInquiriesAction'])->name('inquiries.bulk');
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
    Route::get('/legal', function () {
        return redirect()->route('admin.legal.index');
    })->name('legal.alias');
    Route::get('/legal-pages/{id}/edit', [AdminLegalController::class, 'edit'])->name('legal.edit');
    Route::post('/legal-pages/{id}/update', [AdminLegalController::class, 'update'])->name('legal.update');
    Route::post('/legal-pages/{id}/section/add', [AdminLegalController::class, 'addSection'])->name('legal.section.add');
    Route::post('/legal-pages/section/{sectionId}/item/add', [AdminLegalController::class, 'addItem'])->name('legal.item.add');
    Route::delete('/legal-pages/section/{id}', [AdminLegalController::class, 'deleteSection'])->name('legal.section.delete');
    Route::delete('/legal-pages/item/{id}', [AdminLegalController::class, 'deleteItem'])->name('legal.item.delete');

    // Admin Profile & Security
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

    // Integrations & Portal Settings (General, SEO, Marketing, Google, Meta, Currency, Cache)
    Route::get('/settings/general', [AdminSettingController::class, 'general'])->name('settings.general');
    Route::get('/settings/seo', [AdminSettingController::class, 'seo'])->name('settings.seo');
    Route::get('/settings/marketing', [AdminSettingController::class, 'marketing'])->name('settings.marketing');
    Route::get('/settings/google', [AdminSettingController::class, 'google'])->name('settings.google');
    Route::get('/settings/meta', [AdminSettingController::class, 'meta'])->name('settings.meta');
    Route::get('/settings/currency', [AdminSettingController::class, 'currency'])->name('settings.currency');
    Route::post('/settings/update', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/sync-currency', [AdminSettingController::class, 'syncExchangeRates'])->name('settings.sync-currency');
    Route::post('/clear-cache', [AdminSettingController::class, 'clearCache'])->name('clear-cache');
    Route::post('/run-migrations', [AdminSettingController::class, 'runMigrations'])->name('run-migrations');

    // ── Email Marketing: Subscribers & Audience Lists ───────────────────────
    Route::get('/subscribers/export', [AdminSubscriberController::class, 'exportCsv'])->name('subscribers.export');
    Route::post('/subscribers/import', [AdminSubscriberController::class, 'importCsv'])->name('subscribers.import');
    Route::post('/subscribers/bulk', [AdminSubscriberController::class, 'bulkAction'])->name('subscribers.bulk');
    Route::post('/subscribers/{id}/toggle-status', [AdminSubscriberController::class, 'toggleStatus'])->name('subscribers.toggle-status');
    Route::resource('subscribers', AdminSubscriberController::class);

    // ── Email Marketing: Audience Groups / Segments ─────────────────────────
    Route::resource('subscriber-groups', AdminSubscriberGroupController::class);

    // ── Email Marketing: Responsive Templates ───────────────────────────────
    Route::get('/email-templates/{id}/preview', [AdminEmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::resource('email-templates', AdminEmailTemplateController::class);

    // ── Email Marketing: Campaigns & Analytics ──────────────────────────────
    Route::post('/campaigns/{id}/send', [AdminEmailCampaignController::class, 'send'])->name('campaigns.send');
    Route::post('/campaigns/{id}/send-test', [AdminEmailCampaignController::class, 'sendTest'])->name('campaigns.send-test');
    Route::resource('campaigns', AdminEmailCampaignController::class);

    // ── Mailer & SMTP Server Settings ───────────────────────────────────────
    Route::get('/settings/mail', [AdminMailSettingController::class, 'index'])->name('settings.mail');
    Route::post('/settings/mail', [AdminMailSettingController::class, 'update'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [AdminMailSettingController::class, 'testConnection'])->name('settings.mail.test');
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

// ── Email Marketing Tracking & Unsubscribe ──────────────────────────────────
Route::get('/email/track/open/{token}.gif', [SubscriberController::class, 'trackOpen'])->name('email.track.open');
Route::get('/email/track/click/{token}', [SubscriberController::class, 'trackClick'])->name('email.track.click');
Route::get('/unsubscribe/{token}', [SubscriberController::class, 'unsubscribe'])->name('unsubscribe');
Route::post('/unsubscribe/{token}', [SubscriberController::class, 'processUnsubscribe'])->name('unsubscribe.submit');

// ── Dynamic XML Sitemap & Sitemap Index ──────────────────────────────────────
Route::get('/sitemap_index.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap.xml', [SitemapController::class, 'unified'])->name('sitemap');
Route::get('/sitemap-tours.xml', [SitemapController::class, 'tours'])->name('sitemap.tours');
Route::get('/sitemap-blogs.xml', [SitemapController::class, 'blogs'])->name('sitemap.blogs');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-images.xml', [SitemapController::class, 'images'])->name('sitemap.images');

// ── AI Search Engine & LLM Markdown Endpoints (GEO Optimization) ─────────────
Route::get('/llms.txt', [LlmsController::class, 'index'])->name('llms.txt');
Route::get('/llms-full.txt', [LlmsController::class, 'full'])->name('llms.full');

// ── RFC 9116 Vulnerability Disclosure & Security Policy ──────────────────────
Route::get('/.well-known/security.txt', function () {
    return response()->file(public_path('.well-known/security.txt'), [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->name('security.txt');
Route::redirect('/security.txt', '/.well-known/security.txt', 301);

// ── Explicit High-Value Tour Routes ─────────────────────────────────────────
Route::get('/dune-buggy-rental-dubai', [TourController::class, 'showBuggy'])->name('tours.buggy');

// ── Programmatic Geo-Location Pickup Routes (Pillar 2 SEO) ───────────────────
Route::get('/desert-safari-from-{location}', [LocationLandingController::class, 'show'])->name('tours.location');

// ── SEO 301 Permanent Redirects for Legacy / Shorthand Tour Slugs ───────────
Route::redirect('/dubai-marina-dhow-cruise', '/dhow-cruise-catamaran-cruise-dinner-dubai', 301);
Route::redirect('/ocean-empress-dhow-cruise', '/dhow-cruise-catamaran-cruise-dinner-dubai', 301);
Route::redirect('/abu-dhabi-city-tour', '/abu-dhabi-city-tour-from-dubai', 301);
Route::redirect('/quad-bike-tour-dubai', '/desert-safari-quad-biking-dubai', 301);
Route::redirect('/vip-desert-safari-dubai', '/evening-desert-safari-dubai', 301);
Route::redirect('/buggy-tour-dubai', '/dune-buggy-rental-dubai', 301);

// ── Root-level Dynamic Tour Slugs (Fallback Route) ───────────────────────────
Route::get('/{slug}', [TourController::class, 'show'])->name('tours.show');
