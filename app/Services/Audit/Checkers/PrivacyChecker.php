<?php

namespace App\Services\Audit\Checkers;

use App\Models\LegalPage;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class PrivacyChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Legal Privacy & Cookie Policies Verification (GDPR / UAE Data Protection Law)
        $privacyPolicy = LegalPage::where('slug', 'privacy-policy')->first();
        if (!$privacyPolicy) {
            $issues[] = [
                'category' => 'privacy',
                'check_key' => 'privacy_missing_privacy_policy',
                'title' => 'Missing Official Privacy Policy Page',
                'description' => 'A dedicated Privacy Policy complying with the UAE Federal Decree-Law No. 45 of 2021 regarding Personal Data Protection and GDPR is required.',
                'severity' => 'critical',
                'verification_type' => 'automated',
                'affected_url' => url('/privacy-policy'),
                'evidence' => "LegalPage with slug 'privacy-policy' not found.",
                'recommended_action' => 'Publish the Privacy Policy via CMS Legal Pages with full disclosures regarding customer booking data retention.',
            ];
        }

        $cookiePolicy = LegalPage::where('slug', 'cookie-policy')->first();
        if (!$cookiePolicy) {
            $issues[] = [
                'category' => 'privacy',
                'check_key' => 'privacy_missing_cookie_policy',
                'title' => 'Missing Cookie Policy Page',
                'description' => 'A dedicated Cookie Policy detailing essential vs analytics vs marketing cookies is missing.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/cookie-policy'),
                'evidence' => "LegalPage with slug 'cookie-policy' not found.",
                'recommended_action' => 'Publish the Cookie Policy via CMS Legal Pages.',
            ];
        }

        // 2. Cookie Consent UI Banner & Strict Pre-Consent Gating Presence
        $cookieBannerExists = File::exists(resource_path('views/partials/cookie-banner.blade.php'));
        $appLayout = File::exists(resource_path('views/layouts/app.blade.php')) ? File::get(resource_path('views/layouts/app.blade.php')) : '';

        if (!$cookieBannerExists || (!str_contains($appLayout, 'cookie-banner') && !str_contains($appLayout, 'DunesConsent'))) {
            $issues[] = [
                'category' => 'privacy',
                'check_key' => 'privacy_missing_consent_banner',
                'title' => 'Missing Interactive Cookie Consent Banner',
                'description' => 'Visitors from the EU and international jurisdictions require explicit prior consent before tracking cookies and marketing scripts execute.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'No cookie banner partial or Consent Mode v2 controller found in layouts/app.blade.php.',
                'recommended_action' => 'Enable the interactive cookie consent banner in Privacy & Consent v2 settings.',
            ];
        }

        $strictGating = Setting::getVal('privacy_strict_consent_gating', '1');
        if ($strictGating === '0') {
            $issues[] = [
                'category' => 'privacy',
                'check_key' => 'privacy_strict_gating_disabled',
                'title' => 'Strict Pre-Consent Script Gating Is Disabled',
                'description' => 'Third-party analytics or pixels may fire prior to explicit visitor consent when strict gating is disabled.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => route('admin.settings.privacy.index'),
                'evidence' => 'privacy_strict_consent_gating = 0',
                'recommended_action' => 'Enable strict script & telemetry gating in Privacy & Consent v2 settings.',
            ];
        }

        foreach (($context['probed_pages'] ?? []) as $path => $probe) {
            $html = (string) ($probe['html'] ?? '');
            if ($html !== '' && str_contains($html, 'facebook.com/tr?id=') && str_contains($html, 'noscript=1')) {
                $issues[] = [
                    'category' => 'privacy',
                    'check_key' => 'privacy_unconsented_pixel_beacon',
                    'title' => "Unconsented Meta Pixel Beacon Emitted on {$path}",
                    'description' => 'A tracking pixel image was rendered without user marketing consent.',
                    'severity' => 'high',
                    'verification_type' => 'automated',
                    'affected_url' => url($path),
                    'evidence' => 'Matched unconsented facebook.com/tr noscript beacon in HTML response.',
                    'recommended_action' => 'Ensure Meta Pixel noscript beacon only renders after explicit marketing consent cookie verification.',
                ];
            }
        }

        return $issues;
    }
}
