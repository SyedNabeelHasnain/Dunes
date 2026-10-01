<?php

namespace App\Services\Translation\Contracts;

interface TranslationDriverInterface
{
    /**
     * Translate a string or key-value map of strings from source locale to target locale.
     *
     * @param string|array<string, string> $text
     * @param string $targetLocale
     * @param string $sourceLocale
     * @param bool $isHtml
     * @return string|array<string, string>
     */
    public function translate(string|array $text, string $targetLocale, string $sourceLocale = 'en', bool $isHtml = false): string|array;

    /**
     * Test API connection and return status.
     *
     * @return array{success: bool, message: string, details?: mixed}
     */
    public function testConnection(): array;

    /**
     * Get human-readable provider name.
     */
    public function getName(): string;
}
