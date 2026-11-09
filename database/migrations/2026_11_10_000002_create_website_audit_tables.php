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
        Schema::create('audit_runs', function (Blueprint $table) {
            $table->id();
            $table->string('scan_type', 32)->default('full'); // 'full', 'incremental', 'single_url'
            $table->string('status', 32)->default('completed'); // 'running', 'completed', 'failed'
            $table->unsignedTinyInteger('health_score')->default(100);
            $table->json('category_scores')->nullable();
            $table->unsignedInteger('total_issues_count')->default(0);
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('medium_count')->default(0);
            $table->unsignedInteger('low_count')->default(0);
            $table->unsignedInteger('info_count')->default(0);
            $table->unsignedInteger('scanned_urls_count')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_run_id')->constrained('audit_runs')->cascadeOnDelete();
            $table->string('category', 64)->index(); // 'seo', 'technical_seo', 'ai_readiness', 'performance', 'accessibility', 'security', 'content_quality', 'structured_data', 'privacy'
            $table->string('check_key', 128)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity', 16)->index(); // 'critical', 'high', 'medium', 'low', 'info'
            $table->string('status', 16)->default('open')->index(); // 'open', 'in_progress', 'resolved', 'ignored'
            $table->string('verification_type', 32)->default('automated'); // 'automated', 'partial_automated', 'manual_review'
            $table->string('affected_url')->nullable()->index();
            $table->text('evidence')->nullable();
            $table->text('recommended_action')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('remediation_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_issues');
        Schema::dropIfExists('audit_runs');
    }
};
