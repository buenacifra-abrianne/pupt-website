<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventAnnouncementValidation
{
    public static function validate(Request $request, ?object $existing = null): void
    {
        if (strcasecmp(trim((string) $request->input('category')), 'Event') !== 0) {
            return;
        }

        $errors = [];

        if (trim((string) $request->input('title')) === '') {
            $errors['title'] = 'Event title is required.';
        }

        if (self::plainText((string) $request->input('content')) === '') {
            $errors['content'] = 'Event description is required.';
        }

        if (trim((string) $request->input('location')) === '') {
            $errors['location'] = 'Event venue is required.';
        }

        if (!self::hasImage($request, $existing)) {
            $errors['image'] = 'Event image is required.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private static function hasImage(Request $request, ?object $existing): bool
    {
        if ($request->hasFile('image')) {
            return true;
        }

        if ((string) $request->input('remove_image', '0') === '1') {
            return false;
        }

        if (trim((string) $request->input('existing_image_path')) !== '') {
            return true;
        }

        return trim((string) ($existing?->image_path ?? '')) !== '';
    }

    private static function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\x{00a0}/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
