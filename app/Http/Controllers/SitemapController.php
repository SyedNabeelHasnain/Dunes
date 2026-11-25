<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Language;
use App\Models\Page;
use App\Models\RedirectRule;
use App\Models\SeoTemplate;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use App\Services\LocationLandingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public static function flushAllSitemapCaches(): void
    {
        foreach ([
            'sitemap_index_xml',
            'sitemap_tours_xml',
            'sitemap_blogs_xml',
            'sitemap_pages_xml',
            'sitemap_images_xml',
            'sitemap_wiki_xml',
            'sitemap_videos_xml',
            'site_llms_txt',
            'site_llms_full_txt',
        ] as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Resolve content-type SEO template sitemap preferences (priority, changefreq, enabled).
     */
    protected function loadSeoTemplate(string $contentType): ?SeoTemplate
    {
        try {
            if (! Schema::hasTable('seo_templates')) {
                return null;
            }

            return SeoTemplate::where('content_type', $contentType)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Determine whether an entity has an explicit external cross-domain canonical URL.
     */
    protected function isExternalCanonical(?string $canonicalUrl): bool
    {
        $canonicalUrl = trim((string) $canonicalUrl);
        if ($canonicalUrl === '' || (! str_starts_with($canonicalUrl, 'http://') && ! str_starts_with($canonicalUrl, 'https://'))) {
            return false;
        }

        $targetHost = strtolower((string) parse_url($canonicalUrl, PHP_URL_HOST));
        $appHost = strtolower((string) parse_url(url('/'), PHP_URL_HOST));

        return $targetHost !== '' && $appHost !== '' && $targetHost !== $appHost;
    }

    /**
     * Build lookup of active dynamic redirect or 410 Gone paths so sitemaps never list redirected/gone URLs.
     *
     * @return array<string, true>
     */
    protected function activeRedirectSourceMap(): array
    {
        try {
            if (! Schema::hasTable('redirect_rules')) {
                return [];
            }

            return RedirectRule::where('is_active', true)
                ->pluck('source_path')
                ->mapWithKeys(fn ($p) => [RedirectRule::normalizePath((string) $p) => true])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function shouldExcludeFromSitemap(string $path, mixed $entity = null, ?array $redirectMap = null): bool
    {
        $redirectMap ??= $this->activeRedirectSourceMap();
        $norm = RedirectRule::normalizePath($path);
        if (isset($redirectMap[$norm])) {
            return true;
        }

        if ($entity) {
            $meta = method_exists($entity, 'seoMeta') ? $entity->seoMeta : null;
            if ($meta && $meta->include_in_sitemap === false) {
                return true;
            }
            $robots = strtolower((string) ($meta?->robots_directive ?: ($entity->robots ?? '')));
            if ($meta?->include_in_sitemap !== true && str_contains($robots, 'noindex')) {
                return true;
            }
        }

        return false;
    }
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

        $subMaps = [];
        $tourTpl = $this->loadSeoTemplate('tour');
        $blogTpl = $this->loadSeoTemplate('blog_post');
        $wikiTpl = $this->loadSeoTemplate('wiki_article');
        $pageTpl = $this->loadSeoTemplate('page');

        if (! $tourTpl || $tourTpl->sitemap_enabled !== false) {
            $subMaps[url('/sitemap-tours.xml')] = $tourUpdatedAt ? $tourUpdatedAt->toAtomString() : $now;
        }
        if (! $blogTpl || $blogTpl->sitemap_enabled !== false) {
            $subMaps[url('/sitemap-blogs.xml')] = $blogUpdatedAt ? $blogUpdatedAt->toAtomString() : $now;
        }
        if (! $wikiTpl || $wikiTpl->sitemap_enabled !== false) {
            $subMaps[url('/sitemap-wiki.xml')] = $wikiUpdatedAt ? $wikiUpdatedAt->toAtomString() : $now;
        }
        if (! $pageTpl || $pageTpl->sitemap_enabled !== false) {
            $subMaps[url('/sitemap-pages.xml')] = $now;
        }
        $subMaps[url('/sitemap-images.xml')] = $latestImageDate ? $latestImageDate->toAtomString() : $now;
        $subMaps[url('/sitemap-videos.xml')] = $now;

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
            $template = $this->loadSeoTemplate('tour');
            if ($template && $template->sitemap_enabled === false) {
                return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
            }

            $changefreq = ($template && $template->is_active && ! empty($template->sitemap_changefreq)) ? $template->sitemap_changefreq : 'daily';
            $priority = ($template && $template->is_active && $template->sitemap_priority !== null)
                ? number_format((float) $template->sitemap_priority, 1, '.', '')
                : '0.9';

            $tours = Tour::where('status', 'active')
                ->select('id', 'slug', 'name', 'hero_image', 'updated_at')
                ->with('seoMeta')
                ->get();
            $redirectMap = $this->activeRedirectSourceMap();
            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            foreach ($tours as $tour) {
                if ($this->shouldExcludeFromSitemap('/'.$tour->slug, $tour, $redirectMap)) {
                    continue;
                }
                if ($this->isExternalCanonical($tour->seoMeta?->canonical_override)) {
                    continue;
                }
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
                    $xml .= '    <changefreq>'.$this->xmlEscape($changefreq)."</changefreq>\n";
                    $xml .= '    <priority>'.$this->xmlEscape($priority)."</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }

                    if (! empty($tour->hero_image) && $lang->is_default) {
                        $imgUrl = resolve_tour_image($tour->hero_image);
                        $xml .= "    <image:image>\n";
                        $xml .= '      <image:loc>'.$this->xmlEscape($imgUrl)."</image:loc>\n";
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
            $template = $this->loadSeoTemplate('blog_post');
            if ($template && $template->sitemap_enabled === false) {
                return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
            }

            $changefreq = ($template && $template->is_active && ! empty($template->sitemap_changefreq)) ? $template->sitemap_changefreq : 'weekly';
            $priority = ($template && $template->is_active && $template->sitemap_priority !== null)
                ? number_format((float) $template->sitemap_priority, 1, '.', '')
                : '0.8';

            $blogs = BlogPost::where('status', 'published')
                ->select('id', 'slug', 'title', 'featured_image', 'canonical_url', 'robots', 'updated_at')
                ->with('seoMeta')
                ->get();
            $redirectMap = $this->activeRedirectSourceMap();
            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            foreach ($blogs as $blog) {
                if ($this->shouldExcludeFromSitemap('/blog/'.$blog->slug, $blog, $redirectMap)) {
                    continue;
                }
                $lastmod = $blog->updated_at ? $blog->updated_at->toAtomString() : now()->toAtomString();
                $rawCanonical = $blog->seoMeta?->canonical_override ?: $blog->canonical_url;
                if ($this->isExternalCanonical($rawCanonical)) {
                    continue;
                }
                $enUrl = ! empty($rawCanonical) && str_starts_with($rawCanonical, 'http') ? $rawCanonical : url('/blog/'.$blog->slug);

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
                    $xml .= '    <changefreq>'.$this->xmlEscape($changefreq)."</changefreq>\n";
                    $xml .= '    <priority>'.$this->xmlEscape($priority)."</priority>\n";

                    foreach ($alternates as $hreflang => $altUrl) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                    }

                    if (! empty($blog->featured_image) && $lang->is_default) {
                        $imgUrl = $this->resolveDiskImage($blog->featured_image, 'images/blog');
                        $xml .= "    <image:image>\n";
                        $xml .= '      <image:loc>'.$this->xmlEscape($imgUrl)."</image:loc>\n";
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
        $content = Cache::remember('sitemap_pages_xml', 3600, function () {
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

            $redirectMap = $this->activeRedirectSourceMap();
            $pageModels = collect();
            try {
                $pageModels = Page::with('seoMeta')->get()->keyBy(fn ($p) => ltrim((string) $p->slug, '/'));
            } catch (\Throwable) {
                $pageModels = collect();
            }

            foreach ($staticPages as $page => $meta) {
                $slugKey = ltrim((string) $page, '/') ?: 'home';
                $pageEntity = $pageModels->get($slugKey) ?? $pageModels->get(ltrim((string) $page, '/'));
                if ($this->shouldExcludeFromSitemap($page ?: '/', $pageEntity, $redirectMap)) {
                    continue;
                }
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

            return $xml;
        });

        return response($content, 200, [
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
            $redirectMap = $this->activeRedirectSourceMap();
            $tours = Tour::where('status', 'active')->whereNotNull('hero_image')->with('seoMeta')->get();
            $blogs = BlogPost::where('status', 'published')->whereNotNull('featured_image')->with('seoMeta')->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

            foreach ($tours as $tour) {
                if ($this->shouldExcludeFromSitemap('/'.$tour->slug, $tour, $redirectMap)) {
                    continue;
                }
                $imgUrl = resolve_tour_image($tour->hero_image);
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape(url('/'.$tour->slug))."</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.$this->xmlEscape($imgUrl)."</image:loc>\n";
                $xml .= '      <image:title>'.$this->xmlEscape($tour->name)." - Dubai Desert Safari</image:title>\n";
                $xml .= '      <image:caption>'.$this->xmlEscape($tour->short_desc ?: $tour->name)."</image:caption>\n";
                $xml .= "    </image:image>\n";
                $xml .= "  </url>\n";
            }

            foreach ($blogs as $blog) {
                if ($this->shouldExcludeFromSitemap('/blog/'.$blog->slug, $blog, $redirectMap)) {
                    continue;
                }
                $imgUrl = $this->resolveDiskImage($blog->featured_image, 'images/blog');
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape(url('/blog/'.$blog->slug))."</loc>\n";
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.$this->xmlEscape($imgUrl)."</image:loc>\n";
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
            $template = $this->loadSeoTemplate('wiki_article');
            if ($template && $template->sitemap_enabled === false) {
                return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
            }

            $artChangefreq = ($template && $template->is_active && ! empty($template->sitemap_changefreq)) ? $template->sitemap_changefreq : 'weekly';
            $artPriority = ($template && $template->is_active && $template->sitemap_priority !== null)
                ? number_format((float) $template->sitemap_priority, 2, '.', '')
                : '0.85';

            try {
                $languages = Language::getActive();
            } catch (\Throwable $e) {
                $languages = Language::fallbackCollection();
            }

            $redirectMap = $this->activeRedirectSourceMap();
            $pillars = WikiPillar::where('status', 'active')->with(['seoMeta', 'articles' => function ($q) {
                $q->where('status', 'published')
                    ->select('id', 'pillar_id', 'slug', 'title', 'hero_image', 'excerpt', 'updated_at')
                    ->with('seoMeta');
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
                    if ($this->shouldExcludeFromSitemap($artPath, $article, $redirectMap)) {
                        continue;
                    }
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
                        $xml .= '    <changefreq>'.$this->xmlEscape($artChangefreq)."</changefreq>\n";
                        $xml .= '    <priority>'.$this->xmlEscape($artPriority)."</priority>\n";

                        foreach ($artAlternates as $hreflang => $altUrl) {
                            $xml .= '    <xhtml:link rel="alternate" hreflang="'.$this->xmlEscape($hreflang).'" href="'.$this->xmlEscape($altUrl)."\" />\n";
                        }

                        if (! empty($article->hero_image) && $lang->is_default) {
                            $imgUrl = $this->resolveDiskImage($article->hero_image, 'images');
                            $xml .= "    <image:image>\n";
                            $xml .= '      <image:loc>'.$this->xmlEscape($imgUrl)."</image:loc>\n";
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
     * Resolve image URL ensuring that it exists on disk or fallback to safe poster.
     */
    protected function resolveDiskImage(string $path, string $folder = 'images'): string
    {
        $clean = ltrim(preg_replace('#^'.$folder.'/#i', '', $path), '/');
        $avifCandidate = $folder.'/'.preg_replace('/\.(jpe?g|png|webp|avif)$/i', '.avif', $clean);
        if (file_exists(public_path($avifCandidate))) {
            return asset($avifCandidate);
        }
        if (file_exists(public_path($folder.'/'.$clean))) {
            return asset($folder.'/'.$clean);
        }
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        return asset('images/desert-safari-poster.avif');
    }

    /**
     * Sub-sitemap: Google Video XML Sitemap (/sitemap-videos.xml and /video-sitemap.xml)
     */
    public function videos(): Response
    {
        $content = Cache::remember('sitemap_videos_xml', 3600, function () {
            $galleryService = app(\App\Services\ReviewMediaGalleryService::class);
            $videoReviews = $galleryService->getCustomerVideoReviews();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">'."\n";

            foreach ($videoReviews as $item) {
                $pageUrl = ! empty($item['tour_slug'])
                    ? url('/'.$item['tour_slug'])
                    : url('/gallery?item='.$item['id']);
                $videoUrl = $item['url'];
                $thumbnailUrl = $item['poster_url'];
                $title = $item['title'];
                $description = ! empty($item['description']) ? $item['description'] : ($item['caption'] ?: $title);
                $duration = (int) ($item['duration'] ?? 30);
                $pubDate = ! empty($item['published_date'])
                    ? \Carbon\Carbon::parse($item['published_date'])->format('Y-m-d\TH:i:s+04:00')
                    : now()->format('Y-m-d\TH:i:s+04:00');

                $xml .= "  <url>\n";
                $xml .= '    <loc>'.$this->xmlEscape($pageUrl)."</loc>\n";
                $xml .= "    <video:video>\n";
                $xml .= '      <video:thumbnail_loc>'.$this->xmlEscape($thumbnailUrl)."</video:thumbnail_loc>\n";
                $xml .= '      <video:title>'.$this->xmlEscape($title)."</video:title>\n";
                $xml .= '      <video:description>'.$this->xmlEscape($description)."</video:description>\n";
                $xml .= '      <video:content_loc>'.$this->xmlEscape($videoUrl)."</video:content_loc>\n";
                $xml .= '      <video:player_loc>'.$this->xmlEscape($pageUrl)."</video:player_loc>\n";
                $xml .= '      <video:duration>'.$duration."</video:duration>\n";
                $xml .= '      <video:publication_date>'.$pubDate."</video:publication_date>\n";
                $xml .= "      <video:family_friendly>yes</video:family_friendly>\n";
                $xml .= '      <video:uploader info="'.url('/').'">Dunes Discovery Tourism</video:uploader>'."\n";
                $xml .= "      <video:live>no</video:live>\n";
                $xml .= "      <video:requires_subscription>no</video:requires_subscription>\n";

                if (! empty($item['keywords']) && is_array($item['keywords'])) {
                    foreach (array_slice($item['keywords'], 0, 5) as $tag) {
                        $xml .= '      <video:tag>'.$this->xmlEscape($tag)."</video:tag>\n";
                    }
                }

                $xml .= "    </video:video>\n";
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
