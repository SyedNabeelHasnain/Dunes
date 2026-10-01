<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Translation\TranslationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTranslationSettingController extends Controller
{
    protected TranslationManager $manager;

    public function __construct(TranslationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Show Translation Services Configuration UI.
     */
    public function index(): View
    {
        $settings = Setting::whereIn('setting_key', [
            'translation_active_service',
            'deepl_auth_key',
            'deepl_endpoint_type',
            'google_translate_api_key',
        ])->pluck('setting_value', 'setting_key')->toArray();

        $activeService = strtolower(trim($settings['translation_active_service'] ?? 'none'));

        return view('admin.settings.translations', compact('settings', 'activeService'));
    }

    /**
     * Update Translation Services Configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'translation_active_service' => 'required|in:none,deepl,google',
            'deepl_auth_key' => 'nullable|string|max:255',
            'deepl_endpoint_type' => 'nullable|in:free,pro',
            'google_translate_api_key' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value ?? '']
            );
        }

        $this->manager->clearCache();
        app(\App\Services\SettingsService::class)->clearCache();

        return redirect()->route('admin.settings.translations')
            ->with('success', 'Translation service settings updated successfully.');
    }

    /**
     * Test connection to a specific translation driver.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:deepl,google',
            'auth_key' => 'nullable|string',
            'endpoint_type' => 'nullable|string',
            'api_key' => 'nullable|string',
        ]);

        $driverName = $validated['driver'];

        try {
            // Instantiate driver directly with provided test keys or from database
            if ($driverName === 'deepl') {
                $authKey = ! empty($validated['auth_key'])
                    ? $validated['auth_key']
                    : (Setting::where('setting_key', 'deepl_auth_key')->value('setting_value') ?? '');
                $endpointType = ! empty($validated['endpoint_type'])
                    ? $validated['endpoint_type']
                    : (Setting::where('setting_key', 'deepl_endpoint_type')->value('setting_value') ?? 'free');

                $driver = new \App\Services\Translation\Drivers\DeepLTranslationDriver($authKey, $endpointType);
            } else {
                $apiKey = ! empty($validated['api_key'])
                    ? $validated['api_key']
                    : (Setting::where('setting_key', 'google_translate_api_key')->value('setting_value') ?? '');

                $driver = new \App\Services\Translation\Drivers\GoogleTranslationDriver($apiKey);
            }

            $result = $driver->testConnection();

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger catalog translation via Artisan command.
     */
    public function translateCatalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'force' => 'nullable|boolean',
            'locale' => 'nullable|string|max:10',
            'model' => 'nullable|string|max:50',
        ]);

        try {
            $params = [];
            if (! empty($validated['force'])) {
                $params['--force'] = true;
            }
            if (! empty($validated['locale'])) {
                $params['--locale'] = $validated['locale'];
            }
            if (! empty($validated['model'])) {
                $params['--model'] = $validated['model'];
            }

            \Illuminate\Support\Facades\Artisan::call('app:translate-catalog', $params);
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Global catalog translation executed successfully.',
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Catalog translation error: '.$e->getMessage(),
            ], 500);
        }
    }
}
