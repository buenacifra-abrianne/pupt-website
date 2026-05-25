<?php

namespace Tests\Feature;

use App\Models\BotpressKnowledgeLink;
use App\Services\KnowledgeSync\BotpressKnowledgeAdapter;
use App\Services\KnowledgeSync\FetchResult;
use App\Services\KnowledgeSync\KnowledgeSyncService;
use App\Services\KnowledgeSync\SafeHttpFetcher;
use App\Services\KnowledgeSync\TextExtractor;
use App\Services\KnowledgeSync\WebsiteLinkDiscoveryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class KnowledgeSyncServiceTest extends TestCase
{
    public function test_idempotent_sync_uses_content_hash_to_skip_duplicate_uploads(): void
    {
        $discovery = Mockery::mock(WebsiteLinkDiscoveryService::class);
        $fetcher = Mockery::mock(SafeHttpFetcher::class);
        $adapter = Mockery::mock(BotpressKnowledgeAdapter::class);

        BotpressKnowledgeLink::query()->create([
            'url' => 'https://example.com/a',
            'sync_status' => 'pending',
            'is_active' => true,
            'last_discovered_at' => now(),
        ]);

        $fetcher->shouldReceive('fetch')->twice()->andReturn(
            new FetchResult(true, 'https://example.com/a', 'text/plain', 'same body', null)
        );

        $adapter->shouldReceive('deleteOrDeactivateKnowledgeDocument')->andReturnTrue();
        $adapter->shouldReceive('createOrUpdateKnowledgeDocument')->once()->andReturn([
            'ok' => true,
            'file_id' => 'file-1',
            'error' => null,
        ]);

        $service = new KnowledgeSyncService($discovery, $fetcher, new TextExtractor(), $adapter);

        $first = $service->syncAllActive();
        $second = $service->syncAllActive();

        $this->assertSame(1, $first['synced']);
        $this->assertSame(1, $second['skipped']);

        $record = BotpressKnowledgeLink::query()->where('url', 'https://example.com/a')->firstOrFail();
        $this->assertSame('synced', $record->sync_status);
        $this->assertNotNull($record->content_hash);
    }

    public function test_removed_link_deactivation_calls_botpress_delete(): void
    {
        $discovery = Mockery::mock(WebsiteLinkDiscoveryService::class);
        $fetcher = Mockery::mock(SafeHttpFetcher::class);
        $adapter = Mockery::mock(BotpressKnowledgeAdapter::class);

        BotpressKnowledgeLink::query()->create([
            'url' => 'https://example.com/old',
            'sync_status' => 'inactive',
            'is_active' => false,
            'botpress_file_id' => 'file-old',
            'last_discovered_at' => now()->subDay(),
        ]);

        $adapter->shouldReceive('deleteOrDeactivateKnowledgeDocument')->once()->with('file-old')->andReturnTrue();

        $service = new KnowledgeSyncService($discovery, $fetcher, new TextExtractor(), $adapter);
        $service->syncAllActive();

        $record = BotpressKnowledgeLink::query()->where('url', 'https://example.com/old')->firstOrFail();
        $this->assertNull($record->botpress_file_id);
        $this->assertSame('inactive', $record->sync_status);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('botpress_knowledge_links');
        Schema::create('botpress_knowledge_links', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048)->unique();
            $table->string('content_hash', 64)->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_discovered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('last_error')->nullable();
            $table->string('botpress_file_id')->nullable();
            $table->timestamps();
        });
    }
}
