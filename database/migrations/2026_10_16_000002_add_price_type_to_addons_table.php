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
        if (Schema::hasTable('addons')) {
            Schema::table('addons', function (Blueprint $table) {
                if (! Schema::hasColumn('addons', 'price_type')) {
                    $table->string('price_type', 30)->default('per_person')->after('default_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('addons')) {
            Schema::table('addons', function (Blueprint $table) {
                if (Schema::hasColumn('addons', 'price_type')) {
                    $table->dropColumn('price_type');
                }
            });
        }
    }
};
