<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NewsImage
{
    private const DISK = 'public';

    public static function store(UploadedFile $file, string $directory = 'news'): string|false
    {
        return $file->store($directory, self::DISK);
    }

    public static function delete(?string $path): void
    {
        $value = trim((string) $path);

        if ($value === '' || self::isExternal($value)) {
            return;
        }

        Storage::disk(self::DISK)->delete(ltrim($value, '/'));
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

        return asset('storage/'.$normalized);
    }

    private static function isExternal(string $path): bool
    {
        return preg_match('/^(https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
    }
}
