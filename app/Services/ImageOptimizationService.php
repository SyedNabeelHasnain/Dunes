<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    /**
     * Supported modern output formats and their system availability.
     */
    public function getSupportedFormats(): array
    {
        return [
            'webp' => function_exists('imagewebp') && function_exists('imagecreatefromwebp'),
            'avif' => function_exists('imageavif') && function_exists('imagecreatefromavif'),
        ];
    }

    /**
     * Check if a given file is already in an optimized modern format (WebP or AVIF).
     */
    public function isOptimized(UploadedFile|string $file): bool
    {
        $ext = $this->extractExtension($file);
        return in_array($ext, ['webp', 'avif'], true);
    }

    /**
     * Check if a given file is a convertible image format (PNG, JPG, JPEG, BMP, GIF).
     */
    public function isConvertibleImage(UploadedFile|string $file): bool
    {
        $ext = $this->extractExtension($file);
        return in_array($ext, ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp', 'avif'], true);
    }

    /**
     * Determine if the file needs conversion (i.e. is an image, but not WebP or AVIF).
     */
    public function needsConversion(UploadedFile|string $file): bool
    {
        return $this->isConvertibleImage($file) && ! $this->isOptimized($file);
    }

    /**
     * Convert an uploaded image to high-quality lossless or near-lossless WebP or AVIF.
     *
     * @param UploadedFile $file The original uploaded file
     * @param string $targetFormat 'webp' or 'avif' (defaults to 'webp')
     * @param array $options ['quality' => 'lossless'|'high'|int]
     * @return array Result metadata containing the newly wrapped UploadedFile
     * @throws \RuntimeException If conversion fails or format is unsupported
     */
    public function convert(UploadedFile $file, string $targetFormat = 'webp', array $options = []): array
    {
        $targetFormat = strtolower(trim($targetFormat));
        if (! in_array($targetFormat, ['webp', 'avif'], true)) {
            $targetFormat = 'webp';
        }

        // Check platform support; if AVIF is requested but unsupported, fallback to WebP
        $supported = $this->getSupportedFormats();
        if ($targetFormat === 'avif' && empty($supported['avif'])) {
            $targetFormat = 'webp';
        }

        $sourcePath = $file->getRealPath();
        if (! $sourcePath || ! file_exists($sourcePath)) {
            throw new \RuntimeException("Source image file does not exist at: " . ($sourcePath ?: 'unknown'));
        }

        $originalSize = (int) $file->getSize();
        $originalName = $file->getClientOriginalName();
        $originalExt = strtolower($file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));

        // Load image into GD resource
        $img = $this->createGdImageFromPath($sourcePath, $originalExt);
        if (! $img) {
            throw new \RuntimeException("Unable to decode image file '{$originalName}' for optimization.");
        }

        // Auto-orient based on EXIF metadata if present (e.g. mobile photo uploads)
        $img = $this->autoOrient($img, $sourcePath, $originalExt);

        // Preserve alpha transparency for PNG / WebP / AVIF
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $width = imagesx($img);
        $height = imagesy($img);

        // Quality calculation
        $qualityPreset = $options['quality'] ?? 'lossless';
        $quality = $this->resolveQuality($targetFormat, $qualityPreset);

        // Generate temporary output file
        $tempDir = sys_get_temp_dir();
        $tempFilename = 'dunes_opt_' . Str::random(12) . '.' . $targetFormat;
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempFilename;

        $success = false;
        if ($targetFormat === 'webp') {
            $success = @imagewebp($img, $tempPath, $quality);
        } elseif ($targetFormat === 'avif') {
            $success = @imageavif($img, $tempPath, $quality);
        }

        imagedestroy($img);

        if (! $success || ! file_exists($tempPath) || filesize($tempPath) === 0) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw new \RuntimeException("Failed to encode '{$originalName}' into {$targetFormat}.");
        }

        $convertedSize = (int) filesize($tempPath);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $newFilename = Str::slug($baseName) . '.' . $targetFormat;
        $targetMime = $targetFormat === 'webp' ? 'image/webp' : 'image/avif';

        $convertedUploadedFile = new UploadedFile(
            $tempPath,
            $newFilename,
            $targetMime,
            null,
            true // marks file as test-safe so it can be moved from temp directory
        );

        $savingsBytes = max(0, $originalSize - $convertedSize);
        $savingsPercent = $originalSize > 0 ? round(($savingsBytes / $originalSize) * 100, 1) : 0;

        return [
            'file' => $convertedUploadedFile,
            'original_filename' => $originalName,
            'converted_filename' => $newFilename,
            'original_format' => $originalExt,
            'target_format' => $targetFormat,
            'original_size' => $originalSize,
            'converted_size' => $convertedSize,
            'savings_bytes' => $savingsBytes,
            'savings_percent' => $savingsPercent,
            'width' => $width,
            'height' => $height,
            'quality_preset' => $qualityPreset,
        ];
    }

    /**
     * Resolve quality parameter based on format and preset.
     */
    protected function resolveQuality(string $format, string|int $preset): int
    {
        if (is_numeric($preset)) {
            return max(1, min(100, (int) $preset));
        }

        $preset = strtolower((string) $preset);

        if ($preset === 'high') {
            return $format === 'avif' ? 88 : 92;
        }

        // Default: lossless / maximum quality 100
        return 100;
    }

    /**
     * Load an image into a GD resource based on extension/type.
     */
    protected function createGdImageFromPath(string $path, string $ext): mixed
    {
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'bmp' => @imagecreatefrombmp($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => @imagecreatefromstring(file_get_contents($path)),
        };
    }

    /**
     * Auto-orient image based on EXIF Orientation tag.
     */
    protected function autoOrient(mixed $img, string $path, string $ext): mixed
    {
        if (! function_exists('exif_read_data') || ! in_array($ext, ['jpg', 'jpeg', 'tiff'], true)) {
            return $img;
        }

        try {
            $exif = @exif_read_data($path);
            if (! empty($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];
                $rotated = match ($orientation) {
                    3 => imagerotate($img, 180, 0),
                    6 => imagerotate($img, -90, 0),
                    8 => imagerotate($img, 90, 0),
                    default => null,
                };

                if ($rotated !== null && $rotated !== false) {
                    imagedestroy($img);
                    return $rotated;
                }
            }
        } catch (\Throwable) {
            // Ignore EXIF parsing errors and retain original image orientation
        }

        return $img;
    }

    /**
     * Helper to extract clean lowercase extension.
     */
    protected function extractExtension(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            return strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        }

        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }
}
