<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageStorage
{
    public static function store(UploadedFile $file, string $directory = 'images'): string|false
    {
        foreach (self::candidateDisks() as $disk) {
            try {
                $stored = $file->storePublicly($directory, ['disk' => $disk]);
                if ($stored !== false) {
                    return $stored;
                }
            } catch (Throwable) {
            }
        }

        return false;
    }

    public static function put(string $path, string $contents): bool
    {
        $normalized = self::normalizeStoredPath($path);

        if ($normalized === '') {
            return false;
        }

        foreach (self::candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->put($normalized, $contents, ['visibility' => 'public'])) {
                    return true;
                }
            } catch (Throwable) {
            }
        }

        return false;
    }

    public static function delete(?string $path): void
    {
        $normalized = self::normalizeStoredPath((string) $path);

        if ($normalized === '' || self::isExternal($normalized)) {
            return;
        }

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
            if ($fallback === null || trim($fallback) === '') {
                return null;
            }

            return asset(ltrim($fallback, '/'));
        }

        if (self::isExternal($value)) {
            return $value;
        }

        $normalized = ltrim($value, '/');

        if (str_starts_with($normalized, 'assets/')) {
            return asset($normalized);
        }

        $legacyPublicPath = $normalized;
        if (str_starts_with($legacyPublicPath, 'storage/')) {
            return asset($legacyPublicPath);
        }

        $normalized = self::normalizeStoredPath($normalized);

        $fallbackDisk = self::fallbackDisk();
        if ($fallbackDisk !== '') {
            try {
                if (Storage::disk($fallbackDisk)->exists($normalized)) {
                    return asset('storage/'.$normalized);
                }
            } catch (Throwable) {
            }
        }

        foreach (self::candidateDisks() as $disk) {
            if ($disk === $fallbackDisk) {
                continue;
            }

            try {
                return Storage::disk($disk)->url($normalized);
            } catch (Throwable) {
            }
        }

        return asset('storage/'.$normalized);
    }

    public static function normalizeStoredPath(?string $path): string
    {
        $normalized = ltrim(trim((string) $path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return $normalized;
    }

    private static function candidateDisks(): array
    {
        return array_values(array_unique(array_filter([
            self::primaryDisk(),
            self::fallbackDisk(),
        ], static fn ($disk) => is_string($disk) && trim($disk) !== '')));
    }

    private static function primaryDisk(): string
    {
        return self::normalizeDiskName((string) config('filesystems.image_disk', 's3'));
    }

    private static function fallbackDisk(): string
    {
        return self::normalizeDiskName((string) config('filesystems.image_fallback_disk', 'public'));
    }

    private static function normalizeDiskName(string $disk): string
    {
        $normalized = trim($disk);

        // Public-facing images should never use Laravel's private local disk.
        if ($normalized === 'local') {
            return 'public';
        }

        return $normalized;
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }
}
