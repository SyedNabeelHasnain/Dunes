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
        // Add attribution and draft deduplication columns to bookings
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'traffic_channel')) {
                    $table->string('traffic_channel', 100)->nullable()->index()->after('referrer');
                }
                if (! Schema::hasColumn('bookings', 'gclid')) {
                    $table->string('gclid', 150)->nullable()->index()->after('traffic_channel');
                }
                if (! Schema::hasColumn('bookings', 'fbclid')) {
                    $table->string('fbclid', 150)->nullable()->after('gclid');
                }
                if (! Schema::hasColumn('bookings', 'landing_page')) {
                    $table->text('landing_page')->nullable()->after('fbclid');
                }
                if (! Schema::hasColumn('bookings', 'draft_token')) {
                    $table->string('draft_token', 64)->nullable()->index()->after('landing_page');
                }
            });
        }

        // Add attribution columns to contacts
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'traffic_channel')) {
                    $table->string('traffic_channel', 100)->nullable()->index()->after('status');
                }
                if (! Schema::hasColumn('contacts', 'utm_source')) {
                    $table->string('utm_source', 100)->nullable()->after('traffic_channel');
                }
                if (! Schema::hasColumn('contacts', 'utm_medium')) {
                    $table->string('utm_medium', 100)->nullable()->after('utm_source');
                }
                if (! Schema::hasColumn('contacts', 'utm_campaign')) {
                    $table->string('utm_campaign', 255)->nullable()->after('utm_medium');
                }
                if (! Schema::hasColumn('contacts', 'utm_term')) {
                    $table->string('utm_term', 255)->nullable()->after('utm_campaign');
                }
                if (! Schema::hasColumn('contacts', 'utm_content')) {
                    $table->string('utm_content', 255)->nullable()->after('utm_term');
                }
                if (! Schema::hasColumn('contacts', 'gclid')) {
                    $table->string('gclid', 150)->nullable()->index()->after('utm_content');
                }
                if (! Schema::hasColumn('contacts', 'fbclid')) {
                    $table->string('fbclid', 150)->nullable()->after('gclid');
                }
                if (! Schema::hasColumn('contacts', 'initial_referrer')) {
                    $table->text('initial_referrer')->nullable()->after('fbclid');
                }
                if (! Schema::hasColumn('contacts', 'landing_page')) {
                    $table->text('landing_page')->nullable()->after('initial_referrer');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $columns = ['traffic_channel', 'gclid', 'fbclid', 'landing_page', 'draft_token'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('bookings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $columns = [
                    'traffic_channel', 'utm_source', 'utm_medium', 'utm_campaign',
                    'utm_term', 'utm_content', 'gclid', 'fbclid',
                    'initial_referrer', 'landing_page',
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('contacts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
