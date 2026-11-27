<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const PUBLIC_REDACTED_EXACT_KEYS = [
        'smtp_pass',
        'smtp_password',
        'smtp_fallback_password',
        'ziina_access_token',
        'ziina_webhook_secret',
        'meta_access_token',
        'meta_capi_token',
        'meta_whatsapp_token',
        'meta_webhook_verify_token',
        'recaptcha_secret_key',
        'turnstile_secret_key',
        'tripadvisor_api_key',
        'deepl_auth_key',
        'deepl_api_key',
        'google_translate_api_key',
        'google_places_api_key',
        'google_maps_api_key',
        'google_api_key',
        'gsc_oauth_access_token',
        'bing_webmaster_api_key',
        'notification_webhook_secret',
    ];

    protected ?Collection $settings = null;

    protected ?Collection $publicSettings = null;

    public function publicAll(): Collection
    {
        if ($this->publicSettings === null) {
            $this->publicSettings = $this->all()->reject(function ($value, $key) {
                $lower = strtolower((string) $key);
                if (in_array($lower, self::PUBLIC_REDACTED_EXACT_KEYS, true)) {
                    return true;
                }
                if (str_contains($lower, 'site_key')) {
                    return false;
                }

                foreach (['password', 'secret', 'token', 'api_key', 'auth_key', 'private_key', 'smtp_pass'] as $frag) {
                    if (str_contains($lower, $frag)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $this->publicSettings;
    }

    public function all(): Collection
    {
        if ($this->settings === null) {
            $cached = null;
            try {
                $cached = Cache::remember('site_settings_cache', 3600, function () {
                    return Setting::pluck('setting_value', 'setting_key')->all();
                });
            } catch (\Throwable $e) {
                try {
                    $cached = Setting::pluck('setting_value', 'setting_key')->all();
                } catch (\Throwable $e2) {
                    $cached = [];
                }
            }

            if (is_array($cached)) {
                $this->settings = collect($cached);
            } elseif ($cached instanceof Collection) {
                $this->settings = $cached;
            } else {
                $this->settings = collect();
            }
        }

        return $this->settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        try {
            $val = $this->all()->get($key, $default);

            return $val !== null ? (string) $val : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public function getFromEmail(): string
    {
        return $this->get('site_email', 'info@dunesdiscoverytourism.com') ?: 'info@dunesdiscoverytourism.com';
    }

    public function getAdminEmail(): string
    {
        return $this->get('admin_email', 'admin@dunesdiscoverytourism.com') ?: 'admin@dunesdiscoverytourism.com';
    }

    public function getCcEmails(): array
    {
        $cc = $this->get('admin_email_cc', '');

        return ! empty($cc) ? array_filter(array_map('trim', explode(',', $cc))) : [];
    }

    public function getBccEmails(): array
    {
        $bcc = $this->get('admin_email_bcc', '');

        return ! empty($bcc) ? array_filter(array_map('trim', explode(',', $bcc))) : [];
    }

    /**
     * Parse comma-separated notification recipient emails with fallback.
     */
    public function getNotificationRecipients(string $key, array $fallback = []): array
    {
        $val = $this->get($key, '');
        if (! empty($val)) {
            $parsed = array_filter(array_map('trim', explode(',', $val)));
            if (! empty($parsed)) {
                return $parsed;
            }
        }

        return $fallback;
    }

    public function clearCache(): void
    {
        try {
            Cache::forget('site_settings_cache');
            Cache::forget('translation_settings_cache');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SettingsService::clearCache failed: '.$e->getMessage());
        }
        $this->settings = null;
        $this->publicSettings = null;
    }
}
