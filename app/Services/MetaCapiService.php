<?php

namespace App\Services;

use App\Jobs\SendMetaCapiEvent;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class MetaCapiService
{
    /**
     * Check if Meta integrations are active.
     */
    public function isActive(): bool
    {
        $active = Setting::where('setting_key', 'meta_active')->first();
        $capi = Setting::where('setting_key', 'meta_capi_enabled')->first();
        return $active && $active->setting_value === '1' && $capi && $capi->setting_value === '1';
    }

    /**
     * Dispatch Meta Conversions API Event to queue.
     */
    public function dispatchEvent(string $eventName, array $data = []): void
    {
        if (!$this->isActive()) {
            return;
        }

        // Capture request context in front-end context before queue execution
        $clientIp = request()->ip();
        $userAgent = request()->userAgent();
        
        $cookies = [
            'fbp' => request()->cookie('_fbp'),
            'fbc' => request()->cookie('_fbc'),
        ];

        $eventSourceUrl = request()->fullUrl();

        // Dispatch background job
        SendMetaCapiEvent::dispatch($eventName, $data, $clientIp, $userAgent, $cookies, $eventSourceUrl);
    }
}
