<?php

namespace App\Services\Translation\Drivers;

use App\Services\Translation\Contracts\TranslationDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepLTranslationDriver implements TranslationDriverInterface
{
    protected string $authKey;

    protected string $endpointType; // 'free' or 'pro'

    public function __construct(string $authKey = '', string $endpointType = 'free')
    {
        $this->authKey = trim($authKey);
        $this->endpointType = strtolower(trim($endpointType));

        // Auto-detect endpoint type if key ends with ':fx'
        if (str_ends_with($this->authKey, ':fx')) {
            $this->endpointType = 'free';
        }
    }

    public function getName(): string
    {
        return 'DeepL API ('.ucfirst($this->endpointType).')';
    }

    protected function getBaseUrl(): string
    {
        return $this->endpointType === 'pro'
            ? 'https://api.deepl.com/v2'
            : 'https://api-free.deepl.com/v2';
    }

    /**
     * Map locale code to DeepL target language code.
     */
    protected function mapLocale(string $locale): string
    {
        $code = strtoupper(trim($locale));

        return match ($code) {
            'EN' => 'EN-US',
            default => $code,
        };
    }

    public function translate(string|array $text, string $targetLocale, string $sourceLocale = 'en', bool $isHtml = false): string|array
    {
        if (empty($this->authKey)) {
            throw new \RuntimeException('DeepL API Key is not configured in Settings.');
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

        // Empty check
        if (empty(array_filter($values, fn ($v) => trim($v) !== ''))) {
            return $text;
        }

        $translatedTexts = [];
        $targetLang = $this->mapLocale($targetLocale);
        $sourceLang = strtoupper(trim($sourceLocale));

        foreach (array_chunk($values, 45) as $chunk) {
            $payload = [
                'text' => $chunk,
                'target_lang' => $targetLang,
                'source_lang' => $sourceLang,
            ];

            if ($isHtml) {
                $payload['tag_handling'] = 'html';
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'DeepL-Auth-Key '.$this->authKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($this->getBaseUrl().'/translate', $payload);
            } catch (\Throwable $e) {
                Log::error('DeepL Translation transport failed: '.$e->getMessage());
                throw new \RuntimeException('DeepL Translation connection error: '.$e->getMessage(), 0, $e);
            }

            if (! $response->successful()) {
                $errorMsg = $response->json('message') ?? $response->body();
                Log::error('DeepL Translation failed: '.$errorMsg, ['status' => $response->status()]);
                throw new \RuntimeException('DeepL Translation API Error (HTTP '.$response->status().'): '.$errorMsg);
            }

            $translatedItems = $response->json('translations') ?? [];
            foreach ($translatedItems as $item) {
                $translatedTexts[] = $item['text'] ?? '';
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
        if (empty($this->authKey)) {
            return ['success' => false, 'message' => 'DeepL Auth Key is empty.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'DeepL-Auth-Key '.$this->authKey,
            ])->timeout(10)->get($this->getBaseUrl().'/usage');

            if ($response->successful()) {
                $usage = $response->json();
                $charCount = $usage['character_count'] ?? 0;
                $charLimit = $usage['character_limit'] ?? 0;

                return [
                    'success' => true,
                    'message' => 'Connection verified successfully! Character usage: '.number_format($charCount).' / '.number_format($charLimit),
                    'details' => $usage,
                ];
            }

            return [
                'success' => false,
                'message' => 'DeepL API returned HTTP '.$response->status().': '.$response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to DeepL: '.$e->getMessage(),
            ];
        }
    }
}
