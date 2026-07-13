<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'source', 'source_review_id', 'review_url', 'published_date',
        'reviewer_name', 'reviewer_avatar_url', 'reviewer_profile_url',
        'rating', 'review_title', 'review_text', 'status', 'is_featured',
        'imported_at'
    ];

    protected $casts = [
        'published_date' => 'date',
        'rating' => 'float',
        'is_featured' => 'boolean',
        'imported_at' => 'datetime'
    ];
}
