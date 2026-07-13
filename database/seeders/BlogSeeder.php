<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\BlogPostFaq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Blog Categories
        $catsPath = database_path('seeders/data/blog_categories.json');
        if (File::exists($catsPath)) {
            $cats = json_decode(File::get($catsPath), true);
            foreach ($cats as $cat) {
                BlogCategory::updateOrCreate(
                    ['id' => $cat['id']],
                    [
                        'name' => $cat['name'],
                        'slug' => $cat['slug'],
                        'description' => $cat['description'],
                        'meta_title' => $cat['meta_title'],
                        'meta_desc' => $cat['meta_desc'],
                        'og_image' => $cat['og_image'],
                        'priority' => (int)($cat['priority'] ?? 99),
                        'status' => $cat['status'] ?: 'active',
                    ]
                );
            }
        }

        // 2. Seed Blog Tags
        $tagsPath = database_path('seeders/data/blog_tags.json');
        if (File::exists($tagsPath)) {
            $tags = json_decode(File::get($tagsPath), true);
            foreach ($tags as $tag) {
                BlogTag::updateOrCreate(
                    ['id' => $tag['id']],
                    [
                        'name' => $tag['name'],
                        'slug' => $tag['slug'],
                    ]
                );
            }
        }

        // 3. Seed Blog Posts
        $postsPath = database_path('seeders/data/blog_posts.json');
        if (File::exists($postsPath)) {
            $posts = json_decode(File::get($postsPath), true);
            foreach ($posts as $p) {
                BlogPost::updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'slug' => $p['slug'],
                        'title' => $p['title'],
                        'subtitle' => $p['subtitle'],
                        'category_id' => $p['category_id'] ? (int)$p['category_id'] : null,
                        'excerpt' => $p['excerpt'],
                        'content' => $p['content'],
                        'author_name' => $p['author_name'] ?: 'Dunes Discovery Tourism',
                        'author_title' => $p['author_title'],
                        'author_bio' => $p['author_bio'],
                        'author_avatar' => $p['author_avatar'],
                        'featured_image' => $p['featured_image'],
                        'featured_image_alt' => $p['featured_image_alt'],
                        'featured_image_caption' => $p['featured_image_caption'],
                        'read_time' => (int)($p['read_time'] ?? 5),
                        'status' => $p['status'] ?: 'draft',
                        'is_featured' => (bool)$p['is_featured'],
                        'priority' => (int)($p['priority'] ?? 99),
                        'published_at' => $p['published_at'] ? date('Y-m-d H:i:s', strtotime($p['published_at'])) : null,
                        'meta_title' => $p['meta_title'],
                        'meta_desc' => $p['meta_desc'],
                        'meta_keywords' => $p['meta_keywords'],
                        'focus_keyword' => $p['focus_keyword'],
                        'canonical_url' => $p['canonical_url'],
                        'robots' => $p['robots'] ?: 'index, follow',
                        'og_title' => $p['og_title'],
                        'og_desc' => $p['og_desc'],
                        'og_image' => $p['og_image'],
                        'og_type' => $p['og_type'] ?: 'article',
                        'schema_type' => $p['schema_type'] ?: 'BlogPosting',
                        'ai_summary' => $p['ai_summary'],
                    ]
                );
            }
        }

        // 4. Seed Blog Post Tags Relationships
        $postTagsPath = database_path('seeders/data/blog_post_tags.json');
        if (File::exists($postTagsPath)) {
            $postTags = json_decode(File::get($postTagsPath), true);
            foreach ($postTags as $pt) {
                DB::table('blog_post_tags')->updateOrInsert(
                    ['post_id' => $pt['post_id'], 'tag_id' => $pt['tag_id']]
                );
            }
        }

        // 5. Seed Blog Post FAQs
        $postFaqsPath = database_path('seeders/data/blog_post_faqs.json');
        if (File::exists($postFaqsPath)) {
            $postFaqs = json_decode(File::get($postFaqsPath), true);
            foreach ($postFaqs as $pf) {
                BlogPostFaq::updateOrCreate(
                    ['id' => $pf['id']],
                    [
                        'post_id' => $pf['post_id'],
                        'question' => $pf['question'],
                        'answer' => $pf['answer'],
                        'priority' => (int)($pf['priority'] ?? 99),
                    ]
                );
            }
        }
    }
}
