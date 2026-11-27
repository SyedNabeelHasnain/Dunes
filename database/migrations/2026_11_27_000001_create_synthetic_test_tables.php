<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('synthetic_test_runs')) {
            Schema::create('synthetic_test_runs', function (Blueprint $table) {
                $table->id();
                $table->string('trigger_type', 24)->default('manual'); // manual | scheduled | cli
                $table->string('status', 24)->default('running')->index(); // running | passed | partial | failed
                $table->json('flows_requested');
                $table->string('locale', 12)->default('en');
                $table->string('device_profile', 16)->default('desktop');
                $table->string('mail_mode', 24)->default('intercept'); // intercept | live_smtp
                $table->boolean('auto_cleanup')->default(true);
                $table->boolean('cleanup_completed')->default(false);
                $table->unsignedSmallInteger('total_flows')->default(0);
                $table->unsignedSmallInteger('passed_flows')->default(0);
                $table->unsignedSmallInteger('failed_flows')->default(0);
                $table->unsignedSmallInteger('total_steps')->default(0);
                $table->unsignedSmallInteger('passed_steps')->default(0);
                $table->unsignedSmallInteger('failed_steps')->default(0);
                $table->unsignedInteger('duration_ms')->default(0);
                $table->text('summary')->nullable();
                $table->json('artifacts')->nullable();
                $table->boolean('alert_sent')->default(false);
                $table->unsignedBigInteger('triggered_by')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at'], 'synthetic_runs_status_created_idx');
            });
        }

        if (! Schema::hasTable('synthetic_test_steps')) {
            Schema::create('synthetic_test_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('synthetic_test_run_id')
                    ->constrained('synthetic_test_runs')
                    ->cascadeOnDelete();
                $table->string('flow', 32)->index(); // booking | whatsapp | contact
                $table->unsignedSmallInteger('step_order');
                $table->string('step_key', 64);
                $table->string('title', 180);
                $table->string('http_method', 10)->nullable();
                $table->string('endpoint', 255)->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('status', 20)->index(); // passed | failed | skipped
                $table->unsignedInteger('duration_ms')->default(0);
                $table->text('assertion_summary')->nullable();
                $table->text('error_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('synthetic_test_steps');
        Schema::dropIfExists('synthetic_test_runs');
    }
};
