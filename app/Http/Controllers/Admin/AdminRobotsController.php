<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Governance\ApprovalWorkflowService;
use App\Services\Governance\ConfigAuditService;
use App\Services\Seo\RobotsTxtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRobotsController extends Controller
{
    public function __construct(
        protected RobotsTxtService $robots,
        protected ConfigAuditService $audit,
        protected ApprovalWorkflowService $approvals,
    ) {}

    public function index(): View
    {
        $content = $this->robots->getCurrentContent();
        $botGroups = RobotsTxtService::BOT_GROUPS;
        $botStates = $this->robots->getBotStates($content);
        $validationIssues = $this->robots->validateContent($content);

        return view('admin.settings.robots', compact('content', 'botGroups', 'botStates', 'validationIssues'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => ['nullable', 'in:builder,raw'],
            'robots_txt_content' => ['nullable', 'string', 'max:20000'],
            'bots' => ['nullable', 'array'],
            'custom_disallows' => ['nullable', 'string', 'max:4000'],
        ]);

        $previousContent = $this->robots->getCurrentContent();

        if ($request->input('mode') === 'builder') {
            $newContent = $this->robots->compileFromBotPolicies(
                $request->input('bots', []),
                (string) $request->input('custom_disallows', '')
            );
        } else {
            $newContent = str_replace("\r\n", "\n", trim((string) $request->input('robots_txt_content', '')));
        }

        $validationErrors = $this->robots->validateContent($newContent);
        if ($validationErrors !== []) {
            return back()->withInput()->withErrors(['robots' => implode(' ', $validationErrors)]);
        }

        $user = $request->user();
        if ($user && method_exists($user, 'hasAnyPermission') && ! $user->hasAnyPermission(['robots.publish', 'governance.approve', '*'])) {
            $approval = $this->approvals->propose(
                section: 'robots_txt',
                targetKey: 'robots_txt_content',
                summary: 'Proposed robots.txt crawler policy update',
                payload: ['robots_txt_content' => $newContent],
                previousPayload: ['robots_txt_content' => $previousContent],
                requester: $user,
            );

            return redirect()->route('admin.settings.robots.index')
                ->with('success', "robots.txt change proposal (#{$approval->id}) submitted for administrator approval.");
        }

        $this->robots->persistContent($newContent);

        $this->audit->log(
            section: 'robots_txt',
            action: 'updated',
            settingKey: 'robots_txt_content',
            oldValue: $previousContent,
            newValue: $newContent,
            summary: 'Published updated robots.txt crawler & AI bot rules',
            actor: $user,
        );

        return redirect()->route('admin.settings.robots.index')->with('success', 'robots.txt directives updated and validated.');
    }
}
