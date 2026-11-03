<?php

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
        if (! Schema::hasTable('visitor_session_recordings')) {
            Schema::create('visitor_session_recordings', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 64)->index();
                $table->string('client_ip', 45)->index();
                $table->string('country', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('device_type', 20)->default('desktop')->index();
                $table->string('browser_name', 50)->nullable();
                $table->string('os_name', 50)->nullable();
                $table->string('landing_page', 255)->nullable();
                $table->string('exit_page', 255)->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->unsignedInteger('events_count')->default(0);
                $table->string('storage_path', 255)->nullable();
                $table->unsignedInteger('file_size_bytes')->default(0);
                $table->boolean('is_starred')->default(false)->index();
                $table->boolean('has_inquiry')->default(false)->index();
                $table->string('status', 20)->default('recording');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('last_event_at')->nullable();
                $table->timestamps();

                $table->index(['created_at', 'device_type']);
            });
        }

        if (! Schema::hasTable('visitor_heatmap_clicks')) {
            Schema::create('visitor_heatmap_clicks', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 64)->nullable()->index();
                $table->string('page_path', 255)->index();
                $table->string('device_type', 20)->default('desktop')->index();
                $table->decimal('x_ratio', 6, 4)->default(0);
                $table->decimal('y_ratio', 6, 4)->default(0);
                $table->unsignedInteger('click_x')->default(0);
                $table->unsignedInteger('click_y')->default(0);
                $table->unsignedSmallInteger('viewport_width')->default(0);
                $table->unsignedSmallInteger('viewport_height')->default(0);
                $table->string('target_tag', 50)->nullable();
                $table->string('target_text', 100)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['page_path', 'device_type', 'created_at']);
            });
        }

        if (! Schema::hasTable('visitor_heatmap_scrolls')) {
            Schema::create('visitor_heatmap_scrolls', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 64)->nullable()->index();
                $table->string('page_path', 255)->index();
                $table->string('device_type', 20)->default('desktop')->index();
                $table->unsignedTinyInteger('max_scroll_percent')->default(0);
                $table->unsignedSmallInteger('viewport_height')->default(0);
                $table->unsignedInteger('page_height')->default(0);
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['page_path', 'device_type', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_heatmap_scrolls');
        Schema::dropIfExists('visitor_heatmap_clicks');
        Schema::dropIfExists('visitor_session_recordings');
    }
};
