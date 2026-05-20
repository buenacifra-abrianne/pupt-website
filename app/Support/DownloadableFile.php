<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadableFile
{
    private const DISK = 's3';
    private const MAX_BYTES = 20 * 1024 * 1024;

    public static function store(UploadedFile $file, string $directory = 'downloadables'): string|false
    {
        try {
            $storedPath = $file->store($directory, self::DISK);

            return is_string($storedPath) && $storedPath !== '' ? $storedPath : false;
        } catch (Throwable) {
            return false;
        }
    }

    public static function delete(?string $path): void
    {
        $value = trim((string) $path);

        if ($value === '' || self::isExternal($value)) {
            return;
        }

        $normalized = self::normalizeStoredPath($value);

        try {
            Storage::disk(self::DISK)->delete($normalized);
        } catch (Throwable) {
        }
    }

    public static function url(?string $path, ?string $fallback = null): ?string
    {
        $value = trim((string) $path);

        if ($value === '') {
            if ($fallback === null || $fallback === '') {
                return null;
            }

            return asset(ltrim($fallback, '/'));
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

    public static function validationError(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        if (!$file->isValid()) {
            return 'File upload failed during transfer.';
        }

        if (($file->getSize() ?? 0) > self::MAX_BYTES) {
            return 'File must not exceed 20 MB.';
        }

        return null;
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }

    private static function normalizeStoredPath(?string $path): string
    {
        $normalized = ltrim(trim((string) $path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return $normalized;
    }
}
