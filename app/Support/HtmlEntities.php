<?php

namespace App\Support;

class HtmlEntities
{
    public static function decode(string $value): string
    {
        if ($value === '' || !str_contains($value, '&')) {
            return $value;
        }

        $decoded = $value;

        for ($i = 0; $i < 5; $i++) {
            $next = htmlspecialchars_decode($decoded, ENT_QUOTES | ENT_HTML5);
            $next = html_entity_decode($next, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $next = preg_replace('/&amp;?(?![A-Za-z0-9#])/i', '&', $next) ?? $next;

            if ($next === $decoded || !str_contains($next, '&')) {
                return $next;
            }

            $decoded = $next;
        }

        return $decoded;
    }
}
