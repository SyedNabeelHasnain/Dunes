<?php

namespace App\Services\Audit\Checkers;

use App\Models\Setting;
use App\Models\Tour;

class StructuredDataChecker implements AuditCheckerInterface
{
    public function run(array $context = []): array
    {
        $issues = [];

        // 1. Organization & TravelAgency Schema Properties
        $settings = Setting::whereIn('setting_key', [
            'site_name',
            'site_phone',
            'site_email',
            'company_license_number',
            'site_latitude',
            'site_longitude',
        ])->pluck('setting_value', 'setting_key');

        if (empty($settings['site_latitude']) || empty($settings['site_longitude'])) {
            $issues[] = [
                'category' => 'structured_data',
                'check_key' => 'schema_missing_geo_coordinates',
                'title' => 'Missing GeoCoordinates in TravelAgency Schema Graph',
                'description' => 'LocalBusiness and TravelAgency schema nodes require precise latitude and longitude coordinates for Google Local Map Pack and Knowledge Panel display.',
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => url('/'),
                'evidence' => 'site_latitude or site_longitude is empty in settings.',
                'recommended_action' => 'Enter precise GPS coordinates in CMS Settings > Identity (e.g. 25.2600, 55.2970 for Bur Dubai).',
            ];
        }

        if (empty($settings['site_phone'])) {
            $issues[] = [
                'category' => 'structured_data',
                'check_key' => 'schema_missing_telephone',
                'title' => 'Missing Telephone in Schema ContactPoint',
                'description' => 'Google Rich Results and voice assistants (Siri, Google Assistant) require an international E.164 formatted telephone number.',
                'severity' => 'high',
                'verification_type' => 'automated',
                'affected_url' => url('/contact'),
                'evidence' => 'site_phone setting is empty.',
                'recommended_action' => 'Configure official telephone number (e.g. +971 50 245 6056) in CMS General Settings.',
            ];
        }

        // 2. Active Tour Product/Trip Schema Validation
        $toursWithoutPrice = Tour::where('status', 'active')
            ->doesntHave('tiers')
            ->get();

        foreach ($toursWithoutPrice as $tour) {
            $issues[] = [
                'category' => 'structured_data',
                'check_key' => 'schema_tour_missing_pricing_tier',
                'title' => "Tour '{$tour->name}' Lacks Pricing Tiers for Product Schema",
                'description' => 'Tour has no linked pricing tier. Schema.org Offer nodes will render with empty price, causing Google Search Console merchant schema warnings.',
                'severity' => 'medium',
                'verification_type' => 'automated',
                'affected_url' => url('/' . $tour->slug),
                'evidence' => "Tour #{$tour->id} has 0 linked pricing tiers.",
                'recommended_action' => 'Assign at least one pricing tier in CMS Tours > Edit Tour > Pricing Tiers.',
            ];
        }

        return $issues;
    }
}
