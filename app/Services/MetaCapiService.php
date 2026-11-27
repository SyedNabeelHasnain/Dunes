<?php

namespace App\Services;

use App\Jobs\SendMetaCapiEvent;

class MetaCapiService
{
    /**
     * Check if Meta integrations are active.
     */
    public function isActive(): bool
    {
        $settings = app(SettingsService::class);

        return $settings->get('meta_active') === '1' && $settings->get('meta_capi_enabled') === '1';
    }

    /**
     * Dispatch Meta Conversions API Event to queue.
     */
    public function dispatchEvent(string $eventName, array $data = []): void
    {
        if (! $this->isActive()) {
            return;
        }

        // Capture request context in front-end context before queue execution (allow explicit booking context override for webhooks)
        $clientIp = ! empty($data['client_ip']) ? (string) $data['client_ip'] : request()->ip();
        $userAgent = ! empty($data['user_agent']) ? (string) $data['user_agent'] : request()->userAgent();

        $cookies = [
            'fbp' => $data['fbp'] ?? request()->cookie('_fbp'),
            'fbc' => $data['fbc'] ?? request()->cookie('_fbc'),
        ];

        $eventSourceUrl = ! empty($data['event_source_url']) ? (string) $data['event_source_url'] : request()->fullUrl();

        // Dispatch background job
        SendMetaCapiEvent::dispatch($eventName, $data, $clientIp, $userAgent, $cookies, $eventSourceUrl);
    }
}
