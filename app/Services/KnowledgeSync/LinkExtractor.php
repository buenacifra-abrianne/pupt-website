<?php

namespace App\Services\KnowledgeSync;

use DOMDocument;
use DOMXPath;

class LinkExtractor
{
    /**
     * @return array<int, array{url:string,anchor_text:?string,source_location:?string}>
     */
    public function extractFromHtml(string $html, ?string $sourceLocation = null): array
    {
        $results = [];

        if (trim($html) === '') {
            return $results;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<html><body>'.$html.'</body></html>';
        $document->loadHTML('<?xml encoding="UTF-8">'.$wrapped, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new DOMXPath($document);

        foreach (['href', 'src', 'data-href', 'data-url', 'to', 'action'] as $attribute) {
            foreach ($xpath->query('//*[@'.$attribute.']') ?: [] as $node) {
                $url = trim((string) $node->attributes?->getNamedItem($attribute)?->nodeValue);
                if ($url === '') {
                    continue;
                }

                $anchorText = trim((string) $node->textContent);
                $results[] = [
                    'url' => $url,
                    'anchor_text' => $anchorText !== '' ? $anchorText : null,
                    'source_location' => $sourceLocation,
                ];
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($this->extractFromText($html) as $match) {
            $results[] = [
                'url' => $match,
                'anchor_text' => null,
                'source_location' => $sourceLocation,
            ];
        }

        return $this->uniqueByRawUrl($results);
    }

    /**
     * @return string[]
     */
    public function extractFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $urls = [];

        if (preg_match_all('/\[[^\]]+\]\(([^)\s]+)\)/', $text, $markdownMatches)) {
            foreach ($markdownMatches[1] as $match) {
                $urls[] = trim((string) $match);
            }
        }

        if (preg_match_all('/\bhttps?:\/\/[^\s"\'\)\]>]+/i', $text, $httpMatches)) {
            foreach ($httpMatches[0] as $match) {
                $urls[] = trim((string) $match);
            }
        }

        if (preg_match_all('/(?:href|src|url|to|action)\s*[:=]\s*["\'`](.+?)["\'`]/i', $text, $attrMatches)) {
            foreach ($attrMatches[1] as $match) {
                $urls[] = trim((string) $match);
            }
        }

        if (preg_match_all('/url\(([^)]+)\)/i', $text, $cssMatches)) {
            foreach ($cssMatches[1] as $match) {
                $urls[] = trim((string) trim($match, '\"\''));
            }
        }

        return array_values(array_unique(array_filter($urls, static fn ($url) => $url !== '')));
    }

    /**
     * @return string[]
     */
    public function extractFromSitemapXml(string $xml): array
    {
        if (trim($xml) === '') {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadXML($xml);

        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return [];
        }

        $xpath = new DOMXPath($document);
        $urls = [];

        foreach ($xpath->query('//*[local-name()="loc"]') ?: [] as $node) {
            $value = trim((string) $node->textContent);
            if ($value !== '') {
                $urls[] = $value;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return array_values(array_unique($urls));
    }

    /**
     * @param array<int, array{url:string,anchor_text:?string,source_location:?string}> $rows
     * @return array<int, array{url:string,anchor_text:?string,source_location:?string}>
     */
    private function uniqueByRawUrl(array $rows): array
    {
        $seen = [];
        $out = [];

        foreach ($rows as $row) {
            $key = strtolower(trim($row['url'])).'|'.strtolower((string) $row['source_location']);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $out[] = $row;
        }

        return $out;
    }
}
