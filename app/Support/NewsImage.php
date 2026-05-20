<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NewsImage
{
    private const DEFAULT_DISK = 's3';
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

    public static function store(UploadedFile $file, string $directory = 'news'): string|false
    {
        $storedPath = self::storeOnDisk($file, $directory, self::disk());

        if ($storedPath !== false) {
            return $storedPath;
        }

        $fallbackDisk = self::fallbackDisk();

        if ($fallbackDisk === self::disk()) {
            return false;
        }

        return self::storeOnDisk($file, $directory, $fallbackDisk);
    }

    public static function delete(?string $path): void
    {
        $normalized = ImageStorage::normalizeStoredPath($path);

        if ($normalized !== '' && !self::isExternal($normalized)) {
            foreach (array_unique([self::disk(), self::fallbackDisk()]) as $disk) {
                try {
                    Storage::disk($disk)->delete($normalized);
                } catch (Throwable) {
                }
            }
        }
    }

    public static function url(?string $path, ?string $fallback = null): ?string
    {
        if (!$path) {
            return $fallback ? asset(ltrim($fallback, '/')) : null;
        }

        if (self::isExternal($path)) {
            return $path;
        }

        $normalized = ImageStorage::normalizeStoredPath($path);

        if (str_starts_with($normalized, 'assets/')) {
            return asset($normalized);
        }

        try {
            return Storage::disk(self::disk())->url($normalized);
        } catch (Throwable) {
            return asset('storage/' . $normalized);
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

        $detectedType = $clientMime !== ''
            ? $clientMime
            : ($serverMime !== '' ? $serverMime : ($extension !== '' ? $extension : 'unknown'));

        return 'Unsupported image file type: '.$detectedType.'.';
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }

    private static function disk(): string
    {
        $disk = config('filesystems.image_disk', self::DEFAULT_DISK);

        return is_string($disk) && $disk !== '' ? $disk : self::DEFAULT_DISK;
    }

    private static function fallbackDisk(): string
    {
        $disk = config('filesystems.image_fallback_disk', 'public');

        return is_string($disk) && $disk !== '' ? $disk : 'public';
    }

    private static function storeOnDisk(UploadedFile $file, string $directory, string $disk): string|false
    {
        try {
            $storedPath = $file->store($directory, $disk);

            return is_string($storedPath) && $storedPath !== '' ? $storedPath : false;
        } catch (Throwable) {
            return false;
        }
    }
}
