<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected ?Collection $settings = null;
    
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
            return $val !== null ? (string)$val : $default;
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
        return !empty($cc) ? array_filter(array_map('trim', explode(',', $cc))) : [];
    }
    
    public function getBccEmails(): array
    {
        $bcc = $this->get('admin_email_bcc', '');
        return !empty($bcc) ? array_filter(array_map('trim', explode(',', $bcc))) : [];
    }
    
    public function clearCache(): void
    {
        try {
            Cache::forget('site_settings_cache');
        } catch (\Throwable $e) {}
        $this->settings = null;
    }
}

