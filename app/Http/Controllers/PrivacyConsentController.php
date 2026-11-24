<?php

namespace App\Http\Controllers;

use App\Models\PrivacyConsent;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PrivacyConsentController extends Controller
{
    public function store(Request $request, SettingsService $settingsService): JsonResponse
    {
        $validated = $request->validate([
            'consent_id' => ['nullable', 'string', 'max:64'],
            'action' => ['required', Rule::in(['accept_all', 'reject_all', 'custom', 'withdraw'])],
            'analytics' => ['required', 'boolean'],
            'marketing' => ['required', 'boolean'],
            'functional' => ['required', 'boolean'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = $settingsService->all();
        $consentVersion = trim((string) ($settings['privacy_consent_version'] ?? '1.0')) ?: '1.0';
        $consentId = trim((string) ($validated['consent_id'] ?? '')) ?: (string) Str::uuid();

        $analytics = $validated['action'] === 'reject_all' || $validated['action'] === 'withdraw' ? false : (bool) $validated['analytics'];
        $marketing = $validated['action'] === 'reject_all' || $validated['action'] === 'withdraw' ? false : (bool) $validated['marketing'];
        $functional = $validated['action'] === 'reject_all' || $validated['action'] === 'withdraw' ? false : (bool) $validated['functional'];

        if ($validated['action'] === 'accept_all') {
            $analytics = true;
            $marketing = true;
            $functional = true;
        }

        $ipHash = hash_hmac('sha256', (string) ($request->ip() ?? '0.0.0.0'), (string) config('app.key'));

        PrivacyConsent::create([
            'consent_id' => $consentId,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'ip_hash' => $ipHash,
            'locale' => app()->getLocale() ?: 'en',
            'necessary' => true,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'functional' => $functional,
            'consent_version' => $consentVersion,
            'action' => $validated['action'],
            'page_url' => $validated['page_url'] ?? $request->headers->get('Referer'),
            'user_agent' => Str::limit((string) $request->userAgent(), 490),
            'consented_at' => now(),
        ]);

        $cookiePayload = [
            'id' => $consentId,
            'v' => $consentVersion,
            'necessary' => true,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'functional' => $functional,
            'updated_at' => now()->toIso8601String(),
        ];

        $cookie = cookie(
            name: 'ddt_consent_v1',
            value: json_encode($cookiePayload),
            minutes: 60 * 24 * 180,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: false,
            raw: false,
            sameSite: 'Lax',
        );

        return response()->json([
            'success' => true,
            'consent' => $cookiePayload,
        ])->withCookie($cookie);
    }
}
