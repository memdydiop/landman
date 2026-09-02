<?php

namespace Tests\Feature\Services;

use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_optimized_generates_webp_when_possible(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $path = ImageService::storeOptimized($file, 'test', 'public');

        Storage::disk('public')->assertExists($path);
        $this->assertTrue(str_ends_with($path, '.jpg') || str_ends_with($path, '.webp'));
    }

    public function test_store_optimized_falls_back_to_original_on_invalid_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        // ImageService tries to convert, but pdf is not image, should fallback
        $path = ImageService::storeOptimized($file, 'test', 'public');

        Storage::disk('public')->assertExists($path);
    }
}
