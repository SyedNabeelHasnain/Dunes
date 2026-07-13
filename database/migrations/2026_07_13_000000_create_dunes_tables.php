<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->timestamps();
        });

        // 2. Categories Table (Modernized Taxonomy)
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->string('icon', 100)->nullable();
            $table->integer('priority')->default(99)->index();
            $table->timestamps();
        });

        // 3. Tours Table
        Schema::create('tours', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->unsignedInteger('category_id')->nullable()->index();
            $table->text('short_desc')->nullable();
            $table->text('full_desc')->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('pickup_time', 100)->nullable();
            $table->string('dropoff_time', 100)->nullable();
            $table->integer('min_age')->default(3);
            $table->string('group_size', 50)->nullable();
            $table->string('languages', 255)->default('English, Arabic');
            $table->string('hero_image', 255)->nullable();
            $table->string('thumb_image', 255)->nullable();
            $table->string('og_image', 255)->nullable();
            $table->string('video_url', 255)->nullable();
            $table->decimal('rating', 2, 1)->default(4.8);
            $table->integer('review_count')->default(0);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('status', 50)->default('active')->index();
            $table->integer('priority')->default(99)->index();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        // 4. Tiers Table
        Schema::create('tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('display_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('badge', 100)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('priority')->default(99);
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });

        // 5. Addons Table
        Schema::create('addons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->decimal('default_price', 10, 2)->default(0.00);
            $table->string('status', 50)->default('active');
            $table->integer('priority')->default(99);
            $table->timestamps();
        });

        // 6. Tour Tiers Junction Table
        Schema::create('tour_tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->unsignedInteger('tier_id');
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('price_type', 50)->default('per person');

            $table->unique(['tour_id', 'tier_id']);
            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('cascade');
        });

        // 7. Tour Addons Junction Table
        Schema::create('tour_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->unsignedInteger('addon_id');
            $table->decimal('price', 10, 2);

            $table->unique(['tour_id', 'addon_id']);
            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->foreign('addon_id')->references('id')->on('addons')->onDelete('cascade');
        });

        // 8. Content Items Table (Templates for Inclusions/Exclusions)
        Schema::create('content_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50)->index(); // inclusion, exclusion, highlight, not_allowed, activity
            $table->string('icon', 50)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('priority')->default(99);
            $table->timestamps();
        });

        // 9. Tour Content Table (Links content_items to tours and optionally tiers)
        Schema::create('tour_content', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->unsignedInteger('tier_id')->nullable();
            $table->unsignedInteger('content_id');

            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('content_items')->onDelete('cascade');
        });

        // 10. Itineraries Table
        Schema::create('itineraries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->string('time', 50);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('duration', 50)->nullable();
            $table->integer('priority')->default(99);
            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
        });

        // 11. FAQs Table
        Schema::create('faqs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('question');
            $table->text('answer');
            $table->string('category', 100)->default('general')->index();
            $table->integer('priority')->default(99)->index();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();
        });

        // 12. FAQ Assignments Table
        Schema::create('faq_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('faq_id');
            $table->string('entity_type', 50)->default('general'); // tour, tier, addon, general
            $table->unsignedInteger('entity_id')->nullable();

            $table->foreign('faq_id')->references('id')->on('faqs')->onDelete('cascade');
        });

        // 13. Bookings Table
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference', 20)->unique();
            $table->unsignedInteger('tour_id')->nullable();
            $table->unsignedInteger('tier_id')->nullable();
            $table->string('tour_name', 255);
            $table->string('tier_name', 100)->nullable();
            $table->date('tour_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phone', 50);
            $table->text('pickup_location')->nullable();
            $table->text('special_requests')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('addons_total', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('currency', 10)->default('AED');
            $table->string('status', 50)->default('pending')->index(); // pending, confirmed, completed, cancelled
            $table->string('payment_method', 50)->default('cash'); // cash, advance, full
            $table->string('payment_status', 50)->default('unpaid'); // unpaid, partial, paid, failed, cancelled
            $table->decimal('payment_amount', 10, 2)->default(0.00);
            $table->decimal('balance_due', 10, 2)->default(0.00);
            $table->string('ziina_payment_intent_id', 100)->nullable()->index();
            $table->string('ziina_status', 50)->nullable();
            $table->text('ziina_redirect_url')->nullable();
            $table->unsignedBigInteger('request_log_id')->nullable()->index();
            $table->string('ip_address', 50)->nullable();
            $table->string('ip_location', 255)->nullable();
            $table->string('gps_lat', 50)->nullable();
            $table->string('gps_lng', 50)->nullable();
            $table->text('gps_address')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('set null');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('set null');
        });

        // 14. Booking Addons Table
        Schema::create('booking_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('addon_id')->nullable();
            $table->string('addon_name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });

        // 15. Booking Payments Table
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id')->nullable();
            $table->string('payment_intent_id', 100)->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('AED');
            $table->string('status', 50);
            $table->text('payment_url')->nullable();
            $table->text('notes')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
        });

        // 16. Contacts Table
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phone', 50)->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('message');
            $table->string('status', 50)->default('new')->index();
            $table->unsignedBigInteger('request_log_id')->nullable()->index();
            $table->string('ip_address', 50)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 17. Reviews Table
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source', 50)->index();
            $table->string('source_review_id', 255)->nullable();
            $table->text('review_url')->nullable();
            $table->date('published_date')->nullable();
            $table->string('reviewer_name', 255);
            $table->text('reviewer_avatar_url')->nullable();
            $table->text('reviewer_profile_url')->nullable();
            $table->decimal('rating', 2, 1)->index();
            $table->string('review_title', 255)->nullable();
            $table->text('review_text')->nullable();
            $table->string('status', 50)->default('approved')->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });

        // 18. Request Logs Table
        Schema::create('request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 50)->default('other')->index();
            $table->unsignedInteger('entity_id')->nullable();
            $table->string('form_name', 100)->default('Not Available');
            $table->dateTime('request_timestamp');
            $table->string('request_method', 20)->default('Not Available');
            $table->text('request_uri')->nullable();
            $table->text('query_string')->nullable();
            $table->string('host', 255)->default('Not Available');
            $table->string('server_protocol', 20)->default('Not Available');
            $table->string('https_flag', 20)->default('Not Available');
            $table->string('client_ip', 45)->default('Not Available');
            $table->string('ip_version', 20)->default('Not Available');
            $table->char('ip_hash', 64)->default('Not Available')->index();
            $table->string('country', 100)->default('Not Available');
            $table->string('region', 100)->default('Not Available');
            $table->string('city', 100)->default('Not Available');
            $table->string('latitude', 50)->default('Not Available');
            $table->string('longitude', 50)->default('Not Available');
            $table->string('timezone', 100)->default('Not Available');
            $table->string('isp', 150)->default('Not Available');
            $table->string('organization', 150)->default('Not Available');
            $table->string('asn', 50)->default('Not Available');
            $table->string('hosting_flag', 20)->default('Not Available');
            $table->string('proxy_flag', 20)->default('Not Available');
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->default('Not Available');
            $table->string('os_name', 50)->default('Not Available');
            $table->string('os_version', 50)->default('Not Available');
            $table->string('browser_name', 50)->default('Not Available');
            $table->string('browser_version', 50)->default('Not Available');
            $table->string('bot_indicator', 50)->default('Not Available');
            $table->string('accept_language', 255)->default('Not Available');
            $table->string('accept_encoding', 255)->default('Not Available');
            $table->text('referrer_url')->nullable();
            $table->string('utm_source', 255)->default('Not Available');
            $table->string('utm_medium', 255)->default('Not Available');
            $table->string('utm_campaign', 255)->default('Not Available');
            $table->string('utm_term', 255)->default('Not Available');
            $table->string('utm_content', 255)->default('Not Available');
            $table->text('landing_page')->nullable();
            $table->string('session_id', 128)->default('Not Available')->index();
            $table->dateTime('session_start_time')->nullable();
            $table->dateTime('session_end_time')->nullable();
            $table->integer('session_duration_seconds')->default(0);
            $table->integer('pages_viewed_count')->default(0);
            $table->dateTime('form_load_timestamp')->nullable();
            $table->dateTime('form_submit_timestamp')->nullable();
            $table->integer('form_completion_seconds')->default(0);
            $table->string('repeat_visit_flag', 20)->default('No');
            $table->text('google_maps_link')->nullable();
            $table->string('gps_consent_flag', 20)->default('Not Available');
            $table->string('gps_latitude', 50)->default('Not Available');
            $table->string('gps_longitude', 50)->default('Not Available');
            $table->string('gps_accuracy', 50)->default('Not Available');
            $table->string('gps_altitude', 50)->default('Not Available');
            $table->string('gps_heading', 50)->default('Not Available');
            $table->string('gps_speed', 50)->default('Not Available');
            $table->string('gps_timestamp', 50)->default('Not Available');
            $table->string('gps_source', 50)->default('Not Available');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
        });

        // 19. WhatsApp Inquiries Table
        Schema::create('whatsapp_inquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('request_log_id')->nullable()->index();
            $table->string('name', 255);
            $table->string('phone', 50);
            $table->string('tour_name', 255)->nullable();
            $table->string('page_url', 255)->nullable();
            $table->text('message_text')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 20. Verified Emails Table
        Schema::create('verified_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255)->unique();
            $table->timestamp('verified_at')->useCurrent();
        });

        // 21. Email OTPs Table
        Schema::create('email_otps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255)->index();
            $table->string('otp', 64);
            $table->timestamp('expires_at')->index();
            $table->timestamp('created_at')->useCurrent();
        });

        // 22. Legal Pages Table
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50)->unique();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 23. Legal Sections Table
        Schema::create('legal_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('page_id');
            $table->string('heading', 255)->nullable();
            $table->string('subheading', 255)->nullable();
            $table->integer('priority')->default(99);
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('legal_pages')->onDelete('cascade');
        });

        // 24. Legal Items Table
        Schema::create('legal_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('section_id');
            $table->text('content');
            $table->integer('priority')->default(99);
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('legal_sections')->onDelete('cascade');
        });

        // 25. Blog Categories Table
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('og_image', 255)->nullable();
            $table->integer('priority')->default(99)->index();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();
        });

        // 26. Blog Tags Table
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        // 27. Blog Posts Table
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 255)->unique();
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->unsignedInteger('category_id')->nullable()->index();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('author_name', 255)->default('Dunes Discovery Tourism');
            $table->string('author_title', 255)->nullable();
            $table->text('author_bio')->nullable();
            $table->string('author_avatar', 255)->nullable();
            $table->string('featured_image', 255)->nullable();
            $table->string('featured_image_alt', 255)->nullable();
            $table->string('featured_image_caption', 500)->nullable();
            $table->unsignedTinyInteger('read_time')->default(5);
            $table->string('status', 50)->default('draft')->index(); // draft, published, archived
            $table->boolean('is_featured')->default(false)->index();
            $table->integer('priority')->default(99)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('focus_keyword', 255)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('robots', 100)->default('index, follow');
            $table->string('og_title', 255)->nullable();
            $table->text('og_desc')->nullable();
            $table->string('og_image', 255)->nullable();
            $table->string('og_type', 50)->default('article');
            $table->string('schema_type', 50)->default('BlogPosting');
            $table->text('ai_summary')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('set null');
        });

        // 28. Blog Post Tags Table (Junction)
        Schema::create('blog_post_tags', function (Blueprint $table) {
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('tag_id');

            $table->primary(['post_id', 'tag_id']);
            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('blog_tags')->onDelete('cascade');
        });

        // 29. Blog Post FAQs Table
        Schema::create('blog_post_faqs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id')->index();
            $table->text('question');
            $table->text('answer');
            $table->integer('priority')->default(99);

            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_faqs');
        Schema::dropIfExists('blog_post_tags');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('legal_items');
        Schema::dropIfExists('legal_sections');
        Schema::dropIfExists('legal_pages');
        Schema::dropIfExists('email_otps');
        Schema::dropIfExists('verified_emails');
        Schema::dropIfExists('whatsapp_inquiries');
        Schema::dropIfExists('request_logs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('booking_payments');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('faq_assignments');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('tour_content');
        Schema::dropIfExists('content_items');
        Schema::dropIfExists('tour_addons');
        Schema::dropIfExists('tour_tiers');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('tiers');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
