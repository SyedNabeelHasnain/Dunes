<?php

namespace App\Services\Audit\Checkers;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\File;

class AccessibilityChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Skip-to-Main Landmark & Target Element (WCAG 2.2 SC 2.4.1 Bypass Blocks)
        $appBladePath = resource_path('views/layouts/app.blade.php');
        if (File::exists($appBladePath)) {
            $content = File::get($appBladePath);
            if (!str_contains($content, 'href="#main"') && !str_contains($content, 'href="#main-content"')) {
                $issues[] = [
                    'category' => 'accessibility',
                    'check_key' => 'a11y_missing_skip_link',
                    'title' => 'Missing WCAG Skip-to-Content Navigation Link',
                    'description' => 'Keyboard and screen-reader users must navigate through dozens of header links before reaching primary content (Violation of WCAG 2.2 SC 2.4.1 Level A).',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url('/'),
                    'evidence' => 'No skip-to-content anchor found in layouts/app.blade.php.',
                    'recommended_action' => 'Add <a href="#main" class="sr-only focus:not-sr-only">Skip to main content</a> as the first child of the body tag.',
                ];
            }
            if (!str_contains($content, 'id="main"') && !str_contains($content, 'id="main-content"')) {
                $issues[] = [
                    'category' => 'accessibility',
                    'check_key' => 'a11y_missing_main_landmark_id',
                    'title' => 'Missing #main Landmark Target for Skip Link',
                    'description' => 'The skip navigation link requires a target element with id="main" and tabindex="-1" to receive focus upon activation.',
                    'severity' => 'medium',
                    'verification_type' => 'automated',
                    'affected_url' => url('/'),
                    'evidence' => 'No element with id="main" found in master layout.',
                    'recommended_action' => 'Add id="main" tabindex="-1" to the primary <main> element.',
                ];
            }
        }

        // 2. Missing Alt Text on Active Media Assets (WCAG 2.2 SC 1.1.1 Non-text Content)
        $missingAlt = MediaAsset::where('media_type', 'image')
            ->where(function ($q) {
                $q->whereNull('alt_text')
                    ->orWhere('alt_text', '');
            })
            ->where('usage_count', '>', 0)
            ->take(8)
            ->get();

        foreach ($missingAlt as $img) {
            $issues[] = [
                'category' => 'accessibility',
                'check_key' => 'a11y_missing_image_alt_text',
                'title' => "Missing Descriptive Alt Text - '{$img->filename}'",
                'description' => "Image is actively embedded in public pages but lacks an alt attribute description for visually impaired visitors using assistive screen readers.",
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => $img->public_url,
                'evidence' => "Media ID #{$img->id}, filename: {$img->filename}, usage_count: {$img->usage_count}",
                'recommended_action' => "Open Media Manager, select image #{$img->id}, and enter an informative descriptive alt text.",
            ];
        }

        // 3. Contrast & Focus Management (Informational Guideline)
        $issues[] = [
            'category' => 'accessibility',
            'check_key' => 'a11y_wcag_manual_review_reminder',
            'title' => 'Manual Keyboard & Screen-Reader Verification Reminder',
            'description' => 'Automated tools can only verify ~35% of WCAG criteria. Full WCAG 2.2 Level AA compliance requires manual keyboard tab navigation, focus outline inspection, and screen-reader testing (NVDA / VoiceOver).',
            'severity' => 'info',
            'verification_type' => 'manual_review',
            'affected_url' => url('/'),
            'evidence' => 'WCAG 2.2 Level AA compliance protocol guidelines.',
            'recommended_action' => 'Perform quarterly manual keyboard navigation and screen-reader user testing across booking forms and modals.',
        ];

        return $issues;
    }
}
