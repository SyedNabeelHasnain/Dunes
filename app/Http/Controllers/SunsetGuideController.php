<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SunsetGuideController extends Controller
{
    /**
     * Display the Dubai Desert Sunset & Golden Hour Photography Timing Guide.
     */
    public function index(Request $request, SettingsService $settings)
    {
        $currentLocale = app()->getLocale();
        $currentYear = date('Y');

        // Canonical URL
        $canonical = localized_route('sunset.guide');

        $localizedSunsetMeta = [
            'ar' => [
                'title' => "مواعيد غروب الشمس في صحراء دبي {$currentYear} | دليل الساعة الذهبية في كثبان لهباب | Dunes Discovery",
                'desc' => 'دليل شامل لمواعيد غروب الشمس في صحراء دبي، أوقات الساعة الذهبية للتصوير، وجدول انطلاق رحلات السفاري المسائية في كثبان لهباب الحمراء على مدار العام.',
            ],
            'ru' => [
                'title' => "Время заката в пустыне Дубая {$currentYear} | Гид по золотому часу в дюнах Лахбаб | Dunes Discovery",
                'desc' => 'Полное расписание закатов в пустыне Дубая по месяцам, время «золотого часа» для фотосессий и график выезда на вечернее сафари в красные дюны Лахбаб.',
            ],
            'es' => [
                'title' => "Horarios del Atardecer en el Desierto de Dubái {$currentYear} | Guía de Hora Dorada en Lahbab | Dunes Discovery",
                'desc' => 'Guía completa de horarios de puesta de sol en el desierto de Dubái, ventanas de hora dorada para fotografía y salidas de safari vespertino en las dunas rojas de Lahbab.',
            ],
            'it' => [
                'title' => "Orari del Tramonto nel Deserto di Dubai {$currentYear} | Guida all'Ora d'Oro a Lahbab | Dunes Discovery",
                'desc' => 'Guida completa agli orari del tramonto nel deserto di Dubai, finestre fotografiche dell\'ora d\'oro e programmi del safari serale sulle dune rosse di Lahbab.',
            ],
        ];

        // Dynamic High-CTR SEO Metadata
        $pageTitle = $localizedSunsetMeta[$currentLocale]['title'] ?? ($settings->get('seo_sunset_title') ?: "Dubai Desert Sunset Times {$currentYear} | Lahbab Red Dunes Golden Hour Guide | Dunes Discovery");
        $pageDesc = $localizedSunsetMeta[$currentLocale]['desc'] ?? ($settings->get('seo_sunset_description') ?: 'Complete guide to Dubai desert sunset times, golden hour photography windows, and evening safari schedules in Lahbab Red Dunes. Live astronomical countdown and monthly reference charts.');
        $pageKeys = $settings->get('seo_sunset_keywords') ?: 'dubai desert sunset time, what time does the sun set in dubai desert, lahbab golden hour, evening safari sunset dubai, best time for desert photography dubai';
        $ogImage = asset('images/evening-desert-safari-dubai-dune-discovery-tourism.avif');

        // 12-Month Astronomical & Meteorological Reference Data for Lahbab Red Dunes (25.04° N, 55.67° E)
        $monthlySchedule = [
            1 => ['month' => 'January', 'short' => 'Jan', 'sunset' => '5:48 PM', 'golden_hour' => '5:15 PM – 5:48 PM', 'twilight' => '6:12 PM', 'dusk_temp' => '21°C', 'rating' => 'Optimal Stargazing', 'photo_tip' => 'Crisp cool air with zero haze; ideal for high dune silhouette shots.'],
            2 => ['month' => 'February', 'short' => 'Feb', 'sunset' => '6:10 PM', 'golden_hour' => '5:38 PM – 6:10 PM', 'twilight' => '6:32 PM', 'dusk_temp' => '23°C', 'rating' => 'Peak Season Prime', 'photo_tip' => 'Pleasant desert breezes; great for active dune bashing and sandboarding.'],
            3 => ['month' => 'March', 'short' => 'Mar', 'sunset' => '6:28 PM', 'golden_hour' => '5:56 PM – 6:28 PM', 'twilight' => '6:50 PM', 'dusk_temp' => '25°C', 'rating' => 'Vibrant Amber Skies', 'photo_tip' => 'Warm glow creates rich amber contrast across crimson Lahbab ridges.'],
            4 => ['month' => 'April', 'short' => 'Apr', 'sunset' => '6:45 PM', 'golden_hour' => '6:15 PM – 6:45 PM', 'twilight' => '7:07 PM', 'dusk_temp' => '29°C', 'rating' => 'Long Daylight Windows', 'photo_tip' => 'Extended late afternoons give extra quad bike driving time before dusk.'],
            5 => ['month' => 'May', 'short' => 'May', 'sunset' => '7:02 PM', 'golden_hour' => '6:32 PM – 7:02 PM', 'twilight' => '7:26 PM', 'dusk_temp' => '33°C', 'rating' => 'Warm Crimson Glow', 'photo_tip' => 'Deep orange horizon; camp AC majlis lounges open for cool dining.'],
            6 => ['month' => 'June', 'short' => 'Jun', 'sunset' => '7:15 PM', 'golden_hour' => '6:45 PM – 7:15 PM', 'twilight' => '7:41 PM', 'dusk_temp' => '36°C', 'rating' => 'Longest Golden Hour', 'photo_tip' => 'Solstice sunset; late Land Cruiser departures beat midday heat.'],
            7 => ['month' => 'July', 'short' => 'Jul', 'sunset' => '7:16 PM', 'golden_hour' => '6:46 PM – 7:16 PM', 'twilight' => '7:42 PM', 'dusk_temp' => '37°C', 'rating' => 'Late Evening Magic', 'photo_tip' => 'Dramatic high-altitude thermal cloud formations create vivid pink dusks.'],
            8 => ['month' => 'August', 'short' => 'Aug', 'sunset' => '7:00 PM', 'golden_hour' => '6:30 PM – 7:00 PM', 'twilight' => '7:24 PM', 'dusk_temp' => '36°C', 'rating' => 'Warm Arabian Nights', 'photo_tip' => 'Camp entertainment and starry skies take center stage under desert breezes.'],
            9 => ['month' => 'September', 'short' => 'Sep', 'sunset' => '6:28 PM', 'golden_hour' => '5:58 PM – 6:28 PM', 'twilight' => '6:51 PM', 'dusk_temp' => '32°C', 'rating' => 'Autumn Shift Prime', 'photo_tip' => 'Milder evening temperatures return with rapid sunset color changes.'],
            10 => ['month' => 'October', 'short' => 'Oct', 'sunset' => '5:55 PM', 'golden_hour' => '5:25 PM – 5:55 PM', 'twilight' => '6:18 PM', 'dusk_temp' => '28°C', 'rating' => 'High Season Opening', 'photo_tip' => 'Superb clarity; prime time for falconry flight portraits against sunset.'],
            11 => ['month' => 'November', 'short' => 'Nov', 'sunset' => '5:35 PM', 'golden_hour' => '5:05 PM – 5:35 PM', 'twilight' => '5:59 PM', 'dusk_temp' => '24°C', 'rating' => 'Photographer Favorite', 'photo_tip' => 'Softer sun angle creates deep shadows that carve out dune crests perfectly.'],
            12 => ['month' => 'December', 'short' => 'Dec', 'sunset' => '5:34 PM', 'golden_hour' => '5:04 PM – 5:34 PM', 'twilight' => '5:59 PM', 'dusk_temp' => '20°C', 'rating' => 'Cool Winter Solstice', 'photo_tip' => 'Early dusk transitions quickly into cool campfire and stargazing nights.'],
        ];

        $currentMonthNumber = (int) date('n');
        $currentMonthData = $monthlySchedule[$currentMonthNumber] ?? $monthlySchedule[1];

        // Fetch top relevant evening & sunset tours
        try {
            $sunsetTours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('slug', 'like', '%evening%')
                        ->orWhere('slug', 'like', '%overnight%')
                        ->orWhere('slug', 'like', '%sunset%')
                        ->orWhere('is_bestseller', true);
                })
                ->with(['tiers', 'category'])
                ->orderBy('priority', 'asc')
                ->limit(3)
                ->get();
        } catch (\Throwable $e) {
            $sunsetTours = collect();
        }

        return view('pages.sunset-guide', compact(
            'pageTitle',
            'pageDesc',
            'pageKeys',
            'canonical',
            'ogImage',
            'monthlySchedule',
            'currentMonthData',
            'currentMonthNumber',
            'sunsetTours',
            'currentYear',
            'currentLocale'
        ));
    }
}
