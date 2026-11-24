<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\ContentRevision;
use App\Models\Page;
use App\Models\SeoMetadata;
use App\Models\Tour;
use App\Models\WikiArticle;
use App\Services\Governance\ConfigAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminEditorialController extends Controller
{
    public const ENTITY_MAP = [
        'tour' => Tour::class,
        'blog_post' => BlogPost::class,
        'page' => Page::class,
        'wiki_article' => WikiArticle::class,
    ];

    public function __construct(
        protected ConfigAuditService $audit,
    ) {}

    public function updateSeoMeta(Request $request, string $type, int $id): RedirectResponse
    {
        $modelClass = self::ENTITY_MAP[$type] ?? abort(404);
        /** @var Model $entity */
        $entity = $modelClass::findOrFail($id);

        $validated = $request->validate([
            'focus_keyword' => ['nullable', 'string', 'max:190'],
            'secondary_keywords' => ['nullable', 'string', 'max:1000'],
            'search_intent' => ['nullable', Rule::in(['informational', 'commercial', 'transactional', 'navigational'])],
            'canonical_override' => ['nullable', 'string', 'max:500'],
            'robots_directive' => ['nullable', 'string', 'max:128'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:1000'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'twitter_card' => ['nullable', Rule::in(['summary', 'summary_large_image'])],
            'schema_type_override' => ['nullable', 'string', 'max:64'],
            'schema_json_override' => ['nullable', 'string', 'max:25000'],
            'include_in_sitemap' => ['nullable', 'in:inherit,1,0'],
            'breadcrumb_label' => ['nullable', 'string', 'max:160'],
            'editorial_status' => ['required', Rule::in(['published', 'draft', 'in_review', 'approved', 'needs_revision'])],
            'review_notes' => ['nullable', 'string', 'max:1000'],
            'stale_after_days' => ['nullable', 'integer', 'min:30', 'max:1095'],
            'change_summary' => ['nullable', 'string', 'max:255'],
        ]);

        // Validate custom JSON-LD if provided
        $customJson = trim((string) ($validated['schema_json_override'] ?? ''));
        if ($customJson !== '') {
            json_decode($customJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['schema_json_override' => 'Custom JSON-LD syntax error: '.json_last_error_msg()]);
            }
        }

        $sitemapOverride = match ($validated['include_in_sitemap'] ?? 'inherit') {
            '1' => true,
            '0' => false,
            default => null,
        };

        $secondary = collect(explode(',', (string) ($validated['secondary_keywords'] ?? '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $user = $request->user();
        $editorialStatus = $validated['editorial_status'];
        if ($editorialStatus === 'approved' && $user && method_exists($user, 'hasAnyPermission') && ! $user->hasAnyPermission(['content.publish', 'governance.approve', '*'])) {
            $editorialStatus = 'in_review';
        }

        $meta = SeoMetadata::firstOrNew([
            'entity_type' => $modelClass,
            'entity_id' => $entity->id,
        ]);

        $oldMeta = $meta->exists ? $meta->toArray() : null;

        $meta->fill([
            'focus_keyword' => ($validated['focus_keyword'] ?? null) ?: null,
            'secondary_keywords' => $secondary ?: null,
            'search_intent' => ($validated['search_intent'] ?? null) ?: null,
            'canonical_override' => ($validated['canonical_override'] ?? null) ?: null,
            'robots_directive' => ($validated['robots_directive'] ?? null) ?: null,
            'og_title' => ($validated['og_title'] ?? null) ?: null,
            'og_description' => ($validated['og_description'] ?? null) ?: null,
            'og_image' => ($validated['og_image'] ?? null) ?: null,
            'twitter_card' => ($validated['twitter_card'] ?? null) ?: null,
            'schema_type_override' => ($validated['schema_type_override'] ?? null) ?: null,
            'schema_json_override' => $customJson !== '' ? $customJson : null,
            'include_in_sitemap' => $sitemapOverride,
            'breadcrumb_label' => ($validated['breadcrumb_label'] ?? null) ?: null,
            'editorial_status' => $editorialStatus,
            'review_notes' => ($validated['review_notes'] ?? null) ?: null,
            'stale_after_days' => $validated['stale_after_days'] ?? null,
        ]);

        if (in_array($editorialStatus, ['approved', 'published'], true)) {
            $meta->reviewed_by = $user?->id;
            $meta->reviewed_by_name = $user?->name;
            $meta->reviewed_at = now();
            $meta->approved_by = $user?->id;
            $meta->approved_at = now();
        }

        $meta->save();

        // Sync matching direct columns on BlogPost so legacy queries stay aligned
        if ($entity instanceof BlogPost) {
            $entity->update([
                'focus_keyword' => $meta->focus_keyword ?: $entity->focus_keyword,
                'robots' => $meta->robots_directive ?: $entity->robots,
                'canonical_url' => $meta->canonical_override ?: $entity->canonical_url,
                'og_title' => $meta->og_title ?: $entity->og_title,
                'og_desc' => $meta->og_description ?: $entity->og_desc,
                'og_image' => $meta->og_image ?: $entity->og_image,
            ]);
        }

        if (method_exists($entity, 'recordRevision')) {
            $entity->refresh();
            $entity->recordRevision(
                ($validated['change_summary'] ?? null) ?: 'Updated SEO & Editorial Metadata',
                $editorialStatus
            );
        }

        $this->audit->log(
            section: 'editorial',
            action: 'updated',
            settingKey: "{$type}:{$entity->id}",
            oldValue: $oldMeta,
            newValue: $meta->toArray(),
            entityType: $modelClass,
            entityId: $entity->id,
            summary: "Updated SEO metadata & editorial status ({$editorialStatus}) on {$type} #{$entity->id}",
            actor: $user,
        );

        Cache::forget('site_home_cache');
        SitemapController::flushAllSitemapCaches();

        return back()->with('success', 'SEO, Schema & Editorial metadata saved with revision snapshot.');
    }

    public function resetSeoMeta(string $type, int $id): RedirectResponse
    {
        $modelClass = self::ENTITY_MAP[$type] ?? abort(404);
        $entity = $modelClass::findOrFail($id);

        $meta = SeoMetadata::where('entity_type', $modelClass)->where('entity_id', $entity->id)->first();
        if ($meta) {
            $old = $meta->toArray();
            $meta->update([
                'canonical_override' => null,
                'robots_directive' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => null,
                'twitter_card' => null,
                'schema_type_override' => null,
                'schema_json_override' => null,
                'include_in_sitemap' => null,
            ]);

            $this->audit->log(
                section: 'editorial',
                action: 'reset_to_default',
                settingKey: "{$type}:{$entity->id}",
                oldValue: $old,
                newValue: $meta->fresh()->toArray(),
                entityType: $modelClass,
                entityId: $entity->id,
                summary: "Reset item SEO overrides on {$type} #{$entity->id} to template/global defaults",
            );
        }

        SitemapController::flushAllSitemapCaches();

        return back()->with('success', 'Item SEO overrides cleared — now inheriting Content Type Template & Global Defaults.');
    }

    public function restoreRevision(int $revisionId): RedirectResponse
    {
        $revision = ContentRevision::findOrFail($revisionId);
        $modelClass = $revision->entity_type;

        if (! class_exists($modelClass)) {
            abort(404);
        }

        $entity = $modelClass::findOrFail($revision->entity_id);
        $snapshot = is_array($revision->snapshot) ? $revision->snapshot : [];
        $attrs = is_array($snapshot['attributes'] ?? null) ? $snapshot['attributes'] : [];

        // Record current state before rolling back so rollback itself is reversible
        if (method_exists($entity, 'recordRevision')) {
            $entity->recordRevision("Pre-rollback checkpoint before restoring v{$revision->version_number}");
        }

        $fillable = method_exists($entity, 'getFillable') ? $entity->getFillable() : [];
        $restorable = array_intersect_key($attrs, array_flip($fillable));

        if ($restorable !== []) {
            $entity->forceFill($restorable)->save();
        }

        if (is_array($snapshot['seo_meta'] ?? null) && $snapshot['seo_meta'] !== []) {
            $metaAttrs = collect($snapshot['seo_meta'])->except(['id', 'entity_type', 'entity_id', 'created_at', 'updated_at'])->all();
            SeoMetadata::updateOrCreate(
                ['entity_type' => $modelClass, 'entity_id' => $entity->id],
                $metaAttrs
            );
        }

        $this->audit->log(
            section: 'editorial',
            action: 'rolled_back',
            settingKey: class_basename($modelClass).":{$entity->id}",
            oldValue: ['restored_version' => $revision->version_number],
            newValue: ['revision_id' => $revision->id],
            entityType: $modelClass,
            entityId: $entity->id,
            summary: "Rolled back {$modelClass} #{$entity->id} to Revision v{$revision->version_number}",
        );

        Cache::forget('site_home_cache');
        SitemapController::flushAllSitemapCaches();

        return back()->with('success', "Restored content & SEO state from Revision v{$revision->version_number}.");
    }
}
