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
                            {--source-file= : Path to newly extracted Google reviews JSON}
                            {--master-file= : Path to master seed reviews.json}
                            {--sync-db : Also persist and upsert records into the reviews database table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harmonize and deduplicate extracted Google reviews into master reviews.json and database.';

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
        $this->info("Loaded " . count($masterReviews) . " existing reviews from master file.");

        // 1. Data Cleaning & Sanitization on Existing Records
        $fixedCount = 0;
        foreach ($masterReviews as &$m) {
            // Fix ID 115 anomaly: mislabeled source 'Google' on TripAdvisor review
            if ($m['id'] === 115 && $m['source'] === 'Google') {
                $m['source'] = 'tripadvisor';
                $fixedCount++;
            }
            // Ensure photos array exists
            if (! isset($m['photos']) || ! is_array($m['photos'])) {
                $m['photos'] = [];
            }
        }
        unset($m);

        if ($fixedCount > 0) {
            $this->warn("Repaired {$fixedCount} mislabeled legacy review record(s) (e.g., ID 115 source -> tripadvisor).");
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
        $skippedCount = 0;

        // 3. Process Newly Extracted Reviews (if extracted file exists)
        if (File::exists($extractedPath)) {
            $extractedReviews = json_decode(File::get($extractedPath), true) ?: [];
            $this->info("Loaded " . count($extractedReviews) . " newly extracted Google reviews.");

            foreach ($extractedReviews as $ext) {
                $source = 'google';
                $extSrid = $ext['source_review_id'] ?? null;
                $extName = $ext['reviewer_name'] ?? '';
                $extDate = $ext['published_date'] ?? null;
                $normName = $this->normalizeName($extName);

                // Check match by source_review_id
                $matchedIdx = null;
                if ($extSrid && isset($sridIndex[$source . ':' . $extSrid])) {
                    $matchedIdx = $sridIndex[$source . ':' . $extSrid];
                } elseif ($extName && $extDate && isset($nameDateIndex[$source . ':' . $normName . ':' . $extDate])) {
                    $matchedIdx = $nameDateIndex[$source . ':' . $normName . ':' . $extDate];
                }

                if ($matchedIdx !== null) {
                    // Update existing record with richer data (photos, high-res avatar, review url)
                    $existing = &$masterReviews[$matchedIdx];
                    $changed = false;

                    if (empty($existing['source_review_id']) && $extSrid) {
                        $existing['source_review_id'] = $extSrid;
                        $changed = true;
                    }

                    if (! empty($ext['reviewer_avatar_url']) && (str_contains($existing['reviewer_avatar_url'] ?? '', 'ui-avatars.com') || empty($existing['reviewer_avatar_url']))) {
                        $existing['reviewer_avatar_url'] = $ext['reviewer_avatar_url'];
                        $changed = true;
                    }

                    if (! empty($ext['photos']) && empty($existing['photos'])) {
                        $existing['photos'] = $ext['photos'];
                        $changed = true;
                    }

                    if (empty($existing['review_url']) && ! empty($ext['review_url'])) {
                        $existing['review_url'] = $ext['review_url'];
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
                        'reviewer_name' => $extName,
                        'reviewer_avatar_url' => $ext['reviewer_avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($extName) . '&background=00476d&color=ffffff&bold=true',
                        'reviewer_profile_url' => $ext['reviewer_profile_url'] ?? null,
                        'rating' => (float) ($ext['rating'] ?? 5.0),
                        'review_title' => $ext['review_title'] ?? 'Dunes Discovery Tourism LLC',
                        'review_text' => $ext['review_text'] ?? '',
                        'photos' => $ext['photos'] ?? [],
                        'status' => $ext['status'] ?? 'approved',
                        'is_featured' => (int) ($ext['is_featured'] ?? 0),
                        'imported_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ];

                    $masterReviews[] = $newRecord;
                    $newIdx = count($masterReviews) - 1;

                    // Update index
                    if ($newRecord['source_review_id']) {
                        $sridIndex['google:' . $newRecord['source_review_id']] = $newIdx;
                    }
                    $nameDateIndex['google:' . $normName . ':' . $newRecord['published_date']] = $newIdx;

                    $addedCount++;
                }
            }
        } else {
            $this->comment("No external JSON provided at [{$extractedPath}]. Cleaned and formatted existing reviews.");
        }

        // 4. Save formatted JSON to master reviews.json
        File::put($masterPath, json_encode($masterReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info("Master reviews file updated successfully: " . count($masterReviews) . " total reviews.");

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
     * Normalize author name for fuzzy match comparison.
     */
    protected function normalizeName(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
    }
}
