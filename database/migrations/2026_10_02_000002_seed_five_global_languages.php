<?php

use App\Models\Language;
use App\Services\Localization\LocaleManager;
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
                $table->string('direction', 10)->default('ltr');
                $table->string('flag', 50)->nullable();
                $table->boolean('is_default')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Complete definitions for all 5 core languages
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇬🇧',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇦🇪',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'native_name' => 'Русский',
                'direction' => 'ltr',
                'flag' => '🇷🇺',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'direction' => 'ltr',
                'flag' => '🇪🇸',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'native_name' => 'Italiano',
                'direction' => 'ltr',
                'flag' => '🇮🇹',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        $localeManager = app(LocaleManager::class);

        foreach ($languages as $langData) {
            $existing = DB::table('languages')->where('code', $langData['code'])->first();
            if ($existing) {
                DB::table('languages')->where('code', $langData['code'])->update([
                    'name' => $langData['name'],
                    'native_name' => $langData['native_name'],
                    'direction' => $langData['direction'],
                    'flag' => $langData['flag'],
                    'is_default' => $langData['is_default'],
                    'is_active' => true,
                    'sort_order' => $langData['sort_order'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('languages')->insert(array_merge($langData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // Build complete locale directories and files
            $localeManager->buildLocale($langData['code']);
        }

        Language::clearLanguageCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep English and Arabic, remove additional languages if needed
        DB::table('languages')->whereIn('code', ['ru', 'es', 'it'])->delete();
        Language::clearLanguageCache();
    }
};
