<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'excerpt',
        'content',
        'meta_title',
        'meta_desc',
        'meta_keywords',
        'author_bio',
        'ai_summary',
    ];

    protected $fillable = [
        'slug', 'title', 'subtitle', 'category_id', 'excerpt', 'content',
        'author_name', 'author_title', 'author_bio', 'author_avatar',
        'featured_image', 'featured_image_alt', 'featured_image_caption',
        'read_time', 'status', 'is_featured', 'priority', 'published_at',
        'meta_title', 'meta_desc', 'meta_keywords', 'focus_keyword',
        'canonical_url', 'robots', 'og_title', 'og_desc', 'og_image',
        'og_type', 'schema_type', 'ai_summary',
    ];

    protected $casts = [
        'read_time' => 'integer',
        'is_featured' => 'boolean',
        'priority' => 'integer',
        'published_at' => 'datetime',
    ];

    public function getFeaturedImageUrlAttribute(): string
    {
        $img = $this->featured_image;
        if (empty($img)) {
            return asset('images/desert-safari-poster.avif');
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        $clean = ltrim($img, '/');
        if (str_starts_with($clean, 'blogs/')) {
            return asset('storage/'.$clean);
        }
        if (str_starts_with($clean, 'images/')) {
            return asset($clean);
        }

        return asset('images/blog/'.preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $clean));
    }

    public function getOgImageUrlAttribute(): string
    {
        if (! empty($this->og_image)) {
            return resolve_og_image($this->og_image);
        }

        return $this->featured_image_url;
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags', 'post_id', 'tag_id');
    }

    public function faqs()
    {
        return $this->hasMany(BlogPostFaq::class, 'post_id')->orderBy('priority', 'asc');
    }
}
