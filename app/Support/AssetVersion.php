<?php

namespace App\Support;

class AssetVersion
{
    public static function hash(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            return 'missing';
        }

        $hash = hash_file('sha256', $path);

        return $hash !== false
            ? substr($hash, 0, 12)
            : 'unavailable';
    }
}
