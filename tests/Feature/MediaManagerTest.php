<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Tour;
use App\Models\User;
use App\Services\MediaManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MediaManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?: User::factory()->create();
    }

    public function test_admin_can_access_media_library_index(): void
    {
        MediaAsset::create([
            'uuid' => 'test-uuid-1',
            'filename' => 'sunset-safari.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/sunset-safari.webp',
            'public_url' => '/images/sunset-safari.webp',
            'media_type' => 'image',
            'extension' => 'webp',
            'file_size_bytes' => 102400,
            'width' => 1200,
            'height' => 800,
            'category' => 'tours',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.media.index'));

        $response->assertStatus(200);
        $response->assertSee('Media Library &amp; Asset Manager', false);
        $response->assertSee('sunset-safari.webp');
    }

    public function test_admin_can_upload_media_asset(): void
    {
        $file = UploadedFile::fake()->image('desert-dune-quad.webp', 800, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'file' => $file,
            'category' => 'tours',
            'title' => 'Desert Dune Quad Biking',
            'alt_text' => 'Desert Dune Quad Biking Safari in Dubai',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('media_assets', [
            'category' => 'tours',
            'title' => 'Desert Dune Quad Biking',
            'media_type' => 'image',
        ]);

        // Clean up created file from public
        $asset = MediaAsset::where('title', 'Desert Dune Quad Biking')->first();
        if ($asset && File::exists(public_path($asset->file_path))) {
            @unlink(public_path($asset->file_path));
        }
    }

    public function test_admin_can_retrieve_asset_details(): void
    {
        $asset = MediaAsset::create([
            'uuid' => 'test-uuid-2',
            'filename' => 'vip-majlis-desert.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/vip-majlis-desert.webp',
            'public_url' => '/images/vip-majlis-desert.webp',
            'media_type' => 'image',
            'extension' => 'webp',
            'file_size_bytes' => 204800,
            'width' => 1920,
            'height' => 1080,
            'category' => 'tours',
            'title' => 'VIP Majlis Desert Camp',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.media.details', $asset->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'asset' => [
                'id' => $asset->id,
                'filename' => 'vip-majlis-desert.webp',
                'title' => 'VIP Majlis Desert Camp',
            ],
        ]);
    }

    public function test_admin_can_update_asset_metadata(): void
    {
        $asset = MediaAsset::create([
            'uuid' => 'test-uuid-3',
            'filename' => 'falcon-show.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/falcon-show.webp',
            'public_url' => '/images/falcon-show.webp',
            'media_type' => 'image',
            'category' => 'general',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.media.update', $asset->id), [
            'title' => 'Royal Falcon Show in Dubai',
            'alt_text' => 'Authentic bedouin royal falconry demonstration Dubai desert',
            'category' => 'tours',
            'caption' => 'Experience ancient falconry traditions.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('media_assets', [
            'id' => $asset->id,
            'title' => 'Royal Falcon Show in Dubai',
            'category' => 'tours',
        ]);
    }

    public function test_safe_deletion_blocks_in_use_asset_without_force(): void
    {
        $asset = MediaAsset::create([
            'uuid' => 'test-uuid-4',
            'filename' => 'evening-safari-hero.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/evening-safari-hero.webp',
            'public_url' => '/images/evening-safari-hero.webp',
            'media_type' => 'image',
        ]);

        Tour::create([
            'slug' => 'evening-desert-safari',
            'name' => 'Evening Desert Safari',
            'hero_image' => 'images/evening-safari-hero.webp',
            'status' => 'active',
        ]);

        // Attempt delete without force
        $response = $this->actingAs($this->admin)->deleteJson(route('admin.media.destroy', $asset->id), [
            'force' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'in_use' => true,
        ]);

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);

        // Now delete with force = true
        $forceResponse = $this->actingAs($this->admin)->deleteJson(route('admin.media.destroy', $asset->id), [
            'force' => true,
        ]);

        $forceResponse->assertStatus(200);
        $forceResponse->assertJson(['success' => true]);
    }

    public function test_bulk_category_update_and_delete(): void
    {
        $asset1 = MediaAsset::create([
            'uuid' => 'test-uuid-b1',
            'filename' => 'file1.webp',
            'file_path' => 'images/file1.webp',
            'public_url' => '/images/file1.webp',
            'category' => 'general',
        ]);

        $asset2 = MediaAsset::create([
            'uuid' => 'test-uuid-b2',
            'filename' => 'file2.webp',
            'file_path' => 'images/file2.webp',
            'public_url' => '/images/file2.webp',
            'category' => 'general',
        ]);

        // Bulk category
        $response = $this->actingAs($this->admin)->post(route('admin.media.bulk-action'), [
            'action' => 'set_category',
            'ids' => [$asset1->id, $asset2->id],
            'category' => 'banners',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('banners', $asset1->fresh()->category);
        $this->assertEquals('banners', $asset2->fresh()->category);

        // Bulk delete
        $delResponse = $this->actingAs($this->admin)->post(route('admin.media.bulk-action'), [
            'action' => 'delete',
            'ids' => [$asset1->id, $asset2->id],
            'force' => true,
        ]);

        $delResponse->assertStatus(200);
    }

    public function test_artisan_scan_command_executes_successfully(): void
    {
        $this->artisan('media:scan-assets')
            ->assertSuccessful();

        $this->artisan('media:scan-assets', ['--refresh-usages' => true])
            ->assertSuccessful();
    }

    public function test_clean_public_url_and_poster_url_accessors(): void
    {
        $video = MediaAsset::create([
            'uuid' => 'test-video-uuid',
            'filename' => 'buggy-ride-dubai-desert-review-1.mp4',
            'disk' => 'direct_public',
            'file_path' => 'videos/reviews/buggy-ride-dubai-desert-review-1.mp4',
            'public_url' => '/home/u410503041/domains/dunesdiscoverytourism.com/public_html/videos/reviews/buggy-ride-dubai-desert-review-1.mp4',
            'media_type' => 'video',
            'extension' => 'mp4',
        ]);

        $this->assertEquals('/videos/reviews/buggy-ride-dubai-desert-review-1.mp4', $video->clean_public_url);
        $this->assertStringContainsString('buggy-ride-dubai-desert-review-1', $video->poster_url);
    }

    public function test_picker_items_returns_paginated_json_with_filters(): void
    {
        MediaAsset::create([
            'uuid' => 'test-picker-1',
            'filename' => 'dune-buggy-adventure.webp',
            'disk' => 'direct_public',
            'file_path' => 'images/tours/dune-buggy-adventure.webp',
            'public_url' => '/images/tours/dune-buggy-adventure.webp',
            'media_type' => 'image',
            'extension' => 'webp',
            'category' => 'tours',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.media.picker-items', [
            'type' => 'image',
            'category' => 'tours',
            'search' => 'dune-buggy',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'filename' => 'dune-buggy-adventure.webp',
        ]);
    }

    public function test_deduplication_routine_merges_duplicate_records(): void
    {
        $primary = MediaAsset::create([
            'uuid' => 'test-primary',
            'filename' => 'ziina-icon.png',
            'file_path' => 'images/ziina-icon.png',
            'public_url' => '/images/ziina-icon.png',
            'file_hash' => 'hash1234567890abcdef',
            'usage_count' => 1,
            'usage_locations' => [['type' => 'Setting', 'field' => 'Payment Icon']],
        ]);

        $duplicate = MediaAsset::create([
            'uuid' => 'test-duplicate',
            'filename' => 'ziina-icon.png',
            'file_path' => 'images/blog/ziina-icon.png',
            'public_url' => '/images/blog/ziina-icon.png',
            'file_hash' => 'hash1234567890abcdef',
            'usage_count' => 1,
            'usage_locations' => [['type' => 'Blog Post', 'field' => 'Article Content']],
        ]);

        $service = app(MediaManagerService::class);
        $result = $service->deduplicateExistingAssets();

        $this->assertGreaterThanOrEqual(1, $result['deleted']);
        $this->assertDatabaseHas('media_assets', ['id' => $primary->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $duplicate->id]);

        $freshPrimary = $primary->fresh();
        $this->assertEquals(2, $freshPrimary->usage_count);
    }

    public function test_tour_creation_and_update_with_media_library_paths(): void
    {
        $category = \App\Models\Category::create([
            'slug' => 'desert-safari',
            'name' => 'Desert Safari',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'name' => 'Premium Evening Desert Safari',
            'category_id' => $category->id,
            'short_desc' => 'Short desert safari description',
            'full_desc' => 'Comprehensive detailed tour overview description',
            'duration' => '6 Hours',
            'priority' => 1,
            'status' => 'active',
            'hero_image' => 'images/tours/evening-safari-hero.webp',
            'thumb_image' => 'images/tours/evening-safari-thumb.webp',
            'gallery_images' => [
                'images/tours/slide-1.webp',
                'images/tours/slide-2.webp',
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $tour = Tour::where('slug', 'like', 'premium-evening-desert-safari%')->first();
        $this->assertNotNull($tour);
        $this->assertEquals('images/tours/evening-safari-hero.webp', $tour->hero_image);
        $this->assertEquals('images/tours/evening-safari-thumb.webp', $tour->thumb_image);
        $this->assertCount(2, $tour->gallery_images);

        // Update tour with changed media library selection
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour->id), [
            'name' => 'Premium Evening Desert Safari Updated',
            'category_id' => $category->id,
            'short_desc' => 'Short desert safari description',
            'full_desc' => 'Comprehensive detailed tour overview description',
            'duration' => '6 Hours',
            'priority' => 1,
            'status' => 'active',
            'hero_image' => 'images/tours/new-hero.webp',
            'thumb_image' => 'images/tours/new-thumb.webp',
            'gallery_images' => [
                'images/tours/slide-1.webp',
                'images/tours/slide-3.webp',
            ],
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $freshTour = $tour->fresh();
        $this->assertEquals('images/tours/new-hero.webp', $freshTour->hero_image);
        $this->assertEquals('images/tours/new-thumb.webp', $freshTour->thumb_image);
        $this->assertContains('images/tours/slide-3.webp', $freshTour->gallery_images);
    }
}
