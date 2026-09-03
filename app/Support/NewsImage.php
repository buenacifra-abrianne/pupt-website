<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NewsImage
{
    private const MAX_BYTES = 20 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

    public static function store(UploadedFile $file, string $directory = 'news'): string|false
    {
        $disk = self::disk();

        return $file->store($directory, $disk);
    }

    public static function delete(?string $path): void
    {
        $normalized = self::normalizeStoredPath($path);

        if ($normalized === '' || self::isExternal($normalized) || str_starts_with($normalized, 'assets/')) {
            return;
        }

        foreach (array_unique([self::disk(), self::fallbackDisk()]) as $disk) {
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
            return $fallback ? asset(ltrim($fallback, '/')) : null;
        }

        if (self::isExternal($value)) {
            return $value;
        }

        $normalized = self::normalizeStoredPath($value);

        if ($normalized === '') {
            return $fallback ? asset(ltrim($fallback, '/')) : null;
        }

        if (str_starts_with($normalized, 'assets/')) {
            return asset($normalized);
        }

        $disk = self::disk();
        $fallbackDisk = self::fallbackDisk();

        if ($fallbackDisk !== $disk) {
            try {
                if (Storage::disk($fallbackDisk)->exists($normalized)) {
                    return Storage::disk($fallbackDisk)->url($normalized);
                }
            } catch (Throwable) {
            }
        }

        return Storage::disk($disk)->url($normalized);
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
            return 'Image must not exceed 20 MB.';
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $clientMime = strtolower((string) $file->getClientMimeType());
        $serverMime = strtolower((string) ($file->getMimeType() ?? ''));

        $extensionAllowed = in_array($extension, self::ALLOWED_EXTENSIONS, true);
        $looksLikeImage = str_starts_with($clientMime, 'image/')
            || str_starts_with($serverMime, 'image/');

        if ($extensionAllowed && $looksLikeImage) {
            return null;
        }

        $detectedType = $clientMime !== ''
            ? $clientMime
            : ($serverMime !== '' ? $serverMime : ($extension !== '' ? $extension : 'unknown'));

        return 'Unsupported image file type: '.$detectedType.'.';
    }

    public static function normalizeStoredPath(?string $path): string
    {
        $normalized = ltrim(trim((string) $path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return $normalized;
    }

    private static function disk(): string
    {
        $disk = (string) config('filesystems.image_disk', 's3');

        return $disk !== '' ? $disk : 's3';
    }

    private static function fallbackDisk(): string
    {
        $disk = (string) config('filesystems.image_fallback_disk', 'public');

        return $disk !== '' ? $disk : 'public';
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }
}
