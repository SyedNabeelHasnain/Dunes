<?php

use App\Models\MediaAsset;
use App\Services\MediaManagerService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('media_assets')) {
            Schema::table('media_assets', function (Blueprint $table) {
                if (! Schema::hasColumn('media_assets', 'extra_metadata')) {
                    $table->json('extra_metadata')->nullable()->after('usage_locations');
                }
            });

            // Automatically deduplicate format-variant duplicate records (e.g. .webp alongside .jpg)
            try {
                app(MediaManagerService::class)->deduplicateFormatVariants();
            } catch (\Throwable $e) {
                // Non-fatal during early bootstrap
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('media_assets')) {
            Schema::table('media_assets', function (Blueprint $table) {
                if (Schema::hasColumn('media_assets', 'extra_metadata')) {
                    $table->dropColumn('extra_metadata');
                }
            });
        }
    }
};
