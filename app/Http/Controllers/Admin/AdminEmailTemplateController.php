<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Subscriber;
use App\Services\EmailMarketingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AdminEmailTemplateController extends Controller
{
    protected EmailMarketingService $emailService;

    public function __construct(EmailMarketingService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display templates gallery.
     */
    public function index()
    {
        $templates = EmailTemplate::withCount('campaigns')->latest()->get();

        return view('admin.email-templates.index', compact('templates'));
    }

    /**
     * Show create template form.
     */
    public function create()
    {
        return view('admin.email-templates.edit', ['template' => new EmailTemplate]);
    }

    /**
     * Store new template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'content_html' => 'required|string',
            'content_plain' => 'nullable|string',
        ]);

        $template = EmailTemplate::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'subject' => trim($validated['subject']),
            'preview_text' => $validated['preview_text'] ?? null,
            'content_html' => $validated['content_html'],
            'content_plain' => $validated['content_plain'] ?? null,
            'is_system' => false,
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', "Template '{$template->name}' created successfully.");
    }

    /**
     * Show edit template form.
     */
    public function edit(int $id)
    {
        $template = EmailTemplate::findOrFail($id);

        return view('admin.email-templates.edit', compact('template'));
    }

    /**
     * Update template.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'content_html' => 'required|string',
            'content_plain' => 'nullable|string',
        ]);

        $template->update([
            'name' => trim($validated['name']),
            'subject' => trim($validated['subject']),
            'preview_text' => $validated['preview_text'] ?? null,
            'content_html' => $validated['content_html'],
            'content_plain' => $validated['content_plain'] ?? null,
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', "Template '{$template->name}' updated successfully.");
    }

    /**
     * Delete template.
     */
    public function destroy(int $id): RedirectResponse
    {
        $template = EmailTemplate::findOrFail($id);

        if ($template->is_system) {
            return back()->with('error', "System template '{$template->name}' cannot be deleted.");
        }

        $name = $template->name;
        $template->delete();

        return redirect()->route('admin.email-templates.index')
            ->with('success', "Template '{$name}' deleted successfully.");
    }

    /**
     * Live Preview of Email Template in Iframe.
     */
    public function preview(int $id): Response
    {
        $template = EmailTemplate::findOrFail($id);

        $dummy = new Subscriber([
            'email' => 'alex.turner@example.com',
            'first_name' => 'Alex',
            'last_name' => 'Turner',
            'unsubscribe_token' => 'sample-preview-token',
        ]);

        $html = $this->emailService->renderHtml($template->content_html, $dummy);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
