<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\ImageOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ImageOptimizationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?: User::factory()->create();
        $this->service = app(ImageOptimizationService::class);
    }

    protected function tearDown(): void
    {
        $uploadDir = public_path('images/uploads');
        if (File::exists($uploadDir)) {
            foreach (File::files($uploadDir) as $file) {
                if (str_starts_with($file->getFilename(), 'test-opt-')) {
                    @unlink($file->getRealPath());
                }
            }
        }

        parent::tearDown();
    }

    protected function createRealPngImage(int $width = 100, int $height = 100, string $filename = 'test-opt-sample.png'): UploadedFile
    {
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_png_') . '.png';
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $trans);

        $gold = imagecolorallocatealpha($img, 218, 140, 36, 0);
        imagefilledrectangle($img, 10, 10, $width - 10, $height - 10, $gold);

        imagepng($img, $tempPath);
        imagedestroy($img);

        return new UploadedFile($tempPath, $filename, 'image/png', null, true);
    }

    protected function createRealJpgImage(int $width = 100, int $height = 100, string $filename = 'test-opt-photo.jpg'): UploadedFile
    {
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_jpg_') . '.jpg';
        $img = imagecreatetruecolor($width, $height);
        $red = imagecolorallocate($img, 200, 50, 50);
        imagefill($img, 0, 0, $red);

        imagejpeg($img, $tempPath, 90);
        imagedestroy($img);

        return new UploadedFile($tempPath, $filename, 'image/jpeg', null, true);
    }

    public function test_image_optimization_service_detects_formats_and_convertibility(): void
    {
        $supported = $this->service->getSupportedFormats();
        $this->assertArrayHasKey('webp', $supported);
        $this->assertArrayHasKey('avif', $supported);
        $this->assertTrue($supported['webp']);

        $pngFile = $this->createRealPngImage();
        $this->assertTrue($this->service->needsConversion($pngFile));
        $this->assertTrue($this->service->isConvertibleImage($pngFile));
        $this->assertFalse($this->service->isOptimized($pngFile));

        $webpFile = UploadedFile::fake()->create('already-optimized.webp', 100, 'image/webp');
        $this->assertFalse($this->service->needsConversion($webpFile));
        $this->assertTrue($this->service->isOptimized($webpFile));
    }

    public function test_png_image_is_converted_to_lossless_webp_on_upload(): void
    {
        Storage::fake('public');
        $pngFile = $this->createRealPngImage(120, 120, 'test-opt-desert-dune.png');

        $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'files' => [$pngFile],
            'category' => 'tours',
            'convert_format' => 'webp',
            'convert_quality' => 'lossless',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $uploaded = $response->json('uploaded.0');
        $this->assertNotNull($uploaded);
        $this->assertStringEndsWith('.webp', $uploaded['filename']);

        // Assert conversions metadata in response
        $conversions = $response->json('conversions');
        $this->assertCount(1, $conversions);
        $this->assertEquals('png', $conversions[0]['original_format']);
        $this->assertEquals('webp', $conversions[0]['target_format']);

        // Assert database record has webp extension
        $this->assertDatabaseHas('media_assets', [
            'id' => $uploaded['id'],
            'extension' => 'webp',
            'mime_type' => 'image/webp',
        ]);
    }

    public function test_jpeg_image_is_converted_to_avif_on_upload(): void
    {
        Storage::fake('public');
        $jpgFile = $this->createRealJpgImage(150, 150, 'test-opt-safari-sunset.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'files' => [$jpgFile],
            'category' => 'tours',
            'convert_format' => 'avif',
            'convert_quality' => 'lossless',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $uploaded = $response->json('uploaded.0');
        $this->assertNotNull($uploaded);
        $this->assertStringEndsWith('.avif', $uploaded['filename']);

        // Assert conversions metadata
        $conversions = $response->json('conversions');
        $this->assertCount(1, $conversions);
        $this->assertEquals('avif', $conversions[0]['target_format']);

        $this->assertDatabaseHas('media_assets', [
            'id' => $uploaded['id'],
            'extension' => 'avif',
            'mime_type' => 'image/avif',
        ]);
    }

    public function test_image_upload_with_original_format_bypasses_conversion(): void
    {
        Storage::fake('public');
        $pngFile = $this->createRealPngImage(80, 80, 'test-opt-raw-logo.png');

        $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'files' => [$pngFile],
            'category' => 'site',
            'convert_format' => 'original',
        ]);

        $response->assertStatus(200);
        $uploaded = $response->json('uploaded.0');
        $this->assertNotNull($uploaded);
        $this->assertStringEndsWith('.png', $uploaded['filename']);

        // Converted metadata should be empty
        $conversions = $response->json('conversions');
        $this->assertEmpty($conversions);

        $this->assertDatabaseHas('media_assets', [
            'id' => $uploaded['id'],
            'extension' => 'png',
        ]);
    }

    public function test_inspect_optimization_endpoint_identifies_convertible_files(): void
    {
        $pngFile = $this->createRealPngImage(90, 90, 'test-opt-badge.png');
        $webpFile = UploadedFile::fake()->create('test-opt-ready.webp', 50, 'image/webp');

        $response = $this->actingAs($this->admin)->post(route('admin.media.inspect-optimization'), [
            'files' => [$pngFile, $webpFile],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_convertible' => true,
        ]);

        $files = $response->json('files');
        $this->assertCount(2, $files);
        $this->assertTrue($files[0]['needs_conversion']);
        $this->assertFalse($files[1]['needs_conversion']);
    }

    public function test_duplicate_detection_still_functions_with_converted_media(): void
    {
        Storage::fake('public');
        $png1 = $this->createRealPngImage(110, 110, 'test-opt-duplicate-hero.png');
        $png2 = $this->createRealPngImage(110, 110, 'test-opt-duplicate-hero.png');

        // First upload converts to WebP
        $firstResponse = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'files' => [$png1],
            'category' => 'tours',
            'convert_format' => 'webp',
            'convert_quality' => 'lossless',
        ]);
        $firstResponse->assertStatus(200);
        $firstId = $firstResponse->json('uploaded.0.id');

        // Attempt second upload with identical content converted to WebP without force_upload
        $secondResponse = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
            'files' => [$png2],
            'category' => 'tours',
            'convert_format' => 'webp',
            'convert_quality' => 'lossless',
        ]);

        $secondResponse->assertStatus(409);
        $secondResponse->assertJson([
            'success' => false,
            'is_duplicate' => true,
        ]);
        $this->assertEquals($firstId, $secondResponse->json('duplicate.id'));
    }
}
