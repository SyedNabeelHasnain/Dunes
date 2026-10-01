<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Translation\TranslationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTranslationApiController extends Controller
{
    protected TranslationManager $manager;

    public function __construct(TranslationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Batch or single field translation endpoint.
     */
    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'texts' => 'required|array',
            'target_locale' => 'required|string|max:10',
            'source_locale' => 'nullable|string|max:10',
            'html_fields' => 'nullable|array',
        ]);

        $texts = $validated['texts'];
        $targetLocale = strtolower(trim($validated['target_locale']));
        $sourceLocale = strtolower(trim($validated['source_locale'] ?? 'en'));
        $htmlFields = $validated['html_fields'] ?? [];

        // Validate target locale exists and is active
        $targetLang = Language::where('code', $targetLocale)->first();
        if (! $targetLang) {
            return response()->json([
                'success' => false,
                'message' => "Target locale '{$targetLocale}' is not registered in the system.",
            ], 422);
        }

        if (! $this->manager->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'No active translation service is configured. Please visit Settings > Translation Services to configure DeepL or Google Cloud Translation.',
            ], 422);
        }

        try {
            $driver = $this->manager->driver();
            $results = [];

            // Group into plain and HTML fields for optimal batch translation
            $plainBatch = [];
            $htmlBatch = [];

            foreach ($texts as $key => $val) {
                if (empty($val) || ! is_string($val)) {
                    $results[$key] = '';
                    continue;
                }

                if (in_array($key, $htmlFields, true)) {
                    $htmlBatch[$key] = $val;
                } else {
                    $plainBatch[$key] = $val;
                }
            }

            if (! empty($plainBatch)) {
                $translatedPlain = $driver->translate($plainBatch, $targetLocale, $sourceLocale, false);
                foreach ($translatedPlain as $k => $v) {
                    $results[$k] = $v;
                }
            }

            if (! empty($htmlBatch)) {
                $translatedHtml = $driver->translate($htmlBatch, $targetLocale, $sourceLocale, true);
                foreach ($translatedHtml as $k => $v) {
                    $results[$k] = $v;
                }
            }

            return response()->json([
                'success' => true,
                'translations' => $results,
                'provider' => $driver->getName(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test connection to a specific translation driver.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:deepl,google',
        ]);

        try {
            $driver = $this->manager->driver($validated['driver']);
            $result = $driver->testConnection();

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
