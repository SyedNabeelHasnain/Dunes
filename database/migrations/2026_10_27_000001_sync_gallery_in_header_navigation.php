<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        // Delete any existing header menu items to ensure clean synchronization
        DB::table('menu_items')->where('location', 'header')->delete();

        // Insert the 6 clean, high-priority header items including Gallery
        $headerItems = [
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'Tours',
                    'ar' => 'جولات',
                    'ru' => 'Туры',
                    'es' => 'Tours',
                    'it' => 'Tour',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/tours',
                'route_name' => 'tours.index',
                'target' => '_self',
                'icon' => null,
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'Gallery',
                    'ar' => 'المعرض',
                    'ru' => 'Галерея',
                    'es' => 'Galería',
                    'it' => 'Galleria',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/gallery',
                'route_name' => 'gallery.index',
                'target' => '_self',
                'icon' => null,
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'About',
                    'ar' => 'عن',
                    'ru' => 'О нас',
                    'es' => 'Nosotros',
                    'it' => 'Chi Siamo',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/about',
                'route_name' => 'about',
                'target' => '_self',
                'icon' => null,
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'Blogs',
                    'ar' => 'مدونات',
                    'ru' => 'Блог',
                    'es' => 'Blogs',
                    'it' => 'Blog',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/blog',
                'route_name' => 'blog.index',
                'target' => '_self',
                'icon' => null,
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'FAQs',
                    'ar' => 'الأسئلة الشائعة',
                    'ru' => 'Вопросы и ответы',
                    'es' => 'Preguntas Frecuentes',
                    'it' => 'Domande Frequenti',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/faq',
                'route_name' => 'faq',
                'target' => '_self',
                'icon' => null,
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location' => 'header',
                'label' => json_encode([
                    'en' => 'Contact',
                    'ar' => 'تواصل معنا',
                    'ru' => 'Контакты',
                    'es' => 'Contacto',
                    'it' => 'Contatti',
                ], JSON_UNESCAPED_UNICODE),
                'url' => '/contact',
                'route_name' => 'contact',
                'target' => '_self',
                'icon' => null,
                'order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('menu_items')->insert($headerItems);

        // Clear all relevant caches immediately
        try {
            Cache::forget('cms_menu_header_cache');
            Cache::forget('cms_menu_all_cache');
            Cache::forget('cms_content_version');
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')->where('location', 'header')->where('url', '/gallery')->delete();
        }
    }
};
