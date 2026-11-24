<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('redirect_rules')) {
            Schema::table('redirect_rules', function (Blueprint $table) {
                if (Schema::hasColumn('redirect_rules', 'target_path')) {
                    $table->string('target_path')->nullable()->change();
                }
                if (! Schema::hasColumn('redirect_rules', 'last_verified_at')) {
                    $table->dateTime('last_verified_at')->nullable()->after('last_hit_at');
                }
                if (! Schema::hasColumn('redirect_rules', 'last_verified_status')) {
                    $table->unsignedSmallInteger('last_verified_status')->nullable()->after('last_verified_at');
                }
                if (! Schema::hasColumn('redirect_rules', 'destination_ok')) {
                    $table->boolean('destination_ok')->nullable()->after('last_verified_status');
                }
                if (! Schema::hasColumn('redirect_rules', 'source_type')) {
                    $table->string('source_type', 32)->default('manual')->after('created_by');
                }
            });
        }

        if (Schema::hasTable('audit_runs')) {
            Schema::table('audit_runs', function (Blueprint $table) {
                if (! Schema::hasColumn('audit_runs', 'parent_run_id')) {
                    $table->unsignedBigInteger('parent_run_id')->nullable()->after('triggered_by');
                }
                if (! Schema::hasColumn('audit_runs', 'target_url')) {
                    $table->string('target_url', 500)->nullable()->after('parent_run_id');
                }
                if (! Schema::hasColumn('audit_runs', 'summary_notes')) {
                    $table->text('summary_notes')->nullable()->after('target_url');
                }
            });
        }

        if (Schema::hasTable('audit_issues')) {
            Schema::table('audit_issues', function (Blueprint $table) {
                if (! Schema::hasColumn('audit_issues', 'remediated_by')) {
                    $table->unsignedBigInteger('remediated_by')->nullable()->after('assigned_to');
                }
                if (! Schema::hasColumn('audit_issues', 'entity_type')) {
                    $table->string('entity_type', 128)->nullable()->after('remediated_by');
                }
                if (! Schema::hasColumn('audit_issues', 'entity_id')) {
                    $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_issues')) {
            Schema::table('audit_issues', function (Blueprint $table) {
                foreach (['entity_id', 'entity_type', 'remediated_by'] as $col) {
                    if (Schema::hasColumn('audit_issues', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('audit_runs')) {
            Schema::table('audit_runs', function (Blueprint $table) {
                foreach (['summary_notes', 'target_url', 'parent_run_id'] as $col) {
                    if (Schema::hasColumn('audit_runs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('redirect_rules')) {
            Schema::table('redirect_rules', function (Blueprint $table) {
                foreach (['source_type', 'destination_ok', 'last_verified_status', 'last_verified_at'] as $col) {
                    if (Schema::hasColumn('redirect_rules', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
