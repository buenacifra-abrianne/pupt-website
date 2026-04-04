<?php

namespace App\Services\Ocms;

use Illuminate\Support\Facades\Http;

class OcmsClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ocms.base_url'), '/');
        $this->apiKey = (string) config('services.ocms.api_key');
        $this->timeout = (int) config('services.ocms.timeout', 15);
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl.'/'.ltrim($path, '/');

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-External-Api-Key' => $this->apiKey,
            ])
            ->get($url, $query);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'OCMS request failed: HTTP '.$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }
}