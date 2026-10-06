<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MergeGoogleReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:merge-extracted
                            {--source-file= : Path to newly extracted Google reviews JSON or CSV}
                            {--master-file= : Path to master seed reviews.json}
                            {--sync-db : Also persist and upsert records into the reviews database table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harmonize, deduplicate, and import extracted Google reviews (JSON or CSV) into master reviews.json and database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Google Reviews Reconciliation & Deduplication...');

        $extractedPath = $this->option('source-file') ?: database_path('seeders/data/reviews_extracted_google.json');
        $masterPath = $this->option('master-file') ?: database_path('seeders/data/reviews.json');
        $syncDb = (bool) $this->option('sync-db');

        if (! File::exists($masterPath)) {
            $this->error("Master reviews file not found at: {$masterPath}");
            return Command::FAILURE;
        }

        $masterReviews = json_decode(File::get($masterPath), true) ?: [];
        $this->info('Loaded ' . count($masterReviews) . ' existing reviews from master file.');

        // 1. Data Cleaning & Sanitization on Existing Records
        $fixedCount = 0;
        $cleanedMaster = [];
        foreach ($masterReviews as $m) {
            // Remove legacy mock dummy reviews (g1-g5)
            if (($m['source'] ?? '') === 'google' && in_array($m['source_review_id'] ?? '', ['g1', 'g2', 'g3', 'g4', 'g5'])) {
                $fixedCount++;
                continue;
            }
            // Fix ID 115 anomaly: mislabeled source 'Google' on TripAdvisor review
            if (($m['id'] ?? null) === 115 && ($m['source'] ?? '') === 'Google') {
                $m['source'] = 'tripadvisor';
                $fixedCount++;
            }
            // Ensure photos array exists
            if (! isset($m['photos']) || ! is_array($m['photos'])) {
                $m['photos'] = [];
            }
            $cleanedMaster[] = $m;
        }
        $masterReviews = $cleanedMaster;

        if ($fixedCount > 0) {
            $this->warn("Repaired/purged {$fixedCount} legacy review record(s) (mock placeholders or mislabeled sources).");
        }

        // 2. Build In-Memory Index for Deduplication
        // Key 1: source + source_review_id
        // Key 2: source + normalized reviewer_name + published_date
        $sridIndex = [];
        $nameDateIndex = [];
        $maxId = 0;

        foreach ($masterReviews as $idx => $r) {
            $maxId = max($maxId, (int) ($r['id'] ?? 0));
            $source = strtolower($r['source'] ?? '');

            if (! empty($r['source_review_id'])) {
                $sridIndex[$source . ':' . $r['source_review_id']] = $idx;
            }

            if (! empty($r['reviewer_name']) && ! empty($r['published_date'])) {
                $normName = $this->normalizeName($r['reviewer_name']);
                $nameDateIndex[$source . ':' . $normName . ':' . $r['published_date']] = $idx;
            }
        }

        $addedCount = 0;
        $updatedCount = 0;

        // 3. Process Newly Extracted Reviews (if extracted file exists)
        if (File::exists($extractedPath)) {
            $extractedReviews = $this->loadExtractedReviews($extractedPath);
            $this->info('Loaded ' . count($extractedReviews) . ' newly extracted Google reviews from source.');

            foreach ($extractedReviews as $ext) {
                $source = 'google';
                $extSrid = $ext['source_review_id'] ?? null;
                $extName = $ext['reviewer_name'] ?? '';
                $extDate = $ext['published_date'] ?? null;
                $normName = $this->normalizeName($extName);

                // Check match by source_review_id or fuzzy match by author name + published date
                $matchedIdx = null;
                if ($extSrid && isset($sridIndex[$source . ':' . $extSrid])) {
                    $matchedIdx = $sridIndex[$source . ':' . $extSrid];
                } elseif ($extName && $extDate && isset($nameDateIndex[$source . ':' . $normName . ':' . $extDate])) {
                    $matchedIdx = $nameDateIndex[$source . ':' . $normName . ':' . $extDate];
                }

                if ($matchedIdx !== null) {
                    // Update existing record with richer data
                    $existing = &$masterReviews[$matchedIdx];
                    $changed = false;

                    if (empty($existing['source_review_id']) && $extSrid) {
                        $existing['source_review_id'] = $extSrid;
                        $changed = true;
                    }

                    if (! empty($ext['reviewer_avatar_url']) && (str_contains($existing['reviewer_avatar_url'] ?? '', 'ui-avatars.com') || empty($existing['reviewer_avatar_url']) || ! empty($ext['reviewer_avatar_url']))) {
                        $existing['reviewer_avatar_url'] = $ext['reviewer_avatar_url'];
                        $changed = true;
                    }

                    if (! empty($ext['photos']) && (empty($existing['photos']) || count($ext['photos']) > count($existing['photos']))) {
                        $existing['photos'] = $ext['photos'];
                        $changed = true;
                    }

                    if (empty($existing['review_url']) && ! empty($ext['review_url'])) {
                        $existing['review_url'] = $ext['review_url'];
                        $changed = true;
                    }

                    if (! empty($ext['reviewer_profile_url']) && empty($existing['reviewer_profile_url'])) {
                        $existing['reviewer_profile_url'] = $ext['reviewer_profile_url'];
                        $changed = true;
                    }

                    if (isset($ext['is_local_guide'])) {
                        $existing['is_local_guide'] = (bool) $ext['is_local_guide'];
                        $changed = true;
                    }

                    if (isset($ext['reviewer_reviews_count']) && $ext['reviewer_reviews_count'] !== null) {
                        $existing['reviewer_reviews_count'] = (int) $ext['reviewer_reviews_count'];
                        $changed = true;
                    }

                    if (isset($ext['likes_count'])) {
                        $existing['likes_count'] = (int) $ext['likes_count'];
                        $changed = true;
                    }

                    if (! empty($ext['owner_response_text'])) {
                        $existing['owner_response_text'] = $ext['owner_response_text'];
                        $existing['owner_response_date'] = $ext['owner_response_date'] ?? null;
                        $changed = true;
                    }

                    if (! empty($ext['language']) && empty($existing['language'])) {
                        $existing['language'] = $ext['language'];
                        $changed = true;
                    }

                    if (! empty($ext['visited_in']) && empty($existing['visited_in'])) {
                        $existing['visited_in'] = $ext['visited_in'];
                        $changed = true;
                    }

                    if (empty($existing['review_text']) && ! empty($ext['review_text'])) {
                        $existing['review_text'] = $ext['review_text'];
                        $changed = true;
                    }

                    $existing['updated_at'] = now()->format('Y-m-d H:i:s');
                    $updatedCount++;
                } else {
                    // Add as brand new review
                    $maxId++;
                    $newRecord = [
                        'id' => $maxId,
                        'source' => 'google',
                        'source_review_id' => $extSrid ?: ('g_' . md5($extName . '_' . $extDate)),
                        'review_url' => $ext['review_url'] ?? null,
                        'published_date' => $extDate ?: now()->format('Y-m-d'),
                        'reviewer_name' => $extName ?: 'Google Customer',
                        'reviewer_avatar_url' => $ext['reviewer_avatar_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($extName ?: 'Google Customer') . '&background=00476d&color=ffffff&bold=true'),
                        'reviewer_profile_url' => $ext['reviewer_profile_url'] ?? null,
                        'rating' => (float) ($ext['rating'] ?? 5.0),
                        'review_title' => $ext['review_title'] ?? 'Dunes Discovery Tourism LLC',
                        'review_text' => $ext['review_text'] ?? '',
                        'photos' => $ext['photos'] ?? [],
                        'status' => $ext['status'] ?? 'approved',
                        'is_featured' => (int) ($ext['is_featured'] ?? 0),
                        'is_local_guide' => (bool) ($ext['is_local_guide'] ?? false),
                        'reviewer_reviews_count' => isset($ext['reviewer_reviews_count']) ? (int) $ext['reviewer_reviews_count'] : null,
                        'likes_count' => isset($ext['likes_count']) ? (int) $ext['likes_count'] : 0,
                        'owner_response_text' => $ext['owner_response_text'] ?? null,
                        'owner_response_date' => $ext['owner_response_date'] ?? null,
                        'language' => $ext['language'] ?? null,
                        'visited_in' => $ext['visited_in'] ?? null,
                        'imported_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ];

                    $masterReviews[] = $newRecord;
                    $newIdx = count($masterReviews) - 1;

                    // Update indexes
                    if ($newRecord['source_review_id']) {
                        $sridIndex['google:' . $newRecord['source_review_id']] = $newIdx;
                    }
                    $nameDateIndex['google:' . $normName . ':' . $newRecord['published_date']] = $newIdx;

                    $addedCount++;
                }
            }
        } else {
            $this->comment("No external review file provided at [{$extractedPath}]. Cleaned and formatted existing reviews.");
        }

        // 4. Save formatted JSON to master reviews.json
        File::put($masterPath, json_encode($masterReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info('Master reviews file updated successfully: ' . count($masterReviews) . ' total reviews.');

        // 5. Database Upsert (if requested)
        if ($syncDb) {
            $this->info('Upserting into database `reviews` table...');
            $dbSynced = 0;
            foreach ($masterReviews as $r) {
                Review::updateOrCreate(
                    [
                        'source' => $r['source'],
                        'source_review_id' => $r['source_review_id'],
                    ],
                    [
                        'review_url' => $r['review_url'] ?? null,
                        'published_date' => ! empty($r['published_date']) ? date('Y-m-d', strtotime($r['published_date'])) : null,
                        'reviewer_name' => $r['reviewer_name'] ?? 'Google Customer',
                        'reviewer_avatar_url' => $r['reviewer_avatar_url'] ?? null,
                        'reviewer_profile_url' => $r['reviewer_profile_url'] ?? null,
                        'rating' => (float) ($r['rating'] ?? 5.0),
                        'review_title' => $r['review_title'] ?? null,
                        'review_text' => $r['review_text'] ?? null,
                        'photos' => $r['photos'] ?? [],
                        'status' => $r['status'] ?? 'approved',
                        'is_featured' => (bool) ($r['is_featured'] ?? false),
                        'is_local_guide' => (bool) ($r['is_local_guide'] ?? false),
                        'reviewer_reviews_count' => isset($r['reviewer_reviews_count']) ? (int) $r['reviewer_reviews_count'] : null,
                        'likes_count' => isset($r['likes_count']) ? (int) $r['likes_count'] : 0,
                        'owner_response_text' => $r['owner_response_text'] ?? null,
                        'owner_response_date' => ! empty($r['owner_response_date']) ? date('Y-m-d H:i:s', strtotime($r['owner_response_date'])) : null,
                        'language' => $r['language'] ?? null,
                        'visited_in' => $r['visited_in'] ?? null,
                        'imported_at' => ! empty($r['imported_at']) ? date('Y-m-d H:i:s', strtotime($r['imported_at'])) : now(),
                    ]
                );
                $dbSynced++;
            }
            $this->info("Database sync complete: {$dbSynced} records synchronized.");
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Reviews in Master File', count($masterReviews)],
                ['Google Reviews', count(array_filter($masterReviews, fn($r) => strtolower($r['source']) === 'google'))],
                ['TripAdvisor Reviews', count(array_filter($masterReviews, fn($r) => strtolower($r['source']) === 'tripadvisor'))],
                ['New Reviews Added', $addedCount],
                ['Existing Reviews Enhanced/Updated', $updatedCount],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Load reviews from CSV or JSON based on file extension.
     */
    protected function loadExtractedReviews(string $path): array
    {
        if (str_ends_with(strtolower($path), '.csv')) {
            return $this->loadReviewsFromCsv($path);
        }

        return json_decode(File::get($path), true) ?: [];
    }

    /**
     * Parse and map Google Maps Reviews Scraper CSV.
     */
    protected function loadReviewsFromCsv(string $path): array
    {
        $reviews = [];
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');
        if (! $header) {
            fclose($handle);
            return [];
        }

        // Clean invisible BOM or non-printable characters from header keys
        $header = array_map(function ($col) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col));
        }, $header);

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $photos = [];
            for ($i = 0; $i <= 7; $i++) {
                $key = "reviewImageUrls/{$i}";
                if (! empty($data[$key])) {
                    $url = trim($data[$key]);
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $photos[] = $url;
                    }
                }
            }

            $publishedDate = null;
            if (! empty($data['publishedAtDate'])) {
                $ts = strtotime($data['publishedAtDate']);
                if ($ts !== false) {
                    $publishedDate = date('Y-m-d', $ts);
                }
            }

            $ownerResponseDate = null;
            if (! empty($data['responseFromOwnerDate'])) {
                $ts = strtotime($data['responseFromOwnerDate']);
                if ($ts !== false) {
                    $ownerResponseDate = date('Y-m-d H:i:s', $ts);
                }
            }

            $rating = 5.0;
            if (! empty($data['stars']) && is_numeric($data['stars'])) {
                $rating = (float) $data['stars'];
            } elseif (! empty($data['rating']) && is_numeric($data['rating'])) {
                $rating = (float) $data['rating'];
            }

            $text = ! empty($data['text']) ? $data['text'] : (! empty($data['textTranslated']) ? $data['textTranslated'] : '');

            $reviews[] = [
                'source' => 'google',
                'source_review_id' => ! empty($data['reviewId']) ? trim($data['reviewId']) : null,
                'review_url' => ! empty($data['reviewUrl']) ? trim($data['reviewUrl']) : null,
                'published_date' => $publishedDate ?: now()->format('Y-m-d'),
                'reviewer_name' => ! empty($data['name']) ? trim($data['name']) : 'Google Customer',
                'reviewer_avatar_url' => ! empty($data['reviewerPhotoUrl']) ? trim($data['reviewerPhotoUrl']) : null,
                'reviewer_profile_url' => ! empty($data['reviewerUrl']) ? trim($data['reviewerUrl']) : null,
                'rating' => $rating,
                'review_title' => ! empty($data['title']) ? trim($data['title']) : 'Dunes Discovery Tourism LLC',
                'review_text' => $text,
                'photos' => $photos,
                'status' => 'approved',
                'is_featured' => ($rating >= 5.0 && mb_strlen($text) >= 50) ? 1 : 0,
                'is_local_guide' => strtolower(trim($data['isLocalGuide'] ?? '')) === 'true',
                'reviewer_reviews_count' => isset($data['reviewerNumberOfReviews']) && is_numeric($data['reviewerNumberOfReviews']) ? (int) $data['reviewerNumberOfReviews'] : null,
                'likes_count' => isset($data['likesCount']) && is_numeric($data['likesCount']) ? (int) $data['likesCount'] : 0,
                'owner_response_text' => ! empty($data['responseFromOwnerText']) ? trim($data['responseFromOwnerText']) : null,
                'owner_response_date' => $ownerResponseDate,
                'language' => ! empty($data['language']) ? substr(trim($data['language']), 0, 10) : null,
                'visited_in' => ! empty($data['visitedIn']) ? substr(trim($data['visitedIn']), 0, 50) : null,
            ];
        }

        fclose($handle);

        return $reviews;
    }

    /**
     * Normalize author name for fuzzy match comparison.
     */
    protected function normalizeName(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
    }
}
