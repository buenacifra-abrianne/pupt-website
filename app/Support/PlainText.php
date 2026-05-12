<?php

namespace App\Support;

class PlainText
{
    public static function normalize(?string $value): string
    {
        $text = trim(HtmlEntities::decode((string) $value));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
