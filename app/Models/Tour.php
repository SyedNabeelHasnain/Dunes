<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'category_id', 'short_desc', 'full_desc', 'duration',
        'pickup_time', 'dropoff_time', 'min_age', 'group_size', 'languages',
        'hero_image', 'thumb_image', 'og_image', 'video_url', 'rating',
        'review_count', 'is_bestseller', 'is_featured', 'status', 'priority',
        'meta_title', 'meta_desc', 'meta_keywords'
    ];

    protected $casts = [
        'is_bestseller' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'float',
        'review_count' => 'integer',
        'min_age' => 'integer',
        'priority' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class)->orderBy('priority', 'asc');
    }

    public function tiers()
    {
        return $this->belongsToMany(Tier::class, 'tour_tiers')
            ->withPivot('price', 'old_price', 'price_type')
            ->orderBy('priority', 'asc');
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'tour_addons')
            ->withPivot('price')
            ->orderBy('priority', 'asc');
    }

    public function contentItems()
    {
        return $this->belongsToMany(ContentItem::class, 'tour_content', 'tour_id', 'content_id')
            ->withPivot('tier_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
