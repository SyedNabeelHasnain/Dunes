<?php

namespace App\Traits;

use App\Models\ContentRevision;
use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

trait HasSeoMetadata
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'entity');
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'entity')
            ->orderByDesc('version_number')
            ->orderByDesc('id');
    }

    /**
     * Save a revision snapshot for auditability and 1-click rollback.
     */
    public function recordRevision(?string $changeSummary = null, ?string $editorialStatus = null): ?ContentRevision
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('content_revisions')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $nextVersion = ((int) $this->revisions()->max('version_number')) + 1;
        $user = Auth::user();

        $attributesSnapshot = $this->getAttributes();
        $seoSnapshot = $this->seoMeta ? $this->seoMeta->toArray() : null;

        $title = '';
        if (method_exists($this, 'getTranslation')) {
            $title = (string) ($this->getTranslation('title', 'en', false)
                ?: $this->getTranslation('name', 'en', false)
                ?: '');
        }
        if ($title === '') {
            $title = (string) ($this->title ?? $this->name ?? $this->slug ?? ('#' . $this->getKey()));
        }

        return ContentRevision::create([
            'entity_type' => static::class,
            'entity_id' => $this->getKey(),
            'version_number' => $nextVersion,
            'title' => mb_substr(strip_tags($title), 0, 250),
            'snapshot' => [
                'attributes' => $attributesSnapshot,
                'seo_meta' => $seoSnapshot,
            ],
            'change_summary' => $changeSummary,
            'editorial_status' => $editorialStatus ?? ($this->seoMeta?->editorial_status ?? $this->status ?? null),
            'created_by' => $user?->id,
            'created_by_name' => $user?->name ?? 'System',
            'created_at' => now(),
        ]);
    }
}
