<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Tour;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the dynamic XML sitemap.
     */
    public function index(): Response
    {
        $tours = Tour::where('status', 'active')->select('slug', 'name', 'hero_image', 'updated_at')->get();
        $blogs = BlogPost::where('status', 'published')->select('slug', 'title', 'featured_image', 'updated_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        $staticPages = [
            '' => ['changefreq' => 'weekly', 'priority' => '1.0'],
            '/about' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/contact' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/faq' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/tours' => ['changefreq' => 'weekly', 'priority' => '0.9'],
            '/rate-card' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/blog' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/terms-condition' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/privacy-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/cookie-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/cancellation-refund-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/payment-security-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/safety-liability-waiver' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/ai-editorial-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/responsible-tourism-policy' => ['changefreq' => 'monthly', 'priority' => '0.6'],
        ];
        foreach ($staticPages as $page => $meta) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url($page) . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $meta['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $meta['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        foreach ($tours as $tour) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/' . $tour->slug) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($tour->updated_at ? $tour->updated_at->toAtomString() : now()->toAtomString()) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>daily</changefreq>' . "\n";
            $xml .= '    <priority>0.9</priority>' . "\n";
            if (!empty($tour->hero_image)) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
                $xml .= '    <image:image>' . "\n";
                $xml .= '      <image:loc>' . asset('images/blog/' . $imgFile) . '</image:loc>' . "\n";
                $xml .= '      <image:title>' . htmlspecialchars($tour->name) . '</image:title>' . "\n";
                $xml .= '    </image:image>' . "\n";
            }
            $xml .= '  </url>' . "\n";
        }

        foreach ($blogs as $blog) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/blog/' . $blog->slug) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($blog->updated_at ? $blog->updated_at->toAtomString() : now()->toAtomString()) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            if (!empty($blog->featured_image)) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
                $xml .= '    <image:image>' . "\n";
                $xml .= '      <image:loc>' . asset('images/blog/' . $imgFile) . '</image:loc>' . "\n";
                $xml .= '      <image:title>' . htmlspecialchars($blog->title) . '</image:title>' . "\n";
                $xml .= '    </image:image>' . "\n";
            }
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }
}
