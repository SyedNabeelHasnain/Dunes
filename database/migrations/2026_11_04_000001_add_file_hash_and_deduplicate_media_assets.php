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
                if (! Schema::hasColumn('media_assets', 'file_hash')) {
                    $table->string('file_hash', 64)->nullable()->index()->after('extension');
                }
            });

            // Automatically deduplicate any existing duplicate rows upon migration
            try {
                app(MediaManagerService::class)->deduplicateExistingAssets();
            } catch (\Throwable $e) {
                // Non-fatal if service unavailable during dry run
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
                if (Schema::hasColumn('media_assets', 'file_hash')) {
                    $table->dropIndex(['file_hash']);
                    $table->dropColumn('file_hash');
                }
            });
        }
    }
};
