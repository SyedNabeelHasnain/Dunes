<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pages') || !Schema::hasTable('page_sections') || !Schema::hasTable('menu_items')) {
            return;
        }

        // 1. Seed Pages & Sections
        $pages = [
            [
                'slug' => 'home',
                'name' => 'Homepage',
                'title' => json_encode([
                    'en' => 'Dubai Desert Safari Tours (2026)',
                    'ar' => 'رحلات سفاري صحراء دبي (2026)',
                    'ru' => 'Туры на сафари по пустыне в Дубае (2026)',
                    'es' => 'Tours de Safari por el Desierto de Dubái (2026)',
                    'it' => 'Tour Safari nel Deserto di Dubai (2026)',
                ]),
                'subtitle' => json_encode([
                    'en' => 'Book certified 5-star Dubai desert safaris, 1000cc buggies & VIP dhow cruises with instant confirmation.',
                    'ar' => 'احجز رحلات سفاري صحراوية معتمدة من فئة 5 نجوم وسيارات باجي 1000 سي سي ورحلات عشاء فاخرة بتأكيد فوري.',
                    'ru' => 'Забронируйте сертифицированные 5-звездочные сафари, багги 1000cc и круизы с мгновенным подтверждением.',
                    'es' => 'Reserve safaris certificados de 5 estrellas, buggies de 1000cc y cruceros VIP con confirmación inmediata.',
                    'it' => 'Prenota safari certificati a 5 stelle, buggy da 1000cc e crociere VIP con conferma istantanea.',
                ]),
                'sections' => [
                    [
                        'section_key' => 'silos',
                        'name' => 'Experience Silos',
                        'title' => json_encode([
                            'en' => 'Explore Dubai by Experience',
                            'ar' => 'استكشف دبي حسب التجربة',
                            'ru' => 'Исследуйте Дубай по впечатлениям',
                            'es' => 'Explore Dubái por Experiencia',
                            'it' => 'Esplora Dubai per Esperienza',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Choose your adventure: from adrenaline-filled red dunes to tranquil sunset dhow cruises.',
                            'ar' => 'اختر مغامرتك: من الكثبان الرملية الحمراء المليئة بالأدرينالين إلى رحلات العشاء الهادئة عند غروب الشمس.',
                            'ru' => 'Выберите приключение: от адреналина красных дюн до спокойных вечерних круизов.',
                            'es' => 'Elija su aventura: desde dunas rojas llenas de adrenalina hasta tranquilos cruceros al atardecer.',
                            'it' => 'Scegli la tua avventura: dalle dune rosse adrenaliniche alle tranquille crociere al tramonto.',
                        ]),
                    ],
                    [
                        'section_key' => 'concierge',
                        'name' => 'Safari Concierge Banner',
                        'title' => json_encode([
                            'en' => 'Need Help Deciding? Meet the Safari Concierge',
                            'ar' => 'تحتاج مساعدة في الاختيار؟ تعرف على مرشد السفاري الذكي',
                            'ru' => 'Нужна помощь с выбором? Попробуйте сафари-консьержа',
                            'es' => '¿Necesita ayuda para decidir? Conozca el Safari Concierge',
                            'it' => 'Hai bisogno di aiuto per scegliere? Prova il Safari Concierge',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Answer 3 quick questions to receive a tailored tour recommendation based on your travel style, budget, and group size.',
                            'ar' => 'أجب عن 3 أسئلة سريعة لتلقي توصية مخصصة للرحلة بناءً على أسلوب سفرك وميزانيتك وعدد أفراد مجموعتك.',
                            'ru' => 'Ответьте на 3 вопроса и получите персональную рекомендацию с учетом ваших предпочтений и бюджета.',
                            'es' => 'Responda 3 preguntas rápidas para recibir una recomendazione personalizada según su estilo y presupuesto.',
                            'it' => 'Rispondi a 3 rapide domande per ricevere una raccomandazione personalizzata in base alle tue esigenze.',
                        ]),
                    ],
                    [
                        'section_key' => 'why_choose_us',
                        'name' => 'Why Choose Us',
                        'title' => json_encode([
                            'en' => 'Why Dunes Discovery Tourism',
                            'ar' => 'لماذا ديونز ديسكفري للسياحة',
                            'ru' => 'Почему выбирают Dunes Discovery',
                            'es' => 'Por qué Elegir Dunes Discovery',
                            'it' => 'Perché Scegliere Dunes Discovery',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Dubai’s most trusted safari and adventure operator with premium hospitality standards.',
                            'ar' => 'الشركة الأكثر موثوقية لرحلات السفاري والمغامرات في دبي بأعلى معايير الضيافة الفاخرة.',
                            'ru' => 'Самый надежный оператор сафари и приключений в Дубае с высокими стандартами обслуживания.',
                            'es' => 'El operador de safaris y aventuras más confiable de Dubái con estándares de hospitalidad premium.',
                            'it' => 'Il tour operator di safari e avventure più affidabile di Dubai con standard di ospitalità premium.',
                        ]),
                    ],
                    [
                        'section_key' => 'reviews',
                        'name' => 'Verified Guest Reviews',
                        'title' => json_encode([
                            'en' => 'Verified Guest Reviews & Safari Photos',
                            'ar' => 'تقييمات الضيوف الموثقة وصور رحلات السفاري',
                            'ru' => 'Проверенные отзывы гостей и фотографии сафари',
                            'es' => 'Reseñas Verificadas de Huéspedes y Fotos del Safari',
                            'it' => 'Recensioni Verificate degli Ospiti e Foto del Safari',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Authentic experiences and real traveler snapshots from our certified Dubai desert tours.',
                            'ar' => 'تجارب حقيقية ولقطات للمسافرين من رحلات سفاري صحراء دبي المعتمدة لدينا.',
                            'ru' => 'Подлинные впечатления и реальные снимки путешественников из наших сертифицированных туров.',
                            'es' => 'Experiencias auténticas y fotos reales de viajeros de nuestros tours certificados por el desierto de Dubái.',
                            'it' => 'Esperienze autentiche e scatti reali dei viaggiatori dai nostri tour certificati nel deserto di Dubai.',
                        ]),
                    ],
                    [
                        'section_key' => 'desert_cta',
                        'name' => 'Bottom Desert Adventure CTA',
                        'title' => json_encode([
                            'en' => 'Ready for Your Dubai Desert Adventure?',
                            'ar' => 'هل أنت مستعد لمغامرة صحراء دبي؟',
                            'ru' => 'Готовы к приключению в пустыне Дубая?',
                            'es' => '¿Listo para su Aventura en el Desierto de Dubái?',
                            'it' => 'Pronto per la Tua Avventura nel Deserto di Dubai?',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Reserve your safari experience in 60 seconds with instant booking confirmation. Free cancellation up to 24 hours prior with full refund.',
                            'ar' => 'احجز تجربة السفاري خلال 60 ثانية مع تأكيد حجز فوري. إلغاء مجاني حتى 24 ساعة مسبقاً مع استرداد كامل المبلغ.',
                            'ru' => 'Забронируйте сафари за 60 секунд с мгновенным подтверждением. Бесплатная отмена за 24 часа с полным возвратом.',
                            'es' => 'Reserve su safari en 60 segundos con confirmación inmediata. Cancelación gratuita hasta 24 horas antes con reembolso completo.',
                            'it' => 'Prenota la tua esperienza safari in 60 secondi con conferma immediata. Cancellazione gratuita fino a 24 ore prima con rimborso completo.',
                        ]),
                    ],
                ],
            ],
            [
                'slug' => 'about',
                'name' => 'About Us Page',
                'title' => json_encode([
                    'en' => 'About Dunes Discovery Tourism',
                    'ar' => 'عن ديونز ديسكفري للسياحة',
                    'ru' => 'О компании Dunes Discovery Tourism',
                    'es' => 'Acerca de Dunes Discovery Tourism',
                    'it' => 'Chi Siamo - Dunes Discovery Tourism',
                ]),
                'subtitle' => json_encode([
                    'en' => 'Your licensed destination management partner for authentic Arabian desert expeditions & luxury Dubai tours.',
                    'ar' => 'شريكك المعتمد لإدارة الوجهات لرحلات استكشاف الصحراء العربية الأصيلة وجولات دبي الفاخرة.',
                    'ru' => 'Ваш лицензированный туроператор для аутентичных экспедиций по аравийской пустыне и премиальных туров.',
                    'es' => 'Su socio autorizado para auténticas expediciones por el desierto arábigo y tours de lujo en Dubái.',
                    'it' => 'Il tuo partner autorizzato per autentiche spedizioni nel deserto arabo e tour di lusso a Dubai.',
                ]),
                'sections' => [
                    [
                        'section_key' => 'story',
                        'name' => 'Our Story',
                        'title' => json_encode([
                            'en' => 'Our Journey in the Arabian Desert',
                            'ar' => 'رحلتنا في قلب الصحراء العربية',
                            'ru' => 'Наш путь в Аравийской пустыне',
                            'es' => 'Nuestro Viaje en el Desierto Arábigo',
                            'it' => 'Il Nostro Viaggio nel Deserto Arabo',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Founded in Dubai in 2018 with a vision to redefine desert adventure hospitality.',
                            'ar' => 'تأسست في دبي عام 2018 برؤية واضحة لإعادة صياغة ضيافة مغامرات الصحراء.',
                            'ru' => 'Основана в Дубае в 2018 году с целью поднять стандарты сафари на новый уровень.',
                            'es' => 'Fundada en Dubái en 2018 con la visión de redefinir la hospitalidad del safari en el desierto.',
                            'it' => 'Fondata a Dubai nel 2018 con la visione di ridefinire l’ospitalità delle avventure nel deserto.',
                        ]),
                    ],
                    [
                        'section_key' => 'happy_guests',
                        'name' => 'Happy Guests Counter',
                        'title' => json_encode([
                            'en' => '10,000+ Happy Guests and Counting',
                            'ar' => 'أكثر من 10,000 ضيف سعيد والعدد في تزايد',
                            'ru' => 'Более 10 000 довольных гостей',
                            'es' => 'Más de 10,000 Huéspedes Felices',
                            'it' => 'Oltre 10.000 Ospiti Soddisfatti',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Real travelers from over 85 countries trust us for their Dubai safari experiences.',
                            'ar' => 'مسافرون حقيقيون من أكثر من 85 دولة يثقون بنا لتجارب السفاري في دبي.',
                            'ru' => 'Путешественники из более чем 85 стран доверяют нам свой отдых в Дубае.',
                            'es' => 'Viajeros reales de más de 85 países confían en nosotros para sus experiencias de safari.',
                            'it' => 'Viaggiatori reali da oltre 85 paesi si affidano a noi per le loro esperienze di safari.',
                        ]),
                    ],
                ],
            ],
            [
                'slug' => 'tours_sidebar',
                'name' => 'Tour Detail Sidebar & Global Elements',
                'title' => json_encode([
                    'en' => 'Tour Sidebar Elements',
                    'ar' => 'عناصر الشريط الجانبي للجولات',
                    'ru' => 'Элементы боковой панели туров',
                    'es' => 'Elementos de la Barra Lateral de Tours',
                    'it' => 'Elementi della Barra Laterale dei Tour',
                ]),
                'subtitle' => json_encode([
                    'en' => 'Help widgets, booking box and assistance cards shown across all tour pages.',
                    'ar' => 'صناديق المساعدة وبطاقات الحجز المعروضة في جميع صفحات الجولات.',
                    'ru' => 'Виджеты помощи и блоки бронирования на страницах туров.',
                    'es' => 'Widgets de ayuda y bloques de reserva en páginas de tours.',
                    'it' => 'Widget di aiuto e box di prenotazione nelle pagine dei tour.',
                ]),
                'sections' => [
                    [
                        'section_key' => 'need_help',
                        'name' => 'Need Help Card',
                        'title' => json_encode([
                            'en' => 'Need Help?',
                            'ar' => 'هل تحتاج مساعدة؟',
                            'ru' => 'Нужна помощь?',
                            'es' => '¿Necesita Ayuda?',
                            'it' => 'Hai Bisogno di Aiuto?',
                        ]),
                        'subtitle' => json_encode([
                            'en' => 'Our travel experts are available 24/7 to help you with your booking.',
                            'ar' => 'خبراء السفر لدينا متاحون على مدار الساعة طوال أيام الأسبوع لمساعدتك في حجزك.',
                            'ru' => 'Наши эксперты по путешествиям доступны 24/7, чтобы помочь вам с бронированием.',
                            'es' => 'Nuestros expertos en viajes están disponibles las 24 horas, los 7 días de la semana para ayudarle con su reserva.',
                            'it' => 'I nostri esperti di viaggio sono disponibili 24/7 per assisterti con la prenotazione.',
                        ]),
                    ],
                ],
            ],
        ];

        foreach ($pages as $pData) {
            $sections = $pData['sections'] ?? [];
            unset($pData['sections']);
            $pData['created_at'] = now();
            $pData['updated_at'] = now();

            $existing = DB::table('pages')->where('slug', $pData['slug'])->first();
            if ($existing) {
                $pageId = $existing->id;
                DB::table('pages')->where('id', $pageId)->update($pData);
            } else {
                $pageId = DB::table('pages')->insertGetId($pData);
            }

            foreach ($sections as $index => $sData) {
                $sData['page_id'] = $pageId;
                $sData['order'] = $index + 1;
                $sData['is_active'] = true;
                $sData['created_at'] = now();
                $sData['updated_at'] = now();

                $existingSec = DB::table('page_sections')
                    ->where('page_id', $pageId)
                    ->where('section_key', $sData['section_key'])
                    ->first();

                if ($existingSec) {
                    DB::table('page_sections')->where('id', $existingSec->id)->update($sData);
                } else {
                    DB::table('page_sections')->insert($sData);
                }
            }
        }

        // 2. Seed Menu Items (Header & Footer)
        $menuItems = [
            // Header
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'Home', 'ar' => 'الرئيسية', 'ru' => 'Главная', 'es' => 'Inicio', 'it' => 'Home']),
                'url' => '/',
                'route_name' => 'home',
                'order' => 1,
            ],
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'All Experiences', 'ar' => 'جميع التجارب', 'ru' => 'Все туры', 'es' => 'Todas las Experiencias', 'it' => 'Tutte le Esperienze']),
                'url' => '/tours',
                'route_name' => 'tours.index',
                'order' => 2,
            ],
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'About Us', 'ar' => 'من نحن', 'ru' => 'О нас', 'es' => 'Nosotros', 'it' => 'Chi Siamo']),
                'url' => '/about',
                'route_name' => 'about',
                'order' => 3,
            ],
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'Safari Journal', 'ar' => 'مدونة السفاري', 'ru' => 'Блог о сафари', 'es' => 'Diario de Safari', 'it' => 'Diario di Safari']),
                'url' => '/blog',
                'route_name' => 'blog.index',
                'order' => 4,
            ],
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'FAQs', 'ar' => 'الأسئلة الشائعة', 'ru' => 'Вопросы и ответы', 'es' => 'Preguntas Frecuentes', 'it' => 'Domande Frequenti']),
                'url' => '/faq',
                'route_name' => 'faq',
                'order' => 5,
            ],
            [
                'location' => 'header',
                'label' => json_encode(['en' => 'Contact', 'ar' => 'تواصل معنا', 'ru' => 'Контакты', 'es' => 'Contacto', 'it' => 'Contatti']),
                'url' => '/contact',
                'route_name' => 'contact',
                'order' => 6,
            ],

            // Footer Col 2: Desert Safaris
            [
                'location' => 'footer_desert_safaris',
                'label' => json_encode(['en' => 'Evening Safari', 'ar' => 'سفاري مسائي', 'ru' => 'Вечернее сафари', 'es' => 'Safari Nocturno', 'it' => 'Safari Serale']),
                'url' => '/evening-desert-safari-dubai',
                'route_name' => 'tours.show',
                'order' => 1,
            ],
            [
                'location' => 'footer_desert_safaris',
                'label' => json_encode(['en' => 'Morning Safari', 'ar' => 'سفاري صباحي', 'ru' => 'Утреннее сафари', 'es' => 'Safari Matutino', 'it' => 'Safari Mattutino']),
                'url' => '/morning-desert-safari-dubai',
                'route_name' => 'tours.show',
                'order' => 2,
            ],
            [
                'location' => 'footer_desert_safaris',
                'label' => json_encode(['en' => 'Overnight Safari', 'ar' => 'سفاري المبيت', 'ru' => 'Ночное сафари с ночевкой', 'es' => 'Safari Nocturno con Pernoctación', 'it' => 'Safari con Pernottamento']),
                'url' => '/overnight-desert-safari-dubai',
                'route_name' => 'tours.show',
                'order' => 3,
            ],
            [
                'location' => 'footer_desert_safaris',
                'label' => json_encode(['en' => 'Quad Biking Safari', 'ar' => 'سفاري الدراجات الرباعية', 'ru' => 'Сафари на квадроциклах', 'es' => 'Safari en Quads', 'it' => 'Safari in Quad']),
                'url' => '/desert-safari-quad-biking-dubai',
                'route_name' => 'tours.show',
                'order' => 4,
            ],
            [
                'location' => 'footer_desert_safaris',
                'label' => json_encode(['en' => 'VIP Desert Safari', 'ar' => 'سفاري صحراوي لكبار الشخصيات', 'ru' => 'VIP сафари по пустыне', 'es' => 'Safari VIP por el Desierto', 'it' => 'Safari VIP nel Deserto']),
                'url' => '/luxury-vip-desert-safari-dubai',
                'route_name' => 'tours.show',
                'order' => 5,
            ],

            // Footer Col 3: Tours & Cruises
            [
                'location' => 'footer_tours_cruises',
                'label' => json_encode(['en' => 'Dubai City Tour', 'ar' => 'جولة مدينة دبي', 'ru' => 'Обзорная экскурсия по Дубаю', 'es' => 'Tour por la Ciudad de Dubái', 'it' => 'Tour della Città di Dubai']),
                'url' => '/dubai-city-tour',
                'route_name' => 'tours.show',
                'order' => 1,
            ],
            [
                'location' => 'footer_tours_cruises',
                'label' => json_encode(['en' => 'Abu Dhabi Tour', 'ar' => 'جولة أبوظبي', 'ru' => 'Экскурсия в Абу-Даби', 'es' => 'Tour por Abu Dabi', 'it' => 'Tour di Abu Dhabi']),
                'url' => '/abu-dhabi-city-tour-from-dubai',
                'route_name' => 'tours.show',
                'order' => 2,
            ],
            [
                'location' => 'footer_tours_cruises',
                'label' => json_encode(['en' => 'Marina Cruise', 'ar' => 'رحلة بحرية في مارينا', 'ru' => 'Круиз по Дубай Марина', 'es' => 'Crucero por la Marina', 'it' => 'Crociera a Dubai Marina']),
                'url' => '/dhow-cruise-catamaran-cruise-dinner-dubai',
                'route_name' => 'tours.show',
                'order' => 3,
            ],
            [
                'location' => 'footer_tours_cruises',
                'label' => json_encode(['en' => 'Rate Card (PDF)', 'ar' => 'جدول الأسعار (PDF)', 'ru' => 'Прайс-лист (PDF)', 'es' => 'Tarifario (PDF)', 'it' => 'Listino Prezzi (PDF)']),
                'url' => '/rate-card',
                'route_name' => 'rate-card',
                'order' => 4,
            ],
            [
                'location' => 'footer_tours_cruises',
                'label' => json_encode(['en' => 'Safari Journal', 'ar' => 'مدونة ودليل السفاري', 'ru' => 'Журнал о сафари', 'es' => 'Diario del Safari', 'it' => 'Diario del Safari']),
                'url' => '/blog',
                'route_name' => 'blog.index',
                'order' => 5,
            ],

            // Footer Col 4: Trust & Policies
            [
                'location' => 'footer_trust_policies',
                'label' => json_encode(['en' => 'Terms & Conditions', 'ar' => 'الشروط والأحكام', 'ru' => 'Условия и положения', 'es' => 'Términos y Condiciones', 'it' => 'Termini e Condizioni']),
                'url' => '/terms-condition',
                'route_name' => 'terms',
                'order' => 1,
            ],
            [
                'location' => 'footer_trust_policies',
                'label' => json_encode(['en' => 'Privacy Policy', 'ar' => 'سياسة الخصوصية', 'ru' => 'Политика конфиденциальности', 'es' => 'Política de Privacidad', 'it' => 'Informativa sulla Privacy']),
                'url' => '/privacy-policy',
                'route_name' => 'privacy',
                'order' => 2,
            ],
            [
                'location' => 'footer_trust_policies',
                'label' => json_encode(['en' => 'Cookie Policy', 'ar' => 'سياسة ملفات تعريف الارتباط', 'ru' => 'Политика файлов cookie', 'es' => 'Política de Cookies', 'it' => 'Politica sui Cookie']),
                'url' => '/cookie-policy',
                'route_name' => 'cookies',
                'order' => 3,
            ],
            [
                'location' => 'footer_trust_policies',
                'label' => json_encode(['en' => 'Cancellation & Refund', 'ar' => 'سياسة الإلغاء والاسترداد', 'ru' => 'Отмена и возврат средств', 'es' => 'Cancelación y Reembolso', 'it' => 'Cancellazione e Rimborso']),
                'url' => '/cancellation-policy',
                'route_name' => 'cancellation',
                'order' => 4,
            ],
            [
                'location' => 'footer_trust_policies',
                'label' => json_encode(['en' => 'Payment Security', 'ar' => 'أمان المدفوعات', 'ru' => 'Безопасность платежей', 'es' => 'Seguridad de Pago', 'it' => 'Sicurezza dei Pagamenti']),
                'url' => '/payment-security-policy',
                'route_name' => 'payment.security',
                'order' => 5,
            ],
        ];

        foreach ($menuItems as $m) {
            $m['is_active'] = true;
            $m['target'] = '_self';
            $m['created_at'] = now();
            $m['updated_at'] = now();

            $existing = DB::table('menu_items')
                ->where('location', $m['location'])
                ->where('url', $m['url'])
                ->first();

            if ($existing) {
                DB::table('menu_items')->where('id', $existing->id)->update($m);
            } else {
                DB::table('menu_items')->insert($m);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible migration
    }
};
