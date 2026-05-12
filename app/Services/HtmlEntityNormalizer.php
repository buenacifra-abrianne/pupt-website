<?php

namespace App\Services;

use App\Support\PlainText;

class HtmlEntityNormalizer
{
    /**
     * Normalize plain-text fields before they are stored or rendered.
     *
     * This is for titles, names, categories, locations, labels, etc.
     * Do not use it as an HTML sanitizer for rich content.
     */
    public static function plain(?string $value): string
    {
        return PlainText::normalize($value);
    }
}
