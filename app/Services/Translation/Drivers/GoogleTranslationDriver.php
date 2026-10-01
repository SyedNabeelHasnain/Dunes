<?php

namespace App\Services\Translation\Drivers;

use App\Services\Translation\Contracts\TranslationDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleTranslationDriver implements TranslationDriverInterface
{
    protected string $apiKey;

    public function __construct(string $apiKey = '')
    {
        $this->apiKey = trim($apiKey);
    }

    public function getName(): string
    {
        return 'Google Cloud Translation';
    }

    public function translate(string|array $text, string $targetLocale, string $sourceLocale = 'en', bool $isHtml = false): string|array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Google Cloud Translation API Key is not configured in Settings.');
        }

        $isSingle = is_string($text);
        $keys = [];
        $values = [];

        if ($isSingle) {
            $values[] = (string) $text;
        } else {
            foreach ($text as $k => $v) {
                $keys[] = $k;
                $values[] = (string) $v;
            }
        }

        if (empty(array_filter($values, fn ($v) => trim($v) !== ''))) {
            return $text;
        }

        $payload = [
            'q' => $values,
            'target' => strtolower(trim($targetLocale)),
            'source' => strtolower(trim($sourceLocale)),
            'format' => $isHtml ? 'html' : 'text',
        ];

        $response = Http::timeout(30)->post(
            'https://translation.googleapis.com/language/translate/v2?key='.$this->apiKey,
            $payload
        );

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            Log::error('Google Translation failed: '.$errorMsg, ['status' => $response->status()]);
            throw new \RuntimeException('Google Translation API Error (HTTP '.$response->status().'): '.$errorMsg);
        }

        $items = $response->json('data.translations') ?? [];
        $translatedTexts = array_map(fn ($item) => html_entity_decode($item['translatedText'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'), $items);

        if ($isSingle) {
            return $translatedTexts[0] ?? $text;
        }

        $result = [];
        foreach ($keys as $idx => $key) {
            $result[$key] = $translatedTexts[$idx] ?? ($values[$idx] ?? '');
        }

        return $result;
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'Google Cloud Translation API Key is empty.'];
        }

        try {
            $response = Http::timeout(10)->get(
                'https://translation.googleapis.com/language/translate/v2/languages?key='.$this->apiKey.'&target=en'
            );

            if ($response->successful()) {
                $languages = $response->json('data.languages') ?? [];

                return [
                    'success' => true,
                    'message' => 'Connection verified successfully! Supported language count: '.count($languages),
                    'details' => ['language_count' => count($languages)],
                ];
            }

            return [
                'success' => false,
                'message' => 'Google Translation API returned HTTP '.$response->status().': '.($response->json('error.message') ?? $response->body()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to Google Translation: '.$e->getMessage(),
            ];
        }
    }
}
