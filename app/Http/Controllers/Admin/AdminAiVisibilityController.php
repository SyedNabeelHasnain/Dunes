<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCitationObservation;
use App\Models\RequestLog;
use App\Services\Seo\CrawlerLogAnalyzerService;
use App\Services\Seo\SearchConsoleIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAiVisibilityController extends Controller
{
    public function __construct(
        protected CrawlerLogAnalyzerService $crawlerAnalyzer,
        protected SearchConsoleIntegrationService $searchConsole,
    ) {}
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

    public function index(Request $request): View
    {
        $days = max(1, min(90, (int) $request->input('days', 30)));
        $botHits = [];
        $aiReferrals = collect();

        try {
            if (class_exists(RequestLog::class) && Schema::hasTable('request_logs')) {
                foreach (self::AI_BOT_TOKENS as $bot) {
                    $botHits[$bot] = RequestLog::where('user_agent', 'like', "%{$bot}%")
                        ->where('request_timestamp', '>=', now()->subDays($days))
                        ->count();
                }

                $aiReferrals = RequestLog::whereNotNull('referrer_url')
                    ->where('referrer_url', '!=', '')
                    ->where(function ($q) {
                        foreach (self::AI_REFERRER_DOMAINS as $domain) {
                            $q->orWhere('referrer_url', 'like', "%{$domain}%");
                        }
                    })
                    ->where('request_timestamp', '>=', now()->subDays($days))
                    ->select('referrer_url', DB::raw('COUNT(*) as visits'), DB::raw('MAX(request_timestamp) as last_visit'))
                    ->groupBy('referrer_url')
                    ->orderByDesc('visits')
                    ->limit(25)
                    ->get();
            }
        } catch (\Throwable) {
        }

        $observations = AiCitationObservation::latest('observed_at')->paginate(20);
        $crawlReport = $this->crawlerAnalyzer->analyze($days);
        $searchConsoleStatus = $this->searchConsole->getStatusSummary();

        return view('admin.ai-visibility.index', compact(
            'botHits',
            'aiReferrals',
            'observations',
            'crawlReport',
            'searchConsoleStatus',
            'days'
        ));
    }

    public function testSearchConsole(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(['google', 'bing'])],
        ]);

        $result = $validated['provider'] === 'google'
            ? $this->searchConsole->fetchGoogleSearchAnalytics(28)
            : $this->searchConsole->fetchBingCrawlStats();

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            $count = count($result['rows'] ?? $result['stats'] ?? []);

            return back()->with('success', strtoupper($validated['provider'])." Search Console API connected cleanly ({$count} records returned).");
        }

        return back()->with('error', (string) ($result['message'] ?? 'Search Console API is awaiting valid credentials.'));
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
