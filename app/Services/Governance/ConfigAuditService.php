<?php

namespace App\Services\Governance;

use App\Models\ConfigAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Immutable configuration and governance audit logger with automatic secret redaction.
 */
class ConfigAuditService
{
    private const SECRET_KEY_FRAGMENTS = [
        'password',
        'secret',
        'token',
        'api_key',
        'apikey',
        'private_key',
        'webhook_secret',
        'smtp_pass',
    ];

    public function log(
        string $section,
        string $action,
        ?string $settingKey = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $summary = null,
        ?User $actor = null,
    ): ?ConfigAuditLog {
        try {
            if (! Schema::hasTable('config_audit_logs')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $user = $actor ?? Auth::user();
        $request = app()->bound('request') ? request() : null;

        $oldSerialized = $this->formatAuditValue($settingKey, $oldValue);
        $newSerialized = $this->formatAuditValue($settingKey, $newValue);

        $rawUnchanged = $oldValue === $newValue
            || (is_scalar($oldValue) && is_scalar($newValue) && (string) $oldValue === (string) $newValue);
        if ($action === 'updated' && $rawUnchanged && $summary === null) {
            return null;
        }

        if ($settingKey && $this->isSecretKey($settingKey) && $summary === null) {
            $summary = "Updated confidential setting {$settingKey}";
        }

        return ConfigAuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_email' => $user?->email,
            'user_role' => method_exists($user, 'normalizedRole') ? $user->normalizedRole() : ($user?->role ?? 'system'),
            'section' => $section,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'setting_key' => $settingKey,
            'old_value' => $oldSerialized,
            'new_value' => $newSerialized,
            'summary' => $summary ? Str::limit($summary, 495) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? Str::limit((string) $request->userAgent(), 495) : null,
        ]);
    }

    private function formatAuditValue(?string $key, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($key && $this->isSecretKey($key)) {
            return trim((string) (is_scalar($value) ? $value : json_encode($value))) === ''
                ? ''
                : '[REDACTED]';
        }

        if (is_array($value) || is_object($value)) {
            $sanitized = $this->redactNestedArray(is_array($value) ? $value : (array) $value);

            return json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return Str::limit((string) $value, 15000);
    }

    private function isSecretKey(string $key): bool
    {
        $lower = strtolower($key);
        foreach (self::SECRET_KEY_FRAGMENTS as $fragment) {
            if (str_contains($lower, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function redactNestedArray(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (is_string($k) && $this->isSecretKey($k)) {
                $out[$k] = empty($v) ? '' : '[REDACTED]';
            } elseif (is_array($v)) {
                $out[$k] = $this->redactNestedArray($v);
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}
