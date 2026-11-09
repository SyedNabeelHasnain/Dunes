<?php

namespace App\Services;

use App\Models\Tour;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VideoAnalysisService
{
    protected MediaManagerService $mediaManager;

    /**
     * Map of common languages to country names and ISO-3166-1 alpha-2 codes.
     */
    protected const LANGUAGE_COUNTRY_MAP = [
        'es' => ['country' => 'Spain', 'code' => 'ES'],
        'ru' => ['country' => 'Russia', 'code' => 'RU'],
        'tr' => ['country' => 'Turkey', 'code' => 'TR'],
        'pa' => ['country' => 'India', 'code' => 'IN'],
        'hi' => ['country' => 'India', 'code' => 'IN'],
        'ar' => ['country' => 'United Arab Emirates', 'code' => 'AE'],
        'de' => ['country' => 'Germany', 'code' => 'DE'],
        'it' => ['country' => 'Italy', 'code' => 'IT'],
        'fr' => ['country' => 'France', 'code' => 'FR'],
        'pt' => ['country' => 'Brazil', 'code' => 'BR'],
        'zh' => ['country' => 'China', 'code' => 'CN'],
        'ja' => ['country' => 'Japan', 'code' => 'JP'],
        'ko' => ['country' => 'South Korea', 'code' => 'KR'],
    ];

    public function __construct(MediaManagerService $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * Analyze a video review (either uploaded file or existing library filename).
     *
     * @param  UploadedFile|string  $videoInput
     * @param  string|null  $customSlug
     * @return array<string, mixed>
     */
    public function analyze(UploadedFile|string $videoInput, ?string $customSlug = null): array
    {
        // 1. Resolve video file path and web-relative filename
        $videoInfo = $this->resolveVideoFile($videoInput, $customSlug);
        $videoFullPath = $videoInfo['full_path'];
        $videoFilename = $videoInfo['filename'];
        $baseSlug = $videoInfo['base_slug'];

        $postersDir = public_path('images/video-posters');
        if (! File::exists($postersDir)) {
            File::makeDirectory($postersDir, 0755, true, true);
        }

        // 2. Execute Python/FFmpeg analysis pipeline
        $analysisResult = $this->runAnalysisPipeline($videoFullPath, $postersDir, $baseSlug);

        // 3. Resolve duration and posters
        $duration = max(1, (int) ($analysisResult['duration_int'] ?? 30));
        $durationIso = 'PT' . $duration . 'S';
        $posterWebp = $analysisResult['poster_webp'] ?? ($baseSlug . '.webp');
        $posterJpg = $analysisResult['poster_jpg'] ?? ($baseSlug . '.jpg');

        // 4. Sync newly created posters into MediaAsset if they exist
        $webpPath = $postersDir . DIRECTORY_SEPARATOR . $posterWebp;
        if (File::exists($webpPath)) {
            try {
                $this->mediaManager->scanAndSyncFile($webpPath, 'reviews');
            } catch (\Throwable $e) {
                Log::warning('Could not sync webp poster into media assets: ' . $e->getMessage());
            }
        }

        $jpgPath = $postersDir . DIRECTORY_SEPARATOR . $posterJpg;
        if (File::exists($jpgPath)) {
            try {
                $this->mediaManager->scanAndSyncFile($jpgPath, 'reviews');
            } catch (\Throwable $e) {
                Log::warning('Could not sync jpg poster into media assets: ' . $e->getMessage());
            }
        }

        // 5. Intelligent NLP Extraction & Entity Recognition
        $transcript = trim((string) ($analysisResult['transcript'] ?? ''));
        $englishTranslation = trim((string) ($analysisResult['english_translation'] ?? $transcript));
        $detectedLang = strtolower((string) ($analysisResult['language'] ?? 'en'));

        $nlpData = $this->extractNlpEntities(
            $transcript,
            $englishTranslation,
            $detectedLang,
            $baseSlug
        );

        return [
            'success' => true,
            'data' => [
                'video_filename' => $videoFilename,
                'poster_filename' => $posterWebp,
                'poster_url' => asset('images/video-posters/' . $posterWebp),
                'poster_jpg_url' => asset('images/video-posters/' . $posterJpg),
                'title' => $nlpData['title'],
                'reviewer_name' => $nlpData['reviewer_name'],
                'reviewer_country' => $nlpData['reviewer_country'],
                'reviewer_country_code' => $nlpData['reviewer_country_code'],
                'category' => $nlpData['category'],
                'tour_slug' => $nlpData['tour_slug'],
                'tour_name' => $nlpData['tour_name'],
                'rating' => 5.0,
                'duration' => $duration,
                'duration_iso' => $durationIso,
                'transcript' => $transcript,
                'english_translation' => $englishTranslation,
                'caption' => $nlpData['caption'],
                'description' => $nlpData['description'],
                'location' => $nlpData['location'],
                'keywords' => $nlpData['keywords'],
                'placements' => [
                    'home' => true,
                    'gallery' => true,
                    'tour' => true,
                ],
                'is_active' => true,
            ],
        ];
    }

    /**
     * Resolve the video file from either direct upload or existing file path.
     *
     * @param  UploadedFile|string  $videoInput
     * @param  string|null  $customSlug
     * @return array{full_path: string, filename: string, base_slug: string}
     */
    protected function resolveVideoFile(UploadedFile|string $videoInput, ?string $customSlug = null): array
    {
        if ($videoInput instanceof UploadedFile) {
            $origName = pathinfo($videoInput->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($videoInput->getClientOriginalExtension() ?: 'mp4');
            $baseSlug = $customSlug ?: (Str::slug($origName) ?: 'customer-review') . '-' . time();
            $targetFilename = $baseSlug . '.' . $ext;

            $reviewsDir = public_path('videos/reviews');
            if (! File::exists($reviewsDir)) {
                File::makeDirectory($reviewsDir, 0755, true, true);
            }

            $targetPath = $reviewsDir . DIRECTORY_SEPARATOR . $targetFilename;
            $videoInput->move($reviewsDir, $targetFilename);

            // Sync with media manager
            try {
                $this->mediaManager->scanAndSyncFile($targetPath, 'reviews');
            } catch (\Throwable $e) {
                Log::warning('Could not sync uploaded video into media assets: ' . $e->getMessage());
            }

            return [
                'full_path' => $targetPath,
                'filename' => $targetFilename,
                'base_slug' => $baseSlug,
            ];
        }

        // String input (existing filename from library or relative path)
        $cleanInput = trim($videoInput);
        $cleanInput = str_replace(['\\', 'public/'], ['/', ''], $cleanInput);

        $candidates = [
            public_path($cleanInput),
            public_path('videos/reviews/' . basename($cleanInput)),
            public_path('videos/' . basename($cleanInput)),
            public_path('media/' . basename($cleanInput)),
        ];

        $foundPath = null;
        foreach ($candidates as $cand) {
            if (File::exists($cand) && is_file($cand)) {
                $foundPath = $cand;
                break;
            }
        }

        if (! $foundPath) {
            throw new \InvalidArgumentException("Video file could not be located on the server: [{$videoInput}]");
        }

        $filename = basename($foundPath);
        $baseSlug = $customSlug ?: Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        return [
            'full_path' => $foundPath,
            'filename' => $filename,
            'base_slug' => $baseSlug,
        ];
    }

    /**
     * Run the analysis pipeline (Python worker with faster-whisper & ffmpeg, or fallback).
     *
     * @param  string  $videoFullPath
     * @param  string  $postersDir
     * @param  string  $baseSlug
     * @return array<string, mixed>
     */
    protected function runAnalysisPipeline(string $videoFullPath, string $postersDir, string $baseSlug): array
    {
        $pythonScript = app_path('Scripts/analyze_video.py');

        if (File::exists($pythonScript)) {
            try {
                $process = new Process([
                    'python',
                    $pythonScript,
                    '--video',
                    $videoFullPath,
                    '--posters-dir',
                    $postersDir,
                    '--slug',
                    $baseSlug,
                ]);

                $process->setTimeout(120);
                $process->run();

                if ($process->isSuccessful()) {
                    $output = trim($process->getOutput());
                    $decoded = json_decode($output, true);
                    if (is_array($decoded) && ($decoded['success'] ?? false) === true) {
                        return $decoded;
                    }
                } else {
                    Log::warning('Python analyze_video process returned non-zero code: ' . $process->getErrorOutput());
                }
            } catch (\Throwable $e) {
                Log::warning('Exception while executing Python analyze_video script: ' . $e->getMessage());
            }
        }

        // Fallback: FFmpeg native execution for duration & posters
        return $this->runFfmpegFallback($videoFullPath, $postersDir, $baseSlug);
    }

    /**
     * Native fallback using FFmpeg directly or default values.
     *
     * @param  string  $videoFullPath
     * @param  string  $postersDir
     * @param  string  $baseSlug
     * @return array<string, mixed>
     */
    protected function runFfmpegFallback(string $videoFullPath, string $postersDir, string $baseSlug): array
    {
        $duration = 30;
        $webpName = $baseSlug . '.webp';
        $jpgName = $baseSlug . '.jpg';

        try {
            // Get duration
            $probeProc = new Process([
                'ffprobe', '-v', 'error',
                '-show_entries', 'format=duration',
                '-of', 'default=noprint_wrappers=1:nokey=1',
                $videoFullPath,
            ]);
            $probeProc->run();
            if ($probeProc->isSuccessful() && is_numeric(trim($probeProc->getOutput()))) {
                $duration = max(1, (int) round((float) trim($probeProc->getOutput())));
            }
        } catch (\Throwable $e) {
            Log::info('FFprobe fallback could not determine duration: ' . $e->getMessage());
        }

        $timestamp = max(1.0, min(5.0, $duration * 0.2));

        // Generate JPG
        try {
            $jpgProc = new Process([
                'ffmpeg', '-y', '-ss', (string) $timestamp,
                '-i', $videoFullPath,
                '-vframes', '1',
                '-q:v', '2',
                $postersDir . DIRECTORY_SEPARATOR . $jpgName,
            ]);
            $jpgProc->run();
        } catch (\Throwable $e) {
            // Ignore
        }

        // Generate WebP
        try {
            $webpProc = new Process([
                'ffmpeg', '-y', '-ss', (string) $timestamp,
                '-i', $videoFullPath,
                '-vframes', '1',
                '-quality', '85',
                $postersDir . DIRECTORY_SEPARATOR . $webpName,
            ]);
            $webpProc->run();
        } catch (\Throwable $e) {
            // Ignore
        }

        return [
            'success' => true,
            'duration' => $duration,
            'duration_int' => $duration,
            'poster_webp' => $webpName,
            'poster_jpg' => $jpgName,
            'transcript' => '',
            'english_translation' => '',
            'language' => 'en',
            'language_probability' => 1.0,
        ];
    }

    /**
     * Extract structured NLP entities from transcript and video context.
     *
     * @param  string  $transcript
     * @param  string  $english
     * @param  string  $detectedLang
     * @param  string  $baseSlug
     * @return array<string, mixed>
     */
    protected function extractNlpEntities(string $transcript, string $english, string $detectedLang, string $baseSlug): array
    {
        $combinedText = strtolower($transcript . ' ' . $english . ' ' . str_replace('-', ' ', $baseSlug));

        // 1. Resolve Country & Code
        $country = 'United Arab Emirates';
        $countryCode = 'AE';

        if (isset(self::LANGUAGE_COUNTRY_MAP[$detectedLang])) {
            $country = self::LANGUAGE_COUNTRY_MAP[$detectedLang]['country'];
            $countryCode = self::LANGUAGE_COUNTRY_MAP[$detectedLang]['code'];
        }

        $countryKeywords = [
            'spain' => ['country' => 'Spain', 'code' => 'ES'],
            'spanish' => ['country' => 'Spain', 'code' => 'ES'],
            'españ' => ['country' => 'Spain', 'code' => 'ES'],
            'russia' => ['country' => 'Russia', 'code' => 'RU'],
            'russian' => ['country' => 'Russia', 'code' => 'RU'],
            'turkey' => ['country' => 'Turkey', 'code' => 'TR'],
            'turkish' => ['country' => 'Turkey', 'code' => 'TR'],
            'türk' => ['country' => 'Turkey', 'code' => 'TR'],
            'punjabi' => ['country' => 'India', 'code' => 'IN'],
            'india' => ['country' => 'India', 'code' => 'IN'],
            'indian' => ['country' => 'India', 'code' => 'IN'],
            'australia' => ['country' => 'Australia', 'code' => 'AU'],
            'australian' => ['country' => 'Australia', 'code' => 'AU'],
            'uk' => ['country' => 'United Kingdom', 'code' => 'GB'],
            'british' => ['country' => 'United Kingdom', 'code' => 'GB'],
            'england' => ['country' => 'United Kingdom', 'code' => 'GB'],
            'london' => ['country' => 'United Kingdom', 'code' => 'GB'],
            'usa' => ['country' => 'United States', 'code' => 'US'],
            'american' => ['country' => 'United States', 'code' => 'US'],
            'ghana' => ['country' => 'Ghana', 'code' => 'GH'],
            'ghanaian' => ['country' => 'Ghana', 'code' => 'GH'],
            'colombia' => ['country' => 'Colombia', 'code' => 'CO'],
            'colombian' => ['country' => 'Colombia', 'code' => 'CO'],
            'germany' => ['country' => 'Germany', 'code' => 'DE'],
            'german' => ['country' => 'Germany', 'code' => 'DE'],
            'italy' => ['country' => 'Italy', 'code' => 'IT'],
            'italian' => ['country' => 'Italy', 'code' => 'IT'],
            'france' => ['country' => 'France', 'code' => 'FR'],
            'french' => ['country' => 'France', 'code' => 'FR'],
        ];

        foreach ($countryKeywords as $keyword => $info) {
            if (str_contains($combinedText, $keyword)) {
                $country = $info['country'];
                $countryCode = $info['code'];
                break;
            }
        }

        // 2. Resolve Category & Active Tour
        $category = 'red_dunes';
        $location = 'Lahbab Red Dunes, Dubai, UAE';

        if (preg_match('/(buggy|quad|rzr|polaris|can-am|atv)/i', $combinedText)) {
            $category = 'buggy_quad';
        } elseif (preg_match('/(city|atlantis|burj|palm|marina|sightseeing)/i', $combinedText)) {
            $category = 'city_tours';
            $location = 'Dubai City & Atlantis The Palm, Dubai, UAE';
        } elseif (preg_match('/(vip|luxury|private|majlis)/i', $combinedText)) {
            $category = 'vip_luxury';
        } elseif (preg_match('/(buffet|dinner|camp|tanoura|camel|bbq|fire show)/i', $combinedText)) {
            $category = 'desert_camp';
        }

        // Link to active tour
        $tours = Tour::where('status', 'active')->select('id', 'name', 'slug', 'category')->get();
        $matchedTour = null;

        // Try direct keyword matching with tour slug or name
        foreach ($tours as $t) {
            $slugWords = explode('-', $t->slug);
            $matchCount = 0;
            foreach ($slugWords as $sw) {
                if (strlen($sw) > 3 && str_contains($combinedText, $sw)) {
                    $matchCount++;
                }
            }
            if ($matchCount >= 2) {
                $matchedTour = $t;
                break;
            }
        }

        // If not matched, match by category
        if (! $matchedTour && $tours->isNotEmpty()) {
            if ($category === 'buggy_quad') {
                $matchedTour = $tours->firstWhere('slug', 'dune-buggy-dubai')
                    ?? $tours->firstWhere('category', 'quad-bike')
                    ?? $tours->first();
            } elseif ($category === 'city_tours') {
                $matchedTour = $tours->firstWhere('slug', 'dubai-city-tour')
                    ?? $tours->firstWhere('category', 'city-tours')
                    ?? $tours->first();
            } elseif ($category === 'vip_luxury') {
                $matchedTour = $tours->firstWhere('slug', 'vip-desert-safari-dubai')
                    ?? $tours->first();
            } else {
                $matchedTour = $tours->firstWhere('slug', 'evening-desert-safari-dubai')
                    ?? $tours->first();
            }
        }

        $tourSlug = $matchedTour ? $matchedTour->slug : 'evening-desert-safari-dubai';
        $tourName = $matchedTour ? $matchedTour->name : 'Evening Desert Safari Dubai';

        // 3. Extract Reviewer / Traveler Name
        $reviewerName = null;
        if (preg_match('/(?:my name is|i am|i\'m|we are|we\'re|this is)\s+([A-Z][a-z]+(?:\s+(?:and|&)\s+[A-Z][a-z]+)?)/i', $english, $nm)) {
            $reviewerName = trim($nm[1]);
        } elseif (preg_match('/(?:me llamo|soy)\s+([A-Z][a-z]+)/i', $transcript, $nm)) {
            $reviewerName = trim($nm[1]);
        }

        if (! $reviewerName) {
            // Generate clean traveler moniker
            $reviewerName = $country !== 'United Arab Emirates'
                ? "{$country} Traveler"
                : 'Verified Guest';
        }

        // 4. Generate SEO Title
        $experienceLabel = match ($category) {
            'buggy_quad' => 'Dune Buggy Adventure',
            'city_tours' => 'Dubai Sightseeing Tour',
            'vip_luxury' => 'VIP Desert Safari',
            'desert_camp' => 'Desert Camp Experience',
            default => 'Desert Safari Experience',
        };

        $title = "{$reviewerName} {$experienceLabel} Review - Dunes Discovery Tourism";

        // 5. Generate Summary Caption
        $caption = '';
        if (! empty($english)) {
            $sentences = preg_split('/(?<=[.?!])\s+/', $english, 2);
            $caption = trim($sentences[0] ?? '');
            if (strlen($caption) > 140) {
                $caption = Str::limit($caption, 137);
            }
        }
        if (empty($caption)) {
            $caption = "Unforgettable {$experienceLabel} in Dubai! Heart-pounding adventures and exceptional hospitality.";
        }

        // 6. Generate Full VideoObject Description
        $description = "{$reviewerName} from {$country} shares their authentic experience after enjoying the {$tourName} with Dunes Discovery Tourism in Dubai. Highly praising safari guide Malik, vehicle safety, red dunes bashing, and exceptional Arabian hospitality.";

        // 7. Keywords
        $keywords = implode(', ', [
            'dubai desert safari review',
            str_replace('_', ' ', $category) . ' dubai',
            $tourSlug,
            strtolower($country) . ' tourists dubai',
            'dunes discovery tourism review',
            'customer video testimonial',
        ]);

        return [
            'reviewer_name' => $reviewerName,
            'reviewer_country' => $country,
            'reviewer_country_code' => $countryCode,
            'category' => $category,
            'tour_slug' => $tourSlug,
            'tour_name' => $tourName,
            'title' => $title,
            'caption' => $caption,
            'description' => $description,
            'location' => $location,
            'keywords' => $keywords,
        ];
    }
}
