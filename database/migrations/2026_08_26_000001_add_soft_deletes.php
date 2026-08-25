<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
            $table->softDeletes();
        });

        Schema::table('booking_payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('updated_at');
            $table->dropSoftDeletes();
        });

        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
