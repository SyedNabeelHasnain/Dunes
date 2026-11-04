<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media_assets';

    protected $fillable = [
        'uuid',
        'filename',
        'original_name',
        'disk',
        'file_path',
        'public_url',
        'media_type',
        'mime_type',
        'extension',
        'file_size_bytes',
        'width',
        'height',
        'duration_seconds',
        'title',
        'alt_text',
        'caption',
        'category',
        'folder',
        'is_optimized',
        'usage_count',
        'usage_locations',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration_seconds' => 'integer',
        'is_optimized' => 'boolean',
        'usage_count' => 'integer',
        'usage_locations' => 'array',
    ];

    protected $appends = [
        'clean_public_url',
        'poster_url',
        'formatted_file_size',
        'dimensions',
        'is_image',
        'is_video',
    ];

    protected static function booted(): void
    {
        static::creating(function ($asset) {
            if (empty($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * User who uploaded the asset.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Canonical clean public URL (e.g. /images/... or /videos/...)
     * Strips any absolute server path prefixes if stored erroneously.
     */
    public function getCleanPublicUrlAttribute(): string
    {
        $raw = $this->public_url ?: $this->file_path;
        if (empty($raw)) {
            return '/images/media-placeholder.svg';
        }

        $normalized = str_replace('\\', '/', $raw);

        // If the path contains full server directory prefixes, extract the canonical web path
        foreach (['images/', 'videos/', 'uploads/'] as $prefix) {
            $pos = strpos($normalized, '/' . $prefix);
            if ($pos !== false) {
                return substr($normalized, $pos);
            }
            if (str_starts_with($normalized, $prefix)) {
                return '/' . $normalized;
            }
        }

        return Str::startsWith($normalized, '/') ? $normalized : ('/' . $normalized);
    }

    /**
     * Associated poster / thumbnail for video or image.
     */
    public function getPosterUrlAttribute(): ?string
    {
        if ($this->media_type === 'image') {
            return $this->clean_public_url;
        }

        if ($this->media_type !== 'video') {
            return '/images/media-placeholder.svg';
        }

        $baseName = pathinfo($this->filename, PATHINFO_FILENAME);

        // Check if there is an existing poster in images/video-posters/
        $candidates = [
            "/images/video-posters/{$baseName}.webp",
            "/images/video-posters/{$baseName}.jpg",
            "/images/video-posters/{$baseName}.png",
            "/images/{$baseName}.webp",
            "/images/{$baseName}.jpg",
        ];

        foreach ($candidates as $cand) {
            $local = public_path(ltrim($cand, '/'));
            if (file_exists($local)) {
                return $cand;
            }
        }

        // Default convention: video posters live in /images/video-posters/{baseName}.webp
        return "/images/video-posters/{$baseName}.webp";
    }

    /**
     * Human-readable formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        $val = round($bytes / pow(1024, $i), 1);

        return $val . ' ' . $units[$i];
    }

    /**
     * Display dimensions e.g. "1920 × 1080".
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width} × {$this->height}";
        }

        return null;
    }

    /**
     * Check if asset is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if asset is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return $this->media_type === 'video';
    }

    /**
     * Absolute or relative URL to the asset.
     */
    public function getUrlAttribute(): string
    {
        $clean = $this->clean_public_url;
        if (Str::startsWith($clean, ['http://', 'https://'])) {
            return $clean;
        }

        return url($clean);
    }

    /**
     * Scope for images only.
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope for videos only.
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    /**
     * Scope for category.
     */
    public function scopeCategory($query, ?string $category)
    {
        if ($category && $category !== 'all') {
            return $query->where('category', $category);
        }

        return $query;
    }

    /**
     * Scope for in-use assets.
     */
    public function scopeInUse($query)
    {
        return $query->where('usage_count', '>', 0);
    }

    /**
     * Scope for unused/orphan assets.
     */
    public function scopeUnused($query)
    {
        return $query->where('usage_count', 0);
    }

    /**
     * Search scope across filename, title, and alt text.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('filename', 'like', "%{$term}%")
              ->orWhere('title', 'like', "%{$term}%")
              ->orWhere('alt_text', 'like', "%{$term}%")
              ->orWhere('original_name', 'like', "%{$term}%")
              ->orWhere('file_path', 'like', "%{$term}%");
        });
    }
}
