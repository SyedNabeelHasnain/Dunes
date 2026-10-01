<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'description',
        'meta_title',
        'meta_desc',
    ];

    protected $fillable = ['name', 'slug', 'description', 'meta_title', 'meta_desc', 'og_image', 'priority', 'status'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id')->orderBy('priority', 'asc');
    }
}
