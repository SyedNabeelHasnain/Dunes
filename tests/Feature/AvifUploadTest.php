<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AvifUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function createTempAvif(string $name = 'test.avif'): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'avif_test_') . '.avif';
        copy(public_path('images/desert-safari-poster.avif'), $tempPath);

        return new UploadedFile(
            $tempPath,
            $name,
            'image/avif',
            null,
            true
        );
    }

    public function test_image_rule_accepts_avif_files(): void
    {
        $avifFile = $this->createTempAvif('test-desert.avif');

        $validator = Validator::make(
            ['test_image' => $avifFile],
            ['test_image' => 'image']
        );

        $this->assertTrue($validator->passes(), 'The image validator rule failed on AVIF: ' . json_encode($validator->errors()->all()));
    }

    public function test_image_and_mimes_combined_rule_accepts_avif_files(): void
    {
        $avifFile = $this->createTempAvif('og-card.avif');

        $validator = Validator::make(
            ['seo_default_og_image_file' => $avifFile],
            ['seo_default_og_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:8192']
        );

        $this->assertTrue($validator->passes(), 'The image|mimes rule failed on AVIF: ' . json_encode($validator->errors()->all()));
    }

    public function test_admin_settings_accepts_seo_default_og_image_avif_upload(): void
    {
        $admin = User::factory()->create();
        $avifFile = $this->createTempAvif('social-card-custom.avif');

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'seo_default_og_image_file' => $avifFile,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $savedSetting = Setting::where('setting_key', 'seo_default_og_image')->first();
        $this->assertNotNull($savedSetting, 'seo_default_og_image was not saved');
        $this->assertStringEndsWith('.avif', $savedSetting->setting_value);

        // Cleanup moved file
        $uploadedPath = public_path(ltrim($savedSetting->setting_value, '/'));
        if (File::exists($uploadedPath)) {
            File::delete($uploadedPath);
        }
    }

    public function test_tour_creation_accepts_avif_hero_and_thumb_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $category = Category::create([
            'name' => 'Desert Safaris',
            'slug' => 'desert-safari',
            'status' => 'active',
        ]);

        $heroAvif = $this->createTempAvif('tour-hero.avif');
        $thumbAvif = $this->createTempAvif('tour-thumb.avif');

        $response = $this->actingAs($admin)->post(route('admin.tours.store'), [
            'name' => 'Test AVIF Tour',
            'category_id' => $category->id,
            'short_desc' => 'Short test description',
            'full_desc' => 'Full test description goes here with details.',
            'duration' => '6 Hours',
            'priority' => 1,
            'status' => 'active',
            'hero_image' => $heroAvif,
            'thumb_image' => $thumbAvif,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.tours.index'));

        $tour = Tour::where('slug', 'test-avif-tour')->first();
        $this->assertNotNull($tour, 'Tour was not created');
        $this->assertStringEndsWith('.avif', $tour->hero_image);
        $this->assertStringEndsWith('.avif', $tour->thumb_image);
    }

    public function test_blog_creation_accepts_avif_featured_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $category = BlogCategory::create([
            'name' => 'Travel Tips',
            'slug' => 'travel-tips',
            'status' => 'active',
        ]);

        $blogAvif = $this->createTempAvif('blog-cover.avif');

        $response = $this->actingAs($admin)->post(route('admin.blogs.store'), [
            'title' => 'Test AVIF Blog Post',
            'category_id' => $category->id,
            'excerpt' => 'Short blog excerpt for testing.',
            'content' => 'Complete blog content with rich narrative.',
            'status' => 'published',
            'featured_image' => $blogAvif,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.blogs.index'));

        $post = BlogPost::where('slug', 'test-avif-blog-post')->first();
        $this->assertNotNull($post, 'Blog post was not created');
        $this->assertStringEndsWith('.avif', $post->featured_image);
    }
}
