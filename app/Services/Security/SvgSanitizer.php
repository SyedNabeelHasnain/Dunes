<?php

namespace App\Services\Security;

use InvalidArgumentException;

/**
 * Sanitizes uploaded SVG assets against Stored XSS / XXE payloads and verifies
 * raster uploads are not spoofed script documents (OWASP ASVS 5.0 File Upload).
 */
class SvgSanitizer
{
    /**
     * Sanitize raw SVG XML string, removing executable elements, event attributes,
     * XML external entities, and dangerous URI schemes.
     */
    public static function sanitize(string $svg): string
    {
        $clean = trim($svg);
        if ($clean === '' || ! str_contains(strtolower($clean), '<svg')) {
            throw new InvalidArgumentException('Invalid SVG document: missing <svg> root element.');
        }

        // Remove XML DOCTYPE / ENTITY declarations (XXE defense, including internal DTD subsets)
        $clean = (string) preg_replace('/<!DOCTYPE\s+[^>\[]*(?:\[[^\]]*\])?\s*>/is', '', $clean);
        $clean = (string) preg_replace('/<!ENTITY[^>]*>/is', '', $clean);

        // Remove PHP / ASP processing tags while preserving standard <?xml declarations
        $clean = (string) preg_replace('/<\?(?:php|=|\s).*?\?>/is', '', $clean);

        // Remove dangerous elements and their bodies
        $dangerousTags = ['script', 'foreignObject', 'iframe', 'object', 'embed', 'applet', 'audio', 'video', 'meta', 'link', 'base'];
        foreach ($dangerousTags as $tag) {
            $clean = (string) preg_replace('/<'.$tag.'\b[^>]*>.*?<\/'.$tag.'>/is', '', $clean);
            $clean = (string) preg_replace('/<'.$tag.'\b[^>]*\/?>/is', '', $clean);
        }

        // Strip inline JavaScript event handler attributes (onload=, onclick=, onerror=, etc.)
        $clean = (string) preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/is', '', $clean);

        // Strip javascript:, vbscript:, and data:text/html URIs in href / xlink:href / src
        $clean = (string) preg_replace(
            '/\s+(?:xlink:)?(?:href|src|action|formaction)\s*=\s*(["\'])\s*(?:javascript|vbscript|data\s*:\s*text\/html)[^"\']*\1/is',
            '',
            $clean
        );

        // Strip dangerous CSS expressions or url(javascript:...) inside style attributes or <style>
        $clean = (string) preg_replace('/expression\s*\([^)]*\)/is', '', $clean);
        $clean = (string) preg_replace('/url\s*\(\s*["\']?\s*(?:javascript|vbscript|data\s*:\s*text\/html)[^)]*\)/is', 'none', $clean);

        // Validate resulting markup via DOMDocument with network & entity expansion disabled
        $prevUseErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadXML($clean, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        if (! $loaded || ! $dom->documentElement || strtolower($dom->documentElement->localName) !== 'svg') {
            throw new InvalidArgumentException('Uploaded SVG could not be parsed as safe XML.');
        }

        return $dom->saveXML($dom->documentElement) ?: $clean;
    }

    /**
     * Sanitize an SVG file on disk in-place.
     */
    public static function sanitizeFile(string $filePath): void
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException('SVG file is unreadable.');
        }

        $raw = (string) file_get_contents($filePath);
        $safe = self::sanitize($raw);
        file_put_contents($filePath, $safe, LOCK_EX);
    }

    /**
     * Assert that a non-SVG image/media upload does not start with PHP/HTML script payloads.
     */
    public static function assertNoScriptPolyglot(string $filePath, string $ext): void
    {
        $ext = strtolower($ext);
        if ($ext === 'svg') {
            return;
        }

        $handle = @fopen($filePath, 'rb');
        if (! $handle) {
            throw new InvalidArgumentException('Upload rejected: uploaded media file could not be inspected.');
        }
        $head = (string) fread($handle, 2048);
        fclose($handle);

        $lowerHead = strtolower($head);
        if (str_contains($lowerHead, '<?php') || str_contains($lowerHead, '<script') || str_contains($lowerHead, '<!doctype html')) {
            throw new InvalidArgumentException('Upload rejected: embedded script or HTML signature detected in media file.');
        }
    }
}
