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
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'is_spam')) {
                    $table->boolean('is_spam')->default(false)->index()->after('status');
                }
                if (! Schema::hasColumn('contacts', 'spam_score')) {
                    $table->unsignedSmallInteger('spam_score')->default(0)->index()->after('is_spam');
                }
                if (! Schema::hasColumn('contacts', 'spam_reasons')) {
                    $table->json('spam_reasons')->nullable()->after('spam_score');
                }
                if (! Schema::hasColumn('contacts', 'quarantined_at')) {
                    $table->timestamp('quarantined_at')->nullable()->after('spam_reasons');
                }
                if (! Schema::hasColumn('contacts', 'security_meta')) {
                    $table->json('security_meta')->nullable()->after('quarantined_at');
                }
            });
        }

        if (! Schema::hasTable('spam_events')) {
            Schema::create('spam_events', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 40)->default('contact')->index();
                $table->string('ip_address', 64)->nullable()->index();
                $table->string('email', 255)->nullable();
                $table->string('name_snippet', 160)->nullable();
                $table->string('subject_snippet', 160)->nullable();
                $table->unsignedSmallInteger('spam_score')->default(0);
                $table->string('action_taken', 32)->default('blocked')->index();
                $table->json('reasons')->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->string('request_path', 120)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spam_events');

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $cols = ['is_spam', 'spam_score', 'spam_reasons', 'quarantined_at', 'security_meta'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('contacts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
