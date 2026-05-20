<?php

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadableFile
{
    private const PRIMARY_DISK = 's3';
    private const FALLBACK_DISK = 'public';
    private const MAX_BYTES = 20 * 1024 * 1024;

    public static function store(UploadedFile $file, string $directory = 'downloadables'): string|false
    {
        foreach (self::candidateDisks() as $disk) {
            try {
                $stored = $file->store($directory, $disk);
                if ($stored !== false) {
                    return $stored;
                }
            } catch (Throwable) {
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
            if ($fallback === null || $fallback === '') {
                return null;
            }

            return asset(ltrim($fallback, '/'));
        }

        if (self::isExternal($value)) {
            return $value;
        }

        $normalized = ltrim($value, '/');

        if (str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        if (App::environment('local')) {
            try {
                if (Storage::disk(self::FALLBACK_DISK)->exists($normalized)) {
                    return asset('storage/' . $normalized);
                }
            } catch (Throwable) {
            }
        }

        foreach (self::candidateDisks() as $disk) {
            if ($disk === self::FALLBACK_DISK) {
                continue;
            }

            try {
                return Storage::disk($disk)->url($normalized);
            } catch (Throwable) {
            }
        }

        return asset('storage/' . $normalized);
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

    private static function candidateDisks(): array
    {
        $disks = App::environment('local')
            ? [self::FALLBACK_DISK, self::PRIMARY_DISK]
            : [self::PRIMARY_DISK, self::FALLBACK_DISK];

        return array_values(array_unique(array_filter($disks, static fn ($disk) => is_string($disk) && $disk !== '')));
    }
}