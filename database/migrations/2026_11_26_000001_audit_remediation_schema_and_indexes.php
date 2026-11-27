<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotent forward remediation migration:
     * 1. Ensures wiki_article_tours exists with unsignedInteger tour_id (fixes prior BIGINT/INT FK mismatch on MySQL).
     * 2. Adds nullable tour_tiers.child_price and whatsapp_inquiries.ref_code columns.
     * 3. Repairs any double-encoded JSON dictionaries in legal_pages, legal_sections, legal_items.
     * 4. Migrates legacy users with NULL / empty / 'admin' role to 'super_admin' before fail-closed RBAC.
     * 5. Adds missing high-impact query indexes safely across SQLite and MySQL.
     */
    public function up(): void
    {
        // 1. Ensure wiki_article_tours exists (in case prior BIGINT FK migration failed on MySQL)
        if (! Schema::hasTable('wiki_article_tours') && Schema::hasTable('wiki_articles') && Schema::hasTable('tours')) {
            Schema::create('wiki_article_tours', function (Blueprint $table) {
                $table->foreignId('wiki_article_id')->constrained('wiki_articles')->cascadeOnDelete();
                $table->unsignedInteger('tour_id');
                $table->integer('sort_order')->default(0);
                $table->primary(['wiki_article_id', 'tour_id']);
                $table->foreign('tour_id')->references('id')->on('tours')->cascadeOnDelete();
            });
        }

        // 2. Add tour_tiers.child_price if missing
        if (Schema::hasTable('tour_tiers') && ! Schema::hasColumn('tour_tiers', 'child_price')) {
            Schema::table('tour_tiers', function (Blueprint $table) {
                $table->decimal('child_price', 10, 2)->nullable()->after('price');
            });
        }

        // 3. Add whatsapp_inquiries.ref_code if missing
        if (Schema::hasTable('whatsapp_inquiries') && ! Schema::hasColumn('whatsapp_inquiries', 'ref_code')) {
            Schema::table('whatsapp_inquiries', function (Blueprint $table) {
                $table->string('ref_code', 40)->nullable()->index('whatsapp_inquiries_ref_code_idx');
            });
        }

        // 4. Unwrap any double-encoded JSON dictionaries in legal tables (e.g. {"en":"{\"en\":\"Terms\"}"})
        $this->repairDoubleEncodedJson('legal_pages', ['title', 'subtitle', 'description']);
        $this->repairDoubleEncodedJson('legal_sections', ['heading', 'subheading']);
        $this->repairDoubleEncodedJson('legal_items', ['content']);

        // 5. Promote legacy admin / NULL role users to super_admin before fail-closed RBAC
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereNull('role')
                ->orWhere('role', '')
                ->orWhereRaw('LOWER(role) = ?', ['admin'])
                ->update(['role' => 'super_admin']);
        }

        // 6. Add missing high-impact performance indexes safely (SQLite + MySQL compatible)
        $this->addIndexIfMissing('tiers', ['status'], 'tiers_status_idx');
        $this->addIndexIfMissing('wiki_articles', ['status'], 'wiki_articles_status_idx');
        $this->addIndexIfMissing('wiki_pillars', ['status'], 'wiki_pillars_status_idx');
        $this->addIndexIfMissing('audit_runs', ['status', 'created_at'], 'audit_runs_status_created_idx');
        $this->addIndexIfMissing('booking_payments', ['status', 'customer_email'], 'booking_payments_status_email_idx');
        $this->addIndexIfMissing('reviews', ['published_date'], 'reviews_published_date_idx');
        $this->addIndexIfMissing('config_audit_logs', ['created_at', 'ip_address'], 'config_audit_logs_created_ip_idx');
        $this->addIndexIfMissing('blog_posts', ['created_at'], 'blog_posts_created_at_idx');
        $this->addIndexIfMissing('request_logs', ['created_at'], 'request_logs_created_at_idx');
        $this->addIndexIfMissing('spam_events', ['created_at'], 'spam_events_created_at_idx');
        $this->addIndexIfMissing('email_otps', ['expires_at'], 'email_otps_expires_at_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('tiers', 'tiers_status_idx');
        $this->dropIndexIfExists('wiki_articles', 'wiki_articles_status_idx');
        $this->dropIndexIfExists('wiki_pillars', 'wiki_pillars_status_idx');
        $this->dropIndexIfExists('audit_runs', 'audit_runs_status_created_idx');
        $this->dropIndexIfExists('booking_payments', 'booking_payments_status_email_idx');
        $this->dropIndexIfExists('reviews', 'reviews_published_date_idx');
        $this->dropIndexIfExists('config_audit_logs', 'config_audit_logs_created_ip_idx');
        $this->dropIndexIfExists('blog_posts', 'blog_posts_created_at_idx');
        $this->dropIndexIfExists('request_logs', 'request_logs_created_at_idx');
        $this->dropIndexIfExists('spam_events', 'spam_events_created_at_idx');
        $this->dropIndexIfExists('email_otps', 'email_otps_expires_at_idx');

        if (Schema::hasTable('whatsapp_inquiries') && Schema::hasColumn('whatsapp_inquiries', 'ref_code')) {
            $this->dropIndexIfExists('whatsapp_inquiries', 'whatsapp_inquiries_ref_code_idx');
            Schema::table('whatsapp_inquiries', function (Blueprint $table) {
                $table->dropColumn('ref_code');
            });
        }

        if (Schema::hasTable('tour_tiers') && Schema::hasColumn('tour_tiers', 'child_price')) {
            Schema::table('tour_tiers', function (Blueprint $table) {
                $table->dropColumn('child_price');
            });
        }
    }

    private function repairDoubleEncodedJson(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $present = array_values(array_filter($columns, fn (string $c) => Schema::hasColumn($table, $c)));
        if (empty($present)) {
            return;
        }

        foreach (DB::table($table)->select(array_merge(['id'], $present))->get() as $row) {
            $updates = [];
            foreach ($present as $col) {
                $raw = $row->{$col};
                if (! is_string($raw) || $raw === '') {
                    continue;
                }
                $trimmed = trim($raw);
                if (! str_starts_with($trimmed, '{') || ! str_ends_with($trimmed, '}')) {
                    continue;
                }
                $outer = json_decode($trimmed, true);
                if (! is_array($outer) || ! isset($outer['en']) || ! is_string($outer['en'])) {
                    continue;
                }
                $innerTrimmed = trim($outer['en']);
                if (! str_starts_with($innerTrimmed, '{') || ! str_ends_with($innerTrimmed, '}')) {
                    continue;
                }
                $inner = json_decode($innerTrimmed, true);
                if (! is_array($inner)) {
                    continue;
                }
                // Merge outer non-en locales (e.g. ar) onto unwrapped inner dictionary
                $merged = $inner;
                foreach ($outer as $locale => $val) {
                    if ($locale !== 'en' && ! empty($val) && empty($merged[$locale])) {
                        $merged[$locale] = $val;
                    }
                }
                $updates[$col] = json_encode($merged, JSON_UNESCAPED_UNICODE);
            }

            if (! empty($updates)) {
                DB::table($table)->where('id', $row->id)->update($updates);
            }
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return;
            }
        }

        if (Schema::hasIndex($table, $indexName) || Schema::hasIndex($table, $columns)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // Safely ignore if an equivalent index already exists on production MySQL
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $indexName)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropIndex($indexName);
            });
        } catch (\Throwable) {
        }
    }
};
