<?php

use App\Services\MediaManagerService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('media_assets')) {
            try {
                app(MediaManagerService::class)->deduplicateFormatVariants();
            } catch (\Throwable $e) {
                // Non-fatal during early migration bootstrap
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe to keep merged assets
    }
};
