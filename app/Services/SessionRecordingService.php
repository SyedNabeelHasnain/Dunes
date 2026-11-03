<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Setting;
use App\Models\VisitorSessionRecording;
use App\Models\WhatsappInquiry;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SessionRecordingService
{
    protected IpSecurityService $ipSecurity;
    protected VisitorTrackerService $tracker;

    public function __construct(IpSecurityService $ipSecurity, VisitorTrackerService $tracker)
    {
        $this->ipSecurity = $ipSecurity;
        $this->tracker = $tracker;
    }

    /**
     * Check if session recordings are globally enabled.
     */
    public function isEnabled(): bool
    {
        try {
            $val = Setting::where('setting_key', 'session_recordings_enabled')->value('setting_value');
            return $val === null || $val === '1' || $val === 'true';
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Get configured retention days.
     */
    public function getRetentionDays(): int
    {
        try {
            $val = Setting::where('setting_key', 'session_recordings_retention_days')->value('setting_value');
            return ! empty($val) && is_numeric($val) ? (int) $val : 14;
        } catch (\Throwable $e) {
            return 14;
        }
    }

    /**
     * Ingest a batch of rrweb events for a visitor session.
     */
    public function ingestBatch(string $sessionId, array $events, array $meta = []): ?VisitorSessionRecording
    {
        if (empty($sessionId) || empty($events)) {
            return null;
        }

        $clientIp = $meta['client_ip'] ?? request()->ip();

        // 1. Strict Exclusion Checks (Firewall & Ignored IPs)
        if ($this->ipSecurity->isIpBlocked($clientIp) || $this->ipSecurity->isIpIgnoredFromAnalytics($clientIp)) {
            return null;
        }

        // 2. Strict Bot Guard
        if (($meta['is_bot'] ?? false) === true) {
            return null;
        }

        // Clean session id
        $safeSessionId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $sessionId);
        if (empty($safeSessionId)) {
            $safeSessionId = md5($clientIp . '_' . date('Ymd'));
        }

        $subDir = 'recordings/' . date('Y-m');
        $fullDir = storage_path('app/' . $subDir);
        if (! File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true, true);
        }

        $relativeFilePath = $subDir . '/' . $safeSessionId . '.json.gz';
        $absoluteFilePath = storage_path('app/' . $relativeFilePath);

        // Load existing events if recording file exists
        $existingEvents = [];
        if (File::exists($absoluteFilePath)) {
            $raw = File::get($absoluteFilePath);
            if ($raw !== false && ! empty($raw)) {
                $decoded = @gzdecode($raw);
                if ($decoded !== false) {
                    $existingEvents = json_decode($decoded, true) ?: [];
                } else {
                    $existingEvents = json_decode($raw, true) ?: [];
                }
            }
        }

        // Merge new events into array
        $mergedEvents = array_merge($existingEvents, $events);
        $eventsCount = count($mergedEvents);

        // Calculate session duration from first and last event timestamps
        $durationSeconds = 0;
        if (! empty($mergedEvents)) {
            $firstTs = $mergedEvents[0]['timestamp'] ?? null;
            $lastTs = end($mergedEvents)['timestamp'] ?? null;
            if ($firstTs && $lastTs && $lastTs >= $firstTs) {
                $durationSeconds = (int) round(($lastTs - $firstTs) / 1000);
            }
        }

        // Compress and write back to disk
        $jsonPayload = json_encode($mergedEvents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $compressed = gzencode($jsonPayload, 6);
        if ($compressed !== false) {
            File::put($absoluteFilePath, $compressed);
            $fileSizeBytes = strlen($compressed);
        } else {
            File::put($absoluteFilePath, $jsonPayload);
            $fileSizeBytes = strlen($jsonPayload);
        }

        // Check for inquiry associations (draft, whatsapp, contact)
        $hasInquiry = false;
        try {
            $hasInquiry = Booking::where(function ($q) use ($safeSessionId, $clientIp) {
                $q->where('draft_token', $safeSessionId)->orWhere('ip_address', $clientIp);
            })->exists()
            || Contact::where(function ($q) use ($safeSessionId, $clientIp) {
                $q->where('session_id', $safeSessionId)->orWhere('ip_address', $clientIp);
            })->exists()
            || WhatsappInquiry::where('client_ip', $clientIp)->exists();
        } catch (\Throwable $e) {
            // Non-blocking inquiry detection
        }

        // Upsert database record
        $recording = VisitorSessionRecording::firstOrNew(['session_id' => $safeSessionId]);
        $isNew = ! $recording->exists;

        if ($isNew) {
            $recording->client_ip = $clientIp;
            $recording->country = $meta['country'] ?? null;
            $recording->city = $meta['city'] ?? null;
            $recording->device_type = $meta['device_type'] ?? 'desktop';
            $recording->browser_name = $meta['browser_name'] ?? null;
            $recording->os_name = $meta['os_name'] ?? null;
            $recording->landing_page = $meta['current_page'] ?? null;
            $recording->started_at = now();
        }

        $recording->exit_page = $meta['current_page'] ?? $recording->exit_page;
        $recording->duration_seconds = max($recording->duration_seconds, $durationSeconds);
        $recording->events_count = $eventsCount;
        $recording->storage_path = $relativeFilePath;
        $recording->file_size_bytes = $fileSizeBytes;
        $recording->has_inquiry = $hasInquiry || (bool) $recording->has_inquiry;
        $recording->status = ($meta['is_final'] ?? false) ? 'completed' : 'recording';
        $recording->last_event_at = now();
        $recording->save();

        return $recording;
    }

    /**
     * Retrieve events array for session replay.
     */
    public function getRecordingEvents(VisitorSessionRecording $recording): array
    {
        if (empty($recording->storage_path)) {
            return [];
        }

        $absolutePath = storage_path('app/' . $recording->storage_path);
        if (! File::exists($absolutePath)) {
            return [];
        }

        $raw = File::get($absolutePath);
        if ($raw === false || empty($raw)) {
            return [];
        }

        // Check if gzipped (magic bytes 0x1f 0x8b)
        $isGzipped = (strlen($raw) >= 2 && substr($raw, 0, 2) === "\x1f\x8b");
        if ($isGzipped) {
            $decompressed = @gzdecode($raw);
            if ($decompressed !== false) {
                return json_decode($decompressed, true) ?: [];
            }
        }

        return json_decode($raw, true) ?: [];
    }

    /**
     * Delete an individual recording and its stored file.
     */
    public function deleteRecording(VisitorSessionRecording $recording): bool
    {
        if (! empty($recording->storage_path)) {
            $absolutePath = storage_path('app/' . $recording->storage_path);
            if (File::exists($absolutePath)) {
                @File::delete($absolutePath);
            }
        }

        return (bool) $recording->delete();
    }

    /**
     * Purge recordings older than configured or specified retention days.
     * Starred sessions are permanently protected.
     */
    public function purgeOldRecordings(?int $days = null): array
    {
        $retentionDays = $days ?? $this->getRetentionDays();
        if ($retentionDays <= 0) {
            return ['deleted_count' => 0, 'freed_bytes' => 0];
        }

        $cutoff = now()->subDays($retentionDays);

        $recordings = VisitorSessionRecording::where('is_starred', false)
            ->where('created_at', '<', $cutoff)
            ->get();

        $deletedCount = 0;
        $freedBytes = 0;

        foreach ($recordings as $rec) {
            $bytes = $rec->file_size_bytes;
            if ($this->deleteRecording($rec)) {
                $deletedCount++;
                $freedBytes += $bytes;
            }
        }

        return [
            'deleted_count' => $deletedCount,
            'freed_bytes' => $freedBytes,
            'retention_days' => $retentionDays,
            'cutoff' => $cutoff->toDateTimeString(),
        ];
    }

    /**
     * Bulk purge recordings.
     */
    public function purgeAllRecordings(bool $keepStarred = true): array
    {
        $query = VisitorSessionRecording::query();
        if ($keepStarred) {
            $query->where('is_starred', false);
        }

        $recordings = $query->get();
        $deletedCount = 0;
        $freedBytes = 0;

        foreach ($recordings as $rec) {
            $bytes = $rec->file_size_bytes;
            if ($this->deleteRecording($rec)) {
                $deletedCount++;
                $freedBytes += $bytes;
            }
        }

        return [
            'deleted_count' => $deletedCount,
            'freed_bytes' => $freedBytes,
        ];
    }

    /**
     * Get aggregate storage statistics.
     */
    public function getStorageStats(): array
    {
        $totalCount = VisitorSessionRecording::count();
        $starredCount = VisitorSessionRecording::where('is_starred', true)->count();
        $inquiriesCount = VisitorSessionRecording::where('has_inquiry', true)->count();
        $totalBytes = (int) VisitorSessionRecording::sum('file_size_bytes');
        $totalDurationSec = (int) VisitorSessionRecording::sum('duration_seconds');

        $formattedBytes = '0 KB';
        if ($totalBytes >= 1048576) {
            $formattedBytes = round($totalBytes / 1048576, 1) . ' MB';
        } elseif ($totalBytes > 0) {
            $formattedBytes = round($totalBytes / 1024, 1) . ' KB';
        }

        $formattedDuration = '0m';
        if ($totalDurationSec >= 3600) {
            $h = floor($totalDurationSec / 3600);
            $m = floor(($totalDurationSec % 3600) / 60);
            $formattedDuration = "{$h}h {$m}m";
        } elseif ($totalDurationSec > 0) {
            $formattedDuration = floor($totalDurationSec / 60) . 'm ' . ($totalDurationSec % 60) . 's';
        }

        return [
            'total_recordings' => $totalCount,
            'starred_recordings' => $starredCount,
            'inquiries_recordings' => $inquiriesCount,
            'total_bytes' => $totalBytes,
            'formatted_bytes' => $formattedBytes,
            'total_duration_sec' => $totalDurationSec,
            'formatted_duration' => $formattedDuration,
            'retention_days' => $this->getRetentionDays(),
            'is_enabled' => $this->isEnabled(),
        ];
    }
}
