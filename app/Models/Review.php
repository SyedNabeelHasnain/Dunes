<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'source', 'source_review_id', 'booking_id', 'review_url', 'published_date',
        'reviewer_name', 'reviewer_avatar_url', 'reviewer_profile_url',
        'rating', 'review_title', 'review_text', 'photos', 'status', 'is_featured',
        'is_local_guide', 'reviewer_reviews_count', 'likes_count',
        'owner_response_text', 'owner_response_date', 'language', 'visited_in',
        'imported_at',
    ];

    protected $casts = [
        'published_date' => 'date',
        'rating' => 'float',
        'is_featured' => 'boolean',
        'is_local_guide' => 'boolean',
        'reviewer_reviews_count' => 'integer',
        'likes_count' => 'integer',
        'owner_response_date' => 'datetime',
        'imported_at' => 'datetime',
        'photos' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
