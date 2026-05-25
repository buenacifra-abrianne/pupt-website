<?php

namespace App\Jobs;

use App\Services\KnowledgeSync\KnowledgeSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBotpressKnowledgeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(
        public readonly ?string $url = null,
    ) {
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800, 3600];
    }

    public function handle(KnowledgeSyncService $service): void
    {
        if (is_string($this->url) && trim($this->url) !== '') {
            $service->syncByUrl($this->url);

            return;
        }

        $service->syncAllActive();
    }
}
