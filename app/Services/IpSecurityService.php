<?php

namespace App\Services;

use App\Models\IpRule;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpSecurityService
{
    /**
     * Cache key for cached security rules.
     */
    public const CACHE_KEY = IpRule::CACHE_KEY;

    /**
     * Retrieve all active rules from fast in-memory cache.
     */
    public function getCachedRules(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                $rules = IpRule::where('status', 'active')->get();

                $blockedExact = [];
                $blockedCidrs = [];
                $ignoredExact = [];
                $ignoredCidrs = [];

                foreach ($rules as $rule) {
                    $ip = trim((string) $rule->ip_address);
                    if (empty($ip)) {
                        continue;
                    }

                    if ($rule->ip_mask !== null && $rule->ip_mask > 0) {
                        $cidr = ['ip' => $ip, 'mask' => (int) $rule->ip_mask, 'rule_id' => $rule->id];
                        if ($rule->mode === 'block') {
                            $blockedCidrs[] = $cidr;
                        } elseif ($rule->mode === 'ignore_analytics') {
                            $ignoredCidrs[] = $cidr;
                        }
                    } else {
                        if ($rule->mode === 'block') {
                            $blockedExact[$ip] = $rule->id;
                        } elseif ($rule->mode === 'ignore_analytics') {
                            $ignoredExact[$ip] = $rule->id;
                        }
                    }
                }

                return [
                    'blocked_exact' => $blockedExact,
                    'blocked_cidrs' => $blockedCidrs,
                    'ignored_exact' => $ignoredExact,
                    'ignored_cidrs' => $ignoredCidrs,
                ];
            } catch (\Throwable $e) {
                Log::warning('Failed loading IP security rules: ' . $e->getMessage());

                return [
                    'blocked_exact' => [],
                    'blocked_cidrs' => [],
                    'ignored_exact' => [],
                    'ignored_cidrs' => [],
                ];
            }
        });
    }

    /**
     * Check if a given client IP is blocked by any active firewall rule.
     */
    public function isIpBlocked(string $ip): bool
    {
        $ip = trim($ip);
        if (empty($ip) || in_array(strtolower($ip), ['0.0.0.0', '127.0.0.1', '::1', 'not available'], true)) {
            return false;
        }

        $rules = $this->getCachedRules();

        // 1. Exact match
        if (isset($rules['blocked_exact'][$ip])) {
            return true;
        }

        // 2. CIDR range match
        foreach ($rules['blocked_cidrs'] as $cidr) {
            if ($this->ipMatchesCidr($ip, $cidr['ip'], $cidr['mask'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a given client IP should be excluded from analytics and telemetry tracking.
     */
    public function isIpIgnoredFromAnalytics(string $ip): bool
    {
        $ip = trim($ip);
        if (empty($ip)) {
            return false;
        }

        $rules = $this->getCachedRules();

        // 1. Exact match
        if (isset($rules['ignored_exact'][$ip])) {
            return true;
        }

        // 2. CIDR range match
        foreach ($rules['ignored_cidrs'] as $cidr) {
            if ($this->ipMatchesCidr($ip, $cidr['ip'], $cidr['mask'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a list of all active ignored IPs for analytics SQL query exclusion.
     */
    public function getIgnoredIpsList(): array
    {
        $rules = $this->getCachedRules();

        return array_keys($rules['ignored_exact'] ?? []);
    }

    /**
     * Record a blocked access attempt (increment hit counter).
     */
    public function recordBlockedHit(string $ip): void
    {
        try {
            $rules = $this->getCachedRules();
            $ruleId = $rules['blocked_exact'][$ip] ?? null;

            if (! $ruleId) {
                foreach ($rules['blocked_cidrs'] as $cidr) {
                    if ($this->ipMatchesCidr($ip, $cidr['ip'], $cidr['mask'])) {
                        $ruleId = $cidr['rule_id'];
                        break;
                    }
                }
            }

            if ($ruleId) {
                IpRule::where('id', $ruleId)->update([
                    'blocked_attempts_count' => DB::raw('blocked_attempts_count + 1'),
                    'last_activity_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Silently log without blocking error response
            Log::warning("Failed to record blocked IP hit for {$ip}: " . $e->getMessage());
        }
    }

    /**
     * Purge historical telemetry / request logs for an IP address.
     */
    public function purgeHistoricalLogsForIp(string $ip): int
    {
        $ip = trim($ip);
        if (empty($ip)) {
            return 0;
        }

        return RequestLog::where('client_ip', $ip)->delete();
    }

    /**
     * Flush rules cache.
     */
    public function clearRulesCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Match IP against CIDR subnet mask.
     */
    public function ipMatchesCidr(string $ip, string $rangeIp, int $mask): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($rangeIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($mask <= 0 || $mask > 32) {
                return $ip === $rangeIp;
            }

            $ipLong = ip2long($ip);
            $rangeLong = ip2long($rangeIp);
            $netmask = ~((1 << (32 - $mask)) - 1);

            return ($ipLong & $netmask) === ($rangeLong & $netmask);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($rangeIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($mask <= 0 || $mask > 128) {
                return $ip === $rangeIp;
            }

            $ipBin = inet_pton($ip);
            $rangeBin = inet_pton($rangeIp);
            if ($ipBin === false || $rangeBin === false) {
                return false;
            }

            $bytes = (int) ($mask / 8);
            $bits = $mask % 8;

            if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($rangeBin, 0, $bytes)) {
                return false;
            }

            if ($bits > 0) {
                $ipChar = ord($ipBin[$bytes]);
                $rangeChar = ord($rangeBin[$bytes]);
                $byteMask = ~((1 << (8 - $bits)) - 1);

                return ($ipChar & $byteMask) === ($rangeChar & $byteMask);
            }

            return true;
        }

        return false;
    }
}
