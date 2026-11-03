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
}
