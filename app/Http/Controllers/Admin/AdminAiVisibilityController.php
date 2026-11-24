<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCitationObservation;
use App\Models\RequestLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAiVisibilityController extends Controller
{
    private const AI_BOT_TOKENS = [
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',
        'ClaudeBot',
        'PerplexityBot',
        'Google-Extended',
        'CCBot',
        'Bytespider',
    ];

    private const AI_REFERRER_DOMAINS = [
        'chatgpt.com',
        'chat.openai.com',
        'perplexity.ai',
        'claude.ai',
        'gemini.google.com',
        'copilot.microsoft.com',
    ];

    public function index(): View
    {
        $botHits = [];
        $aiReferrals = collect();

        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                foreach (self::AI_BOT_TOKENS as $bot) {
                    $botHits[$bot] = RequestLog::where('user_agent', 'like', "%{$bot}%")
                        ->where('request_timestamp', '>=', now()->subDays(30))
                        ->count();
                }

                $aiReferrals = RequestLog::where(function ($q) {
                    foreach (self::AI_REFERRER_DOMAINS as $domain) {
                        $q->orWhere('referrer_url', 'like', "%{$domain}%");
                    }
                })
                    ->where('request_timestamp', '>=', now()->subDays(30))
                    ->select('referrer_url', DB::raw('COUNT(*) as visits'), DB::raw('MAX(request_timestamp) as last_visit'))
                    ->groupBy('referrer_url')
                    ->orderByDesc('visits')
                    ->limit(25)
                    ->get();
            }
        } catch (\Throwable) {
        }

        $observations = AiCitationObservation::latest('observed_at')->paginate(20);

        return view('admin.ai-visibility.index', compact('botHits', 'aiReferrals', 'observations'));
    }

    public function storeObservation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(['chatgpt', 'perplexity', 'claude', 'gemini', 'google_ai_overview', 'copilot'])],
            'prompt_query' => ['required', 'string', 'max:255'],
            'cited_url' => ['nullable', 'string', 'max:500'],
            'brand_mentioned' => ['nullable', 'boolean'],
            'citation_position' => ['nullable', 'integer', 'min:1', 'max:20'],
            'sentiment' => ['required', Rule::in(['positive', 'neutral', 'negative'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'observed_at' => ['nullable', 'date'],
        ]);

        AiCitationObservation::create([
            'platform' => $validated['platform'],
            'prompt_query' => $validated['prompt_query'],
            'cited_url' => $validated['cited_url'] ?? null,
            'brand_mentioned' => $request->boolean('brand_mentioned', true),
            'citation_position' => $validated['citation_position'] ?? null,
            'sentiment' => $validated['sentiment'],
            'notes' => $validated['notes'] ?? null,
            'observed_at' => $validated['observed_at'] ?? now(),
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.ai-visibility.index')->with('success', 'Observed AI citation benchmark recorded.');
    }

    public function destroyObservation(int $id): RedirectResponse
    {
        AiCitationObservation::findOrFail($id)->delete();

        return redirect()->route('admin.ai-visibility.index')->with('success', 'Citation observation removed.');
    }
}
