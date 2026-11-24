<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\SeoMetadata;
use App\Models\SeoTemplate;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Models\WikiPillar;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 3-Tier SEO & Structured Data Inheritance Engine:
 * Global Defaults -> Content Type Templates (seo_templates) -> Entity/Page Overrides (seo_metadata + model fields).
 */
class SeoResolverService
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * Resolve effective SEO metadata and origin trace for any public route/entity.
     *
     * @return array{
     *   title: string,
     *   description: string,
     *   keywords: string,
     *   robots: string,
     *   canonical_url: string,
     *   og_title: string,
     *   og_description: string,
     *   og_image: string,
     *   og_type: string,
     *   twitter_card: string,
     *   focus_keyword: ?string,
     *   secondary_keywords: array<int, string>,
     *   search_intent: ?string,
     *   schema_type: ?string,
     *   schema_json_override: ?string,
     *   editorial_status: string,
     *   origins: array<string, string>,
     *   template: ?SeoTemplate
     * }
     */
    public function resolve(
        string $contentType,
        ?Model $entity = null,
        array $fallback = [],
        ?Request $request = null,
        ?string $locale = null,
    ): array {
        $request ??= app()->bound('request') ? request() : null;
        $locale ??= app()->getLocale();
        $rawSiteSettings = $this->settingsService->all();
        $siteSettings = $rawSiteSettings instanceof \Illuminate\Support\Collection ? $rawSiteSettings->all() : (is_array($rawSiteSettings) ? $rawSiteSettings : []);
        $siteName = trim((string) ($siteSettings['site_name'] ?? 'Dunes Discovery Tourism')) ?: 'Dunes Discovery Tourism';
        $currentYear = date('Y');

        $template = $this->loadTemplate($contentType);
        $seoMeta = ($entity && method_exists($entity, 'seoMeta')) ? $entity->seoMeta : null;

        $origins = [];

        // Extract entity base labels and category context
        $rawEntityTitle = $this->extractTranslatedAttr($entity, ['title', 'name'], $locale);
        $rawEntityDesc = $this->extractTranslatedAttr($entity, ['excerpt', 'short_desc', 'subtitle', 'description'], $locale);
        $categoryName = $this->extractCategoryName($entity, $locale);
        $focusKeyword = $this->resolveFocusKeyword($entity, $seoMeta);

        $tokens = [
            '{title}' => trim((string) ($fallback['raw_title'] ?? $rawEntityTitle ?: $siteName)),
            '{site_name}' => $siteName,
            '{year}' => $currentYear,
            '{excerpt}' => Str::limit(trim(strip_tags((string) ($fallback['raw_description'] ?? $rawEntityDesc))), 155, ''),
            '{description}' => trim(strip_tags((string) ($fallback['raw_description'] ?? $rawEntityDesc))),
            '{category}' => $categoryName ?: 'Dubai Desert Tours',
            '{focus_keyword}' => $focusKeyword ?: 'dubai desert safari',
        ];

        // ── 1. Title resolution: Item field -> Global route setting -> Content Type Template -> Global default -> Auto fallback
        $itemTitle = $this->extractTranslatedAttr($entity, ['meta_title'], $locale);
        $routeSettingTitle = ! empty($fallback['setting_key_prefix'])
            ? trim((string) ($siteSettings[$fallback['setting_key_prefix'].'_title'] ?? ''))
            : '';

        if ($itemTitle !== '') {
            $title = $itemTitle;
            $origins['title'] = 'item_override';
        } elseif ($routeSettingTitle !== '') {
            $title = $routeSettingTitle;
            $origins['title'] = 'page_setting_override';
        } elseif ($template && $template->is_active && ! empty($template->title_pattern)) {
            $title = $this->applyPattern($template->title_pattern, $tokens);
            $origins['title'] = 'content_type_template';
        } elseif (! empty($siteSettings['seo_default_title'])) {
            $title = trim((string) $siteSettings['seo_default_title']);
            $origins['title'] = 'global_default';
        } elseif (! empty($fallback['title'])) {
            $title = (string) $fallback['title'];
            $origins['title'] = 'auto_fallback';
        } else {
            $title = "{$siteName} | Dubai Desert Safari Tours ({$currentYear})";
            $origins['title'] = 'auto_fallback';
        }

        // ── 2. Description resolution
        $itemDesc = $this->extractTranslatedAttr($entity, ['meta_desc', 'meta_description'], $locale);
        $routeSettingDesc = ! empty($fallback['setting_key_prefix'])
            ? trim((string) ($siteSettings[$fallback['setting_key_prefix'].'_description'] ?? ''))
            : '';

        if ($itemDesc !== '') {
            $description = $itemDesc;
            $origins['description'] = 'item_override';
        } elseif ($routeSettingDesc !== '') {
            $description = $routeSettingDesc;
            $origins['description'] = 'page_setting_override';
        } elseif ($template && $template->is_active && ! empty($template->description_pattern) && $this->applyPattern($template->description_pattern, $tokens) !== '') {
            $description = $this->applyPattern($template->description_pattern, $tokens);
            $origins['description'] = 'content_type_template';
        } elseif (! empty($siteSettings['seo_default_description'])) {
            $description = trim((string) $siteSettings['seo_default_description']);
            $origins['description'] = 'global_default';
        } else {
            $description = (string) ($fallback['description'] ?? 'Book Dubai best desert safari tours with Dunes Discovery Tourism.');
            $origins['description'] = 'auto_fallback';
        }

        // ── 3. Keywords resolution
        $itemKeywords = $this->extractTranslatedAttr($entity, ['meta_keywords'], $locale);
        $routeSettingKeywords = ! empty($fallback['setting_key_prefix'])
            ? trim((string) ($siteSettings[$fallback['setting_key_prefix'].'_keywords'] ?? ''))
            : '';

        if ($itemKeywords !== '') {
            $keywords = $itemKeywords;
            $origins['keywords'] = 'item_override';
        } elseif ($routeSettingKeywords !== '') {
            $keywords = $routeSettingKeywords;
            $origins['keywords'] = 'page_setting_override';
        } elseif ($template && $template->is_active && ! empty($template->keywords_pattern)) {
            $keywords = $this->applyPattern($template->keywords_pattern, $tokens);
            $origins['keywords'] = 'content_type_template';
        } elseif (! empty($siteSettings['seo_default_keywords'])) {
            $keywords = trim((string) $siteSettings['seo_default_keywords']);
            $origins['keywords'] = 'global_default';
        } else {
            $keywords = (string) ($fallback['keywords'] ?? 'dubai desert safari, dunes discovery tourism');
            $origins['keywords'] = 'auto_fallback';
        }

        // ── 4. Robots directive + centralized query-param policy
        $entityRobots = trim((string) ($seoMeta?->robots_directive ?: ($entity?->robots ?? '')));
        if ($entityRobots !== '') {
            $baseRobots = $entityRobots;
            $origins['robots'] = 'item_override';
        } elseif ($template && $template->is_active && ! empty($template->robots_default)) {
            $baseRobots = $template->robots_default;
            $origins['robots'] = 'content_type_template';
        } else {
            $baseRobots = (string) ($fallback['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
            $origins['robots'] = 'global_default';
        }

        $robots = $this->applyQueryParamRobotsPolicy($baseRobots, $request, $siteSettings);

        // ── 5. Canonical resolution
        $itemCanonical = trim((string) ($seoMeta?->canonical_override ?: ($entity?->canonical_url ?? '')));
        if ($itemCanonical !== '' && ($locale === 'en' || ! empty($seoMeta?->canonical_override))) {
            // Preserve self-referential localized canonicals when entity stores only default EN canonical
            $canonicalUrl = ($locale !== 'en' && ! empty($seoMeta?->canonical_override) && str_contains($itemCanonical, url('/')))
                ? $itemCanonical
                : ($locale === 'en' ? $itemCanonical : (string) ($fallback['canonical_url'] ?? $request?->url() ?? url('/')));
            $origins['canonical_url'] = 'item_override';
        } else {
            $canonicalUrl = (string) ($fallback['canonical_url'] ?? $request?->url() ?? url('/'));
            $origins['canonical_url'] = 'auto_self_referential';
        }

        // ── 6. OpenGraph & Schema overrides
        $ogTitle = trim((string) ($seoMeta?->og_title ?: ($entity?->og_title ?? ''))) ?: $title;
        $ogDesc = trim((string) ($seoMeta?->og_description ?: ($entity?->og_desc ?? ''))) ?: $description;

        $routeSettingOgImage = ! empty($fallback['setting_key_prefix'])
            ? trim((string) ($siteSettings[$fallback['setting_key_prefix'].'_og_image'] ?? ''))
            : '';
        $rawOgImg = $seoMeta?->og_image
            ?: ($entity?->og_image ?? null)
            ?: ($routeSettingOgImage ?: ($siteSettings['seo_default_og_image'] ?? null));

        $ogImage = $rawOgImg
            ? resolve_og_image($rawOgImg)
            : (string) ($fallback['og_image'] ?? asset('images/evening-desert-safari-dubai-dune-discovery-tourism.avif'));

        $ogType = trim((string) ($entity?->og_type ?: ($template?->og_type_default ?: ($fallback['og_type'] ?? 'website'))));
        $twitterCard = trim((string) ($seoMeta?->twitter_card ?: 'summary_large_image'));
        $schemaType = trim((string) ($seoMeta?->schema_type_override ?: ($entity?->schema_type ?: ($template?->schema_type_default ?: '')))) ?: null;

        return [
            'title' => trim($title),
            'description' => trim($description),
            'keywords' => trim(preg_replace('/\s*,\s*/', ', ', trim($keywords, " ,\t\n\r"))),
            'robots' => $robots,
            'canonical_url' => $canonicalUrl,
            'og_title' => $ogTitle,
            'og_description' => $ogDesc,
            'og_image' => $ogImage,
            'og_type' => $ogType,
            'twitter_card' => $twitterCard,
            'focus_keyword' => $focusKeyword,
            'secondary_keywords' => is_array($seoMeta?->secondary_keywords) ? $seoMeta->secondary_keywords : [],
            'search_intent' => $seoMeta?->search_intent,
            'schema_type' => $schemaType,
            'schema_json_override' => $seoMeta?->schema_json_override,
            'editorial_status' => $seoMeta?->editorial_status ?? 'published',
            'origins' => $origins,
            'template' => $template,
        ];
    }

    /**
     * Resolve hierarchical SEO overrides transparently for layout Blade rendering across all public controllers.
     */
    public function applyToLayoutVariables(array $vars, ?Request $request = null): array
    {
        $request ??= app()->bound('request') ? request() : null;
        $locale = (string) ($vars['currentLocale'] ?? app()->getLocale());
        $rawSettings = $vars['settings'] ?? $this->settingsService->all();
        $settings = $rawSettings instanceof \Illuminate\Support\Collection
            ? $rawSettings->all()
            : (is_array($rawSettings) ? $rawSettings : []);

        $entity = $vars['seoEntity']
            ?? $vars['tour']
            ?? $vars['post']
            ?? $vars['article']
            ?? $vars['pillar']
            ?? $vars['page']
            ?? null;

        $contentType = match (true) {
            $entity instanceof Tour => 'tour',
            $entity instanceof BlogPost => 'blog_post',
            $entity instanceof WikiArticle => 'wiki_article',
            $entity instanceof WikiPillar => 'wiki_pillar',
            $entity instanceof Page => 'page',
            $request && trim($request->path(), '/') === '' => 'home',
            default => 'page',
        };

        // Load matching CMS Page record for core routes like about/contact/faq/home if not explicitly passed
        if (! $entity && $request) {
            try {
                if (Schema::hasTable('pages')) {
                    $slugPath = trim((string) preg_replace('#^(en|ar|ru|es|it)(/|$)#', '', trim($request->path(), '/')), '/') ?: 'home';
                    $cmsPage = Page::where('slug', $slugPath)->with('seoMeta')->first();
                    if ($cmsPage) {
                        $entity = $cmsPage;
                    }
                }
            } catch (\Throwable) {
                $entity = null;
            }
        }

        if ($entity && method_exists($entity, 'loadMissing')) {
            try {
                if (Schema::hasTable('seo_metadata')) {
                    $entity->loadMissing('seoMeta');
                }
            } catch (\Throwable) {
                // Continue gracefully
            }
        }

        $seoMeta = ($entity && method_exists($entity, 'seoMeta')) ? $entity->seoMeta : null;
        $dbTemplate = null;
        try {
            if (Schema::hasTable('seo_templates')) {
                $dbTemplate = SeoTemplate::where('content_type', $contentType)->where('is_active', true)->first();
            }
        } catch (\Throwable) {
            $dbTemplate = null;
        }

        $siteName = trim((string) ($settings['site_name'] ?? 'Dunes Discovery Tourism')) ?: 'Dunes Discovery Tourism';
        $currentYear = date('Y');
        $rawEntityTitle = $this->extractTranslatedAttr($entity, ['title', 'name'], $locale);
        $rawEntityDesc = $this->extractTranslatedAttr($entity, ['excerpt', 'short_desc', 'subtitle', 'description'], $locale);
        $categoryName = $this->extractCategoryName($entity, $locale);
        $focusKeyword = $this->resolveFocusKeyword($entity, $seoMeta);

        $tokens = [
            '{title}' => $rawEntityTitle ?: ($vars['pageTitle'] ?? $siteName),
            '{site_name}' => $siteName,
            '{year}' => $currentYear,
            '{excerpt}' => Str::limit(trim(strip_tags($rawEntityDesc)), 155, ''),
            '{description}' => trim(strip_tags($rawEntityDesc)) ?: (string) ($vars['pageDesc'] ?? ''),
            '{category}' => $categoryName ?: 'Dubai Desert Tours',
            '{focus_keyword}' => $focusKeyword ?: 'dubai desert safari',
        ];

        $out = [];

        // 1. Item-level explicit meta override (including Page::meta_title / meta_description)
        $itemMetaTitle = $this->extractTranslatedAttr($entity, ['meta_title'], $locale);
        $itemMetaDesc = $this->extractTranslatedAttr($entity, ['meta_desc', 'meta_description'], $locale);
        $itemMetaKeys = $this->extractTranslatedAttr($entity, ['meta_keywords'], $locale);

        if ($itemMetaTitle !== '') {
            $out['pageTitle'] = $itemMetaTitle;
        } elseif ($dbTemplate && ! empty($dbTemplate->title_pattern) && $dbTemplate->updated_by !== null) {
            $out['pageTitle'] = $this->applyPattern($dbTemplate->title_pattern, $tokens);
        } elseif (empty($vars['pageTitle']) && ! empty($settings['seo_default_title'])) {
            $out['pageTitle'] = trim((string) $settings['seo_default_title']);
        }

        if ($itemMetaDesc !== '') {
            $out['pageDesc'] = $itemMetaDesc;
        } elseif ($dbTemplate && ! empty($dbTemplate->description_pattern) && $dbTemplate->updated_by !== null) {
            $renderedDesc = $this->applyPattern($dbTemplate->description_pattern, $tokens);
            if ($renderedDesc !== '') {
                $out['pageDesc'] = $renderedDesc;
            }
        } elseif (empty($vars['pageDesc']) && ! empty($settings['seo_default_description'])) {
            $out['pageDesc'] = trim((string) $settings['seo_default_description']);
        }

        if ($itemMetaKeys !== '') {
            $out['pageKeys'] = $itemMetaKeys;
        } elseif ($dbTemplate && ! empty($dbTemplate->keywords_pattern) && $dbTemplate->updated_by !== null) {
            $renderedKeys = $this->applyPattern($dbTemplate->keywords_pattern, $tokens);
            if ($renderedKeys !== '') {
                $out['pageKeys'] = $renderedKeys;
            }
        } elseif (empty($vars['pageKeys']) && ! empty($settings['seo_default_keywords'])) {
            $out['pageKeys'] = trim((string) $settings['seo_default_keywords']);
        }

        // 2. Robots resolution preserving explicit indexation (e.g. ?page=2) and transactional noindex,nofollow
        $existingRobots = (string) ($vars['pageRobots'] ?? '');
        if ($existingRobots !== 'noindex, nofollow') {
            $baseRobots = null;
            if (! empty($seoMeta?->robots_directive)) {
                $baseRobots = (string) $seoMeta->robots_directive;
            } elseif ($entity && ! empty($entity->robots)) {
                $baseRobots = (string) $entity->robots;
            } elseif ($dbTemplate && ! empty($dbTemplate->robots_default) && $dbTemplate->updated_by !== null) {
                $baseRobots = (string) $dbTemplate->robots_default;
            }

            if ($baseRobots !== null) {
                $out['pageRobots'] = str_contains($existingRobots, 'noindex')
                    ? $this->applyQueryParamRobotsPolicy($baseRobots, $request, $settings)
                    : $baseRobots;
            } elseif (str_contains($existingRobots, 'noindex') && $request && $request->query() !== [] && ($settings['seo_index_tracking_params'] ?? '0') === '1') {
                $trackingKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid', 'wbraid', 'gbraid', 'gad_source'];
                if (array_diff(array_keys($request->query()), $trackingKeys) === []) {
                    $out['pageRobots'] = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
                }
            }
        }

        // 3. Canonical & Social & Custom Schema overrides from normalized seo_metadata
        if (! empty($seoMeta?->canonical_override) && $locale === 'en') {
            $out['canonicalUrl'] = (string) $seoMeta->canonical_override;
        }
        if (! empty($seoMeta?->og_title)) {
            $out['ogTitle'] = (string) $seoMeta->og_title;
        }
        if (! empty($seoMeta?->og_description)) {
            $out['ogDesc'] = (string) $seoMeta->og_description;
        }
        if (! empty($seoMeta?->og_image)) {
            $out['ogImage'] = resolve_og_image((string) $seoMeta->og_image);
        }
        if (! empty($seoMeta?->schema_json_override)) {
            $out['seoCustomSchemaJson'] = (string) $seoMeta->schema_json_override;
        }

        return $out;
    }

    /**
     * Centralized robots helper preserving GSC query-variant hygiene while letting CMS toggle marketing params.
     */
    public function applyQueryParamRobotsPolicy(string $baseRobots, ?Request $request = null, array|\Illuminate\Support\Collection|null $siteSettings = null): string
    {
        if (! $request) {
            return $baseRobots;
        }

        $query = $request->query();
        if ($query === []) {
            return $baseRobots;
        }

        if ($siteSettings === null) {
            $raw = $this->settingsService->all();
            $siteSettings = $raw instanceof \Illuminate\Support\Collection ? $raw->all() : (is_array($raw) ? $raw : []);
        } elseif ($siteSettings instanceof \Illuminate\Support\Collection) {
            $siteSettings = $siteSettings->all();
        }

        // Only allow pure tracking params to stay indexable if CMS explicitly sets seo_index_tracking_params = '1'
        if (($siteSettings['seo_index_tracking_params'] ?? '0') === '1') {
            $trackingKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid', 'wbraid', 'gbraid', 'gad_source'];
            $nonTrackingKeys = array_diff(array_keys($query), $trackingKeys);
            if ($nonTrackingKeys === []) {
                return $baseRobots;
            }
        }

        return 'noindex, follow';
    }

    public function loadTemplate(string $contentType): ?SeoTemplate
    {
        try {
            if (! Schema::hasTable('seo_templates')) {
                return null;
            }
            $template = SeoTemplate::where('content_type', $contentType)->first();
            if (! $template) {
                $defaults = SeoTemplate::defaultDefinitions();
                if (isset($defaults[$contentType])) {
                    $template = new SeoTemplate(array_merge(['content_type' => $contentType, 'is_active' => true], $defaults[$contentType]));
                }
            }

            return $template;
        } catch (\Throwable) {
            return null;
        }
    }

    private function applyPattern(string $pattern, array $tokens): string
    {
        $rendered = str_replace(array_keys($tokens), array_values($tokens), $pattern);
        $rendered = preg_replace('/\s{2,}/', ' ', trim($rendered));
        $rendered = trim((string) $rendered, " |-\t\n\r");

        return $rendered;
    }

    private function extractTranslatedAttr(?Model $entity, array $fields, string $locale): string
    {
        if (! $entity) {
            return '';
        }

        foreach ($fields as $field) {
            if (method_exists($entity, 'getTranslation')) {
                $val = $entity->getTranslation($field, $locale, false);
                if (empty($val) && $locale !== 'en') {
                    $val = $entity->getTranslation($field, 'en', false);
                }
                if (is_string($val) && trim($val) !== '') {
                    return trim($val);
                }
            }
            $raw = $entity->getAttribute($field);
            if (is_string($raw) && trim($raw) !== '') {
                return trim($raw);
            }
        }

        return '';
    }

    private function extractCategoryName(?Model $entity, string $locale): ?string
    {
        if (! $entity) {
            return null;
        }

        if ($entity instanceof Tour || $entity instanceof BlogPost) {
            $cat = $entity->category;
            if ($cat && method_exists($cat, 'getTranslation')) {
                return trim((string) ($cat->getTranslation('name', $locale, true) ?: $cat->name));
            }

            return $cat?->name;
        }

        if ($entity instanceof WikiArticle) {
            $pillar = $entity->pillar;

            return $pillar && method_exists($pillar, 'getTranslation')
                ? trim((string) ($pillar->getTranslation('name', $locale, true) ?: $pillar->name))
                : $pillar?->name;
        }

        return null;
    }

    private function resolveFocusKeyword(?Model $entity, ?SeoMetadata $seoMeta): ?string
    {
        if (! empty($seoMeta?->focus_keyword)) {
            return trim((string) $seoMeta->focus_keyword);
        }

        if ($entity && ! empty($entity->focus_keyword)) {
            return trim((string) $entity->focus_keyword);
        }

        return null;
    }
}
