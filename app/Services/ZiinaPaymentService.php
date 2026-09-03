<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZiinaPaymentService
{
    /**
     * Check if Ziina is active.
     */
    public function isActive(): bool
    {
        $setting = Setting::where('setting_key', 'ziina_active')->first();
        return $setting && $setting->setting_value === '1';
    }

    /**
     * Get the advance payment percentage.
     */
    public function getAdvancePercent(): int
    {
        $setting = Setting::where('setting_key', 'ziina_advance_percent')->first();
        return $setting ? (int)$setting->setting_value : 10;
    }

    /**
     * Check if test mode is enabled.
     */
    public function isTestMode(): bool
    {
        $setting = Setting::where('setting_key', 'ziina_test_mode')->first();
        return $setting && $setting->setting_value === '1';
    }

    /**
     * Get the Ziina Access Token.
     */
    protected function getToken(): string
    {
        $setting = Setting::where('setting_key', 'ziina_access_token')->first();
        return $setting ? trim($setting->setting_value) : '';
    }

    /**
     * Create a payment intent in Ziina.
     */
    public function createPaymentIntent(float $amount, string $currency, string $successUrl, string $cancelUrl, string $message = ''): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            return ['error' => 'Ziina access token missing'];
        }

        // Amount must be in fils (1 AED = 100 fils)
        $amountFils = (int)round($amount * 100);

        $payload = [
            'amount' => $amountFils,
            'currency_code' => strtoupper($currency),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        // Clean & truncate message for Ziina API constraints (max 50 chars, alphanumeric/spaces/hyphen)
        if (!empty($message)) {
            $cleanMessage = trim(preg_replace('/[^a-zA-Z0-9 \-_.]/', ' ', $message));
            $cleanMessage = preg_replace('/\s+/', ' ', $cleanMessage);
            if (mb_strlen($cleanMessage) > 50) {
                $cleanMessage = mb_substr($cleanMessage, 0, 50);
            }
            if (!empty($cleanMessage)) {
                $payload['message'] = $cleanMessage;
            }
        }

        if ($this->isTestMode()) {
            $payload['test'] = true;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(20)
                ->post('https://api-v2.ziina.com/api/payment_intent', $payload);

            if ($response->failed()) {
                Log::error('Ziina Create Payment Intent API Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);

                $data = $response->json();
                $errorMsg = 'Payment gateway error. Please try again.';
                if (isset($data['message']) && is_string($data['message'])) {
                    $errorMsg = $data['message'];
                } elseif (isset($data['error']) && is_string($data['error'])) {
                    $errorMsg = $data['error'];
                } elseif (isset($data['errors']) && is_array($data['errors'])) {
                    $errorMsg = implode(', ', array_map(fn($e) => is_array($e) ? implode(' ', $e) : $e, $data['errors']));
                }

                return [
                    'error' => $errorMsg,
                    'details' => $data
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Ziina Create Payment Intent Exception', [
                'message' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['error' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch a payment intent status from Ziina.
     */
    public function fetchPaymentIntent(string $intentId): array
    {
        $token = $this->getToken();
        if (empty($token)) {
            return ['error' => 'Ziina access token missing'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(20)
                ->get("https://api-v2.ziina.com/api/payment_intent/{$intentId}");

            if ($response->failed()) {
                Log::error('Ziina Fetch Payment Intent API Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'intent_id' => $intentId
                ]);

                $data = $response->json();
                return [
                    'error' => $data['message'] ?? 'Ziina error',
                    'details' => $data
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Ziina Fetch Payment Intent Exception', [
                'message' => $e->getMessage(),
                'intent_id' => $intentId
            ]);

            return ['error' => 'Connection failed: ' . $e->getMessage()];
        }
    }
}
