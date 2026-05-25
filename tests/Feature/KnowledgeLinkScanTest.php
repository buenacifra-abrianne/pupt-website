<?php

namespace Tests\Feature;

use App\Models\BotpressKnowledgeLink;
use App\Services\KnowledgeSync\BotpressKnowledgeAdapter;
use App\Services\KnowledgeSync\KnowledgeSyncService;
use App\Services\KnowledgeSync\SafeHttpFetcher;
use App\Services\KnowledgeSync\TextExtractor;
use App\Services\KnowledgeSync\WebsiteLinkDiscoveryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class KnowledgeLinkScanTest extends TestCase
{
    public function test_scan_deduplicates_and_marks_removed_links_inactive(): void
    {
        BotpressKnowledgeLink::query()->create([
            'url' => 'https://old.example.com/page',
            'sync_status' => 'synced',
            'is_active' => true,
            'last_discovered_at' => now()->subDay(),
        ]);

        $discovery = Mockery::mock(WebsiteLinkDiscoveryService::class);
        $discovery->shouldReceive('discoverKnowledgeCandidateUrls')->once()->andReturn([
            'https://new.example.com/doc.pdf',
            'https://new.example.com/doc.pdf',
        ]);

        $service = new KnowledgeSyncService(
            $discovery,
            Mockery::mock(SafeHttpFetcher::class),
            new TextExtractor(),
            Mockery::mock(BotpressKnowledgeAdapter::class)
        );

        $result = $service->scanLinks();

        $this->assertSame(1, $result['discovered']);
        $this->assertSame(1, BotpressKnowledgeLink::query()->where('url', 'https://new.example.com/doc.pdf')->count());

        $old = BotpressKnowledgeLink::query()->where('url', 'https://old.example.com/page')->firstOrFail();
        $this->assertFalse($old->is_active);
        $this->assertSame('inactive', $old->sync_status);
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
