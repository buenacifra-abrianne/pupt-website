<?php

namespace App\Console\Commands;

use App\Jobs\SyncBotpressKnowledgeJob;
use App\Services\KnowledgeSync\KnowledgeSyncService;
use Illuminate\Console\Command;

class SyncBotpressCommand extends Command
{
    protected $signature = 'sync:botpress {--queue : Dispatch background job instead of running now}';

    protected $description = 'Sync discovered knowledge links to Botpress.';

    public function handle(KnowledgeSyncService $service): int
    {
        if ((bool) $this->option('queue')) {
            SyncBotpressKnowledgeJob::dispatch();
            $this->info('sync:botpress dispatched to queue.');

            return self::SUCCESS;
        }

        $result = $service->syncAllActive();

        $this->info('Sync completed.');
        $this->line('Synced: '.$result['synced']);
        $this->line('Skipped: '.$result['skipped']);
        $this->line('Failed: '.$result['failed']);
        $this->line('Inactive: '.$result['inactivated']);

        return self::SUCCESS;
    }
}
