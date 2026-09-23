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
        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'infants')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->integer('infants')->default(0)->after('children');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'infants')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('infants');
            });
        }
    }
};
