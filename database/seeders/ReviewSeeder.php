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
        if (empty($reviews) || ! is_array($reviews)) {
            return;
        }

        Review::unguard();

        $chunks = array_chunk($reviews, 50);
        foreach ($chunks as $chunk) {
            $records = [];
            foreach ($chunk as $r) {
                $records[] = [
                    'id' => (int) $r['id'],
                    'source' => $r['source'] ?? 'google',
                    'source_review_id' => $r['source_review_id'] ?? null,
                    'review_url' => $r['review_url'] ?? null,
                    'published_date' => ! empty($r['published_date']) ? date('Y-m-d', strtotime($r['published_date'])) : null,
                    'reviewer_name' => $r['reviewer_name'] ?? 'Verified Guest',
                    'reviewer_avatar_url' => $r['reviewer_avatar_url'] ?? null,
                    'reviewer_profile_url' => $r['reviewer_profile_url'] ?? null,
                    'rating' => (float) ($r['rating'] ?? 5.0),
                    'review_title' => $r['review_title'] ?? null,
                    'review_text' => $r['review_text'] ?? null,
                    'status' => $r['status'] ?: 'approved',
                    'photos' => is_array($r['photos'] ?? null) ? json_encode($r['photos']) : ($r['photos'] ?? null),
                    'is_featured' => ! empty($r['is_featured']) ? 1 : 0,
                    'is_local_guide' => ! empty($r['is_local_guide']) ? 1 : 0,
                    'reviewer_reviews_count' => isset($r['reviewer_reviews_count']) ? (int) $r['reviewer_reviews_count'] : null,
                    'likes_count' => isset($r['likes_count']) ? (int) $r['likes_count'] : 0,
                    'owner_response_text' => $r['owner_response_text'] ?? null,
                    'owner_response_date' => ! empty($r['owner_response_date']) ? date('Y-m-d H:i:s', strtotime($r['owner_response_date'])) : null,
                    'language' => $r['language'] ?? 'en',
                    'visited_in' => $r['visited_in'] ?? null,
                    'imported_at' => ! empty($r['imported_at']) ? date('Y-m-d H:i:s', strtotime($r['imported_at'])) : now()->toDateTimeString(),
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }

            Review::upsert(
                $records,
                ['id'],
                [
                    'source', 'source_review_id', 'review_url', 'published_date',
                    'reviewer_name', 'reviewer_avatar_url', 'reviewer_profile_url',
                    'rating', 'review_title', 'review_text', 'photos', 'status',
                    'is_featured', 'is_local_guide', 'reviewer_reviews_count',
                    'likes_count', 'owner_response_text', 'owner_response_date',
                    'language', 'visited_in', 'imported_at', 'updated_at',
                ]
            );
        }

        Review::reguard();
    }
}
