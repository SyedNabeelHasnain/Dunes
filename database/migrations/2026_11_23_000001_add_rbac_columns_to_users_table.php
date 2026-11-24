<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 32)->default('super_admin')->index()->after('password');
                }
                if (! Schema::hasColumn('users', 'permissions')) {
                    $table->json('permissions')->nullable()->after('role');
                }
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index()->after('permissions');
                }
                if (! Schema::hasColumn('users', 'last_login_at')) {
                    $table->dateTime('last_login_at')->nullable()->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['last_login_at', 'is_active', 'permissions', 'role'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
