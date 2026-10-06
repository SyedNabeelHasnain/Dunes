<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/reviews.json');
        if (! File::exists($path)) {
            return;
        }

        $reviews = json_decode(File::get($path), true);
        foreach ($reviews as $r) {
            Review::updateOrCreate(
                ['id' => $r['id']],
                [
                    'source' => $r['source'],
                    'source_review_id' => $r['source_review_id'],
                    'review_url' => $r['review_url'],
                    'published_date' => $r['published_date'] ? date('Y-m-d', strtotime($r['published_date'])) : null,
                    'reviewer_name' => $r['reviewer_name'],
                    'reviewer_avatar_url' => $r['reviewer_avatar_url'],
                    'reviewer_profile_url' => $r['reviewer_profile_url'],
                    'rating' => (float) $r['rating'],
                    'review_title' => $r['review_title'],
                    'review_text' => $r['review_text'],
                    'status' => $r['status'] ?: 'approved',
                    'photos' => $r['photos'] ?? [],
                    'is_featured' => (bool) $r['is_featured'],
                    'is_local_guide' => (bool) ($r['is_local_guide'] ?? false),
                    'reviewer_reviews_count' => isset($r['reviewer_reviews_count']) ? (int) $r['reviewer_reviews_count'] : null,
                    'likes_count' => isset($r['likes_count']) ? (int) $r['likes_count'] : 0,
                    'owner_response_text' => $r['owner_response_text'] ?? null,
                    'owner_response_date' => ! empty($r['owner_response_date']) ? date('Y-m-d H:i:s', strtotime($r['owner_response_date'])) : null,
                    'language' => $r['language'] ?? null,
                    'visited_in' => $r['visited_in'] ?? null,
                    'imported_at' => $r['imported_at'] ? date('Y-m-d H:i:s', strtotime($r['imported_at'])) : now(),
                ]
            );
        }
    }
}
