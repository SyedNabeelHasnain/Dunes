<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (!Schema::hasColumn('contacts', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
                if (!Schema::hasColumn('contacts', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('booking_payments')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_payments', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (Schema::hasColumn('contacts', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                if (Schema::hasColumn('contacts', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }

        if (Schema::hasTable('booking_payments')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                if (Schema::hasColumn('booking_payments', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
