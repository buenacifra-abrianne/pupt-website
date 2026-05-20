<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadableFile
{
    private const DEFAULT_DISK = 's3';
    private const MAX_BYTES = 20 * 1024 * 1024;

    public static function store(UploadedFile $file, string $directory = 'downloadables'): string|false
    {
        try {
            return $file->store($directory, self::disk());
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

        $normalized = ltrim($value, '/');

        try {
            Storage::disk(self::disk())->delete($normalized);
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

        $normalized = ltrim($value, '/');

        if (str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'storage/')) {
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

    private static function disk(): string
    {
        $disk = config('filesystems.default', self::DEFAULT_DISK);

        return is_string($disk) && $disk !== '' ? $disk : self::DEFAULT_DISK;
    }
}
