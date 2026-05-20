<?php

namespace Tests\Feature;

use App\Support\NewsImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageStorageTest extends TestCase
{
    public function test_news_image_upload_falls_back_to_public_disk_when_primary_disk_fails(): void
    {
        config([
            'filesystems.image_disk' => 'missing-news-disk',
            'filesystems.image_fallback_disk' => 'public',
        ]);

        Storage::fake('public');

        $path = NewsImage::store(UploadedFile::fake()->create('result.png', 100, 'image/png'), 'news');

        $this->assertIsString($path);
        $this->assertStringStartsWith('news/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
