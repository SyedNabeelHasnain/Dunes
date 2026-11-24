<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('privacy_consents')) {
            Schema::create('privacy_consents', function (Blueprint $table) {
                $table->id();
                $table->string('consent_id', 64)->index();
                $table->string('session_id', 128)->nullable()->index();
                $table->string('ip_hash', 64)->nullable();
                $table->string('locale', 12)->default('en');
                $table->boolean('necessary')->default(true);
                $table->boolean('analytics')->default(false);
                $table->boolean('marketing')->default(false);
                $table->boolean('functional')->default(false);
                $table->string('consent_version', 32)->default('1.0');
                $table->string('action', 32)->default('custom');
                $table->string('page_url', 500)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->dateTime('consented_at')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('web_vitals_metrics')) {
            Schema::create('web_vitals_metrics', function (Blueprint $table) {
                $table->id();
                $table->string('page_path', 255)->index();
                $table->string('route_name', 128)->nullable()->index();
                $table->string('locale', 12)->default('en');
                $table->string('device_type', 24)->default('desktop')->index();
                $table->string('metric_name', 16)->index();
                $table->decimal('metric_value', 10, 3);
                $table->string('rating', 24)->index();
                $table->string('navigation_type', 32)->nullable();
                $table->string('connection_type', 32)->nullable();
                $table->string('session_id', 128)->nullable();
                $table->dateTime('recorded_at')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_citation_observations')) {
            Schema::create('ai_citation_observations', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 48)->index();
                $table->string('prompt_query', 255);
                $table->string('cited_url', 500)->nullable();
                $table->boolean('brand_mentioned')->default(true);
                $table->unsignedTinyInteger('citation_position')->nullable();
                $table->string('sentiment', 24)->default('positive');
                $table->text('notes')->nullable();
                $table->dateTime('observed_at')->index();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_citation_observations');
        Schema::dropIfExists('web_vitals_metrics');
        Schema::dropIfExists('privacy_consents');
    }
};
