<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoTemplate;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSeoTemplatesController extends Controller
{
    public function __construct(
        protected ConfigAuditService $audit,
    ) {}

    public function index(): View
    {
        SeoTemplate::ensureDefaultsExist();
        $templates = SeoTemplate::orderBy('id')->get();
        $defaults = SeoTemplate::defaultDefinitions();

        return view('admin.settings.seo-templates', compact('templates', 'defaults'));
    }

    public function update(Request $request, string $contentType): RedirectResponse
    {
        SeoTemplate::ensureDefaultsExist();
        $template = SeoTemplate::where('content_type', $contentType)->firstOrFail();

        $validated = $request->validate([
            'title_pattern' => ['nullable', 'string', 'max:255'],
            'description_pattern' => ['nullable', 'string', 'max:1000'],
            'keywords_pattern' => ['nullable', 'string', 'max:1000'],
            'robots_default' => ['required', 'string', 'max:128'],
            'og_type_default' => ['required', 'string', 'max:32'],
            'schema_type_default' => ['nullable', 'string', 'max:64'],
            'sitemap_priority' => ['required', 'numeric', 'min:0.1', 'max:1.0'],
            'sitemap_changefreq' => ['required', 'in:always,hourly,daily,weekly,monthly,yearly,never'],
            'sitemap_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $old = $template->only(array_keys($validated));

        $template->update([
            'title_pattern' => $validated['title_pattern'] ?? $template->title_pattern,
            'description_pattern' => $validated['description_pattern'] ?? $template->description_pattern,
            'keywords_pattern' => $validated['keywords_pattern'] ?? $template->keywords_pattern,
            'robots_default' => $validated['robots_default'],
            'og_type_default' => $validated['og_type_default'],
            'schema_type_default' => $validated['schema_type_default'] ?? $template->schema_type_default,
            'sitemap_priority' => $validated['sitemap_priority'],
            'sitemap_changefreq' => $validated['sitemap_changefreq'],
            'sitemap_enabled' => $request->boolean('sitemap_enabled'),
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ]);

        $this->audit->log(
            section: 'seo_template',
            action: 'updated',
            settingKey: "seo_template:{$contentType}",
            oldValue: $old,
            newValue: $template->only(array_keys($validated)),
            entityType: SeoTemplate::class,
            entityId: $template->id,
            summary: "Updated SEO inheritance template for {$template->label}",
        );

        return redirect()->route('admin.settings.seo-templates.index')->with('success', "SEO template '{$template->label}' updated.");
    }

    public function reset(string $contentType): RedirectResponse
    {
        $template = SeoTemplate::where('content_type', $contentType)->firstOrFail();
        $defaults = SeoTemplate::defaultDefinitions();

        if (! isset($defaults[$contentType])) {
            return back()->withErrors(['template' => 'No built-in default found for this content type.']);
        }

        $old = $template->toArray();
        $template->update(array_merge($defaults[$contentType], ['is_active' => true, 'updated_by' => auth()->id()]));

        $this->audit->log(
            section: 'seo_template',
            action: 'reset_to_default',
            settingKey: "seo_template:{$contentType}",
            oldValue: $old,
            newValue: $defaults[$contentType],
            entityType: SeoTemplate::class,
            entityId: $template->id,
            summary: "Reset SEO template '{$template->label}' back to system defaults",
        );

        return redirect()->route('admin.settings.seo-templates.index')->with('success', "Reset '{$template->label}' to baseline defaults.");
    }
}
