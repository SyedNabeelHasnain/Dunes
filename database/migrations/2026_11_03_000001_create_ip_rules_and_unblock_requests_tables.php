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
        // 1. IP Rules & Firewall Table
        if (! Schema::hasTable('ip_rules')) {
            Schema::create('ip_rules', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->unsignedTinyInteger('ip_mask')->nullable();
                $table->string('mode', 30)->default('ignore_analytics')->index(); // 'ignore_analytics', 'block', 'monitor'
                $table->string('label', 150)->nullable();
                $table->text('reason')->nullable();
                $table->string('status', 20)->default('active')->index(); // 'active', 'inactive'
                $table->unsignedInteger('blocked_attempts_count')->default(0);
                $table->timestamp('last_activity_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['ip_address', 'status', 'mode']);
            });
        }

        // 2. Visitor Unblock Requests Table
        if (! Schema::hasTable('unblock_requests')) {
            Schema::create('unblock_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ip_rule_id')->nullable();
                $table->string('ip_address', 45)->index();
                $table->string('name', 150);
                $table->string('email', 150);
                $table->string('phone', 50)->nullable();
                $table->text('reason');
                $table->text('user_agent')->nullable();
                $table->string('status', 30)->default('pending')->index(); // 'pending', 'approved_unblocked', 'rejected_kept_blocked', 'switched_to_ignored'
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->foreign('ip_rule_id')->references('id')->on('ip_rules')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unblock_requests');
        Schema::dropIfExists('ip_rules');
    }
};
