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
            return $file->store($directory, 's3');
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
            return Storage::disk('s3')->put($normalized, $contents);
        } catch (Throwable) {
            return false;
        }
    }

    public static function delete(?string $path): void
    {
        $normalized = self::normalizeStoredPath($path);

        if ($normalized === '' || self::isExternal($normalized)) {
            return;
        }

        try {
            Storage::disk('s3')->delete($normalized);
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

        return Storage::disk('s3')->url($normalized);
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