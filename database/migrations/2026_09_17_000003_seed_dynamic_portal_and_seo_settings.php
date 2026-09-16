<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            // Site Identity & Licensing
            'site_name' => 'Dunes Discovery Tourism',
            'site_phone' => '+971 50 245 6056',
            'site_whatsapp' => '971502456056',
            'site_email' => 'info@dunesdiscoverytourism.com',
            'site_support_email' => 'support@dunesdiscoverytourism.com',
            'site_address' => 'Al Fahidi, Bur Dubai, Dubai, United Arab Emirates',
            'company_license_number' => '1430583',
            'google_maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14439.467468694034!2d55.2707828!3d25.2048493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f434910086b6d%3A0xc4db9db186e4e1e2!2sDunes%20Discovery%20Tourism%20LLC!5e0!3m2!1sen!2sae!4v1700000000000!5m2!1sen!2sae',
            
            // Social Media & Reviews
            'social_tripadvisor' => 'https://www.tripadvisor.com/UserReviewEdit-g295424-d29026644-Dunes_Discovery-Dubai_Emirate_of_Dubai.html',
            'social_google' => 'https://search.google.com/local/writereview?placeid=ChIJbWsIEIVEdEER4uHEhb2dbcQ',
            'social_facebook' => 'https://facebook.com/dunesdiscoverytourism',
            'social_instagram' => 'https://instagram.com/dunesdiscoverytourism',
            'social_youtube' => 'https://youtube.com/@dunesdiscoverytourism',
            'social_tiktok' => 'https://tiktok.com/@dunesdiscoverytourism',

            // Global / Default SEO
            'seo_default_title' => 'Dunes Discovery Tourism | Premium Dubai Desert Safari Tours',
            'seo_default_description' => 'Experience Dubai\'s premier desert safari adventures with Dunes Discovery Tourism. VIP majlis, dune bashing, quad biking, camel rides & gourmet BBQ.',
            'seo_default_keywords' => 'desert safari dubai, dubai safari, dunes discovery tourism, evening desert safari, vip desert safari',
            'seo_default_og_image' => '/images/og-default.jpg',

            // Homepage SEO
            'seo_home_title' => 'Dunes Discovery Tourism | Dubai Desert Safari & Adventure Tours 2026',
            'seo_home_description' => 'Experience the best desert safari in Dubai with Dunes Discovery Tourism. High-octane dune bashing, quad biking, camel rides, falconry & 5-star VIP BBQ dinners.',
            'seo_home_keywords' => 'desert safari dubai, evening desert safari, dunes discovery, dubai safari tours 2026, quad biking dubai',
            'seo_home_og_image' => '/images/og-home.jpg',

            // Tours Catalog SEO
            'seo_tours_title' => 'Dubai Desert Safari Tours & Adventure Packages 2026 | Dunes Discovery',
            'seo_tours_description' => 'Browse our complete catalog of certified Dubai desert safari packages. Compare Standard, VIP, and Private tours with live pricing and instant confirmation.',
            'seo_tours_keywords' => 'dubai tour catalog, desert safari packages, evening safari, morning safari, overnight desert safari dubai',
            'seo_tours_og_image' => '/images/og-tours.jpg',

            // Blog Catalog SEO
            'seo_blog_title' => 'Dubai Desert Safari & Travel Guide Blog 2026 | Dunes Discovery',
            'seo_blog_description' => 'Expert travel guides, insider tips, packing checklists, and cultural insights for your Dubai desert safari journey from certified tourism specialists.',
            'seo_blog_keywords' => 'dubai desert blog, desert safari tips, dubai travel guide, what to wear desert safari, dune bashing tips',
            'seo_blog_og_image' => '/images/og-blog.jpg',

            // About Us SEO
            'seo_about_title' => 'About Dunes Discovery Tourism | Certified Dubai Desert Safari Experts',
            'seo_about_description' => 'Learn about Dunes Discovery Tourism LLC. Licensed by Dubai Department of Economy and Tourism (DET #1430583), offering trusted desert safari experiences since 2018.',
            'seo_about_keywords' => 'about dunes discovery, dubai tourism license 1430583, trusted dubai tour operator, desert safari team',
            'seo_about_og_image' => '/images/og-about.jpg',

            // Contact Us SEO
            'seo_contact_title' => 'Contact Dunes Discovery Tourism | 24/7 Dubai Safari Support',
            'seo_contact_description' => 'Get in touch with Dunes Discovery Tourism. 24/7 customer support via WhatsApp (+971 50 245 6056), phone, and email for instant safari bookings and inquiries.',
            'seo_contact_keywords' => 'contact dunes discovery, dubai safari customer service, dunes whatsapp booking, dubai tour phone number',
            'seo_contact_og_image' => '/images/og-contact.jpg',

            // FAQ SEO
            'seo_faq_title' => 'Frequently Asked Questions | Dubai Desert Safari Guide 2026',
            'seo_faq_description' => 'Find answers to top questions about Dubai desert safaris: pickup locations, safety, attire, infant policies, cancellation rules, and payment options.',
            'seo_faq_keywords' => 'desert safari faq, what to bring desert safari, dubai safari safety questions, desert safari timing faq',
            'seo_faq_og_image' => '/images/og-faq.jpg',

            // Rate Card SEO
            'seo_rate_card_title' => 'Official Rate Card & Pricing Guide 2026 | Dunes Discovery Tourism',
            'seo_rate_card_description' => 'View transparent, all-inclusive rates for all Dubai Desert Safari packages, VIP majlis upgrades, buggy rentals, and private transport options.',
            'seo_rate_card_keywords' => 'desert safari prices dubai, safari rate card 2026, dubai buggy prices, vip safari rates',
            'seo_rate_card_og_image' => '/images/og-rate-card.jpg',

            // Marketing: Top Announcement Banner
            'promo_top_banner_enabled' => '1',
            'promo_top_banner_badge' => 'Limited Time Offer',
            'promo_top_banner_text' => 'Special Online Exclusive: Get 25% OFF on all Desert Safari Tours!',
            'promo_top_banner_code' => 'DUNESWELCOME',
            'promo_top_banner_discount' => '25',

            // Marketing: Welcome 25% Modal
            'promo_welcome_modal_enabled' => '1',
            'promo_welcome_modal_headline' => 'Unlock Exclusive 25% OFF',
            'promo_welcome_modal_subheadline' => 'Book your unforgettable Dubai Desert Safari today with our premier welcome discount.',
            'promo_welcome_modal_discount' => '25',
            'promo_welcome_modal_timer_minutes' => '15',
            'promo_welcome_modal_delay_seconds' => '5',

            // Tracking Defaults
            'google_ads_id' => 'AW-17859624049',
            'google_conversion_label' => 'eR3SCLimtvobEPH4kMRC',
        ];

        $now = now();
        foreach ($settings as $key => $val) {
            $exists = DB::table('settings')->where('setting_key', $key)->exists();
            if (!$exists) {
                DB::table('settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $val,
                    'updated_at' => $now,
                ]);
            }
        }

        try {
            Cache::forget('site_settings_cache');
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
