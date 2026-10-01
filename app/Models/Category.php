<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'description',
        'meta_title',
        'meta_desc',
        'meta_keywords',
    ];

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'priority',
        'description',
        'meta_title',
        'meta_desc',
        'meta_keywords',
    ];

    public function tours()
    {
        return $this->hasMany(Tour::class)->orderBy('priority', 'asc');
    }
}
