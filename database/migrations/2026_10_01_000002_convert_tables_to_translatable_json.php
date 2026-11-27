<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure any missing translatable columns exist
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (! Schema::hasColumn('categories', 'meta_title')) {
                    $table->text('meta_title')->nullable();
                }
                if (! Schema::hasColumn('categories', 'meta_desc')) {
                    $table->text('meta_desc')->nullable();
                }
                if (! Schema::hasColumn('categories', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable();
                }
            });
        }

        // Helper function to convert column to JSON if not already JSON
        $convertRowsToJson = function (string $tableName, array $columns) {
            if (! Schema::hasTable($tableName)) {
                return;
            }

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $updates = [];
                foreach ($columns as $column) {
                    if (! isset($row->{$column})) {
                        continue;
                    }

                    $val = $row->{$column};
                    if ($val === null || $val === '') {
                        continue;
                    }

                    $trimmed = trim((string) $val);
                    // Check if already valid JSON dictionary
                    if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                        $decoded = json_decode($trimmed, true);
                        if (is_array($decoded)) {
                            continue;
                        }
                    }

                    // Wrap into English default translation
                    $updates[$column] = json_encode(['en' => $val], JSON_UNESCAPED_UNICODE);
                }

                if (! empty($updates)) {
                    DB::table($tableName)->where('id', $row->id)->update($updates);
                }
            }
        };

        // 2. Convert standard entities
        $convertRowsToJson('tours', ['name', 'short_desc', 'full_desc', 'meta_title', 'meta_desc', 'meta_keywords']);
        $convertRowsToJson('categories', ['name', 'description', 'meta_title', 'meta_desc', 'meta_keywords']);
        $convertRowsToJson('tiers', ['name', 'display_name', 'description']);
        $convertRowsToJson('addons', ['name', 'description']);
        $convertRowsToJson('content_items', ['title', 'description']);
        $convertRowsToJson('itineraries', ['title', 'description']);
        $convertRowsToJson('faqs', ['question', 'answer']);
        $convertRowsToJson('blog_posts', ['title', 'subtitle', 'excerpt', 'content', 'author_bio', 'ai_summary', 'meta_title', 'meta_desc', 'meta_keywords']);
        $convertRowsToJson('blog_categories', ['name', 'description', 'meta_title', 'meta_desc']);
        $convertRowsToJson('blog_tags', ['name']);
        $convertRowsToJson('blog_post_faqs', ['question', 'answer']);
        $convertRowsToJson('email_templates', ['subject', 'preview_text', 'content_html', 'content_plain']);

        $mergeEnAr = static function (?string $enVal, ?string $arVal): ?string {
            $dict = [];
            if ($enVal !== null && $enVal !== '') {
                $trimmed = trim($enVal);
                if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                    $decoded = json_decode($trimmed, true);
                    if (is_array($decoded)) {
                        $dict = $decoded;
                    } else {
                        $dict['en'] = $enVal;
                    }
                } else {
                    $dict['en'] = $enVal;
                }
            }
            if ($arVal !== null && $arVal !== '' && empty($dict['ar'])) {
                $dict['ar'] = $arVal;
            }

            return ! empty($dict) ? json_encode($dict, JSON_UNESCAPED_UNICODE) : null;
        };

        // 3. Migrate legacy Legal Tables with _ar columns into unified JSON
        if (Schema::hasTable('legal_pages')) {
            $pages = DB::table('legal_pages')->get();
            foreach ($pages as $p) {
                $updates = [];

                $titleJson = $mergeEnAr($p->title ?? null, $p->title_ar ?? null);
                if ($titleJson !== null) {
                    $updates['title'] = $titleJson;
                }

                $subJson = $mergeEnAr($p->subtitle ?? null, $p->subtitle_ar ?? null);
                if ($subJson !== null) {
                    $updates['subtitle'] = $subJson;
                }

                $descJson = $mergeEnAr($p->description ?? null, $p->description_ar ?? null);
                if ($descJson !== null) {
                    $updates['description'] = $descJson;
                }

                if (! empty($updates)) {
                    DB::table('legal_pages')->where('id', $p->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('legal_sections')) {
            $sections = DB::table('legal_sections')->get();
            foreach ($sections as $s) {
                $updates = [];
                $headingJson = $mergeEnAr($s->heading ?? null, $s->heading_ar ?? null);
                if ($headingJson !== null) {
                    $updates['heading'] = $headingJson;
                }

                $subheadingJson = $mergeEnAr($s->subheading ?? null, $s->subheading_ar ?? null);
                if ($subheadingJson !== null) {
                    $updates['subheading'] = $subheadingJson;
                }

                if (! empty($updates)) {
                    DB::table('legal_sections')->where('id', $s->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('legal_items')) {
            $items = DB::table('legal_items')->get();
            foreach ($items as $item) {
                $contentJson = $mergeEnAr($item->content ?? null, $item->content_ar ?? null);
                if ($contentJson !== null) {
                    DB::table('legal_items')->where('id', $item->id)->update([
                        'content' => $contentJson,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal maintains data intact
    }
};
