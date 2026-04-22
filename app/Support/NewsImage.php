<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class NewsImage
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

    public static function store(UploadedFile $file, string $directory = 'news'): string|false
    {
        return ImageStorage::store($file, $directory);
    }

    public static function delete(?string $path): void
    {
        ImageStorage::delete($path);
    }

    public static function url(?string $path, ?string $fallback = null): ?string
    {
        return ImageStorage::url($path, $fallback);
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
}
