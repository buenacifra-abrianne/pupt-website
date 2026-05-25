<?php

namespace App\Services\KnowledgeSync;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BotpressKnowledgeAdapter
{
    public function testConnection(): bool
    {
        $response = $this->client()->get($this->baseUrl().'/v1/files');

        return $response->successful();
    }

    /**
     * @return array{ok:bool,file_id:?string,error:?string}
     */
    public function createOrUpdateKnowledgeDocument(string $sourceUrl, string $title, string $text, ?string $existingFileId = null): array
    {
        $keyPrefix = trim((string) config('knowledge_sync.botpress.file_key_prefix', 'knowledge-sync'));
        $fileKey = $keyPrefix.'/'.hash('sha256', $sourceUrl).'.txt';
        $payload = $title."\n\nSource: {$sourceUrl}\n\n".$text;
        $size = strlen($payload);

        // TODO: Confirm latest Botpress endpoint contract for Knowledge Base document upsert.
        // Current implementation uses Files API as official transport layer.
        $createResponse = $this->client()->put($this->baseUrl().'/v1/files', [
            'key' => $fileKey,
            'size' => $size,
        ]);

        if (!$createResponse->successful()) {
            return [
                'ok' => false,
                'file_id' => $existingFileId,
                'error' => 'Botpress file create failed: '.$createResponse->status(),
            ];
        }

        $uploadUrl = (string) data_get($createResponse->json(), 'file.uploadUrl', '');
        $fileId = (string) data_get($createResponse->json(), 'file.id', '');

        if ($uploadUrl === '' || $fileId === '') {
            return [
                'ok' => false,
                'file_id' => $existingFileId,
                'error' => 'Botpress response missing upload URL or file ID.',
            ];
        }

        $uploadResponse = Http::timeout((int) config('knowledge_sync.fetch.timeout_seconds', 15))
            ->withBody($payload, 'text/plain; charset=utf-8')
            ->put($uploadUrl);

        if (!$uploadResponse->successful()) {
            return [
                'ok' => false,
                'file_id' => $existingFileId,
                'error' => 'Botpress file upload failed: '.$uploadResponse->status(),
            ];
        }

        // TODO: Confirm Knowledge Base indexing endpoint for associating files to KB.
        // Placeholder uses file upload only; add explicit KB linkage endpoint when confirmed.

        return [
            'ok' => true,
            'file_id' => $fileId,
            'error' => null,
        ];
    }

    public function deleteOrDeactivateKnowledgeDocument(?string $fileId): bool
    {
        if (!is_string($fileId) || trim($fileId) === '') {
            return true;
        }

        // TODO: Confirm whether KB deactivation endpoint should be used instead of file delete.
        $response = $this->client()->delete($this->baseUrl().'/v1/files/'.urlencode($fileId));

        return $response->successful() || $response->status() === 404;
    }

    private function client(): PendingRequest
    {
        return Http::timeout((int) config('knowledge_sync.fetch.timeout_seconds', 15))
            ->acceptJson()
            ->asJson()
            ->withToken((string) config('knowledge_sync.botpress.token'))
            ->withHeaders([
                'x-bot-id' => (string) config('knowledge_sync.botpress.bot_id'),
            ]);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('knowledge_sync.botpress.api_base_url', 'https://api.botpress.cloud'), '/');
    }
}
