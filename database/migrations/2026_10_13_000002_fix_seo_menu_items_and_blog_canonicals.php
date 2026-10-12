<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix legacy menu item URLs, remove hardcoded English canonical_url overrides on blog_posts
     * so localized blog routes (/ar/blog/*, /ru/blog/*, /es/blog/*, /it/blog/*) emit self-referencing
     * canonicals, and populate multilingual SEO metadata for core tours.
     */
    public function up(): void
    {
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')
                ->where('url', '/luxury-vip-desert-safari-dubai')
                ->update(['url' => '/evening-desert-safari-dubai', 'updated_at' => now()]);

            DB::table('menu_items')
                ->where('url', '/cancellation-policy')
                ->update(['url' => '/cancellation-refund-policy', 'updated_at' => now()]);
        }

        if (Schema::hasTable('blog_posts') && Schema::hasColumn('blog_posts', 'canonical_url')) {
            DB::table('blog_posts')
                ->whereNotNull('canonical_url')
                ->where('canonical_url', 'like', '%dunesdiscoverytourism.com/blog/%')
                ->update(['canonical_url' => null]);
        }

        if (Schema::hasTable('tours')) {
            $tourTranslations = [
                'evening-desert-safari-dubai' => [
                    'name' => [
                        'ar' => 'سفاري صحراء دبي المسائي',
                        'ru' => 'Вечернее пустынное сафари в Дубае',
                        'es' => 'Safari Vespertino por el Desierto de Dubái',
                        'it' => 'Safari Serale nel Deserto di Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'سفاري صحراء دبي المسائي 2026 | تطعيس الكثبان الحمراء وعشاء شواء | من 99 درهم',
                        'ru' => 'Вечернее сафари в пустыне Дубая 2026 | Джип-тур 4x4 и барбекю | От 99 AED',
                        'es' => 'Safari por el Desierto de Dubái al Atardecer 2026 | 4x4 y Cena BBQ | Desde 99 AED',
                        'it' => 'Safari Serale nel Deserto di Dubai 2026 | Dune Bashing 4x4 e BBQ | Da 99 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'احجز أفضل رحلة سفاري مسائية في صحراء لهباب الحمراء بدبي. تطعيس بسيارات الدفع الرباعي 4x4، ركوب الجمال، التزلج على الرمال، بوفيه عشاء شواء فاخر و3 عروض حية مع توصيل من الفندق.',
                        'ru' => 'Забронируйте лучшее вечернее сафари по красным дюнам Лахбаб в Дубае: джиппинг 45 мин, катание на верблюдах, сэндбординг, ужин-барбекю и 3 живых шоу. Трансфер из отеля.',
                        'es' => 'Reserva el mejor safari vespertino en las dunas rojas de Lahbab en Dubái. Dune bashing 4x4, paseo en camello, sandboarding, cena buffet BBQ y 3 espectáculos en vivo con recogida en hotel.',
                        'it' => 'Prenota il miglior safari serale sulle dune rosse di Lahbab a Dubai. Dune bashing 4x4, giro in cammello, sandboarding, cena buffet BBQ e 3 spettacoli dal vivo con trasferimento in hotel.',
                    ],
                ],
                'morning-desert-safari-dubai' => [
                    'name' => [
                        'ar' => 'سفاري صحراء دبي الصباحي',
                        'ru' => 'Утреннее сафари в пустыне Дубая',
                        'es' => 'Safari Matutino por el Desierto de Dubái',
                        'it' => 'Safari Mattutino nel Deserto di Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'سفاري صحراء دبي الصباحي 2026 | مغامرة الكثبان الرملية عند الشروق | من 120 درهم',
                        'ru' => 'Утреннее сафари в пустыне Дубая 2026 | Джип-тур на рассвете | От 120 AED',
                        'es' => 'Safari Matutino por el Desierto de Dubái 2026 | Dune Bashing 4x4 | Desde 120 AED',
                        'it' => 'Safari Mattutino nel Deserto di Dubai 2026 | Dune Bashing all\'Alba | Da 120 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'استمتع برحلة سفاري صحراوية صباحية في كثبان دبي الحمراء. تطعيس بسيارات 4x4، تزلج على الرمال، وركوب الجمال مع خدمة توصيل مجانية من وإلى الفندق.',
                        'ru' => 'Утреннее пустынное сафари по красным дюнам Дубая: экстремальное катание по дюнам на 4x4, сэндбординг и прогулка на верблюдах с трансфером из отеля.',
                        'es' => 'Disfruta del mejor safari matutino en las dunas rojas de Dubái: dune bashing en 4x4, sandboarding y paseo en camello con recogida gratuita en tu hotel.',
                        'it' => 'Scopri il safari mattutino tra le dune rosse di Dubai: dune bashing in 4x4, sandboarding e giro in cammello con trasferimento gratuito dall\'hotel.',
                    ],
                ],
                'overnight-desert-safari-dubai' => [
                    'name' => [
                        'ar' => 'سفاري المبيت في صحراء دبي',
                        'ru' => 'Ночное сафари с ночевкой в пустыне Дубая',
                        'es' => 'Safari Nocturno con Campamento en el Desierto de Dubái',
                        'it' => 'Safari con Pernottamento nel Deserto di Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'سفاري المبيت في صحراء دبي 2026 | تخييم بدوي وإفطار صحراوي | من 350 درهم',
                        'ru' => 'Сафари с ночевкой в пустыне Дубая 2026 | Бедуинский кемпинг и завтрак | От 350 AED',
                        'es' => 'Safari Nocturno y Acampada en el Desierto de Dubái 2026 | Desde 350 AED',
                        'it' => 'Safari con Pernottamento nel Deserto di Dubai 2026 | Tenda Beduina e Colazione | Da 350 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'عش تجربة تخييم ومبيت أصيلة في صحراء دبي تحت النجوم. تشمل تطعيس الكثبان الرملية، عشاء شواء فاخر، خيام بدوية مجهزة، وإفطار طازج عند الشروق.',
                        'ru' => 'Аутентичный кемпинг с ночевкой в пустыне Дубая: закатный джиппинг, наблюдение за звездами, ужин-барбекю, уютные шатры и свежий завтрак на рассвете.',
                        'es' => 'Auténtica experiencia de acampada nocturna en el desierto de Dubái: dune bashing al atardecer, observación de estrellas, cena BBQ, tiendas beduinas y desayuno al amanecer.',
                        'it' => 'Autentico campeggio notturno nel deserto di Dubai: dune bashing al tramonto, osservazione delle stelle, cena BBQ, tende beduine e colazione fresca all\'alba.',
                    ],
                ],
                'desert-safari-quad-biking-dubai' => [
                    'name' => [
                        'ar' => 'سفاري صحراء دبي مع الدراجات الرباعية',
                        'ru' => 'Пустынное сафари на квадроциклах в Дубае',
                        'es' => 'Safari por el Desierto con Quads ATV en Dubái',
                        'it' => 'Safari nel Deserto con Quad ATV a Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'سفاري صحراء دبي مع الدراجات الرباعية 2026 | قيادة ذاتية ATV | من 199 درهم',
                        'ru' => 'Сафари на квадроциклах в пустыне Дубая 2026 | Квадроциклы ATV и BBQ | От 199 AED',
                        'es' => 'Safari por el Desierto con Quads en Dubái 2026 | ATV y Cena BBQ | Desde 199 AED',
                        'it' => 'Safari nel Deserto con Quad a Dubai 2026 | Guida ATV e Cena BBQ | Da 199 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'مغامرة الدراجات الرباعية ATV في كثبان دبي الحمراء مع برنامج السفاري المسائي الكامل، تطعيس 4x4، تزلج على الرمال، وبوفيه عشاء شواء فاخر.',
                        'ru' => 'Экстремальное сафари на квадроциклах 250–400cc по красным дюнам Дубая в сочетании с джиппингом, сэндбордингом, ужином-барбекю и шоу-программой.',
                        'es' => 'Aventura en quads ATV de 250cc-400cc por las dunas rojas de Dubái combinada con dune bashing 4x4, sandboarding, cena BBQ y espectáculos en vivo.',
                        'it' => 'Avventura in quad ATV da 250cc-400cc sulle dune rosse di Dubai combinata con dune bashing 4x4, sandboarding, cena BBQ e spettacoli dal vivo.',
                    ],
                ],
                'dubai-city-tour' => [
                    'name' => [
                        'ar' => 'جولة معالم مدينة دبي السياحية',
                        'ru' => 'Обзорная экскурсия по Дубаю',
                        'es' => 'Tour Panorámico por la Ciudad de Dubái',
                        'it' => 'Tour Panoramico della Città di Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'جولة مدينة دبي السياحية 2026 | نصف يوم ويوم كامل | من 120 درهم',
                        'ru' => 'Обзорная экскурсия по Дубаю 2026 | Главные достопримечательности | От 120 AED',
                        'es' => 'Tour por la Ciudad de Dubái 2026 | Medio Día y Día Completo | Desde 120 AED',
                        'it' => 'Tour della Città di Dubai 2026 | Mezza Giornata e Giornata Intera | Da 120 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'اكتشف أشهر معالم دبي التاريخية والحديثة: برج خليفة، سوق الذهب، خور دبي، نخلة جميرا ومرسى دبي مع مرشد سياحي محترف وتوصيل من الفندق.',
                        'ru' => 'Откройте для себя главные достопримечательности Дубая: Бурдж-Халифа, Золотой рынок, лодка абра, Пальма Джумейра и Дубай Марина с трансфером из отеля.',
                        'es' => 'Explora los lugares más emblemáticos de Dubái: Burj Khalifa, Zoco del Oro, paseo en barco Abra, Palm Jumeirah y Dubai Marina con recogida en el hotel.',
                        'it' => 'Esplora i luoghi più iconici di Dubai: Burj Khalifa, Gold Souk, giro in barca Abra, Palm Jumeirah e Dubai Marina con trasferimento in hotel.',
                    ],
                ],
                'abu-dhabi-city-tour-from-dubai' => [
                    'name' => [
                        'ar' => 'جولة مدينة أبوظبي من دبي',
                        'ru' => 'Экскурсия в Абу-Даби из Дубая',
                        'es' => 'Excursión a Abu Dabi desde Dubái',
                        'it' => 'Tour di Abu Dhabi da Dubai',
                    ],
                    'meta_title' => [
                        'ar' => 'جولة أبوظبي السياحية من دبي 2026 | جامع الشيخ زايد الكبير | من 199 درهم',
                        'ru' => 'Экскурсия в Абу-Даби из Дубая 2026 | Мечеть Шейха Зайда | От 199 AED',
                        'es' => 'Tour a Abu Dabi desde Dubái 2026 | Gran Mezquita Sheikh Zayed | Desde 199 AED',
                        'it' => 'Tour di Abu Dhabi da Dubai 2026 | Grande Moschea Sheikh Zayed | Da 199 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'رحلة يوم كامل من دبي إلى العاصمة أبوظبي لزيارة جامع الشيخ زايد الكبير، كورنيش أبوظبي، قصر الإمارات والقرية التراثية مع توصيل 4x4.',
                        'ru' => 'Однодневная экскурсия в Абу-Даби из Дубая: посещение Великой мечети Шейха Зайда, набережной Корниш, Emirates Palace и Лувра с трансфером 4x4.',
                        'es' => 'Excursión de día completo a Abu Dabi desde Dubái: visita la Gran Mezquita Sheikh Zayed, Corniche, Emirates Palace y el Louvre con traslado en 4x4.',
                        'it' => 'Escursione di un\'intera giornata ad Abu Dhabi da Dubai: visita la Grande Moschea Sheikh Zayed, la Corniche, l\'Emirates Palace e il Louvre con trasferimento 4x4.',
                    ],
                ],
                'dhow-cruise-catamaran-cruise-dinner-dubai' => [
                    'name' => [
                        'ar' => 'رحلة عشاء الداو والكاتاماران في دبي مارينا',
                        'ru' => 'Вечерний круиз на дау и катамаране с ужином в Дубай Марина',
                        'es' => 'Crucero en Dhow y Catamarán con Cena en Dubai Marina',
                        'it' => 'Crociera in Dhow e Catamarano con Cena a Dubai Marina',
                    ],
                    'meta_title' => [
                        'ar' => 'رحلة عشاء بحرية في دبي مارينا 2026 | بوفيه 5 نجوم وعروض حية | من 150 درهم',
                        'ru' => 'Круиз с ужином по Дубай Марина 2026 | Шведский стол 5★ и шоу | От 150 AED',
                        'es' => 'Crucero con Cena por Dubai Marina 2026 | Buffet 5★ y Espectáculos | Desde 150 AED',
                        'it' => 'Crociera con Cena a Dubai Marina 2026 | Buffet 5★ e Spettacoli | Da 150 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'استمتع برحلة عشاء فاخرة لمدة ساعتين على متن سفينة الداو الخشبية أو الكاتاماران في دبي مارينا مع بوفيه عالمي 5 نجوم وعرض التنورة الحي.',
                        'ru' => 'Роскошный двухчасовой вечерний круиз на традиционной лодке дау или катамаране по Дубай Марина с ужином 5 звезд и живым танцем Танура.',
                        'es' => 'Disfruta de un crucero nocturno de 2 horas en dhow o catamarán por Dubai Marina con cena buffet internacional 5 estrellas y danza Tanoura en vivo.',
                        'it' => 'Goditi una crociera serale di 2 ore in dhow o catamarano a Dubai Marina con cena buffet internazionale 5 stelle e spettacolo Tanoura dal vivo.',
                    ],
                ],
                'dune-buggy-rental-dubai' => [
                    'name' => [
                        'ar' => 'تأجير عربات الباجي الصحراوية في دبي',
                        'ru' => 'Аренда багги в пустыне Дубая (Can-Am и Polaris)',
                        'es' => 'Alquiler de Buggies en el Desierto de Dubái (1000cc Can-Am)',
                        'it' => 'Noleggio Dune Buggy nel Deserto di Dubai (1000cc Can-Am)',
                    ],
                    'meta_title' => [
                        'ar' => 'تأجير باجي صحراوي في دبي 2026 | عربات كان-أم 1000 سي سي | من 599 درهم',
                        'ru' => 'Аренда багги в пустыне Дубая 2026 | Can-Am Maverick X3 1000cc | От 599 AED',
                        'es' => 'Alquiler de Dune Buggy en Dubái 2026 | Can-Am Maverick X3 1000cc | Desde 599 AED',
                        'it' => 'Noleggio Dune Buggy a Dubai 2026 | Can-Am Maverick X3 1000cc | Da 599 AED',
                    ],
                    'meta_desc' => [
                        'ar' => 'استأجر أقوى عربات الباجي الصحراوية 1000cc Can-Am Maverick وPolaris RZR في كثبان لهباب الحمراء بدبي مع معدات السلامة الكاملة وتوصيل 4x4.',
                        'ru' => 'Арендуйте мощные багги Can-Am Maverick X3 1000cc и Polaris RZR для езды по красным дюнам Лахбаб в Дубае с полной экипировкой и трансфером 4x4.',
                        'es' => 'Alquila potentes buggies Can-Am Maverick X3 1000cc y Polaris RZR en las dunas rojas de Lahbab en Dubái con equipo de seguridad y recogida 4x4.',
                        'it' => 'Noleggia potenti dune buggy Can-Am Maverick X3 1000cc e Polaris RZR sulle dune rosse di Lahbab a Dubai con equipaggiamento di sicurezza e pickup 4x4.',
                    ],
                ],
            ];

            foreach ($tourTranslations as $slug => $fields) {
                $tourRow = DB::table('tours')->where('slug', $slug)->first();
                if (! $tourRow) {
                    continue;
                }

                $updates = [];
                foreach ($fields as $col => $localeMap) {
                    $existingRaw = $tourRow->{$col} ?? '';
                    $dict = [];
                    if (is_string($existingRaw) && str_starts_with(trim($existingRaw), '{')) {
                        $decoded = json_decode(trim($existingRaw), true);
                        if (is_array($decoded)) {
                            $dict = $decoded;
                        }
                    } elseif (! empty($existingRaw)) {
                        $dict = ['en' => $existingRaw];
                    }

                    foreach ($localeMap as $langCode => $translatedText) {
                        if (empty($dict[$langCode])) {
                            $dict[$langCode] = $translatedText;
                        }
                    }

                    $updates[$col] = json_encode($dict, JSON_UNESCAPED_UNICODE);
                }

                if (! empty($updates)) {
                    DB::table('tours')->where('id', $tourRow->id)->update($updates);
                }
            }
        }

        try {
            Cache::forget('sitemap_tours_xml');
            Cache::forget('sitemap_blogs_xml');
            Cache::forget('sitemap_images_xml');
            Cache::forget('site_home_cache');
        } catch (Throwable $e) {
            // ignore cache driver errors during CLI migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive SEO data migration
    }
};
