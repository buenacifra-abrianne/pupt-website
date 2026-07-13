<?php

namespace App\Services\Ocms;

use Illuminate\Support\Facades\Http;

class OcmsClient
{
    protected string $baseUrl;
    protected string $token;
    protected string $systemKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ocms.base_url'), '/');
        $this->token = (string) config('services.ocms.token');
        $this->systemKey = (string) config('services.ocms.system_key');
        $this->timeout = (int) config('services.ocms.timeout', 15);
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '' && $this->token !== '';
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl.'/'.ltrim($path, '/');

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
                'X-External-System' => $this->systemKey,
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