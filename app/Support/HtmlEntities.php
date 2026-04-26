<?php

namespace App\Support;

class HtmlEntities
{
    public static function decode(string $value): string
    {
        if ($value === '' || !str_contains($value, '&')) {
            return $value;
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
