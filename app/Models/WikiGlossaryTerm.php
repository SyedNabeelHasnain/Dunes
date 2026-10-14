<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiGlossaryTerm extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'term',
        'definition',
    ];

    protected $fillable = [
        'term_key',
        'term',
        'definition',
        'wiki_article_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(WikiArticle::class, 'wiki_article_id');
    }
}
