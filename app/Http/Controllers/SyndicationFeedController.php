<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SyndicationFeedController extends Controller
{
    /**
     * Cache duration: 3600 seconds (1 hour).
     */
    protected int $cacheTtl = 3600;

    /**
     * Generate and serve the public Tours RSS 2.0 / Media RSS XML feed.
     */
    public function toursXml(): Response
    {
        $content = Cache::remember('feeds_tours_rss_xml', $this->cacheTtl, function () {
            return $this->buildToursRssXml();
        });

        return response($content, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Build the RSS XML string.
     */
    protected function buildToursRssXml(): string
    {
        try {
            $settings = Setting::pluck('setting_value', 'setting_key')->all();
        } catch (\Throwable $e) {
            $settings = [];
        }

        $license = $settings['company_license_number'] ?? ($settings['site_det_license'] ?? '1430583');
        $tours = Tour::with(['category', 'tiers'])->where('status', 'active')->orderBy('priority', 'asc')->get();

        $now = now()->toRfc2822String();
        $feedUrl = url('/feeds/tours.xml');
        $siteUrl = url('/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dunes="https://dunesdiscoverytourism.com/ns/tours">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= '    <title>' . $this->escape('Dunes Discovery Tourism | Dubai Tours & Desert Safaris Feed') . "</title>\n";
        $xml .= "    <link>{$siteUrl}</link>\n";
        $xml .= '    <description>' . $this->escape("Official syndicated catalog of certified Dubai Desert Safaris, Dune Buggy Rentals, City Tours, and Luxury Cruises by Dunes Discovery Tourism LLC (DET License #{$license}).") . "</description>\n";
        $xml .= "    <language>en</language>\n";
        $xml .= "    <lastBuildDate>{$now}</lastBuildDate>\n";
        $xml .= "    <atom:link href=\"{$feedUrl}\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($tours as $tour) {
            $tourUrl = url('/' . $tour->slug);
            $title = $this->escape($tour->name);
            $category = $this->escape($tour->category?->name ?? 'Desert Safari');
            
            $startingPrice = 79.00;
            if ($tour->tiers->isNotEmpty()) {
                $minPrice = $tour->tiers->min(fn($t) => $t->pivot->price ?? 0);
                if ($minPrice > 0) {
                    $startingPrice = (float) $minPrice;
                }
            }

            $pickupTime = $tour->pickup_time ?: 'Flexible Departure';
            $dropoffTime = $tour->dropoff_time ?: 'Hotel Return';
            $desc = strip_tags((string) $tour->short_desc);
            $descFull = "{$desc} Starting from AED {$startingPrice}. Duration: {$tour->duration}. Pickup: {$pickupTime}. Drop-off: {$dropoffTime}. Verified DET License #{$license}.";
            $descEsc = $this->escape($descFull);

            $images = $tour->getImageList();
            $heroImage = $images[0] ?? url('/images/placeholder.webp');

            $pubDate = ($tour->updated_at ?: now())->toRfc2822String();

            $xml .= "    <item>\n";
            $xml .= "      <title>{$title}</title>\n";
            $xml .= "      <link>{$tourUrl}</link>\n";
            $xml .= "      <guid isPermaLink=\"true\">{$tourUrl}</guid>\n";
            $xml .= "      <description>{$descEsc}</description>\n";
            $xml .= "      <category>{$category}</category>\n";
            $xml .= "      <dc:creator>Dunes Discovery Tourism</dc:creator>\n";
            $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
            $xml .= '      <dunes:starting_price currency="AED">' . number_format($startingPrice, 2, '.', '') . "</dunes:starting_price>\n";
            $xml .= '      <dunes:duration>' . $this->escape($tour->duration) . "</dunes:duration>\n";
            $xml .= '      <dunes:pickup_time>' . $this->escape($pickupTime) . "</dunes:pickup_time>\n";
            $xml .= '      <dunes:dropoff_time>' . $this->escape($dropoffTime) . "</dunes:dropoff_time>\n";
            $xml .= '      <media:content url="' . $this->escape($heroImage) . '" medium="image" />' . "\n";
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= "</rss>";

        return $xml;
    }

    /**
     * Escape XML special characters.
     */
    protected function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
