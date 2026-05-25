<?php

namespace App\Console\Commands;

use App\Jobs\ScanWebsiteLinksJob;
use App\Services\KnowledgeSync\KnowledgeSyncService;
use Illuminate\Console\Command;

class ScanLinksCommand extends Command
{
    protected $signature = 'scan:links {--queue : Dispatch background job instead of running now}';

    protected $description = 'Discover external/document links across website sources.';

    public function handle(KnowledgeSyncService $service): int
    {
        if ((bool) $this->option('queue')) {
            ScanWebsiteLinksJob::dispatch();
            $this->info('scan:links dispatched to queue.');

            return self::SUCCESS;
        }

        $result = $service->scanLinks();

        $this->info('Scan completed.');
        $this->line('Discovered: '.$result['discovered']);
        $this->line('Created: '.$result['created']);
        $this->line('Reactivated: '.$result['reactivated']);
        $this->line('Inactivated: '.$result['inactivated']);

        return self::SUCCESS;
    }
}
