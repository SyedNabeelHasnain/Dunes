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
     * Sanitize raw SVG XML string via DOMDocument, removing executable elements,
     * SMIL animation vectors, event attributes, XML external entities, and dangerous URI schemes.
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

        // Parse via DOMDocument with network & entity expansion strictly disabled
        $prevUseErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadXML($clean, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        if (! $loaded || ! $dom->documentElement || strtolower($dom->documentElement->localName) !== 'svg') {
            throw new InvalidArgumentException('Uploaded SVG could not be parsed as safe XML.');
        }

        $blockedTags = [
            'script', 'foreignobject', 'iframe', 'object', 'embed', 'applet',
            'audio', 'video', 'meta', 'link', 'base', 'style', 'use', 'image',
            'feimage', 'handler', 'listener', 'animate', 'animatemotion',
            'animatetransform', 'animatecolor', 'set',
        ];

        $toRemove = [];
        $walk = function (\DOMNode $node) use (&$walk, &$toRemove, $blockedTags): void {
            if ($node instanceof \DOMProcessingInstruction || $node instanceof \DOMComment) {
                $toRemove[] = $node;

                return;
            }

            if ($node instanceof \DOMElement) {
                $tag = strtolower($node->localName ?: $node->nodeName);
                if (in_array($tag, $blockedTags, true)) {
                    $toRemove[] = $node;

                    return;
                }

                if ($node->hasAttributes()) {
                    $attrsToRemove = [];
                    foreach ($node->attributes as $attr) {
                        $attrName = strtolower($attr->name);
                        $localAttr = strtolower($attr->localName ?: $attr->name);
                        // Decode entities and strip control/whitespace chars before scheme inspection
                        $decodedVal = html_entity_decode((string) $attr->value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $compactVal = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $decodedVal));

                        // Block all on* event handlers
                        if (str_starts_with($localAttr, 'on') || str_starts_with($attrName, 'on')) {
                            $attrsToRemove[] = $attr->name;
                            continue;
                        }

                        // Block javascript:/vbscript:/data: URI schemes in any URI/target/style attribute
                        if (in_array($localAttr, ['href', 'src', 'action', 'formaction', 'from', 'to', 'values', 'by'], true)) {
                            if (preg_match('/^(?:javascript|vbscript|data):/i', $compactVal)) {
                                $attrsToRemove[] = $attr->name;
                                continue;
                            }
                        }

                        if ($localAttr === 'style') {
                            if (preg_match('/(?:expression\s*\(|javascript:|vbscript:|data:|@import|behavior\s*:|-moz-binding)/i', $compactVal)) {
                                $attrsToRemove[] = $attr->name;
                                continue;
                            }
                        }

                        if (preg_match('/(?:javascript|vbscript):/i', $compactVal)) {
                            $attrsToRemove[] = $attr->name;
                        }
                    }

                    foreach ($attrsToRemove as $name) {
                        $node->removeAttribute($name);
                    }
                }
            }

            foreach ($node->childNodes as $child) {
                $walk($child);
            }
        };

        $walk($dom->documentElement);
        foreach ($toRemove as $node) {
            $node->parentNode?->removeChild($node);
        }

        return $dom->saveXML($dom->documentElement) ?: throw new InvalidArgumentException('Failed to serialize sanitized SVG.');
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
     * Assert that a non-SVG image/media upload does not contain PHP/HTML script polyglot payloads.
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

        $overlap = '';
        $chunkSize = 65536;
        $maxScanBytes = 2097152; // Scan up to first 2 MB in overlapping chunks
        $scanned = 0;

        try {
            while (! feof($handle) && $scanned < $maxScanBytes) {
                $chunk = (string) fread($handle, $chunkSize);
                if ($chunk === '') {
                    break;
                }
                $scanned += strlen($chunk);
                $window = strtolower($overlap . $chunk);
                if (
                    str_contains($window, '<?php')
                    || str_contains($window, '<?=')
                    || str_contains($window, '<script')
                    || str_contains($window, '<!doctype html')
                ) {
                    throw new InvalidArgumentException('Upload rejected: embedded script or HTML signature detected in media file.');
                }
                $overlap = substr($chunk, -32);
            }
        } finally {
            fclose($handle);
        }
    }
}
