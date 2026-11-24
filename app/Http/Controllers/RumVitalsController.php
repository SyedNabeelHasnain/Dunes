<?php

namespace App\Http\Controllers;

use App\Models\WebVitalsMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RumVitalsController extends Controller
{
    public const BUDGETS = [
        'LCP' => ['good' => 2500.0, 'poor' => 4000.0],
        'INP' => ['good' => 200.0, 'poor' => 500.0],
        'CLS' => ['good' => 0.1, 'poor' => 0.25],
        'FCP' => ['good' => 1800.0, 'poor' => 3000.0],
        'TTFB' => ['good' => 800.0, 'poor' => 1800.0],
    ];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_path' => ['required', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:128'],
            'locale' => ['nullable', 'string', 'max:12'],
            'device_type' => ['nullable', 'in:mobile,tablet,desktop'],
            'metric_name' => ['required', Rule::in(array_keys(self::BUDGETS))],
            'metric_value' => ['required', 'numeric', 'min:0', 'max:120000'],
            'navigation_type' => ['nullable', 'string', 'max:32'],
            'connection_type' => ['nullable', 'string', 'max:32'],
        ]);

        $metric = $validated['metric_name'];
        $value = (float) $validated['metric_value'];
        $threshold = self::BUDGETS[$metric];
        $rating = $value <= $threshold['good']
            ? 'good'
            : ($value <= $threshold['poor'] ? 'needs-improvement' : 'poor');

        WebVitalsMetric::create([
            'page_path' => '/'.ltrim((string) parse_url($validated['page_path'], PHP_URL_PATH), '/'),
            'route_name' => $validated['route_name'] ?? null,
            'locale' => $validated['locale'] ?? (app()->getLocale() ?: 'en'),
            'device_type' => $validated['device_type'] ?? 'desktop',
            'metric_name' => $metric,
            'metric_value' => $value,
            'rating' => $rating,
            'navigation_type' => $validated['navigation_type'] ?? null,
            'connection_type' => $validated['connection_type'] ?? null,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'recorded_at' => now(),
        ]);

        return response()->json(['success' => true, 'rating' => $rating], 201);
    }
}
