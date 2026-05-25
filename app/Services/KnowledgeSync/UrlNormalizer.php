<?php

namespace App\Services\KnowledgeSync;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Arr;

class UrlNormalizer
{
    /**
     * @param string[] $stripParameters
     */
    public function normalize(string $rawUrl, string $baseUrl, array $stripParameters = []): ?string
    {
        $candidate = trim(html_entity_decode($rawUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($candidate === '' || str_starts_with($candidate, '#')) {
            return null;
        }

        if (preg_match('/^(javascript:|data:|mailto:|tel:|file:|ftp:)/i', $candidate) === 1) {
            return null;
        }

        try {
            $baseUri = new Uri($baseUrl);
            $candidateUri = new Uri($candidate);
            $uri = $candidateUri->getScheme() === ''
                ? UriResolver::resolve($baseUri, $candidateUri)
                : $candidateUri;
        } catch (\Throwable) {
            return null;
        }

        $scheme = strtolower($uri->getScheme());
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($uri->getHost());
        if ($host === '') {
            return null;
        }

        $query = [];
        parse_str($uri->getQuery(), $query);

        foreach ($stripParameters as $param) {
            if (is_string($param) && $param !== '') {
                Arr::forget($query, $param);
                unset($query[$param]);
            }
        }

        $port = $uri->getPort();
        $normalized = $uri
            ->withScheme($scheme)
            ->withHost($host)
            ->withFragment('')
            ->withQuery(http_build_query($query));

        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $normalized = $normalized->withPort(null);
        }

        $path = preg_replace('#//+#', '/', $normalized->getPath()) ?: '/';
        if ($path === '') {
            $path = '/';
        }

        $normalized = $normalized->withPath($path);

        return (string) $normalized;
    }

    public function isDocumentUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return str_ends_with($path, '.pdf') || str_ends_with($path, '.docx');
    }
}
