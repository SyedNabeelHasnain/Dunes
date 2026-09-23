<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use App\Models\EmailTemplate;
use App\Models\Subscriber;
use App\Models\SubscriberGroup;
use App\Services\EmailMarketingService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminEmailCampaignController extends Controller
{
    protected EmailMarketingService $emailService;
    protected SettingsService $settings;

    public function __construct(EmailMarketingService $emailService, SettingsService $settings)
    {
        $this->emailService = $emailService;
        $this->settings = $settings;
    }

    /**
     * Display campaigns dashboard.
     */
    public function index(Request $request)
    {
        $query = EmailCampaign::with(['group', 'template'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->paginate(15)->withQueryString();

        $stats = [
            'total_campaigns' => EmailCampaign::count(),
            'total_sent' => EmailCampaign::sum('sent_count'),
            'total_opened' => EmailCampaign::sum('opened_count'),
            'total_clicked' => EmailCampaign::sum('clicked_count'),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'stats'));
    }

    /**
     * Show create campaign form / wizard.
     */
    public function create()
    {
        $templates = EmailTemplate::latest()->get();
        $groups = SubscriberGroup::withCount([
            'subscribers as active_count' => function ($q) {
                $q->where('status', 'subscribed');
            }
        ])->get();
        $totalActiveSubscribers = Subscriber::where('status', 'subscribed')->count();

        $defaultFromAddress = $this->settings->get('smtp_from_address', config('mail.from.address'));
        $defaultFromName = $this->settings->get('smtp_from_name', $this->settings->get('site_name', 'Dunes Discovery Tourism'));
        $defaultReplyTo = $this->settings->get('smtp_reply_to', $defaultFromAddress);

        return view('admin.campaigns.create', compact(
            'templates', 'groups', 'totalActiveSubscribers',
            'defaultFromAddress', 'defaultFromName', 'defaultReplyTo'
        ));
    }

    /**
     * Store new campaign.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'from_name' => 'nullable|string|max:150',
            'from_email' => 'nullable|email:filter|max:255',
            'reply_to' => 'nullable|email:filter|max:255',
            'template_id' => 'nullable|exists:email_templates,id',
            'target_type' => 'required|in:all,group',
            'group_id' => 'nullable|required_if:target_type,group|exists:subscriber_groups,id',
            'content_html' => 'required|string',
            'action' => 'required|in:save_draft,send_now,schedule',
            'scheduled_at' => 'nullable|required_if:action,schedule|date',
        ]);

        $status = 'draft';
        $scheduledAt = null;

        if ($validated['action'] === 'schedule' && !empty($validated['scheduled_at'])) {
            $status = 'scheduled';
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
        }

        $campaign = EmailCampaign::create([
            'title' => trim($validated['title']),
            'subject' => trim($validated['subject']),
            'preview_text' => $validated['preview_text'] ?? null,
            'from_name' => $validated['from_name'] ?? $this->settings->get('smtp_from_name'),
            'from_email' => $validated['from_email'] ?? $this->settings->get('smtp_from_address'),
            'reply_to' => $validated['reply_to'] ?? $this->settings->get('smtp_reply_to'),
            'template_id' => $validated['template_id'] ?? null,
            'target_type' => $validated['target_type'],
            'group_id' => $validated['target_type'] === 'group' ? $validated['group_id'] : null,
            'content_html' => $validated['content_html'],
            'status' => $status,
            'scheduled_at' => $scheduledAt,
        ]);

        if ($validated['action'] === 'send_now') {
            $batchSize = (int) $this->settings->get('newsletter_batch_size', '50');
            $batchDelay = (int) $this->settings->get('newsletter_batch_delay', '1');

            $res = $this->emailService->dispatchCampaign($campaign, $batchSize, $batchDelay);

            return redirect()->route('admin.campaigns.show', $campaign->id)
                ->with('success', "Campaign dispatched to {$res['sent']} recipient(s)!");
        }

        if ($validated['action'] === 'schedule') {
            return redirect()->route('admin.campaigns.show', $campaign->id)
                ->with('success', "Campaign '{$campaign->title}' scheduled for broadcast on " . ($scheduledAt ? $scheduledAt->format('M d, Y h:i A') : 'scheduled time') . ".");
        }

        return redirect()->route('admin.campaigns.show', $campaign->id)
            ->with('success', "Campaign '{$campaign->title}' saved as draft.");
    }

    /**
     * Show detailed Campaign Analytics & Recipient Logs.
     */
    public function show(Request $request, int $id)
    {
        $campaign = EmailCampaign::with(['group', 'template'])->findOrFail($id);

        $logsQuery = EmailCampaignLog::with(['subscriber', 'clicks'])
            ->where('campaign_id', $campaign->id)
            ->latest();

        if ($request->filled('log_status')) {
            $logsQuery->where('status', $request->log_status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $logsQuery->whereHas('subscriber', function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%");
            });
        }

        $logs = $logsQuery->paginate(25)->withQueryString();

        return view('admin.campaigns.show', compact('campaign', 'logs'));
    }

    /**
     * Send an existing draft campaign immediately.
     */
    public function send(int $id): RedirectResponse
    {
        $campaign = EmailCampaign::findOrFail($id);

        if (in_array($campaign->status, ['sent', 'sending'])) {
            return back()->with('error', "Campaign is already {$campaign->status}.");
        }

        $batchSize = (int) $this->settings->get('newsletter_batch_size', '50');
        $batchDelay = (int) $this->settings->get('newsletter_batch_delay', '1');

        $res = $this->emailService->dispatchCampaign($campaign, $batchSize, $batchDelay);

        return redirect()->route('admin.campaigns.show', $campaign->id)
            ->with('success', "Campaign dispatched to {$res['sent']} recipient(s)!");
    }

    /**
     * Send a single diagnostic test email of this campaign.
     */
    public function sendTest(Request $request, int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);

        $request->validate([
            'test_email' => 'required|email:filter|max:255',
        ]);

        $recipient = trim($request->input('test_email'));
        $res = $this->emailService->sendTestEmail($recipient, $campaign->subject, $campaign->content_html);

        return response()->json($res);
    }

    /**
     * Delete campaign.
     */
    public function destroy(int $id): RedirectResponse
    {
        $campaign = EmailCampaign::findOrFail($id);
        $title = $campaign->title;
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', "Campaign '{$title}' deleted successfully.");
    }

    /**
     * Redirect edit requests to campaign show/analytics view.
     */
    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('admin.campaigns.show', $id);
    }
}
