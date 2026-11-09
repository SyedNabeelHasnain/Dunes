<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedirectRule extends Model
{
    use HasFactory;

    protected $table = 'redirect_rules';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'hit_count',
        'last_hit_at',
        'notes',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Normalizes a path string: ensures leading slash, trims trailing slash (except root), strips query string.
     */
    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }

        // Parse path only if full URL passed
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = $parsed ?: '/';
        }

        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');

        return $path === '' ? '/' : strtolower($path);
    }
}
