<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZiinaPaymentService
{
    /**
     * Check if Ziina is active.
     */
    public function isActive(): bool
    {
        return app(SettingsService::class)->get('ziina_active') === '1';
    }

    /**
     * Get the advance payment percentage.
     */
    public function getAdvancePercent(): int
    {
        $percent = app(SettingsService::class)->get('deposit_percentage')
            ?? app(SettingsService::class)->get('ziina_advance_percent', '25');

        $parsed = is_numeric($percent) ? (int) $percent : 25;

        return max(1, min(100, $parsed));
    }

    /**
     * Check if test mode is enabled.
     */
    public function isTestMode(): bool
    {
        return app(SettingsService::class)->get('ziina_test_mode') === '1';
    }

    /**
     * Get the Ziina Access Token.
     */
    protected function getToken(): string
    {
        return trim(app(SettingsService::class)->get('ziina_access_token', ''));
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
        $amountFils = (int) round($amount * 100);

        $payload = [
            'amount' => $amountFils,
            'currency_code' => strtoupper($currency),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        // Clean & truncate message for Ziina API constraints (max 50 chars, alphanumeric/spaces/hyphen)
        if (! empty($message)) {
            $cleanMessage = trim(preg_replace('/[^a-zA-Z0-9 \-_.]/', ' ', $message));
            $cleanMessage = preg_replace('/\s+/', ' ', $cleanMessage);
            if (mb_strlen($cleanMessage) > 50) {
                $cleanMessage = mb_substr($cleanMessage, 0, 50);
            }
            if (! empty($cleanMessage)) {
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
                    'payload' => $payload,
                ]);

                $data = $response->json();
                $errorMsg = 'Payment gateway error. Please try again.';
                if (isset($data['message']) && is_string($data['message'])) {
                    $errorMsg = $data['message'];
                } elseif (isset($data['error']) && is_string($data['error'])) {
                    $errorMsg = $data['error'];
                } elseif (isset($data['errors']) && is_array($data['errors'])) {
                    $errorMsg = implode(', ', array_map(fn ($e) => is_array($e) ? implode(' ', $e) : $e, $data['errors']));
                }

                return [
                    'error' => $errorMsg,
                    'details' => $data,
                ];
            }

            $json = $response->json();
            if (! is_array($json)) {
                Log::error('Ziina Create Payment Intent Invalid JSON Response', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return ['error' => 'Payment gateway returned an unexpected response. Please try again.'];
            }

            return $json;

        } catch (\Throwable $e) {
            Log::error('Ziina Create Payment Intent Exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return ['error' => 'Unable to reach payment gateway right now. Please try again shortly.'];
        }
    }

    /**
     * Fetch a payment intent status from Ziina.
     */
    public function fetchPaymentIntent(string $intentId): array
    {
        $intentId = trim($intentId);
        if (! preg_match('/^[A-Za-z0-9_\-]{4,128}$/', $intentId)) {
            return ['error' => 'Invalid Ziina payment intent identifier'];
        }

        $token = $this->getToken();
        if (empty($token)) {
            return ['error' => 'Ziina access token missing'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->connectTimeout(5)
                ->timeout(15)
                ->get("https://api-v2.ziina.com/api/payment_intent/{$intentId}");

            if ($response->failed()) {
                Log::error('Ziina Fetch Payment Intent API Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'intent_id' => $intentId,
                ]);

                $data = $response->json();

                return [
                    'error' => is_array($data) && isset($data['message']) && is_string($data['message']) ? $data['message'] : 'Ziina error',
                    'details' => $data,
                ];
            }

            $json = $response->json();
            if (! is_array($json)) {
                Log::error('Ziina Fetch Payment Intent Invalid JSON Response', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                    'intent_id' => $intentId,
                ]);

                return ['error' => 'Unexpected payment gateway response'];
            }

            return $json;

        } catch (\Throwable $e) {
            Log::error('Ziina Fetch Payment Intent Exception', [
                'message' => $e->getMessage(),
                'intent_id' => $intentId,
            ]);

            return ['error' => 'Unable to verify payment status right now.'];
        }
    }
}
