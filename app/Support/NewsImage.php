<?php

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NewsImage
{
    private const PRIMARY_DISK = 's3';
    private const FALLBACK_DISK = 'public';
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

    public static function store(UploadedFile $file, string $directory = 'news'): string|false
{
    foreach (self::candidateDisks() as $disk) {
        try {
            \Log::info("Trying upload to disk: " . $disk);

            $stored = $file->store($directory, $disk);

            \Log::info("Upload result: " . json_encode($stored));

            if ($stored !== false) {
                return $stored;
            }

        } catch (Throwable $e) {
            \Log::error("S3 upload failed on disk {$disk}: " . $e->getMessage());
        }
    }

    return false;
}

    public static function delete(?string $path): void
    {
        $value = trim((string) $path);

        if ($value === '' || self::isExternal($value)) {
            return;
        }

        $normalized = ltrim($value, '/');

        foreach (self::candidateDisks() as $disk) {
            try {
                Storage::disk($disk)->delete($normalized);
            } catch (Throwable) {
            }
        }
    }

    public static function url(?string $path, ?string $fallback = null): ?string
    {
        $value = trim((string) $path);

        if ($value === '') {
            return null;
        }

        if (self::isExternal($value)) {
            return $value;
        }

        $normalized = ltrim($value, '/');

        try {
            return Storage::disk('s3')->url($normalized);
        } catch (Throwable) {
            return asset('storage/'.$normalized);
        }
    }

    public static function validationError(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        if (!$file->isValid()) {
            return 'Image upload failed during transfer.';
        }

        if (($file->getSize() ?? 0) > self::MAX_BYTES) {
            return 'Image must not exceed 5 MB.';
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $clientMime = strtolower((string) $file->getClientMimeType());
        $serverMime = strtolower((string) ($file->getMimeType() ?? ''));

        $looksLikeImage = str_starts_with($clientMime, 'image/')
            || str_starts_with($serverMime, 'image/')
            || in_array($extension, self::ALLOWED_EXTENSIONS, true);

        if ($looksLikeImage) {
            return null;
        }

        $detectedType = $clientMime !== '' ? $clientMime : ($serverMime !== '' ? $serverMime : ($extension !== '' ? $extension : 'unknown'));

        return 'Unsupported image file type: '.$detectedType.'.';
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }

    private static function candidateDisks(): array
    {
        $disks = App::environment('local')
            ? [self::FALLBACK_DISK, self::PRIMARY_DISK]
            : [self::PRIMARY_DISK, self::FALLBACK_DISK];

        return array_values(array_unique(array_filter($disks, static fn ($disk) => is_string($disk) && $disk !== '')));
    }
}
