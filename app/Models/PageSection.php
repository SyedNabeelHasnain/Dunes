<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'body',
    ];

    protected $fillable = [
        'page_id',
        'section_key',
        'name',
        'title',
        'subtitle',
        'body',
        'extra_data',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
