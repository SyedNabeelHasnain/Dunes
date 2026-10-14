<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Language;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use App\Services\LocationLandingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Escape a string for safe XML output.
     */
    protected function xmlEscape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Google Sitemap Index (sitemap_index.xml)
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        $latestTour = Tour::where('status', 'active')->latest('updated_at')->first();
        $latestBlog = BlogPost::where('status', 'published')->latest('updated_at')->first();
        $latestWiki = WikiArticle::where('status', 'published')->latest('updated_at')->first();
        $now = now()->toAtomString();

        $tourUpdatedAt = $latestTour?->updated_at;
        $blogUpdatedAt = $latestBlog?->updated_at;
        $wikiUpdatedAt = $latestWiki?->updated_at;
        $latestImageDate = collect([$tourUpdatedAt, $blogUpdatedAt, $wikiUpdatedAt])->filter()->max();

        $subMaps = [
            url('/sitemap-tours.xml') => $tourUpdatedAt ? $tourUpdatedAt->toAtomString() : $now,
            url('/sitemap-blogs.xml') => $blogUpdatedAt ? $blogUpdatedAt->toAtomString() : $now,
            url('/sitemap-wiki.xml') => $wikiUpdatedAt ? $wikiUpdatedAt->toAtomString() : $now,
            url('/sitemap-pages.xml') => $now,
            url('/sitemap-images.xml') => $latestImageDate ? $latestImageDate->toAtomString() : $now,
        ];

        foreach ($subMaps as $loc => $lastmod) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>'.$this->xmlEscape($loc)."</loc>\n";
            $xml .= '    <lastmod>'.$this->xmlEscape($lastmod)."</lastmod>\n";
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
            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            foreach ($tours as $tour) {
                $lastmod = $tour->updated_at ? $tour->updated_at->toAtomString() : now()->toAtomString();
                $enUrl = url('/'.$tour->slug);

                // Alternates array
                $alternates = [
                    'x-default' => $enUrl,
                ];
                foreach ($languages as $lang) {
                    $alternates[$lang->code] = $lang->is_default ? $enUrl : url('/'.$lang->code.'/'.$tour->slug);
                }

                // Render entry for each active language
                foreach ($languages as $lang) {
                    $pageLoc = $alternates[$lang->code];
                    $xml .= "  <url>\n";
                    $xml .= '    <loc>'.$this->xmlEscape($pageLoc)."</loc>\n";
                    $xml .= '    <lastmod>'.$this->xmlEscape($lastmod)."</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.9</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }

                    if (! empty($tour->hero_image) && $lang->is_default) {
                        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
                        $xml .= "    <image:image>\n";
                        $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/'.$imgFile))."</image:loc>\n";
                        $xml .= '      <image:title>'.$this->xmlEscape($tour->name)."</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }
                    $xml .= "  </url>\n";
                }
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
            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            foreach ($blogs as $blog) {
                $lastmod = $blog->updated_at ? $blog->updated_at->toAtomString() : now()->toAtomString();
                $enUrl = url('/blog/'.$blog->slug);

                $alternates = [
                    'x-default' => $enUrl,
                ];
                foreach ($languages as $lang) {
                    $alternates[$lang->code] = $lang->is_default ? $enUrl : url('/'.$lang->code.'/blog/'.$blog->slug);
                }

                foreach ($languages as $lang) {
                    $pageLoc = $alternates[$lang->code];
                    $xml .= "  <url>\n";
                    $xml .= '    <loc>'.$this->xmlEscape($pageLoc)."</loc>\n";
                    $xml .= '    <lastmod>'.$this->xmlEscape($lastmod)."</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.8</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }

                    if (! empty($blog->featured_image) && $lang->is_default) {
                        $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
                        $xml .= "    <image:image>\n";
                        $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/blog/'.$imgFile))."</image:loc>\n";
                        $xml .= '      <image:title>'.$this->xmlEscape($blog->title)."</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }
                    $xml .= "  </url>\n";
                }
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
        try {
            $languages = Language::getActive();
        } catch (\Throwable $e) {
            $languages = Language::fallbackCollection();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        $staticPages = [
            '' => ['changefreq' => 'weekly', 'priority' => '1.0'],
            '/about' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/contact' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/faq' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/tours' => ['changefreq' => 'weekly', 'priority' => '0.9'],
            '/rate-card' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/blog' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/gallery' => ['changefreq' => 'daily', 'priority' => '0.85'],
            '/dubai-desert-sunset-times' => ['changefreq' => 'daily', 'priority' => '0.85'],
            '/press-kit' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/build-your-own-safari' => ['changefreq' => 'weekly', 'priority' => '0.85'],
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
        foreach (LocationLandingService::getLocations() as $loc) {
            $staticPages['/'.$loc['slug']] = ['changefreq' => 'weekly', 'priority' => '0.9'];
        }

        $latestTour = Tour::where('status', 'active')->latest('updated_at')->first();
        $latestBlog = BlogPost::where('status', 'published')->latest('updated_at')->first();
        $weeklyMod = now()->startOfWeek()->toAtomString();
        $monthlyMod = now()->startOfMonth()->toAtomString();
        $tourMod = $latestTour?->updated_at ? $latestTour->updated_at->toAtomString() : $weeklyMod;
        $blogMod = $latestBlog?->updated_at ? $latestBlog->updated_at->toAtomString() : $weeklyMod;

        foreach ($staticPages as $page => $meta) {
            if (in_array($page, ['', '/tours', '/build-your-own-safari', '/rate-card'], true)) {
                $pageLastmod = $tourMod;
            } elseif ($page === '/blog') {
                $pageLastmod = $blogMod;
            } elseif ($meta['changefreq'] === 'monthly') {
                $pageLastmod = $monthlyMod;
            } else {
                $pageLastmod = $weeklyMod;
            }

            // Alternates
            $enUrl = ($page === '' || $page === '/') ? (rtrim(url('/'), '/').'/') : url($page);
            $alternates = [
                'x-default' => $enUrl,
            ];
            foreach ($languages as $lang) {
                if ($lang->is_default) {
                    $alternates[$lang->code] = $enUrl;
                } else {
                    $alternates[$lang->code] = ($page === '' || $page === '/') ? url('/'.$lang->code) : url('/'.$lang->code.$page);
                }
            }

            foreach ($languages as $lang) {
                $loc = $alternates[$lang->code];
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape($loc)."</loc>\n";
                $xml .= '    <lastmod>'.$this->xmlEscape($pageLastmod)."</lastmod>\n";
                $xml .= "    <changefreq>{$meta['changefreq']}</changefreq>\n";
                $xml .= "    <priority>{$meta['priority']}</priority>\n";

                foreach ($alternates as $hreflang => $altUrl) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                }

                $xml .= "  </url>\n";
            }
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

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

            foreach ($tours as $tour) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $tour->hero_image);
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape(url('/'.$tour->slug))."</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/'.$imgFile))."</image:loc>\n";
                $xml .= '      <image:title>'.$this->xmlEscape($tour->name)." - Dubai Desert Safari</image:title>\n";
                $xml .= '      <image:caption>'.$this->xmlEscape($tour->short_desc ?: $tour->name)."</image:caption>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            foreach ($blogs as $blog) {
                $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $blog->featured_image);
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape(url('/blog/'.$blog->slug))."</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/blog/'.$imgFile))."</image:loc>\n";
                $xml .= '      <image:title>'.$this->xmlEscape($blog->title)."</image:title>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            foreach (LocationLandingService::getLocations() as $loc) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape(url('/'.$loc['slug']))."</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/desert-safari-poster.avif'))."</image:loc>\n";
                $xml .= '      <image:title>'.$this->xmlEscape($loc['headline'])."</image:title>\n";
                $xml .= '      <image:caption>'.$this->xmlEscape($loc['subheadline'])."</image:caption>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            // Pillar 4: Verified Authentic DDT Moments Gallery & Tour Images
            $ddtPath = database_path('seeders/data/ddt_moments.json');
            if (file_exists($ddtPath)) {
                $ddtItems = json_decode(file_get_contents($ddtPath), true);
                if (is_array($ddtItems)) {
                    foreach ($ddtItems as $item) {
                        $tourSlug = $item['tour_slug'] ?? null;
                        $pageUrl = $tourSlug ? url('/'.$tourSlug) : url('/gallery');
                        $xml .= "  <url>\n";
                        $xml .= '    <loc>'.$this->xmlEscape($pageUrl)."</loc>\n";
                        $xml .= "    <image:image>\n";
                        $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/'.$item['filename']))."</image:loc>\n";
                        $xml .= '      <image:title>'.$this->xmlEscape($item['title'])."</image:title>\n";
                        $xml .= '      <image:caption>'.$this->xmlEscape($item['caption'] ?: $item['description'])."</image:caption>\n";
                        if (! empty($item['location'])) {
                            $xml .= '      <image:geo_location>'.$this->xmlEscape($item['location'])."</image:geo_location>\n";
                        }
                        $xml .= "    </image:image>\n";
                        $xml .= "  </url>\n";
                    }
                }
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap: Knowledge Portal Pillars, Cluster Guides & Glossary
     */
    public function wiki(): Response
    {
        $content = Cache::remember('sitemap_wiki_xml', 3600, function () {
            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $pillars = WikiPillar::where('status', 'active')->with(['articles' => function ($q) {
                $q->where('status', 'published')->select('id', 'pillar_id', 'slug', 'title', 'hero_image', 'excerpt', 'updated_at');
            }])->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            $now = now()->toAtomString();
            $latestArticle = WikiArticle::where('status', 'published')->latest('updated_at')->first();
            $hubLastmod = $latestArticle?->updated_at ? $latestArticle->updated_at->toAtomString() : $now;

            // Static Wiki Hub and Glossary pages
            $basePages = [
                '/wiki' => ['lastmod' => $hubLastmod, 'priority' => '0.9', 'changefreq' => 'weekly'],
                '/wiki/glossary' => ['lastmod' => $hubLastmod, 'priority' => '0.8', 'changefreq' => 'weekly'],
            ];

            foreach ($basePages as $pagePath => $pageMeta) {
                $enUrl = url($pagePath);
                $alternates = ['x-default' => $enUrl];
                foreach ($languages as $lang) {
                    $alternates[$lang->code] = $lang->is_default ? $enUrl : url('/'.$lang->code.$pagePath);
                }

                foreach ($languages as $lang) {
                    $pageLoc = $alternates[$lang->code];
                    $xml .= "  <url>\n";
                    $xml .= '    <loc>'.$this->xmlEscape($pageLoc)."</loc>\n";
                    $xml .= '    <lastmod>'.$this->xmlEscape($pageMeta['lastmod'])."</lastmod>\n";
                    $xml .= '    <changefreq>'.$this->xmlEscape($pageMeta['changefreq'])."</changefreq>\n";
                    $xml .= '    <priority>'.$this->xmlEscape($pageMeta['priority'])."</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }
                    $xml .= "  </url>\n";
                }
            }

            // Pillars and their cluster articles
            foreach ($pillars as $pillar) {
                $pillarLastmod = $pillar->updated_at ? $pillar->updated_at->toAtomString() : $hubLastmod;
                $pillarPath = '/wiki/'.$pillar->slug;
                $enUrl = url($pillarPath);

                $alternates = ['x-default' => $enUrl];
                foreach ($languages as $lang) {
                    $alternates[$lang->code] = $lang->is_default ? $enUrl : url('/'.$lang->code.$pillarPath);
                }

                foreach ($languages as $lang) {
                    $pageLoc = $alternates[$lang->code];
                    $xml .= "  <url>\n";
                    $xml .= '    <loc>'.$this->xmlEscape($pageLoc)."</loc>\n";
                    $xml .= '    <lastmod>'.$this->xmlEscape($pillarLastmod)."</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }
                    $xml .= "  </url>\n";
                }

                foreach ($pillar->articles as $article) {
                    $artLastmod = $article->updated_at ? $article->updated_at->toAtomString() : $now;
                    $artPath = '/wiki/'.$pillar->slug.'/'.$article->slug;
                    $artEnUrl = url($artPath);

                    $artAlternates = ['x-default' => $artEnUrl];
                    foreach ($languages as $lang) {
                        $artAlternates[$lang->code] = $lang->is_default ? $artEnUrl : url('/'.$lang->code.$artPath);
                    }

                    foreach ($languages as $lang) {
                        $pageLoc = $artAlternates[$lang->code];
                        $xml .= "  <url>\n";
                        $xml .= '    <loc>'.$this->xmlEscape($pageLoc)."</loc>\n";
                        $xml .= '    <lastmod>'.$this->xmlEscape($artLastmod)."</lastmod>\n";
                        $xml .= "    <changefreq>weekly</changefreq>\n";
                        $xml .= "    <priority>0.85</priority>\n";

                        foreach ($artAlternates as $hreflang => $altUrl) {
                            $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                        }

                        if (! empty($article->hero_image) && $lang->is_default) {
                            $imgFile = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $article->hero_image);
                            $xml .= "    <image:image>\n";
                            $xml .= '      <image:loc>'.$this->xmlEscape(asset('images/'.$imgFile))."</image:loc>\n";
                            $xml .= '      <image:title>'.$this->xmlEscape($article->title)."</image:title>\n";
                            if (! empty($article->excerpt)) {
                                $xml .= '      <image:caption>'.$this->xmlEscape($article->excerpt)."</image:caption>\n";
                            }
                            $xml .= "    </image:image>\n";
                        }

                        $xml .= "  </url>\n";
                    }
                }
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
