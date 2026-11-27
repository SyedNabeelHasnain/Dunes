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

        $translatedTexts = [];
        $encodedKey = urlencode($this->apiKey);
        $target = strtolower(trim($targetLocale));
        $source = strtolower(trim($sourceLocale));

        foreach (array_chunk($values, 100) as $chunk) {
            $payload = [
                'q' => $chunk,
                'target' => $target,
                'source' => $source,
                'format' => $isHtml ? 'html' : 'text',
            ];

            try {
                $response = Http::timeout(30)->post(
                    'https://translation.googleapis.com/language/translate/v2?key='.$encodedKey,
                    $payload
                );
            } catch (\Throwable $e) {
                $safeMsg = $this->scrubKey($e->getMessage());
                Log::error('Google Translation transport failed: '.$safeMsg);
                throw new \RuntimeException('Google Translation connection error: '.$safeMsg, 0, $e);
            }

            if (! $response->successful()) {
                $errorMsg = $this->scrubKey((string) ($response->json('error.message') ?? $response->body()));
                Log::error('Google Translation failed: '.$errorMsg, ['status' => $response->status()]);
                throw new \RuntimeException('Google Translation API Error (HTTP '.$response->status().'): '.$errorMsg);
            }

            $items = $response->json('data.translations') ?? [];
            foreach ($items as $item) {
                $rawText = (string) ($item['translatedText'] ?? '');
                $translatedTexts[] = $isHtml
                    ? $rawText
                    : html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

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
                'https://translation.googleapis.com/language/translate/v2/languages?key='.urlencode($this->apiKey).'&target=en'
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
                'message' => 'Google Translation API returned HTTP '.$response->status().': '.$this->scrubKey((string) ($response->json('error.message') ?? $response->body())),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to Google Translation: '.$this->scrubKey($e->getMessage()),
            ];
        }
    }

    protected function scrubKey(string $message): string
    {
        $scrubbed = (string) preg_replace('/(key=)[^&\s\'"]+/i', '$1[REDACTED]', $message);
        if ($this->apiKey !== '') {
            $scrubbed = str_replace([$this->apiKey, urlencode($this->apiKey)], '[REDACTED]', $scrubbed);
        }

        return $scrubbed;
    }
}
