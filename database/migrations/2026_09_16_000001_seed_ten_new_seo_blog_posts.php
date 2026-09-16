<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogPostFaq;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $postsPath = database_path('seeders/data/blog_posts.json');
        if (!File::exists($postsPath)) {
            return;
        }

        $posts = json_decode(File::get($postsPath), true);
        if (!is_array($posts)) {
            return;
        }

        $defaultCatId = BlogCategory::value('id');

        // Target specifically the 10 new posts (IDs 31 to 40)
        $targetSlugs = [
            'luxury-vip-private-desert-safari-dubai',
            'dubai-desert-safari-price-breakdown-avoid-scams',
            'dune-buggy-driving-rules-license-requirements-dubai',
            'dubai-desert-safari-with-toddlers-kids-family-handbook',
            'food-dining-dubai-desert-safari-bbq-menu-vegetarian-jain',
            'lahbab-red-dunes-vs-al-qudra-vs-ddcr-desert-locations',
            'best-dubai-combo-tour-packages-save-money',
            'bedouin-culture-dubai-camel-trekking-falconry-henna-guide',
            'abu-dhabi-shore-excursion-from-dubai-cruise-port-guide',
            'dubai-marina-dhow-cruise-deck-selection-timings-secrets'
        ];

        foreach ($posts as $p) {
            if (!in_array($p['slug'], $targetSlugs)) {
                continue;
            }

            $targetCatId = !empty($p['category_id']) 
                ? (BlogCategory::where('id', $p['category_id'])->value('id') ?? $defaultCatId) 
                : $defaultCatId;

            $post = BlogPost::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'slug' => $p['slug'],
                    'title' => $p['title'],
                    'subtitle' => $p['subtitle'] ?? null,
                    'category_id' => $targetCatId,
                    'excerpt' => $p['excerpt'] ?? null,
                    'content' => $p['content'] ?? '',
                    'author_name' => $p['author_name'] ?? 'Dunes Discovery Team',
                    'author_title' => $p['author_title'] ?? null,
                    'author_bio' => $p['author_bio'] ?? null,
                    'author_avatar' => $p['author_avatar'] ?? null,
                    'featured_image' => $p['featured_image'] ?? null,
                    'featured_image_alt' => $p['featured_image_alt'] ?? null,
                    'featured_image_caption' => $p['featured_image_caption'] ?? null,
                    'read_time' => (int)($p['read_time'] ?? 6),
                    'status' => $p['status'] ?? 'published',
                    'is_featured' => (bool)($p['is_featured'] ?? false),
                    'priority' => (int)($p['priority'] ?? 99),
                    'published_at' => !empty($p['published_at']) ? date('Y-m-d H:i:s', strtotime($p['published_at'])) : now(),
                    'meta_title' => $p['meta_title'] ?? null,
                    'meta_desc' => $p['meta_desc'] ?? null,
                    'meta_keywords' => $p['meta_keywords'] ?? null,
                    'focus_keyword' => $p['focus_keyword'] ?? null,
                    'canonical_url' => $p['canonical_url'] ?? null,
                    'robots' => $p['robots'] ?? 'index, follow',
                    'og_title' => $p['og_title'] ?? null,
                    'og_desc' => $p['og_desc'] ?? null,
                    'og_image' => $p['og_image'] ?? null,
                    'og_type' => $p['og_type'] ?? 'article',
                    'schema_type' => $p['schema_type'] ?? 'BlogPosting',
                    'ai_summary' => $p['ai_summary'] ?? null,
                ]
            );

            // Seed Tag associations for this post
            $tagsRelPath = database_path('seeders/data/blog_post_tags.json');
            if (File::exists($tagsRelPath)) {
                $tagRels = json_decode(File::get($tagsRelPath), true);
                if (is_array($tagRels)) {
                    foreach ($tagRels as $tr) {
                        if (($tr['post_id'] ?? null) == ($p['id'] ?? null)) {
                            DB::table('blog_post_tags')->insertOrIgnore([
                                'post_id' => $post->id,
                                'tag_id' => $tr['tag_id']
                            ]);
                        }
                    }
                }
            }

            // Seed FAQs for this post
            $faqsPath = database_path('seeders/data/blog_post_faqs.json');
            if (File::exists($faqsPath)) {
                $faqs = json_decode(File::get($faqsPath), true);
                if (is_array($faqs)) {
                    foreach ($faqs as $faqData) {
                        if (($faqData['post_id'] ?? null) == ($p['id'] ?? null)) {
                            BlogPostFaq::updateOrCreate(
                                [
                                    'post_id' => $post->id,
                                    'question' => $faqData['question']
                                ],
                                [
                                    'answer' => $faqData['answer'],
                                    'priority' => (int)($faqData['priority'] ?? 99)
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $targetSlugs = [
            'luxury-vip-private-desert-safari-dubai',
            'dubai-desert-safari-price-breakdown-avoid-scams',
            'dune-buggy-driving-rules-license-requirements-dubai',
            'dubai-desert-safari-with-toddlers-kids-family-handbook',
            'food-dining-dubai-desert-safari-bbq-menu-vegetarian-jain',
            'lahbab-red-dunes-vs-al-qudra-vs-ddcr-desert-locations',
            'best-dubai-combo-tour-packages-save-money',
            'bedouin-culture-dubai-camel-trekking-falconry-henna-guide',
            'abu-dhabi-shore-excursion-from-dubai-cruise-port-guide',
            'dubai-marina-dhow-cruise-deck-selection-timings-secrets'
        ];

        BlogPost::whereIn('slug', $targetSlugs)->delete();
    }
};
