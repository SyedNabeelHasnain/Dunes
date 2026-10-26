<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Services\SettingsService;
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

    public int $tries = 3;

    public array $backoff = [15, 60, 180];

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
        $settings = app(SettingsService::class);
        $pixelId = trim($settings->get('meta_pixel_id', ''));
        $token = trim($settings->get('meta_capi_token', '') ?: $settings->get('meta_access_token', ''));

        if (empty($pixelId) || empty($token)) {
            return;
        }

        $graphVersion = config('services.meta.graph_version', 'v21.0') ?: 'v21.0';
        $endpoint = "https://graph.facebook.com/{$graphVersion}/{$pixelId}/events";

        $userData = [];

        // Add cookies (_fbp, _fbc)
        if (! empty($this->cookies['fbp'])) {
            $userData['fbp'] = $this->cookies['fbp'];
        }
        if (! empty($this->cookies['fbc'])) {
            $userData['fbc'] = $this->cookies['fbc'];
        }

        // Add hashed email if present
        if (! empty($this->data['email'])) {
            $userData['em'] = [hash('sha256', strtolower(trim($this->data['email'])))];
        }

        // Add hashed phone if present
        if (! empty($this->data['phone'])) {
            $phoneDigits = preg_replace('/[^0-9]/', '', $this->data['phone']);
            if (! empty($phoneDigits)) {
                $userData['ph'] = [hash('sha256', $phoneDigits)];
            }
        }

        // IP and User Agent settings check (via cached SettingsService)
        $sendIpUaRaw = $settings->get('meta_send_ip_ua', '1');
        $sendIpUa = $sendIpUaRaw === null || $sendIpUaRaw === '' || $sendIpUaRaw === '1' || $sendIpUaRaw === 'true';

        if ($sendIpUa) {
            if (! empty($this->clientIp)) {
                $userData['client_ip_address'] = $this->clientIp;
            }
            if (! empty($this->userAgent)) {
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

        if (! empty($this->data['event_id'])) {
            $eventPayload['event_id'] = $this->data['event_id'];
        }

        $payload = [
            'data' => [$eventPayload],
            'access_token' => $token,
        ];

        // Test event code config
        $testCode = trim((string) $settings->get('meta_test_event_code', ''));
        if (! empty($testCode)) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::connectTimeout(5)->timeout(12)->post($endpoint, $payload);

            if ($response->failed()) {
                $status = $response->status();
                Log::error("Meta CAPI Error ({$this->eventName})", [
                    'status' => $status,
                    'body' => $response->json(),
                ]);

                if (($status === 429 || $status >= 500) && $this->job && $this->job->getConnectionName() !== 'sync') {
                    $this->release(60);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Meta CAPI Exception ({$this->eventName}): ".$e->getMessage());

            if ($this->job && $this->job->getConnectionName() !== 'sync') {
                $this->release(60);
            }
        }
    }
}
