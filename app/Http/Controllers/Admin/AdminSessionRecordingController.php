<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VisitorSessionRecording;
use App\Services\SessionRecordingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSessionRecordingController extends Controller
{
    protected SessionRecordingService $recordingService;

    public function __construct(SessionRecordingService $recordingService)
    {
        $this->recordingService = $recordingService;
    }

    /**
     * Display session recordings dashboard with filters & KPIs.
     */
    public function index(Request $request): View
    {
        $query = VisitorSessionRecording::query()->orderBy('last_event_at', 'desc');

        if ($request->filled('device') && $request->device !== 'all') {
            $query->where('device_type', $request->device);
        }

        if ($request->filled('country') && $request->country !== 'all') {
            $query->where('country', $request->country);
        }

        if ($request->filled('inquiry')) {
            if ($request->inquiry === 'yes') {
                $query->where('has_inquiry', true);
            } elseif ($request->inquiry === 'no') {
                $query->where('has_inquiry', false);
            }
        }

        if ($request->filled('starred') && $request->starred === '1') {
            $query->where('is_starred', true);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('session_id', 'like', "%{$s}%")
                  ->orWhere('client_ip', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('landing_page', 'like', "%{$s}%");
            });
        }

        if ($request->filled('days') && is_numeric($request->days)) {
            $query->where('created_at', '>=', now()->subDays((int) $request->days));
        }

        $recordings = $query->paginate(25)->withQueryString();
        $stats = $this->recordingService->getStorageStats();

        // Get unique countries for filter dropdown
        $countries = VisitorSessionRecording::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('admin.analytics.recordings.index', compact('recordings', 'stats', 'countries'));
    }

    /**
     * Return events for a session replay via JSON.
     */
    public function events(int|string $id): JsonResponse
    {
        $recording = is_numeric($id)
            ? VisitorSessionRecording::find($id)
            : VisitorSessionRecording::where('session_id', $id)->first();

        if (! $recording) {
            return response()->json(['success' => false, 'message' => 'Recording not found.'], 404);
        }

        $events = $this->recordingService->getRecordingEvents($recording);

        return response()->json([
            'success' => true,
            'recording' => [
                'id' => $recording->id,
                'session_id' => $recording->session_id,
                'client_ip' => $recording->client_ip,
                'country' => $recording->country,
                'city' => $recording->city,
                'device_type' => $recording->device_type,
                'browser_name' => $recording->browser_name,
                'os_name' => $recording->os_name,
                'landing_page' => $recording->landing_page,
                'exit_page' => $recording->exit_page,
                'duration_seconds' => $recording->duration_seconds,
                'formatted_duration' => $recording->formatted_duration,
                'events_count' => $recording->events_count,
                'file_size' => $recording->formatted_file_size,
                'is_starred' => (bool) $recording->is_starred,
                'has_inquiry' => (bool) $recording->has_inquiry,
                'created_at' => $recording->created_at?->format('M d, Y h:i A'),
                'relative_time' => $recording->last_event_at?->diffForHumans() ?? $recording->created_at?->diffForHumans(),
            ],
            'events' => $events,
            'events_count' => count($events),
        ]);
    }

    /**
     * Toggle starred status on a recording.
     */
    public function toggleStar(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $recording = VisitorSessionRecording::findOrFail($id);
        $recording->is_starred = ! $recording->is_starred;
        $recording->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_starred' => (bool) $recording->is_starred,
                'message' => $recording->is_starred ? 'Recording starred and protected from auto-purge.' : 'Recording unstarred.',
            ]);
        }

        return back()->with('success', $recording->is_starred ? 'Recording starred.' : 'Recording unstarred.');
    }

    /**
     * Delete an individual recording.
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $recording = VisitorSessionRecording::findOrFail($id);
        $this->recordingService->deleteRecording($recording);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Recording deleted successfully.',
            ]);
        }

        return back()->with('success', 'Session recording deleted successfully.');
    }

    /**
     * Bulk purge recordings.
     */
    public function purge(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'expired');

        if ($mode === 'all') {
            $keepStarred = ! $request->boolean('include_starred');
            $res = $this->recordingService->purgeAllRecordings($keepStarred);
            $count = $res['deleted_count'];
            return back()->with('success', "Purged {$count} recording(s) completely.");
        }

        $days = $request->filled('days') ? (int) $request->days : null;
        $res = $this->recordingService->purgeOldRecordings($days);
        $count = $res['deleted_count'];
        $d = $res['retention_days'];

        return back()->with('success', "Purged {$count} recording(s) older than {$d} days.");
    }

    /**
     * Update session recording settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'session_recordings_retention_days' => 'required|integer|min:1|max:365',
            'session_recordings_enabled' => 'nullable|boolean',
        ]);

        Setting::updateOrCreate(
            ['setting_key' => 'session_recordings_retention_days'],
            ['setting_value' => (string) $request->session_recordings_retention_days]
        );

        Setting::updateOrCreate(
            ['setting_key' => 'session_recordings_enabled'],
            ['setting_value' => $request->boolean('session_recordings_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Session recording preferences updated successfully.');
    }
}
