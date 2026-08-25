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
            $this->settings = Cache::remember('site_settings_cache', 3600, function () {
                return Setting::all()->pluck('setting_value', 'setting_key');
            });
        }
        return $this->settings;
    }
    
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()->get($key, $default);
    }
    
    public function getFromEmail(): string
    {
        return $this->get('site_email', 'info@dunesdiscoverytourism.com');
    }
    
    public function getAdminEmail(): string
    {
        return $this->get('admin_email', 'admin@dunesdiscoverytourism.com');
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
        Cache::forget('site_settings_cache');
        $this->settings = null;
    }
}
