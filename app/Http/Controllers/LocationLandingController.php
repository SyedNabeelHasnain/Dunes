<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use App\Services\LocationLandingService;

class LocationLandingController extends Controller
{
    /**
     * Display a programmatic geo-location pickup landing page.
     */
    public function show(...$params)
    {
        $location = (string) end($params);
        $locationData = LocationLandingService::find($location);

        if (! $locationData) {
            abort(404);
        }

        try {
            $safariTours = Tour::where('status', 'active')
                ->where(function ($q) {
                    $q->where('slug', 'like', '%safari%')
                        ->orWhere('slug', 'like', '%buggy%')
                        ->orWhere('name', 'like', '%Safari%')
                        ->orWhere('name', 'like', '%Desert%');
                })
                ->with(['tiers', 'category'])
                ->orderBy('is_bestseller', 'desc')
                ->orderBy('priority', 'asc')
                ->get();

            if ($safariTours->isEmpty()) {
                $safariTours = Tour::where('status', 'active')
                    ->with(['tiers', 'category'])
                    ->orderBy('priority', 'asc')
                    ->take(6)
                    ->get();
            }
        } catch (\Throwable $e) {
            $safariTours = collect();
        }

        try {
            $reviews = Review::where('status', 'approved')
                ->orderBy('rating', 'desc')
                ->orderBy('published_date', 'desc')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            $reviews = collect();
        }

        $allLocations = LocationLandingService::getLocations();

        $currentLocale = app()->getLocale();
        $currentYear = date('Y');
        $locName = $locationData['name'];
        $driveTime = $locationData['transit_time'] ?? $locationData['drive_time'] ?? '45 Minutes';

        $localizedLocMeta = [
            'ar' => [
                'title' => "سفاري صحراء دبي مع توصيل من {$locName} ({$currentYear}) | على بُعد {$driveTime} | Dunes Discovery",
                'desc' => "احجز رحلة سفاري صحراء دبي مع خدمة استقبال وتوصيل مجانية بسيارات الدفع الرباعي 4x4 من كافة فنادق وأبراج {$locName} ({$driveTime} إلى كثبان لهباب الحمراء).",
            ],
            'ru' => [
                'title' => "Сафари в пустыне Дубая с трансфером из {$locName} ({$currentYear}) | {$driveTime} в пути | Dunes Discovery",
                'desc' => "Забронируйте пустынное сафари в Дубае с бесплатным трансфером на 4x4 прямо из отелей и резиденций района {$locName} ({$driveTime} до красных дюн Лахбаб).",
            ],
            'es' => [
                'title' => "Safari por el Desierto de Dubái desde {$locName} ({$currentYear}) | A {$driveTime} | Dunes Discovery",
                'desc' => "Reserva tu safari por el desierto de Dubái con recogida puerta a puerta en 4x4 desde cualquier hotel o residencia en {$locName} ({$driveTime} hasta las dunas rojas de Lahbab).",
            ],
            'it' => [
                'title' => "Safari nel Deserto di Dubai con Partenza da {$locName} ({$currentYear}) | A {$driveTime} | Dunes Discovery",
                'desc' => "Prenota il tuo safari nel deserto di Dubai con trasferimento 4x4 porta a porta da tutti gli hotel e residence di {$locName} ({$driveTime} dalle dune rosse di Lahbab).",
            ],
        ];

        $pageTitle = $localizedLocMeta[$currentLocale]['title'] ?? $locationData['meta_title'];
        $pageDesc = $localizedLocMeta[$currentLocale]['desc'] ?? $locationData['meta_desc'];
        $canonical = ($currentLocale && $currentLocale !== 'en') ? url('/'.$currentLocale.'/'.$locationData['slug']) : url('/'.$locationData['slug']);
        $ogImage = asset('images/desert-safari-poster.avif');
        $pageRobots = ! empty(request()->query())
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        return view('pages.location-tour', compact(
            'locationData',
            'safariTours',
            'reviews',
            'allLocations',
            'pageTitle',
            'pageDesc',
            'canonical',
            'ogImage',
            'pageRobots'
        ));
    }
}
