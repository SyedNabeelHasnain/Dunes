<?php

use App\Http\Controllers\Admin\AdminAiVisibilityController;
use App\Http\Controllers\Admin\AdminAuditController;
use App\Http\Controllers\Admin\AdminEditorialController;
use App\Http\Controllers\Admin\AdminGovernanceController;
use App\Http\Controllers\Admin\AdminKeywordController;
use App\Http\Controllers\Admin\AdminPrivacyController;
use App\Http\Controllers\Admin\AdminRedirectController;
use App\Http\Controllers\Admin\AdminRobotsController;
use App\Http\Controllers\Admin\AdminSecurityController;
use App\Http\Controllers\Admin\AdminSeoTemplatesController;
use App\Http\Controllers\Admin\AdminSystemHealthController;
use App\Http\Controllers\Admin\AdminUserRoleController;
use App\Http\Controllers\RobotsTxtController;
use App\Http\Middleware\AdminNoCacheMiddleware;
use Illuminate\Support\Facades\Route;

// ── Public Dynamic robots.txt Endpoint ──────────────────────────────────────
Route::get('/robots.txt', [RobotsTxtController::class, 'show'])->name('robots.txt');

// ── Admin Governance, RBAC, Audit Logs & Approval Workflows ─────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminNoCacheMiddleware::class])->group(function () {
    Route::get('/governance', [AdminGovernanceController::class, 'index'])
        ->middleware('permission:governance.audit_logs,audit.view,seo.view')
        ->name('governance.index');

    Route::get('/governance/logs/export', [AdminGovernanceController::class, 'exportLogs'])
        ->middleware('permission:governance.audit_logs')
        ->name('governance.logs.export');

    Route::post('/governance/approvals/{id}/approve', [AdminGovernanceController::class, 'approve'])
        ->middleware('permission:governance.approve')
        ->name('governance.approvals.approve');

    Route::post('/governance/approvals/{id}/reject', [AdminGovernanceController::class, 'reject'])
        ->middleware('permission:governance.approve')
        ->name('governance.approvals.reject');

    Route::prefix('governance/users')->name('governance.users.')->middleware('permission:governance.rbac')->group(function () {
        Route::get('/', [AdminUserRoleController::class, 'index'])->name('index');
        Route::post('/', [AdminUserRoleController::class, 'store'])->name('store');
        Route::post('/{user}/role', [AdminUserRoleController::class, 'updateRole'])->name('update-role');
        Route::post('/{user}/toggle', [AdminUserRoleController::class, 'toggleActive'])->name('toggle');
    });

    // ── Technical SEO, Inheritance Templates, Keywords & Robots.txt ─────────
    Route::get('/settings/robots', [AdminRobotsController::class, 'index'])
        ->middleware('permission:robots.request_approval,robots.publish,seo.manage')
        ->name('settings.robots.index');
    Route::post('/settings/robots', [AdminRobotsController::class, 'update'])
        ->middleware('permission:robots.request_approval,robots.publish,seo.manage')
        ->name('settings.robots.update');

    Route::get('/settings/seo-templates', [AdminSeoTemplatesController::class, 'index'])
        ->middleware('permission:seo.templates,seo.manage')
        ->name('settings.seo-templates.index');
    Route::post('/settings/seo-templates/{contentType}', [AdminSeoTemplatesController::class, 'update'])
        ->middleware('permission:seo.templates,seo.manage')
        ->name('settings.seo-templates.update');
    Route::post('/settings/seo-templates/{contentType}/reset', [AdminSeoTemplatesController::class, 'reset'])
        ->middleware('permission:seo.templates,seo.manage')
        ->name('settings.seo-templates.reset');

    Route::get('/settings/keywords', [AdminKeywordController::class, 'index'])
        ->middleware('permission:keywords.manage,seo.view')
        ->name('settings.keywords.index');

    Route::post('/settings/redirects/{id}/verify', [AdminRedirectController::class, 'verify'])
        ->middleware('permission:redirects.manage')
        ->name('settings.redirects.verify');

    // ── Per-Item Normalized SEO Metadata & Revision Rollback ─────────────────
    Route::post('/editorial/{type}/{id}/seo-meta', [AdminEditorialController::class, 'updateSeoMeta'])
        ->middleware('permission:seo.item_edit,content.edit')
        ->name('editorial.seo-meta.update');
    Route::post('/editorial/{type}/{id}/seo-meta/reset', [AdminEditorialController::class, 'resetSeoMeta'])
        ->middleware('permission:seo.item_edit,content.edit')
        ->name('editorial.seo-meta.reset');
    Route::post('/editorial/revisions/{revisionId}/restore', [AdminEditorialController::class, 'restoreRevision'])
        ->middleware('permission:content.edit,content.publish')
        ->name('editorial.revisions.restore');

    // ── Single-Page On-Demand Audit Drawer ──────────────────────────────────
    Route::post('/audit/page', [AdminAuditController::class, 'auditPage'])
        ->middleware('permission:audit.page_view,audit.run')
        ->name('audit.page');

    // ── Privacy & Consent Mode v2, DSAR, Security Headers, System Health & AI Visibility ──
    Route::get('/settings/privacy', [AdminPrivacyController::class, 'index'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.index');
    Route::post('/settings/privacy', [AdminPrivacyController::class, 'update'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.update');
    Route::get('/settings/privacy/dsar', [AdminPrivacyController::class, 'dsarLookup'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.dsar');
    Route::post('/settings/privacy/dsar-export', [AdminPrivacyController::class, 'dsarExport'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.dsar-export');
    Route::post('/settings/privacy/dsar-erase', [AdminPrivacyController::class, 'dsarErase'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.dsar-erase');
    Route::post('/settings/privacy/purge-expired', [AdminPrivacyController::class, 'purgeExpired'])
        ->middleware('permission:privacy.manage')
        ->name('settings.privacy.purge-expired');

    Route::post('/security/headers', [AdminSecurityController::class, 'updateHeaders'])
        ->middleware('permission:security.manage')
        ->name('security.headers.update');

    Route::get('/health', [AdminSystemHealthController::class, 'index'])
        ->middleware('permission:system_health.view,performance.manage')
        ->name('health.index');
    Route::post('/health/retry-failed', [AdminSystemHealthController::class, 'retryFailedJobs'])
        ->middleware('permission:system_health.view,performance.manage')
        ->name('health.retry');

    Route::get('/ai-visibility', [AdminAiVisibilityController::class, 'index'])
        ->middleware('permission:ai_visibility.manage,seo.view')
        ->name('ai-visibility.index');
    Route::get('/seo/crawl-analytics', [AdminAiVisibilityController::class, 'index'])
        ->middleware('permission:seo.view,seo.manage,system_health.view')
        ->name('seo.crawl-analytics');
    Route::post('/ai-visibility/search-console/test', [AdminAiVisibilityController::class, 'testSearchConsole'])
        ->middleware('permission:ai_visibility.manage,seo.manage')
        ->name('ai-visibility.search-console.test');
    Route::post('/ai-visibility/observations', [AdminAiVisibilityController::class, 'storeObservation'])
        ->middleware('permission:ai_visibility.manage')
        ->name('ai-visibility.observations.store');
    Route::delete('/ai-visibility/observations/{id}', [AdminAiVisibilityController::class, 'destroyObservation'])
        ->middleware('permission:ai_visibility.manage')
        ->name('ai-visibility.observations.destroy');
});
