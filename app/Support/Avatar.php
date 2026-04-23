<?php

namespace App\Support;

class Avatar
{
    public static function resolveUrl(?string $profilePicture): string
    {
        return (string) (ImageStorage::url($profilePicture) ?? '');
    }

    public static function initials(?string $name = '', ?string $firstName = '', ?string $lastName = ''): string
    {
        $primary = trim(implode(' ', array_filter([
            (string) $firstName,
            (string) $lastName,
        ], fn ($part) => trim((string) $part) !== '')));

        if ($primary === '') {
            $primary = trim((string) $name);
        }

        $tokens = preg_split('/\s+/', $primary) ?: [];
        $initials = '';

        foreach ($tokens as $token) {
            $trimmed = trim((string) $token);
            if ($trimmed === '') {
                continue;
            }

            $initials .= strtoupper(substr($trimmed, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }

        if ($initials !== '') {
            return $initials;
        }

        $fallback = trim((string) $firstName);
        if ($fallback === '') {
            $fallback = trim((string) $lastName);
        }
        if ($fallback === '') {
            $fallback = trim((string) $name);
        }
        if ($fallback === '') {
            $fallback = 'U';
        }

        return strtoupper(substr($fallback, 0, 1));
    }
}
