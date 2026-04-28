<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageStorage
{
    private const DISK = 's3';

    public static function store(UploadedFile $file, string $directory = 'images'): string|false
    {
        try {
            return $file->storePublicly($directory, ['disk' => self::DISK]);
        } catch (Throwable) {
            return false;
        }
    }

    public static function put(string $path, string $contents): bool
    {
        $normalized = self::normalizeStoredPath($path);

        if ($normalized === '') {
            return false;
        }

        try {
            return Storage::disk(self::DISK)->put($normalized, $contents, [
                'visibility' => 'public',
            ]);
        } catch (Throwable) {
            return false;
        }
    }

    public static function delete(?string $path): void
    {
        $normalized = self::normalizeStoredPath((string) $path);

        if ($normalized === '' || self::isExternal($normalized)) {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($normalized);
        } catch (Throwable) {
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

        if (str_starts_with($normalized, 'assets/')) {
            return asset($normalized);
        }

        try {
            return Storage::disk(self::DISK)->url($normalized);
        } catch (Throwable) {
            return $fallback ? asset(ltrim($fallback, '/')) : null;
        }
    }

    public static function normalizeStoredPath(?string $path): string
    {
        $normalized = ltrim(trim((string) $path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return $normalized;
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }
}