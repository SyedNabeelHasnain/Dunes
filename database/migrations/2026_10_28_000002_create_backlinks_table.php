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
        if (! Schema::hasTable('backlinks')) {
            Schema::create('backlinks', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255)->nullable();
                $table->text('source_url');
                $table->string('target_url', 255);
                $table->string('referring_domain', 190)->nullable()->index();
                $table->string('category', 50)->default('travel_directory')->index(); // travel_directory, guest_post, blog_mention, partner_portal, forum
                $table->string('anchor_text', 255)->nullable();
                $table->string('link_type', 30)->default('dofollow')->index(); // dofollow, nofollow, ugc, sponsored
                $table->unsignedTinyInteger('domain_authority_est')->default(0);
                $table->string('status', 30)->default('active')->index(); // active, lost, pending, broken
                $table->unsignedSmallInteger('http_status_code')->default(200);
                $table->timestamp('last_checked_at')->nullable();
                $table->text('verification_notes')->nullable();
                $table->string('utm_source', 100)->nullable()->index();
                $table->unsignedBigInteger('referral_visits_count')->default(0);
                $table->unsignedBigInteger('bookings_count')->default(0);
                $table->decimal('revenue_generated_aed', 12, 2)->default(0.00);
                $table->string('partner_name', 255)->nullable();
                $table->string('partner_email', 255)->nullable();
                $table->string('campaign', 100)->nullable();
                $table->decimal('cost_aed', 10, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Add nullable backlink_id column to bookings table for ROI conversion attribution
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'backlink_id')) {
                    $table->unsignedBigInteger('backlink_id')->nullable()->after('coupon_id')->index();
                }
            });

            try {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->foreign('backlink_id')->references('id')->on('backlinks')->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // Graceful fallback for environments with strict foreign key constraints
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'backlink_id')) {
            try {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->dropForeign(['backlink_id']);
                });
            } catch (\Throwable $e) {}

            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('backlink_id');
            });
        }

        Schema::dropIfExists('backlinks');
    }
};
