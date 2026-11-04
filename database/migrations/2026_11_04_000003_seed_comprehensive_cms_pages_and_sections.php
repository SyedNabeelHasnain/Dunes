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
        if (! Schema::hasTable('pages') || ! Schema::hasTable('page_sections')) {
            return;
        }

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
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Book certified 5-star Dubai desert safaris, 1000cc buggies & VIP dhow cruises with instant confirmation.',
                    'ar' => 'احجز رحلات سفاري صحراوية معتمدة من فئة 5 نجوم وسيارات باجي 1000 سي سي ورحلات عشاء فاخرة بتأكيد فوري.',
                    'ru' => 'Забронируйте сертифицированные 5-звездочные сафари, багги 1000cc и круизы с мгновенным подтверждением.',
                    'es' => 'Reserve safaris certificados de 5 estrellas, buggies de 1000cc y cruceros VIP con confirmación inmediata.',
                    'it' => 'Prenota safari certificati a 5 stelle, buggy da 1000cc e crociere VIP con conferma istantanea.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'trust_bar',
                        'name' => 'Trust & Safety Assurance Bar',
                        'title' => json_encode([
                            'en' => 'Licensed & Insured Dubai Operator',
                            'ar' => 'مشغل رحلات مرخص ومؤمن في دبي',
                            'ru' => 'Лицензированный туроператор в Дубае',
                            'es' => 'Operador Autorizado y Asegurado en Dubái',
                            'it' => 'Operatore Autorizzato e Assicurato a Dubai',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Govt. Approved Dubai Tourism (DET) #1430583 • 4.9/5 Rated (2,800+ Reviews) • 24hr Free Cancellation • Instant WhatsApp Voucher',
                            'ar' => 'مرخص من دائرة الاقتصاد والسياحة بدبي #1430583 • تقييم 4.9/5 • إلغاء مجاني حتى 24 ساعة • قسيمة واتساب فورية',
                            'ru' => 'Лицензия Дубайского туризма #1430583 • Рейтинг 4.9/5 • Бесплатная отмена за 24ч • Мгновенный ваучер',
                            'es' => 'Aprobado por Turismo de Dubái (DET) #1430583 • Calificación 4.9/5 • Cancelación Gratuita 24h • Cupón WhatsApp Inmediato',
                            'it' => 'Autorizzato dal Turismo di Dubai #1430583 • Valutato 4.9/5 • Cancellazione Gratuita 24h • Voucher WhatsApp Istantaneo',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Verified Quality & Safety',
                            'ar' => 'جودة وأمان موثقان',
                            'ru' => 'Проверенное качество и безопасность',
                            'es' => 'Calidad y Seguridad Verificadas',
                            'it' => 'Qualità e Sicurezza Verificate',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                    [
                        'section_key' => 'silos',
                        'name' => 'Experience Silos',
                        'title' => json_encode([
                            'en' => 'Explore Dubai by Experience',
                            'ar' => 'استكشف دبي حسب التجربة',
                            'ru' => 'Исследуйте Дубай по впечатлениям',
                            'es' => 'Explore Dubái por Experiencia',
                            'it' => 'Esplora Dubai per Esperienza',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Choose your adventure: from adrenaline-filled red dunes to tranquil sunset dhow cruises.',
                            'ar' => 'اختر مغامرتك: من الكثبان الرملية الحمراء المليئة بالأدرينالين إلى رحلات العشاء الهادئة عند غروب الشمس.',
                            'ru' => 'Выберите приключение: от адреналина красных дюн до спокойных вечерних круизов.',
                            'es' => 'Elija su aventura: desde dunas rojas llenas de adrenalina hasta tranquilos cruceros al atardecer.',
                            'it' => 'Scegli la tua avventura: dalle dune rosse adrenaliniche alle tranquille crociere al tramonto.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Explore by Experience',
                            'ar' => 'استكشف حسب التجربة',
                            'ru' => 'По категориям',
                            'es' => 'Explorar por Experiencia',
                            'it' => 'Esplora per Esperienza',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                    [
                        'section_key' => 'featured_tours',
                        'name' => 'Top Desert Safari Tours Showcase',
                        'title' => json_encode([
                            'en' => 'Most Popular Dubai Desert Safaris',
                            'ar' => 'أشهر رحلات سفاري صحراء دبي',
                            'ru' => 'Самые популярные сафари в пустыне Дубая',
                            'es' => 'Los Safaris por el Desierto Más Populares de Dubái',
                            'it' => 'I Safari nel Deserto Più Popolari di Dubai',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Certified 5-star desert safaris with dune bashing, camel rides, quad biking & luxury BBQ dining.',
                            'ar' => 'رحلات سفاري صحراوية معتمدة من فئة 5 نجوم تشمل ركوب الكثبان الرملية وركوب الجمال ودراجات الدفع الرباعي وعشاء مشاوي فاخر.',
                            'ru' => 'Сертифицированные 5-звездочные сафари с джип-сафари, катанием на верблюдах, квадроциклах и барбекю.',
                            'es' => 'Safaris certificados de 5 estrellas con dunas, paseos en camello, quads y cena barbacoa de lujo.',
                            'it' => 'Safari certificati a 5 stelle con dune bashing, cammellate, quad e barbecue di lusso.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Handpicked Desert Adventures',
                            'ar' => 'مغامرات صحراوية مختارة بعناية',
                            'ru' => 'Лучшие приключения в пустыне',
                            'es' => 'Aventuras en el Desierto Seleccionadas',
                            'it' => 'Avventure nel Deserto Selezionate',
                        ], JSON_UNESCAPED_UNICODE),
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
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Authentic experiences and real traveler snapshots from our certified Dubai desert tours.',
                            'ar' => 'تجارب حقيقية ولقطات للمسافرين من رحلات سفاري صحراء دبي المعتمدة لدينا.',
                            'ru' => 'Подлинные впечатления и реальные снимки путешественников из наших сертифицированных туров.',
                            'es' => 'Experiencias auténticas y fotos reales de viajeros de nuestros tours certificados por el desierto de Dubái.',
                            'it' => 'Esperienze autentiche e scatti reali dei viaggiatori dai nostri tour certificati nel deserto di Dubai.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Real Feedback',
                            'ar' => 'آراء حقيقية',
                            'ru' => 'Реальные отзывы',
                            'es' => 'Opiniones Reales',
                            'it' => 'Feedback Reale',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                    [
                        'section_key' => 'videos',
                        'name' => 'Customer Video Reviews Reel',
                        'title' => json_encode([
                            'en' => 'Watch Real Travelers in the Dubai Dunes',
                            'ar' => 'شاهد تجارب المسافرين الحقيقية في كثبان دبي',
                            'ru' => 'Посмотрите реальные видео гостей в дюнах Дубая',
                            'es' => 'Vea a Viajeros Reales en las Dunas de Dubái',
                            'it' => 'Guarda i Veri Viaggiatori nelle Dune di Dubai',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Unfiltered video feedback straight from our guests in Polaris RZR buggies, red dune bashing, and Bedouin desert camps.',
                            'ar' => 'مقاطع فيديو حقيقية ومباشرة من ضيوفنا في سيارات بولاريس، ركوب الكثبان الحمراء، ومخيمات البدو الصحراوية.',
                            'ru' => 'Живые видео-отзывы от наших гостей: багги Polaris, дюны Лахбаб и бедуинские лагеря.',
                            'es' => 'Opiniones en vídeo sin filtros de nuestros huéspedes en buggies Polaris RZR, dunas rojas y campamentos beduinos.',
                            'it' => 'Video feedback autentico direttamente dai nostri ospiti su buggy Polaris RZR, dune bashing e accampamenti beduini.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Real Customer Video Reviews',
                            'ar' => 'فيديوهات تقييمات العملاء الحقيقية',
                            'ru' => 'Видео-отзывы реальных клиентов',
                            'es' => 'Reseñas en Vídeo de Clientes Reales',
                            'it' => 'Video Recensioni dei Clienti Reali',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                    [
                        'section_key' => 'moments',
                        'name' => 'Live Desert Moments Photo Stream',
                        'title' => json_encode([
                            'en' => 'Live Desert Moments & Guest Captures',
                            'ar' => 'لحظات صحراوية حية ولقطات الضيوف',
                            'ru' => 'Живые моменты пустыни и снимки гостей',
                            'es' => 'Momentos Vivos en el Desierto y Fotos de Huéspedes',
                            'it' => 'Momenti Vivi nel Deserto e Scatti degli Ospiti',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Authentic, high-resolution snapshots captured daily by our desert captains and safari guests across Lahbab Red Dunes.',
                            'ar' => 'لقطات أصيلة وعالية الدقة يتم التقاطها يومياً بواسطة قباطنة الصحراء وضيوفنا عبر الكثبان الرملية الحمراء.',
                            'ru' => 'Подлинные фотографии высокого разрешения, снятые ежедневно нашими гидами и гостями в красных дюнах Лахбаб.',
                            'es' => 'Instantáneas auténticas de alta resolución capturadas a diario por nuestros guías y huéspedes en las dunas rojas.',
                            'it' => 'Scatti autentici ad alta risoluzione catturati ogni giorno dai nostri capitani del deserto e dagli ospiti nelle dune rosse.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Captured in Lahbab',
                            'ar' => 'التقطت في الهباب',
                            'ru' => 'Снято в Лахбабе',
                            'es' => 'Capturado en Lahbab',
                            'it' => 'Catturato a Lahbab',
                        ], JSON_UNESCAPED_UNICODE),
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
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Answer 3 quick questions to receive a tailored tour recommendation based on your travel style, budget, and group size.',
                            'ar' => 'أجب عن 3 أسئلة سريعة لتلقي توصية مخصصة للرحلة بناءً على أسلوب سفرك وميزانيتك وعدد أفراد مجموعتك.',
                            'ru' => 'Ответьте на 3 вопроса и получите персональную рекомендацию с учетом ваших предпочтений и бюджета.',
                            'es' => 'Responda 3 preguntas rápidas para recibir una recomendación personalizada según su estilo y presupuesto.',
                            'it' => 'Rispondi a 3 rapide domande per ricevere una raccomandazione personalizzata in base alle tue esigenze.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Instant Trip Matcher',
                            'ar' => 'مطابقة الرحلة الفورية',
                            'ru' => 'Мгновенный подбор тура',
                            'es' => 'Buscador de Viaje Inmediato',
                            'it' => 'Selettore Rapido di Viaggio',
                        ], JSON_UNESCAPED_UNICODE),
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
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Dubai’s most trusted safari and adventure operator with premium hospitality standards.',
                            'ar' => 'الشركة الأكثر موثوقية لرحلات السفاري والمغامرات في دبي بأعلى معايير الضيافة الفاخرة.',
                            'ru' => 'Самый надежный оператор саفاри и приключений в Дубае с высокими стандартами обслуживания.',
                            'es' => 'El operador de safaris y aventuras más confiable de Dubái con estándares de hospitalidad premium.',
                            'it' => 'Il tour operator di safari e avventure più affidabile di Dubai con standard di ospitalità premium.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'The Dunes Discovery Difference',
                            'ar' => 'ميزة ديونز ديسكفري',
                            'ru' => 'Преимущества Dunes Discovery',
                            'es' => 'La Diferencia de Dunes Discovery',
                            'it' => 'La Differenza di Dunes Discovery',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                    [
                        'section_key' => 'faqs',
                        'name' => 'Frequently Asked Questions',
                        'title' => json_encode([
                            'en' => 'Frequently Asked Questions',
                            'ar' => 'الأسئلة الشائعة والمتكررة',
                            'ru' => 'Часто задаваемые вопросы',
                            'es' => 'Preguntas Frecuentes',
                            'it' => 'Domande Frequenti',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Everything you need to know about booking, pickup locations, clothing advice, and dune safety.',
                            'ar' => 'كل ما تحتاج إلى معرفته حول الحجز وأماكن الالتقاء والملابس المناسبة وسلامة الكثبان الرملية.',
                            'ru' => 'Все, что нужно знать о бронировании, трансфере, одежде и безопасности в пустыне.',
                            'es' => 'Todo lo que necesita saber sobre reservas, puntos de recogida, vestimenta y seguridad en las dunas.',
                            'it' => 'Tutto quello che c\'è da sapere su prenotazioni, punti di ritiro, abbigliamento e sicurezza sulle dune.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Got Questions?',
                            'ar' => 'هل لديك استفسار؟',
                            'ru' => 'Есть вопросы?',
                            'es' => '¿Tiene Preguntas?',
                            'it' => 'Hai Domande?',
                        ], JSON_UNESCAPED_UNICODE),
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
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Reserve your safari experience in 60 seconds with instant booking confirmation. Free cancellation up to 24 hours prior with full refund.',
                            'ar' => 'احجز تجربة السفاري خلال 60 ثانية مع تأكيد حجز فوري. إلغاء مجاني حتى 24 ساعة مسبقاً مع استرداد كامل المبلغ.',
                            'ru' => 'Забронируйте сафари за 60 секунд с мгновенным подтверждением. Бесплатная отмена за 24 часа с полным возвратом.',
                            'es' => 'Reserve su safari en 60 segundos con confirmación inmediata. Cancelación gratuita hasta 24 horas antes con reembolso completo.',
                            'it' => 'Prenota la tua esperienza safari in 60 secondi con conferma immediata. Cancellazione gratuita fino a 24 ore prima con rimborso completo.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => json_encode([
                            'en' => 'Instant Online Booking',
                            'ar' => 'حجز فوري عبر الإنترنت',
                            'ru' => 'Мгновенное онлайн-бронирование',
                            'es' => 'Reserva Online Inmediata',
                            'it' => 'Prenotazione Online Istantanea',
                        ], JSON_UNESCAPED_UNICODE),
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
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Your licensed destination management partner for authentic Arabian desert expeditions & luxury Dubai tours.',
                    'ar' => 'شريكك المعتمد لإدارة الوجهات لرحلات استكشاف الصحراء العربية الأصيلة وجولات دبي الفاخرة.',
                    'ru' => 'Ваш лицензированный туроператор для аутентичных экспедиций по аравийской пустыне и премиальных туров.',
                    'es' => 'Su socio autorizado para auténticas expediciones por el desierto arábigo y tours de lujo en Dubái.',
                    'it' => 'Il tuo partner autorizzato per autentiche spedizioni nel deserto arabo e tour di lusso a Dubai.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'trust_bar',
                        'name' => 'Operations Highlights & Stats Strip',
                        'title' => json_encode([
                            'en' => '10,000+ Happy Guests • 4.9/5 Rating • Own 4x4 Fleet',
                            'ar' => 'أكثر من 10,000 ضيف سعيد • تقييم 4.9/5 • أسطول دفع رباعي خاص',
                            'ru' => 'Более 10 000 гостей • Рейтинг 4.9/5 • Собственный автопарк',
                            'es' => 'Más de 10,000 Huéspedes • 4.9/5 Estrellas • Flota Propia 4x4',
                            'it' => 'Oltre 10.000 Ospiti • Valutazione 4.9/5 • Flotta 4x4 Di Proprietà',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Licensed Dubai Tourism operator with master captains and premium camp hospitality.',
                            'ar' => 'مشغل سياحي معتمد في دبي مع قباطنة محترفين وأعلى معايير الضيافة في المخيمات.',
                            'ru' => 'Лицензированный оператор с опытными гидами и премиальным сервисом.',
                            'es' => 'Operador autorizado de turismo en Dubái con capitanes expertos y campamentos premium.',
                            'it' => 'Operatore turistico autorizzato di Dubai con capitani esperti e ospitalità premium.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                    [
                        'section_key' => 'story',
                        'name' => 'Our Story & Heritage',
                        'title' => json_encode([
                            'en' => 'Redefining Dubai Desert Tourism Hospitality',
                            'ar' => 'إعادة صياغة ضيافة سياحة الصحراء في دبي',
                            'ru' => 'Новый стандарт гостеприимства в пустыне Дубая',
                            'es' => 'Redefiniendo la Hospitalidad del Turismo en el Desierto de Dubái',
                            'it' => 'Ridefinire l\'Ospitalità del Turismo nel Deserto di Dubai',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Founded in Dubai with a vision to deliver genuine Arabian hospitality and thrilling expeditions with own luxury fleet.',
                            'ar' => 'تأسست في دبي برؤية لتقديم ضيافة عربية أصيلة ورحلات استكشافية مثيرة بأسطولنا الفاخر الخاص.',
                            'ru' => 'Основана в Дубае с целью предоставить подлинное арабское гостеприимство и захватывающие экспедиции на собственном автопарке.',
                            'es' => 'Fundada en Dubái con la visión de ofrecer auténtica hospitalidad árabe y emocionantes expediciones con flota propia.',
                            'it' => 'Fondata a Dubai con la visione di offrire autentica ospitalità araba ed entusiasmanti spedizioni con la propria flotta.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                    [
                        'section_key' => 'values',
                        'name' => 'What Sets Us Apart (Core Pillars)',
                        'title' => json_encode([
                            'en' => 'Why Travelers Choose Dunes Discovery',
                            'ar' => 'لماذا يختار المسافرون ديونز ديسكفري',
                            'ru' => 'Почему путешественники выбирают Dunes Discovery',
                            'es' => 'Por Qué los Viajeros Eligen Dunes Discovery',
                            'it' => 'Perché i Viaggiatori Scelgono Dunes Discovery',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Own 4x4 fleet, certified master safari drivers, transparent direct pricing, and VIP private Bedouin camps.',
                            'ar' => 'أسطول دفع رباعي مملوك بالكامل، قباطنة سفاري معتمدون، أسعار شفافة ومباشرة، ومخيمات بدوية خاصة لكبار الشخصيات.',
                            'ru' => 'Собственный автопарк 4x4, сертифицированные гиды, честные прямые цены и VIP-лагеря.',
                            'es' => 'Flota propia 4x4, guías de safari certificados, precios directos transparentes y campamentos beduinos VIP.',
                            'it' => 'Flotta 4x4 propria, guide safari certificate, prezzi diretti e trasparenti e accampamenti beduini VIP.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
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
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Real travelers from over 85 countries trust us for their Dubai safari experiences.',
                            'ar' => 'مسافرون حقيقيون من أكثر من 85 دولة يثقون بنا لتجارب السفاري في دبي.',
                            'ru' => 'Путешественники из более чем 85 стран доверяют нам свой отдых в Дубае.',
                            'es' => 'Viajeros reales de más de 85 países confían en nosotros para sus experiencias de safari.',
                            'it' => 'Viaggiatori reali da oltre 85 paesi si affidano a noi per le loro esperienze di safari.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                ],
            ],
            [
                'slug' => 'contact',
                'name' => 'Contact Us Page',
                'title' => json_encode([
                    'en' => 'Contact Dunes Discovery Tourism',
                    'ar' => 'اتصل بـ ديونز ديسكفري للسياحة',
                    'ru' => 'Свяжитесь с Dunes Discovery Tourism',
                    'es' => 'Contacte con Dunes Discovery Tourism',
                    'it' => 'Contatta Dunes Discovery Tourism',
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Our desert safari specialists are available 24/7 to assist with bookings, VIP custom packages, and instant queries.',
                    'ar' => 'فريقنا المتخصص في رحلات السفاري متاح على مدار الساعة للمساعدة في الحجوزات والباقات الخاصة والاستفسارات الفورية.',
                    'ru' => 'Наши специалисты по сафари доступны 24/7 для помощи с бронированием, VIP-турами и вопросами.',
                    'es' => 'Nuestros especialistas en safaris están disponibles las 24 horas, los 7 días de la semana para reservas y consultas.',
                    'it' => 'I nostri specialisti di safari sono disponibili 24/7 per assistenza con prenotazioni, pacchetti VIP e domande rapide.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'channels',
                        'name' => 'Fast Support Channels',
                        'title' => json_encode([
                            'en' => 'Get in Touch in Seconds',
                            'ar' => 'تواصل معنا خلال ثوانٍ',
                            'ru' => 'Свяжитесь с нами за секунды',
                            'es' => 'Póngase en Contacto en Segundos',
                            'it' => 'Mettiti in Contatto in Pochi Secondi',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'WhatsApp, direct phone hotline, operational email, or visit our licensed Dubai headquarters.',
                            'ar' => 'واتساب، هاتف مباشر، بريد إلكتروني، أو قم بزيارة مقرنا المعتمد في دبي.',
                            'ru' => 'WhatsApp, телефонная линия, email или визит в наш лицензированный офис в Дубае.',
                            'es' => 'WhatsApp, teléfono directo, correo electrónico o visite nuestra sede en Dubái.',
                            'it' => 'WhatsApp, telefono diretto, email o visita la nostra sede a Dubai.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                    [
                        'section_key' => 'office_info',
                        'name' => 'Office Headquarters & Licensing',
                        'title' => json_encode([
                            'en' => 'Dunes Discovery Tourism LLC Headquarters',
                            'ar' => 'المقر الرئيسي لشركة ديونز ديسكفري للسياحة ش.ذ.م.م',
                            'ru' => 'Главный офис Dunes Discovery Tourism LLC',
                            'es' => 'Sede Central de Dunes Discovery Tourism LLC',
                            'it' => 'Sede Centrale Dunes Discovery Tourism LLC',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Office 702, Al Tawhidi Building 1, Al Mankhool, Bur Dubai, Dubai, UAE • License #1430583',
                            'ar' => 'مكتب 702، بناية التوحيدي 1، المنخول، بر دبي، دبي، الإمارات • ترخيص #1430583',
                            'ru' => 'Офис 702, Al Tawhidi Building 1, Аль-Манхул, Бар-Дубай, ОАЭ • Лицензия #1430583',
                            'es' => 'Oficina 702, Al Tawhidi Building 1, Al Mankhool, Bur Dubai, Dubái, EAU • Licencia #1430583',
                            'it' => 'Ufficio 702, Al Tawhidi Building 1, Al Mankhool, Bur Dubai, Dubai, EAU • Licenza #1430583',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                ],
            ],
            [
                'slug' => 'faq',
                'name' => 'FAQ Hub Page',
                'title' => json_encode([
                    'en' => 'Frequently Asked Questions Hub',
                    'ar' => 'مركز الأسئلة الشائعة والمتكررة',
                    'ru' => 'Центр ответов на часто задаваемые вопросы',
                    'es' => 'Centro de Preguntas Frecuentes',
                    'it' => 'Centro Domande Frequenti',
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Find instant answers regarding pickup timings, dress code, dune safety, payment options, and cancellation rules.',
                    'ar' => 'اعثر على إجابات فورية بخصوص مواعيد التوصيل، الملابس المناسبة، سلامة الكثبان، خيارات الدفع وشروط الإلغاء.',
                    'ru' => 'Быстрые ответы на вопросы о трансфере, дресс-коде, безопасности, оплате и правилах отмены.',
                    'es' => 'Encuentre respuestas rápidas sobre horarios de recogida, vestimenta, seguridad, opciones de pago y cancelación.',
                    'it' => 'Trova risposte immediate su orari di ritiro, abbigliamento, sicurezza sulle dune, opzioni di pagamento e cancellazione.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'support_banner',
                        'name' => 'Need More Help Banner',
                        'title' => json_encode([
                            'en' => 'Still Have Questions? Chat with Safari Captains',
                            'ar' => 'هل لا يزال لديك أسئلة؟ تحدث مباشرة مع قباطنة السفاري',
                            'ru' => 'Остались вопросы? Напишите нашим гидам в WhatsApp',
                            'es' => '¿Aún tiene preguntas? Hable con los capitanes de safari',
                            'it' => 'Hai ancora domande? Chatta con i capitani del safari',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Our live tour operations desk responds within 2 minutes on WhatsApp to answer any specific query.',
                            'ar' => 'مكتب العمليات المباشر لدينا يجيب خلال دقيقتين عبر واتساب للرد على أي استفسار محدد.',
                            'ru' => 'Наша служба поддержки отвечает в течение 2 минут в WhatsApp.',
                            'es' => 'Nuestro equipo de operaciones responde en 2 minutos por WhatsApp para cualquier consulta.',
                            'it' => 'Il nostro desk operativo risponde entro 2 minuti su WhatsApp per qualsiasi richiesta specifica.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                ],
            ],
            [
                'slug' => 'gallery',
                'name' => 'Media & Moments Gallery Page',
                'title' => json_encode([
                    'en' => 'Authentic Guest Moments & Desert Safari Visuals',
                    'ar' => 'لحظات الضيوف الأصيلة ومرئيات سفاري الصحراء',
                    'ru' => 'Подлинные моменты гостей и галерея сафари',
                    'es' => 'Momentos Auténticos de Huéspedes y Galería del Safari',
                    'it' => 'Momenti Autentici degli Ospiti e Galleria del Safari',
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Explore real 4x4 dune bashing action, Polaris RZR adventures, camel rides, and sunset moments across Dubai.',
                    'ar' => 'استكشف مغامرات سيارات الدفع الرباعي الحقيقية وبولاريس وركوب الجمال ولحظات غروب الشمس الساحرة في دبي.',
                    'ru' => 'Посмотрите реальные кадры сафари 4х4, багги Polaris, верблюдов и заката в пустыне.',
                    'es' => 'Explore la auténtica acción 4x4 en las dunas, aventuras en Polaris RZR, paseos en camello y atardeceres.',
                    'it' => 'Esplora l\'azione autentica del dune bashing 4x4, le avventure in Polaris RZR, le cammellate e i tramonti a Dubai.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'video_reels',
                        'name' => 'Guest Video Reels Section',
                        'title' => json_encode([
                            'en' => 'Watch Unfiltered Guest Stories & Video Reviews',
                            'ar' => 'شاهد قصص وتجارب ضيوفنا الحقيقية بالفيديو',
                            'ru' => 'Посмотрите реальные видео-истории гостей и отзывы',
                            'es' => 'Vea Historias Reales de Huéspedes y Reseñas en Vídeo',
                            'it' => 'Guarda le Storie Reali degli Ospiti e le Video Recensioni',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Real, unedited vertical videos recorded by tourists enjoying our morning, evening, and VIP desert expeditions.',
                            'ar' => 'مقاطع فيديو عمودية حقيقية وغير معدلة سجلها السياح أثناء استمتاعهم برحلات السفاري الصباحية والمسائية والـ VIP.',
                            'ru' => 'Реальные вертикальные видео от туристов с утренних, вечерних и VIP сафари.',
                            'es' => 'Vídeos verticales reales y sin editar grabados por turistas en nuestros safaris.',
                            'it' => 'Video verticali autentici e non modificati registrati dai turisti durante le nostre spedizioni nel deserto.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                    [
                        'section_key' => 'cta',
                        'name' => 'Gallery Experience CTA',
                        'title' => json_encode([
                            'en' => 'Ready to Create Your Own Dubai Desert Memories?',
                            'ar' => 'هل أنت مستعد لصنع ذكرياتك الخاصة في صحراء دبي؟',
                            'ru' => 'Готовы создать свои воспоминания в пустыне Дубая?',
                            'es' => '¿Listo para Crear Sus Propios Recuerdos en el Desierto de Dubái?',
                            'it' => 'Pronto a Creare i Tuoi Ricordi nel Deserto di Dubai?',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Book online in 60 seconds. Instant booking voucher delivered right to your WhatsApp.',
                            'ar' => 'احجز عبر الإنترنت خلال 60 ثانية. قسيمة الحجز الفوري تصل مباشرة إلى تطبيق واتساب الخاص بك.',
                            'ru' => 'Забронируйте онлайн за 60 секунд. Мгновенный ваучер в ваш WhatsApp.',
                            'es' => 'Reserve en línea en 60 segundos. Cupón instantáneo directo a su WhatsApp.',
                            'it' => 'Prenota online in 60 secondi. Voucher istantaneo inviato direttamente sul tuo WhatsApp.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
                    ],
                ],
            ],
            [
                'slug' => 'tours',
                'name' => 'Tours Catalog Hub',
                'title' => json_encode([
                    'en' => 'Explore All Dubai Desert Safari & Adventure Tours',
                    'ar' => 'استكشف جميع جولات سفاري صحراء دبي ومغامراتها',
                    'ru' => 'Все туры сафари и приключений в пустыне Дубая',
                    'es' => 'Explore Todos los Tours de Safari y Aventuras en Dubái',
                    'it' => 'Esplora Tutti i Tour Safari e le Avventure a Dubai',
                ], JSON_UNESCAPED_UNICODE),
                'subtitle' => json_encode([
                    'en' => 'Find your ideal desert experience: evening safaris, quad biking, dune buggies, sunrise tours, and luxury dhow cruises.',
                    'ar' => 'اعثر على تجربتك الصحراوية المثالية: رحلات السفاري المسائية، دراجات الدفع الرباعي، عربات الكثبان، رحلات الشروق، ورحلات العشاء البحرية الفاخرة.',
                    'ru' => 'Найдите свое идеальное приключение: вечерние сафари, квадроциклы, багги, рассветы и круизы на дау.',
                    'es' => 'Encuentre su experiencia ideal: safaris nocturnos, quads, buggies, tours al amanecer y cruceros en dhow.',
                    'it' => 'Trova la tua esperienza ideale: safari serali, quad, dune buggy, tour all\'alba e crociere di lusso in dhow.',
                ], JSON_UNESCAPED_UNICODE),
                'sections' => [
                    [
                        'section_key' => 'why_book',
                        'name' => 'Why Book Directly Banner',
                        'title' => json_encode([
                            'en' => 'Why Book Directly With Dunes Discovery',
                            'ar' => 'لماذا تحجز مباشرة مع ديونز ديسكفري',
                            'ru' => 'Почему выгодно бронировать напрямую у Dunes Discovery',
                            'es' => 'Por Qué Reservar Directamente con Dunes Discovery',
                            'it' => 'Perché Prenotare Direttamente con Dunes Discovery',
                        ], JSON_UNESCAPED_UNICODE),
                        'subtitle' => json_encode([
                            'en' => 'Direct operator pricing with zero middleman markups, guaranteed brand-new 4x4 vehicles, and free hotel pickup.',
                            'ar' => 'أسعار المشغل المباشر بدون أي رسوم وسيط، سيارات دفع رباعي جديدة ومضمونة، وتوصيل مجاني من وإلى الفندق.',
                            'ru' => 'Цены напрямую без посредников, новые автомобили 4х4 и бесплатный трансфер от отеля.',
                            'es' => 'Precios directos del operador sin intermediarios, vehículos 4x4 garantizados y recogida gratuita en el hotel.',
                            'it' => 'Prezzi diretti dell\'operatore senza intermediari, veicoli 4x4 nuovi garantiti e ritiro gratuito in hotel.',
                        ], JSON_UNESCAPED_UNICODE),
                        'body' => null,
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
                // Preserve user-customized page name/title if already set, but ensure status published
                DB::table('pages')->where('id', $pageId)->update([
                    'status' => 'published',
                    'updated_at' => now(),
                ]);
            } else {
                $pData['status'] = 'published';
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
                    // Update name and ensure active
                    DB::table('page_sections')->where('id', $existingSec->id)->update([
                        'name' => $sData['name'],
                        'order' => $sData['order'],
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('page_sections')->insert($sData);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe to keep records on rollback
    }
};
