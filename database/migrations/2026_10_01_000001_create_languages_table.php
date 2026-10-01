<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 10)->unique()->index();
                $table->string('name', 100);
                $table->string('native_name', 100);
                $table->string('direction', 10)->default('ltr'); // 'ltr' or 'rtl'
                $table->string('flag', 50)->nullable();
                $table->boolean('is_default')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial supported languages: English (Default) and Arabic (RTL)
            DB::table('languages')->insert([
                [
                    'code' => 'en',
                    'name' => 'English',
                    'native_name' => 'English',
                    'direction' => 'ltr',
                    'flag' => 'gb',
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'ar',
                    'name' => 'Arabic',
                    'native_name' => 'العربية',
                    'direction' => 'rtl',
                    'flag' => 'ae',
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
