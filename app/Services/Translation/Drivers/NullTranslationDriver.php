<?php

namespace App\Services\Translation\Drivers;

use App\Services\Translation\Contracts\TranslationDriverInterface;

class NullTranslationDriver implements TranslationDriverInterface
{
    public function getName(): string
    {
        return 'None (Manual Translation Only)';
    }

    public function translate(string|array $text, string $targetLocale, string $sourceLocale = 'en', bool $isHtml = false): string|array
    {
        throw new \RuntimeException('No active Translation Service is configured. Please enable DeepL or Google in Settings > Translation Services.');
    }

    public function testConnection(): array
    {
        return [
            'success' => false,
            'message' => 'No translation service is currently active. Select DeepL or Google Cloud Translation to enable auto-translations.',
        ];
    }
}
