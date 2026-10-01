<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Localization\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLanguageController extends Controller
{
    protected LocaleManager $localeManager;

    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Display language management interface.
     */
    public function index(): View
    {
        $languages = Language::orderBy('is_default', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.settings.languages', compact('languages'));
    }

    /**
     * Store and build a brand-new language locale.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|min:2|max:10|regex:/^[a-zA-Z\-]+$/|unique:languages,code',
            'name' => 'required|string|max:100',
            'native_name' => 'required|string|max:100',
            'direction' => 'required|in:ltr,rtl',
            'flag' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $code = strtolower(trim($validated['code']));

        $language = Language::create([
            'code' => $code,
            'name' => trim($validated['name']),
            'native_name' => trim($validated['native_name']),
            'direction' => $validated['direction'],
            'flag' => trim($validated['flag'] ?? ''),
            'is_default' => false,
            'is_active' => true,
            'sort_order' => (int) ($validated['sort_order'] ?? 10),
        ]);

        // Build the 100% complete locale ecosystem immediately
        $this->localeManager->buildLocale($code);

        return redirect()->route('admin.settings.languages')
            ->with('success', "Language [{$language->name}] added and complete locale ecosystem built successfully.");
    }

    /**
     * Update an existing language.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $language = Language::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:100',
            'native_name' => 'required|string|max:100',
            'direction' => 'required|in:ltr,rtl',
            'flag' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ];

        // English cannot change code or direction
        if ($language->code !== 'en') {
            $rules['is_active'] = 'nullable|boolean';
        }

        $validated = $request->validate($rules);

        $language->name = trim($validated['name']);
        $language->native_name = trim($validated['native_name']);
        $language->direction = ($language->code === 'en') ? 'ltr' : $validated['direction'];
        $language->flag = trim($validated['flag'] ?? '');
        $language->sort_order = (int) ($validated['sort_order'] ?? 0);

        if ($language->code !== 'en' && isset($validated['is_active'])) {
            $language->is_active = (bool) $validated['is_active'];
        }

        $language->save();

        // Ensure locale files are intact
        $this->localeManager->buildLocale($language->code);

        return redirect()->route('admin.settings.languages')
            ->with('success', "Language [{$language->name}] updated successfully.");
    }

    /**
     * Delete language and purge its complete locale data.
     * English is permanently protected.
     */
    public function destroy(int $id): RedirectResponse
    {
        $language = Language::findOrFail($id);

        if ($language->code === 'en' || $language->is_default) {
            return redirect()->route('admin.settings.languages')
                ->with('error', 'The default language (English) is protected and cannot be deleted.');
        }

        $code = $language->code;
        $name = $language->name;

        // Delete from database
        $language->delete();

        // Purge complete locale directory and files
        $this->localeManager->removeLocale($code);

        return redirect()->route('admin.settings.languages')
            ->with('success', "Language [{$name}] and its entire locale ecosystem were permanently purged.");
    }

    /**
     * Toggle language active status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $language = Language::findOrFail($id);

        if ($language->code === 'en') {
            return redirect()->route('admin.settings.languages')
                ->with('error', 'The default language (English) cannot be deactivated.');
        }

        $language->is_active = ! $language->is_active;
        $language->save();

        return redirect()->route('admin.settings.languages')
            ->with('success', "Status for [{$language->name}] updated to ".($language->is_active ? 'Active' : 'Inactive').'.');
    }
}
