<?php

namespace App\Jobs;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMetaCapiEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventName;
    protected $data;
    protected $clientIp;
    protected $userAgent;
    protected $cookies;
    protected $eventSourceUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(string $eventName, array $data, ?string $clientIp, ?string $userAgent, array $cookies, string $eventSourceUrl)
    {
        $this->eventName = $eventName;
        $this->data = $data;
        $this->clientIp = $clientIp;
        $this->userAgent = $userAgent;
        $this->cookies = $cookies;
        $this->eventSourceUrl = $eventSourceUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pixelIdSetting = Setting::where('setting_key', 'meta_pixel_id')->first();
        $capiTokenSetting = Setting::where('setting_key', 'meta_capi_token')->first()
            ?? Setting::where('setting_key', 'meta_access_token')->first();

        $pixelId = $pixelIdSetting ? trim($pixelIdSetting->setting_value) : '';
        $token = $capiTokenSetting ? trim($capiTokenSetting->setting_value) : '';

        if (empty($pixelId) || empty($token)) {
            return;
        }

        $endpoint = "https://graph.facebook.com/v25.0/{$pixelId}/events";
        
        $userData = [];
        
        // Add cookies (_fbp, _fbc)
        if (!empty($this->cookies['fbp'])) {
            $userData['fbp'] = $this->cookies['fbp'];
        }
        if (!empty($this->cookies['fbc'])) {
            $userData['fbc'] = $this->cookies['fbc'];
        }

        // Add hashed email if present
        if (!empty($this->data['email'])) {
            $userData['em'] = [hash('sha256', strtolower(trim($this->data['email'])))];
        }

        // Add hashed phone if present
        if (!empty($this->data['phone'])) {
            $phoneDigits = preg_replace('/[^0-9]/', '', $this->data['phone']);
            if (!empty($phoneDigits)) {
                $userData['ph'] = [hash('sha256', $phoneDigits)];
            }
        }

        // IP and User Agent settings check
        $sendIpUaSetting = Setting::where('setting_key', 'meta_send_ip_ua')->first();
        $sendIpUa = !$sendIpUaSetting || $sendIpUaSetting->setting_value === '1';

        if ($sendIpUa) {
            if (!empty($this->clientIp)) {
                $userData['client_ip_address'] = $this->clientIp;
            }
            if (!empty($this->userAgent)) {
                $userData['client_user_agent'] = $this->userAgent;
            }
        }

        // Custom parameters
        $customData = $this->data['custom_data'] ?? [];

        $eventPayload = [
            'event_name' => $this->eventName,
            'event_time' => time(),
            'action_source' => 'website',
            'event_source_url' => $this->eventSourceUrl,
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        if (!empty($this->data['event_id'])) {
            $eventPayload['event_id'] = $this->data['event_id'];
        }

        $payload = [
            'data' => [$eventPayload]
        ];

        // Test event code config
        $testCodeSetting = Setting::where('setting_key', 'meta_test_event_code')->first();
        $testCode = $testCodeSetting ? trim($testCodeSetting->setting_value) : '';
        if (!empty($testCode)) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(15)
                ->post("{$endpoint}?access_token=" . rawurlencode($token), $payload);

            if ($response->failed()) {
                Log::error("Meta CAPI Error ({$this->eventName})", [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Meta CAPI Job Exception ({$this->eventName})", [
                'message' => $e->getMessage()
            ]);
        }
    }
}
