<?php

namespace App\Services\Security;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Prevents Server-Side Request Forgery (SSRF) on outbound HTTP requests
 * initiated from CMS inputs (backlink verification, webhooks, feeds).
 */
class SafeUrlValidator
{
    private const BLOCKED_HOST_SUFFIXES = [
        '.local',
        '.localhost',
        '.internal',
        '.localdomain',
        '.home',
        '.lan',
        '.arpa',
    ];

    private const BLOCKED_EXACT_HOSTS = [
        'localhost',
        'metadata',
        'metadata.google.internal',
        'metadata.goog',
        '169.254.169.254',
        '::1',
        '0.0.0.0',
    ];

    /**
     * Validate a URL before outbound HTTP dispatch.
     *
     * @throws InvalidArgumentException when scheme, host, port, or resolved IP is unsafe.
     */
    public static function assertSafeUrl(string $url, bool $resolveDns = true): string
    {
        $trimmed = trim($url);
        if ($trimmed === '' || strlen($trimmed) > 2048) {
            throw new InvalidArgumentException('URL is empty or exceeds maximum length.');
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $trimmed)) {
            throw new InvalidArgumentException('URL contains invalid control characters.');
        }

        $parts = parse_url($trimmed);
        if (! is_array($parts)) {
            throw new InvalidArgumentException('Malformed URL.');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Only HTTP and HTTPS schemes are permitted.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('Embedded credentials in URLs are not permitted.');
        }

        $host = strtolower(trim((string) ($parts['host'] ?? ''), " \t\n\r\0\x0B[]"));
        if ($host === '') {
            throw new InvalidArgumentException('URL host is missing.');
        }

        if (isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443, 8080, 8443], true)) {
            throw new InvalidArgumentException('Target port is restricted to standard HTTP/HTTPS ports.');
        }

        if (in_array($host, self::BLOCKED_EXACT_HOSTS, true)) {
            throw new InvalidArgumentException('Target host is restricted.');
        }

        foreach (self::BLOCKED_HOST_SUFFIXES as $suffix) {
            if (str_ends_with($host, $suffix)) {
                throw new InvalidArgumentException('Private or internal domain suffixes are not permitted.');
            }
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (! self::isPublicIp($host)) {
                throw new InvalidArgumentException('Target IP resides in a private, loopback, link-local, or reserved range.');
            }

            return $trimmed;
        }

        if ($resolveDns) {
            $ips = self::resolveHostIps($host);
            foreach ($ips as $ip) {
                if (! self::isPublicIp($ip)) {
                    throw new InvalidArgumentException('Target hostname resolves to a non-public IP address.');
                }
            }
        }

        return $trimmed;
    }

    /**
     * Check whether a URL is safe without throwing.
     */
    public static function isSafeUrl(string $url, bool $resolveDns = true): bool
    {
        try {
            self::assertSafeUrl($url, $resolveDns);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Check that an IPv4/IPv6 address is globally routable (excludes RFC1918, CGNAT, loopback, link-local, metadata, multicast).
     */
    public static function isPublicIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                return false;
            }
            // Block RFC 6598 CGNAT (100.64.0.0/10) and multicast/reserved upper blocks
            $cgnatStart = ip2long('100.64.0.0');
            $cgnatEnd = ip2long('100.127.255.255');
            if ($long >= $cgnatStart && $long <= $cgnatEnd) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create an Http PendingRequest with SSRF redirect protection configured.
     */
    public static function httpClient(int $timeoutSeconds = 8, int $connectTimeoutSeconds = 4): PendingRequest
    {
        return Http::connectTimeout($connectTimeoutSeconds)
            ->timeout($timeoutSeconds)
            ->withOptions([
                'allow_redirects' => [
                    'max' => 3,
                    'strict' => true,
                    'protocols' => ['http', 'https'],
                    'on_redirect' => function ($request, $response, $uri): void {
                        self::assertSafeUrl((string) $uri);
                    },
                ],
            ]);
    }

    /**
     * Resolve A and AAAA records for a hostname safely.
     *
     * @return list<string>
     */
    private static function resolveHostIps(string $host): array
    {
        $ips = [];

        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (! empty($record['ip'])) {
                        $ips[] = (string) $record['ip'];
                    } elseif (! empty($record['ipv6'])) {
                        $ips[] = (string) $record['ipv6'];
                    }
                }
            }
        }

        if ($ips === [] && function_exists('gethostbynamel')) {
            $v4 = @gethostbynamel($host);
            if (is_array($v4)) {
                $ips = $v4;
            }
        }

        return array_values(array_unique($ips));
    }
}
