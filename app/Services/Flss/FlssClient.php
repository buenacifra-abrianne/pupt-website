<?php

namespace App\Services\Flss;

use Illuminate\Support\Facades\Http;

class FlssClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.flss.base_url'), '/');
        $this->apiKey = (string) config('services.flss.api_key');
        $this->timeout = (int) config('services.flss.timeout', 15);
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->buildUrl($path, $query);
        $method = 'GET';
        $body = '';
        $timestamp = (string) time();
        $nonce = '';

        $message = "{$method}|{$url}|{$body}|{$timestamp}|{$nonce}";
        $signature = hash_hmac('sha256', $message, $this->apiKey);

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-HMAC-Signature' => $signature,
                'X-HMAC-Timestamp' => $timestamp,
                'X-HMAC-Nonce' => $nonce,
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'FLSS request failed: HTTP '.$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    public function health(): array
    {
        return $this->get('/api/health');
    }

    protected function buildUrl(string $path, array $query = []): string
    {
        $url = $this->baseUrl.'/'.ltrim($path, '/');

        if (!empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }
}