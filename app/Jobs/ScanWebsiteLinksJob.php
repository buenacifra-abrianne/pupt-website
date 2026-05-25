<?php

namespace App\Jobs;

use App\Services\KnowledgeSync\KnowledgeSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanWebsiteLinksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(KnowledgeSyncService $service): void
    {
        $service->scanLinks();
    }
}
