<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Backlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'source_url',
        'target_url',
        'referring_domain',
        'source_domain',
        'category',
        'platform',
        'anchor_text',
        'link_type',
        'rel_type',
        'domain_authority_est',
        'domain_authority',
        'status',
        'http_status_code',
        'http_status',
        'last_checked_at',
        'last_crawled_at',
        'verification_notes',
        'utm_source',
        'referral_visits_count',
        'referral_visits',
        'bookings_count',
        'revenue_generated_aed',
        'attributed_revenue_aed',
        'partner_name',
        'partner_email',
        'campaign',
        'cost_aed',
        'notes',
    ];

    protected $casts = [
        'domain_authority_est' => 'integer',
        'http_status_code' => 'integer',
        'referral_visits_count' => 'integer',
        'bookings_count' => 'integer',
        'revenue_generated_aed' => 'float',
        'cost_aed' => 'float',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Auto-populate missing title, domain, and UTM source on creation/saving.
     */
    protected static function booted(): void
    {
        static::saving(function (Backlink $backlink) {
            if (empty($backlink->referring_domain) && !empty($backlink->source_url)) {
                $backlink->referring_domain = self::normalizeDomain($backlink->source_url);
            }

            if (empty($backlink->title)) {
                $backlink->title = $backlink->anchor_text ?: ($backlink->referring_domain ?: 'Tracked Backlink');
            }

            if (empty($backlink->utm_source) && !empty($backlink->referring_domain)) {
                $backlink->utm_source = self::generateUtmSource($backlink->referring_domain);
            }
        });
    }

    /**
     * Relationship to bookings attributed to this backlink.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'backlink_id');
    }

    // ── Seamless Aliases for Convenience & Code Readability ──────────────────

    protected function sourceDomain(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->referring_domain,
            set: fn ($value) => ['referring_domain' => $value]
        );
    }

    protected function relType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->link_type,
            set: fn ($value) => ['link_type' => $value]
        );
    }

    protected function platform(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->category,
            set: fn ($value) => ['category' => $value]
        );
    }

    protected function domainAuthority(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->domain_authority_est,
            set: fn ($value) => ['domain_authority_est' => (int) $value]
        );
    }

    protected function referralVisits(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->referral_visits_count,
            set: fn ($value) => ['referral_visits_count' => (int) $value]
        );
    }

    protected function attributedRevenueAed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->revenue_generated_aed,
            set: fn ($value) => ['revenue_generated_aed' => (float) $value]
        );
    }

    protected function lastCrawledAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_checked_at,
            set: fn ($value) => ['last_checked_at' => $value]
        );
    }

    protected function httpStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->http_status_code,
            set: fn ($value) => ['http_status_code' => (int) $value]
        );
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBroken($query)
    {
        return $query->whereIn('status', ['broken_404', 'broken', 'lost', 'removed']);
    }

    public function scopeDofollow($query)
    {
        return $query->where('link_type', 'dofollow');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Domain & URL Utilities ────────────────────────────────────────────────

    /**
     * Normalize domain string from a full URL.
     */
    public static function normalizeDomain(string $url): string
    {
        $parsed = parse_url($url, PHP_URL_HOST);
        if ($parsed) {
            $host = strtolower($parsed);
            return preg_replace('/^www\./', '', $host);
        }

        $cleaned = preg_replace('#^https?://#i', '', $url);
        $cleaned = explode('/', $cleaned)[0];
        $cleaned = explode(':', $cleaned)[0];
        return strtolower(preg_replace('/^www\./', '', $cleaned));
    }

    /**
     * Generate standard slug-safe UTM source from domain name.
     */
    public static function generateUtmSource(string $domain): string
    {
        $normalized = self::normalizeDomain($domain);
        $clean = preg_replace('/[^a-z0-9]+/i', '_', strtolower($normalized));
        return trim($clean, '_');
    }

    /**
     * Build full URL with standardized UTM parameters for partner placement.
     */
    public function buildTrackingUrl(array|string|null $options = null): string
    {
        $campaign = is_array($options) ? ($options['campaign'] ?? null) : $options;
        $campaign = $campaign ?: ($this->campaign ?: ('backlink_' . ($this->category ?: 'referral')));
        $utmSource = $this->utm_source ?: self::generateUtmSource($this->referring_domain ?: $this->source_url);

        $base = $this->target_url ?: url('/');
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base . $separator . http_build_query([
            'utm_source' => $utmSource,
            'utm_medium' => 'referral',
            'utm_campaign' => $campaign,
        ]);
    }

    /**
     * Verify the live health of this backlink by crawling the source URL.
     */
    public function verifyHealth(): array
    {
        $sourceUrl = trim((string) $this->source_url);
        if (empty($sourceUrl) || ! filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            $this->update([
                'status' => 'broken',
                'http_status_code' => 400,
                'last_checked_at' => now(),
                'verification_notes' => 'Invalid or missing source URL.',
            ]);

            return [
                'success' => false,
                'status' => 'broken',
                'message' => 'Invalid or missing source URL.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(10)->get($sourceUrl);

            $statusCode = $response->status();
            $this->http_status_code = $statusCode;
            $this->last_checked_at = now();

            if ($statusCode >= 400) {
                $status = 'broken';
                $notes = "Source URL returned HTTP {$statusCode}.";
                $this->update([
                    'status' => $status,
                    'http_status_code' => $statusCode,
                    'last_checked_at' => now(),
                    'verification_notes' => $notes,
                ]);

                return ['success' => false, 'status' => $status, 'message' => $notes];
            }

            $html = (string) $response->body();
            if (empty($html)) {
                $this->update([
                    'status' => 'lost',
                    'last_checked_at' => now(),
                    'verification_notes' => 'Empty response body received from source URL.',
                ]);

                return ['success' => false, 'status' => 'lost', 'message' => 'Empty response body.'];
            }

            // Parse HTML and look for link to Dunes Discovery Tourism
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $links = $xpath->query('//a[@href]');

            $targetHost = parse_url(url('/'), PHP_URL_HOST);
            $targetPath = parse_url($this->target_url ?: url('/'), PHP_URL_PATH);

            $foundLink = null;
            $foundRel = '';
            $foundAnchor = '';

            foreach ($links as $link) {
                $href = (string) $link->getAttribute('href');
                if (empty($href)) {
                    continue;
                }

                $isMatch = false;
                if ($targetHost && str_contains(strtolower($href), strtolower($targetHost))) {
                    $isMatch = true;
                } elseif (str_contains(strtolower($href), 'dunesdiscoverytourism.com')) {
                    $isMatch = true;
                } elseif ($targetPath && $targetPath !== '/' && str_contains(strtolower($href), strtolower($targetPath))) {
                    $isMatch = true;
                }

                if ($isMatch) {
                    $foundLink = $href;
                    $foundRel = strtolower((string) $link->getAttribute('rel'));
                    $foundAnchor = trim($link->textContent);
                    break;
                }
            }

            if ($foundLink) {
                $linkType = 'dofollow';
                if (str_contains($foundRel, 'nofollow')) {
                    $linkType = 'nofollow';
                } elseif (str_contains($foundRel, 'sponsored')) {
                    $linkType = 'sponsored';
                } elseif (str_contains($foundRel, 'ugc')) {
                    $linkType = 'ugc';
                }

                $notes = "Verified live {$linkType} link pointing to {$foundLink}. Anchor: '{$foundAnchor}'.";
                $this->update([
                    'status' => 'active',
                    'link_type' => $linkType,
                    'anchor_text' => $foundAnchor ?: $this->anchor_text,
                    'http_status_code' => $statusCode,
                    'last_checked_at' => now(),
                    'verification_notes' => $notes,
                ]);

                return [
                    'success' => true,
                    'status' => 'active',
                    'rel_type' => $linkType,
                    'anchor_text' => $foundAnchor,
                    'message' => $notes,
                ];
            }

            $notes = 'Source URL crawled successfully (HTTP 200), but no active link pointing to Dunes Discovery Tourism was found.';
            $this->update([
                'status' => 'lost',
                'http_status_code' => $statusCode,
                'last_checked_at' => now(),
                'verification_notes' => $notes,
            ]);

            return [
                'success' => false,
                'status' => 'lost',
                'message' => $notes,
            ];
        } catch (\Throwable $e) {
            $notes = 'Crawl failed with exception: ' . $e->getMessage();
            $this->update([
                'status' => 'broken',
                'http_status_code' => 500,
                'last_checked_at' => now(),
                'verification_notes' => $notes,
            ]);

            return [
                'success' => false,
                'status' => 'broken',
                'message' => $notes,
            ];
        }
    }
}
