<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignClick;
use App\Models\EmailCampaignLog;
use App\Models\Subscriber;
use App\Models\SubscriberGroup;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Public AJAX Newsletter Subscription Endpoint.
     */
    public function subscribe(Request $request): JsonResponse
    {
        // 1. Anti-Bot Honeypot Protection
        if (!empty($request->input('website_url'))) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing!',
            ]);
        }

        // 2. Validate input
        $validated = $request->validate([
            'email' => 'required|email:filter|max:255',
            'name' => 'nullable|string|max:150',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'consent' => 'nullable',
            'source' => 'nullable|string|max:50',
        ]);

        $email = strtolower(trim($validated['email']));

        $fullName = !empty($validated['name']) ? trim($validated['name']) : null;
        if ($fullName) {
            $parts = preg_split('/\s+/', $fullName, 2);
            $firstName = $parts[0] ?? null;
            $lastName = $parts[1] ?? null;
        } else {
            $firstName = !empty($validated['first_name']) ? trim($validated['first_name']) : null;
            $lastName = !empty($validated['last_name']) ? trim($validated['last_name']) : null;
        }

        $phone = !empty($validated['phone']) ? trim($validated['phone']) : null;
        $source = !empty($validated['source']) ? trim($validated['source']) : 'footer';

        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->status === 'subscribed') {
                return response()->json([
                    'success' => true,
                    'message' => 'You are already subscribed to our exclusive updates!',
                    'already_subscribed' => true,
                ]);
            }

            // Reactivate previously unsubscribed contact
            $subscriber->update([
                'status' => 'subscribed',
                'first_name' => $firstName ?: $subscriber->first_name,
                'last_name' => $lastName ?: $subscriber->last_name,
                'phone' => $phone ?: $subscriber->phone,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'unsubscribe_reason' => null,
            ]);
        } else {
            // Create brand new subscriber
            $subscriber = Subscriber::create([
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'status' => 'subscribed',
                'source' => $source,
                'ip_address' => $request->ip(),
                'country' => $request->header('CF-IPCountry') ?: null,
                'subscribed_at' => now(),
            ]);
        }

        // Auto-assign to default General Newsletter group
        $defaultGroup = SubscriberGroup::where('slug', 'general-newsletter')->first();
        if ($defaultGroup && !$subscriber->groups()->where('subscriber_groups.id', $defaultGroup->id)->exists()) {
            $subscriber->groups()->attach($defaultGroup->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Welcome aboard! Thank you for subscribing to our exclusive updates.',
        ]);
    }

    /**
     * Show Unsubscribe Confirmation Screen.
     */
    public function unsubscribe(string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return response()->view('subscribers.unsubscribe', [
                'subscriber' => null,
                'message' => 'Invalid or expired unsubscribe link.',
            ], 404);
        }

        return view('subscribers.unsubscribe', [
            'subscriber' => $subscriber,
            'token' => $token,
            'alreadyUnsubscribed' => $subscriber->status === 'unsubscribed',
        ]);
    }

    /**
     * Process Unsubscribe Request.
     */
    public function processUnsubscribe(Request $request, string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return response()->view('subscribers.unsubscribe', [
                'subscriber' => null,
                'message' => 'Invalid or expired unsubscribe link.',
            ], 404);
        }

        $reason = $request->input('reason', 'User requested opt-out');
        if ($request->filled('other_reason')) {
            $reason .= ': ' . trim($request->input('other_reason'));
        }

        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                'unsubscribe_reason' => Str::limit($reason, 250),
            ]);

            // If an active campaign log is associated, increment campaign unsubscribed count
            $log = EmailCampaignLog::where('subscriber_id', $subscriber->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($log && $log->campaign) {
                $log->campaign->increment('unsubscribed_count');
            }
        }

        return view('subscribers.unsubscribe', [
            'subscriber' => $subscriber,
            'token' => $token,
            'completed' => true,
        ]);
    }

    /**
     * Stream 1x1 Invisible GIF Tracking Pixel for Email Opens.
     */
    public function trackOpen(string $token): Response
    {
        // 43-byte transparent 1x1 GIF binary
        $pixel = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');

        if (!empty($token)) {
            try {
                $log = EmailCampaignLog::where('tracking_token', $token)->first();
                if ($log) {
                    $firstOpen = $log->open_count === 0;

                    $log->increment('open_count');
                    $log->update([
                        'status' => in_array($log->status, ['clicked', 'opened']) ? $log->status : 'opened',
                        'opened_at' => $log->opened_at ?: now(),
                        'ip_address' => request()->ip(),
                        'user_agent' => Str::limit(request()->userAgent(), 500),
                    ]);

                    if ($firstOpen && $log->campaign) {
                        $log->campaign->increment('unique_opens');
                    }
                    if ($log->campaign) {
                        $log->campaign->increment('opened_count');
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Track open failed for token [{$token}]: " . $e->getMessage());
            }
        }

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
        ]);
    }

    /**
     * Track Link Click & 302 Redirect to Destination URL.
     */
    public function trackClick(Request $request, string $token): RedirectResponse
    {
        $targetUrl = $request->query('url', url('/'));

        // Basic URL validation
        if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            $targetUrl = url('/');
        }

        if (!empty($token)) {
            try {
                $log = EmailCampaignLog::where('tracking_token', $token)->first();
                if ($log) {
                    $firstClick = $log->click_count === 0;

                    $log->increment('click_count');
                    $log->update([
                        'status' => 'clicked',
                        'clicked_at' => $log->clicked_at ?: now(),
                        'opened_at' => $log->opened_at ?: now(),
                    ]);

                    if ($firstClick && $log->campaign) {
                        $log->campaign->increment('unique_clicks');
                    }
                    if ($log->campaign) {
                        $log->campaign->increment('clicked_count');
                    }

                    // Record click event
                    EmailCampaignClick::create([
                        'campaign_log_id' => $log->id,
                        'url' => $targetUrl,
                        'clicked_at' => now(),
                        'ip_address' => $request->ip(),
                        'user_agent' => Str::limit($request->userAgent(), 500),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Track click failed for token [{$token}]: " . $e->getMessage());
            }
        }

        return redirect()->away($targetUrl);
    }
}
