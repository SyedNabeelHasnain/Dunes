<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('config_audit_logs')) {
            Schema::create('config_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 120)->nullable();
                $table->string('user_email', 160)->nullable();
                $table->string('user_role', 32)->nullable();
                $table->string('section', 64)->index();
                $table->string('action', 48)->index();
                $table->string('entity_type', 128)->nullable()->index();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('setting_key', 128)->nullable()->index();
                $table->longText('old_value')->nullable();
                $table->longText('new_value')->nullable();
                $table->string('summary', 500)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('change_approvals')) {
            Schema::create('change_approvals', function (Blueprint $table) {
                $table->id();
                $table->string('section', 64)->index();
                $table->string('target_key', 190)->index();
                $table->string('summary', 255);
                $table->longText('payload');
                $table->longText('previous_payload')->nullable();
                $table->string('status', 24)->default('pending_review')->index();
                $table->unsignedBigInteger('requested_by')->nullable()->index();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->text('review_notes')->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->dateTime('applied_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('change_approvals');
        Schema::dropIfExists('config_audit_logs');
    }
};
