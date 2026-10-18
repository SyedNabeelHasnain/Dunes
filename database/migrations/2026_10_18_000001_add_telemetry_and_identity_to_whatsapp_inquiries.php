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
            if (! Schema::hasColumn('whatsapp_inquiries', 'client_ip')) {
                $table->string('client_ip', 45)->nullable()->index()->after('request_log_id');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'country')) {
                $table->string('country', 100)->nullable()->after('client_ip');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'city')) {
                $table->string('city', 100)->nullable()->after('country');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'region')) {
                $table->string('region', 100)->nullable()->after('city');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'device_type')) {
                $table->string('device_type', 30)->nullable()->after('region');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'browser_name')) {
                $table->string('browser_name', 50)->nullable()->after('device_type');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'os_name')) {
                $table->string('os_name', 50)->nullable()->after('browser_name');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'isp')) {
                $table->string('isp', 150)->nullable()->after('os_name');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'organization')) {
                $table->string('organization', 150)->nullable()->after('isp');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'utm_source')) {
                $table->string('utm_source', 100)->nullable()->after('organization');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'utm_medium')) {
                $table->string('utm_medium', 100)->nullable()->after('utm_source');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'utm_campaign')) {
                $table->string('utm_campaign', 150)->nullable()->after('utm_medium');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'referrer_url')) {
                $table->text('referrer_url')->nullable()->after('utm_campaign');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'screen_res')) {
                $table->string('screen_res', 50)->nullable()->after('referrer_url');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'client_timezone')) {
                $table->string('client_timezone', 100)->nullable()->after('screen_res');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'client_language')) {
                $table->string('client_language', 50)->nullable()->after('client_timezone');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'email')) {
                $table->string('email', 150)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'prospect_source')) {
                $table->string('prospect_source', 100)->nullable()->after('email');
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
                'client_ip', 'country', 'city', 'region', 'device_type',
                'browser_name', 'os_name', 'isp', 'organization',
                'utm_source', 'utm_medium', 'utm_campaign', 'referrer_url',
                'screen_res', 'client_timezone', 'client_language',
                'email', 'prospect_source'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('whatsapp_inquiries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
