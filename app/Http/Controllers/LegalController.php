<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;

class LegalController extends Controller
{
    /**
     * Helper to load a legal page with its eager-loaded sections and items.
     */
    private function getPageWithRelations(string $slug): LegalPage
    {
        return LegalPage::where('slug', $slug)
            ->with(['sections' => function ($query) {
                $query->orderBy('priority', 'asc')->with(['items' => function ($q) {
                    $q->orderBy('priority', 'asc');
                }]);
            }])->firstOrFail();
    }

    private function buildLegalResponse(string $slug, string $routeName, string $enTitle, string $enDesc, string $pageKeys)
    {
        $page = $this->getPageWithRelations($slug);
        $currentYear = date('Y');
        $currentLocale = app()->getLocale();
        $canonical = localized_route($routeName);
        $ogImage = asset('images/desert-safari-poster.avif');

        if ($currentLocale && $currentLocale !== 'en') {
            $localizedLegalTitles = [
                'terms-condition' => [
                    'ar' => 'الشروط والأحكام الرسمية',
                    'ru' => 'Условия и положения бронирования',
                    'es' => 'Términos y Condiciones Oficiales',
                    'it' => 'Termini e Condizioni Ufficiali',
                ],
                'privacy-policy' => [
                    'ar' => 'سياسة الخصوصية وحماية البيانات',
                    'ru' => 'Политика конфиденциальности и защиты данных',
                    'es' => 'Política de Privacidad y Protección de Datos',
                    'it' => 'Informativa sulla Privacy e Protezione dei Dati',
                ],
                'cookie-policy' => [
                    'ar' => 'سياسة ملفات تعريف الارتباط والتتبع',
                    'ru' => 'Политика использования файлов Cookie',
                    'es' => 'Política de Cookies y Tecnologías de Seguimiento',
                    'it' => 'Politica sui Cookie e Tecnologie di Tracciamento',
                ],
                'cancellation-refund-policy' => [
                    'ar' => 'سياسة الإلغاء والاسترداد الكامل (100%)',
                    'ru' => 'Политика отмены бронирования и 100% возврата средств',
                    'es' => 'Política de Cancelación, Reembolso del 100% y Reprogramación',
                    'it' => 'Politica di Cancellazione, Rimborso del 100% e Riprogrammazione',
                ],
                'payment-security-policy' => [
                    'ar' => 'سياسة أمان المدفوعات ومكافحة الاحتيال',
                    'ru' => 'Политика безопасности платежей и защиты от мошенничества',
                    'es' => 'Política de Seguridad de Pagos y Prevención de Fraude',
                    'it' => 'Politica sulla Sicurezza dei Pagamenti e Antifrode',
                ],
                'safety-liability-waiver' => [
                    'ar' => 'معايير السلامة الصحراوية وإخلاء المسؤولية',
                    'ru' => 'Правила безопасности в пустыне и освобождение от ответственности',
                    'es' => 'Normas de Seguridad en el Desierto y Exención de Responsabilidad',
                    'it' => 'Standard di Sicurezza nel Deserto e Liberatoria di Responsabilità',
                ],
                'ai-editorial-policy' => [
                    'ar' => 'سياسة التحرير والذكاء الاصطناعي والملكية الفكرية',
                    'ru' => 'Редакционная политика, ИИ и интеллектуальная собственность',
                    'es' => 'Política Editorial, IA y Propiedad Intelectual',
                    'it' => 'Politica Editoriale, IA e Proprietà Intellettuale',
                ],
                'responsible-tourism-policy' => [
                    'ar' => 'سياسة السياحة المسؤولة والاستدامة البيئية',
                    'ru' => 'Политика ответственного туризма и экологической устойчивости',
                    'es' => 'Política de Turismo Responsable y Sostenibilidad Ambiental',
                    'it' => 'Politica sul Turismo Responsabile e Sostenibilità Ambientale',
                ],
            ];

            $locTitle = $localizedLegalTitles[$slug][$currentLocale]
                ?? ($page->hasTranslation('title', $currentLocale) ? $page->getTranslation('title', $currentLocale) : $page->title);
            $pageTitle = "{$locTitle} ({$currentYear}) | Dunes Discovery Tourism Dubai";

            if ($page->hasTranslation('description', $currentLocale)) {
                $pageDesc = $page->getTranslation('description', $currentLocale);
            } else {
                $suffixMap = [
                    'ar' => 'وثيقة قانونية رسمية معتمدة من شركة Dunes Discovery Tourism LLC في دبي، الإمارات العربية المتحدة (ترخيص DTCM #1430583).',
                    'ru' => 'Официальный регламент туроператора Dunes Discovery Tourism LLC в Дубае, ОАЭ (лицензия DTCM #1430583).',
                    'es' => 'Documento normativo oficial de Dunes Discovery Tourism LLC en Dubái, Emiratos Árabes Unidos (licencia DTCM #1430583).',
                    'it' => 'Documento normativo ufficiale di Dunes Discovery Tourism LLC a Dubai, Emirati Arabi Uniti (licenza DTCM #1430583).',
                ];
                $pageDesc = "{$locTitle} — ".($suffixMap[$currentLocale] ?? $enDesc);
            }
        } else {
            $pageTitle = $enTitle;
            $pageDesc = $enDesc;
        }

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }

    /**
     * Display the Terms and Conditions page.
     */
    public function terms()
    {
        $currentYear = date('Y');

        return $this->buildLegalResponse(
            'terms-condition',
            'terms',
            "Terms & Conditions ({$currentYear}) | Dunes Discovery Tourism LLC Dubai",
            'Read the official Terms and Conditions of Dunes Discovery Tourism LLC. Covers tour bookings, cancellations, 100% refund policy, and passenger safety guidelines.',
            'dunes discovery terms, desert safari terms and conditions dubai, booking cancellation policy, uae tour operator contract'
        );
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        $currentYear = date('Y');

        return $this->buildLegalResponse(
            'privacy-policy',
            'privacy',
            "Privacy Policy & Data Protection ({$currentYear}) | Dunes Discovery Tourism LLC Dubai",
            'Read the Privacy Policy of Dunes Discovery Tourism LLC. Learn how we handle customer data, payment security, and UAE PDPL and GDPR compliance.',
            'dunes discovery privacy policy, data protection dubai, uae pdpl compliance, secure booking dubai'
        );
    }

    /**
     * Display the Cookie & Tracking Technologies Policy page.
     */
    public function cookies()
    {
        $currentYear = date('Y');

        return $this->buildLegalResponse(
            'cookie-policy',
            'cookies',
            "Cookie & Tracking Technologies Policy ({$currentYear}) | Dunes Discovery Tourism LLC Dubai",
            'Learn about cookies, local storage, Google Consent Mode v2, and analytics tracking deployed on Dunes Discovery Tourism in compliance with international ePrivacy standards.',
            'cookie policy dubai, google consent mode v2, website cookies desert safari, analytics tracking policy'
        );
    }

    /**
     * Display the Cancellation, Refund & Rescheduling Policy page.
     */
    public function cancellation()
    {
        return $this->buildLegalResponse(
            'cancellation-refund-policy',
            'cancellation',
            'Cancellation, Refund & Rescheduling Policy (100% Refund Guarantee) | Dunes Discovery Tourism',
            'Explore our 24-hour free cancellation policy, 100% refund guarantee, and flexible rescheduling for all Dubai desert safaris and city excursions.',
            'desert safari refund policy, cancel desert safari dubai, 100 refund guarantee, free cancellation tour dubai, rescheduling dubai tour'
        );
    }

    /**
     * Display the Payment, Security & Anti-Fraud Policy page.
     */
    public function paymentSecurity()
    {
        return $this->buildLegalResponse(
            'payment-security-policy',
            'payment.security',
            'Payment Security & Anti-Fraud Policy | Dunes Discovery Tourism LLC Dubai',
            'Learn about our PCI-DSS Level 1 payment security, 256-bit SSL encryption, 3D Secure 2.0 verification, and fraud prevention through Ziina Payment Gateway.',
            'payment security dubai, ziina payment gateway, secure booking desert safari, 3d secure apple pay, anti fraud travel policy'
        );
    }

    /**
     * Display the Desert Safari & Off-Road Adventure Liability Waiver page.
     */
    public function safetyWaiver()
    {
        return $this->buildLegalResponse(
            'safety-liability-waiver',
            'safety.waiver',
            'Desert Safari & Off-Road Adventure Liability Waiver | Dunes Discovery Tourism',
            'Review our comprehensive off-road safety standards, RTA-certified safari drivers, vehicle roll-cages, and health guidelines for desert dune adventures.',
            'desert safari safety waiver, dubai dune bashing rules, rta safari regulations, off road liability waiver, quad biking safety rules'
        );
    }

    /**
     * Display the AI Search Engine, Editorial & Intellectual Property Policy page.
     */
    public function aiEditorial()
    {
        return $this->buildLegalResponse(
            'ai-editorial-policy',
            'ai.editorial',
            'AI Search Engine, Editorial & Intellectual Property Policy | Dunes Discovery Tourism',
            'Discover our Generative Engine Optimization (GEO) standards, machine-readable llms.txt protocols, expert guide verification, and copyright rules.',
            'ai editorial policy, llms.txt tourism dubai, generative engine optimization, content transparency, copyright dubai travel'
        );
    }

    /**
     * Display the Responsible Tourism & Environmental Sustainability Policy page.
     */
    public function responsibleTourism()
    {
        return $this->buildLegalResponse(
            'responsible-tourism-policy',
            'responsible.tourism',
            'Responsible Tourism & Environmental Sustainability Policy | Dunes Discovery Tourism',
            'Our charter for Arabian desert ecosystem protection, Leave No Trace wilderness protocols, ethical camel welfare, and Emirati Bedouin heritage preservation.',
            'responsible tourism dubai, desert conservation ddcr, sustainable safari dubai, eco friendly desert tour, animal welfare charter'
        );
    }

    /**
     * Generic fallback display for any legal page by slug.
     */
    public function show(string $slug)
    {
        $page = $this->getPageWithRelations($slug);
        $currentYear = date('Y');
        $pageTitle = "{$page->title} ({$currentYear}) | Dunes Discovery Tourism LLC Dubai";
        $pageDesc = $page->description ?: 'Read official policy guidelines from Dunes Discovery Tourism L.L.C in Dubai, United Arab Emirates.';
        $pageKeys = "dunes discovery policy, {$page->slug}, dubai tour policies";
        $currentLocale = app()->getLocale();
        $canonical = ($currentLocale && $currentLocale !== 'en') ? url('/'.$currentLocale.'/'.$page->slug) : url('/'.$page->slug);
        $ogImage = asset('images/desert-safari-poster.avif');

        return view('legal.show', compact('page', 'pageTitle', 'pageDesc', 'pageKeys', 'canonical', 'ogImage'));
    }
}
