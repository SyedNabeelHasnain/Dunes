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
        Schema::table('whatsapp_inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_inquiries', 'status')) {
                $table->string('status', 30)->default('new')->index()->after('message_text');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'is_read')) {
                $table->boolean('is_read')->default(false)->index()->after('status');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            if (! Schema::hasColumn('whatsapp_inquiries', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_inquiries', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('whatsapp_inquiries', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('whatsapp_inquiries', 'is_read')) {
                $table->dropColumn('is_read');
            }
            if (Schema::hasColumn('whatsapp_inquiries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
