<?php

namespace App\Services\KnowledgeSync;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class SafeHttpFetcher
{
    public function __construct(
        private readonly UrlSafetyValidator $safetyValidator,
    ) {
    }

    public function fetch(string $url, ?string $trustedInternalHost = null): FetchResult
    {
        $safe = $this->safetyValidator->validate($url, $trustedInternalHost);
        if (!$safe['allowed']) {
            return FetchResult::failed($safe['reason'] ?? 'URL failed safety validation.');
        }

        $maxBytes = (int) config('knowledge_sync.fetch.max_response_bytes', 5_000_000);
        $maxRedirects = (int) config('knowledge_sync.fetch.max_redirects', 3);

        $client = new Client([
            'timeout' => (int) config('knowledge_sync.fetch.timeout_seconds', 15),
            'connect_timeout' => (int) config('knowledge_sync.fetch.connect_timeout_seconds', 8),
            'http_errors' => false,
            'allow_redirects' => [
                'max' => $maxRedirects,
                'strict' => true,
                'track_redirects' => true,
                'referer' => false,
            ],
            'headers' => [
                'User-Agent' => 'PUP-KnowledgeSync/1.0',
                'Accept' => 'text/html,text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document;q=0.9,*/*;q=0.1',
            ],
        ]);

        try {
            $response = $client->request('GET', $url);
        } catch (\Throwable $e) {
            return FetchResult::failed('Request failed: '.$e->getMessage());
        }

        $historyHeader = $response->getHeader('X-Guzzle-Redirect-History');
        foreach ($historyHeader as $redirectUrl) {
            $redirectSafe = $this->safetyValidator->validate((string) $redirectUrl, $trustedInternalHost);
            if (!$redirectSafe['allowed']) {
                return FetchResult::failed('Blocked redirect URL encountered.');
            }
        }

        $finalUrl = end($historyHeader);
        if (!is_string($finalUrl) || $finalUrl === '') {
            $finalUrl = $url;
        }

        $finalSafe = $this->safetyValidator->validate($finalUrl, $trustedInternalHost);
        if (!$finalSafe['allowed']) {
            return FetchResult::failed('Final redirected URL is not allowed.');
        }

        $contentType = strtolower(trim((string) $response->getHeaderLine('Content-Type')));
        if ($contentType === '') {
            return FetchResult::failed('Missing content type.');
        }

        if (!$this->isAllowedContentType($contentType)) {
            return FetchResult::failed('Unsupported content type: '.$contentType);
        }

        $lengthHeader = (int) $response->getHeaderLine('Content-Length');
        if ($lengthHeader > 0 && $lengthHeader > $maxBytes) {
            return FetchResult::failed('Response exceeded max allowed size.');
        }

        $stream = $response->getBody();
        $body = '';

        while (!$stream->eof()) {
            $chunk = $stream->read(8192);
            if ($chunk === '') {
                break;
            }

            $body .= $chunk;

            if (strlen($body) > $maxBytes) {
                return FetchResult::failed('Response exceeded max allowed size.');
            }
        }

        if (!Str::startsWith((string) $response->getStatusCode(), '2')) {
            return FetchResult::failed('HTTP status '.$response->getStatusCode());
        }

        return new FetchResult(true, $finalUrl, $contentType, $body, null);
    }

    private function isAllowedContentType(string $contentType): bool
    {
        $allowlist = (array) config('knowledge_sync.fetch.allowed_content_types', []);

        foreach ($allowlist as $allowed) {
            if (!is_string($allowed) || $allowed === '') {
                continue;
            }

            if (str_starts_with($contentType, strtolower($allowed))) {
                return true;
            }
        }

        return false;
    }
}
