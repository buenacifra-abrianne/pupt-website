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
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'a' => ['href', 'target', 'rel'],
        'div' => [],
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
