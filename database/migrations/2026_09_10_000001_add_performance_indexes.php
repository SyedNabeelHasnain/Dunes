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
        if (Schema::hasTable('request_logs')) {
            Schema::table('request_logs', function (Blueprint $table) {
                $table->index('request_timestamp');
                $table->index('bot_indicator');
                $table->index('client_ip');
                $table->index('country');
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->index('payment_status');
                $table->index('email');
                $table->index('tour_date');
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('request_logs')) {
            Schema::table('request_logs', function (Blueprint $table) {
                $table->dropIndex(['request_timestamp']);
                $table->dropIndex(['bot_indicator']);
                $table->dropIndex(['client_ip']);
                $table->dropIndex(['country']);
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['payment_status']);
                $table->dropIndex(['email']);
                $table->dropIndex(['tour_date']);
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropIndex(['email']);
            });
        }
    }
};
