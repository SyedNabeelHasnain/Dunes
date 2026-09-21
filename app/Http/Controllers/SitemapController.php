<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Tour;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Google Sitemap Index (sitemap_index.xml)
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $latestTour = Tour::where('status', 'active')->latest('updated_at')->first();
        $latestBlog = BlogPost::where('status', 'published')->latest('updated_at')->first();
        $now = now()->toAtomString();

        $subMaps = [
            url('/sitemap-tours.xml') => $latestTour?->updated_at ? $latestTour->updated_at->toAtomString() : $now,
            url('/sitemap-blogs.xml') => $latestBlog?->updated_at ? $latestBlog->updated_at->toAtomString() : $now,
            url('/sitemap-pages.xml') => $now,
            url('/sitemap-images.xml') => $latestTour?->updated_at ? $latestTour->updated_at->toAtomString() : $now,
        ];

        foreach ($subMaps as $loc => $lastmod) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Sub-sitemap: Active Commercial Tours
     */
    public function tours(): Response
    {
        $content = Cache::remember('sitemap_tours_xml', 3600, function () {
            $tours = Tour::where('status', 'active')->select('slug', 'name', 'hero_image', 'updated_at')->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($tours as $tour) {
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . url('/' . $tour->slug) . "</loc>\n";
                $xml .= "    <lastmod>" . ($tour->updated_at ? $tour->updated_at->toAtomString() : now()->toAtomString()) . "</lastmod>\n";
                $xml .= "    <changefreq>daily</changefreq>\n";
                $xml .= "    <priority>0.9</priority>\n";
                if (!empty($tour->hero_image)) {
                    $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
                    $xml .= "    <image:image>\n";
                    $xml .= "      <image:loc>" . asset('images/' . $imgFile) . "</image:loc>\n";
                    $xml .= "      <image:title>" . htmlspecialchars($tour->name) . "</image:title>\n";
                    $xml .= "    </image:image>\n";
                }
                $xml .= "  </url>\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap: Published Blog Posts & Guides
     */
    public function blogs(): Response
    {
        $content = Cache::remember('sitemap_blogs_xml', 3600, function () {
            $blogs = BlogPost::where('status', 'published')->select('slug', 'title', 'featured_image', 'updated_at')->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($blogs as $blog) {
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . url('/blog/' . $blog->slug) . "</loc>\n";
                $xml .= "    <lastmod>" . ($blog->updated_at ? $blog->updated_at->toAtomString() : now()->toAtomString()) . "</lastmod>\n";
                $xml .= "    <changefreq>weekly</changefreq>\n";
                $xml .= "    <priority>0.8</priority>\n";
                if (!empty($blog->featured_image)) {
                    $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
                    $xml .= "    <image:image>\n";
                    $xml .= "      <image:loc>" . asset('images/blog/' . $imgFile) . "</image:loc>\n";
                    $xml .= "      <image:title>" . htmlspecialchars($blog->title) . "</image:title>\n";
                    $xml .= "    </image:image>\n";
                }
                $xml .= "  </url>\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap: Core Institutional & Policy Pages
     */
    public function pages(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticPages = [
            '' => ['changefreq' => 'weekly', 'priority' => '1.0'],
            '/about' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/contact' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/faq' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/tours' => ['changefreq' => 'weekly', 'priority' => '0.9'],
            '/rate-card' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/blog' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/dune-buggy-rental-dubai' => ['changefreq' => 'daily', 'priority' => '0.95'],
            '/terms-condition' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/privacy-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/cookie-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/cancellation-refund-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/payment-security-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/safety-liability-waiver' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/ai-editorial-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/responsible-tourism-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/review/guest' => ['changefreq' => 'weekly', 'priority' => '0.7'],
        ];

        // Pillar 2: Programmatic Geo-Location Pickup Pages
        foreach (\App\Services\LocationLandingService::getLocations() as $loc) {
            $staticPages['/' . $loc['slug']] = ['changefreq' => 'weekly', 'priority' => '0.9'];
        }

        foreach ($staticPages as $page => $meta) {
            $loc = ($page === '' || $page === '/') ? (rtrim(url('/'), '/') . '/') : url($page);
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . $loc . "</loc>\n";
            $xml .= "    <changefreq>{$meta['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$meta['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Sub-sitemap: Google Image Sitemap
     */
    public function images(): Response
    {
        $content = Cache::remember('sitemap_images_xml', 3600, function () {
            $tours = Tour::where('status', 'active')->whereNotNull('hero_image')->get();
            $blogs = BlogPost::where('status', 'published')->whereNotNull('featured_image')->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($tours as $tour) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . url('/' . $tour->slug) . "</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . asset('images/' . $imgFile) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($tour->name) . " - Dubai Desert Safari</image:title>\n";
                $xml .= "      <image:caption>" . htmlspecialchars($tour->short_desc ?: $tour->name) . "</image:caption>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            foreach ($blogs as $blog) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . url('/blog/' . $blog->slug) . "</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . asset('images/blog/' . $imgFile) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($blog->title) . "</image:title>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            foreach (\App\Services\LocationLandingService::getLocations() as $loc) {
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . url('/' . $loc['slug']) . "</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . asset('images/desert-safari-poster.avif') . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($loc['headline']) . "</image:title>\n";
                $xml .= "      <image:caption>" . htmlspecialchars($loc['subheadline']) . "</image:caption>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    /**
     * Backward-compatible unified /sitemap.xml
     */
    public function unified(): Response
    {
        return $this->index();
    }
}
