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

    protected const MAX_EVENTS_PER_SESSION = 5000;
    protected const MAX_BYTES_PER_SESSION = 5242880; // 5 MB

    public function __construct(IpSecurityService $ipSecurity)
    {
        $this->ipSecurity = $ipSecurity;
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
        if (empty($sessionId) || empty($events) || ! $this->isEnabled()) {
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

        // Clean & bound session id
        $safeSessionId = substr((string) preg_replace('/[^a-zA-Z0-9_\-]/', '', $sessionId), 0, 100);
        if (empty($safeSessionId)) {
            $safeSessionId = md5($clientIp . '_' . date('Ymd'));
        }

        // Reuse existing recording storage_path across month boundaries so playback never splits across directories
        $existingRecord = VisitorSessionRecording::where('session_id', $safeSessionId)->first();
        if ($existingRecord && ! empty($existingRecord->storage_path)) {
            $relativeFilePath = $existingRecord->storage_path;
            $fullDir = dirname(storage_path('app/' . $relativeFilePath));
        } else {
            $subDir = 'recordings/' . date('Y-m');
            $fullDir = storage_path('app/' . $subDir);
            $relativeFilePath = $subDir . '/' . $safeSessionId . '.json.gz';
        }

        if (! File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true, true);
        }

        $absoluteFilePath = storage_path('app/' . $relativeFilePath);
        $lockFilePath = $absoluteFilePath . '.lock';

        $lockHandle = @fopen($lockFilePath, 'c+b');
        if ($lockHandle) {
            @flock($lockHandle, LOCK_EX);
        }

        $recording = null;

        try {
            // Load existing events if recording file exists
            $existingEvents = [];
            if (File::exists($absoluteFilePath)) {
                $raw = File::get($absoluteFilePath);
                if ($raw !== false && ! empty($raw)) {
                    $decoded = @gzdecode($raw, self::MAX_BYTES_PER_SESSION * 4);
                    if ($decoded !== false) {
                        $existingEvents = json_decode($decoded, true) ?: [];
                    } else {
                        $existingEvents = json_decode($raw, true) ?: [];
                    }
                }
            }

            // Merge, deduplicate, cap, and chronologically sort events so multi-batch/unload requests never break playback
            $mergedEvents = $this->normalizeEvents(array_merge($existingEvents, $events));
            if (count($mergedEvents) > self::MAX_EVENTS_PER_SESSION) {
                // Keep earliest Meta/FullSnapshot events + latest tail up to cap
                $mergedEvents = array_slice($mergedEvents, 0, self::MAX_EVENTS_PER_SESSION);
            }
            $eventsCount = count($mergedEvents);

            // Calculate accurate session duration across chronological timestamps
            $durationSeconds = 0;
            if (! empty($mergedEvents)) {
                $firstTs = (float) ($mergedEvents[0]['timestamp'] ?? 0);
                $lastTs = (float) (end($mergedEvents)['timestamp'] ?? 0);
                if ($firstTs > 0 && $lastTs >= $firstTs) {
                    $durationSeconds = (int) round(($lastTs - $firstTs) / 1000);
                }
            }

            // Compress and write atomically via temp file + rename under exclusive lock
            $jsonPayload = json_encode($mergedEvents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $compressed = gzencode($jsonPayload, 6);
            $bytesToWrite = $compressed !== false ? $compressed : $jsonPayload;
            if (strlen($bytesToWrite) <= self::MAX_BYTES_PER_SESSION) {
                $tmpPath = $absoluteFilePath . '.tmp.' . uniqid('', true);
                File::put($tmpPath, $bytesToWrite);
                @rename($tmpPath, $absoluteFilePath);
                $fileSizeBytes = strlen($bytesToWrite);
            } else {
                $fileSizeBytes = File::exists($absoluteFilePath) ? (int) filesize($absoluteFilePath) : strlen($bytesToWrite);
            }

            // Check for inquiry associations (draft, whatsapp, contact — contacts table uses ip_address)
            $hasInquiry = false;
            try {
                $hasInquiry = Booking::where(function ($q) use ($safeSessionId, $clientIp) {
                    $q->where('draft_token', $safeSessionId)->orWhere('ip_address', $clientIp);
                })->exists()
                || Contact::where('ip_address', $clientIp)->exists()
                || WhatsappInquiry::where('client_ip', $clientIp)->exists();
            } catch (\Throwable $e) {
                Log::debug('SessionRecordingService inquiry lookup failed: ' . $e->getMessage());
            }

            // Upsert database record inside the lock so concurrent batches stay consistent
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
            $recording->duration_seconds = max((int) ($recording->duration_seconds ?? 0), $durationSeconds);
            $recording->events_count = $eventsCount;
            $recording->storage_path = $relativeFilePath;
            $recording->file_size_bytes = $fileSizeBytes;
            $recording->has_inquiry = $hasInquiry || (bool) $recording->has_inquiry;
            $recording->status = ($meta['is_final'] ?? false) ? 'completed' : 'recording';
            $recording->last_event_at = now();
            $recording->save();
        } finally {
            if ($lockHandle) {
                @flock($lockHandle, LOCK_UN);
                @fclose($lockHandle);
            }
        }

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

        // Check if gzipped (magic bytes 0x1f 0x8b) and always normalize events
        $isGzipped = (strlen($raw) >= 2 && substr($raw, 0, 2) === "\x1f\x8b");
        if ($isGzipped) {
            $decompressed = @gzdecode($raw);
            if ($decompressed !== false) {
                $events = json_decode($decompressed, true) ?: [];

                return $this->normalizeEvents(is_array($events) ? $events : []);
            }
        }

        $events = json_decode($raw, true) ?: [];

        return $this->normalizeEvents(is_array($events) ? $events : []);
    }

    /**
     * Prepare a recording stream for rrweb-player playback: validates FullSnapshot availability,
     * strips leading orphan mutations before the first FullSnapshot, guarantees valid Meta dimensions,
     * and extracts multi-page navigation timeline markers.
     *
     * @return array{
     *   events: array<int, array<string, mixed>>,
     *   replayable: bool,
     *   has_full_snapshot: bool,
     *   has_meta: bool,
     *   raw_events_count: int,
     *   duration_ms: int,
     *   pages: array<int, array{url: string, path: string, offset_ms: int, timestamp: int}>,
     *   reason: ?string
     * }
     */
    public function prepareEventsForPlayback(VisitorSessionRecording $recording): array
    {
        $events = $this->getRecordingEvents($recording);
        $rawCount = count($events);

        if ($rawCount === 0) {
            return [
                'events' => [],
                'replayable' => false,
                'has_full_snapshot' => false,
                'has_meta' => false,
                'raw_events_count' => 0,
                'duration_ms' => 0,
                'pages' => [],
                'reason' => 'No recorded DOM events were found on disk for this session.',
            ];
        }

        $firstSnapIdx = null;
        foreach ($events as $idx => $evt) {
            if ((int) ($evt['type'] ?? -1) === 2) {
                $firstSnapIdx = $idx;
                break;
            }
        }

        if ($firstSnapIdx === null) {
            return [
                'events' => $events,
                'replayable' => false,
                'has_full_snapshot' => false,
                'has_meta' => false,
                'raw_events_count' => $rawCount,
                'duration_ms' => 0,
                'pages' => [],
                'reason' => "Captured {$rawCount} telemetry event(s), but the visitor navigated away before an initial DOM FullSnapshot reached the server.",
            ];
        }

        // Keep any matching Meta/Load event at or right before the first FullSnapshot, dropping leading orphan incremental events
        $startIdx = $firstSnapIdx;
        for ($i = $firstSnapIdx - 1; $i >= 0; $i--) {
            $t = (int) ($events[$i]['type'] ?? -1);
            if ($t === 3) {
                break;
            }
            $startIdx = $i;
        }
        $stream = array_slice($events, $startIdx);

        $isMob = strtolower((string) ($recording->device_type ?? '')) === 'mobile';
        $fallbackMeta = [
            'href' => $recording->landing_page ? url($recording->landing_page) : url('/'),
            'width' => $isMob ? 390 : 1280,
            'height' => $isMob ? 844 : 800,
        ];

        // Normalize existing Meta events so width/height are always >= 320x240 (prevents Infinity scale in rrweb-player)
        $hasMeta = false;
        foreach ($stream as &$evt) {
            if ((int) ($evt['type'] ?? -1) === 4) {
                $hasMeta = true;
                $data = is_array($evt['data'] ?? null) ? $evt['data'] : [];
                $w = (int) ($data['width'] ?? 0);
                $h = (int) ($data['height'] ?? 0);
                if ($w < 320 || $h < 240) {
                    $data['width'] = $w >= 320 ? $w : $fallbackMeta['width'];
                    $data['height'] = $h >= 240 ? $h : $fallbackMeta['height'];
                }
                if (empty($data['href'])) {
                    $data['href'] = $fallbackMeta['href'];
                }
                $evt['data'] = $data;
            }
        }
        unset($evt);

        $startTs = (int) ($stream[0]['timestamp'] ?? 0);
        $endTs = (int) (end($stream)['timestamp'] ?? $startTs);
        $durationMs = max(0, $endTs - $startTs);

        $pages = [];
        foreach ($stream as $evt) {
            if ((int) ($evt['type'] ?? -1) === 4 && ! empty($evt['data']['href'])) {
                $href = (string) $evt['data']['href'];
                $path = parse_url($href, PHP_URL_PATH) ?: '/';
                $ts = (int) ($evt['timestamp'] ?? $startTs);
                $pages[] = [
                    'url' => $href,
                    'path' => $path,
                    'offset_ms' => max(0, $ts - $startTs),
                    'timestamp' => $ts,
                ];
            }
        }

        return [
            'events' => $stream,
            'replayable' => count($stream) >= 2,
            'has_full_snapshot' => true,
            'has_meta' => $hasMeta,
            'recommended_meta' => $fallbackMeta,
            'raw_events_count' => $rawCount,
            'duration_ms' => $durationMs,
            'pages' => $pages,
            'reason' => null,
        ];
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
            if (File::exists($absolutePath . '.lock')) {
                @File::delete($absolutePath . '.lock');
            }
        }

        return (bool) $recording->delete();
    }

    /**
     * Chronologically sort and deduplicate rrweb events while preserving Meta -> FullSnapshot ordering on equal timestamps.
     *
     * @param  array<int, mixed>  $events
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeEvents(array $events): array
    {
        $indexed = [];
        $seen = [];

        foreach ($events as $seq => $evt) {
            if (! is_array($evt) || ! isset($evt['type'], $evt['timestamp'])) {
                continue;
            }
            $type = (int) $evt['type'];
            $ts = (float) $evt['timestamp'];

            // Deduplicate identical retries/double-unload emissions
            $sig = $type . '|' . $ts . '|' . md5(json_encode($evt['data'] ?? null));
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;

            $indexed[] = [
                'event' => $evt,
                'ts' => $ts,
                'prio' => match ($type) {
                    0 => 0, // DomContentLoaded
                    1 => 1, // Load
                    4 => 2, // Meta (must precede FullSnapshot!)
                    2 => 3, // FullSnapshot
                    5 => 4, // Custom
                    6 => 5, // Plugin
                    default => 6, // IncrementalSnapshot
                },
                'seq' => $seq,
            ];
        }

        usort($indexed, static function (array $a, array $b): int {
            if ($a['ts'] !== $b['ts']) {
                return $a['ts'] <=> $b['ts'];
            }
            if ($a['prio'] !== $b['prio']) {
                return $a['prio'] <=> $b['prio'];
            }

            return $a['seq'] <=> $b['seq'];
        });

        return array_map(static fn (array $item): array => $item['event'], $indexed);
    }

    /**
     * Purge recordings older than configured or specified retention days.
     * Starred sessions are permanently protected.
     */
    public function purgeOldRecordings(?int $days = null): array
    {
        $retentionDays = $days ?? $this->getRetentionDays();
        if ($retentionDays <= 0) {
            return [
                'deleted_count' => 0,
                'freed_bytes' => 0,
                'retention_days' => $retentionDays,
                'cutoff' => null,
            ];
        }

        $cutoff = now()->subDays($retentionDays);
        $deletedCount = 0;
        $freedBytes = 0;

        VisitorSessionRecording::where('is_starred', false)
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($recordings) use (&$deletedCount, &$freedBytes) {
                foreach ($recordings as $rec) {
                    $bytes = (int) $rec->file_size_bytes;
                    $lockPath = ! empty($rec->storage_path) ? storage_path('app/' . $rec->storage_path . '.lock') : null;
                    if ($this->deleteRecording($rec)) {
                        if ($lockPath && File::exists($lockPath)) {
                            @unlink($lockPath);
                        }
                        $deletedCount++;
                        $freedBytes += $bytes;
                    }
                }
            });

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

        $deletedCount = 0;
        $freedBytes = 0;

        $query->chunkById(100, function ($recordings) use (&$deletedCount, &$freedBytes) {
            foreach ($recordings as $rec) {
                $bytes = (int) $rec->file_size_bytes;
                $lockPath = ! empty($rec->storage_path) ? storage_path('app/' . $rec->storage_path . '.lock') : null;
                if ($this->deleteRecording($rec)) {
                    if ($lockPath && File::exists($lockPath)) {
                        @unlink($lockPath);
                    }
                    $deletedCount++;
                    $freedBytes += $bytes;
                }
            }
        });

        return [
            'deleted_count' => $deletedCount,
            'freed_bytes' => $freedBytes,
        ];
    }

    /**
     * Get aggregate storage statistics in a single SQL query.
     */
    public function getStorageStats(): array
    {
        $agg = VisitorSessionRecording::query()
            ->selectRaw('COUNT(*) as total_count, SUM(CASE WHEN is_starred = 1 THEN 1 ELSE 0 END) as starred_count, SUM(CASE WHEN has_inquiry = 1 THEN 1 ELSE 0 END) as inquiries_count, COALESCE(SUM(file_size_bytes), 0) as total_bytes, COALESCE(SUM(duration_seconds), 0) as total_duration_sec')
            ->first();

        $totalCount = (int) ($agg->total_count ?? 0);
        $starredCount = (int) ($agg->starred_count ?? 0);
        $inquiriesCount = (int) ($agg->inquiries_count ?? 0);
        $totalBytes = (int) ($agg->total_bytes ?? 0);
        $totalDurationSec = (int) ($agg->total_duration_sec ?? 0);

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
