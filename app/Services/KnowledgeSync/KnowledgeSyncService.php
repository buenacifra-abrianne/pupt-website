<?php

namespace App\Services\KnowledgeSync;

use App\Models\BotpressKnowledgeLink;
use Illuminate\Support\Collection;

class KnowledgeSyncService
{
    public function __construct(
        private readonly WebsiteLinkDiscoveryService $discoveryService,
        private readonly SafeHttpFetcher $fetcher,
        private readonly TextExtractor $textExtractor,
        private readonly BotpressKnowledgeAdapter $botpressAdapter,
    ) {
    }

    /**
     * @return array{discovered:int,created:int,reactivated:int,inactivated:int}
     */
    public function scanLinks(): array
    {
        $urls = array_values(array_unique($this->discoveryService->discoverKnowledgeCandidateUrls()));
        $now = now();
        $created = 0;
        $reactivated = 0;

        foreach ($urls as $url) {
            $record = BotpressKnowledgeLink::query()->where('url', $url)->first();

            if (!$record) {
                BotpressKnowledgeLink::query()->create([
                    'url' => $url,
                    'sync_status' => 'pending',
                    'last_discovered_at' => $now,
                    'is_active' => true,
                ]);
                $created++;
                continue;
            }

            $record->last_discovered_at = $now;

            if (!$record->is_active) {
                $record->is_active = true;
                $record->sync_status = 'pending';
                $reactivated++;
            }

            $record->save();
        }

        $inactivated = 0;

        if ($urls !== []) {
            $inactivated = BotpressKnowledgeLink::query()
                ->where('is_active', true)
                ->whereNotIn('url', $urls)
                ->update([
                    'is_active' => false,
                    'sync_status' => 'inactive',
                    'updated_at' => $now,
                ]);
        }

        return [
            'discovered' => count($urls),
            'created' => $created,
            'reactivated' => $reactivated,
            'inactivated' => (int) $inactivated,
        ];
    }

    /**
     * @return array{synced:int,skipped:int,failed:int,inactivated:int}
     */
    public function syncAllActive(): array
    {
        $synced = 0;
        $skipped = 0;
        $failed = 0;

        $this->deactivateRemovedLinks();

        /** @var Collection<int, BotpressKnowledgeLink> $records */
        $records = BotpressKnowledgeLink::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get();

        foreach ($records as $record) {
            $result = $this->syncSingle($record);

            if ($result === 'synced') {
                $synced++;
                continue;
            }

            if ($result === 'skipped') {
                $skipped++;
                continue;
            }

            $failed++;
        }

        $inactivated = (int) BotpressKnowledgeLink::query()->where('sync_status', 'inactive')->count();

        return compact('synced', 'skipped', 'failed', 'inactivated');
    }

    public function syncByUrl(string $url): bool
    {
        $record = BotpressKnowledgeLink::query()->firstOrCreate(
            ['url' => $url],
            [
                'sync_status' => 'pending',
                'is_active' => true,
                'last_discovered_at' => now(),
            ]
        );

        return $this->syncSingle($record) !== 'failed';
    }

    private function deactivateRemovedLinks(): void
    {
        $inactiveWithFile = BotpressKnowledgeLink::query()
            ->where('is_active', false)
            ->whereNotNull('botpress_file_id')
            ->get();

        foreach ($inactiveWithFile as $record) {
            $deleted = $this->botpressAdapter->deleteOrDeactivateKnowledgeDocument($record->botpress_file_id);

            if ($deleted) {
                $record->botpress_file_id = null;
            }

            $record->sync_status = 'inactive';
            $record->save();
        }
    }

    private function syncSingle(BotpressKnowledgeLink $record): string
    {
        $baseHost = strtolower((string) parse_url((string) config('knowledge_sync.base_url', config('app.url')), PHP_URL_HOST));
        $host = strtolower((string) parse_url($record->url, PHP_URL_HOST));
        $trustedHost = (bool) config('knowledge_sync.fetch.allow_private_ips_for_internal_base_host', true) && $host === $baseHost
            ? $baseHost
            : null;

        $fetchResult = $this->fetcher->fetch($record->url, $trustedHost);

        if (!$fetchResult->ok || !is_string($fetchResult->body) || !is_string($fetchResult->contentType) || !is_string($fetchResult->finalUrl)) {
            $record->sync_status = 'failed';
            $record->last_error = $fetchResult->error;
            $record->save();

            return 'failed';
        }

        $extracted = $this->textExtractor->extract($fetchResult->contentType, $fetchResult->body, $fetchResult->finalUrl);
        if (!$extracted || trim($extracted->text) === '') {
            $record->sync_status = 'skipped';
            $record->last_error = 'No readable text extracted.';
            $record->save();

            return 'skipped';
        }

        $hash = hash('sha256', $extracted->text);

        if ($record->content_hash === $hash && $record->sync_status === 'synced') {
            $record->last_error = null;
            $record->save();

            return 'skipped';
        }

        $syncResult = $this->botpressAdapter->createOrUpdateKnowledgeDocument(
            $fetchResult->finalUrl,
            $extracted->title,
            $extracted->text,
            $record->botpress_file_id
        );

        if (!$syncResult['ok']) {
            $record->sync_status = 'failed';
            $record->last_error = $syncResult['error'];
            $record->save();

            return 'failed';
        }

        $record->content_hash = $hash;
        $record->sync_status = 'synced';
        $record->last_synced_at = now();
        $record->last_error = null;
        $record->botpress_file_id = $syncResult['file_id'];
        $record->save();

        return 'synced';
    }
}
