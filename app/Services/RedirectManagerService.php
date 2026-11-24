<?php

namespace App\Services;

use App\Models\RedirectRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RedirectManagerService
{
    protected const CACHE_KEY = 'cms_active_redirect_rules_map';
    protected const CACHE_TTL = 86400; // 24 hours

    /**
     * Resolves incoming URL path against active dynamic redirects.
     * Returns array ['target' => string, 'status' => int] or null.
     */
    public function resolve(string $rawPath): ?array
    {
        $normalized = RedirectRule::normalizePath($rawPath);
        $map = $this->getActiveMap();

        if (!isset($map[$normalized])) {
            return null;
        }

        $ruleData = $map[$normalized];
        $target = (string) ($ruleData['target_path'] ?? '');
        $status = (int) $ruleData['status_code'];
        $hops = 0;

        if ($status !== 410 && $target !== '') {
            // Follow chains up to 5 hops to yield single 301/302/307/308 response and avoid redirect chains
            $visited = [$normalized => true];
            $maxHops = 5;

            while ($hops < $maxHops) {
                $nextNormalized = RedirectRule::normalizePath($target);
                if (isset($map[$nextNormalized]) && (int) $map[$nextNormalized]['status_code'] !== 410) {
                    if (isset($visited[$nextNormalized])) {
                        // Loop detected in chain; break out to prevent infinite redirect
                        break;
                    }
                    $visited[$nextNormalized] = true;
                    $target = (string) $map[$nextNormalized]['target_path'];
                    $hops++;
                } else {
                    break;
                }
            }
        }

        // Asynchronously or safely update hit count
        try {
            DB::table('redirect_rules')
                ->where('id', $ruleData['id'])
                ->increment('hit_count', 1, ['last_hit_at' => now()]);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return [
            'target' => $target,
            'status' => $status,
            'hops' => $hops,
        ];
    }

    /**
     * Detects redirect loops or chained hops before saving a rule.
     */
    public function detectLoopOrChain(string $sourcePath, ?string $targetPath, ?int $ignoreId = null, int $statusCode = 301): array
    {
        if ($statusCode === 410) {
            return [
                'is_loop' => false,
                'is_chain' => false,
                'final_target' => '',
                'hops' => [RedirectRule::normalizePath($sourcePath)],
                'message' => 'HTTP 410 Gone terminates directly with no target redirect.',
            ];
        }

        $source = RedirectRule::normalizePath($sourcePath);
        $target = RedirectRule::normalizePath((string) $targetPath);

        if ($source === $target) {
            return [
                'is_loop' => true,
                'is_chain' => false,
                'final_target' => $target,
                'hops' => [$source, $target],
                'message' => 'Source URL cannot redirect directly to itself (Self-referencing loop).',
            ];
        }

        $query = RedirectRule::where('is_active', true);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        $existingRules = $query->get()->keyBy(fn ($r) => RedirectRule::normalizePath($r->source_path));

        $visited = [$source => true];
        $current = $target;
        $chain = [$source, $target];
        $isLoop = false;
        $isChain = false;
        $finalTarget = $target;

        for ($i = 0; $i < 10; $i++) {
            if ($current === $source || isset($visited[$current])) {
                $isLoop = true;
                break;
            }

            if (isset($existingRules[$current])) {
                $isChain = true;
                $visited[$current] = true;
                $next = RedirectRule::normalizePath($existingRules[$current]->target_path);
                $chain[] = $next;
                $finalTarget = $next;
                $current = $next;
            } else {
                break;
            }
        }

        return [
            'is_loop' => $isLoop,
            'is_chain' => $isChain,
            'final_target' => $finalTarget,
            'hops' => $chain,
            'message' => $isLoop
                ? 'Redirect loop detected: ' . implode(' -> ', $chain)
                : ($isChain ? 'Redirect chain detected: ' . implode(' -> ', $chain) . '. We recommend redirecting directly to ' . $finalTarget : 'Clean direct redirect.'),
        ];
    }

    /**
     * Saves or updates a redirect rule.
     */
    public function saveRule(array $data, ?int $id = null): RedirectRule
    {
        $statusCode = in_array((int) ($data['status_code'] ?? 301), [301, 302, 307, 308, 410], true) ? (int) $data['status_code'] : 301;
        $source = RedirectRule::normalizePath((string) $data['source_path']);
        $target = $statusCode === 410 ? '' : trim((string) ($data['target_path'] ?? ''));

        // If relative, normalize; if full URL (e.g. external), keep trimmed
        if ($target !== '' && ! str_starts_with($target, 'http://') && ! str_starts_with($target, 'https://')) {
            $target = RedirectRule::normalizePath($target);
        }

        $rule = $id ? RedirectRule::findOrFail($id) : new RedirectRule();
        $rule->source_path = $source;
        $rule->target_path = $target;
        $rule->status_code = $statusCode;
        $rule->notes = $data['notes'] ?? null;
        $rule->is_active = isset($data['is_active']) ? (bool) $data['is_active'] : true;
        if (isset($data['source_type'])) {
            $rule->source_type = (string) $data['source_type'];
        }
        if (isset($data['created_by'])) {
            $rule->created_by = $data['created_by'];
        }
        $rule->save();

        $this->flushCache();

        return $rule;
    }

    /**
     * Deletes a redirect rule.
     */
    public function deleteRule(int $id): bool
    {
        $rule = RedirectRule::findOrFail($id);
        $res = $rule->delete();
        $this->flushCache();

        return (bool) $res;
    }

    /**
     * Returns cached key-value mapping of active redirects.
     */
    public function getActiveMap(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return RedirectRule::where('is_active', true)
                    ->get()
                    ->keyBy(fn ($r) => RedirectRule::normalizePath($r->source_path))
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'target_path' => $r->target_path,
                        'status_code' => $r->status_code,
                    ])
                    ->all();
            });
        } catch (\Throwable) {
            return [];
        }
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        if (class_exists(\App\Http\Controllers\SitemapController::class) && method_exists(\App\Http\Controllers\SitemapController::class, 'flushAllSitemapCaches')) {
            \App\Http\Controllers\SitemapController::flushAllSitemapCaches();
        }
    }

    public function clearCache(): void
    {
        $this->flushCache();
    }
}
