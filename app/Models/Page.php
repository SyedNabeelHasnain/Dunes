<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'slug',
        'name',
        'title',
        'subtitle',
        'meta_title',
        'meta_description',
        'status',
    ];

    public function sections()
    {
        return $this->hasMany(PageSection::class, 'page_id')->orderBy('order', 'asc');
    }

    public function getSection(string $key): ?PageSection
    {
        return $this->sections->firstWhere('section_key', $key);
    }
}
