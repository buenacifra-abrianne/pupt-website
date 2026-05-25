<?php

namespace App\Services\KnowledgeSync;

use DOMDocument;
use DOMXPath;

class TextExtractor
{
    public function extract(string $contentType, string $body, string $sourceUrl): ?ExtractedDocument
    {
        $contentType = strtolower(trim($contentType));

        if (str_starts_with($contentType, 'text/html')) {
            return $this->extractFromHtml($body);
        }

        if (str_starts_with($contentType, 'text/plain')) {
            $text = $this->limitText(trim($body));

            return new ExtractedDocument($sourceUrl, $text);
        }

        if (str_starts_with($contentType, 'application/pdf')) {
            $text = $this->limitText($this->extractFromPdf($body));

            return new ExtractedDocument($sourceUrl, $text);
        }

        if (str_starts_with($contentType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
            $text = $this->limitText($this->extractFromDocx($body));

            return new ExtractedDocument($sourceUrl, $text);
        }

        return null;
    }

    private function extractFromHtml(string $html): ExtractedDocument
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);

        $xpath = new DOMXPath($document);

        foreach (['//script', '//style', '//noscript'] as $expression) {
            foreach ($xpath->query($expression) ?: [] as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $title = trim((string) ($xpath->query('//title')->item(0)?->textContent ?? ''));

        $parts = [];
        foreach (['//h1', '//h2', '//h3'] as $headingExpression) {
            foreach ($xpath->query($headingExpression) ?: [] as $node) {
                $value = trim((string) $node->textContent);
                if ($value !== '') {
                    $parts[] = $value;
                }
            }
        }

        $bodyText = trim((string) ($xpath->query('//body')->item(0)?->textContent ?? $document->textContent));
        if ($bodyText !== '') {
            $parts[] = $bodyText;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $text = preg_replace('/\s+/u', ' ', implode("\n", $parts)) ?? '';

        return new ExtractedDocument($title !== '' ? $title : 'Untitled', $this->limitText(trim($text)));
    }

    private function extractFromPdf(string $binary): string
    {
        // Lightweight extraction fallback: collect literal strings from PDF text objects.
        if (trim($binary) === '') {
            return '';
        }

        $text = '';

        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $binary, $matches)) {
            foreach ($matches[0] as $match) {
                $segment = substr($match, 1, -1);
                $segment = preg_replace('/\\\\([nrtbf\\\\()])/', ' ', $segment) ?? $segment;
                $text .= ' '.$segment;
            }
        }

        $text = preg_replace('/[^\P{C}\n\r\t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function extractFromDocx(string $binary): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'docx_sync_');
        if ($tmpPath === false) {
            return '';
        }

        file_put_contents($tmpPath, $binary);

        $zip = new \ZipArchive();
        $text = '';

        if ($zip->open($tmpPath) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if (is_string($xml) && $xml !== '') {
                $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
                $text = strip_tags($xml);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
            }

            $zip->close();
        }

        @unlink($tmpPath);

        return trim($text);
    }

    private function limitText(string $text): string
    {
        $maxBytes = (int) config('knowledge_sync.fetch.max_text_bytes', 200_000);
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (strlen($text) <= $maxBytes) {
            return $text;
        }

        return substr($text, 0, $maxBytes);
    }
}
