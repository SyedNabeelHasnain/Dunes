<?php

namespace App\Services\Audit\Checkers;

use App\Http\Controllers\RumVitalsController;
use App\Models\MediaAsset;
use App\Models\Setting;
use App\Models\WebVitalsMetric;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PerformanceChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Unoptimized Heavy Images (> 500KB not in modern WebP/AVIF format)
        $heavyImages = MediaAsset::where('media_type', 'image')
            ->where('file_size_bytes', '>', 500 * 1024)
            ->whereNotIn('extension', ['webp', 'avif'])
            ->take(10)
            ->get();

        foreach ($heavyImages as $img) {
            $kb = round($img->file_size_bytes / 1024, 1);
            $issues[] = [
                'category' => 'performance',
                'check_key' => 'perf_heavy_uncompressed_image',
                'title' => "Heavy Legacy Format Image ({$kb} KB) - '{$img->filename}'",
                'description' => "Image exceeds 500 KB and is encoded in legacy format ({$img->extension}). Converting to WebP or AVIF can reduce file size by 65-80% without perceptible quality loss, improving LCP.",
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => $img->public_url,
                'evidence' => "File size: {$kb} KB, Format: {$img->extension}",
                'recommended_action' => "Convert image #{$img->id} to WebP or AVIF using the CMS Media Manager Lossless Conversion Engine.",
            ];
        }

        // 2. Missing Dimensions on Media Assets (CLS Risk)
        $missingDimensions = MediaAsset::where('media_type', 'image')
            ->where(function ($q) {
                $q->whereNull('width')
                    ->orWhereNull('height')
                    ->orWhere('width', 0)
                    ->orWhere('height', 0);
            })
            ->take(5)
            ->get();

        foreach ($missingDimensions as $img) {
            $issues[] = [
                'category' => 'performance',
                'check_key' => 'perf_missing_image_dimensions',
                'title' => "Image Missing Intrinsic Dimensions - '{$img->filename}'",
                'description' => 'Image metadata lacks explicit width and height values. Images rendered without aspect-ratio or dimensions can cause Cumulative Layout Shift (CLS > 0.1).',
                'severity' => 'low',
                'verification_type' => 'automated',
                'affected_url' => $img->public_url,
                'evidence' => "Width: " . ($img->width ?? 'NULL') . ", Height: " . ($img->height ?? 'NULL'),
                'recommended_action' => 'Re-inspect asset in Media Library to populate intrinsic width and height dimensions.',
            ];
        }

        // 3. Hero Poster & LCP Asset Verification
        $heroPoster = Setting::where('setting_key', 'site_hero_poster')->value('setting_value');
        if (empty($heroPoster)) {
            $issues[] = [
                'category' => 'performance',
                'check_key' => 'perf_missing_hero_poster',
                'title' => 'Missing Hero Poster Fallback for Video Background',
                'description' => 'A fallback poster image for the homepage hero video is not explicitly set in CMS settings. Mobile devices that block video autoplay will show a blank hero block, hurting LCP.',
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'site_hero_poster setting is empty.',
                'recommended_action' => 'Select an optimized AVIF/WebP hero poster in CMS Settings > Identity & Media.',
            ];
        }

        // 4. Real-User Core Web Vitals (p75 RUM vs Google Thresholds)
        try {
            if (Schema::hasTable('web_vitals_metrics')) {
                foreach (['LCP', 'INP', 'CLS'] as $metric) {
                    $baseQuery = WebVitalsMetric::where('metric_name', $metric)
                        ->where('recorded_at', '>=', now()->subDays(14));
                    $sampleCount = (clone $baseQuery)->count();
                    if ($sampleCount >= 5) {
                        $p75Index = (int) floor(0.75 * ($sampleCount - 1));
                        $p75 = (float) (clone $baseQuery)
                            ->orderBy('metric_value')
                            ->offset($p75Index)
                            ->limit(1)
                            ->value('metric_value');
                        $budget = RumVitalsController::BUDGETS[$metric]['good'];
                        if ($p75 > $budget) {
                            $issues[] = [
                                'category' => 'performance',
                                'check_key' => 'perf_rum_budget_exceeded',
                                'title' => "Core Web Vitals Budget Exceeded: p75 {$metric} = ".round($p75, 2),
                                'description' => "Real user 75th percentile {$metric} exceeds target budget ({$budget}).",
                                'severity' => $p75 > RumVitalsController::BUDGETS[$metric]['poor'] ? 'high' : 'medium',
                                'verification_type' => 'automated',
                                'affected_url' => route('admin.health.index'),
                                'evidence' => "Observed p75 over {$sampleCount} samples: {$p75} vs budget <= {$budget}",
                                'recommended_action' => 'Inspect System Health & CWV Monitor to optimize LCP/CLS/INP on slow pages.',
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('PerformanceChecker RUM query failed: '.$e->getMessage());
        }

        // 5. DOM Check on Probed Public Routes for Unsized Images (CLS) & Render-Blocking Scripts
        foreach (($context['probed_pages'] ?? []) as $path => $probe) {
            $html = (string) ($probe['html'] ?? '');
            if ($html === '') {
                continue;
            }
            preg_match_all('/<img\b[^>]*>/i', $html, $imgMatches);
            $unsizedCount = 0;
            foreach ($imgMatches[0] ?? [] as $imgTag) {
                if (! preg_match('/\bwidth\s*=/i', $imgTag) || ! preg_match('/\bheight\s*=/i', $imgTag)) {
                    $unsizedCount++;
                }
            }
            if ($unsizedCount >= 3) {
                $issues[] = [
                    'category' => 'performance',
                    'check_key' => 'perf_dom_unsized_images',
                    'title' => "{$unsizedCount} Images Missing Explicit Dimensions on {$path}",
                    'description' => 'Rendered HTML contains multiple <img> tags lacking explicit width and height attributes, elevating CLS risk.',
                    'severity' => 'low',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => "Unsized <img> count on {$path}: {$unsizedCount}",
                    'recommended_action' => 'Add explicit width and height HTML attributes matching natural aspect ratio.',
                ];
            }
        }

        return $issues;
    }
}
