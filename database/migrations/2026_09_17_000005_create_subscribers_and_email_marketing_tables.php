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
        // 1. Subscribers Table
        if (!Schema::hasTable('subscribers')) {
            Schema::create('subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email', 255)->unique();
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('phone', 50)->nullable();
                $table->enum('status', ['subscribed', 'unsubscribed', 'bounced', 'pending'])->default('subscribed')->index();
                $table->string('source', 50)->default('footer')->index();
                $table->string('unsubscribe_token', 64)->unique();
                $table->string('verification_token', 64)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->string('unsubscribe_reason', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'source']);
                $table->index('created_at');
            });
        }

        // 2. Subscriber Groups Table
        if (!Schema::hasTable('subscriber_groups')) {
            Schema::create('subscriber_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('slug', 150)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        // 3. Subscriber Group Pivot Table
        if (!Schema::hasTable('subscriber_group_pivot')) {
            Schema::create('subscriber_group_pivot', function (Blueprint $table) {
                $table->foreignId('subscriber_id')->constrained('subscribers')->onDelete('cascade');
                $table->foreignId('group_id')->constrained('subscriber_groups')->onDelete('cascade');
                $table->primary(['subscriber_id', 'group_id']);
            });
        }

        // 4. Email Templates Table
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('slug', 150)->unique();
                $table->string('subject', 255);
                $table->string('preview_text', 255)->nullable();
                $table->longText('content_html');
                $table->text('content_plain')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 5. Email Campaigns Table
        if (!Schema::hasTable('email_campaigns')) {
            Schema::create('email_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('title', 200);
                $table->string('subject', 255);
                $table->string('preview_text', 255)->nullable();
                $table->string('from_name', 150)->nullable();
                $table->string('from_email', 255)->nullable();
                $table->string('reply_to', 255)->nullable();
                $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->enum('target_type', ['all', 'group'])->default('all');
                $table->foreignId('group_id')->nullable()->constrained('subscriber_groups')->nullOnDelete();
                $table->longText('content_html');
                $table->text('content_plain')->nullable();
                $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'cancelled', 'failed'])->default('draft')->index();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('delivered_count')->default(0);
                $table->unsignedInteger('opened_count')->default(0);
                $table->unsignedInteger('unique_opens')->default(0);
                $table->unsignedInteger('clicked_count')->default(0);
                $table->unsignedInteger('unique_clicks')->default(0);
                $table->unsignedInteger('bounced_count')->default(0);
                $table->unsignedInteger('unsubscribed_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'created_at']);
            });
        }

        // 6. Email Campaign Logs Table
        if (!Schema::hasTable('email_campaign_logs')) {
            Schema::create('email_campaign_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
                $table->foreignId('subscriber_id')->constrained('subscribers')->onDelete('cascade');
                $table->string('tracking_token', 64)->unique();
                $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('pending')->index();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->unsignedInteger('open_count')->default(0);
                $table->unsignedInteger('click_count')->default(0);
                $table->text('error_message')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();

                $table->index(['campaign_id', 'status']);
                $table->index(['subscriber_id', 'status']);
            });
        }

        // 7. Email Campaign Clicks Table
        if (!Schema::hasTable('email_campaign_clicks')) {
            Schema::create('email_campaign_clicks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_log_id')->constrained('email_campaign_logs')->onDelete('cascade');
                $table->text('url');
                $table->timestamp('clicked_at');
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_clicks');
        Schema::dropIfExists('email_campaign_logs');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('subscriber_group_pivot');
        Schema::dropIfExists('subscriber_groups');
        Schema::dropIfExists('subscribers');
    }
};
