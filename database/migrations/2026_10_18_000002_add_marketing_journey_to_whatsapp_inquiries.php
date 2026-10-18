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
        Schema::table('whatsapp_inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_inquiries', 'traffic_channel')) {
                $table->string('traffic_channel', 100)->nullable()->index()->after('utm_campaign');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'initial_referrer')) {
                $table->text('initial_referrer')->nullable()->after('traffic_channel');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'landing_page')) {
                $table->text('landing_page')->nullable()->after('initial_referrer');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'session_duration_seconds')) {
                $table->unsignedInteger('session_duration_seconds')->default(0)->after('landing_page');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'pages_viewed_count')) {
                $table->unsignedSmallInteger('pages_viewed_count')->default(1)->after('session_duration_seconds');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'journey_timeline')) {
                $table->longText('journey_timeline')->nullable()->after('pages_viewed_count');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'gclid')) {
                $table->string('gclid', 150)->nullable()->index()->after('journey_timeline');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'fbclid')) {
                $table->string('fbclid', 150)->nullable()->index()->after('gclid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_inquiries', function (Blueprint $table) {
            $cols = [
                'traffic_channel',
                'initial_referrer',
                'landing_page',
                'session_duration_seconds',
                'pages_viewed_count',
                'journey_timeline',
                'gclid',
                'fbclid',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('whatsapp_inquiries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
