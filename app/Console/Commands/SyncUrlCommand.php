<?php

namespace App\Console\Commands;

use App\Jobs\SyncBotpressKnowledgeJob;
use App\Services\KnowledgeSync\KnowledgeSyncService;
use Illuminate\Console\Command;

class SyncUrlCommand extends Command
{
    protected $signature = 'sync:url {url : URL to discover/sync} {--queue : Dispatch background job instead of running now}';

    protected $description = 'Sync a single URL to Botpress.';

    public function handle(KnowledgeSyncService $service): int
    {
        $url = trim((string) $this->argument('url'));

        if ($url === '') {
            $this->error('URL is required.');

            return self::FAILURE;
        }

        if ((bool) $this->option('queue')) {
            SyncBotpressKnowledgeJob::dispatch($url);
            $this->info('sync:url dispatched to queue.');

            return self::SUCCESS;
        }

        $ok = $service->syncByUrl($url);

        if (!$ok) {
            $this->error('sync:url failed.');

            return self::FAILURE;
        }

        $this->info('sync:url completed.');

        return self::SUCCESS;
    }
}
