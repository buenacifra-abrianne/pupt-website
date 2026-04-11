<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class RichText
{
    /**
     * @var array<string, string[]>
     */
    private const ALLOWED_TAGS = [
        'p' => ['style'],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'span' => ['style'],
        'ul' => [],
        'ol' => [],
        'li' => ['style'],
        'a' => ['href', 'target', 'rel', 'style'],
        'div' => ['style'],
        'blockquote' => ['style'],
    ];

    public static function sanitize(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument('1.0', 'UTF-8');
        $html = '<div id="rich-text-root">'.$value.'</div>';
        $document->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        /** @var DOMElement|null $root */
        $root = $document->getElementById('rich-text-root');
        if (!$root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return e(strip_tags($value));
        }

        self::sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($output);
    }

    public static function plainText(?string $value): string
    {
        $sanitized = self::sanitize($value);
        $text = html_entity_decode(strip_tags($sanitized), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    public static function excerpt(?string $value, int $limit = 200): string
    {
        $text = self::plainText($value);
        if ($text === '') {
            return '';
        }

        return e(Str::limit($text, $limit));
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        for ($child = $node->firstChild; $child !== null; $child = $next) {
            $next = $child->nextSibling;

            if ($child instanceof DOMElement) {
                self::sanitizeElement($child);
                continue;
            }

            self::sanitizeChildren($child);
        }
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);

        if (!array_key_exists($tagName, self::ALLOWED_TAGS)) {
            self::unwrapNode($element);
            return;
        }

        self::sanitizeAttributes($element, self::ALLOWED_TAGS[$tagName]);
        self::sanitizeChildren($element);
    }

    /**
     * @param string[] $allowedAttributes
     */
    private static function sanitizeAttributes(DOMElement $element, array $allowedAttributes): void
    {
        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);
            if (!$attribute) {
                continue;
            }

            $name = strtolower($attribute->nodeName);
            if (!in_array($name, $allowedAttributes, true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if ($element->tagName === 'a' && $name === 'href') {
                $href = trim($attribute->nodeValue);
                if (!self::isAllowedHref($href)) {
                    $element->removeAttribute('href');
                }
            }

            if ($name === 'style') {
                $sanitizedStyle = self::sanitizeStyleAttribute($attribute->nodeValue);
                if ($sanitizedStyle === '') {
                    $element->removeAttribute('style');
                } else {
                    $element->setAttribute('style', $sanitizedStyle);
                }
            }
        }

        if (strtolower($element->tagName) === 'a') {
            $target = strtolower(trim((string) $element->getAttribute('target')));
            if ($target === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            }
        }
    }

    private static function isAllowedHref(string $href): bool
    {
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, '/')) {
            return true;
        }

        return (bool) preg_match('/^(https?:|mailto:|tel:)/i', $href);
    }

    private static function sanitizeStyleAttribute(string $style): string
    {
        $style = trim($style);
        if ($style === '') {
            return '';
        }

        $allowed = [];
        $declarations = preg_split('/\s*;\s*/', $style) ?: [];

        foreach ($declarations as $declaration) {
            if ($declaration === '' || !str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            if ($property === '' || $value === '') {
                continue;
            }

            $sanitizedValue = match ($property) {
                'color', 'background-color' => self::sanitizeColorValue($value),
                'font-size' => self::sanitizeFontSizeValue($value),
                'text-align' => self::sanitizeTextAlignValue($value),
                'line-height' => self::sanitizeLineHeightValue($value),
                'vertical-align' => self::sanitizeVerticalAlignValue($value),
                'display' => self::sanitizeDisplayValue($value),
                default => '',
            };

            if ($sanitizedValue !== '') {
                $allowed[$property] = $sanitizedValue;
            }
        }

        $normalized = [];
        foreach ($allowed as $property => $value) {
            $normalized[] = $property.': '.$value;
        }

        return implode('; ', $normalized);
    }

    private static function sanitizeColorValue(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^#[0-9a-f]{3,8}$/i', $value)) {
            return strtoupper($value);
        }

        if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/i', $value)) {
            return $value;
        }

        if (preg_match('/^hsla?\(\s*\d{1,3}(?:deg)?\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/i', $value)) {
            return $value;
        }

        if (preg_match('/^(?:inherit|transparent|currentcolor|black|silver|gray|white|maroon|red|purple|fuchsia|green|lime|olive|yellow|navy|blue|teal|aqua|orange|brown)$/i', $value)) {
            return strtolower($value) === 'currentcolor' ? 'currentColor' : $value;
        }

        return '';
    }

    private static function sanitizeFontSizeValue(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d+(?:\.\d+)?(?:px|em|rem|%)$/i', $value) ? strtolower($value) : '';
    }

    private static function sanitizeTextAlignValue(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['left', 'center', 'right', 'justify'], true) ? $value : '';
    }

    private static function sanitizeLineHeightValue(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d+(?:\.\d+)?(?:px|em|rem|%)?$/i', $value) ? strtolower($value) : '';
    }

    private static function sanitizeVerticalAlignValue(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['baseline', 'middle', 'sub', 'super', 'top', 'bottom'], true) ? $value : '';
    }

    private static function sanitizeDisplayValue(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['inline', 'inline-block', 'block'], true) ? $value : '';
    }

    private static function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
