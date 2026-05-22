<?php

namespace Tests\Feature;

use App\Support\NewsImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageStorageTest extends TestCase
{
    public function test_news_image_upload_stores_on_s3_only(): void
    {
        config([
            'filesystems.image_disk' => 's3',
        ]);

        Storage::fake('s3');
        Storage::fake('public');

        $path = NewsImage::store(UploadedFile::fake()->create('result.png', 100, 'image/png'), 'news');

        $this->assertIsString($path);
        $this->assertStringStartsWith('news/', $path);
        Storage::disk('s3')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_news_image_url_uses_s3_disk(): void
    {
        config([
            'filesystems.image_disk' => 's3',
            'filesystems.disks.s3.url' => 'https://cdn.example.test',
        ]);

        $path = 'news/result.png';

        $this->assertSame('https://cdn.example.test/'.$path, NewsImage::url($path));
    }

    public function test_news_image_url_uses_public_fallback_when_file_exists_there(): void
    {
        config([
            'filesystems.image_disk' => 's3',
            'filesystems.image_fallback_disk' => 'public',
            'filesystems.disks.public.url' => 'https://app.example.test/storage',
            'filesystems.disks.s3.url' => 'https://cdn.example.test',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('news/result.png', 'image');

        $this->assertSame('/storage/news/result.png', NewsImage::url('news/result.png'));
    }
}
