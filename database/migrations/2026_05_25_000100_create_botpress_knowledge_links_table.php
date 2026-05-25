<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('botpress_knowledge_links')) {
            return;
        }

        Schema::create('botpress_knowledge_links', function (Blueprint $table) {
            $table->id();
            $table->string('url', 512)->unique();
            $table->string('content_hash', 64)->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'skipped', 'inactive'])->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_discovered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('last_error')->nullable();
            $table->string('botpress_file_id')->nullable();
            $table->timestamps();

            $table->index('sync_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('botpress_knowledge_links');
    }
};
